@extends('layouts.app')
@section('judul', 'Rekap Kehadiran')

@php use App\Support\Tanggal; @endphp

@section('konten')
    <x-kepala-halaman judul="Rekap Kehadiran"
                      :keterangan="$kelas?->nama ? $kelas->nama.' · '.Tanggal::pendek($dari).' – '.Tanggal::pendek($sampai) : null">
        <x-slot:aksi>
            <x-tombol gaya="garis" ikon="arrow-down-tray" :href="route('presensi.ekspor', request()->query())">Excel</x-tombol>
            <x-tombol gaya="garis" ikon="printer" :href="route('presensi.cetak', request()->query())">Daftar Hadir (F4)</x-tombol>
        </x-slot:aksi>
    </x-kepala-halaman>

    <form method="GET" class="kartu tanpa-cetak mb-6 grid gap-3 p-4 sm:grid-cols-2 lg:grid-cols-5">
        <x-bidang label="Kelas" nama="kelas_id">
            <x-pilihan nama="kelas_id" :opsi="$kelasList->pluck('nama', 'id')" :terpilih="$kelas?->id"/>
        </x-bidang>
        <x-bidang label="Mata Pelajaran" nama="mata_pelajaran_id">
            <x-pilihan nama="mata_pelajaran_id" :opsi="$mapelList->pluck('nama', 'id')"
                       :terpilih="$mataPelajaranId" kosong="Semua mapel"/>
        </x-bidang>
        <x-bidang label="Dari" nama="dari">
            <x-isian nama="dari" tipe="date" :value="$dari->toDateString()"/>
        </x-bidang>
        <x-bidang label="Sampai" nama="sampai">
            <x-isian nama="sampai" tipe="date" :value="$sampai->toDateString()"/>
        </x-bidang>
        <div class="flex items-end">
            <x-tombol gaya="utama" ikon="funnel">Tampilkan</x-tombol>
        </div>
    </form>

    <x-tabel>
        <x-slot:kepala>
            <tr>
                <th class="px-4 py-3">No</th>
                <th class="px-4 py-3">Nama Siswa</th>
                <th class="px-3 py-3 text-center">H</th>
                <th class="px-3 py-3 text-center">S</th>
                <th class="px-3 py-3 text-center">I</th>
                <th class="px-3 py-3 text-center">A</th>
                <th class="px-3 py-3 text-center">B</th>
                <th class="px-3 py-3 text-center">D</th>
                <th class="px-4 py-3 text-center">Pertemuan</th>
                <th class="px-4 py-3 text-center">Kehadiran</th>
            </tr>
        </x-slot:kepala>

        @forelse ($rekap as $baris)
            @php
                $siswa = $siswas->get($baris['siswa_id']);
                $perluPerhatian = ($baris['alfa'] + $baris['bolos']) >= $ambangAlfa;
            @endphp
            <tr @class(['hover:bg-slate-50', 'bg-rose-50/60' => $perluPerhatian])>
                <td class="px-4 py-2.5 text-slate-500">{{ $siswa?->pivot->no_absen }}</td>
                <td class="px-4 py-2.5">
                    <p class="font-medium text-slate-800">{{ $siswa?->nama }}</p>
                    <p class="text-xs text-slate-400">{{ $siswa?->nisn }}</p>
                </td>
                <td class="px-3 py-2.5 text-center font-semibold text-emerald-700">{{ $baris['hadir'] }}</td>
                <td class="px-3 py-2.5 text-center text-amber-700">{{ $baris['sakit'] }}</td>
                <td class="px-3 py-2.5 text-center text-sky-700">{{ $baris['izin'] }}</td>
                <td @class(['px-3 py-2.5 text-center', 'nilai-merah' => $perluPerhatian, 'text-rose-700' => ! $perluPerhatian])>
                    {{ $baris['alfa'] }}
                </td>
                <td class="px-3 py-2.5 text-center text-red-800">{{ $baris['bolos'] }}</td>
                <td class="px-3 py-2.5 text-center text-violet-700">{{ $baris['dispensasi'] }}</td>
                <td class="px-4 py-2.5 text-center text-slate-500">{{ $baris['pertemuan'] }}</td>
                <td class="px-4 py-2.5 text-center">
                    <span @class([
                        'rounded-full px-2 py-0.5 text-xs font-bold',
                        'bg-emerald-100 text-emerald-800' => $baris['persentase'] >= 85,
                        'bg-amber-100 text-amber-800' => $baris['persentase'] >= 70 && $baris['persentase'] < 85,
                        'bg-rose-100 text-rose-800' => $baris['persentase'] < 70,
                    ])>{{ number_format($baris['persentase'], 1, ',', '.') }}%</span>
                </td>
            </tr>
        @empty
            <x-tabel.kosong :kolom="10" ikon="chart-bar"
                            judul="Belum ada data kehadiran"
                            pesan="Isi presensi pada pertemuan yang sudah terlaksana terlebih dahulu."/>
        @endforelse
    </x-tabel>

    <p class="mt-3 text-xs text-slate-500">
        Baris merah = siswa dengan alfa + bolos ≥ {{ $ambangAlfa }} kali. Dispensasi dihitung hadir;
        agenda berstatus libur/kegiatan sekolah tidak dihitung sebagai pertemuan.
    </p>
@endsection
