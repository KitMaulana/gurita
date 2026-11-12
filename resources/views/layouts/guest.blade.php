@php
    $adaBuild = file_exists(public_path('build/manifest.json'));
    $adaCss = file_exists(public_path('css/app.css'));
@endphp
<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name') }}</title>

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
                        primary: { DEFAULT: '#4F46E5', 50: '#EEF2FF', 600: '#4338CA' },
                        accent:  { DEFAULT: '#0D9488', 600: '#0F766E' },
                    },
                    fontFamily: { sans: ['Plus Jakarta Sans', 'ui-sans-serif', 'system-ui', 'sans-serif'] },
                }},
            };
        </script>
    @endif
</head>
<body class="flex min-h-full items-center justify-center bg-slate-50 p-4 font-sans text-slate-800 antialiased">
    <div class="w-full max-w-md rounded-2xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8">
        {{ $slot }}
    </div>
</body>
</html>
