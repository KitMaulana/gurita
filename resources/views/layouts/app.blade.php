@php
    use App\Support\Tanggal;
    use App\Models\Pengaturan;

    $pengguna = auth()->user();
    $adaBuild = file_exists(public_path('build/manifest.json'));
    $adaCss = file_exists(public_path('css/app.css'));
    $namaSekolah = Pengaturan::ambil('nama_sekolah', 'SMA Negeri 1 Ciruas');

    $daftarMenuCepat = [
        ['judul' => 'Beranda', 'kategori' => 'Utama', 'ket' => 'Ringkasan jadwal, statistik & pengingat harian', 'url' => route('beranda'), 'ikon' => 'home'],
        ['judul' => 'Jadwal Mengajar', 'kategori' => 'Utama', 'ket' => 'Jadwal mingguan Senin–Sabtu & cetak PDF F4', 'url' => route('jadwal.index'), 'ikon' => 'calendar-days'],
        ['judul' => 'Isi Agenda dari Jadwal', 'kategori' => 'Pintasan', 'ket' => 'Catat pertemuan harian otomatis dari sesi jadwal', 'url' => route('agenda.dari-jadwal'), 'ikon' => 'bolt'],
        ['judul' => 'Agenda Mengajar', 'kategori' => 'Utama', 'ket' => 'Daftar seluruh catatan pertemuan & ekspor', 'url' => route('agenda.index'), 'ikon' => 'clipboard-document-list'],
        ['judul' => 'Input Presensi Siswa', 'kategori' => 'Pintasan', 'ket' => 'Tandai kehadiran siswa per sesi kelas', 'url' => route('presensi.index'), 'ikon' => 'user-group'],
        ['judul' => 'Rekap Kehadiran Siswa', 'kategori' => 'Akademik', 'ket' => 'Persentase kehadiran & cetak daftar hadir F4', 'url' => route('presensi.rekap'), 'ikon' => 'chart-bar'],
        ['judul' => 'Daftar Nilai', 'kategori' => 'Akademik', 'ket' => 'Input nilai formatif & sumatif simpan otomatis', 'url' => route('nilai.index'), 'ikon' => 'academic-cap'],
        ['judul' => 'Kelola Penilaian', 'kategori' => 'Akademik', 'ket' => 'Buat & atur penilaian formatif/sumatif/remedial', 'url' => route('penilaian.index'), 'ikon' => 'clipboard-document-check'],
        ['judul' => 'Bab & Tujuan Pembelajaran', 'kategori' => 'Akademik', 'ket' => 'Struktur CP, Bab & Tujuan Pembelajaran', 'url' => route('bab.index'), 'ikon' => 'book-open'],
        ['judul' => 'Bank Materi', 'kategori' => 'Akademik', 'ket' => 'Folder bahan ajar, modul PDF, PPT & video', 'url' => route('materi.index'), 'ikon' => 'folder'],
        ['judul' => 'Rekap Rapor & Leger', 'kategori' => 'Akademik', 'ket' => 'Bekukan rapor, deskripsi capaian & kartu nilai', 'url' => route('rapor.index'), 'ikon' => 'document-chart-bar'],
        ['judul' => 'Profil & Akun', 'kategori' => 'Akun', 'ket' => 'Ubah foto profil, jabatan, kutipan & sandi', 'url' => route('profile.edit'), 'ikon' => 'user-circle'],
    ];

    if ($pengguna?->isAdmin()) {
        $daftarMenuCepat = array_merge($daftarMenuCepat, [
            ['judul' => 'Master Tahun Ajaran', 'kategori' => 'Admin', 'ket' => 'Kelola semester aktif & salin data antar-semester', 'url' => route('admin.tahun-ajaran.index'), 'ikon' => 'calendar'],
            ['judul' => 'Master Mata Pelajaran', 'kategori' => 'Admin', 'ket' => 'Daftar mata pelajaran umum, pilihan & mulok', 'url' => route('admin.mata-pelajaran.index'), 'ikon' => 'book-open'],
            ['judul' => 'Master Kelas / Rombel', 'kategori' => 'Admin', 'ket' => 'Daftar kelas, wali kelas & anggota siswa', 'url' => route('admin.kelas.index'), 'ikon' => 'building-office'],
            ['judul' => 'Master Data Siswa', 'kategori' => 'Admin', 'ket' => 'Kelola siswa & impor massal CSV/Excel', 'url' => route('admin.siswa.index'), 'ikon' => 'users'],
            ['judul' => 'Kelola Jadwal Sekolah', 'kategori' => 'Admin', 'ket' => 'Atur jadwal guru, mode Ramadhan & impor CSV', 'url' => route('admin.jadwal.index'), 'ikon' => 'table-cells'],
            ['judul' => 'Pengguna & Hak Akses', 'kategori' => 'Admin', 'ket' => 'Kelola akun guru, wali kelas & admin', 'url' => route('admin.pengguna.index'), 'ikon' => 'identification'],
            ['judul' => 'Standar KKTP', 'kategori' => 'Admin', 'ket' => 'Atur batas ketuntasan per mapel & tingkat', 'url' => route('admin.kktp.index'), 'ikon' => 'check-badge'],
            ['judul' => 'Pengaturan Sekolah', 'kategori' => 'Admin', 'ket' => 'Identitas sekolah, bobot nilai & predikat', 'url' => route('admin.pengaturan.edit'), 'ikon' => 'cog-6-tooth'],
            ['judul' => 'Log Aktivitas Sistem', 'kategori' => 'Admin', 'ket' => 'Jejak audit perubahan data portal', 'url' => route('admin.log.index'), 'ikon' => 'clock'],
        ]);
    }
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
        <link rel="stylesheet" href="{{ asset('css/app.css') }}?v={{ filemtime(public_path('css/app.css')) }}">
    @else
        <script src="{{ asset('vendor/tailwind-play.js') }}"></script>
        <script>
            tailwind.config = {
                theme: {
                    extend: {
                        colors: {
                            primary: { DEFAULT: '#4F46E5', 50: '#EEF2FF', 100: '#E0E7FF', 500: '#6366F1', 600: '#4338CA', 700: '#3730A3', 800: '#312E81', 900: '#1E1B4B' },
                            accent:  { DEFAULT: '#0D9488', 50: '#F0FDFA', 100: '#CCFBF1', 500: '#14B8A6', 600: '#0F766E', 700: '#115E59' },
                        },
                        fontFamily: { sans: ['Plus Jakarta Sans', 'ui-sans-serif', 'system-ui', 'sans-serif'] },
                    },
                },
            };
        </script>
    @endif

    @livewireStyles

    <style>
        [x-cloak] { display: none !important; }
        @media print {
            .tanpa-cetak { display: none !important; }
        }

        /* Warna Status Presensi & Kehadiran */
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

        /* Status Legend Grid */
        .status-legend-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 10px;
        }
        @media (min-width: 640px) {
            .status-legend-grid { grid-template-columns: repeat(3, minmax(0, 1fr)); }
        }
        @media (min-width: 1024px) {
            .status-legend-grid { grid-template-columns: repeat(6, minmax(0, 1fr)); }
        }

        /* Sesi Pelajaran Cards — Minimalis Profesional */
        .sesi-section-header {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            margin-bottom: 14px;
        }
        .sesi-section-title-group {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .sesi-section-icon {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 36px;
            height: 36px;
            border-radius: 10px;
            background-color: #0f172a;
            color: #ffffff;
            flex-shrink: 0;
        }
        .sesi-cards-grid {
            display: grid;
            grid-template-columns: repeat(1, minmax(0, 1fr));
            gap: 14px;
        }
        @media (min-width: 640px) {
            .sesi-cards-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        }
        @media (min-width: 1024px) {
            .sesi-cards-grid { grid-template-columns: repeat(3, minmax(0, 1fr)); }
        }
        .sesi-card {
            position: relative;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            border-radius: 14px;
            background-color: #ffffff;
            border: 1px solid #e2e8f0;
            padding: 16px 16px 13px 16px;
            text-decoration: none !important;
            overflow: hidden;
            transition: border-color 0.15s ease, box-shadow 0.15s ease;
            box-shadow: 0 1px 2px rgba(15, 23, 42, 0.04);
        }
        .sesi-card::before {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 3px;
            background: var(--sesi-accent, #4F46E5);
        }
        .sesi-card:hover {
            border-color: #cbd5e1;
            box-shadow: 0 4px 12px -2px rgba(15, 23, 42, 0.07);
        }
        .sesi-card.is-active {
            background-color: #0f172a !important;
            border-color: #0f172a !important;
            color: #ffffff !important;
            box-shadow: 0 8px 20px -6px rgba(15, 23, 42, 0.25) !important;
        }
        .sesi-card.is-active::before {
            height: 3px;
            background: var(--sesi-accent, #818cf8);
        }
        .btn-filter-dark {
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;
            gap: 6px !important;
            white-space: nowrap !important;
            background-color: #0f172a !important;
            color: #ffffff !important;
            border: 1px solid #0f172a !important;
            border-radius: 8px !important;
            padding: 8px 14px !important;
            font-size: 12px !important;
            font-weight: 600 !important;
            line-height: 1.25 !important;
            transition: background-color 0.15s ease !important;
            cursor: pointer;
        }
        .btn-filter-dark:hover {
            background-color: #1e293b !important;
        }
        .btn-filter-light {
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;
            gap: 5px !important;
            white-space: nowrap !important;
            background-color: #ffffff !important;
            color: #334155 !important;
            border: 1px solid #cbd5e1 !important;
            border-radius: 8px !important;
            padding: 8px 12px !important;
            font-size: 12px !important;
            font-weight: 600 !important;
            line-height: 1.25 !important;
            transition: all 0.15s ease !important;
            text-decoration: none !important;
        }
        .btn-filter-light:hover {
            background-color: #f8fafc !important;
            border-color: #94a3b8 !important;
            color: #0f172a !important;
        }
        .status-legend-card {
            display: flex !important;
            align-items: center !important;
            gap: 10px !important;
            padding: 10px 12px !important;
            border-radius: 10px !important;
        }
        .status-legend-badge {
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;
            width: 32px !important;
            height: 32px !important;
            min-width: 32px !important;
            min-height: 32px !important;
            border-radius: 8px !important;
            font-size: 13px !important;
            font-weight: 800 !important;
            line-height: 1 !important;
            color: #ffffff !important;
            flex-shrink: 0 !important;
        }
    </style>
    @stack('kepala')
</head>
<body class="h-full bg-slate-50 font-sans text-slate-800 antialiased selection:bg-primary selection:text-white"
      x-data="{
          sidebarTerbuka: false,
          cariMenuTerbuka: false,
          kataKunciMenu: '',
          menuCepat: {{ Js::from($daftarMenuCepat) }},
          get hasilMenu() {
              const q = this.kataKunciMenu.toLowerCase().trim();
              if (!q) return this.menuCepat;
              return this.menuCepat.filter(m =>
                  m.judul.toLowerCase().includes(q) ||
                  m.ket.toLowerCase().includes(q) ||
                  m.kategori.toLowerCase().includes(q)
              );
          },
          bukaCariMenu() {
              this.cariMenuTerbuka = true;
              this.kataKunciMenu = '';
              this.$nextTick(() => {
                  if (this.$refs.inputCariMenu) this.$refs.inputCariMenu.focus();
              });
          }
      }"
      @keydown.window.ctrl.k.prevent="bukaCariMenu()"
      @keydown.window.meta.k.prevent="bukaCariMenu()">

<div class="min-h-full lg:flex">

    {{-- Overlay drawer di layar < 1024px --}}
    <div x-cloak x-show="sidebarTerbuka" x-transition.opacity
         @click="sidebarTerbuka = false"
         class="fixed inset-0 z-30 bg-slate-900/40 backdrop-blur-xs lg:hidden"></div>

    {{-- SIDEBAR MINIMALIS --}}
    <aside :class="sidebarTerbuka ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'"
           class="tanpa-cetak fixed inset-y-0 left-0 z-40 flex w-64 -translate-x-full flex-col border-r border-slate-200 bg-white transition-transform duration-200 lg:static lg:translate-x-0">

        {{-- Brand Header --}}
        <div class="flex h-14 items-center justify-between border-b border-slate-200/80 px-4">
            <a href="{{ route('beranda') }}" class="flex items-center gap-2.5 min-w-0">
                <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-slate-900 text-xs font-bold text-white">
                    PG
                </div>
                <div class="min-w-0">
                    <p class="truncate text-sm font-bold tracking-tight text-slate-900">Portal Guru</p>
                    <p class="truncate text-[10px] font-medium text-slate-500">{{ $namaSekolah }}</p>
                </div>
            </a>
            <button type="button" @click="sidebarTerbuka = false"
                    class="rounded-md p-1 text-slate-400 hover:bg-slate-100 hover:text-slate-700 lg:hidden"
                    aria-label="Tutup menu">
                <x-heroicon-o-x-mark class="h-5 w-5"/>
            </button>
        </div>

        {{-- Kartu Ringkas Akun & Tahun Ajaran --}}
        <div class="border-b border-slate-100 px-4 py-3.5">
            <div class="flex items-center gap-2.5">
                @if ($pengguna?->foto)
                    <img src="{{ $pengguna->foto_url ?? Storage::url($pengguna->foto) }}" alt="Foto {{ $pengguna->name }}"
                         class="h-9 w-9 shrink-0 rounded-full object-cover ring-1 ring-slate-200"
                         onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                    <div class="hidden h-9 w-9 shrink-0 items-center justify-center rounded-full bg-slate-900 text-xs font-bold text-white">
                        {{ $pengguna?->inisial }}
                    </div>
                @else
                    <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-slate-900 text-xs font-bold text-white">
                        {{ $pengguna?->inisial }}
                    </div>
                @endif
                <div class="min-w-0 flex-1">
                    <p class="truncate text-xs font-semibold text-slate-900">{{ $pengguna?->name }}</p>
                    <p class="truncate text-[11px] text-slate-500">{{ $pengguna?->jabatan ?: 'Guru Mata Pelajaran' }}</p>
                </div>
            </div>

            <div class="mt-2.5 flex items-center justify-between rounded-lg bg-slate-50 px-2.5 py-1.5 text-[11px] border border-slate-200/70">
                <span class="text-slate-500">Tahun Aktif</span>
                <span class="inline-flex items-center gap-1.5 font-semibold text-slate-800 whitespace-nowrap">
                    <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                    {{ $tahunAjaranAktif?->label ?? 'Belum diatur' }}
                </span>
            </div>
        </div>

        {{-- Navigasi Menu --}}
        <nav @click="if (window.innerWidth < 1024) sidebarTerbuka = false" class="flex-1 space-y-0.5 overflow-y-auto px-3 py-3 text-sm">
            <p class="px-3 pb-1 text-[10px] font-semibold uppercase tracking-wider text-slate-400">Utama</p>
            <x-menu-sidebar :href="route('beranda')" :aktif="request()->routeIs('beranda')" ikon="home">Beranda</x-menu-sidebar>
            <x-menu-sidebar :href="route('jadwal.index')" :aktif="request()->routeIs('jadwal.*')" ikon="calendar-days">Jadwal Mengajar</x-menu-sidebar>
            <x-menu-sidebar :href="route('agenda.index')" :aktif="request()->routeIs('agenda.*')" ikon="clipboard-document-list">Agenda Mengajar</x-menu-sidebar>
            <x-menu-sidebar :href="route('presensi.index')" :aktif="request()->routeIs('presensi.*')" ikon="user-group">Presensi Siswa</x-menu-sidebar>

            <p class="px-3 pb-1 pt-4 text-[10px] font-semibold uppercase tracking-wider text-slate-400">Penilaian & Materi</p>
            <x-menu-sidebar :href="route('nilai.index')" :aktif="request()->routeIs('nilai.*') || request()->routeIs('penilaian.*')" ikon="academic-cap">Daftar Nilai</x-menu-sidebar>
            <x-menu-sidebar :href="route('materi.index')" :aktif="request()->routeIs('materi.*')" ikon="folder">Bank Materi</x-menu-sidebar>
            <x-menu-sidebar :href="route('bab.index')" :aktif="request()->routeIs('bab.*')" ikon="book-open">Bab & Tujuan (TP)</x-menu-sidebar>
            <x-menu-sidebar :href="route('rapor.index')" :aktif="request()->routeIs('rapor.*')" ikon="document-chart-bar">Rekap Rapor</x-menu-sidebar>

            @if ($pengguna?->isAdmin())
                <p class="px-3 pb-1 pt-4 text-[10px] font-semibold uppercase tracking-wider text-slate-400">Administrasi</p>
                <x-menu-sidebar :href="route('admin.tahun-ajaran.index')" :aktif="request()->routeIs('admin.tahun-ajaran.*')" ikon="calendar">Tahun Ajaran</x-menu-sidebar>
                <x-menu-sidebar :href="route('admin.mata-pelajaran.index')" :aktif="request()->routeIs('admin.mata-pelajaran.*')" ikon="book-open">Mata Pelajaran</x-menu-sidebar>
                <x-menu-sidebar :href="route('admin.kelas.index')" :aktif="request()->routeIs('admin.kelas.*')" ikon="building-office">Kelas / Rombel</x-menu-sidebar>
                <x-menu-sidebar :href="route('admin.siswa.index')" :aktif="request()->routeIs('admin.siswa.*')" ikon="users">Data Siswa</x-menu-sidebar>
                <x-menu-sidebar :href="route('admin.jadwal.index')" :aktif="request()->routeIs('admin.jadwal.*')" ikon="table-cells">Kelola Jadwal</x-menu-sidebar>
                <x-menu-sidebar :href="route('admin.pengguna.index')" :aktif="request()->routeIs('admin.pengguna.*')" ikon="identification">Pengguna & Peran</x-menu-sidebar>
                <x-menu-sidebar :href="route('admin.kktp.index')" :aktif="request()->routeIs('admin.kktp.*')" ikon="check-badge">Standar KKTP</x-menu-sidebar>
                <x-menu-sidebar :href="route('admin.pengaturan.edit')" :aktif="request()->routeIs('admin.pengaturan.*')" ikon="cog-6-tooth">Pengaturan Sekolah</x-menu-sidebar>
                <x-menu-sidebar :href="route('admin.log.index')" :aktif="request()->routeIs('admin.log.*')" ikon="clock">Log Aktivitas</x-menu-sidebar>
            @endif
        </nav>

        {{-- Footer Akun Sidebar --}}
        <div class="border-t border-slate-200/80 p-3 space-y-0.5">
            <a href="{{ route('profile.edit') }}" wire:navigate.hover
               @class([
                   'flex items-center gap-2.5 rounded-lg px-3 py-2 text-xs font-medium transition-colors',
                   'bg-indigo-50 text-primary font-semibold' => request()->routeIs('profile.*'),
                   'text-slate-600 hover:bg-slate-100 hover:text-slate-900' => ! request()->routeIs('profile.*'),
               ])>
                <x-heroicon-o-user-circle class="h-4 w-4 shrink-0"/>
                <span>Profil & Akun</span>
            </a>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit"
                        class="flex w-full cursor-pointer items-center gap-2.5 rounded-lg px-3 py-2 text-xs font-medium text-rose-600 transition-colors hover:bg-rose-50">
                    <x-heroicon-o-arrow-right-on-rectangle class="h-4 w-4 shrink-0"/>
                    <span>Keluar</span>
                </button>
            </form>
        </div>
    </aside>

    {{-- KONTEN UTAMA --}}
    <div class="flex min-w-0 flex-1 flex-col pb-16 lg:pb-0">
        {{-- Topbar Clean --}}
        <header class="tanpa-cetak sticky top-0 z-20 flex h-14 items-center gap-3 border-b border-slate-200 bg-white/95 px-4 backdrop-blur-sm lg:px-8">
            <button type="button" @click="sidebarTerbuka = true"
                    class="inline-flex items-center justify-center rounded-lg border border-slate-200 bg-white p-1.5 text-slate-600 hover:bg-slate-50 lg:hidden"
                    aria-label="Buka menu">
                <x-heroicon-o-bars-3 class="h-5 w-5"/>
            </button>

            <div class="min-w-0 flex-1 flex items-center gap-3">
                <h1 class="truncate text-sm font-bold tracking-tight text-slate-900 sm:text-base">@yield('judul', 'Beranda')</h1>
                <span class="hidden sm:inline text-slate-300">|</span>
                <div class="hidden sm:flex items-center gap-2 text-xs text-slate-500 whitespace-nowrap"
                     x-data="{ jam: '' }"
                     x-init="
                         const updateJam = () => {
                             const d = new Date();
                             jam = d.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });
                         };
                         updateJam();
                         setInterval(updateJam, 15000);
                     ">
                    <span>{{ Tanggal::lengkap(now()) }}</span>
                    <span class="text-slate-300">·</span>
                    <span class="font-medium text-slate-700 tabular-nums" x-show="jam" x-cloak x-text="jam + ' WIB'"></span>
                </div>
                @if (\App\Support\JamPelajaran::isModeRamadhan())
                    <span class="hidden md:inline-flex items-center gap-1 rounded-md bg-emerald-50 px-2 py-0.5 text-[11px] font-semibold text-emerald-700 border border-emerald-200 whitespace-nowrap">
                        Mode Ramadhan
                    </span>
                @endif
            </div>

            {{-- Pencarian Cepat Menu (Ctrl+K) --}}
            <button type="button" @click="bukaCariMenu()"
                    class="hidden md:inline-flex cursor-pointer items-center gap-2 rounded-lg border border-slate-200 bg-slate-50 px-3 py-1.5 text-xs text-slate-500 transition-colors hover:border-slate-300 hover:bg-white hover:text-slate-700 whitespace-nowrap">
                <x-heroicon-o-magnifying-glass class="h-3.5 w-3.5 text-slate-400"/>
                <span>Cari menu...</span>
                <kbd class="rounded border border-slate-200 bg-white px-1.5 py-0.5 font-sans text-[10px] font-medium text-slate-400">Ctrl K</kbd>
            </button>

            {{-- Dropdown Pintasan --}}
            <div class="relative" x-data="{ bukaPintasan: false }">
                <button type="button" @click="bukaPintasan = !bukaPintasan"
                        class="inline-flex cursor-pointer items-center gap-1.5 rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-xs font-semibold text-slate-700 transition-colors hover:bg-slate-50 whitespace-nowrap"
                        title="Pintasan Cepat">
                    <x-heroicon-o-bolt class="h-3.5 w-3.5 text-primary"/>
                    <span class="hidden sm:inline">Pintasan</span>
                </button>

                <div x-cloak x-show="bukaPintasan" @click.outside="bukaPintasan = false"
                     x-transition:enter="transition ease-out duration-150"
                     x-transition:enter-start="opacity-0 translate-y-1"
                     x-transition:enter-end="opacity-100 translate-y-0"
                     class="absolute right-0 mt-2 w-64 rounded-xl border border-slate-200 bg-white p-1.5 shadow-lg">
                    <p class="px-2.5 py-1.5 text-[10px] font-semibold uppercase tracking-wider text-slate-400">Pintasan Mengajar</p>
                    <a href="{{ route('presensi.index') }}" class="flex items-center gap-2.5 rounded-lg px-2.5 py-2 text-xs font-medium text-slate-700 hover:bg-slate-100">
                        <x-heroicon-o-user-group class="h-4 w-4 text-emerald-600 shrink-0"/>
                        <div>
                            <p class="font-semibold text-slate-800">Input Presensi Hari Ini</p>
                            <p class="text-[11px] text-slate-400">Kehadiran per sesi kelas</p>
                        </div>
                    </a>
                    <a href="{{ route('agenda.dari-jadwal') }}" class="flex items-center gap-2.5 rounded-lg px-2.5 py-2 text-xs font-medium text-slate-700 hover:bg-slate-100">
                        <x-heroicon-o-clipboard-document-list class="h-4 w-4 text-primary shrink-0"/>
                        <div>
                            <p class="font-semibold text-slate-800">Catat Agenda Mengajar</p>
                            <p class="text-[11px] text-slate-400">Sesuai jadwal kelas hari ini</p>
                        </div>
                    </a>
                    <a href="{{ route('nilai.index') }}" class="flex items-center gap-2.5 rounded-lg px-2.5 py-2 text-xs font-medium text-slate-700 hover:bg-slate-100">
                        <x-heroicon-o-academic-cap class="h-4 w-4 text-amber-600 shrink-0"/>
                        <div>
                            <p class="font-semibold text-slate-800">Isi Daftar Nilai</p>
                            <p class="text-[11px] text-slate-400">Formatif & Sumatif otomatis</p>
                        </div>
                    </a>
                    <a href="{{ route('penilaian.create') }}" class="flex items-center gap-2.5 rounded-lg px-2.5 py-2 text-xs font-medium text-slate-700 hover:bg-slate-100">
                        <x-heroicon-o-plus-circle class="h-4 w-4 text-sky-600 shrink-0"/>
                        <div>
                            <p class="font-semibold text-slate-800">Buat Penilaian Baru</p>
                            <p class="text-[11px] text-slate-400">Tambah kolom tugas / ujian</p>
                        </div>
                    </a>
                </div>
            </div>

            {{-- Notifikasi / Peringatan --}}
            @php $peringatan = $peringatanGlobal ?? collect(); @endphp
            <div class="relative" x-data="{ buka: false }">
                <button type="button" @click="buka = !buka"
                        class="relative inline-flex cursor-pointer items-center justify-center rounded-lg border border-slate-200 bg-white p-1.5 text-slate-600 transition-colors hover:bg-slate-50 hover:text-slate-900"
                        aria-label="Notifikasi">
                    <x-heroicon-o-bell class="h-4.5 w-4.5"/>
                    @if ($peringatan->count())
                        <span class="absolute -right-1 -top-1 flex h-4 min-w-4 items-center justify-center rounded-full bg-rose-600 px-1 text-[9px] font-bold text-white ring-2 ring-white">
                            {{ $peringatan->count() > 9 ? '9+' : $peringatan->count() }}
                        </span>
                    @endif
                </button>
                <div x-cloak x-show="buka" @click.outside="buka = false" x-transition
                     class="absolute right-0 mt-2 w-80 sm:w-96 rounded-xl border border-slate-200 bg-white p-2.5 shadow-lg">
                    <div class="flex items-center justify-between border-b border-slate-100 px-2 pb-2 mb-1.5">
                        <p class="text-xs font-semibold text-slate-800">Pengingat Administrasi</p>
                        @if ($peringatan->count())
                            <span class="rounded-md bg-rose-50 px-2 py-0.5 text-[10px] font-semibold text-rose-700">{{ $peringatan->count() }} item</span>
                        @endif
                    </div>
                    <div class="max-h-72 space-y-1 overflow-y-auto">
                        @forelse ($peringatan as $item)
                            <a href="{{ $item['url'] ?? '#' }}" wire:navigate
                               class="flex items-start gap-2.5 rounded-lg p-2 text-xs text-slate-700 transition-colors hover:bg-slate-100">
                                <x-heroicon-o-exclamation-triangle class="mt-0.5 h-4 w-4 text-amber-600 shrink-0"/>
                                <span class="leading-relaxed">{{ $item['pesan'] }}</span>
                            </a>
                        @empty
                            <div class="py-6 text-center">
                                <x-heroicon-o-check-badge class="mx-auto h-7 w-7 text-emerald-500"/>
                                <p class="mt-1.5 text-xs font-semibold text-slate-700">Semua administrasi mutakhir</p>
                                <p class="text-[11px] text-slate-400">Tidak ada tanggungan tugas saat ini.</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </header>

        <main class="flex-1 px-4 py-6 lg:px-8">
            <x-notifikasi/>
            <div>
                @yield('konten')
            </div>
        </main>

        <footer class="tanpa-cetak border-t border-slate-200 bg-white px-4 py-3.5 text-xs text-slate-400 lg:px-8">
            <div class="flex flex-col sm:flex-row items-center justify-between gap-2">
                <span>© {{ date('Y') }} <strong class="font-medium text-slate-600">{{ $namaSekolah }}</strong> — {{ config('app.name') }}</span>
                <span class="text-[11px] text-slate-400">Tekan <kbd class="rounded border border-slate-200 bg-slate-50 px-1.5 py-0.5 font-sans text-[10px] font-medium text-slate-500">Ctrl K</kbd> untuk navigasi cepat</span>
            </div>
        </footer>
    </div>
