<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Leger Nilai</title>
    <style>
        @page { margin: 14mm 10mm; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 8px; color: #111; }
        table.data { width: 100%; border-collapse: collapse; }
        table.data th, table.data td { border: 1px solid #999; padding: 2px 4px; vertical-align: top; }
        table.data th { background: #E8EEF6; font-size: 7px; }
        .angka { text-align: center; }
        .nama { text-align: left; white-space: nowrap; }
        .akhir { background: #EEF3F9; font-weight: bold; }
    </style>
</head>
<body>
<x-cetak.kop :pengaturan="$pengaturan" judul="Leger Nilai"
             :subjudul="$kelas->nama.' — '.($mataPelajaran?->nama ?? '')
                .' | '.$kelas->tahunAjaran->label.' | KKTP '.$kktp"/>

<table class="data">
    <thead>
        <tr>
            <th style="width:20px;">No</th>
            <th style="width:60px;">NISN</th>
            <th class="nama">Nama Siswa</th>
            <th style="width:24px;">F</th>
            <th style="width:24px;">SL</th>
            <th style="width:24px;">SA</th>
            <th style="width:26px;">NA</th>
            <th style="width:20px;">P</th>
            <th style="width:36px;">Status</th>
            <th style="width:18px;">H</th>
            <th style="width:18px;">S</th>
            <th style="width:18px;">I</th>
            <th style="width:18px;">A</th>
            <th>Deskripsi Capaian</th>
        </tr>
    </thead>
    <tbody>
        @forelse ($baris as $b)
            @php $n = $b['nilai']; $p = $b['presensi']; @endphp
            <tr>
                <td class="angka">{{ $b['siswa']->pivot->no_absen }}</td>
                <td class="angka">{{ $b['siswa']->nisn }}</td>
                <td class="nama">{{ $b['siswa']->nama }}</td>
                <td class="angka">{{ $n['formatif'] !== null ? number_format($n['formatif'], 1, ',', '') : '—' }}</td>
                <td class="angka">{{ $n['sumatif_lingkup'] !== null ? number_format($n['sumatif_lingkup'], 1, ',', '') : '—' }}</td>
                <td class="angka">{{ $n['sumatif_akhir'] !== null ? number_format($n['sumatif_akhir'], 1, ',', '') : '—' }}</td>
                <td class="angka akhir">{{ round($n['nilai_akhir']) }}</td>
                <td class="angka akhir">{{ $n['predikat'] }}</td>
                <td class="angka">{{ $n['is_tuntas'] ? 'Tuntas' : 'Belum' }}</td>
                <td class="angka">{{ $p['hadir'] + $p['dispensasi'] }}</td>
                <td class="angka">{{ $p['sakit'] }}</td>
                <td class="angka">{{ $p['izin'] }}</td>
                <td class="angka">{{ $p['alfa'] + $p['bolos'] }}</td>
                <td>{{ $b['rapor']?->deskripsi_capaian ?: $b['deskripsi'] }}</td>
            </tr>
        @empty
            <tr><td colspan="14" style="text-align:center; padding:16px; font-style:italic;">Belum ada data.</td></tr>
        @endforelse
    </tbody>
</table>

<x-cetak.tanda-tangan :pengaturan="$pengaturan" :guru="$guru"/>
</body>
</html>
