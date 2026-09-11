<?php

namespace App\Livewire;

use App\Models\Kelas;
use App\Models\Kktp;
use App\Models\Nilai;
use App\Models\Penilaian;
use App\Services\PerhitunganNilaiService;
use Illuminate\Support\Collection;
use Livewire\Attributes\Locked;
use Livewire\Component;

/**
 * Grid nilai: baris = siswa, kolom = penilaian.
 * Setiap sel disimpan otomatis saat kehilangan fokus, dengan indikator "tersimpan".
 */
class TabelNilai extends Component
{
    #[Locked]
    public int $kelasId;

    #[Locked]
    public int $mataPelajaranId;

    /** @var array<int,array<int,string|null>> nilai[siswaId][penilaianId] */
    public array $nilai = [];

    public ?string $selTersimpan = null;

    public function mount(int $kelasId, int $mataPelajaranId): void
    {
        $this->kelasId = $kelasId;
        $this->mataPelajaranId = $mataPelajaranId;

        foreach ($this->penilaians() as $penilaian) {
            foreach ($penilaian->nilais as $baris) {
                $this->nilai[$baris->siswa_id][$penilaian->id] = $baris->nilai !== null
                    ? rtrim(rtrim((string) $baris->nilai, '0'), '.')
                    : null;
            }
        }
    }

    public function simpanSel(int $siswaId, int $penilaianId): void
    {
        $penilaian = $this->penilaians()->firstWhere('id', $penilaianId);

        if (! $penilaian) {
            return;
        }

        if (! auth()->user()->can('update', $penilaian)) {
            $this->addError('nilai', 'Penilaian ini terkunci atau bukan milik Anda.');

            return;
        }

        $mentah = $this->nilai[$siswaId][$penilaianId] ?? null;
        $bersih = is_string($mentah) ? str_replace(',', '.', trim($mentah)) : $mentah;

        if ($bersih === '' || $bersih === null) {
            $bersih = null;
        } else {
            if (! is_numeric($bersih) || $bersih < 0 || $bersih > $penilaian->nilai_maksimal) {
                $this->addError(
                    "nilai.{$siswaId}.{$penilaianId}",
                    "Nilai harus antara 0 dan {$penilaian->nilai_maksimal}."
                );

                return;
            }

            $bersih = round((float) $bersih, 2);
        }

        $this->resetErrorBag("nilai.{$siswaId}.{$penilaianId}");

        Nilai::updateOrCreate(
            ['penilaian_id' => $penilaianId, 'siswa_id' => $siswaId],
            ['nilai' => $bersih]
        );

        $this->selTersimpan = "{$siswaId}-{$penilaianId}";
        $this->penilaianCache = null;   // muat ulang agar kolom kalkulasi ikut berubah
    }

    /** @var Collection<int,Penilaian>|null */
    protected ?Collection $penilaianCache = null;

    /** @return Collection<int,Penilaian> */
    public function penilaians(): Collection
    {
        return $this->penilaianCache ??= Penilaian::query()
            ->tahunAktif()
            ->where('kelas_id', $this->kelasId)
            ->where('mata_pelajaran_id', $this->mataPelajaranId)
            ->with(['nilais', 'bab', 'tujuanPembelajaran', 'kelas'])
            ->orderBy('jenis')
            ->orderBy('tanggal')
            ->get();
    }

    public function render()
    {
        $kelas = Kelas::with('siswas')->findOrFail($this->kelasId);
        $penilaians = $this->penilaians();
        $perhitungan = app(PerhitunganNilaiService::class);
        $kktp = Kktp::untuk($this->mataPelajaranId, $kelas->tingkat, $kelas->tahun_ajaran_id);

        $rekap = $kelas->siswas->mapWithKeys(fn ($siswa) => [
            $siswa->id => $perhitungan->hitungSiswa($siswa->id, $penilaians, $kktp),
        ]);

        return view('livewire.tabel-nilai', [
            'kelas' => $kelas,
            'siswas' => $kelas->siswas,
            'penilaians' => $penilaians,
            'rekap' => $rekap,
            'kktp' => $kktp,
        ]);
    }
}
