<?php

namespace App\Imports;

use App\Models\Kelas;
use App\Models\Siswa;
use App\Models\TahunAjaran;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

/**
 * Import siswa dari template_siswa.csv:
 * nisn,nis,nama,jenis_kelamin,kelas,no_absen
 *
 * NISN duplikat → data diperbarui (bukan digandakan).
 * Kelas yang belum ada → baris ditolak dengan pesan nomor baris (§9).
 */
class SiswaImport implements ToCollection, WithHeadingRow
{
    /** @var array<int,string> */
    public array $galat = [];

    public int $jumlahBaru = 0;

    public int $jumlahDiperbarui = 0;

    /** @var array<int,string> */
    public array $kelasBaru = [];

    public function __construct(protected ?int $tahunAjaranId = null)
    {
        $this->tahunAjaranId ??= TahunAjaran::aktif()?->id;
    }

    public function collection(Collection $baris): void
    {
        $kelasList = Kelas::where('tahun_ajaran_id', $this->tahunAjaranId)->get();
        $kelasPerNama = [];
        $kelasPerCanonical = [];

        foreach ($kelasList as $k) {
            $kelasPerNama[mb_strtolower(trim($k->nama))] = $k;
            $canonical = Kelas::canonicalizeName($k->nama);
            if ($canonical !== '') {
                $kelasPerCanonical[$canonical] ??= $k;
            }
        }

        $provisioner = app(\App\Services\MasterDataProvisioner::class);

        foreach ($baris as $i => $data) {
            $nomorBaris = $i + 2;   // +1 header, +1 basis satu

            $nisn = trim((string) ($data['nisn'] ?? '')) ?: null;
            $nis = trim((string) ($data['nis'] ?? '')) ?: null;
            $nama = trim((string) ($data['nama'] ?? ''));
            $namaKelas = mb_strtolower(trim((string) ($data['kelas'] ?? '')));
            $jenisKelamin = strtoupper(trim((string) ($data['jenis_kelamin'] ?? '')));

            if ($nisn === null && $nis === null && $nama === '') {
                continue;   // baris kosong di akhir berkas
            }

            if ($nama === '') {
                $this->galat[] = "Baris {$nomorBaris}: nama siswa wajib diisi.";

                continue;
            }

            if (! in_array($jenisKelamin, ['L', 'P'], true)) {
                $this->galat[] = "Baris {$nomorBaris}: jenis kelamin harus L atau P.";

                continue;
            }

            $kelas = null;
            if ($namaKelas !== '') {
                $kelas = $kelasPerNama[$namaKelas] ?? null;

                if (! $kelas) {
                    $canonicalInput = Kelas::canonicalizeName($namaKelas);
                    $kelas = $kelasPerCanonical[$canonicalInput] ?? null;
                }

                // Kelas belum terdaftar -> otomatis dibuatkan mengikuti data bulk siswa
                if (! $kelas) {
                    $kelasDibuat = false;
                    $kelas = $provisioner->temukanAtauBuatKelas(trim((string) $data['kelas']), $this->tahunAjaranId, $kelasDibuat);

                    $kelasPerNama[$namaKelas] = $kelas;
                    $canonicalInput = Kelas::canonicalizeName($namaKelas);
                    if ($canonicalInput !== '') {
                        $kelasPerCanonical[$canonicalInput] = $kelas;
                    }
                    if ($kelasDibuat) {
                        $this->kelasBaru[$kelas->id] = $kelas->nama;
                    }
                }
            }

            DB::transaction(function () use ($data, $nisn, $nis, $nama, $jenisKelamin, $kelas, $nomorBaris) {
                // Pencarian siswa:
                // 1. Berdasarkan NISN (jika terisi)
                // 2. Berdasarkan NIS (jika NISN kosong tetapi NIS terisi)
                // 3. Berdasarkan nama di kelas terkait (jika NISN & NIS kosong)
                $siswa = null;
                if ($nisn) {
                    $siswa = Siswa::withTrashed()->firstWhere('nisn', $nisn);
                } elseif ($nis) {
                    $siswa = Siswa::withTrashed()->firstWhere('nis', $nis);
                } elseif ($kelas) {
                    $siswa = $kelas->siswas()->withTrashed()->where('siswas.nama', $nama)->first();
                }

                if ($siswa) {
                    $siswa->restore();
                    $siswa->update([
                        'nisn' => $nisn ?: $siswa->nisn,
                        'nis' => $nis ?: $siswa->nis,
                        'nama' => $nama,
                        'jenis_kelamin' => $jenisKelamin,
                        'is_aktif' => true,
                    ]);
                    $this->jumlahDiperbarui++;
                } else {
                    $siswa = Siswa::create([
                        'nisn' => $nisn,
                        'nis' => $nis,
                        'nama' => $nama,
                        'jenis_kelamin' => $jenisKelamin,
                        'is_aktif' => true,
                    ]);
                    $this->jumlahBaru++;
                }

                if (! $kelas) {
                    return;
                }

                $noAbsen = (int) ($data['no_absen'] ?? 0);

                if ($noAbsen < 1) {
                    $noAbsen = ((int) $kelas->siswas()->max('no_absen')) + 1;
                }

                // Nomor absen bentrok → geser ke nomor kosong berikutnya.
                $dipakai = $kelas->siswas()
                    ->wherePivot('no_absen', $noAbsen)
                    ->where('siswas.id', '!=', $siswa->id)
                    ->exists();

                if ($dipakai) {
                    $noAbsen = ((int) $kelas->siswas()->max('no_absen')) + 1;
                    $this->galat[] = "Baris {$nomorBaris}: nomor absen sudah dipakai, siswa diberi nomor {$noAbsen}.";
                }

                $kelas->siswas()->syncWithoutDetaching([$siswa->id => ['no_absen' => $noAbsen]]);
            });
        }

        // Sinkronisasi otomatis: jika jadwal diunggah duluan sebelum siswa,
        // sekarang hubungkan jadwal-jadwal tersebut ke kelas yang baru dibuat dari data siswa.
        $this->sinkronkanJadwal($provisioner);
    }

    /**
     * Menghubungkan jadwal yang belum memiliki kelas_id ke kelas yang baru dibuat berdasarkan nama_kelas_jadwal.
     */
    protected function sinkronkanJadwal(\App\Services\MasterDataProvisioner $provisioner): void
    {
        $jadwalTertunda = \App\Models\Jadwal::where('tahun_ajaran_id', $this->tahunAjaranId)
            ->whereNull('kelas_id')
            ->whereNotNull('nama_kelas_jadwal')
            ->get();

        foreach ($jadwalTertunda as $jadwal) {
            $kelas = $provisioner->cariKelas($jadwal->nama_kelas_jadwal, $this->tahunAjaranId);
            if ($kelas) {
                $jadwal->update(['kelas_id' => $kelas->id]);
            }
        }
    }

    public function ringkasan(): string
    {
        $teks = "{$this->jumlahBaru} siswa baru, {$this->jumlahDiperbarui} diperbarui.";
        if (! empty($this->kelasBaru)) {
            $daftar = implode(', ', array_unique($this->kelasBaru));
            $teks .= ' '.count(array_unique($this->kelasBaru))." kelas baru otomatis dibuat ({$daftar}).";
        }

        return $teks;
    }
}
