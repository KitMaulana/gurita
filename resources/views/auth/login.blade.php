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
<body class="min-h-full bg-slate-50 font-sans text-slate-800 antialiased selection:bg-primary selection:text-white">

<div class="flex min-h-screen flex-col justify-center lg:grid lg:grid-cols-12">
    {{-- Kolom Kiri: Panel Identitas Minimalis (Desktop) --}}
    <div class="hidden lg:col-span-6 lg:flex lg:flex-col lg:justify-between bg-slate-900 p-12 text-white border-r border-slate-800">
        <div class="flex items-center gap-3">
            <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-white text-sm font-bold text-slate-900">
                PG
            </div>
            <div>
                <p class="text-sm font-bold tracking-tight">{{ config('app.name') }}</p>
                <p class="text-xs text-slate-400">{{ $namaSekolah }}</p>
            </div>
        </div>

        <div class="my-auto max-w-lg space-y-6">
            <h1 class="text-3xl font-bold leading-snug tracking-tight text-white xl:text-4xl">
                Administrasi mengajar lebih rapi dan terstruktur.
            </h1>
            <p class="text-sm leading-relaxed text-slate-400">
                Kelola jadwal pelajaran, agenda pertemuan, presensi siswa, daftar nilai, bahan ajar, hingga cetak leger rapor dalam satu ruang kerja terpadu.
            </p>

            <div class="space-y-3 pt-2 border-t border-slate-800 text-xs text-slate-300">
                <div class="flex items-center gap-3">
                    <x-heroicon-o-check-badge class="h-5 w-5 text-emerald-400 shrink-0"/>
                    <span>Presensi kelas cepat per sesi pelajaran dengan rekap persentase otomatis.</span>
                </div>
                <div class="flex items-center gap-3">
                    <x-heroicon-o-calendar-days class="h-5 w-5 text-indigo-400 shrink-0"/>
                    <span>Agenda mengajar terhubung langsung ke jadwal dan tujuan pembelajaran.</span>
                </div>
                <div class="flex items-center gap-3">
                    <x-heroicon-o-document-chart-bar class="h-5 w-5 text-sky-400 shrink-0"/>
                    <span>Kalkulasi nilai akhir KKTP, deskripsi capaian, dan cetak PDF F4 siap pakai.</span>
                </div>
            </div>
        </div>

        <div class="flex items-center justify-between text-xs text-slate-500">
            <span>Kurikulum Nasional</span>
            <span>{{ $namaSekolah }}</span>
        </div>
    </div>

    {{-- Kolom Kanan: Form Login --}}
    <div class="flex flex-col justify-center px-4 py-10 sm:px-8 lg:col-span-6 lg:px-16">
        <div class="mx-auto w-full max-w-md">
            <div class="mb-6 text-center lg:hidden">
                <div class="mx-auto mb-3 flex h-11 w-11 items-center justify-center rounded-xl bg-slate-900 text-sm font-bold text-white">
                    PG
                </div>
                <h1 class="text-xl font-bold tracking-tight text-slate-900">{{ config('app.name') }}</h1>
                <p class="text-xs text-slate-500">{{ $namaSekolah }}</p>
            </div>

            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8">
                <div class="mb-6">
                    <h2 class="text-xl font-bold tracking-tight text-slate-900">Masuk ke Portal</h2>
                    <p class="mt-1 text-xs sm:text-sm text-slate-500">
                        Gunakan Email atau NIP yang terdaftar pada akun sekolah Anda.
                    </p>
                </div>

                @if (session('status'))
                    <div class="mb-4 rounded-lg border border-emerald-200 bg-emerald-50 px-3.5 py-2.5 text-xs font-medium text-emerald-800">
                        {{ session('status') }}
                    </div>
                @endif

                <form method="POST" action="{{ route('login') }}" class="space-y-4">
                    @csrf

                    <div class="space-y-1.5">
                        <label for="login" class="block text-xs font-semibold text-slate-700">Email atau NIP</label>
                        <input id="login" name="login" type="text" value="{{ old('login') }}" required autofocus
                               autocomplete="username" placeholder="nama@sekolah.sch.id atau NIP"
                               class="block w-full rounded-lg border border-slate-200 bg-white px-3.5 py-2.5 text-sm text-slate-800 shadow-[0_1px_2px_rgba(15,23,42,0.04)] focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/15">
                        @error('login')
                            <p class="flex items-center gap-1 text-xs font-medium text-rose-600">
                                <x-heroicon-s-exclamation-circle class="h-4 w-4 shrink-0"/>
                                <span>{{ $message }}</span>
                            </p>
                        @enderror
                    </div>

                    <div class="space-y-1.5">
                        <label for="password" class="block text-xs font-semibold text-slate-700">Kata Sandi</label>
                        <div class="relative">
                            <input id="password" name="password" type="password" required autocomplete="current-password"
                                   placeholder="Masukkan kata sandi"
                                   class="block w-full rounded-lg border border-slate-200 bg-white px-3.5 py-2.5 pr-20 text-sm text-slate-800 shadow-[0_1px_2px_rgba(15,23,42,0.04)] focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/15">
                            <button type="button"
                                    onclick="const p = document.getElementById('password'); if (p.type === 'password') { p.type = 'text'; this.textContent = 'Sembunyi'; } else { p.type = 'password'; this.textContent = 'Lihat'; }"
                                    class="absolute right-2 top-1/2 -translate-y-1/2 rounded-md bg-slate-100 px-2 py-1 text-[11px] font-medium text-slate-600 hover:bg-slate-200">
                                Lihat
                            </button>
                        </div>
                        @error('password')
                            <p class="flex items-center gap-1 text-xs font-medium text-rose-600">
                                <x-heroicon-s-exclamation-circle class="h-4 w-4 shrink-0"/>
                                <span>{{ $message }}</span>
                            </p>
                        @enderror
                    </div>

                    <div class="flex items-center justify-between pt-1">
                        <label class="flex cursor-pointer items-center gap-2 text-xs sm:text-sm text-slate-600 select-none">
                            <input type="checkbox" name="remember"
                                   class="h-4 w-4 rounded border-slate-300 text-primary focus:ring-primary">
                            <span>Ingat saya di perangkat ini</span>
                        </label>
                    </div>

                    <button type="submit"
                            class="mt-2 flex w-full cursor-pointer items-center justify-center gap-2 rounded-lg bg-slate-900 px-4 py-2.5 text-sm font-semibold text-white transition-colors hover:bg-slate-800">
                        <span>Masuk</span>
                        <x-heroicon-o-arrow-right class="h-4 w-4"/>
                    </button>
                </form>

                <p class="mt-5 text-center text-xs text-slate-500">
                    Lupa kata sandi atau belum memiliki akun? Hubungi Admin / Waka Kurikulum.
                </p>
            </div>
        </div>
    </div>
</div>

</body>
</html>
