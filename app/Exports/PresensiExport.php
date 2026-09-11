<?php

namespace App\Exports;

use App\Models\Kelas;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;

class PresensiExport implements FromCollection, ShouldAutoSize, WithHeadings, WithTitle
{
    public function __construct(
        protected Kelas $kelas,
        protected Collection $rekap,
        protected Collection $siswas,
    ) {}

    public function collection(): Collection
    {
        return $this->rekap->map(function (array $baris) {
            $siswa = $this->siswas->get($baris['siswa_id']);

            return [
                $siswa?->pivot?->no_absen ?? '',
                $siswa?->nisn ?? '',
                $siswa?->nama ?? '',
                $baris['hadir'],
                $baris['sakit'],
                $baris['izin'],
                $baris['alfa'],
                $baris['bolos'],
                $baris['dispensasi'],
                $baris['pertemuan'],
                $baris['persentase'].'%',
            ];
        });
    }

    public function headings(): array
    {
        return [
            'No. Absen', 'NISN', 'Nama Siswa',
            'Hadir', 'Sakit', 'Izin', 'Alfa', 'Bolos', 'Dispensasi',
            'Jumlah Pertemuan', 'Persentase Kehadiran',
        ];
    }

    public function title(): string
    {
        return 'Rekap Presensi '.$this->kelas->nama;
    }
}
