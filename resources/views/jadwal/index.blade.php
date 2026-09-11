@extends('layouts.app')
@section('judul', 'Jadwal Mengajar')

@php
    use App\Enums\HariEnum;
    use App\Support\WarnaKelas;
    use App\Support\WarnaMapel;

    $semuaJadwal = $jadwalPerHari->flatten(1);

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
    <x-kepala-halaman judul="Jadwal Mengajar"
                      :keterangan="$guru->name.' — '.$tahunAjaran?->label">
        <x-slot:aksi>
            <x-tombol gaya="garis" ikon="printer"
                      :href="route('jadwal.cetak', request()->only('tahun_ajaran_id', 'guru_id'))">
                Cetak PDF (F4)
            </x-tombol>
        </x-slot:aksi>
    </x-kepala-halaman>

    <form method="GET" class="kartu tanpa-cetak mb-6 flex flex-wrap items-end gap-3 p-4">
        <x-bidang label="Tahun Ajaran" nama="tahun_ajaran_id" class="min-w-56 flex-1">
            <x-pilihan nama="tahun_ajaran_id"
                       :opsi="$tahunAjarans->pluck('label', 'id')"
                       :terpilih="$tahunAjaran?->id"/>
        </x-bidang>

        @if ($guruList->isNotEmpty())
            <x-bidang label="Guru" nama="guru_id" class="min-w-56 flex-1">
                <x-pilihan nama="guru_id" :opsi="$guruList->pluck('name', 'id')" :terpilih="$guru->id"/>
            </x-bidang>
        @endif

        <x-tombol gaya="utama" ikon="funnel">Tampilkan</x-tombol>
    </form>

    @if (\App\Support\JamPelajaran::isModeRamadhan())
        <div class="mb-5 rounded-xl border border-emerald-300 bg-gradient-to-r from-emerald-50 via-teal-50 to-emerald-50 p-4 text-xs text-emerald-950 shadow-sm flex items-center gap-3">
            <span class="text-3xl">🌙</span>
            <div>
                <p class="font-bold text-emerald-900 text-sm">Mode Bulan Ramadhan Sedang Aktif</p>
                <p class="text-emerald-800 mt-0.5 leading-relaxed">
                    Jam mengajar di bawah ini otomatis mengikuti jadwal Ramadhan sekolah (durasi JP dipersingkat 30 menit). Selamat menjalankan ibadah puasa.
                </p>
            </div>
        </div>
    @endif

    {{-- Keterangan Kode Warna Kelas & Mapel (Legend) --}}
    @if ($daftarKelasUnik->isNotEmpty() || $daftarMapelUnik->isNotEmpty())
        <div class="mb-6 rounded-2xl border border-slate-200/80 bg-white p-4 shadow-sm space-y-3">
            {{-- Legenda Warna Kelas --}}
            @if ($daftarKelasUnik->isNotEmpty())
                <div>
                    <div class="flex flex-wrap items-center justify-between gap-2 mb-2">
                        <div class="flex items-center gap-2">
                            <span class="inline-flex h-6 w-6 items-center justify-center rounded-lg bg-gradient-to-br from-emerald-500 to-teal-600 text-white shadow-sm text-xs font-bold">🏫</span>
                            <h4 class="text-xs font-bold uppercase tracking-wider text-slate-800">Kode Warna Kelas</h4>
                        </div>
                        <span class="text-[11px] text-slate-500 font-medium">Tiap kelas memiliki warna kartu berbeda agar jadwal langsung terbaca sekilas</span>
                    </div>
                    <div class="flex flex-wrap gap-2">
                        @foreach ($daftarKelasUnik as $kelas)
                            <div class="inline-flex items-center gap-2 rounded-lg px-3 py-1.5 text-xs font-black shadow-xs transition-all hover:scale-105"
                                 style="background-color: {{ $kelas['warna']['bg_card'] }}; border: 1.5px solid {{ $kelas['warna']['border'] }}; color: {{ $kelas['warna']['badge_text'] }};">
                                <span class="h-2.5 w-2.5 rounded-full shrink-0 shadow-xs" style="background-color: {{ $kelas['warna']['accent'] }};"></span>
                                <span>{{ $kelas['nama'] }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            {{-- Legenda Mapel jika ada lebih dari 1 mapel atau agenda bersama --}}
            @if ($daftarMapelUnik->count() > 1 || ($daftarMapelUnik->first()['is_agenda'] ?? false))
                <div class="pt-2 border-t border-slate-100">
                    <div class="flex flex-wrap items-center justify-between gap-2 mb-2">
                        <div class="flex items-center gap-2">
                            <span class="inline-flex h-5 w-5 items-center justify-center rounded-md bg-gradient-to-br from-indigo-500 to-purple-600 text-white shadow-xs text-[10px] font-bold">🎨</span>
                            <h5 class="text-[11px] font-black uppercase tracking-wider text-slate-700">Mata Pelajaran / Agenda</h5>
                        </div>
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
    <div class="grid gap-5 md:grid-cols-2 xl:grid-cols-3">
        @foreach (HariEnum::cases() as $hari)
            @php
                $daftar = $jadwalPerHari[$hari->value];
                $warnaHari = WarnaMapel::warnaHari($hari);
            @endphp
            <div class="kartu overflow-hidden border border-slate-200/90 shadow-sm transition-all duration-200 hover:shadow-md flex flex-col">
                {{-- Header Hari dengan Gradien Khas --}}
                <div class="flex items-center justify-between px-4 py-3 text-white shadow-xs"
                     style="background: {{ $warnaHari['header'] }};">
                    <div class="flex items-center gap-2.5">
                        <span class="inline-flex h-7 w-7 items-center justify-center rounded-lg font-extrabold text-xs shadow-inner"
                              style="background: {{ $warnaHari['badge_bg'] }}; border: 1px solid {{ $warnaHari['badge_border'] }};">
                            {{ substr($hari->label(), 0, 2) }}
                        </span>
                        <h3 class="font-extrabold text-base tracking-wide">{{ $hari->label() }}</h3>
                    </div>
                    <span class="rounded-full px-2.5 py-0.5 text-xs font-bold shadow-xs backdrop-blur-xs"
                          style="background: {{ $warnaHari['badge_bg'] }}; border: 1px solid {{ $warnaHari['badge_border'] }};">
                        {{ $daftar->count() }} JP
                    </span>
                </div>

                {{-- Konten Slot JP Tiap Hari --}}
                <div class="p-3 space-y-2.5 bg-slate-50/40 flex-1">
                    @forelse ($daftar as $jadwal)
                        @php
                            $warnaKelas = WarnaKelas::untuk($jadwal->kelas_tampilan, $jadwal->isAgendaBersama());
                            $warnaMapel = WarnaMapel::untuk($jadwal->nama_tampilan, $jadwal->isAgendaBersama());
                        @endphp
                        <div class="group relative flex items-center gap-3 rounded-xl p-3 shadow-xs transition-all duration-150 hover:shadow-md hover:-translate-y-0.5"
                             style="background-color: {{ $warnaKelas['bg_card'] }}; border: 1px solid {{ $warnaKelas['border'] }}; border-left: 5px solid {{ $warnaKelas['accent'] }};">

                            {{-- Kotak Badge JP (Warna Sesuai Kelas) --}}
                            <div class="flex h-11 w-11 shrink-0 flex-col items-center justify-center rounded-lg shadow-xs transition-transform group-hover:scale-105"
                                 style="background-color: {{ $warnaKelas['jp_bg'] }}; color: {{ $warnaKelas['jp_text'] }};">
                                <span class="text-[9px] font-extrabold uppercase tracking-wider opacity-90 leading-none">JP</span>
                                <span class="text-base font-black leading-tight">{{ $jadwal->jam_ke }}</span>
                            </div>

                            {{-- Informasi Kelas, Mapel & Jam --}}
                            <div class="min-w-0 flex-1">
                                <div class="flex items-center gap-2">
                                    {{-- Badge Pil Nama Kelas Lebih Besar & Tebal (Bold) --}}
                                    <span class="inline-flex items-center gap-1.5 rounded-lg px-2.5 py-1 text-sm font-black tracking-tight shadow-xs"
                                          style="background-color: {{ $warnaKelas['badge_bg'] }}; color: {{ $warnaKelas['badge_text'] }}; border: 1.5px solid {{ $warnaKelas['border'] }};">
                                        <span class="h-2 w-2 rounded-full shrink-0 shadow-xs" style="background-color: {{ $warnaKelas['accent'] }};"></span>
                                        <strong class="font-black">{{ $jadwal->kelas_tampilan }}</strong>
                                    </span>

                                    @if ($jadwal->isAgendaBersama())
                                        <span class="inline-flex items-center gap-1 rounded-md px-2 py-0.5 text-[10px] font-black uppercase tracking-wider bg-amber-200/90 text-amber-950 border border-amber-300 shadow-2xs">
                                            Bersama
                                        </span>
                                    @endif
                                </div>

                                {{-- Pil Nama Mata Pelajaran Lebih Tebal & Jelas (Bold) --}}
                                <div class="mt-1.5 flex flex-wrap items-center gap-1.5">
                                    <span class="inline-flex items-center gap-1.5 rounded-full px-3 py-1 text-xs font-black truncate max-w-full shadow-2xs"
                                          style="background-color: {{ $warnaMapel['badge_bg'] }}; color: {{ $warnaMapel['badge_text'] }}; border: 1.5px solid {{ $warnaMapel['border'] }};">
                                        <span class="h-1.5 w-1.5 rounded-full shrink-0" style="background-color: {{ $warnaMapel['dot'] }};"></span>
                                        <strong class="font-black truncate">{{ $jadwal->nama_tampilan }}</strong>
                                    </span>
                                </div>

                                {{-- Waktu & Ruang Tebal (Bold) --}}
                                <p class="mt-2 flex items-center gap-1.5 text-xs font-bold text-slate-700">
                                    <svg class="h-3.5 w-3.5 text-slate-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    <strong class="font-black text-slate-900 tracking-tight">{{ $jadwal->jam }}</strong>
                                    @if ($jadwal->ruang)
                                        <span class="text-slate-400 font-bold">·</span>
                                        <svg class="h-3.5 w-3.5 text-slate-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                        </svg>
                                        <strong class="font-black text-slate-900">{{ $jadwal->ruang }}</strong>
                                    @endif
                                </p>
                            </div>
                        </div>
                    @empty
                        <div class="flex flex-col items-center justify-center py-10 text-center text-slate-400">
                            <svg class="h-9 w-9 text-slate-300 mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                            <p class="text-xs font-semibold text-slate-600">Tidak ada jadwal mengajar</p>
                            <p class="text-[11px] text-slate-400 mt-0.5">Hari bebas jam pelajaran</p>
                        </div>
                    @endforelse
                </div>
            </div>
        @endforeach
    </div>
@endsection
