@props(['pengaturan', 'guru', 'kota' => 'Ciruas'])

@php use App\Support\Tanggal; @endphp

<table style="width:100%; margin-top:24px; font-size:10px;">
    <tr>
        <td style="width:50%; text-align:center;">
            <div>Mengetahui,</div>
            <div>Kepala Sekolah</div>
            <div style="height:56px;"></div>
            <div style="font-weight:bold; text-decoration:underline;">
                {{ $pengaturan['kepala_sekolah'] ?: '.................................' }}
            </div>
            <div>NIP. {{ $pengaturan['nip_kepala_sekolah'] ?: '.........................' }}</div>
        </td>
        <td style="width:50%; text-align:center;">
            <div>{{ $kota }}, {{ Tanggal::pendek(now()) }}</div>
            <div>Guru Mata Pelajaran</div>
            <div style="height:56px;"></div>
            <div style="font-weight:bold; text-decoration:underline;">{{ $guru->name }}</div>
            <div>NIP. {{ $guru->nip ?: '.........................' }}</div>
        </td>
    </tr>
</table>
