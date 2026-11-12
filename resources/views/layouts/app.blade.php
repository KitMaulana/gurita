@php
    use App\Support\Tanggal;
    use App\Models\Pengaturan;

    $pengguna = auth()->user();
    $adaBuild = file_exists(public_path('build/manifest.json'));
    $adaCss = file_exists(public_path('css/app.css'));
    $namaSekolah = Pengaturan::ambil('nama_sekolah', 'SMA Negeri 1 Ciruas');

    $daftarMenuCepat = [
        ['judul' => 'Beranda / Dashboard', 'kategori' => 'Utama', 'ket' => 'Ringkasan jadwal, statistik & pengingat harian', 'url' => route('beranda'), 'ikon' => 'home'],
        ['judul' => 'Jadwal Mengajar', 'kategori' => 'Utama', 'ket' => 'Jadwal mingguan Senin–Sabtu & cetak PDF F4', 'url' => route('jadwal.index'), 'ikon' => 'calendar-days'],
        ['judul' => 'Isi Agenda dari Jadwal', 'kategori' => 'Aksi Cepat', 'ket' => 'Catat pertemuan harian otomatis dari sesi jadwal', 'url' => route('agenda.dari-jadwal'), 'ikon' => 'bolt'],
        ['judul' => 'Riwayat Agenda Mengajar', 'kategori' => 'Utama', 'ket' => 'Daftar seluruh catatan pertemuan & ekspor', 'url' => route('agenda.index'), 'ikon' => 'clipboard-document-list'],
        ['judul' => 'Input Presensi Siswa', 'kategori' => 'Aksi Cepat', 'ket' => 'Tandai kehadiran siswa 1-klik per sesi kelas', 'url' => route('presensi.index'), 'ikon' => 'user-group'],
        ['judul' => 'Rekap Kehadiran Siswa', 'kategori' => 'Akademik', 'ket' => 'Persentase kehadiran & cetak daftar hadir F4', 'url' => route('presensi.rekap'), 'ikon' => 'chart-bar'],
        ['judul' => 'Daftar Nilai (Grid Livewire)', 'kategori' => 'Akademik', 'ket' => 'Input nilai formatif & sumatif simpan otomatis', 'url' => route('nilai.index'), 'ikon' => 'academic-cap'],
        ['judul' => 'Kelola Penilaian / Asesmen', 'kategori' => 'Akademik', 'ket' => 'Buat & atur penilaian formatif/sumatif/remedial', 'url' => route('penilaian.index'), 'ikon' => 'clipboard-document-check'],
        ['judul' => 'Bab & Tujuan Pembelajaran (TP)', 'kategori' => 'Akademik', 'ket' => 'Struktur CP, Bab & Tujuan Pembelajaran', 'url' => route('bab.index'), 'ikon' => 'book-open'],
        ['judul' => 'Bank Materi Pembelajaran', 'kategori' => 'Akademik', 'ket' => 'Folder bahan ajar, modul PDF, PPT & video', 'url' => route('materi.index'), 'ikon' => 'folder'],
        ['judul' => 'Rekap Rapor & Leger Nilai', 'kategori' => 'Akademik', 'ket' => 'Bekukan rapor, deskripsi capaian & kartu nilai', 'url' => route('rapor.index'), 'ikon' => 'document-chart-bar'],
        ['judul' => 'Profil & Kata Sandi Saya', 'kategori' => 'Akun', 'ket' => 'Ubah foto profil, jabatan, kutipan motivasi & sandi', 'url' => route('profile.edit'), 'ikon' => 'user-circle'],
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
        {{-- Fallback tanpa Node/Vite: Tailwind Play + Alpine dilayani dari folder publik (offline-aman). --}}
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
                        keyframes: { fadeIn: { '0%': { opacity: '0', transform: 'translateY(8px)' }, '100%': { opacity: '1', transform: 'translateY(0)' } } },
                        animation: { 'fade-in': 'fadeIn 0.4s ease-out both' },
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

        /* Modern Sesi Pelajaran Cards (Global) */
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
            width: 38px;
            height: 38px;
            border-radius: 12px;
            background: linear-gradient(135deg, #4F46E5 0%, #6366F1 100%);
            color: #ffffff;
            box-shadow: 0 4px 10px rgba(79, 70, 229, 0.25);
            flex-shrink: 0;
        }
        .sesi-cards-grid {
            display: grid;
            grid-template-columns: repeat(1, minmax(0, 1fr));
            gap: 16px;
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
            border-radius: 18px;
            background-color: #ffffff;
            border: 1.5px solid #e2e8f0;
            padding: 18px 18px 14px 18px;
            text-decoration: none !important;
            overflow: hidden;
            transition: all 0.24s cubic-bezier(0.16, 1, 0.3, 1);
            box-shadow: 0 3px 12px -2px rgba(15, 23, 42, 0.06), 0 1px 3px -1px rgba(15, 23, 42, 0.04);
        }
        .sesi-card::before {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 5px;
            background: var(--sesi-accent, #4F46E5);
            transition: height 0.2s ease;
        }
        .sesi-card:hover {
            transform: translateY(-4px);
            border-color: var(--sesi-accent, #818cf8);
            box-shadow: 0 14px 28px -6px rgba(79, 70, 229, 0.14), 0 4px 10px -2px rgba(15, 23, 42, 0.05);
        }
        .sesi-card:hover .sesi-card-cta-arrow {
            transform: translateX(4px);
        }
        .sesi-card.is-active {
            background: linear-gradient(135deg, #1E1B4B 0%, #3730A3 55%, #4F46E5 100%) !important;
            border-color: #4F46E5 !important;
            color: #ffffff !important;
            box-shadow: 0 16px 32px -8px rgba(79, 70, 229, 0.42), 0 0 0 2px var(--sesi-accent, #818cf8) !important;
            transform: translateY(-2px);
        }
        .sesi-card.is-active::before {
            height: 5px;
            background: linear-gradient(90deg, var(--sesi-accent, #38bdf8), #ffffff, var(--sesi-accent, #38bdf8));
        }
        .btn-filter-dark {
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;
            gap: 6px !important;
            background: linear-gradient(135deg, #4F46E5 0%, #4338CA 100%) !important;
            color: #ffffff !important;
            border: 1px solid #3730A3 !important;
            border-radius: 12px !important;
            padding: 8px 14px !important;
            font-size: 12px !important;
            font-weight: 700 !important;
            line-height: 1.25 !important;
            box-shadow: 0 2px 6px rgba(79, 70, 229, 0.25) !important;
            transition: all 0.18s ease !important;
            cursor: pointer;
        }
        .btn-filter-dark:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 10px rgba(79, 70, 229, 0.35) !important;
        }
        .btn-filter-light {
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;
            gap: 5px !important;
            background-color: #ffffff !important;
            color: #334155 !important;
            border: 1px solid #cbd5e1 !important;
            border-radius: 12px !important;
            padding: 8px 12px !important;
            font-size: 12px !important;
            font-weight: 700 !important;
            line-height: 1.25 !important;
            box-shadow: 0 1px 2px rgba(15, 23, 42, 0.04) !important;
            transition: all 0.18s ease !important;
            text-decoration: none !important;
        }
        .btn-filter-light:hover {
            background-color: #f1f5f9 !important;
            border-color: #818cf8 !important;
            color: #4F46E5 !important;
        }
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
         class="fixed inset-0 z-30 bg-slate-900/50 backdrop-blur-xs lg:hidden"></div>

    {{-- SIDEBAR --}}
    <aside :class="sidebarTerbuka ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'"
           class="tanpa-cetak fixed inset-y-0 left-0 z-40 flex w-72 -translate-x-full flex-col border-r border-slate-200/80 bg-white shadow-xl transition-transform duration-200 lg:static lg:translate-x-0 lg:shadow-none">

        {{-- Brand Header & Kartu Profil Modern --}}
        <div class="relative overflow-hidden bg-gradient-to-br from-slate-900 via-indigo-950 to-primary-800 px-5 pb-5 pt-5 text-white">
            {{-- Ornamen cahaya dekoratif --}}
            <div class="pointer-events-none absolute -right-8 -top-8 h-28 w-28 rounded-full bg-indigo-500/25 blur-2xl"></div>
            <div class="pointer-events-none absolute -bottom-8 left-4 h-24 w-24 rounded-full bg-teal-400/20 blur-2xl"></div>

            {{-- Baris Identitas Aplikasi --}}
            <div class="relative mb-4 flex items-center justify-between">
                <a href="{{ route('beranda') }}" class="flex items-center gap-2.5">
                    <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-gradient-to-tr from-primary-500 to-teal-400 text-sm font-black text-white shadow-md shadow-indigo-500/30 ring-1 ring-white/30">
                        PG
                    </div>
                    <div>
                        <p class="text-xs font-black uppercase tracking-wider text-white">Portal Guru</p>
                        <p class="truncate text-[10px] font-medium text-indigo-200/80 max-w-[150px]">{{ $namaSekolah }}</p>
                    </div>
                </a>
                <button type="button" @click="sidebarTerbuka = false"
                        class="rounded-lg p-1 text-white/70 hover:bg-white/10 hover:text-white lg:hidden"
                        aria-label="Tutup menu">
                    <x-heroicon-o-x-mark class="h-5 w-5"/>
                </button>
            </div>

            {{-- Kartu Profil Pengguna --}}
            <div class="relative rounded-2xl border border-white/15 bg-white/10 p-3.5 backdrop-blur-md">
                <div class="flex items-center gap-3">
                    <div class="relative shrink-0">
                        @if ($pengguna?->foto)
                            <img src="{{ $pengguna->foto_url ?? Storage::url($pengguna->foto) }}" alt="Foto {{ $pengguna->name }}"
                                 class="h-11 w-11 rounded-xl object-cover ring-2 ring-white/40"
                                 onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                            <div class="hidden h-11 w-11 items-center justify-center rounded-xl bg-gradient-to-br from-indigo-400 to-teal-400 text-base font-black text-white ring-2 ring-white/40">
                                {{ $pengguna?->inisial }}
                            </div>
                        @else
                            <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-gradient-to-br from-indigo-400 to-teal-400 text-base font-black text-white ring-2 ring-white/40">
                                {{ $pengguna?->inisial }}
                            </div>
                        @endif
                        <span class="absolute -bottom-0.5 -right-0.5 h-3 w-3 rounded-full border-2 border-indigo-950 bg-emerald-400" title="Aktif"></span>
                    </div>
                    <div class="min-w-0 flex-1">
                        <p class="truncate text-sm font-extrabold leading-tight text-white">{{ $pengguna?->name }}</p>
                        <p class="mt-0.5 truncate text-[11px] font-medium text-indigo-200">{{ $pengguna?->jabatan ?: 'Guru Mata Pelajaran' }}</p>
                    </div>
                </div>

                {{-- Strip Tahun Ajaran Aktif --}}
                <div class="mt-3 flex items-center justify-between border-t border-white/10 pt-2.5 text-[11px]">
                    <span class="font-semibold text-indigo-200/80">Tahun Aktif:</span>
                    <span class="inline-flex items-center gap-1 rounded-full bg-emerald-400/20 px-2.5 py-0.5 font-extrabold text-emerald-300 ring-1 ring-emerald-400/30">
                        <span class="h-1.5 w-1.5 rounded-full bg-emerald-400"></span>
                        {{ $tahunAjaranAktif?->label ?? 'Belum diatur' }}
                    </span>
                </div>
            </div>
        </div>

        {{-- Navigasi Menu --}}
        <nav @click="if (window.innerWidth < 1024) sidebarTerbuka = false" class="flex-1 space-y-1 overflow-y-auto px-3.5 py-4 text-sm">
            <p class="px-3 pb-1.5 text-[10px] font-extrabold uppercase tracking-widest text-slate-400">Menu Utama</p>
            <x-menu-sidebar :href="route('beranda')" :aktif="request()->routeIs('beranda')" ikon="home">Beranda</x-menu-sidebar>
            <x-menu-sidebar :href="route('jadwal.index')" :aktif="request()->routeIs('jadwal.*')" ikon="calendar-days">Jadwal Mengajar</x-menu-sidebar>
            <x-menu-sidebar :href="route('agenda.index')" :aktif="request()->routeIs('agenda.*')" ikon="clipboard-document-list">Agenda Mengajar</x-menu-sidebar>
            <x-menu-sidebar :href="route('presensi.index')" :aktif="request()->routeIs('presensi.*')" ikon="user-group">Presensi Siswa</x-menu-sidebar>

            <p class="px-3 pb-1.5 pt-4 text-[10px] font-extrabold uppercase tracking-widest text-slate-400">Penilaian & Materi</p>
            <x-menu-sidebar :href="route('nilai.index')" :aktif="request()->routeIs('nilai.*') || request()->routeIs('penilaian.*')" ikon="academic-cap">Daftar Nilai</x-menu-sidebar>
            <x-menu-sidebar :href="route('materi.index')" :aktif="request()->routeIs('materi.*')" ikon="folder">Bank Materi</x-menu-sidebar>
            <x-menu-sidebar :href="route('bab.index')" :aktif="request()->routeIs('bab.*')" ikon="book-open">Bab & Tujuan (TP)</x-menu-sidebar>
            <x-menu-sidebar :href="route('rapor.index')" :aktif="request()->routeIs('rapor.*')" ikon="document-chart-bar">Rekap Rapor</x-menu-sidebar>

            @if ($pengguna?->isAdmin())
                <p class="px-3 pb-1.5 pt-5 text-[10px] font-extrabold uppercase tracking-widest text-slate-400">Administrasi Sekolah</p>
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
        <div class="border-t border-slate-100 bg-slate-50/60 p-3 space-y-1">
            <a href="{{ route('profile.edit') }}" wire:navigate.hover
               @class([
                   'flex items-center gap-3 rounded-xl px-3.5 py-2 text-xs font-bold transition-colors',
                   'bg-primary-50 text-primary' => request()->routeIs('profile.*'),
                   'text-slate-600 hover:bg-white hover:text-primary' => ! request()->routeIs('profile.*'),
               ])>
                <x-heroicon-o-user-circle class="h-5 w-5 shrink-0"/>
                <span>Profil & Pengaturan Akun</span>
            </a>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit"
                        class="flex w-full cursor-pointer items-center gap-3 rounded-xl px-3.5 py-2 text-xs font-bold text-rose-600 transition-colors hover:bg-rose-50">
                    <x-heroicon-o-arrow-right-on-rectangle class="h-5 w-5 shrink-0"/>
                    <span>Keluar dari Portal</span>
                </button>
            </form>
        </div>
    </aside>

    {{-- KONTEN UTAMA --}}
    <div class="flex min-w-0 flex-1 flex-col pb-16 lg:pb-0">
        {{-- Topbar Modern Glassmorphic --}}
        <header class="tanpa-cetak sticky top-0 z-20 flex items-center gap-3 border-b border-slate-200/80 bg-white/85 px-4 py-3 backdrop-blur-md lg:px-8">
            <button type="button" @click="sidebarTerbuka = true"
                    class="tombol-sentuh inline-flex items-center justify-center rounded-xl border border-slate-200 bg-white p-2 text-slate-600 shadow-2xs hover:bg-slate-50 lg:hidden"
                    aria-label="Buka menu">
                <x-heroicon-o-bars-3 class="h-6 w-6"/>
            </button>

            <div class="min-w-0 flex-1">
                <div class="flex items-center gap-2">
                    <h1 class="truncate text-base font-black tracking-tight text-slate-900 sm:text-lg">@yield('judul', 'Beranda')</h1>
                    @if (\App\Support\JamPelajaran::isModeRamadhan())
                        <span class="hidden sm:inline-flex items-center gap-1 rounded-full bg-emerald-100 px-2.5 py-0.5 text-[10px] font-extrabold text-emerald-800 border border-emerald-200">
                            🌙 Ramadhan
                        </span>
                    @endif
                </div>
                <div class="flex items-center gap-2 text-xs font-medium text-slate-500"
                     x-data="{ jam: '' }"
                     x-init="
                         const updateJam = () => {
                             const d = new Date();
                             jam = d.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });
                         };
                         updateJam();
                         setInterval(updateJam, 15000);
                     ">
                    <span class="truncate">{{ Tanggal::lengkap(now()) }}</span>
                    <span class="text-slate-300">•</span>
                    <span class="inline-flex items-center gap-1 font-bold text-primary" x-show="jam" x-cloak>
                        <x-heroicon-o-clock class="h-3.5 w-3.5"/>
                        <span x-text="jam + ' WIB'"></span>
                    </span>
                </div>
            </div>

            {{-- Tombol Pencarian Cepat Menu (Command Palette Ctrl+K) --}}
            <button type="button" @click="bukaCariMenu()"
                    class="hidden md:inline-flex cursor-pointer items-center gap-2.5 rounded-xl border border-slate-200 bg-slate-50/80 px-3.5 py-2 text-xs font-semibold text-slate-500 transition hover:border-primary/40 hover:bg-white hover:text-slate-800 shadow-2xs">
                <x-heroicon-o-magnifying-glass class="h-4 w-4 text-slate-400"/>
                <span>Cari menu atau fitur...</span>
                <kbd class="rounded-md border border-slate-200 bg-white px-1.5 py-0.5 font-sans text-[10px] font-bold text-slate-400">Ctrl K</kbd>
            </button>

            {{-- Dropdown Aksi Cepat (⚡ Pintasan) --}}
            <div class="relative" x-data="{ bukaPintasan: false }">
                <button type="button" @click="bukaPintasan = !bukaPintasan"
                        class="tombol-sentuh inline-flex cursor-pointer items-center gap-1.5 rounded-xl bg-gradient-to-r from-primary-50 to-indigo-50 px-3 py-2 text-xs font-extrabold text-primary border border-primary-200/70 transition hover:from-primary hover:to-indigo-600 hover:text-white hover:shadow-md hover:shadow-primary/20"
                        title="Pintasan Aksi Cepat">
                    <x-heroicon-s-bolt class="h-4 w-4"/>
                    <span class="hidden sm:inline">Aksi Cepat</span>
                </button>

                <div x-cloak x-show="bukaPintasan" @click.outside="bukaPintasan = false"
                     x-transition:enter="transition ease-out duration-150"
                     x-transition:enter-start="opacity-0 translate-y-2 scale-95"
                     x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                     class="absolute right-0 mt-2 w-64 rounded-2xl border border-slate-200/90 bg-white p-2 shadow-xl">
                    <p class="px-3 py-1.5 text-[10px] font-extrabold uppercase tracking-wider text-slate-400">Pintasan Mengajar</p>
                    <a href="{{ route('presensi.index') }}" class="flex items-center gap-3 rounded-xl px-3 py-2.5 text-xs font-bold text-slate-700 hover:bg-primary-50 hover:text-primary transition">
                        <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-emerald-100 text-emerald-700">
                            <x-heroicon-o-user-group class="h-4 w-4"/>
                        </span>
                        <div>
                            <p>Input Presensi Hari Ini</p>
                            <p class="text-[10px] font-normal text-slate-400">Tandai hadir 1-klik</p>
                        </div>
                    </a>
                    <a href="{{ route('agenda.dari-jadwal') }}" class="flex items-center gap-3 rounded-xl px-3 py-2.5 text-xs font-bold text-slate-700 hover:bg-primary-50 hover:text-primary transition">
                        <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-indigo-100 text-indigo-700">
                            <x-heroicon-o-clipboard-document-list class="h-4 w-4"/>
                        </span>
                        <div>
                            <p>Catat Agenda Mengajar</p>
                            <p class="text-[10px] font-normal text-slate-400">Sesuai jadwal kelas hari ini</p>
                        </div>
                    </a>
                    <a href="{{ route('nilai.index') }}" class="flex items-center gap-3 rounded-xl px-3 py-2.5 text-xs font-bold text-slate-700 hover:bg-primary-50 hover:text-primary transition">
                        <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-amber-100 text-amber-700">
                            <x-heroicon-o-academic-cap class="h-4 w-4"/>
                        </span>
                        <div>
                            <p>Isi Daftar Nilai</p>
                            <p class="text-[10px] font-normal text-slate-400">Formatif & Sumatif otomatis</p>
                        </div>
                    </a>
                    <a href="{{ route('penilaian.create') }}" class="flex items-center gap-3 rounded-xl px-3 py-2.5 text-xs font-bold text-slate-700 hover:bg-primary-50 hover:text-primary transition">
                        <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-sky-100 text-sky-700">
                            <x-heroicon-o-plus-circle class="h-4 w-4"/>
                        </span>
                        <div>
                            <p>Buat Penilaian Baru</p>
                            <p class="text-[10px] font-normal text-slate-400">Tambah kolom tugas / ujian</p>
                        </div>
                    </a>
                </div>
            </div>

            {{-- Notifikasi / Peringatan --}}
            @php $peringatan = $peringatanGlobal ?? collect(); @endphp
            <div class="relative" x-data="{ buka: false }">
                <button type="button" @click="buka = !buka"
                        class="tombol-sentuh relative inline-flex cursor-pointer items-center justify-center rounded-xl border border-slate-200 bg-white p-2 text-slate-600 shadow-2xs transition hover:border-primary/40 hover:bg-slate-50 hover:text-primary"
                        aria-label="Notifikasi">
                    <x-heroicon-o-bell class="h-5 w-5"/>
                    @if ($peringatan->count())
                        <span class="absolute -right-1 -top-1 flex h-4 min-w-4 items-center justify-center rounded-full bg-rose-500 px-1 text-[10px] font-extrabold text-white ring-2 ring-white">
                            {{ $peringatan->count() > 9 ? '9+' : $peringatan->count() }}
                        </span>
                    @endif
                </button>
                <div x-cloak x-show="buka" @click.outside="buka = false" x-transition
                     class="absolute right-0 mt-2 w-80 sm:w-96 rounded-2xl border border-slate-200 bg-white p-3 shadow-2xl">
                    <div class="flex items-center justify-between border-b border-slate-100 px-2 pb-2.5 mb-2">
                        <p class="text-xs font-extrabold uppercase tracking-wider text-slate-700">Pengingat Administrasi</p>
                        @if ($peringatan->count())
                            <span class="rounded-full bg-rose-100 px-2 py-0.5 text-[10px] font-extrabold text-rose-700">{{ $peringatan->count() }} item</span>
                        @endif
                    </div>
                    <div class="max-h-72 space-y-1.5 overflow-y-auto">
                        @forelse ($peringatan as $item)
                            <a href="{{ $item['url'] ?? '#' }}" wire:navigate
                               class="flex items-start gap-2.5 rounded-xl p-2.5 text-xs font-medium text-slate-700 transition hover:bg-primary-50 hover:text-primary">
                                <span class="mt-0.5 flex h-6 w-6 shrink-0 items-center justify-center rounded-lg bg-amber-100 text-amber-700">
                                    <x-heroicon-o-exclamation-triangle class="h-3.5 w-3.5"/>
                                </span>
                                <span class="leading-relaxed">{{ $item['pesan'] }}</span>
                            </a>
                        @empty
                            <div class="py-6 text-center">
                                <x-heroicon-o-check-badge class="mx-auto h-8 w-8 text-emerald-400"/>
                                <p class="mt-1.5 text-xs font-bold text-slate-700">Semua Beres! 🎉</p>
                                <p class="text-[11px] text-slate-400">Tidak ada tanggungan administrasi saat ini.</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </header>

        <main class="flex-1 px-4 py-6 lg:px-8 animate-fade-in">
            <x-notifikasi/>
            <div>
                @yield('konten')
            </div>
        </main>

        <footer class="tanpa-cetak border-t border-slate-200/70 bg-white/50 px-4 py-4 text-center text-xs font-medium text-slate-400 lg:px-8">
            <div class="flex flex-col sm:flex-row items-center justify-between gap-2">
                <span>© {{ date('Y') }} <strong class="font-bold text-slate-600">{{ $namaSekolah }}</strong> — {{ config('app.name') }}</span>
                <span class="text-[11px] text-slate-400">Kurikulum Nasional • Tekan <kbd class="rounded border border-slate-200 bg-white px-1.5 py-0.5 font-sans text-[10px] font-bold text-slate-500">Ctrl K</kbd> untuk navigasi cepat</span>
            </div>
        </footer>
    </div>
</div>

{{-- MOBILE BOTTOM NAVIGATION BAR (< 1024px) --}}
<nav class="tanpa-cetak fixed bottom-0 inset-x-0 z-30 flex items-center justify-around border-t border-slate-200/90 bg-white/95 px-1 py-1.5 backdrop-blur-md shadow-[0_-4px_20px_rgba(15,23,42,0.06)] lg:hidden">
    <a href="{{ route('beranda') }}"
       class="flex flex-1 flex-col items-center justify-center gap-0.5 rounded-xl py-1 text-[10px] font-extrabold transition {{ request()->routeIs('beranda') ? 'text-primary' : 'text-slate-400 hover:text-slate-700' }}">
        <x-heroicon-o-home class="h-5 w-5"/>
        <span>Beranda</span>
    </a>
    <a href="{{ route('jadwal.index') }}"
       class="flex flex-1 flex-col items-center justify-center gap-0.5 rounded-xl py-1 text-[10px] font-extrabold transition {{ request()->routeIs('jadwal.*') ? 'text-primary' : 'text-slate-400 hover:text-slate-700' }}">
        <x-heroicon-o-calendar-days class="h-5 w-5"/>
        <span>Jadwal</span>
    </a>
    <a href="{{ route('presensi.index') }}"
       class="flex flex-1 flex-col items-center justify-center gap-0.5 rounded-xl py-1 text-[10px] font-extrabold transition {{ request()->routeIs('presensi.*') ? 'text-accent' : 'text-slate-400 hover:text-slate-700' }}">
        <div class="flex h-8 w-10 items-center justify-center rounded-xl {{ request()->routeIs('presensi.*') ? 'bg-accent text-white shadow-sm shadow-accent/30' : 'bg-primary-50 text-primary' }}">
            <x-heroicon-o-user-group class="h-5 w-5"/>
        </div>
        <span>Presensi</span>
    </a>
    <a href="{{ route('agenda.index') }}"
       class="flex flex-1 flex-col items-center justify-center gap-0.5 rounded-xl py-1 text-[10px] font-extrabold transition {{ request()->routeIs('agenda.*') ? 'text-primary' : 'text-slate-400 hover:text-slate-700' }}">
        <x-heroicon-o-clipboard-document-list class="h-5 w-5"/>
        <span>Agenda</span>
    </a>
    <button type="button" @click="sidebarTerbuka = true"
            class="flex flex-1 flex-col items-center justify-center gap-0.5 rounded-xl py-1 text-[10px] font-extrabold text-slate-400 hover:text-slate-700">
        <x-heroicon-o-squares-2x2 class="h-5 w-5"/>
        <span>Menu</span>
    </button>
</nav>

{{-- MODAL PENCARIAN CEPAT / COMMAND PALETTE (Ctrl + K) --}}
<div x-cloak x-show="cariMenuTerbuka"
     @keydown.escape.window="cariMenuTerbuka = false"
     class="tanpa-cetak fixed inset-0 z-50 flex items-start justify-center bg-slate-900/50 p-4 pt-16 sm:pt-24 backdrop-blur-xs">
    <div x-show="cariMenuTerbuka"
         x-transition:enter="transition ease-out duration-150"
         x-transition:enter-start="opacity-0 scale-95"
         x-transition:enter-end="opacity-100 scale-100"
         @click.outside="cariMenuTerbuka = false"
         class="w-full max-w-lg overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-2xl">
        <div class="flex items-center gap-3 border-b border-slate-100 px-4 py-3.5">
            <x-heroicon-o-magnifying-glass class="h-5 w-5 text-primary shrink-0"/>
            <input x-ref="inputCariMenu" x-model="kataKunciMenu" type="text"
                   placeholder="Ketik nama menu atau fitur (mis. presensi, nilai, rapor)..."
                   class="w-full border-0 bg-transparent p-0 text-sm font-semibold text-slate-800 placeholder:font-normal placeholder:text-slate-400 focus:outline-none focus:ring-0">
            <button type="button" @click="cariMenuTerbuka = false"
                    class="rounded-lg bg-slate-100 px-2 py-1 text-[10px] font-bold text-slate-500 hover:bg-slate-200">
                ESC
            </button>
        </div>
        <div class="max-h-80 overflow-y-auto p-2 space-y-1">
            <template x-for="(item, idx) in hasilMenu" :key="idx">
                <a :href="item.url"
                   class="flex items-center justify-between gap-3 rounded-xl px-3.5 py-2.5 text-left transition hover:bg-primary-50 group">
                    <div class="min-w-0 flex-1">
                        <div class="flex items-center gap-2">
                            <span class="text-sm font-bold text-slate-800 group-hover:text-primary" x-text="item.judul"></span>
                            <span class="rounded-full bg-slate-100 px-2 py-0.5 text-[10px] font-bold text-slate-500 group-hover:bg-primary-100 group-hover:text-primary-700" x-text="item.kategori"></span>
                        </div>
                        <p class="truncate text-xs text-slate-400 mt-0.5" x-text="item.ket"></p>
                    </div>
                    <span class="text-slate-300 group-hover:translate-x-0.5 group-hover:text-primary transition">→</span>
                </a>
            </template>
            <div x-show="hasilMenu.length === 0" class="py-8 text-center text-xs text-slate-400">
                Tidak ditemukan menu dengan kata kunci "<span class="font-bold text-slate-600" x-text="kataKunciMenu"></span>"
            </div>
        </div>
    </div>
</div>

@livewireScripts
@stack('skrip')
</body>
</html>
