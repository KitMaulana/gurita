<?php

namespace App\Imports;

use App\Enums\HariEnum;
use App\Models\Jadwal;
use App\Models\TahunAjaran;
use App\Services\MasterDataProvisioner;
use App\Services\PemeriksaBentrokJadwal;
use App\Support\JamPelajaran;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

/**
 * Import jadwal dari CSV / Excel:
 * Format ringkas (SIDACHEERS): hari, jam_ke, nama_kelas, nama_guru, mata_pelajaran
 * Format lengkap: hari, jam_ke, jam_mulai, jam_selesai, kelas, mata_pelajaran, guru_nip, ruang, title
 *
 * Mengadopsi prinsip Schedule-First:
 * Kelas, Mata Pelajaran, dan Akun Guru otomatis dibuatkan jika belum ada di sistem.
 */
class JadwalImport implements ToCollection, WithHeadingRow
{
    /** @var array<int,string> */
    public array $galat = [];

    /** @var array<int,string> */
    public array $peringatan = [];

    public int $jumlahTersimpan = 0;

    /** @var array<int, array{nama: string, email: string, nip: ?string, password: string}> */
    public array $guruBaru = [];

    /** @var array<int, string> */
    public array $kelasBaru = [];

    /** @var array<int, string> */
    public array $mapelBaru = [];

    public function __construct(protected ?int $tahunAjaranId = null)
    {
        $this->tahunAjaranId ??= TahunAjaran::aktif()?->id;
    }

