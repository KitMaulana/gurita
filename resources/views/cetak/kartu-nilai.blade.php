<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Kartu Nilai</title>
    <style>
        @page { margin: 18mm 15mm; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 10px; color: #111; }
        table.identitas { width: 100%; margin-bottom: 12px; font-size: 10px; }
        table.identitas td { padding: 2px 0; }
        table.data { width: 100%; border-collapse: collapse; }
        table.data th, table.data td { border: 1px solid #999; padding: 5px 7px; }
        table.data th { background: #E8EEF6; font-size: 9px; text-transform: uppercase; }
        .angka { text-align: center; }
        .akhir { background: #EEF3F9; font-weight: bold; font-size: 12px; }
        .kotak { border: 1px solid #999; padding: 8px; margin-top: 10px; }
    </style>
</head>
<body>
@php
    // Pakai objek siswa dari baris rekap — hanya objek itu yang membawa pivot no_absen.
    $siswa = $baris['siswa'];
    $n = $baris['nilai'];
    $p = $baris['presensi'];
@endphp

<x-cetak.kop :pengaturan="$pengaturan" judul="Kartu Hasil Belajar"
             :subjudul="$kelas->tahunAjaran->label"/>

<table class="identitas">
    <tr>
        <td style="width:110px;">Nama Siswa</td><td style="width:8px;">:</td>
        <td style="font-weight:bold;">{{ $siswa->nama }}</td>
        <td style="width:80px;">Kelas</td><td style="width:8px;">:</td>
        <td>{{ $kelas->nama }}</td>
    </tr>
    <tr>
        <td>NISN</td><td>:</td><td>{{ $siswa->nisn }}</td>
        <td>Mata Pelajaran</td><td>:</td><td>{{ $mataPelajaran?->nama }}</td>
    </tr>
    <tr>
        <td>No. Absen</td><td>:</td><td>{{ $siswa->pivot->no_absen }}</td>
        <td>KKTP</td><td>:</td><td>{{ $kktp }}</td>
    </tr>
</table>

<table class="data">
    <thead>
        <tr>
            <th>Komponen Penilaian</th>
            <th style="width:80px;">Nilai</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>Rata-rata Formatif</td>
            <td class="angka">{{ $n['formatif'] !== null ? number_format($n['formatif'], 1, ',', '') : '—' }}</td>
        </tr>
        <tr>
            <td>Rata-rata Sumatif Lingkup Materi</td>
            <td class="angka">{{ $n['sumatif_lingkup'] !== null ? number_format($n['sumatif_lingkup'], 1, ',', '') : '—' }}</td>
        </tr>
        <tr>
            <td>Sumatif Akhir Semester</td>
            <td class="angka">{{ $n['sumatif_akhir'] !== null ? number_format($n['sumatif_akhir'], 1, ',', '') : '—' }}</td>
        </tr>
        <tr>
            <td class="akhir">NILAI AKHIR &nbsp; (Predikat {{ $n['predikat'] }} — {{ $n['is_tuntas'] ? 'Tuntas' : 'Belum Tuntas' }})</td>
            <td class="angka akhir">{{ round($n['nilai_akhir']) }}</td>
        </tr>
    </tbody>
</table>

<table class="data" style="margin-top:10px;">
    <thead>
        <tr>
            <th>Kehadiran</th>
            <th style="width:60px;">Hadir</th>
            <th style="width:60px;">Sakit</th>
            <th style="width:60px;">Izin</th>
            <th style="width:60px;">Alfa</th>
            <th style="width:80px;">Persentase</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>Dari {{ $p['pertemuan'] }} pertemuan efektif</td>
            <td class="angka">{{ $p['hadir'] + $p['dispensasi'] }}</td>
            <td class="angka">{{ $p['sakit'] }}</td>
            <td class="angka">{{ $p['izin'] }}</td>
            <td class="angka">{{ $p['alfa'] + $p['bolos'] }}</td>
            <td class="angka">{{ number_format($p['persentase'], 1, ',', '') }}%</td>
        </tr>
    </tbody>
</table>

<div class="kotak">
    <div style="font-weight:bold; margin-bottom:4px;">Deskripsi Capaian</div>
    <div style="line-height:1.5;">{{ $baris['rapor']?->deskripsi_capaian ?: $baris['deskripsi'] }}</div>
</div>

<x-cetak.tanda-tangan :pengaturan="$pengaturan" :guru="$guru"/>
</body>
</html>
