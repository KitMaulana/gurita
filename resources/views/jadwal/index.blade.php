@extends('layouts.app')
@section('judul', 'Jadwal Mengajar')

@php
    use App\Enums\HariEnum;
    use App\Support\WarnaKelas;
    use App\Support\WarnaMapel;

    $semuaJadwal = $jadwalPerHari->flatten(1);
    $hariIniEnum = HariEnum::dariTanggal(now());

    $daftarKelasUnik = $semuaJadwal->map(function ($j) {
        return [
            'nama' => $j->kelas_tampilan,
            'is_agenda' => $j->isAgendaBersama(),
            'warna' => WarnaKelas::untuk($j->kelas_tampilan, $j->isAgendaBersama()),
        ];
    })->unique('nama')->values();

    $daftarMapelUnik = $semuaJadwal->map(function ($j) {
        return [
            'nama' => $j->nama_tampilan,
            'is_agenda' => $j->isAgendaBersama(),
            'warna' => WarnaMapel::untuk($j->nama_tampilan, $j->isAgendaBersama()),
        ];
    })->unique('nama')->values();
@endphp

@section('konten')
    <div x-data="{ hariTerpilih: 'semua', tampilLegenda: false }">
        <x-kepala-halaman judul="Jadwal Mengajar"
                          :keterangan="$guru->name.' — '.$tahunAjaran?->label.' · Total '.$semuaJadwal->count().' JP / Minggu'">
            <x-slot:aksi>
                <x-tombol gaya="aksen" ikon="bolt" :href="route('agenda.dari-jadwal')">
                    Isi Agenda Hari Ini
                </x-tombol>
                <x-tombol gaya="garis" ikon="printer"
                          :href="route('jadwal.cetak', request()->only('tahun_ajaran_id', 'guru_id'))">
                    Cetak PDF (F4)
                </x-tombol>
            </x-slot:aksi>
        </x-kepala-halaman>

        {{-- Filter Tahun Ajaran & Guru --}}
        <form method="GET" class="kartu tanpa-cetak mb-5 flex flex-wrap items-end gap-3 p-4">
            <x-bidang label="Tahun Ajaran" nama="tahun_ajaran_id" class="min-w-52 flex-1">
                <x-pilihan nama="tahun_ajaran_id"
                           :opsi="$tahunAjarans->pluck('label', 'id')"
                           :terpilih="$tahunAjaran?->id"/>
            </x-bidang>

            @if ($guruList->isNotEmpty())
                <x-bidang label="Guru Pengajar" nama="guru_id" class="min-w-52 flex-1">
                    <x-pilihan nama="guru_id" :opsi="$guruList->pluck('name', 'id')" :terpilih="$guru->id"/>
                </x-bidang>
            @endif

            <x-tombol gaya="utama" ikon="funnel">Tampilkan</x-tombol>

            @if ($daftarKelasUnik->isNotEmpty())
                <button type="button" @click="tampilLegenda = !tampilLegenda"
                        class="tombol-sentuh inline-flex cursor-pointer items-center gap-1.5 whitespace-nowrap rounded-lg border border-slate-200 bg-white px-3.5 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-50">
                    <x-heroicon-o-swatch class="h-4 w-4 text-slate-400"/>
                    <span>Kode Warna ({{ $daftarKelasUnik->count() }})</span>
                    <x-heroicon-o-chevron-down class="h-3.5 w-3.5 transition-transform" ::class="tampilLegenda ? 'rotate-180' : ''"/>
                </button>
            @endif
        </form>

        @if (\App\Support\JamPelajaran::isModeRamadhan())
            <div class="mb-5 rounded-xl border border-emerald-200 bg-emerald-50/70 p-4 text-xs text-emerald-900 flex items-center gap-3">
                <x-heroicon-o-moon class="h-5 w-5 text-emerald-700 shrink-0"/>
                <div>
                    <p class="font-semibold text-emerald-950">Mode Bulan Ramadhan Aktif</p>
                    <p class="text-emerald-800 mt-0.5">
                        Jam mengajar otomatis mengikuti jadwal Ramadhan sekolah (durasi JP 30 menit).
                    </p>
                </div>
            </div>
        @endif

        {{-- Pill Filter Hari Interaktif --}}
        <div class="tanpa-cetak mb-5 flex items-center gap-1.5 overflow-x-auto pb-1">
            <button type="button" @click="hariTerpilih = 'semua'"
                    :class="hariTerpilih === 'semua'
                        ? 'bg-slate-900 text-white'
                        : 'bg-white text-slate-600 border border-slate-200 hover:bg-slate-50'"
                    class="cursor-pointer whitespace-nowrap rounded-lg px-3 py-1.5 text-xs font-semibold transition-colors">
                Semua Hari ({{ $semuaJadwal->count() }} JP)
            </button>
            @foreach (HariEnum::cases() as $h)
                @php
                    $jmlHari = $jadwalPerHari[$h->value]->count();
                    $isHariIni = $hariIniEnum === $h;
                @endphp
                <button type="button" @click="hariTerpilih = '{{ $h->value }}'"
                        :class="hariTerpilih === '{{ $h->value }}'
                            ? 'bg-slate-900 text-white'
                            : 'bg-white text-slate-600 border border-slate-200 hover:bg-slate-50'"
                        class="cursor-pointer inline-flex items-center gap-1.5 whitespace-nowrap rounded-lg px-3 py-1.5 text-xs font-semibold transition-colors">
                    <span>{{ $h->label() }}</span>
                    @if ($isHariIni)
                        <span class="rounded bg-emerald-500 px-1.5 py-0.5 text-[9px] font-bold text-white uppercase">Hari Ini</span>
                    @endif
                    <span class="opacity-65 text-[11px] tabular-nums">({{ $jmlHari }})</span>
                </button>
            @endforeach
        </div>

        {{-- Keterangan Kode Warna Kelas & Mapel --}}
        @if ($daftarKelasUnik->isNotEmpty() || $daftarMapelUnik->isNotEmpty())
            <div x-show="tampilLegenda" x-collapse x-cloak class="mb-6 rounded-xl border border-slate-200 bg-white p-4 space-y-3">
                @if ($daftarKelasUnik->isNotEmpty())
                    <div>
                        <p class="mb-2 text-xs font-semibold text-slate-700">Kode Warna Kelas</p>
                        <div class="flex flex-wrap gap-2">
                            @foreach ($daftarKelasUnik as $kelas)
                                <div class="inline-flex items-center gap-1.5 rounded-md px-2.5 py-1 text-xs font-semibold whitespace-nowrap"
                                     style="background-color: {{ $kelas['warna']['bg_card'] }}; border: 1px solid {{ $kelas['warna']['border'] }}; color: {{ $kelas['warna']['badge_text'] }};">
                                    <span class="h-2 w-2 rounded-full shrink-0" style="background-color: {{ $kelas['warna']['accent'] }};"></span>
                                    <span>{{ $kelas['nama'] }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                @if ($daftarMapelUnik->count() > 1 || ($daftarMapelUnik->first()['is_agenda'] ?? false))
                    <div class="pt-2 border-t border-slate-100">
                        <p class="mb-2 text-xs font-semibold text-slate-700">Mata Pelajaran / Agenda</p>
                        <div class="flex flex-wrap gap-1.5">
                            @foreach ($daftarMapelUnik as $mapel)
                                <span class="inline-flex items-center gap-1.5 rounded-md px-2.5 py-1 text-xs font-semibold whitespace-nowrap"
                                      style="background-color: {{ $mapel['warna']['badge_bg'] }}; border: 1px solid {{ $mapel['warna']['border'] }}; color: {{ $mapel['warna']['badge_text'] }};">
                                    <span class="h-1.5 w-1.5 rounded-full shrink-0" style="background-color: {{ $mapel['warna']['accent'] }};"></span>
                                    <span>{{ $mapel['nama'] }}</span>
                                </span>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
        @endif

        {{-- Grid Jadwal Mingguan --}}
        <div class="grid gap-5"
             :class="hariTerpilih === 'semua' ? 'md:grid-cols-2 xl:grid-cols-3' : 'grid-cols-1 max-w-3xl'">
            @foreach (HariEnum::cases() as $hari)
                @php
                    $daftar = $jadwalPerHari[$hari->value];
                    $warnaHari = WarnaMapel::warnaHari($hari);
                    $isHariIni = $hariIniEnum === $hari;
                @endphp
                <div x-show="hariTerpilih === 'semua' || hariTerpilih === '{{ $hari->value }}'"
                     class="kartu overflow-hidden {{ $isHariIni ? 'ring-2 ring-primary border-primary/40' : '' }} flex flex-col">
                    <div class="flex items-center justify-between px-4 py-3 text-white"
                         style="background: {{ $warnaHari['header'] }};">
                        <div class="flex items-center gap-2">
                            <h3 class="font-bold text-sm tracking-tight">{{ $hari->label() }}</h3>
                            @if ($isHariIni)
                                <span class="rounded bg-white px-1.5 py-0.5 text-[10px] font-bold text-slate-900 uppercase">Hari Ini</span>
                            @endif
                        </div>
                        <span class="rounded-md px-2 py-0.5 text-xs font-semibold whitespace-nowrap"
                              style="background: {{ $warnaHari['badge_bg'] }}; border: 1px solid {{ $warnaHari['badge_border'] }};">
                            {{ $daftar->count() }} JP
                        </span>
                    </div>

                    <div class="p-3.5 space-y-2.5 bg-slate-50/40 flex-1">
                        @forelse ($daftar as $jadwal)
                            @php
                                $warnaKelas = WarnaKelas::untuk($jadwal->kelas_tampilan, $jadwal->isAgendaBersama());
                                $warnaMapel = WarnaMapel::untuk($jadwal->nama_tampilan, $jadwal->isAgendaBersama());
                            @endphp
                            <div class="group relative flex items-start gap-3 rounded-lg p-3 bg-white border border-slate-200 transition-colors hover:border-slate-300"
                                 style="border-left: 4px solid {{ $warnaKelas['accent'] }};">

                                <div class="flex h-10 w-10 shrink-0 flex-col items-center justify-center rounded-lg"
                                     style="background-color: {{ $warnaKelas['jp_bg'] }}; color: {{ $warnaKelas['jp_text'] }};">
                                    <span class="text-[8px] font-bold uppercase leading-none opacity-90">JP</span>
                                    <span class="text-sm font-bold leading-tight">{{ $jadwal->jam_ke }}</span>
                                </div>

                                <div class="min-w-0 flex-1">
                                    <div class="flex items-center justify-between gap-2">
                                        <div class="flex items-center gap-1.5 flex-wrap">
                                            <span class="inline-flex items-center gap-1 rounded-md px-2 py-0.5 text-xs font-bold whitespace-nowrap shrink-0"
                                                  style="background-color: {{ $warnaKelas['badge_bg'] }}; color: {{ $warnaKelas['badge_text'] }}; border: 1px solid {{ $warnaKelas['border'] }};">
                                                {{ $jadwal->kelas_tampilan }}
                                            </span>

                                            @if ($jadwal->isAgendaBersama())
                                                <span class="rounded-md px-1.5 py-0.5 text-[10px] font-semibold bg-amber-100 text-amber-900 whitespace-nowrap shrink-0">
                                                    Bersama
                                                </span>
                                            @endif
                                        </div>

                                        @if (! $jadwal->isAgendaBersama() && $jadwal->kelas_id)
                                            <div class="tanpa-cetak flex items-center gap-1 shrink-0">
                                                <a href="{{ route('nilai.index', ['kelas_id' => $jadwal->kelas_id, 'mata_pelajaran_id' => $jadwal->mata_pelajaran_id]) }}"
                                                   title="Daftar Nilai {{ $jadwal->kelas_tampilan }}"
                                                   class="rounded border border-slate-200 bg-slate-50 px-2 py-0.5 text-[11px] font-semibold text-slate-700 hover:bg-primary hover:text-white hover:border-primary transition-colors">
                                                    Nilai
                                                </a>
                                                <a href="{{ route('presensi.rekap', ['kelas_id' => $jadwal->kelas_id, 'mata_pelajaran_id' => $jadwal->mata_pelajaran_id]) }}"
                                                   title="Rekap Kehadiran {{ $jadwal->kelas_tampilan }}"
                                                   class="rounded border border-slate-200 bg-slate-50 px-2 py-0.5 text-[11px] font-semibold text-slate-700 hover:bg-slate-900 hover:text-white hover:border-slate-900 transition-colors">
                                                    Rekap
                                                </a>
                                            </div>
                                        @endif
                                    </div>

                                    <p class="mt-1.5 truncate text-xs font-semibold text-slate-800" title="{{ $jadwal->nama_tampilan }}">
                                        {{ $jadwal->nama_tampilan }}
                                    </p>

                                    <p class="mt-1 flex items-center gap-1.5 text-[11px] text-slate-500">
                                        <x-heroicon-o-clock class="h-3.5 w-3.5 text-slate-400 shrink-0"/>
                                        <span class="font-medium text-slate-700">{{ $jadwal->jam }} WIB</span>
                                        @if ($jadwal->ruang)
                                            <span class="text-slate-300">·</span>
                                            <span>Ruang {{ $jadwal->ruang }}</span>
                                        @endif
                                    </p>
                                </div>
                            </div>
                        @empty
                            <div class="flex flex-col items-center justify-center py-8 text-center text-slate-400">
                                <x-heroicon-o-calendar-days class="h-7 w-7 text-slate-300 mb-1.5"/>
                                <p class="text-xs font-medium text-slate-500">Tidak ada jadwal</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            @endforeach
        </div>
    </div>
@endsection