</div>

{{-- MOBILE BOTTOM NAVIGATION BAR (< 1024px) --}}
<nav class="tanpa-cetak fixed bottom-0 inset-x-0 z-30 flex items-center justify-around border-t border-slate-200 bg-white/95 px-1 py-1.5 backdrop-blur-sm lg:hidden">
    <a href="{{ route('beranda') }}"
       class="flex flex-1 flex-col items-center justify-center gap-0.5 py-1 text-[10px] font-semibold {{ request()->routeIs('beranda') ? 'text-primary' : 'text-slate-500' }}">
        <x-heroicon-o-home class="h-5 w-5"/>
        <span>Beranda</span>
    </a>
    <a href="{{ route('jadwal.index') }}"
       class="flex flex-1 flex-col items-center justify-center gap-0.5 py-1 text-[10px] font-semibold {{ request()->routeIs('jadwal.*') ? 'text-primary' : 'text-slate-500' }}">
        <x-heroicon-o-calendar-days class="h-5 w-5"/>
        <span>Jadwal</span>
    </a>
    <a href="{{ route('presensi.index') }}"
       class="flex flex-1 flex-col items-center justify-center gap-0.5 py-1 text-[10px] font-semibold {{ request()->routeIs('presensi.*') ? 'text-primary' : 'text-slate-500' }}">
        <x-heroicon-o-user-group class="h-5 w-5"/>
        <span>Presensi</span>
    </a>
    <a href="{{ route('agenda.index') }}"
       class="flex flex-1 flex-col items-center justify-center gap-0.5 py-1 text-[10px] font-semibold {{ request()->routeIs('agenda.*') ? 'text-primary' : 'text-slate-500' }}">
        <x-heroicon-o-clipboard-document-list class="h-5 w-5"/>
        <span>Agenda</span>
    </a>
    <button type="button" @click="sidebarTerbuka = true"
            class="flex flex-1 flex-col items-center justify-center gap-0.5 py-1 text-[10px] font-semibold text-slate-500">
        <x-heroicon-o-bars-3 class="h-5 w-5"/>
        <span>Menu</span>
    </button>
