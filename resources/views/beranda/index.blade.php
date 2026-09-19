@extends('layouts.app')
@section('judul', 'Beranda')

@php use App\Support\Tanggal; @endphp

@section('konten')
    {{-- Kartu sambutan --}}
    <div class="mb-6 overflow-hidden rounded-xl bg-gradient-to-br from-[#1A365D] to-[#2D4A6F] p-6 text-white shadow-sm">
        <p class="text-sm text-white/70">{{ Tanggal::lengkap(now()) }}</p>
        <h2 class="mt-1 text-2xl font-extrabold">Selamat datang, {{ $guru->name }}</h2>
        <p class="text-sm text-white/80">{{ $guru->jabatan ?: 'Guru' }}</p>
        @if ($guru->quote)
            <p class="mt-4 border-l-2 border-accent pl-3 text-sm italic text-white/90">“{{ $guru->quote }}”</p>
        @endif
    </div>

    {{-- 4 kartu statistik --}}
    <div class="mb-6 grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <x-kartu-statistik label="Kelas Diampu" :nilai="$statistik['kelas_diampu']"
                           ikon="building-office" warna="primary" :href="route('jadwal.index')"/>
        <x-kartu-statistik label="Materi Tersedia" :nilai="$statistik['materi_tersedia']"
                           ikon="folder" warna="accent" :href="route('materi.index')"/>
        <x-kartu-statistik label="Total Siswa" :nilai="$statistik['total_siswa']"
                           ikon="users" warna="sky"/>
        <x-kartu-statistik label="Jadwal Hari Ini" :nilai="$statistik['jadwal_hari_ini']"
                           ikon="calendar-days" warna="amber"/>
    </div>

    <div class="grid gap-6 lg:grid-cols-3">
        {{-- Jadwal hari ini --}}
        <div class="lg:col-span-2">
            <div class="kartu p-5">
                <div class="mb-4 flex flex-wrap items-center justify-between gap-2">
                    <div class="flex items-center gap-2">
                        <h3 class="font-bold text-primary">Jadwal Hari Ini</h3>
                        @if (\App\Support\JamPelajaran::isModeRamadhan())
                            <span class="inline-flex items-center gap-1 rounded-full bg-emerald-100 px-2.5 py-0.5 text-[11px] font-bold text-emerald-800 border border-emerald-200">
                                🌙 Mode Ramadhan
                            </span>
                        @endif
                    </div>
                    <a href="{{ route('agenda.dari-jadwal') }}" class="text-sm font-semibold text-accent-600 hover:underline">
                        Isi Agenda & Presensi →
                    </a>
                </div>

                @forelse ($jadwalHariIni as $jadwal)
                    @php
                        $agenda = $agendaHariIni->get($jadwal->id);
                        $warnaKelas = \App\Support\WarnaKelas::untuk($jadwal->kelas_tampilan, $jadwal->isAgendaBersama());
                        $warnaMapel = \App\Support\WarnaMapel::untuk($jadwal->nama_tampilan, $jadwal->isAgendaBersama());
                    @endphp
                    <div @class([
                        'mb-2.5 flex flex-col gap-3 rounded-xl border p-3.5 sm:flex-row sm:items-center transition-all hover:shadow-xs',
                        'bg-accent-50/50 ring-2 ring-accent' => $jadwal->sedangBerlangsung(),
                        'bg-white' => ! $jadwal->sedangBerlangsung(),
                    ]) style="border-left: 5px solid {{ $warnaKelas['accent'] }};">
                        <div class="flex h-11 w-11 shrink-0 flex-col items-center justify-center rounded-lg shadow-xs"
                             style="background-color: {{ $warnaKelas['jp_bg'] }}; color: {{ $warnaKelas['jp_text'] }};">
                            <span class="text-[9px] font-extrabold uppercase tracking-wider opacity-90 leading-none">JP</span>
                            <span class="text-base font-black leading-tight">{{ $jadwal->jam_ke }}</span>
                        </div>
                        <div class="min-w-0 flex-1">
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="inline-flex items-center gap-1.5 rounded-lg px-2.5 py-1 text-sm font-black tracking-tight shadow-xs"
                                      style="background-color: {{ $warnaKelas['badge_bg'] }}; color: {{ $warnaKelas['badge_text'] }}; border: 1.5px solid {{ $warnaKelas['border'] }};">
                                    <span class="h-2 w-2 rounded-full shrink-0 shadow-xs" style="background-color: {{ $warnaKelas['accent'] }};"></span>
                                    <strong class="font-black">{{ $jadwal->kelas_tampilan }}</strong>
                                </span>
                                <span class="inline-flex items-center gap-1.5 rounded-full px-3 py-1 text-xs font-black shadow-2xs"
                                      style="background-color: {{ $warnaMapel['badge_bg'] }}; color: {{ $warnaMapel['badge_text'] }}; border: 1.5px solid {{ $warnaMapel['border'] }};">
                                    <span class="h-1.5 w-1.5 rounded-full shrink-0" style="background-color: {{ $warnaMapel['dot'] }};"></span>
                                    <strong class="font-black truncate">{{ $jadwal->nama_tampilan }}</strong>
                                </span>
                                @if ($jadwal->sedangBerlangsung())
                                    <span class="inline-flex items-center gap-1 rounded-full bg-accent px-2 py-0.5 text-[10px] font-bold text-white shadow-xs">
                                        SEDANG BERLANGSUNG
                                    </span>
                                @endif
                            </div>
                            <p class="mt-2 text-xs font-bold text-slate-700 flex items-center gap-1.5">
                                <svg class="h-3.5 w-3.5 text-slate-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <strong class="font-black text-slate-900 tracking-tight">{{ $jadwal->jam }}</strong>
                                @if ($jadwal->ruang)
                                    <span class="text-slate-400 font-bold">·</span>
                                    <strong class="font-black text-slate-900">{{ $jadwal->ruang }}</strong>
                                @endif
                            </p>
                        </div>
                        <div class="shrink-0">
                            @if ($agenda)
                                <x-tombol gaya="garis" :href="route('presensi.isi', $agenda)" ikon="user-group">
                                    {{ $agenda->presensiSudahDiisi() ? 'Lihat Presensi' : 'Isi Presensi' }}
                                </x-tombol>
                            @else
                                <div class="flex items-center gap-1.5">
                                    <x-tombol gaya="aksen" :href="route('presensi.index', ['tanggal' => today()->toDateString(), 'jadwal_id' => $jadwal->id])" ikon="check-circle">
                                        Isi Presensi
                                    </x-tombol>
                                    <x-tombol gaya="garis" :href="route('agenda.dari-jadwal', ['tanggal' => today()->toDateString(), 'jadwal_id' => $jadwal->id])" ikon="plus" title="Isi Agenda Mengajar">
                                        Agenda
                                    </x-tombol>
                                </div>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="py-10 text-center">
                        <x-heroicon-o-calendar-days class="mx-auto h-10 w-10 text-slate-300"/>
                        <p class="mt-2 text-sm font-semibold text-slate-600">Tidak ada jadwal hari ini</p>
                        <p class="text-sm text-slate-400">Nikmati harinya, atau lengkapi agenda yang tertinggal.</p>
                    </div>
                @endforelse
            </div>

            {{-- Rekap presensi hari ini --}}
            <div class="kartu mt-6 p-5">
                <h3 class="mb-4 font-bold text-primary">Rekap Presensi Hari Ini</h3>

                @forelse ($rekapHariIni as $agenda)
                    <div class="mb-2 flex flex-wrap items-center justify-between gap-2 rounded-lg border border-slate-200 p-3">
                        <div class="min-w-0">
                            <p class="truncate text-sm font-semibold text-slate-800">
                                {{ $agenda->jadwal?->kelas_tampilan ?? '—' }}
                            </p>
                            <p class="truncate text-xs text-slate-500">{{ $agenda->jadwal?->nama_tampilan ?? '—' }}</p>
                        </div>
                        <div class="flex flex-wrap gap-1.5 text-xs font-semibold">
                            <span class="rounded-full bg-emerald-100 px-2 py-0.5 text-emerald-800">H {{ $agenda->hadir }}</span>
                            <span class="rounded-full bg-amber-100 px-2 py-0.5 text-amber-800">S {{ $agenda->sakit }}</span>
                            <span class="rounded-full bg-sky-100 px-2 py-0.5 text-sky-800">I {{ $agenda->izin }}</span>
                            <span class="rounded-full bg-rose-100 px-2 py-0.5 text-rose-800">A {{ $agenda->alfa }}</span>
                        </div>
                    </div>
                @empty
                    <p class="py-6 text-center text-sm text-slate-400">Belum ada presensi yang diisi hari ini.</p>
                @endforelse
            </div>
        </div>

        {{-- Peringatan --}}
        <div>
            <div class="kartu p-5">
                <h3 class="mb-4 flex items-center gap-2 font-bold text-primary">
                    <x-heroicon-o-exclamation-triangle class="h-5 w-5 text-amber-500"/> Perlu Perhatian
                </h3>

                @forelse ($peringatan->take(12) as $item)
                    <a href="{{ $item['url'] }}"
                       class="mb-2 block rounded-lg border border-slate-200 p-3 text-sm text-slate-700 hover:border-accent hover:bg-accent-50">
                        {{ $item['pesan'] }}
                    </a>
                @empty
                    <div class="py-8 text-center">
                        <x-heroicon-o-check-circle class="mx-auto h-10 w-10 text-emerald-300"/>
                        <p class="mt-2 text-sm text-slate-500">Semua administrasi sudah rapi.</p>
                    </div>
                @endforelse

                @if ($peringatan->count() > 12)
                    <p class="pt-1 text-center text-xs text-slate-400">
                        dan {{ $peringatan->count() - 12 }} peringatan lainnya
                    </p>
                @endif
            </div>
        </div>
    </div>
@endsection
