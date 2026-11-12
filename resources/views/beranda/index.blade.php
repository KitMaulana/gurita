@extends('layouts.app')
@section('judul', 'Beranda')

@php
    use App\Support\Tanggal;

    $jamSekarang = (int) now()->format('H');
    $sapaanWaktu = match (true) {
        $jamSekarang >= 4 && $jamSekarang < 11 => ['teks' => 'Selamat Pagi', 'emoji' => '☀️'],
        $jamSekarang >= 11 && $jamSekarang < 15 => ['teks' => 'Selamat Siang', 'emoji' => '🌤️'],
        $jamSekarang >= 15 && $jamSekarang < 18 => ['teks' => 'Selamat Sore', 'emoji' => '🌇'],
        default => ['teks' => 'Selamat Malam', 'emoji' => '🌙'],
    };

    $totalJadwalHariIni = $jadwalHariIni->count();
    $terisiHariIni = $jadwalHariIni->filter(fn ($j) => $agendaHariIni->has($j->id))->count();
    $persenSelesaiHariIni = $totalJadwalHariIni > 0 ? round(($terisiHariIni / $totalJadwalHariIni) * 100) : 0;
@endphp

@section('konten')
    {{-- HERO BANNER SAMBUTAN MODERN & ENERJIK --}}
    <div class="relative mb-6 overflow-hidden rounded-3xl bg-gradient-to-br from-slate-900 via-indigo-950 to-primary-700 p-6 sm:p-8 text-white shadow-xl shadow-indigo-950/15">
        {{-- Ornamen cahaya dekoratif --}}
        <div class="pointer-events-none absolute -right-12 -top-12 h-56 w-56 rounded-full bg-indigo-500/25 blur-3xl"></div>
        <div class="pointer-events-none absolute -bottom-16 right-28 h-48 w-48 rounded-full bg-teal-400/20 blur-3xl"></div>

        <div class="relative z-10 flex flex-col justify-between gap-6 lg:flex-row lg:items-center">
            <div class="max-w-2xl">
                <div class="inline-flex flex-wrap items-center gap-2 rounded-full border border-white/15 bg-white/10 px-3.5 py-1 text-xs font-bold text-indigo-100 backdrop-blur-xs">
                    <span>{{ $sapaanWaktu['emoji'] }}</span>
                    <span>{{ Tanggal::lengkap(now()) }}</span>
                    @if (\App\Support\JamPelajaran::isModeRamadhan())
                        <span class="rounded-full bg-emerald-400/25 px-2 py-0.5 text-[10px] font-extrabold text-emerald-200">Mode Ramadhan</span>
                    @endif
                </div>

                <h2 class="mt-3 text-2xl font-black tracking-tight sm:text-3xl">
                    {{ $sapaanWaktu['teks'] }}, <span class="bg-gradient-to-r from-white via-indigo-100 to-teal-200 bg-clip-text text-transparent">{{ $guru->name }}!</span>
                </h2>
                <p class="mt-1 text-xs sm:text-sm font-medium text-indigo-200/90">
                    {{ $guru->jabatan ?: 'Guru Mata Pelajaran' }}
                </p>

                @if ($guru->quote)
                    <div class="mt-4 inline-flex items-start gap-2.5 rounded-2xl border border-white/10 bg-white/5 px-4 py-2.5 text-xs sm:text-sm italic text-indigo-100 backdrop-blur-xs">
                        <span class="text-teal-300 not-italic font-black">“</span>
                        <span>{{ $guru->quote }}</span>
                        <span class="text-teal-300 not-italic font-black">”</span>
                    </div>
                @endif
            </div>

            {{-- Pintasan Aksi Cepat di Hero --}}
            <div class="flex flex-col gap-3 sm:flex-row lg:flex-col xl:flex-row shrink-0">
                <a href="{{ route('presensi.index') }}"
                   class="inline-flex items-center justify-center gap-2.5 rounded-2xl bg-gradient-to-r from-teal-400 to-emerald-500 px-5 py-3.5 text-xs sm:text-sm font-extrabold text-slate-950 shadow-lg shadow-teal-500/25 transition hover:-translate-y-0.5 hover:shadow-xl">
                    <x-heroicon-s-user-group class="h-5 w-5"/>
                    <span>Input Presensi Cepat</span>
                </a>
                <a href="{{ route('agenda.dari-jadwal') }}"
                   class="inline-flex items-center justify-center gap-2.5 rounded-2xl border border-white/20 bg-white/10 px-5 py-3.5 text-xs sm:text-sm font-extrabold text-white backdrop-blur-md transition hover:bg-white/20">
                    <x-heroicon-o-clipboard-document-list class="h-5 w-5 text-indigo-200"/>
                    <span>Isi Agenda Hari Ini</span>
                </a>
            </div>
        </div>

        {{-- Progress Bar Ringkas Jadwal Hari Ini (jika ada jadwal) --}}
        @if ($totalJadwalHariIni > 0)
            <div class="relative z-10 mt-6 border-t border-white/10 pt-4 flex flex-col sm:flex-row sm:items-center justify-between gap-3 text-xs">
                <div class="flex items-center gap-2 text-indigo-100">
                    <span class="inline-flex h-2 w-2 rounded-full bg-teal-400"></span>
                    <span>Progres Administrasi Mengajar Hari Ini: <strong class="text-white">{{ $terisiHariIni }} dari {{ $totalJadwalHariIni }} JP</strong> tercatat ({{ $persenSelesaiHariIni }}%)</span>
                </div>
                <div class="h-2 w-full sm:w-56 overflow-hidden rounded-full bg-white/15">
                    <div class="h-full rounded-full bg-gradient-to-r from-teal-400 to-emerald-400 transition-all duration-500"
                         style="width: {{ $persenSelesaiHariIni }}%"></div>
                </div>
            </div>
        @endif
    </div>

    {{-- 4 KARTU STATISTIK BENTO --}}
    <div class="mb-6 grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <x-kartu-statistik label="Kelas Diampu" :nilai="$statistik['kelas_diampu']"
                           keterangan="Rombel aktif semester ini"
                           ikon="building-office" warna="primary" :href="route('jadwal.index')"/>
        <x-kartu-statistik label="Materi Tersedia" :nilai="$statistik['materi_tersedia']"
                           keterangan="Bahan ajar & tautan modul"
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
                <div class="mb-5 flex flex-wrap items-center justify-between gap-2 border-b border-slate-100 pb-4">
                    <div class="flex items-center gap-3">
                        <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-primary-50 text-primary">
                            <x-heroicon-o-calendar-days class="h-5 w-5"/>
                        </div>
                        <div>
                            <div class="flex items-center gap-2">
                                <h3 class="text-base font-extrabold tracking-tight text-slate-900">Jadwal Hari Ini</h3>
                                @if (\App\Support\JamPelajaran::isModeRamadhan())
                                    <span class="inline-flex items-center gap-1 rounded-full bg-emerald-100 px-2.5 py-0.5 text-[10px] font-bold text-emerald-800 border border-emerald-200">
                                        🌙 Mode Ramadhan
                                    </span>
                                @endif
                            </div>
                            <p class="text-xs text-slate-500">Klik tombol di samping jadwal untuk langsung mengisi presensi atau agenda</p>
                        </div>
                    </div>
                    <a href="{{ route('agenda.dari-jadwal') }}"
                       class="inline-flex items-center gap-1 rounded-xl bg-accent-50 px-3 py-1.5 text-xs font-extrabold text-accent-700 transition hover:bg-accent hover:text-white">
                        <span>Buka Sesi Harian</span>
                        <span>→</span>
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
                        'mb-3 flex flex-col gap-3 rounded-2xl border p-4 sm:flex-row sm:items-center transition-all duration-200 hover:shadow-md',
                        'bg-gradient-to-r from-teal-50/70 to-white ring-2 ring-accent border-accent/40' => $sedangJalan,
                        'bg-white border-slate-200/80 hover:border-slate-300' => ! $sedangJalan,
                    ]) style="border-left: 5px solid {{ $warnaKelas['accent'] }};">

                        {{-- Badge JP --}}
                        <div class="flex items-center gap-3 sm:contents">
                            <div class="flex h-12 w-12 shrink-0 flex-col items-center justify-center rounded-xl shadow-xs"
                                 style="background-color: {{ $warnaKelas['jp_bg'] }}; color: {{ $warnaKelas['jp_text'] }};">
                                <span class="text-[9px] font-extrabold uppercase tracking-wider opacity-90 leading-none">JP</span>
                                <span class="text-base font-black leading-tight">{{ $jadwal->jam_ke }}</span>
                            </div>

                            <div class="min-w-0 flex-1">
                                <div class="flex flex-wrap items-center gap-2">
                                    <span class="inline-flex items-center gap-1.5 rounded-lg px-2.5 py-1 text-xs sm:text-sm font-black tracking-tight shadow-2xs"
                                          style="background-color: {{ $warnaKelas['badge_bg'] }}; color: {{ $warnaKelas['badge_text'] }}; border: 1.5px solid {{ $warnaKelas['border'] }};">
                                        <span class="h-2 w-2 rounded-full shrink-0" style="background-color: {{ $warnaKelas['accent'] }};"></span>
                                        <strong class="font-black">{{ $jadwal->kelas_tampilan }}</strong>
                                    </span>

                                    <span class="inline-flex items-center gap-1.5 rounded-full px-3 py-1 text-xs font-black shadow-2xs"
                                          style="background-color: {{ $warnaMapel['badge_bg'] }}; color: {{ $warnaMapel['badge_text'] }}; border: 1.5px solid {{ $warnaMapel['border'] }};">
                                        <span class="h-1.5 w-1.5 rounded-full shrink-0" style="background-color: {{ $warnaMapel['dot'] }};"></span>
                                        <strong class="font-black truncate">{{ $jadwal->nama_tampilan }}</strong>
                                    </span>

                                    @if ($sedangJalan)
                                        <span class="inline-flex items-center gap-1 rounded-full bg-rose-600 px-2.5 py-0.5 text-[10px] font-black text-white shadow-xs animate-pulse">
                                            <span class="h-1.5 w-1.5 rounded-full bg-white"></span>
                                            SEDANG BERLANGSUNG
                                        </span>
                                    @endif

                                    @if ($agenda)
                                        <span class="inline-flex items-center gap-1 rounded-full bg-emerald-100 px-2 py-0.5 text-[10px] font-extrabold text-emerald-800 border border-emerald-200">
                                            ✓ Agenda Tercatat
                                        </span>
                                    @endif
                                </div>

                                <div class="mt-2 flex flex-wrap items-center gap-2 text-xs font-bold text-slate-600">
                                    <span class="inline-flex items-center gap-1 text-slate-800">
                                        <x-heroicon-o-clock class="h-3.5 w-3.5 text-slate-500"/>
                                        <strong>{{ $jadwal->jam }} WIB</strong>
                                    </span>
                                    @if ($jadwal->ruang)
                                        <span class="text-slate-300">•</span>
                                        <span class="inline-flex items-center gap-1 text-slate-700">
                                            <x-heroicon-o-building-office class="h-3.5 w-3.5 text-slate-400"/>
                                            <span>Ruang {{ $jadwal->ruang }}</span>
                                        </span>
                                    @endif
                                    @if ($agenda)
                                        <span class="text-slate-300">•</span>
                                        <span class="truncate text-slate-500 font-medium max-w-xs" title="{{ $agenda->judul_materi }}">
                                            📖 {{ $agenda->judul_materi }}
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
                                <x-tombol gaya="aksen" :href="route('presensi.index', ['tanggal' => today()->toDateString(), 'jadwal_id' => $jadwal->id])" ikon="check-circle">
                                    Isi Presensi
                                </x-tombol>
                                <x-tombol gaya="garis" :href="route('agenda.dari-jadwal', ['tanggal' => today()->toDateString(), 'jadwal_id' => $jadwal->id])" ikon="plus" title="Isi Agenda Mengajar">
                                    Agenda
                                </x-tombol>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="rounded-2xl border border-dashed border-slate-200 bg-slate-50/50 py-12 px-4 text-center">
                        <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-indigo-50 text-primary">
                            <x-heroicon-o-sparkles class="h-7 w-7"/>
                        </div>
                        <p class="mt-3 text-base font-extrabold text-slate-800">Tidak ada jadwal mengajar hari ini 🎉</p>
                        <p class="mt-1 text-xs sm:text-sm text-slate-500 max-w-md mx-auto">
                            Gunakan waktu luang ini untuk menyiapkan bahan ajar di Bank Materi atau memperbarui penilaian siswa.
                        </p>
                        <div class="mt-4 flex flex-wrap justify-center gap-2">
                            <x-tombol gaya="garis" ikon="calendar-days" :href="route('jadwal.index')">Lihat Jadwal Mingguan</x-tombol>
                            <x-tombol gaya="aksen" ikon="academic-cap" :href="route('nilai.index')">Buka Daftar Nilai</x-tombol>
                        </div>
                    </div>
                @endforelse
            </div>

            {{-- Rekap Presensi Hari Ini --}}
            <div class="kartu p-5 sm:p-6">
                <div class="mb-4 flex items-center justify-between gap-2 border-b border-slate-100 pb-3.5">
                    <div class="flex items-center gap-2.5">
                        <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-emerald-50 text-emerald-600">
                            <x-heroicon-o-user-group class="h-5 w-5"/>
                        </div>
                        <div>
                            <h3 class="text-base font-extrabold tracking-tight text-slate-900">Rekap Presensi Hari Ini</h3>
                            <p class="text-xs text-slate-500">Ringkasan kehadiran siswa per sesi pertemuan hari ini</p>
                        </div>
                    </div>
                    <a href="{{ route('presensi.rekap') }}" class="text-xs font-extrabold text-primary hover:underline">
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
                       class="mb-2.5 flex flex-wrap items-center justify-between gap-3 rounded-2xl border border-slate-200/80 bg-white p-3.5 transition hover:border-primary/40 hover:bg-slate-50/70">
                        <div class="min-w-0 flex-1">
                            <div class="flex items-center gap-2">
                                <p class="truncate text-sm font-extrabold text-slate-900">
                                    {{ $agenda->jadwal?->kelas_tampilan ?? '—' }}
                                </p>
                                <span class="rounded-md bg-slate-100 px-2 py-0.5 text-[10px] font-bold text-slate-600">
                                    {{ $persenHadir }}% Hadir
                                </span>
                            </div>
                            <p class="truncate text-xs font-medium text-slate-500 mt-0.5">
                                {{ $agenda->jadwal?->nama_tampilan ?? '—' }} · {{ $agenda->judul_materi }}
                            </p>
                        </div>
                        <div class="flex flex-wrap gap-1.5 text-xs font-bold">
                            <span class="rounded-lg bg-emerald-100 px-2.5 py-1 text-emerald-800 border border-emerald-200">H {{ $agenda->hadir }}</span>
                            <span class="rounded-lg bg-amber-100 px-2.5 py-1 text-amber-800 border border-amber-200">S {{ $agenda->sakit }}</span>
                            <span class="rounded-lg bg-sky-100 px-2.5 py-1 text-sky-800 border border-sky-200">I {{ $agenda->izin }}</span>
                            <span class="rounded-lg bg-rose-100 px-2.5 py-1 text-rose-800 border border-rose-200">A {{ $agenda->alfa }}</span>
                        </div>
                    </a>
                @empty
                    <div class="rounded-2xl border border-dashed border-slate-200 py-8 text-center">
                        <p class="text-sm font-bold text-slate-600">Belum ada presensi yang diisi hari ini</p>
                        <p class="mt-0.5 text-xs text-slate-400">Pilih salah satu sesi kelas untuk mulai mencatat kehadiran siswa.</p>
                    </div>
                @endforelse
            </div>
        </div>

        {{-- KOLOM KANAN (1/3): ASISTEN PENGINGAT & MENU CEPAT --}}
        <div class="space-y-6">
            {{-- Kartu Perlu Perhatian --}}
            <div class="kartu p-5 sm:p-6">
                <div class="mb-4 flex items-center justify-between border-b border-slate-100 pb-3.5">
                    <h3 class="flex items-center gap-2 text-base font-extrabold tracking-tight text-slate-900">
                        <span class="flex h-8 w-8 items-center justify-center rounded-xl bg-amber-100 text-amber-600">
                            <x-heroicon-o-bell-alert class="h-4.5 w-4.5"/>
                        </span>
                        <span>Perlu Perhatian</span>
                    </h3>
                    @if ($peringatan->count() > 0)
                        <span class="rounded-full bg-amber-100 px-2.5 py-0.5 text-xs font-extrabold text-amber-800">
                            {{ $peringatan->count() }}
                        </span>
                    @endif
                </div>

                <div class="space-y-2">
                    @forelse ($peringatan->take(10) as $item)
                        <a href="{{ $item['url'] }}"
                           class="group flex items-start justify-between gap-2.5 rounded-xl border border-slate-200/80 bg-slate-50/50 p-3 text-xs sm:text-sm font-medium text-slate-700 transition hover:border-primary/40 hover:bg-primary-50/50 hover:text-primary">
                            <span class="leading-relaxed">{{ $item['pesan'] }}</span>
                            <span class="shrink-0 text-slate-400 transition group-hover:translate-x-0.5 group-hover:text-primary">→</span>
                        </a>
                    @empty
                        <div class="rounded-2xl bg-emerald-50/60 border border-emerald-100 py-8 px-4 text-center">
                            <x-heroicon-o-check-badge class="mx-auto h-10 w-10 text-emerald-500"/>
                            <p class="mt-2 text-sm font-extrabold text-emerald-950">Semua Administrasi Rapi! ✨</p>
                            <p class="mt-0.5 text-xs text-emerald-700">Agenda, presensi, dan nilai Anda sudah mutakhir.</p>
                        </div>
                    @endforelse
                </div>

                @if ($peringatan->count() > 10)
                    <p class="pt-3 text-center text-xs font-semibold text-slate-400">
                        + {{ $peringatan->count() - 10 }} pengingat lainnya
                    </p>
                @endif
            </div>

            {{-- Kartu Pintasan Fitur Populer --}}
            <div class="kartu p-5 sm:p-6">
                <h3 class="mb-3 text-xs font-extrabold uppercase tracking-wider text-slate-400">Akses Cepat Modul</h3>
                <div class="grid grid-cols-2 gap-2.5">
                    <a href="{{ route('nilai.index') }}" class="flex flex-col gap-1.5 rounded-2xl border border-slate-200/80 bg-slate-50/60 p-3.5 transition hover:border-primary/40 hover:bg-primary-50/40">
                        <x-heroicon-o-academic-cap class="h-5 w-5 text-primary"/>
                        <span class="text-xs font-extrabold text-slate-800">Grid Nilai</span>
                        <span class="text-[10px] text-slate-500">Edit nilai otomatis</span>
                    </a>
                    <a href="{{ route('rapor.index') }}" class="flex flex-col gap-1.5 rounded-2xl border border-slate-200/80 bg-slate-50/60 p-3.5 transition hover:border-primary/40 hover:bg-primary-50/40">
                        <x-heroicon-o-document-chart-bar class="h-5 w-5 text-teal-600"/>
                        <span class="text-xs font-extrabold text-slate-800">Rekap Rapor</span>
                        <span class="text-[10px] text-slate-500">Leger & Kartu PDF</span>
                    </a>
                    <a href="{{ route('bab.index') }}" class="flex flex-col gap-1.5 rounded-2xl border border-slate-200/80 bg-slate-50/60 p-3.5 transition hover:border-primary/40 hover:bg-primary-50/40">
                        <x-heroicon-o-book-open class="h-5 w-5 text-amber-600"/>
                        <span class="text-xs font-extrabold text-slate-800">Bab & TP</span>
                        <span class="text-[10px] text-slate-500">Struktur kurikulum</span>
                    </a>
                    <a href="{{ route('materi.index') }}" class="flex flex-col gap-1.5 rounded-2xl border border-slate-200/80 bg-slate-50/60 p-3.5 transition hover:border-primary/40 hover:bg-primary-50/40">
                        <x-heroicon-o-folder class="h-5 w-5 text-sky-600"/>
                        <span class="text-xs font-extrabold text-slate-800">Bank Materi</span>
                        <span class="text-[10px] text-slate-500">Modul & video ajar</span>
                    </a>
                </div>
            </div>
        </div>
    </div>
@endsection
