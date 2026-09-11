<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Daftar Nilai</title>
    <style>
        @page { margin: 14mm 10mm; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 8px; color: #111; }
        table.data { width: 100%; border-collapse: collapse; }
        table.data th, table.data td { border: 1px solid #999; padding: 2px 3px; }
        table.data th { background: #E8EEF6; font-size: 7px; }
        .angka { text-align: center; }
        .nama { text-align: left; white-space: nowrap; }
        .merah { background: #FDE8E8; font-weight: bold; }
        .akhir { background: #EEF3F9; font-weight: bold; }
    </style>
</head>
<body>
<x-cetak.kop :pengaturan="$pengaturan" judul="Daftar Nilai"
             :subjudul="$kelas->nama.' — '.($mataPelajaran?->nama ?? '').' | KKTP '.$kktp"/>

<table class="data">
    <thead>
        <tr>
            <th style="width:20px;">No</th>
            <th class="nama">Nama Siswa</th>
            @foreach ($penilaians as $penilaian)
                <th style="width:26px;">{{ Str::limit($penilaian->nama, 12) }}</th>
            @endforeach
            <th style="width:26px;">Rata F</th>
            <th style="width:26px;">Rata SL</th>
            <th style="width:24px;">SA</th>
            <th style="width:28px;">NA</th>
            <th style="width:20px;">Pred</th>
            <th style="width:38px;">Status</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($siswas as $siswa)
            @php $r = $perhitungan->hitungSiswa($siswa->id, $penilaians, $kktp); @endphp
            <tr>
                <td class="angka">{{ $siswa->pivot->no_absen }}</td>
                <td class="nama">{{ $siswa->nama }}</td>
                @foreach ($penilaians as $penilaian)
                    @php $n = $penilaian->nilais->firstWhere('siswa_id', $siswa->id)?->nilai; @endphp
                    <td class="angka {{ $n !== null && $n < $kktp ? 'merah' : '' }}">
                        {{ $n !== null ? rtrim(rtrim(number_format($n, 2, ',', ''), '0'), ',') : '—' }}
                    </td>
                @endforeach
                <td class="angka">{{ $r['formatif'] !== null ? number_format($r['formatif'], 1, ',', '') : '—' }}</td>
                <td class="angka">{{ $r['sumatif_lingkup'] !== null ? number_format($r['sumatif_lingkup'], 1, ',', '') : '—' }}</td>
                <td class="angka">{{ $r['sumatif_akhir'] !== null ? number_format($r['sumatif_akhir'], 1, ',', '') : '—' }}</td>
                <td class="angka akhir">{{ number_format($r['nilai_akhir'], 1, ',', '') }}</td>
                <td class="angka akhir">{{ $r['predikat'] }}</td>
                <td class="angka">{{ $r['is_tuntas'] ? 'Tuntas' : 'Belum' }}</td>
            </tr>
        @endforeach
    </tbody>
</table>

<p style="margin-top:8px; font-size:8px;">
    Keterangan: F = Formatif, SL = Sumatif Lingkup Materi, SA = Sumatif Akhir Semester, NA = Nilai Akhir.
    Sel berlatar merah menandakan nilai di bawah KKTP.
</p>

<x-cetak.tanda-tangan :pengaturan="$pengaturan" :guru="$guru"/>
</body>
</html>
