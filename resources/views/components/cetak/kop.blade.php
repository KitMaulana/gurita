@props(['pengaturan', 'judul', 'subjudul' => null])

<table style="width:100%; border-collapse:collapse; margin-bottom:6px;">
    <tr>
        @if (!empty($pengaturan['logo']) && file_exists(storage_path('app/public/'.$pengaturan['logo'])))
            <td style="width:70px; vertical-align:middle;">
                <img src="{{ storage_path('app/public/'.$pengaturan['logo']) }}" style="height:60px;">
            </td>
        @endif
        <td style="text-align:center; vertical-align:middle;">
            <div style="font-size:15px; font-weight:bold; letter-spacing:.5px;">
                {{ strtoupper($pengaturan['nama_sekolah']) }}
            </div>
            @if (!empty($pengaturan['npsn']))
                <div style="font-size:9px;">NPSN {{ $pengaturan['npsn'] }}</div>
            @endif
            @if (!empty($pengaturan['alamat_sekolah']))
                <div style="font-size:9px;">{{ $pengaturan['alamat_sekolah'] }}</div>
            @endif
        </td>
    </tr>
</table>

<hr style="border:none; border-top:2px solid #1A365D; margin:0 0 10px;">

<div style="text-align:center; margin-bottom:10px;">
    <div style="font-size:13px; font-weight:bold; text-transform:uppercase;">{{ $judul }}</div>
    @if ($subjudul)
        <div style="font-size:10px;">{{ $subjudul }}</div>
    @endif
</div>
