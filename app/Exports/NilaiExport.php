<?php

namespace App\Exports;

use App\Models\Kelas;
use App\Models\Kktp;
use App\Models\Penilaian;
use App\Services\PerhitunganNilaiService;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;

class NilaiExport implements FromCollection, ShouldAutoSize, WithHeadings, WithTitle
{
    protected Collection $penilaians;

    public function __construct(protected Kelas $kelas, protected int $mataPelajaranId)
    {
        $this->penilaians = Penilaian::query()
            ->where('tahun_ajaran_id', $kelas->tahun_ajaran_id)
            ->where('kelas_id', $kelas->id)
            ->where('mata_pelajaran_id', $mataPelajaranId)
            ->with('nilais')
            ->orderBy('jenis')->orderBy('tanggal')
            ->get();
    }

    public function collection(): Collection
    {
        $perhitungan = app(PerhitunganNilaiService::class);
        $kktp = Kktp::untuk($this->mataPelajaranId, $this->kelas->tingkat, $this->kelas->tahun_ajaran_id);

        return $this->kelas->siswas()->get()->map(function ($siswa) use ($perhitungan, $kktp) {
            $baris = [
                $siswa->pivot->no_absen,
                $siswa->nisn,
                $siswa->nama,
            ];

            foreach ($this->penilaians as $penilaian) {
                $baris[] = $penilaian->nilais->firstWhere('siswa_id', $siswa->id)?->nilai;
            }

            $rekap = $perhitungan->hitungSiswa($siswa->id, $this->penilaians, $kktp);

            return array_merge($baris, [
                $rekap['formatif'],
                $rekap['sumatif_lingkup'],
                $rekap['sumatif_akhir'],
                $rekap['nilai_akhir'],
                $rekap['predikat'],
                $rekap['is_tuntas'] ? 'Tuntas' : 'Belum Tuntas',
            ]);
        });
    }

    public function headings(): array
    {
        $kepala = ['No. Absen', 'NISN', 'Nama Siswa'];

        foreach ($this->penilaians as $penilaian) {
            $kepala[] = $penilaian->jenis->labelPendek().' — '.$penilaian->nama;
        }

        return array_merge($kepala, [
            'Rata Formatif', 'Rata Sumatif Lingkup', 'Sumatif Akhir',
            'Nilai Akhir', 'Predikat', 'Status',
        ]);
    }

    public function title(): string
    {
        return 'Daftar Nilai '.$this->kelas->nama;
    }
}
