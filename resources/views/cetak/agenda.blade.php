<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Agenda Mengajar</title>
    <style>
        @page { margin: 15mm 12mm; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 9px; color: #111; }
        table.data { width: 100%; border-collapse: collapse; }
        table.data th, table.data td { border: 1px solid #999; padding: 3px 5px; vertical-align: top; }
        table.data th { background: #E8EEF6; font-size: 8px; text-transform: uppercase; }
        .kosong { text-align: center; color: #888; font-style: italic; padding: 16px; }
    </style>
</head>
<body>
@php use App\Support\Tanggal; @endphp

<x-cetak.kop :pengaturan="$pengaturan" judul="Agenda Mengajar"
             :subjudul="$guru->name
                .($filter['dari'] ? ' | '.Tanggal::pendek($filter['dari']).' – '.Tanggal::pendek($filter['sampai'] ?? now()) : '')"/>

<table class="data">
    <thead>
        <tr>
            <th style="width:22px;">No</th>
            <th style="width:60px;">Tanggal</th>
            <th style="width:70px;">Kelas</th>
            <th style="width:26px;">JP</th>
            <th style="width:30px;">Pert.</th>
            <th style="width:130px;">Materi</th>
            <th>Uraian Kegiatan</th>
            <th style="width:65px;">Metode</th>
            <th style="width:60px;">Status</th>
        </tr>
    </thead>
    <tbody>
        @forelse ($daftar as $i => $agenda)
            <tr>
                <td style="text-align:center;">{{ $i + 1 }}</td>
                <td>{{ Tanggal::angka($agenda->tanggal) }}</td>
                <td>{{ $agenda->jadwal?->kelas_tampilan ?? '—' }}</td>
                <td style="text-align:center;">{{ $agenda->jadwal?->jam_ke ?? '—' }}</td>
                <td style="text-align:center;">{{ $agenda->pertemuan_ke }}</td>
                <td>{{ $agenda->judul_materi }}</td>
                <td>{{ $agenda->uraian_kegiatan ?? '—' }}</td>
                <td>{{ $agenda->metode ?? '—' }}</td>
                <td>{{ $agenda->status->label() }}</td>
            </tr>
        @empty
            <tr><td colspan="9" class="kosong">Tidak ada agenda pada rentang yang dipilih.</td></tr>
        @endforelse
    </tbody>
</table>

<x-cetak.tanda-tangan :pengaturan="$pengaturan" :guru="$guru"/>
</body>
</html>
