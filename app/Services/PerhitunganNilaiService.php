<?php

namespace App\Services;

use App\Enums\JenisPenilaian;
use App\Models\Kelas;
use App\Models\Kktp;
use App\Models\Nilai;
use App\Models\Penilaian;
use App\Models\Pengaturan;
use Illuminate\Support\Collection;

/**
 * Perhitungan nilai akhir sesuai CLAUDE.md §8.1 & §8.2.
 *
 * Komponen yang belum punya nilai dikeluarkan dari perhitungan dan bobotnya
 * dinormalisasi ulang, supaya nilai tidak anjlok di tengah semester.
 */
class PerhitunganNilaiService
{
    /**
     * Rekap satu kelas + mapel.
     *
     * @return Collection<int, array{
     *     siswa_id:int, formatif:?float, sumatif_lingkup:?float, sumatif_akhir:?float,
     *     nilai_akhir:float, predikat:string, is_tuntas:bool, is_sementara:bool, kktp:int
     * }>
     */
    public function rekapKelas(Kelas $kelas, int $mataPelajaranId, ?int $tahunAjaranId = null): Collection
    {
        $tahunAjaranId ??= $kelas->tahun_ajaran_id;

        $penilaians = Penilaian::query()
            ->where('tahun_ajaran_id', $tahunAjaranId)
            ->where('kelas_id', $kelas->id)
            ->where('mata_pelajaran_id', $mataPelajaranId)
            ->with(['nilais' => fn ($q) => $q->whereNotNull('nilai')])
            ->orderBy('tanggal')
            ->get();

        $kktp = Kktp::untuk($mataPelajaranId, $kelas->tingkat, $tahunAjaranId);
        $siswas = $kelas->siswas()->get();

        return $siswas->map(function ($siswa) use ($penilaians, $kktp) {
            return $this->hitungSiswa($siswa->id, $penilaians, $kktp);
        })->values();
    }

    /**
     * Hitung nilai akhir satu siswa dari kumpulan penilaian yang sudah di-eager load.
     *
     * @param  Collection<int, Penilaian>  $penilaians
     */
    public function hitungSiswa(int $siswaId, Collection $penilaians, int $kktp): array
    {
        $formatif = $this->rataJenis($penilaians, JenisPenilaian::Formatif, $siswaId);
        $sumatifLingkup = $this->rataJenis($penilaians, JenisPenilaian::SumatifLingkup, $siswaId);
        $sumatifAkhir = $this->nilaiSumatifAkhir($penilaians, $siswaId);

        $komponen = [
            JenisPenilaian::Formatif->value => $formatif,
            JenisPenilaian::SumatifLingkup->value => $sumatifLingkup,
            JenisPenilaian::SumatifAkhir->value => $sumatifAkhir,
        ];

        $bobot = $this->bobot();
        $totalBobotTerpakai = 0.0;
        $totalNilai = 0.0;

        foreach ($komponen as $jenis => $nilai) {
            if ($nilai === null) {
                continue;   // komponen belum ada → dikeluarkan, bobot dinormalisasi
            }
            $totalNilai += $nilai * $bobot[$jenis];
            $totalBobotTerpakai += $bobot[$jenis];
        }

        $nilaiAkhir = $totalBobotTerpakai > 0
            ? round($totalNilai / $totalBobotTerpakai, 2)
            : 0.0;

        $adaKomponenKosong = in_array(null, $komponen, true);

        return [
            'siswa_id' => $siswaId,
            'formatif' => $formatif,
            'sumatif_lingkup' => $sumatifLingkup,
            'sumatif_akhir' => $sumatifAkhir,
            'nilai_akhir' => $nilaiAkhir,
            'predikat' => $this->predikat($nilaiAkhir),
            'is_tuntas' => $nilaiAkhir >= $kktp,
            'is_sementara' => $adaKomponenKosong,
            'kktp' => $kktp,
        ];
    }

    /**
     * Rata-rata satu jenis penilaian. Untuk penilaian remedial, nilai yang dipakai
     * adalah nilai tertinggi antara asli & remedial, dibatasi maksimal KKTP (§7.5).
     *
     * @param  Collection<int, Penilaian>  $penilaians
     */
    protected function rataJenis(Collection $penilaians, JenisPenilaian $jenis, int $siswaId): ?float
    {
        $nilai = $penilaians
            ->where('jenis', $jenis)
            ->map(fn (Penilaian $p) => $this->nilaiSiswa($p, $siswaId))
            ->filter(fn ($n) => $n !== null);

        return $nilai->isEmpty() ? null : round($nilai->avg(), 2);
    }

    /** Sumatif akhir: ambil penilaian terakhir yang sudah dinilai. */
    protected function nilaiSumatifAkhir(Collection $penilaians, int $siswaId): ?float
    {
        return $penilaians
            ->where('jenis', JenisPenilaian::SumatifAkhir)
            ->sortByDesc('tanggal')
            ->map(fn (Penilaian $p) => $this->nilaiSiswa($p, $siswaId))
            ->filter(fn ($n) => $n !== null)
            ->first();
    }

    /** Nilai siswa pada satu penilaian, sudah diskalakan ke 0–100. */
    protected function nilaiSiswa(Penilaian $penilaian, int $siswaId): ?float
    {
        /** @var Nilai|null $baris */
        $baris = $penilaian->nilais->firstWhere('siswa_id', $siswaId);

        if (! $baris || $baris->nilai === null) {
            return null;
        }

        $nilai = (float) $baris->nilai;

        if ($penilaian->nilai_maksimal > 0 && $penilaian->nilai_maksimal != 100) {
            $nilai = $nilai / $penilaian->nilai_maksimal * 100;
        }

        // Nilai remedial dibatasi maksimal KKTP bila pengaturan mengaktifkannya.
        if ($penilaian->is_remedial && Pengaturan::ambil('remedial_dibatasi_kktp') == '1') {
            $kktp = Kktp::untuk(
                $penilaian->mata_pelajaran_id,
                $penilaian->kelas?->tingkat ?? 'XII',
                $penilaian->tahun_ajaran_id
            );
            $nilai = min($nilai, $kktp);
        }

        return round($nilai, 2);
    }

    /** @return array<string,float> bobot per jenis (jumlah = 100) */
    public function bobot(): array
    {
        $bobot = [
            JenisPenilaian::Formatif->value => Pengaturan::angka('bobot_formatif'),
            JenisPenilaian::SumatifLingkup->value => Pengaturan::angka('bobot_sumatif_lingkup'),
            JenisPenilaian::SumatifAkhir->value => Pengaturan::angka('bobot_sumatif_akhir'),
        ];

        return array_sum($bobot) > 0 ? $bobot : [
            JenisPenilaian::Formatif->value => 20,
            JenisPenilaian::SumatifLingkup->value => 40,
            JenisPenilaian::SumatifAkhir->value => 40,
        ];
    }

    public function predikat(float $nilaiAkhir): string
    {
        return match (true) {
            $nilaiAkhir >= Pengaturan::angka('ambang_predikat_a') => 'A',
            $nilaiAkhir >= Pengaturan::angka('ambang_predikat_b') => 'B',
            $nilaiAkhir >= Pengaturan::angka('ambang_predikat_c') => 'C',
            default => 'D',
        };
    }

    public function labelPredikat(string $predikat): string
    {
        return match ($predikat) {
            'A' => 'sangat baik',
            'B' => 'baik',
            'C' => 'cukup',
            default => 'perlu bimbingan',
        };
    }
}