    public function collection(Collection $baris): void
    {
        $provisioner = app(MasterDataProvisioner::class);
        $provisioner->preload($this->tahunAjaranId);

        // Preload seluruh jadwal yang sudah ada pada tahun ajaran aktif ke memori
        $existingJadwals = Jadwal::where('tahun_ajaran_id', $this->tahunAjaranId)
            ->with([
                'kelas:id,nama',
                'mataPelajaran:id,nama,singkatan',
                'guru:id,name',
            ])
            ->get();

        $kelasOccupied = [];
        $guruOccupied = [];
        $fileKelasSlots = [];
        $fileGuruSlots = [];

        foreach ($existingJadwals as $ej) {
            $hariVal = $ej->hari instanceof HariEnum ? $ej->hari->value : (string) $ej->hari;
            $hariLabel = $ej->hari instanceof HariEnum ? $ej->hari->label() : (HariEnum::tryFrom($hariVal)?->label() ?? $hariVal);

            if ($ej->kelas_id) {
                $slotKelasKey = "{$hariVal}_{$ej->jam_ke}_{$ej->kelas_id}";
                $kelasOccupied[$slotKelasKey] = [
                    'source' => 'db',
                    'nama_kelas' => $ej->kelas?->nama ?? 'Semua Kelas',
                    'kegiatan' => $ej->title ?: ($ej->mataPelajaran?->nama ?? 'Kegiatan'),
                    'nama_guru' => $ej->guru?->name ?? '—',
                    'hari_label' => $hariLabel,
                    'jam_ke' => $ej->jam_ke,
                ];
            }

            if ($ej->guru_id) {
                $slotGuruKey = "{$hariVal}_{$ej->jam_ke}_{$ej->guru_id}";
                $guruOccupied[$slotGuruKey][] = [
                    'source' => 'db',
                    'nama_guru' => $ej->guru?->name ?? 'Guru',
                    'nama_kelas' => $ej->kelas?->nama ?? 'Kelas',
                    'hari_label' => $hariLabel,
                    'jam_ke' => $ej->jam_ke,
                    'boleh_paralel' => $ej->mataPelajaran ? $ej->mataPelajaran->bolehParalel() : false,
                ];
            }
        }

        $siapSimpan = [];

        foreach ($baris as $i => $data) {
            $nomorBaris = $i + 2;

            $hariRaw = mb_strtolower(trim((string) ($data['hari'] ?? '')));
            $jamKe = (int) ($data['jam_ke'] ?? 0);
            $namaKelas = trim((string) ($data['nama_kelas'] ?? $data['kelas'] ?? ''));
            $namaMapel = trim((string) ($data['mata_pelajaran'] ?? ''));
            $nip = trim((string) ($data['guru_nip'] ?? $data['nip'] ?? ''));
            $namaGuru = trim((string) ($data['nama_guru'] ?? $data['guru'] ?? ''));
            $title = trim((string) ($data['title'] ?? $data['kegiatan'] ?? ''));
            $ruang = trim((string) ($data['ruang'] ?? '')) ?: null;

            // Baris kosong diabaikan
            if ($namaKelas === '' && $namaMapel === '' && $title === '') {
                continue;
            }

            if (! HariEnum::tryFrom($hariRaw)) {
                $this->galat[] = "Baris {$nomorBaris}: hari \"{$hariRaw}\" tidak dikenali.";
                continue;
            }

            if ($jamKe < 1 || $jamKe > 15) {
                $this->galat[] = "Baris {$nomorBaris}: jam ke-{$jamKe} tidak valid.";
                continue;
            }

            $jamMulai = $this->jam($data['jam_mulai'] ?? null);
            $jamSelesai = $this->jam($data['jam_selesai'] ?? null);

            // Jika jam mulai/selesai kosong, otomatis hitung dari slot JamPelajaran
            if (empty($jamMulai) || empty($jamSelesai)) {
                $slot = JamPelajaran::getSlotTime($jamKe, $hariRaw);
                if ($slot) {
                    $jamMulai = $jamMulai ?: $slot['start'];
                    $jamSelesai = $jamSelesai ?: $slot['end'];
                }
            }

            if (empty($jamMulai) || empty($jamSelesai)) {
                $this->galat[] = "Baris {$nomorBaris}: jam ke / jam mulai / jam selesai tidak valid.";
                continue;
            }

            // A. Kasus Agenda Bersama (Semua Kelas)
            if ($title !== '') {
                $calon = [
                    'tahun_ajaran_id' => $this->tahunAjaranId,
                    'kelas_id' => null,
                    'mata_pelajaran_id' => null,
                    'guru_id' => null,
                    'title' => $title,
                    'hari' => $hariRaw,
                    'jam_ke' => $jamKe,
                    'jam_mulai' => $jamMulai,
                    'jam_selesai' => $jamSelesai,
                    'ruang' => $ruang,
                ];

                $siapSimpan[] = $calon;
                continue;
            }

            // B. Kasus Mata Pelajaran
            $isNamaKelasKosong = ($namaKelas === '' || in_array(mb_strtolower($namaKelas), ['-', 'kosong', 'null', 'none'], true));

            if ($namaMapel === '') {
                $this->galat[] = "Baris {$nomorBaris}: mata pelajaran tidak boleh kosong.";
                continue;
            }
            if ($namaGuru === '' && $nip === '') {
                $this->galat[] = "Baris {$nomorBaris}: nama guru atau NIP tidak boleh kosong.";
                continue;
            }

            // Cek entitas yang sudah ada tanpa melakukan insert ke database
            $existingKelas = $isNamaKelasKosong ? null : $provisioner->cariKelas($namaKelas, $this->tahunAjaranId);
            $existingMapel = $provisioner->cariMataPelajaran($namaMapel);
            $existingGuru = $provisioner->cariGuru($namaGuru, $nip);

            if ($existingMapel) {
                $bolehParalel = $existingMapel->bolehParalel();
            } else {
                $teksMapel = mb_strtoupper($namaMapel);
                $bolehParalel = str_contains($teksMapel, 'PJOK') || str_contains($teksMapel, 'JASMANI');
            }

            $bentrok = [];

            // 1. Cek bentrok kelas
            if ($existingKelas) {
                $slotKelasKey = "{$hariRaw}_{$jamKe}_{$existingKelas->id}";
                if (isset($kelasOccupied[$slotKelasKey])) {
                    $info = $kelasOccupied[$slotKelasKey];
                    if ($info['source'] === 'db') {
                        $bentrok[] = sprintf(
                            'Bentrok: kelas %s sudah terisi %s (%s) pada %s jam ke-%d.',
                            $info['nama_kelas'],
                            $info['kegiatan'],
                            $info['nama_guru'],
                            $info['hari_label'],
                            $info['jam_ke'],
                        );
                    } else {
                        $bentrok[] = 'Bentrok dengan baris lain di berkas yang sama (kelas & jam sama).';
                    }
                }
            }

            if (! $isNamaKelasKosong) {
                $fileKelasKey = "{$hariRaw}_{$jamKe}_".mb_strtolower($namaKelas);
                if (isset($fileKelasSlots[$fileKelasKey]) && ! in_array('Bentrok dengan baris lain di berkas yang sama (kelas & jam sama).', $bentrok, true)) {
                    $bentrok[] = 'Bentrok dengan baris lain di berkas yang sama (kelas & jam sama).';
                }
            }

            // 2. Cek bentrok guru
            if ($existingGuru) {
                $slotGuruKey = "{$hariRaw}_{$jamKe}_{$existingGuru->id}";
                if (isset($guruOccupied[$slotGuruKey]) && ! $bolehParalel) {
                    foreach ($guruOccupied[$slotGuruKey] as $info) {
                        if (! $info['boleh_paralel']) {
                            if ($info['source'] === 'db') {
                                $bentrok[] = sprintf(
                                    'Bentrok: %s sudah mengajar %s pada %s jam ke-%d.',
                                    $info['nama_guru'],
                                    $info['nama_kelas'],
                                    $info['hari_label'],
                                    $info['jam_ke'],
                                    );
                            } else {
                                $bentrok[] = 'Bentrok dengan baris lain di berkas yang sama (guru & jam sama).';
                            }
                            break;
                        }
                    }
                }
            }

            $guruIdent = $existingGuru ? 'id_'.$existingGuru->id : ($nip !== '' ? 'nip_'.$nip : 'nama_'.mb_strtolower($namaGuru));
            $fileGuruKey = "{$hariRaw}_{$jamKe}_{$guruIdent}";
            if (isset($fileGuruSlots[$fileGuruKey]) && ! $bolehParalel) {
                foreach ($fileGuruSlots[$fileGuruKey] as $fg) {
                    if (! $fg['boleh_paralel'] && ! in_array('Bentrok dengan baris lain di berkas yang sama (guru & jam sama).', $bentrok, true)) {
                        $bentrok[] = 'Bentrok dengan baris lain di berkas yang sama (guru & jam sama).';
                        break;
                    }
                }
            }

            if ($bentrok) {
                foreach ($bentrok as $pesan) {
                    $this->peringatan[] = "Baris {$nomorBaris}: {$pesan} (jadwal tetap diinput)";
                }
            }

            // Cari kelas HANYA jika sudah terdaftar dari data siswa (TIDAK membuat kelas baru dari jadwal)
            $kelas = null;
            if (! $isNamaKelasKosong) {
                $kelas = $provisioner->cariKelas($namaKelas, $this->tahunAjaranId);
            }

            $mapelBaru = false;
            $mapel = $provisioner->temukanAtauBuatMataPelajaran($namaMapel, $this->tahunAjaranId, $mapelBaru);
            if ($mapelBaru) {
                $this->mapelBaru[$mapel->id] = $mapel->nama;
            }

            $guruBaru = false;
            $guru = $provisioner->temukanAtauBuatGuru($namaGuru, $nip, $guruBaru);
            if ($guruBaru) {
                $this->guruBaru[$guru->id] = [
                    'nama' => $guru->name,
                    'email' => $guru->email,
                    'nip' => $guru->nip,
                    'password' => '12345',
                ];
            }

            // Daftarkan ke slot terpakai di memori
            if ($kelas) {
                $slotKelasKey = "{$hariRaw}_{$jamKe}_{$kelas->id}";
                $kelasOccupied[$slotKelasKey] = [
                    'source' => 'file',
                    'nama_kelas' => $kelas->nama,
                    'kegiatan' => $mapel->nama,
                    'nama_guru' => $guru->name,
                    'hari_label' => HariEnum::tryFrom($hariRaw)?->label() ?? $hariRaw,
                    'jam_ke' => $jamKe,
                ];
                if (isset($fileKelasKey)) {
                    $fileKelasSlots[$fileKelasKey] = true;
                }
            }

            $slotGuruKey = "{$hariRaw}_{$jamKe}_{$guru->id}";
            $guruOccupied[$slotGuruKey][] = [
                'source' => 'file',
                'nama_guru' => $guru->name,
                'nama_kelas' => $kelas?->nama ?? '—',
                'hari_label' => HariEnum::tryFrom($hariRaw)?->label() ?? $hariRaw,
                'jam_ke' => $jamKe,
                'boleh_paralel' => $mapel->bolehParalel(),
            ];
            $fileGuruSlots[$fileGuruKey][] = [
                'boleh_paralel' => $mapel->bolehParalel(),
            ];

            $siapSimpan[] = [
                'tahun_ajaran_id' => $this->tahunAjaranId,
                'kelas_id' => $kelas?->id,
                'nama_kelas_jadwal' => $namaKelas !== '' ? $namaKelas : null,
                'mata_pelajaran_id' => $mapel->id,
                'guru_id' => $guru->id,
                'title' => null,
                'hari' => $hariRaw,
                'jam_ke' => $jamKe,
                'jam_mulai' => $jamMulai,
                'jam_selesai' => $jamSelesai,
                'ruang' => $ruang,
            ];
        }

        DB::transaction(function () use ($siapSimpan) {
            foreach ($siapSimpan as $calon) {
                Jadwal::create($calon);
                $this->jumlahTersimpan++;
            }
        });
    }

    protected function jam(mixed $nilai): ?string
    {
        if (blank($nilai)) {
            return null;
        }

        if (is_numeric($nilai) && $nilai < 1) {
            return gmdate('H:i', (int) round($nilai * 86400));
        }

        $teks = trim((string) $nilai);

        if (preg_match('/^\d{1,2}:\d{2}(:\d{2})?$/', $teks)) {
            return substr(str_pad($teks, 5, '0', STR_PAD_LEFT), 0, 5);
        }

        return null;
    }

    public function ringkasan(): string
    {
        $info = "{$this->jumlahTersimpan} jadwal berhasil disimpan.";

        $tambahan = [];
        if (count($this->guruBaru) > 0) {
            $tambahan[] = count($this->guruBaru).' akun guru baru dibuat';
        }
        if (count($this->kelasBaru) > 0) {
            $tambahan[] = count($this->kelasBaru).' kelas baru dibuat';
        }
        if (count($this->mapelBaru) > 0) {
            $tambahan[] = count($this->mapelBaru).' mapel baru dibuat';
        }

        if (! empty($tambahan)) {
            $info .= ' ('.implode(', ', $tambahan).')';
        }

        return $info;
    }
}
