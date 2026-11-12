@props([
    'label',
    'nilai',
    'ikon' => 'chart-bar',
    'warna' => 'primary',
    'keterangan' => null,
    'href' => null,
])

@php
    $tema = match ($warna) {
        'accent' => [
            'ikon_bg' => 'bg-gradient-to-br from-teal-500 to-emerald-600 text-white shadow-lg shadow-teal-500/25',
            'glow'    => 'from-teal-500/10 to-transparent',
            'aksen'   => 'text-teal-600',
        ],
        'amber' => [
            'ikon_bg' => 'bg-gradient-to-br from-amber-500 to-orange-500 text-white shadow-lg shadow-amber-500/25',
            'glow'    => 'from-amber-500/10 to-transparent',
            'aksen'   => 'text-amber-600',
        ],
        'sky' => [
            'ikon_bg' => 'bg-gradient-to-br from-sky-500 to-blue-600 text-white shadow-lg shadow-sky-500/25',
            'glow'    => 'from-sky-500/10 to-transparent',
            'aksen'   => 'text-sky-600',
        ],
        'rose' => [
            'ikon_bg' => 'bg-gradient-to-br from-rose-500 to-pink-600 text-white shadow-lg shadow-rose-500/25',
            'glow'    => 'from-rose-500/10 to-transparent',
            'aksen'   => 'text-rose-600',
        ],
        default => [
            'ikon_bg' => 'bg-gradient-to-br from-primary to-indigo-700 text-white shadow-lg shadow-primary/25',
            'glow'    => 'from-primary/10 to-transparent',
            'aksen'   => 'text-primary',
        ],
    };
    $tag = $href ? 'a' : 'div';
@endphp

<{{ $tag }} @if ($href) href="{{ $href }}" @endif
    {{ $attributes->merge(['class' => 'kartu group relative overflow-hidden flex items-center gap-4 p-5 ' . ($href ? 'kartu-hover cursor-pointer' : '')]) }}>
    {{-- Efek glow dekoratif sudut kanan atas --}}
    <div class="pointer-events-none absolute -right-6 -top-6 h-24 w-24 rounded-full bg-gradient-to-bl {{ $tema['glow'] }} blur-xl transition-transform duration-300 group-hover:scale-125"></div>

    <div class="flex h-13 w-13 shrink-0 items-center justify-center rounded-2xl {{ $tema['ikon_bg'] }} transition-transform duration-200 group-hover:scale-105" style="width: 52px; height: 52px;">
        <x-dynamic-component :component="'heroicon-o-'.$ikon" class="h-6 w-6"/>
    </div>

    <div class="min-w-0 flex-1">
        <p class="truncate text-xs font-bold uppercase tracking-wider text-slate-400">{{ $label }}</p>
        <div class="mt-0.5 flex items-baseline gap-2">
            <p class="text-2xl font-black tracking-tight text-slate-900 sm:text-3xl">{{ $nilai }}</p>
        </div>
        @if ($keterangan)
            <p class="mt-0.5 truncate text-xs font-medium text-slate-500">{{ $keterangan }}</p>
        @endif
    </div>

    @if ($href)
        <div class="shrink-0 text-slate-300 transition-all duration-200 group-hover:translate-x-1 {{ $tema['aksen'] }}">
            <x-heroicon-o-arrow-up-right class="h-5 w-5"/>
        </div>
    @endif
</{{ $tag }}>
