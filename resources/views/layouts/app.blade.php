@php
    use App\Support\Tanggal;
    use App\Models\Pengaturan;

    $pengguna = auth()->user();
    $adaBuild = file_exists(public_path('build/manifest.json'));
    $adaCss = file_exists(public_path('css/app.css'));
@endphp
<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('judul', 'Beranda') — {{ config('app.name') }}</title>

    <link rel="dns-prefetch" href="https://fonts.bunny.net">
    <link rel="preconnect" href="https://fonts.bunny.net" crossorigin>
    <link href="https://fonts.bunny.net/css?family=plus-jakarta-sans:400,500,600,700,800&display=swap" rel="stylesheet">

    @if ($adaBuild)
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @elseif ($adaCss)
        <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    @else
        {{-- Fallback tanpa Node/Vite: Tailwind Play + Alpine dilayani dari folder publik (offline-aman). --}}
        <script src="{{ asset('vendor/tailwind-play.js') }}"></script>
        <script>
            tailwind.config = {
                theme: {
                    extend: {
                        colors: {
                            primary: { DEFAULT: '#1A365D', 50: '#EEF3F9', 100: '#D6E2F0', 500: '#24548F', 600: '#2D4A6F', 700: '#1A365D', 800: '#142B4A', 900: '#0E1F36' },
                            accent:  { DEFAULT: '#7C9885', 50: '#F1F5F2', 100: '#DFE9E2', 500: '#7C9885', 600: '#6B8874', 700: '#587260' },
                        },
                        fontFamily: { sans: ['Plus Jakarta Sans', 'ui-sans-serif', 'system-ui', 'sans-serif'] },
                        keyframes: { fadeIn: { '0%': { opacity: '0', transform: 'translateY(8px)' }, '100%': { opacity: '1', transform: 'translateY(0)' } } },
                        animation: { 'fade-in': 'fadeIn 0.4s ease-out both' },
                    },
                },
            };
        </script>
        <style type="text/tailwindcss">
            @layer components {
                .kartu { @apply rounded-xl border border-slate-200 bg-white shadow-sm transition-all duration-200; }
                .kartu-hover:hover { @apply -translate-y-1 shadow-lg; }
                .nilai-merah { @apply bg-red-50 text-red-700 font-semibold; }
                .tombol-sentuh { min-height: 44px; min-width: 44px; }
            }
        </style>
    @endif

    {{-- Livewire 3 sudah membundel Alpine; dipasang manual agar semua halaman
         memakai satu instans Alpine yang sama (bukan hanya halaman ber-Livewire). --}}
    @livewireStyles

    <style>
        [x-cloak] { display: none !important; }
        @media print {
            .tanpa-cetak { display: none !important; }
        }

        /* Warna Status Presensi & Kehadiran (Aman dari Tailwind Purge) */
        .bg-emerald-100 { background-color: #d1fae5 !important; }
        .text-emerald-800 { color: #065f46 !important; }
        .border-emerald-200 { border-color: #a7f3d0 !important; }

        .bg-amber-100 { background-color: #fef3c7 !important; }
        .text-amber-800 { color: #92400e !important; }
        .border-amber-200 { border-color: #fde68a !important; }

        .bg-sky-100 { background-color: #e0f2fe !important; }
        .text-sky-800 { color: #075985 !important; }
        .border-sky-200 { border-color: #bae6fd !important; }

        .bg-rose-100 { background-color: #ffe4e6 !important; }
        .text-rose-800 { color: #9f1239 !important; }
        .border-rose-200 { border-color: #fecdd3 !important; }

        .bg-red-200 { background-color: #fee2e2 !important; }
        .text-red-900 { color: #991b1b !important; }
        .border-red-300 { border-color: #fca5a5 !important; }

        .bg-violet-100 { background-color: #ede9fe !important; }
        .text-violet-800 { color: #5b21b6 !important; }
        .border-violet-200 { border-color: #ddd6fe !important; }

        /* Solid badge colors */
        .bg-emerald-600 { background-color: #059669 !important; color: #ffffff !important; }
        .bg-amber-500 { background-color: #d97706 !important; color: #ffffff !important; }
        .bg-sky-500 { background-color: #0284c7 !important; color: #ffffff !important; }
        .bg-rose-500 { background-color: #e11d48 !important; color: #ffffff !important; }
        .bg-red-700 { background-color: #dc2626 !important; color: #ffffff !important; }
        .bg-violet-600 { background-color: #7c3aed !important; color: #ffffff !important; }

        /* Status Legend Aesthetic Badges & Cards */
        .status-legend-card {
            display: flex !important;
            align-items: center !important;
            gap: 12px !important;
            padding: 10px 14px !important;
            border-radius: 12px !important;
            transition: all 0.2s ease !important;
        }
        .status-legend-card:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
        }
        .status-legend-badge {
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;
            width: 36px !important;
            height: 36px !important;
            min-width: 36px !important;
            min-height: 36px !important;
            border-radius: 10px !important;
            font-size: 15px !important;
            font-weight: 800 !important;
            line-height: 1 !important;
            text-align: center !important;
            color: #ffffff !important;
            flex-shrink: 0 !important;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.12) !important;
        }
    </style>
    @stack('kepala')
</head>
<body class="h-full bg-slate-50 font-sans text-slate-800 antialiased">
<div x-data="{ sidebarTerbuka: false }" class="min-h-full lg:flex">

    {{-- Overlay drawer di layar < 1024px --}}
    <div x-cloak x-show="sidebarTerbuka" x-transition.opacity
         @click="sidebarTerbuka = false"
         class="fixed inset-0 z-30 bg-slate-900/40 backdrop-blur-sm lg:hidden"></div>

    {{-- SIDEBAR --}}
    {{-- Kelas dasar sudah menyembunyikan drawer di ponsel, sehingga tidak ada
         kedipan sebelum Alpine selesai dimuat (tanpa perlu x-cloak). --}}
    <aside :class="sidebarTerbuka ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'"
           class="tanpa-cetak fixed inset-y-0 left-0 z-40 flex w-72 -translate-x-full flex-col border-r border-slate-200 bg-white transition-transform duration-200 lg:static lg:translate-x-0">

        {{-- Header profil bergradasi --}}
        <div class="bg-gradient-to-br from-[#1A365D] to-[#2D4A6F] px-5 py-6 text-white">
            <div class="flex items-center gap-3">
                @if ($pengguna?->foto)
                    <img src="{{ Storage::url($pengguna->foto) }}" alt="Foto {{ $pengguna->name }}"
                         class="h-12 w-12 rounded-full object-cover ring-2 ring-white/40">
                @else
                    <div class="flex h-12 w-12 items-center justify-center rounded-full bg-white/15 text-lg font-bold ring-2 ring-white/30">
                        {{ $pengguna?->inisial }}
                    </div>
                @endif
                <div class="min-w-0">
                    <p class="truncate text-sm font-bold leading-tight">{{ $pengguna?->name }}</p>
                    <p class="truncate text-xs text-white/70">{{ $pengguna?->jabatan ?: 'Guru' }}</p>
                </div>
            </div>
            <p class="mt-4 text-[11px] uppercase tracking-wide text-white/60">Tahun Ajaran</p>
            <p class="text-sm font-semibold">
                {{ $tahunAjaranAktif?->label ?? 'Belum diatur' }}
            </p>
        </div>

        {{-- Menu --}}
        <nav @click="if (window.innerWidth < 1024) sidebarTerbuka = false" class="flex-1 space-y-1 overflow-y-auto px-3 py-4 text-sm">
            <x-menu-sidebar :href="route('beranda')" :aktif="request()->routeIs('beranda')" ikon="home">Beranda</x-menu-sidebar>
            <x-menu-sidebar :href="route('jadwal.index')" :aktif="request()->routeIs('jadwal.*')" ikon="calendar-days">Jadwal Mengajar</x-menu-sidebar>
            <x-menu-sidebar :href="route('agenda.index')" :aktif="request()->routeIs('agenda.*')" ikon="clipboard-document-list">Agenda Mengajar</x-menu-sidebar>
            <x-menu-sidebar :href="route('presensi.index')" :aktif="request()->routeIs('presensi.*')" ikon="user-group">Presensi Siswa</x-menu-sidebar>
            <x-menu-sidebar :href="route('nilai.index')" :aktif="request()->routeIs('nilai.*') || request()->routeIs('penilaian.*')" ikon="academic-cap">Daftar Nilai</x-menu-sidebar>
            <x-menu-sidebar :href="route('materi.index')" :aktif="request()->routeIs('materi.*')" ikon="folder">Bank Materi</x-menu-sidebar>
            <x-menu-sidebar :href="route('rapor.index')" :aktif="request()->routeIs('rapor.*')" ikon="document-chart-bar">Rekap Rapor</x-menu-sidebar>
            <x-menu-sidebar :href="route('bab.index')" :aktif="request()->routeIs('bab.*')" ikon="book-open">Bab & Tujuan Pembelajaran</x-menu-sidebar>

            @if ($pengguna?->isAdmin())
                <p class="px-3 pb-1 pt-5 text-[11px] font-bold uppercase tracking-wider text-slate-400">Administrasi</p>
                <x-menu-sidebar :href="route('admin.tahun-ajaran.index')" :aktif="request()->routeIs('admin.tahun-ajaran.*')" ikon="calendar">Tahun Ajaran</x-menu-sidebar>
                <x-menu-sidebar :href="route('admin.mata-pelajaran.index')" :aktif="request()->routeIs('admin.mata-pelajaran.*')" ikon="book-open">Mata Pelajaran</x-menu-sidebar>
                <x-menu-sidebar :href="route('admin.kelas.index')" :aktif="request()->routeIs('admin.kelas.*')" ikon="building-office">Kelas</x-menu-sidebar>
                <x-menu-sidebar :href="route('admin.siswa.index')" :aktif="request()->routeIs('admin.siswa.*')" ikon="users">Siswa</x-menu-sidebar>
                <x-menu-sidebar :href="route('admin.jadwal.index')" :aktif="request()->routeIs('admin.jadwal.*')" ikon="table-cells">Kelola Jadwal</x-menu-sidebar>
                <x-menu-sidebar :href="route('admin.pengguna.index')" :aktif="request()->routeIs('admin.pengguna.*')" ikon="identification">Pengguna & Peran</x-menu-sidebar>
                <x-menu-sidebar :href="route('admin.kktp.index')" :aktif="request()->routeIs('admin.kktp.*')" ikon="check-badge">KKTP</x-menu-sidebar>
                <x-menu-sidebar :href="route('admin.pengaturan.edit')" :aktif="request()->routeIs('admin.pengaturan.*')" ikon="cog-6-tooth">Pengaturan Sekolah</x-menu-sidebar>
                <x-menu-sidebar :href="route('admin.log.index')" :aktif="request()->routeIs('admin.log.*')" ikon="clock">Log Aktivitas</x-menu-sidebar>
            @endif
        </nav>

        <div class="border-t border-slate-200 p-3">
            <a href="{{ route('profile.edit') }}" wire:navigate.hover
               class="flex items-center gap-2 rounded-lg px-3 py-2 text-sm text-slate-600 hover:bg-slate-50">
                <x-heroicon-o-user-circle class="h-5 w-5"/> Profil Saya
            </a>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit"
                        class="flex w-full items-center gap-2 rounded-lg px-3 py-2 text-sm text-rose-600 hover:bg-rose-50">
                    <x-heroicon-o-arrow-right-on-rectangle class="h-5 w-5"/> Keluar
                </button>
            </form>
        </div>
    </aside>

    {{-- KONTEN --}}
    <div class="flex min-w-0 flex-1 flex-col">
        {{-- Topbar --}}
        <header class="tanpa-cetak sticky top-0 z-20 flex items-center gap-3 border-b border-slate-200 bg-white px-4 py-3 lg:px-8">
            <button type="button" @click="sidebarTerbuka = true"
                    class="tombol-sentuh rounded-lg p-2 text-slate-600 hover:bg-slate-100 lg:hidden"
                    aria-label="Buka menu">
                <x-heroicon-o-bars-3 class="h-6 w-6"/>
            </button>

            <div class="min-w-0 flex-1">
                <h1 class="truncate text-lg font-bold text-primary">@yield('judul', 'Beranda')</h1>
                <p class="truncate text-xs text-slate-500">{{ Tanggal::lengkap(now()) }}</p>
            </div>

            @php $peringatan = $peringatanGlobal ?? collect(); @endphp
            <div class="relative" x-data="{ buka: false }">
                <button type="button" @click="buka = !buka"
                        class="tombol-sentuh relative rounded-lg p-2 text-slate-600 hover:bg-slate-100"
                        aria-label="Notifikasi">
                    <x-heroicon-o-bell class="h-6 w-6"/>
                    @if ($peringatan->count())
                        <span class="absolute right-1.5 top-1.5 flex h-2.5 w-2.5 rounded-full bg-rose-500 ring-2 ring-white"></span>
                    @endif
                </button>
                <div x-cloak x-show="buka" @click.outside="buka = false" x-transition
                     class="absolute right-0 mt-2 w-80 rounded-xl border border-slate-200 bg-white p-2 shadow-lg">
                    <p class="px-3 py-2 text-xs font-bold uppercase tracking-wide text-slate-400">Peringatan</p>
                    @forelse ($peringatan as $item)
                        <a href="{{ $item['url'] ?? '#' }}" wire:navigate class="block rounded-lg px-3 py-2 text-sm hover:bg-slate-50">
                            {{ $item['pesan'] }}
                        </a>
                    @empty
                        <p class="px-3 py-3 text-sm text-slate-500">Tidak ada peringatan.</p>
                    @endforelse
                </div>
            </div>
        </header>

        <main class="flex-1 px-4 py-6 lg:px-8">
            <x-notifikasi/>
            <div>
                @yield('konten')
            </div>
        </main>

        <footer class="tanpa-cetak border-t border-slate-200 px-4 py-4 text-center text-xs text-slate-400 lg:px-8">
            {{ Pengaturan::ambil('nama_sekolah') }} — {{ config('app.name') }}
        </footer>
    </div>
</div>

@livewireScripts
@stack('skrip')
</body>
</html>
