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
            <x-bidang label="Tahun Ajaran" nama="tahun_ajaran_id" class="min-w-56 flex-1">
                <x-pilihan nama="tahun_ajaran_id"
                           :opsi="$tahunAjarans->pluck('label', 'id')"
                           :terpilih="$tahunAjaran?->id"/>
            </x-bidang>

            @if ($guruList->isNotEmpty())
                <x-bidang label="Guru Pengajar" nama="guru_id" class="min-w-56 flex-1">
                    <x-pilihan nama="guru_id" :opsi="$guruList->pluck('name', 'id')" :terpilih="$guru->id"/>
                </x-bidang>
            @endif

            <x-tombol gaya="utama" ikon="funnel">Tampilkan</x-tombol>

            @if ($daftarKelasUnik->isNotEmpty())
                <button type="button" @click="tampilLegenda = !tampilLegenda"
                        class="tombol-sentuh inline-flex cursor-pointer items-center gap-1.5 rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-xs font-bold text-slate-600 transition hover:bg-slate-100">
                    <span>🎨 Kode Warna Kelas ({{ $daftarKelasUnik->count() }})</span>
                    <x-heroicon-o-chevron-down class="h-3.5 w-3.5 transition-transform" ::class="tampilLegenda ? 'rotate-180' : ''"/>
                </button>
            @endif
        </form>

        @if (\App\Support\JamPelajaran::isModeRamadhan())
            <div class="mb-5 rounded-2xl border border-emerald-300 bg-gradient-to-r from-emerald-50 via-teal-50 to-emerald-50 p-4 text-xs text-emerald-950 shadow-xs flex items-center gap-3">
                <span class="text-3xl">🌙</span>
                <div>
                    <p class="font-bold text-emerald-900 text-sm">Mode Bulan Ramadhan Sedang Aktif</p>
                    <p class="text-emerald-800 mt-0.5 leading-relaxed">
                        Jam mengajar di bawah ini otomatis mengikuti jadwal Ramadhan sekolah (durasi JP dipersingkat 30 menit). Selamat menjalankan ibadah puasa.
                    </p>
                </div>
            </div>
        @endif

        {{-- Pill Filter Hari Interaktif (Tanpa Reload) --}}
        <div class="tanpa-cetak mb-5 flex items-center gap-1.5 overflow-x-auto pb-1">
            <button type="button" @click="hariTerpilih = 'semua'"
                    :class="hariTerpilih === 'semua'
                        ? 'bg-primary text-white shadow-sm shadow-primary/25'
                        : 'bg-white text-slate-600 border border-slate-200 hover:bg-slate-50'"
                    class="cursor-pointer whitespace-nowrap rounded-xl px-3.5 py-2 text-xs font-extrabold transition">
                Semua Hari ({{ $semuaJadwal->count() }} JP)
            </button>
            @foreach (HariEnum::cases() as $h)
                @php
                    $jmlHari = $jadwalPerHari[$h->value]->count();
                    $isHariIni = $hariIniEnum === $h;
                @endphp
                <button type="button" @click="hariTerpilih = '{{ $h->value }}'"
                        :class="hariTerpilih === '{{ $h->value }}'
                            ? 'bg-primary text-white shadow-sm shadow-primary/25'
                            : 'bg-white text-slate-600 border border-slate-200 hover:bg-slate-50'"
                        class="cursor-pointer inline-flex items-center gap-1.5 whitespace-nowrap rounded-xl px-3.5 py-2 text-xs font-bold transition">
                    <span>{{ $h->label() }}</span>
                    @if ($isHariIni)
                        <span class="rounded-full bg-emerald-400 px-1.5 py-0.2 text-[9px] font-black text-slate-950">HARI INI</span>
                    @endif
                    <span class="rounded-md bg-black/10 px-1.5 py-0.5 text-[10px] font-extrabold">{{ $jmlHari }}</span>
                </button>
            @endforeach
        </div>

        {{-- Keterangan Kode Warna Kelas & Mapel (Collapsible agar ringkas) --}}
        @if ($daftarKelasUnik->isNotEmpty() || $daftarMapelUnik->isNotEmpty())
            <div x-show="tampilLegenda" x-collapse x-cloak class="mb-6 rounded-2xl border border-slate-200/80 bg-white p-4 shadow-xs space-y-3">
                @if ($daftarKelasUnik->isNotEmpty())
                    <div>
                        <div class="flex flex-wrap items-center justify-between gap-2 mb-2">
                            <div class="flex items-center gap-2">
                                <span class="inline-flex h-6 w-6 items-center justify-center rounded-lg bg-gradient-to-br from-emerald-500 to-teal-600 text-white shadow-xs text-xs font-bold">🏫</span>
                                <h4 class="text-xs font-bold uppercase tracking-wider text-slate-800">Kode Warna Kelas</h4>
                            </div>
                            <span class="text-[11px] text-slate-500 font-medium">Tiap kelas memiliki warna kartu berbeda agar jadwal langsung terbaca sekilas</span>
                        </div>
                        <div class="flex flex-wrap gap-2">
                            @foreach ($daftarKelasUnik as $kelas)
                                <div class="inline-flex items-center gap-2 rounded-lg px-3 py-1.5 text-xs font-black shadow-2xs"
                                     style="background-color: {{ $kelas['warna']['bg_card'] }}; border: 1.5px solid {{ $kelas['warna']['border'] }}; color: {{ $kelas['warna']['badge_text'] }};">
                                    <span class="h-2.5 w-2.5 rounded-full shrink-0" style="background-color: {{ $kelas['warna']['accent'] }};"></span>
                                    <span>{{ $kelas['nama'] }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                @if ($daftarMapelUnik->count() > 1 || ($daftarMapelUnik->first()['is_agenda'] ?? false))
                    <div class="pt-2 border-t border-slate-100">
                        <div class="flex items-center gap-2 mb-2">
                            <span class="inline-flex h-5 w-5 items-center justify-center rounded-md bg-gradient-to-br from-indigo-500 to-purple-600 text-white shadow-2xs text-[10px] font-bold">🎨</span>
                            <h5 class="text-[11px] font-black uppercase tracking-wider text-slate-700">Mata Pelajaran / Agenda</h5>
                        </div>
                        <div class="flex flex-wrap gap-1.5">
                            @foreach ($daftarMapelUnik as $mapel)
                                <span class="inline-flex items-center gap-1.5 rounded-full px-3 py-1 text-xs font-black shadow-2xs"
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
                     class="kartu overflow-hidden border {{ $isHariIni ? 'ring-2 ring-primary border-primary/40' : 'border-slate-200/90' }} shadow-xs transition-all duration-200 hover:shadow-md flex flex-col">
                    {{-- Header Hari dengan Gradien Khas --}}
                    <div class="flex items-center justify-between px-4 py-3.5 text-white shadow-2xs"
                         style="background: {{ $warnaHari['header'] }};">
                        <div class="flex items-center gap-2.5">
                            <span class="inline-flex h-7 w-7 items-center justify-center rounded-lg font-extrabold text-xs shadow-inner"
                                  style="background: {{ $warnaHari['badge_bg'] }}; border: 1px solid {{ $warnaHari['badge_border'] }};">
                                {{ substr($hari->label(), 0, 2) }}
                            </span>
                            <h3 class="font-extrabold text-base tracking-wide">{{ $hari->label() }}</h3>
                            @if ($isHariIni)
                                <span class="rounded-full bg-white text-slate-900 px-2 py-0.5 text-[10px] font-black uppercase">Hari Ini</span>
                            @endif
                        </div>
                        <span class="rounded-full px-2.5 py-0.5 text-xs font-bold shadow-2xs backdrop-blur-xs"
                              style="background: {{ $warnaHari['badge_bg'] }}; border: 1px solid {{ $warnaHari['badge_border'] }};">
                            {{ $daftar->count() }} JP
                        </span>
                    </div>

                    {{-- Konten Slot JP Tiap Hari --}}
                    <div class="p-3.5 space-y-2.5 bg-slate-50/40 flex-1">
                        @forelse ($daftar as $jadwal)
                            @php
                                $warnaKelas = WarnaKelas::untuk($jadwal->kelas_tampilan, $jadwal->isAgendaBersama());
                                $warnaMapel = WarnaMapel::untuk($jadwal->nama_tampilan, $jadwal->isAgendaBersama());
                            @endphp
                            <div class="group relative flex items-center gap-3 rounded-xl p-3 shadow-2xs transition-all duration-150 hover:shadow-md hover:-translate-y-0.5"
                                 style="background-color: {{ $warnaKelas['bg_card'] }}; border: 1px solid {{ $warnaKelas['border'] }}; border-left: 5px solid {{ $warnaKelas['accent'] }};">

                                {{-- Kotak Badge JP (Warna Sesuai Kelas) --}}
                                <div class="flex h-11 w-11 shrink-0 flex-col items-center justify-center rounded-xl shadow-2xs transition-transform group-hover:scale-105"
                                     style="background-color: {{ $warnaKelas['jp_bg'] }}; color: {{ $warnaKelas['jp_text'] }};">
                                    <span class="text-[9px] font-extrabold uppercase tracking-wider opacity-90 leading-none">JP</span>
                                    <span class="text-base font-black leading-tight">{{ $jadwal->jam_ke }}</span>
                                </div>

                                {{-- Informasi Kelas, Mapel & Jam --}}
                                <div class="min-w-0 flex-1">
                                    <div class="flex items-center justify-between gap-2">
                                        <div class="flex items-center gap-2">
                                            <span class="inline-flex items-center gap-1.5 rounded-lg px-2.5 py-1 text-sm font-black tracking-tight shadow-2xs"
                                                  style="background-color: {{ $warnaKelas['badge_bg'] }}; color: {{ $warnaKelas['badge_text'] }}; border: 1.5px solid {{ $warnaKelas['border'] }};">
                                                <span class="h-2 w-2 rounded-full shrink-0" style="background-color: {{ $warnaKelas['accent'] }};"></span>
                                                <strong class="font-black">{{ $jadwal->kelas_tampilan }}</strong>
                                            </span>

                                            @if ($jadwal->isAgendaBersama())
                                                <span class="inline-flex items-center gap-1 rounded-md px-2 py-0.5 text-[10px] font-black uppercase tracking-wider bg-amber-200/90 text-amber-950 border border-amber-300">
                                                    Bersama
                                                </span>
                                            @endif
                                        </div>

                                        {{-- Pintasan Langsung dari Kartu Jadwal --}}
                                        @if (! $jadwal->isAgendaBersama() && $jadwal->kelas_id)
                                            <div class="tanpa-cetak flex items-center gap-1 opacity-85 group-hover:opacity-100">
                                                <a href="{{ route('nilai.index', ['kelas_id' => $jadwal->kelas_id, 'mata_pelajaran_id' => $jadwal->mata_pelajaran_id]) }}"
                                                   title="Buka Daftar Nilai {{ $jadwal->kelas_tampilan }}"
                                                   class="rounded-lg bg-white/90 px-2 py-1 text-[10px] font-extrabold text-slate-700 shadow-2xs border border-slate-200/80 hover:bg-primary hover:text-white hover:border-primary transition">
                                                    Nilai
                                                </a>
                                                <a href="{{ route('presensi.rekap', ['kelas_id' => $jadwal->kelas_id, 'mata_pelajaran_id' => $jadwal->mata_pelajaran_id]) }}"
                                                   title="Rekap Kehadiran {{ $jadwal->kelas_tampilan }}"
                                                   class="rounded-lg bg-white/90 px-2 py-1 text-[10px] font-extrabold text-slate-700 shadow-2xs border border-slate-200/80 hover:bg-accent hover:text-white hover:border-accent transition">
                                                    Rekap
                                                </a>
                                            </div>
                                        @endif
                                    </div>

                                    {{-- Pil Nama Mata Pelajaran --}}
                                    <div class="mt-1.5 flex flex-wrap items-center gap-1.5">
                                        <span class="inline-flex items-center gap-1.5 rounded-full px-3 py-1 text-xs font-black truncate max-w-full shadow-2xs"
                                              style="background-color: {{ $warnaMapel['badge_bg'] }}; color: {{ $warnaMapel['badge_text'] }}; border: 1.5px solid {{ $warnaMapel['border'] }};">
                                            <span class="h-1.5 w-1.5 rounded-full shrink-0" style="background-color: {{ $warnaMapel['dot'] }};"></span>
                                            <strong class="font-black truncate">{{ $jadwal->nama_tampilan }}</strong>
                                        </span>
                                    </div>

                                    {{-- Waktu & Ruang --}}
                                    <p class="mt-2 flex items-center gap-1.5 text-xs font-bold text-slate-700">
                                        <x-heroicon-o-clock class="h-3.5 w-3.5 text-slate-600 shrink-0"/>
                                        <strong class="font-black text-slate-900 tracking-tight">{{ $jadwal->jam }}</strong>
                                        @if ($jadwal->ruang)
                                            <span class="text-slate-400 font-bold">·</span>
                                            <x-heroicon-o-building-office class="h-3.5 w-3.5 text-slate-600 shrink-0"/>
                                            <strong class="font-black text-slate-900">{{ $jadwal->ruang }}</strong>
                                        @endif
                                    </p>
                                </div>
                            </div>
                        @empty
                            <div class="flex flex-col items-center justify-center py-10 text-center text-slate-400">
                                <x-heroicon-o-calendar-days class="h-9 w-9 text-slate-300 mb-2"/>
                                <p class="text-xs font-bold text-slate-600">Tidak ada jadwal mengajar</p>
                                <p class="text-[11px] text-slate-400 mt-0.5">Hari bebas jam pelajaran</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            @endforeach
        </div>
    </div>
@endsection
