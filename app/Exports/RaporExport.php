<?php

namespace App\Exports;

use App\Models\Kelas;
use App\Models\MataPelajaran;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;

/** Leger rapor — format datar agar mudah diunggah ke e-Rapor. */
class RaporExport implements FromCollection, ShouldAutoSize, WithHeadings, WithTitle
{
    public function __construct(
        protected Collection $baris,
        protected Kelas $kelas,
        protected ?MataPelajaran $mataPelajaran,
    ) {}

    public function collection(): Collection
    {
        return $this->baris->map(fn (array $b) => [
            $b['siswa']->pivot->no_absen ?? '',
            $b['siswa']->nisn,
            $b['siswa']->nama,
            $b['nilai']['formatif'],
            $b['nilai']['sumatif_lingkup'],
            $b['nilai']['sumatif_akhir'],
            round($b['nilai']['nilai_akhir']),
            $b['nilai']['predikat'],
            $b['nilai']['is_tuntas'] ? 'Tuntas' : 'Belum Tuntas',
            $b['presensi']['hadir'] + $b['presensi']['dispensasi'],
            $b['presensi']['sakit'],
            $b['presensi']['izin'],
            $b['presensi']['alfa'] + $b['presensi']['bolos'],
            $b['rapor']?->deskripsi_capaian ?: $b['deskripsi'],
        ]);
    }

    public function headings(): array
    {
        return [
            'No. Absen', 'NISN', 'Nama Siswa',
            'Formatif', 'Sumatif Lingkup', 'Sumatif Akhir',
            'Nilai Akhir', 'Predikat', 'Status Ketuntasan',
            'Hadir', 'Sakit', 'Izin', 'Alfa',
            'Deskripsi Capaian',
        ];
    }

    public function title(): string
    {
        return 'Leger '.$this->kelas->nama.' '.($this->mataPelajaran?->singkatan ?? '');
    }
}
