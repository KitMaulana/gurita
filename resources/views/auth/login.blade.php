@php
    use App\Models\Pengaturan;
    $adaBuild = file_exists(public_path('build/manifest.json'));
    $adaCss = file_exists(public_path('css/app.css'));
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
        <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    @else
        <script src="{{ asset('vendor/tailwind-play.js') }}"></script>
        <script>
            tailwind.config = {
                theme: { extend: {
                    colors: {
                        primary: { DEFAULT: '#1A365D', 50: '#EEF3F9', 600: '#2D4A6F', 700: '#1A365D' },
                        accent:  { DEFAULT: '#7C9885', 600: '#6B8874' },
                    },
                    fontFamily: { sans: ['Plus Jakarta Sans', 'ui-sans-serif', 'system-ui', 'sans-serif'] },
                }},
            };
        </script>
    @endif
</head>
<body class="flex min-h-full items-center justify-center bg-gradient-to-br from-[#1A365D] to-[#2D4A6F] p-4 font-sans">

<div class="w-full max-w-md">
    <div class="mb-6 text-center text-white">
        <div class="mx-auto mb-3 flex h-14 w-14 items-center justify-center rounded-2xl bg-white/15 text-2xl font-extrabold">
            PG
        </div>
        <h1 class="text-2xl font-extrabold">{{ config('app.name') }}</h1>
        <p class="text-sm text-white/70">{{ Pengaturan::ambil('nama_sekolah') }}</p>
    </div>

    <div class="rounded-xl bg-white p-6 shadow-xl sm:p-8">
        @if (session('status'))
            <div class="mb-4 rounded-lg bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
                {{ session('status') }}
            </div>
        @endif

        <form method="POST" action="{{ route('login') }}" class="space-y-4">
            @csrf

            <div class="space-y-1.5">
                <label for="login" class="block text-sm font-semibold text-slate-700">Email atau NIP</label>
                <input id="login" name="login" type="text" value="{{ old('login') }}" required autofocus
                       autocomplete="username" placeholder="nama@sekolah.sch.id atau NIP"
                       class="block w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-primary focus:ring-primary">
                @error('login')
                    <p class="text-xs font-medium text-rose-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="space-y-1.5">
                <label for="password" class="block text-sm font-semibold text-slate-700">Kata Sandi</label>
                <input id="password" name="password" type="password" required autocomplete="current-password"
                       class="block w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-primary focus:ring-primary">
                @error('password')
                    <p class="text-xs font-medium text-rose-600">{{ $message }}</p>
                @enderror
            </div>

            <label class="flex cursor-pointer items-center gap-2 text-sm text-slate-600">
                <input type="checkbox" name="remember"
                       class="h-4 w-4 rounded border-slate-300 text-accent focus:ring-accent">
                Ingat saya di perangkat ini
            </label>

            <button type="submit"
                    class="w-full rounded-lg bg-primary px-4 py-3 text-sm font-bold text-white shadow-sm transition-colors hover:bg-primary-600">
                Masuk
            </button>
        </form>

        <p class="mt-5 text-center text-xs text-slate-400">
            Lupa kata sandi atau belum punya akun? Hubungi admin / Waka Kurikulum.
        </p>
    </div>
</div>

</body>
</html>