</nav>

{{-- MODAL PENCARIAN CEPAT / COMMAND PALETTE (Ctrl + K) --}}
<div x-cloak x-show="cariMenuTerbuka"
     @keydown.escape.window="cariMenuTerbuka = false"
     class="tanpa-cetak fixed inset-0 z-50 flex items-start justify-center bg-slate-900/40 p-4 pt-16 sm:pt-24 backdrop-blur-xs">
    <div x-show="cariMenuTerbuka"
         x-transition:enter="transition ease-out duration-150"
         x-transition:enter-start="opacity-0 scale-98"
         x-transition:enter-end="opacity-100 scale-100"
         @click.outside="cariMenuTerbuka = false"
         class="w-full max-w-lg overflow-hidden rounded-xl border border-slate-200 bg-white shadow-xl">
        <div class="flex items-center gap-3 border-b border-slate-100 px-4 py-3">
            <x-heroicon-o-magnifying-glass class="h-4 w-4 text-slate-400 shrink-0"/>
            <input x-ref="inputCariMenu" x-model="kataKunciMenu" type="text"
                   placeholder="Ketik nama menu atau fitur..."
                   class="w-full border-0 bg-transparent p-0 text-sm text-slate-800 placeholder:text-slate-400 focus:outline-none focus:ring-0">
            <button type="button" @click="cariMenuTerbuka = false"
                    class="rounded border border-slate-200 bg-slate-50 px-1.5 py-0.5 text-[10px] font-medium text-slate-500 hover:bg-slate-100">
                ESC
            </button>
        </div>
        <div class="max-h-80 overflow-y-auto p-1.5 space-y-0.5">
            <template x-for="(item, idx) in hasilMenu" :key="idx">
                <a :href="item.url"
                   class="flex items-center justify-between gap-3 rounded-lg px-3 py-2 text-left transition-colors hover:bg-slate-100 group">
                    <div class="min-w-0 flex-1">
                        <div class="flex items-center gap-2">
                            <span class="text-sm font-medium text-slate-800 group-hover:text-slate-950" x-text="item.judul"></span>
                            <span class="rounded bg-slate-100 px-1.5 py-0.5 text-[10px] font-medium text-slate-500" x-text="item.kategori"></span>
                        </div>
                        <p class="truncate text-xs text-slate-400" x-text="item.ket"></p>
                    </div>
                    <x-heroicon-o-chevron-right class="h-4 w-4 shrink-0 text-slate-300 group-hover:text-slate-500"/>
                </a>
            </template>
            <div x-show="hasilMenu.length === 0" class="py-8 text-center text-xs text-slate-400">
                Tidak ditemukan menu "<span class="font-medium text-slate-600" x-text="kataKunciMenu"></span>"
            </div>
        </div>
    </div>
</div>

@livewireScripts
@stack('skrip')
</body>
</html>
