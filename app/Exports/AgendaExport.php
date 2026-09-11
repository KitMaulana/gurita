<?php

namespace App\Exports;

use App\Models\Agenda;
use App\Support\Tanggal;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithTitle;

class AgendaExport implements FromCollection, ShouldAutoSize, WithHeadings, WithMapping, WithTitle
{
    public function __construct(protected Collection $agendas) {}

    public function collection(): Collection
    {
        return $this->agendas;
    }

    public function headings(): array
    {
        return [
            'Tanggal', 'Hari', 'Kelas', 'Mata Pelajaran', 'Jam Ke',
            'Pertemuan Ke', 'Judul Materi', 'Bab', 'Uraian Kegiatan',
            'Metode', 'Status', 'Catatan',
        ];
    }

    /** @param  Agenda  $agenda */
    public function map($agenda): array
    {
        return [
            Tanggal::angka($agenda->tanggal),
            Tanggal::namaHari($agenda->tanggal),
            $agenda->jadwal?->kelas?->nama ?? $agenda->jadwal?->kelas_tampilan ?? '—',
            $agenda->jadwal?->mataPelajaran?->nama ?? ($agenda->jadwal?->title ?: '—'),
            $agenda->jadwal?->jam_ke ?? '—',
            $agenda->pertemuan_ke,
            $agenda->judul_materi,
            $agenda->bab?->kode,
            $agenda->uraian_kegiatan,
            $agenda->metode,
            $agenda->status->label(),
            $agenda->catatan,
        ];
    }

    public function title(): string
    {
        return 'Agenda Mengajar';
    }
}
