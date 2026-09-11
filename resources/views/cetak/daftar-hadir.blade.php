<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Daftar Hadir</title>
    <style>
        @page { margin: 14mm 10mm; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 8px; color: #111; }
        table.data { width: 100%; border-collapse: collapse; }
        table.data th, table.data td { border: 1px solid #999; padding: 2px 3px; }
        table.data th { background: #E8EEF6; font-size: 7px; }
        td.sel { text-align: center; width: 14px; font-weight: bold; }
        .nama { text-align: left; white-space: nowrap; }
        .kosong { text-align: center; color: #888; font-style: italic; padding: 16px; }
        .ket { margin-top: 8px; font-size: 8px; }
    </style>
</head>
<body>
@php use App\Support\Tanggal; @endphp

<x-cetak.kop :pengaturan="$pengaturan" judul="Daftar Hadir Siswa"
             :subjudul="$kelas->nama.' — '.($mataPelajaran?->nama ?? '').' | '
                .Tanggal::pendek($dari).' – '.Tanggal::pendek($sampai)"/>

@if ($matriks['tanggal']->isEmpty())
    <p class="kosong">Belum ada pertemuan pada rentang tanggal ini.</p>
@else
    <table class="data">
        <thead>
            <tr>
                <th style="width:22px;">No</th>
                <th class="nama">Nama Siswa</th>
                @foreach ($matriks['tanggal'] as $agenda)
                    <th style="width:14px;">{{ $agenda->tanggal->format('d/m') }}</th>
                @endforeach
                <th style="width:18px;">H</th>
                <th style="width:18px;">S</th>
                <th style="width:18px;">I</th>
                <th style="width:18px;">A</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($matriks['baris'] as $i => $baris)
                @php
                    $hitung = ['H' => 0, 'S' => 0, 'I' => 0, 'A' => 0];
                    foreach ($baris['sel'] as $huruf) {
                        if ($huruf === 'H' || $huruf === 'D') $hitung['H']++;
                        elseif ($huruf === 'S') $hitung['S']++;
                        elseif ($huruf === 'I') $hitung['I']++;
                        elseif ($huruf === 'A' || $huruf === 'B') $hitung['A']++;
                    }
                @endphp
                <tr>
                    <td style="text-align:center;">{{ $baris['siswa']->pivot->no_absen }}</td>
                    <td class="nama">{{ $baris['siswa']->nama }}</td>
                    @foreach ($baris['sel'] as $huruf)
                        <td class="sel">{{ $huruf }}</td>
                    @endforeach
                    <td class="sel">{{ $hitung['H'] }}</td>
                    <td class="sel">{{ $hitung['S'] }}</td>
                    <td class="sel">{{ $hitung['I'] }}</td>
                    <td class="sel">{{ $hitung['A'] }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <p class="ket">
        Keterangan: H = Hadir, S = Sakit, I = Izin, A = Alfa, B = Bolos, D = Dispensasi (dihitung hadir).
    </p>
@endif

<x-cetak.tanda-tangan :pengaturan="$pengaturan" :guru="$guru"/>
</body>
</html>
