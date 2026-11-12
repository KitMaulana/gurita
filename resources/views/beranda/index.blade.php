@extends('layouts.app')
@section('judul', 'Beranda')

@php
    use App\Support\Tanggal;

    $jamSekarang = (int) now()->format('H');
    $sapaanWaktu = match (true) {
        $jamSekarang >= 4 && $jamSekarang < 11 => 'Selamat Pagi',
        $jamSekarang >= 11 && $jamSekarang < 15 => 'Selamat Siang',
        $jamSekarang >= 15 && $jamSekarang < 18 => 'Selamat Sore',
        default => 'Selamat Malam',
    };

    $totalJadwalHariIni = $jadwalHariIni->count();
    $terisiHariIni = $jadwalHariIni->filter(fn ($j) => $agendaHariIni->has($j->id))->count();
    $persenSelesaiHariIni = $totalJadwalHariIni > 0 ? round(($terisiHariIni / $totalJadwalHariIni) * 100) : 0;
@endphp

@section('konten')
    {{-- HEADER SAMBUTAN MINIMALIS PROFESIONAL --}}
    <div class="kartu mb-6 p-5 sm:p-6">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            <div class="min-w-0">
                <div class="flex flex-wrap items-center gap-2 text-xs font-medium text-slate-500">
                    <span class="inline-flex items-center gap-1.5">
                        <x-heroicon-o-calendar-days class="h-4 w-4 text-slate-400"/>
                        {{ Tanggal::lengkap(now()) }}
                    </span>
                    @if (\App\Support\JamPelajaran::isModeRamadhan())
                        <span class="inline-flex items-center rounded-md bg-emerald-50 px-2 py-0.5 text-[11px] font-semibold text-emerald-700 border border-emerald-200">
                            Mode Ramadhan
                        </span>
                    @endif
                </div>

                <h2 class="mt-1.5 text-xl font-bold tracking-tight text-slate-900 sm:text-2xl">
                    {{ $sapaanWaktu }}, {{ $guru->name }}
                </h2>
                <p class="mt-0.5 text-xs sm:text-sm text-slate-500">
                    {{ $guru->jabatan ?: 'Guru Mata Pelajaran' }}
                </p>
            </div>

            {{-- Pintasan Aksi Simetris --}}
            <div class="flex flex-wrap items-center gap-2.5 shrink-0">
                <x-tombol gaya="aksen" ikon="user-group" :href="route('presensi.index')">
                    Input Presensi
                </x-tombol>
                <x-tombol gaya="garis" ikon="clipboard-document-list" :href="route('agenda.dari-jadwal')">
                    Isi Agenda Hari Ini
                </x-tombol>
            </div>
        </div>

        @if ($guru->quote || $totalJadwalHariIni > 0)
            <div class="mt-5 flex flex-col gap-3 border-t border-slate-100 pt-4 sm:flex-row sm:items-center sm:justify-between text-xs">
                @if ($guru->quote)
                    <p class="border-l-2 border-slate-300 pl-3 italic text-slate-500 max-w-xl">
                        “{{ $guru->quote }}”
                    </p>
                @else
                    <span></span>
                @endif

                @if ($totalJadwalHariIni > 0)
                    <div class="flex items-center gap-3 shrink-0">
                        <span class="text-slate-500 whitespace-nowrap">
                            Progres Hari Ini: <strong class="font-semibold text-slate-800">{{ $terisiHariIni }}/{{ $totalJadwalHariIni }} JP</strong> ({{ $persenSelesaiHariIni }}%)
                        </span>
                        <div class="h-1.5 w-28 overflow-hidden rounded-full bg-slate-100">
                            <div class="h-full rounded-full bg-emerald-600 transition-all duration-300"
                                 style="width: {{ $persenSelesaiHariIni }}%"></div>
                        </div>
                    </div>
                @endif
            </div>
        @endif
    </div>

    {{-- 4 KARTU STATISTIK --}}
    <div class="mb-6 grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <x-kartu-statistik label="Kelas Diampu" :nilai="$statistik['kelas_diampu']"
                           keterangan="Rombel aktif semester ini"
                           ikon="building-office" warna="primary" :href="route('jadwal.index')"/>
        <x-kartu-statistik label="Materi Tersedia" :nilai="$statistik['materi_tersedia']"
                           keterangan="Bahan ajar & modul"
                           ikon="folder" warna="accent" :href="route('materi.index')"/>
        <x-kartu-statistik label="Total Siswa" :nilai="$statistik['total_siswa']"
                           keterangan="Siswa di kelas ampu Anda"
                           ikon="users" warna="sky" :href="route('presensi.rekap')"/>
        <x-kartu-statistik label="Jadwal Hari Ini" :nilai="$statistik['jadwal_hari_ini'].' JP'"
                           keterangan="Target jam pelajaran hari ini"
                           ikon="calendar-days" warna="amber" :href="route('agenda.dari-jadwal')"/>
    </div>

    <div class="grid gap-6 lg:grid-cols-3">
        {{-- KOLOM KIRI (2/3): JADWAL HARI INI & REKAP PRESENSI --}}
        <div class="lg:col-span-2 space-y-6">
            {{-- Jadwal Hari Ini --}}
            <div class="kartu p-5 sm:p-6">
                <div class="mb-4 flex flex-wrap items-center justify-between gap-2 border-b border-slate-100 pb-3.5">
                    <div>
                        <div class="flex items-center gap-2">
                            <h3 class="text-base font-bold tracking-tight text-slate-900">Jadwal Hari Ini</h3>
                            @if (\App\Support\JamPelajaran::isModeRamadhan())
                                <span class="rounded-md bg-emerald-50 px-2 py-0.5 text-[11px] font-semibold text-emerald-700 border border-emerald-200 whitespace-nowrap">
                                    Mode Ramadhan
                                </span>
                            @endif
                        </div>
                        <p class="text-xs text-slate-500">Pilih sesi kelas untuk mengisi presensi atau agenda pertemuan</p>
                    </div>
                    <a href="{{ route('agenda.dari-jadwal') }}"
                       class="inline-flex items-center gap-1 text-xs font-semibold text-primary hover:underline whitespace-nowrap">
                        <span>Buka Sesi Harian</span>
                        <x-heroicon-o-arrow-right class="h-3.5 w-3.5"/>
                    </a>
                </div>

                @forelse ($jadwalHariIni as $jadwal)
                    @php
                        $agenda = $agendaHariIni->get($jadwal->id);
                        $warnaKelas = \App\Support\WarnaKelas::untuk($jadwal->kelas_tampilan, $jadwal->isAgendaBersama());
                        $warnaMapel = \App\Support\WarnaMapel::untuk($jadwal->nama_tampilan, $jadwal->isAgendaBersama());
                        $sedangJalan = $jadwal->sedangBerlangsung();
                    @endphp
                    <div @class([
                        'mb-3 flex flex-col gap-3 rounded-xl border p-4 sm:flex-row sm:items-center transition-colors',
                        'bg-indigo-50/30 border-primary/40 ring-1 ring-primary/20' => $sedangJalan,
                        'bg-white border-slate-200 hover:border-slate-300' => ! $sedangJalan,
                    ]) style="border-left: 4px solid {{ $warnaKelas['accent'] }};">

                        <div class="flex items-center gap-3.5 min-w-0 flex-1">
                            <div class="flex h-11 w-11 shrink-0 flex-col items-center justify-center rounded-lg"
                                 style="background-color: {{ $warnaKelas['jp_bg'] }}; color: {{ $warnaKelas['jp_text'] }};">
                                <span class="text-[9px] font-bold uppercase leading-none opacity-90">JP</span>
                                <span class="text-sm font-bold leading-tight">{{ $jadwal->jam_ke }}</span>
                            </div>

                            <div class="min-w-0 flex-1">
                                <div class="flex flex-wrap items-center gap-1.5">
                                    <span class="inline-flex items-center gap-1.5 rounded-md px-2 py-0.5 text-xs font-bold whitespace-nowrap shrink-0"
                                          style="background-color: {{ $warnaKelas['badge_bg'] }}; color: {{ $warnaKelas['badge_text'] }}; border: 1px solid {{ $warnaKelas['border'] }};">
                                        <span class="h-1.5 w-1.5 rounded-full shrink-0" style="background-color: {{ $warnaKelas['accent'] }};"></span>
                                        {{ $jadwal->kelas_tampilan }}
                                    </span>

                                    <span class="inline-flex items-center gap-1.5 rounded-md px-2.5 py-0.5 text-xs font-semibold whitespace-nowrap shrink-0"
                                          style="background-color: {{ $warnaMapel['badge_bg'] }}; color: {{ $warnaMapel['badge_text'] }}; border: 1px solid {{ $warnaMapel['border'] }};">
                                        {{ $jadwal->nama_tampilan }}
                                    </span>

                                    @if ($sedangJalan)
                                        <span class="inline-flex items-center gap-1 rounded-md bg-rose-600 px-2 py-0.5 text-[10px] font-bold text-white whitespace-nowrap shrink-0">
                                            Berlangsung
                                        </span>
                                    @endif

                                    @if ($agenda)
                                        <span class="inline-flex items-center gap-1 rounded-md bg-emerald-50 px-2 py-0.5 text-[10px] font-semibold text-emerald-700 border border-emerald-200 whitespace-nowrap shrink-0">
                                            Agenda Tercatat
                                        </span>
                                    @endif
                                </div>

                                <div class="mt-1.5 flex flex-wrap items-center gap-2 text-xs text-slate-500">
                                    <span class="inline-flex items-center gap-1 font-medium text-slate-700 whitespace-nowrap">
                                        <x-heroicon-o-clock class="h-3.5 w-3.5 text-slate-400"/>
                                        {{ $jadwal->jam }} WIB
                                    </span>
                                    @if ($jadwal->ruang)
                                        <span class="text-slate-300">·</span>
                                        <span class="whitespace-nowrap">Ruang {{ $jadwal->ruang }}</span>
                                    @endif
                                    @if ($agenda)
                                        <span class="text-slate-300">·</span>
                                        <span class="truncate text-slate-500 max-w-xs" title="{{ $agenda->judul_materi }}">
                                            {{ $agenda->judul_materi }}
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <div class="shrink-0 flex items-center gap-2 pt-2 sm:pt-0 border-t sm:border-t-0 border-slate-100">
                            @if ($agenda)
                                <x-tombol gaya="garis" :href="route('presensi.isi', $agenda)" ikon="user-group">
                                    {{ $agenda->presensiSudahDiisi() ? 'Lihat Presensi' : 'Isi Presensi' }}
                                </x-tombol>
                            @else
                                <x-tombol gaya="utama" :href="route('presensi.index', ['tanggal' => today()->toDateString(), 'jadwal_id' => $jadwal->id])" ikon="check-circle">
                                    Isi Presensi
                                </x-tombol>
                                <x-tombol gaya="garis" :href="route('agenda.dari-jadwal', ['tanggal' => today()->toDateString(), 'jadwal_id' => $jadwal->id])" ikon="plus" title="Isi Agenda Mengajar">
                                    Agenda
                                </x-tombol>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="rounded-xl border border-dashed border-slate-200 bg-slate-50/50 py-10 px-4 text-center">
                        <x-heroicon-o-calendar-days class="mx-auto h-9 w-9 text-slate-300"/>
                        <p class="mt-2.5 text-sm font-semibold text-slate-800">Tidak ada jadwal mengajar hari ini</p>
                        <p class="mt-0.5 text-xs text-slate-500 max-w-md mx-auto">
                            Lihat jadwal mingguan atau buka daftar nilai untuk memperbarui penilaian siswa.
                        </p>
                        <div class="mt-4 flex flex-wrap justify-center gap-2">
                            <x-tombol gaya="garis" ikon="calendar-days" :href="route('jadwal.index')">Jadwal Mingguan</x-tombol>
                            <x-tombol gaya="utama" ikon="academic-cap" :href="route('nilai.index')">Daftar Nilai</x-tombol>
                        </div>
                    </div>
                @endforelse
            </div>

            {{-- Rekap Presensi Hari Ini --}}
            <div class="kartu p-5 sm:p-6">
                <div class="mb-4 flex items-center justify-between gap-2 border-b border-slate-100 pb-3.5">
                    <div>
                        <h3 class="text-base font-bold tracking-tight text-slate-900">Rekap Presensi Hari Ini</h3>
                        <p class="text-xs text-slate-500">Ringkasan kehadiran siswa per sesi pertemuan hari ini</p>
                    </div>
                    <a href="{{ route('presensi.rekap') }}" class="text-xs font-semibold text-primary hover:underline whitespace-nowrap">
                        Rekap Bulanan →
                    </a>
                </div>

                @forelse ($rekapHariIni as $agenda)
                    @php
                        $totalHadir = (int) $agenda->hadir;
                        $totalSemua = max(1, $totalHadir + (int) $agenda->sakit + (int) $agenda->izin + (int) $agenda->alfa);
                        $persenHadir = round(($totalHadir / $totalSemua) * 100);
                    @endphp
                    <a href="{{ route('presensi.isi', $agenda) }}"
                       class="mb-2.5 flex flex-wrap items-center justify-between gap-3 rounded-xl border border-slate-200 bg-white p-3.5 transition-colors hover:border-slate-300 hover:bg-slate-50/70">
                        <div class="min-w-0 flex-1">
                            <div class="flex items-center gap-2">
                                <p class="truncate text-sm font-semibold text-slate-900">
                                    {{ $agenda->jadwal?->kelas_tampilan ?? '—' }}
                                </p>
                                <span class="rounded-md bg-slate-100 px-2 py-0.5 text-[10px] font-semibold text-slate-600 whitespace-nowrap shrink-0">
                                    {{ $persenHadir }}% Hadir
                                </span>
                            </div>
                            <p class="truncate text-xs text-slate-500 mt-0.5">
                                {{ $agenda->jadwal?->nama_tampilan ?? '—' }} · {{ $agenda->judul_materi }}
                            </p>
                        </div>
                        <div class="flex items-center gap-1.5 flex-nowrap shrink-0 text-xs font-semibold">
                            <span class="rounded-md bg-emerald-100 px-2 py-1 text-emerald-800 whitespace-nowrap">H {{ $agenda->hadir }}</span>
                            <span class="rounded-md bg-amber-100 px-2 py-1 text-amber-800 whitespace-nowrap">S {{ $agenda->sakit }}</span>
                            <span class="rounded-md bg-sky-100 px-2 py-1 text-sky-800 whitespace-nowrap">I {{ $agenda->izin }}</span>
                            <span class="rounded-md bg-rose-100 px-2 py-1 text-rose-800 whitespace-nowrap">A {{ $agenda->alfa }}</span>
                        </div>
                    </a>
                @empty
                    <div class="rounded-xl border border-dashed border-slate-200 py-8 text-center">
                        <p class="text-sm font-medium text-slate-600">Belum ada presensi yang diisi hari ini</p>
                        <p class="mt-0.5 text-xs text-slate-400">Pilih sesi kelas untuk mencatat kehadiran siswa.</p>
                    </div>
                @endforelse
            </div>
        </div>

        {{-- KOLOM KANAN (1/3): PENGINGAT & PINTASAN MODUL --}}
        <div class="space-y-6">
            {{-- Kartu Perlu Perhatian --}}
            <div class="kartu p-5 sm:p-6">
                <div class="mb-4 flex items-center justify-between border-b border-slate-100 pb-3">
                    <h3 class="flex items-center gap-2 text-base font-bold tracking-tight text-slate-900">
                        <x-heroicon-o-bell-alert class="h-4.5 w-4.5 text-amber-600"/>
                        <span>Perlu Perhatian</span>
                    </h3>
                    @if ($peringatan->count() > 0)
                        <span class="rounded-md bg-amber-100 px-2 py-0.5 text-xs font-semibold text-amber-800 tabular-nums">
                            {{ $peringatan->count() }}
                        </span>
                    @endif
                </div>

                <div class="space-y-2">
                    @forelse ($peringatan->take(10) as $item)
                        <a href="{{ $item['url'] }}"
                           class="group flex items-start justify-between gap-2.5 rounded-lg border border-slate-200 bg-slate-50/50 p-3 text-xs sm:text-sm text-slate-700 transition-colors hover:border-slate-300 hover:bg-slate-50 hover:text-slate-950">
                            <span class="leading-relaxed">{{ $item['pesan'] }}</span>
                            <x-heroicon-o-chevron-right class="mt-0.5 h-4 w-4 shrink-0 text-slate-400 group-hover:text-slate-600"/>
                        </a>
                    @empty
                        <div class="rounded-xl bg-emerald-50/60 border border-emerald-200/70 py-7 px-4 text-center">
                            <x-heroicon-o-check-badge class="mx-auto h-8 w-8 text-emerald-600"/>
                            <p class="mt-2 text-sm font-semibold text-emerald-900">Semua administrasi rapi</p>
                            <p class="mt-0.5 text-xs text-emerald-700">Agenda, presensi, dan nilai Anda sudah mutakhir.</p>
                        </div>
                    @endforelse
                </div>

                @if ($peringatan->count() > 10)
                    <p class="pt-3 text-center text-xs text-slate-400">
                        + {{ $peringatan->count() - 10 }} pengingat lainnya
                    </p>
                @endif
            </div>

            {{-- Kartu Akses Cepat Modul --}}
            <div class="kartu p-5 sm:p-6">
                <h3 class="mb-3 text-xs font-semibold uppercase tracking-wider text-slate-400">Akses Cepat Modul</h3>
                <div class="grid grid-cols-2 gap-2.5">
                    <a href="{{ route('nilai.index') }}" class="flex flex-col gap-1 rounded-xl border border-slate-200 bg-slate-50/60 p-3.5 transition-colors hover:border-slate-300 hover:bg-white">
                        <x-heroicon-o-academic-cap class="h-5 w-5 text-primary"/>
                        <span class="text-xs font-semibold text-slate-900 mt-1">Grid Nilai</span>
                        <span class="text-[11px] text-slate-500">Simpan otomatis</span>
                    </a>
                    <a href="{{ route('rapor.index') }}" class="flex flex-col gap-1 rounded-xl border border-slate-200 bg-slate-50/60 p-3.5 transition-colors hover:border-slate-300 hover:bg-white">
                        <x-heroicon-o-document-chart-bar class="h-5 w-5 text-teal-600"/>
                        <span class="text-xs font-semibold text-slate-900 mt-1">Rekap Rapor</span>
                        <span class="text-[11px] text-slate-500">Leger & Kartu PDF</span>
                    </a>
                    <a href="{{ route('bab.index') }}" class="flex flex-col gap-1 rounded-xl border border-slate-200 bg-slate-50/60 p-3.5 transition-colors hover:border-slate-300 hover:bg-white">
                        <x-heroicon-o-book-open class="h-5 w-5 text-amber-600"/>
                        <span class="text-xs font-semibold text-slate-900 mt-1">Bab & TP</span>
                        <span class="text-[11px] text-slate-500">Struktur materi</span>
                    </a>
                    <a href="{{ route('materi.index') }}" class="flex flex-col gap-1 rounded-xl border border-slate-200 bg-slate-50/60 p-3.5 transition-colors hover:border-slate-300 hover:bg-white">
                        <x-heroicon-o-folder class="h-5 w-5 text-sky-600"/>
                        <span class="text-xs font-semibold text-slate-900 mt-1">Bank Materi</span>
                        <span class="text-[11px] text-slate-500">Modul & video ajar</span>
                    </a>
                </div>
            </div>
        </div>
    </div>
@endsection
