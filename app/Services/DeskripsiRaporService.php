<?php

namespace App\Services;

use App\Models\Kelas;
use App\Models\Penilaian;
use App\Models\Siswa;
use Illuminate\Support\Collection;

/**
 * Deskripsi capaian naratif berbasis Tujuan Pembelajaran (CLAUDE.md §8.4).
 * Hasilnya selalu bisa disunting guru sebelum rapor dibekukan.
 */
class DeskripsiRaporService
{
    public function __construct(protected PerhitunganNilaiService $perhitungan) {}

    /**
     * Susun deskripsi untuk satu siswa.
     *
     * @param  Collection<int, Penilaian>  $penilaians  penilaian kelas+mapel, sudah eager load nilais & tujuanPembelajaran
     */
    public function untukSiswa(Siswa $siswa, Collection $penilaians, string $predikat): string
    {
        $capaian = $this->capaianPerTujuan($penilaians, $siswa->id);
        $namaDepan = $this->namaDepan($siswa->nama);
        $mutu = $this->perhitungan->labelPredikat($predikat);

        if ($capaian->isEmpty()) {
            return "Ananda {$namaDepan} menunjukkan capaian {$mutu} pada mata pelajaran ini.";
        }

        $tertinggi = $capaian->sortByDesc('rata')->first();
        $terendah = $capaian->sortBy('rata')->first();

        $kalimat = "Ananda {$namaDepan} menunjukkan penguasaan {$mutu} pada "
            .$this->rapikan($tertinggi['deskripsi']).'.';

        if ($capaian->count() > 1 && $terendah['rata'] < $tertinggi['rata']) {
            $kalimat .= ' Perlu peningkatan pada '.$this->rapikan($terendah['deskripsi']).'.';
        }

        return $kalimat;
    }

    /**
     * Deskripsi untuk seluruh siswa satu kelas sekaligus.
     *
     * @param  array<int,string>  $predikatPerSiswa  siswa_id => predikat
     * @return array<int,string> siswa_id => deskripsi
     */
    public function untukKelas(Kelas $kelas, Collection $penilaians, array $predikatPerSiswa): array
    {
        return $kelas->siswas()->get()
            ->mapWithKeys(fn (Siswa $siswa) => [
                $siswa->id => $this->untukSiswa(
                    $siswa,
                    $penilaians,
                    $predikatPerSiswa[$siswa->id] ?? 'C'
                ),
            ])
            ->all();
    }

    /**
     * Rata-rata nilai siswa dikelompokkan per Tujuan Pembelajaran (fallback: per Bab).
     *
     * @return Collection<int, array{deskripsi:string, rata:float}>
     */
    protected function capaianPerTujuan(Collection $penilaians, int $siswaId): Collection
    {
        return $penilaians
            ->filter(fn (Penilaian $p) => $p->tujuanPembelajaran || $p->bab)
            ->map(function (Penilaian $p) use ($siswaId) {
                $nilai = $p->nilais->firstWhere('siswa_id', $siswaId)?->nilai;

                if ($nilai === null) {
                    return null;
                }

                return [
                    'kunci' => $p->tujuan_pembelajaran_id
                        ? 'tp-'.$p->tujuan_pembelajaran_id
                        : 'bab-'.$p->bab_id,
                    'deskripsi' => $p->tujuanPembelajaran?->deskripsi ?? $p->bab?->judul ?? $p->nama,
                    'nilai' => (float) $nilai,
                ];
            })
            ->filter()
            ->groupBy('kunci')
            ->map(fn (Collection $grup) => [
                'deskripsi' => $grup->first()['deskripsi'],
                'rata' => round($grup->avg('nilai'), 2),
            ])
            ->values();
    }

    protected function namaDepan(string $nama): string
    {
        return explode(' ', trim($nama))[0];
    }

    /** Turunkan huruf kapital awal agar menyatu di tengah kalimat. */
    protected function rapikan(string $teks): string
    {
        $teks = rtrim(trim($teks), '.');

        return mb_strtolower(mb_substr($teks, 0, 1)).mb_substr($teks, 1);
    }
}
