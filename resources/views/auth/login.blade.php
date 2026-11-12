@php
    use App\Models\Pengaturan;
    $adaBuild = file_exists(public_path('build/manifest.json'));
    $adaCss = file_exists(public_path('css/app.css'));
    $namaSekolah = Pengaturan::ambil('nama_sekolah', 'SMA Negeri 1 Ciruas');
@endphp
<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Masuk — {{ config('app.name') }}</title>

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
                theme: { extend: {
                    colors: {
                        primary: { DEFAULT: '#4F46E5', 50: '#EEF2FF', 600: '#4338CA', 700: '#3730A3' },
                        accent:  { DEFAULT: '#0D9488', 600: '#0F766E' },
                    },
                    fontFamily: { sans: ['Plus Jakarta Sans', 'ui-sans-serif', 'system-ui', 'sans-serif'] },
                }},
            };
        </script>
    @endif
</head>
<body class="min-h-full bg-slate-950 font-sans text-slate-800 antialiased selection:bg-primary selection:text-white">

<div class="relative flex min-h-screen flex-col justify-center overflow-hidden lg:grid lg:grid-cols-12">
    {{-- Latar belakang ambient glow --}}
    <div class="pointer-events-none absolute -left-24 -top-24 h-96 w-96 rounded-full bg-indigo-600/30 blur-3xl"></div>
    <div class="pointer-events-none absolute bottom-0 right-0 h-96 w-96 rounded-full bg-teal-500/20 blur-3xl"></div>

    {{-- Kolom Kiri: Hero Visual & Fitur (Tampil di Desktop) --}}
    <div class="relative hidden lg:col-span-7 lg:flex lg:flex-col lg:justify-between bg-gradient-to-br from-slate-900 via-indigo-950 to-primary-900 p-12 text-white overflow-hidden">
        <div class="pointer-events-none absolute inset-0 opacity-20"
             style="background-image: radial-gradient(rgba(255,255,255,0.25) 1px, transparent 1px); background-size: 24px 24px;"></div>

        <div class="relative z-10 flex items-center gap-3">
            <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-gradient-to-tr from-primary-500 to-teal-400 text-lg font-black text-white shadow-lg shadow-indigo-500/30 ring-2 ring-white/20">
                PG
            </div>
            <div>
                <p class="text-base font-extrabold tracking-tight">{{ config('app.name') }}</p>
                <p class="text-xs font-medium text-indigo-200/80">{{ $namaSekolah }}</p>
            </div>
        </div>

        <div class="relative z-10 my-auto max-w-xl space-y-6 py-8">
            <span class="inline-flex items-center gap-2 rounded-full border border-indigo-400/30 bg-indigo-500/15 px-3.5 py-1.5 text-xs font-bold text-indigo-200 backdrop-blur-xs">
                <span>✨</span> Workspace Digital Guru Modern & Terintegrasi
            </span>
            <h1 class="text-4xl font-black leading-tight tracking-tight text-white xl:text-5xl">
                Administrasi Mengajar Lebih <span class="bg-gradient-to-r from-teal-300 via-sky-300 to-indigo-300 bg-clip-text text-transparent">Cepat, Rapi & Nyaman.</span>
            </h1>
            <p class="text-sm leading-relaxed text-indigo-100/80 sm:text-base">
                Tidak perlu lagi repot membawa tumpukan buku agenda, kertas absen, dan file Excel terpisah. Kelola jadwal, presensi, nilai, materi, hingga leger rapor dalam satu genggaman.
            </p>

            {{-- 3 Kartu Keunggulan --}}
            <div class="grid gap-3 pt-2 sm:grid-cols-3">
                <div class="rounded-2xl border border-white/10 bg-white/5 p-4 backdrop-blur-md">
                    <div class="mb-2 inline-flex h-9 w-9 items-center justify-center rounded-xl bg-emerald-400/20 text-emerald-300">
                        <x-heroicon-o-check-badge class="h-5 w-5"/>
                    </div>
                    <p class="text-xs font-extrabold text-white">Presensi 1-Klik</p>
                    <p class="mt-0.5 text-[11px] text-indigo-200/70">Tandai semua hadir & rekap otomatis real-time.</p>
                </div>
                <div class="rounded-2xl border border-white/10 bg-white/5 p-4 backdrop-blur-md">
                    <div class="mb-2 inline-flex h-9 w-9 items-center justify-center rounded-xl bg-sky-400/20 text-sky-300">
                        <x-heroicon-o-calendar-days class="h-5 w-5"/>
                    </div>
                    <p class="text-xs font-extrabold text-white">Agenda Pintar</p>
                    <p class="mt-0.5 text-[11px] text-indigo-200/70">Terhubung langsung dengan sesi jadwal harian.</p>
                </div>
                <div class="rounded-2xl border border-white/10 bg-white/5 p-4 backdrop-blur-md">
                    <div class="mb-2 inline-flex h-9 w-9 items-center justify-center rounded-xl bg-amber-400/20 text-amber-300">
                        <x-heroicon-o-document-chart-bar class="h-5 w-5"/>
                    </div>
                    <p class="text-xs font-extrabold text-white">Rapor & Leger Siap</p>
                    <p class="mt-0.5 text-[11px] text-indigo-200/70">Kalkulasi KKTP & deskripsi capaian otomatis.</p>
                </div>
            </div>
        </div>

        <div class="relative z-10 flex items-center justify-between text-xs text-indigo-200/60">
            <span>Kurikulum Permendikdasmen 13/2025</span>
            <span>{{ $namaSekolah }}</span>
        </div>
    </div>

    {{-- Kolom Kanan: Form Login --}}
    <div class="relative z-10 flex flex-col justify-center px-4 py-10 sm:px-8 lg:col-span-5 lg:bg-slate-50 lg:px-12">
        <div class="mx-auto w-full max-w-md">
            {{-- Header Mobile --}}
            <div class="mb-6 text-center lg:hidden text-white">
                <div class="mx-auto mb-3 flex h-14 w-14 items-center justify-center rounded-2xl bg-gradient-to-tr from-primary to-teal-400 text-xl font-black text-white shadow-lg">
                    PG
                </div>
                <h1 class="text-2xl font-black tracking-tight">{{ config('app.name') }}</h1>
                <p class="text-xs font-medium text-indigo-200">{{ $namaSekolah }}</p>
            </div>

            <div class="rounded-3xl border border-slate-200/90 bg-white p-6 shadow-2xl sm:p-8">
                <div class="mb-6">
                    <span class="inline-flex items-center gap-1.5 rounded-full bg-primary-50 px-3 py-1 text-[11px] font-extrabold text-primary">
                        👋 Selamat Datang Kembali
                    </span>
                    <h2 class="mt-2 text-2xl font-black tracking-tight text-slate-900">Masuk ke Portal</h2>
                    <p class="mt-1 text-xs sm:text-sm font-medium text-slate-500">
                        Gunakan Email atau NIP Anda untuk mengakses ruang kerja mengajar.
                    </p>
                </div>

                @if (session('status'))
                    <div class="mb-4 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-xs font-bold text-emerald-800">
                        {{ session('status') }}
                    </div>
                @endif

                <form method="POST" action="{{ route('login') }}" class="space-y-4">
                    @csrf

                    <div class="space-y-1.5">
                        <label for="login" class="block text-xs font-bold uppercase tracking-wider text-slate-600">Email atau NIP</label>
                        <div class="relative">
                            <input id="login" name="login" type="text" value="{{ old('login') }}" required autofocus
                                   autocomplete="username" placeholder="Contoh: guru@sman1ciruas.sch.id atau NIP"
                                   class="block w-full rounded-xl border border-slate-200 bg-slate-50/50 px-4 py-3 text-sm font-semibold text-slate-800 shadow-2xs transition focus:border-primary focus:bg-white focus:outline-none focus:ring-4 focus:ring-primary/15">
                        </div>
                        @error('login')
                            <p class="flex items-center gap-1 text-xs font-semibold text-rose-600">
                                <x-heroicon-s-exclamation-circle class="h-4 w-4 shrink-0"/>
                                <span>{{ $message }}</span>
                            </p>
                        @enderror
                    </div>

                    <div class="space-y-1.5">
                        <label for="password" class="block text-xs font-bold uppercase tracking-wider text-slate-600">Kata Sandi</label>
                        <div class="relative">
                            <input id="password" name="password" type="password" required autocomplete="current-password"
                                   placeholder="Masukkan kata sandi Anda"
                                   class="block w-full rounded-xl border border-slate-200 bg-slate-50/50 px-4 py-3 pr-16 text-sm font-semibold text-slate-800 shadow-2xs transition focus:border-primary focus:bg-white focus:outline-none focus:ring-4 focus:ring-primary/15">
                            <button type="button"
                                    onclick="const p = document.getElementById('password'); if (p.type === 'password') { p.type = 'text'; this.textContent = 'Sembunyi'; } else { p.type = 'password'; this.textContent = 'Lihat'; }"
                                    class="absolute right-2.5 top-1/2 -translate-y-1/2 rounded-lg bg-slate-100 px-2.5 py-1 text-[11px] font-bold text-slate-600 hover:bg-slate-200">
                                Lihat
                            </button>
                        </div>
                        @error('password')
                            <p class="flex items-center gap-1 text-xs font-semibold text-rose-600">
                                <x-heroicon-s-exclamation-circle class="h-4 w-4 shrink-0"/>
                                <span>{{ $message }}</span>
                            </p>
                        @enderror
                    </div>

                    <div class="flex items-center justify-between pt-1">
                        <label class="flex cursor-pointer items-center gap-2 text-xs sm:text-sm font-semibold text-slate-600 select-none">
                            <input type="checkbox" name="remember"
                                   class="h-4 w-4 rounded border-slate-300 text-primary focus:ring-primary">
                            <span>Ingat saya di perangkat ini</span>
                        </label>
                    </div>

                    <button type="submit"
                            class="mt-2 flex w-full cursor-pointer items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-primary to-indigo-600 px-4 py-3.5 text-sm font-extrabold text-white shadow-lg shadow-primary/30 transition-all hover:from-primary-600 hover:to-indigo-700 hover:shadow-xl active:scale-[0.99]">
                        <span>Masuk Sekarang</span>
                        <x-heroicon-o-arrow-right class="h-4 w-4"/>
                    </button>
                </form>

                <div class="mt-6 rounded-2xl bg-slate-50 p-3.5 text-center border border-slate-100">
                    <p class="text-xs font-medium text-slate-500">
                        Lupa kata sandi atau belum memiliki akun?
                        <strong class="font-bold text-slate-700">Hubungi Admin / Waka Kurikulum.</strong>
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

</body>
</html>
