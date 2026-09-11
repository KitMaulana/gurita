<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Jadwal Mengajar</title>
    <style>
        @page { margin: 18mm 12mm; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 10px; color: #111; }
        table.data { width: 100%; border-collapse: collapse; }
        table.data th, table.data td { border: 1px solid #999; padding: 4px 6px; }
        table.data th { background: #E8EEF6; font-size: 9px; text-transform: uppercase; }
        .hari { background: #1A365D; color: #fff; font-weight: bold; }
        .kosong { text-align: center; color: #888; font-style: italic; }
    </style>
</head>
<body>
<x-cetak.kop :pengaturan="$pengaturan" judul="Jadwal Mengajar"
             :subjudul="$guru->name.($guru->nip ? ' — NIP '.$guru->nip : '').' | '.$tahunAjaran?->label.(\App\Support\JamPelajaran::isModeRamadhan() ? ' (Mode Bulan Ramadhan)' : '')"/>

<table class="data">
    <thead>
        <tr>
            <th style="width:70px;">Hari</th>
            <th style="width:40px;">JP</th>
            <th style="width:90px;">Waktu</th>
            <th style="width:110px;">Kelas</th>
            <th>Mata Pelajaran</th>
            <th style="width:80px;">Ruang</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($jadwalPerHari as $nilaiHari => $daftar)
            @if ($daftar->isEmpty())
                <tr>
                    <td class="hari">{{ ucfirst($nilaiHari) }}</td>
                    <td colspan="5" class="kosong">Tidak ada jadwal</td>
                </tr>
            @else
                @foreach ($daftar as $i => $jadwal)
                    <tr>
                        @if ($i === 0)
                            <td class="hari" rowspan="{{ $daftar->count() }}">{{ $jadwal->hari->label() }}</td>
                        @endif
                        <td style="text-align:center;">{{ $jadwal->jam_ke }}</td>
                        <td style="text-align:center;">{{ $jadwal->jam }}</td>
                        <td>{{ $jadwal->kelas_tampilan }}</td>
                        <td>{{ $jadwal->nama_tampilan }}</td>
                        <td style="text-align:center;">{{ $jadwal->ruang ?? '—' }}</td>
                    </tr>
                @endforeach
            @endif
        @endforeach
    </tbody>
</table>

<x-cetak.tanda-tangan :pengaturan="$pengaturan" :guru="$guru"/>
</body>
</html>
