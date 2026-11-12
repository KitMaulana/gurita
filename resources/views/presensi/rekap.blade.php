@extends('layouts.app')
@section('judul', 'Rekap Kehadiran')

@php
    use App\Support\Tanggal;
    $totalSiswaRekap = count($rekap);
    $rataPersen = $totalSiswaRekap > 0 ? collect($rekap)->avg('persentase') : 0;
    $jumlahOptimal = collect($rekap)->where('persentase', '>=', 85)->count();
    $jumlahRawan = collect($rekap)->filter(fn ($b) => ($b['alfa'] + $b['bolos']) >= $ambangAlfa)->count();
@endphp

@section('konten')
    <x-kepala-halaman judul="Rekap Kehadiran Siswa"
                      :keterangan="$kelas?->nama ? 'Kelas '.$kelas->nama.' · '.Tanggal::pendek($dari).' – '.Tanggal::pendek($sampai) : 'Pantau persentase kehadiran dan identifikasi siswa yang membutuhkan perhatian.'">
        <x-slot:aksi>
            <x-tombol gaya="garis" ikon="arrow-down-tray" :href="route('presensi.ekspor', request()->query())">Excel</x-tombol>
            <x-tombol gaya="garis" ikon="printer" :href="route('presensi.cetak', request()->query())">Daftar Hadir (F4)</x-tombol>
        </x-slot:aksi>
    </x-kepala-halaman>

    {{-- Filter Bar --}}
    <form method="GET" class="kartu tanpa-cetak mb-6 grid gap-3.5 p-4 sm:grid-cols-2 lg:grid-cols-5">
        <x-bidang label="Kelas" nama="kelas_id">
            <x-pilihan nama="kelas_id" :opsi="$kelasList->pluck('nama', 'id')" :terpilih="$kelas?->id"/>
        </x-bidang>
        <x-bidang label="Mata Pelajaran" nama="mata_pelajaran_id">
            <x-pilihan nama="mata_pelajaran_id" :opsi="$mapelList->pluck('nama', 'id')"
                       :terpilih="$mataPelajaranId" kosong="Semua mapel"/>
        </x-bidang>
        <x-bidang label="Dari Tanggal" nama="dari">
            <x-isian nama="dari" tipe="date" :value="$dari->toDateString()"/>
        </x-bidang>
        <x-bidang label="Sampai Tanggal" nama="sampai">
            <x-isian nama="sampai" tipe="date" :value="$sampai->toDateString()"/>
        </x-bidang>
        <div class="flex items-end">
            <x-tombol gaya="utama" ikon="funnel" class="w-full">Tampilkan</x-tombol>
        </div>
    </form>

    {{-- Ringkasan Statistik Rekap --}}
    @if ($totalSiswaRekap > 0)
        <div class="mb-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <x-kartu-statistik label="Total Siswa" :nilai="$totalSiswaRekap" keterangan="Siswa terdaftar di kelas" ikon="users" warna="utama"/>
            <x-kartu-statistik label="Rata-rata Kehadiran" :nilai="number_format($rataPersen, 1, ',', '.').'%'" keterangan="Tingkat kehadiran kelas" ikon="chart-bar" warna="aksen"/>
            <x-kartu-statistik label="Kehadiran ≥ 85%" :nilai="$jumlahOptimal" keterangan="Siswa disiplin hadir" ikon="check-badge" warna="aksen"/>
            <x-kartu-statistik label="Perlu Perhatian" :nilai="$jumlahRawan" :keterangan="'Alfa + Bolos ≥ '.$ambangAlfa.'x'" ikon="exclamation-triangle" :warna="$jumlahRawan > 0 ? 'bahaya' : 'utama'"/>
        </div>
    @endif

    <div x-data="{ cari: '', hanyaRawan: false }">
        @if ($totalSiswaRekap > 0)
            <div class="mb-3 flex flex-wrap items-center justify-between gap-2">
                <div class="relative w-full sm:w-72">
                    <x-heroicon-o-magnifying-glass class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-slate-400"/>
                    <input type="text" x-model="cari" placeholder="Cari nama siswa..."
                           class="w-full rounded-lg border-slate-200 bg-white pl-9 pr-4 py-1.5 text-xs text-slate-800 focus:border-primary focus:ring-primary">
                </div>
                <button type="button" @click="hanyaRawan = !hanyaRawan"
                        class="inline-flex cursor-pointer items-center gap-1.5 rounded-lg px-3 py-1.5 text-xs font-semibold transition-colors whitespace-nowrap"
                        :class="hanyaRawan ? 'bg-rose-600 text-white' : 'bg-white text-slate-700 border border-slate-200 hover:bg-slate-50'">
                    <x-heroicon-o-exclamation-triangle class="h-4 w-4"/>
                    <span>Filter Perlu Perhatian ({{ $jumlahRawan }})</span>
                </button>
            </div>
        @endif

        <x-tabel>
            <x-slot:kepala>
                <tr>
                    <th class="px-4 py-3 whitespace-nowrap">No</th>
                    <th class="px-4 py-3 whitespace-nowrap">Nama Siswa</th>
                    <th class="px-3 py-3 text-center whitespace-nowrap" title="Hadir">H</th>
                    <th class="px-3 py-3 text-center whitespace-nowrap" title="Sakit">S</th>
                    <th class="px-3 py-3 text-center whitespace-nowrap" title="Izin">I</th>
                    <th class="px-3 py-3 text-center whitespace-nowrap" title="Alfa">A</th>
                    <th class="px-3 py-3 text-center whitespace-nowrap" title="Bolos">B</th>
                    <th class="px-3 py-3 text-center whitespace-nowrap" title="Dispensasi">D</th>
                    <th class="px-4 py-3 text-center whitespace-nowrap">Pertemuan</th>
                    <th class="px-4 py-3 text-center whitespace-nowrap">Kehadiran</th>
                </tr>
            </x-slot:kepala>

            @forelse ($rekap as $baris)
                @php
                    $siswa = $siswas->get($baris['siswa_id']);
                    $perluPerhatian = ($baris['alfa'] + $baris['bolos']) >= $ambangAlfa;
                @endphp
                <tr x-show="(!hanyaRawan || {{ $perluPerhatian ? 'true' : 'false' }}) && (!cari || {{ Js::from(strtolower($siswa?->nama ?? '')) }}.includes(cari.toLowerCase()))"
                    @class(['hover:bg-slate-50/80 transition-colors', 'bg-rose-50/50' => $perluPerhatian])>
                    <td class="px-4 py-3 text-slate-500 font-semibold tabular-nums whitespace-nowrap">{{ $siswa?->pivot->no_absen }}</td>
                    <td class="px-4 py-3 whitespace-nowrap">
                        <div class="flex items-center gap-2">
                            <p class="font-semibold text-slate-800">{{ $siswa?->nama }}</p>
                            @if ($perluPerhatian)
                                <span class="rounded bg-rose-100 px-1.5 py-0.5 text-[10px] font-semibold text-rose-700 whitespace-nowrap">Perhatian</span>
                            @endif
                        </div>
                        <p class="text-xs text-slate-400">{{ $siswa?->nisn }}</p>
                    </td>
                    <td class="px-3 py-3 text-center font-semibold text-emerald-700 tabular-nums">{{ $baris['hadir'] }}</td>
                    <td class="px-3 py-3 text-center text-amber-700 tabular-nums">{{ $baris['sakit'] }}</td>
                    <td class="px-3 py-3 text-center text-sky-700 tabular-nums">{{ $baris['izin'] }}</td>
                    <td @class(['px-3 py-3 text-center font-semibold tabular-nums', 'nilai-merah' => $perluPerhatian, 'text-rose-700' => ! $perluPerhatian])>
                        {{ $baris['alfa'] }}
                    </td>
                    <td class="px-3 py-3 text-center text-red-800 tabular-nums">{{ $baris['bolos'] }}</td>
                    <td class="px-3 py-3 text-center text-violet-700 tabular-nums">{{ $baris['dispensasi'] }}</td>
                    <td class="px-4 py-3 text-center font-medium text-slate-600 tabular-nums">{{ $baris['pertemuan'] }}</td>
                    <td class="px-4 py-3 whitespace-nowrap">
                        <div class="flex items-center justify-center gap-2">
                            <div class="hidden sm:block h-1.5 w-16 overflow-hidden rounded-full bg-slate-100">
                                <div @class([
                                    'h-full rounded-full',
                                    'bg-emerald-600' => $baris['persentase'] >= 85,
                                    'bg-amber-500' => $baris['persentase'] >= 70 && $baris['persentase'] < 85,
                                    'bg-rose-600' => $baris['persentase'] < 70,
                                ]) style="width: {{ min(100, max(0, $baris['persentase'])) }}%"></div>
                            </div>
                            <span @class([
                                'rounded-md px-2 py-0.5 text-xs font-semibold tabular-nums whitespace-nowrap',
                                'bg-emerald-100 text-emerald-800' => $baris['persentase'] >= 85,
                                'bg-amber-100 text-amber-800' => $baris['persentase'] >= 70 && $baris['persentase'] < 85,
                                'bg-rose-100 text-rose-800' => $baris['persentase'] < 70,
                            ])>{{ number_format($baris['persentase'], 1, ',', '.') }}%</span>
                        </div>
                    </td>
                </tr>
            @empty
                <x-tabel.kosong :kolom="10" ikon="chart-bar"
                                judul="Belum ada data kehadiran"
                                pesan="Isi presensi pada pertemuan yang sudah terlaksana terlebih dahulu."/>
            @endforelse
        </x-tabel>
    </div>

    <div class="mt-4 flex items-center gap-2 rounded-lg border border-slate-200 bg-white px-4 py-2.5 text-xs text-slate-600">
        <x-heroicon-o-information-circle class="h-4 w-4 text-slate-400 shrink-0"/>
        <span>Baris merah = siswa dengan Alfa + Bolos ≥ <strong>{{ $ambangAlfa }} kali</strong>. Dispensasi dihitung hadir; agenda berstatus libur/kegiatan sekolah tidak dihitung sebagai pertemuan.</span>
    </div>
@endsection
