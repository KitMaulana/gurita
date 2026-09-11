@props([
    'label',
    'nilai',
    'ikon' => 'chart-bar',
    'warna' => 'primary',
    'keterangan' => null,
    'href' => null,
])

@php
    $gaya = match ($warna) {
        'accent' => 'bg-accent-50 text-accent-700',
        'amber' => 'bg-amber-50 text-amber-700',
        'sky' => 'bg-sky-50 text-sky-700',
        'rose' => 'bg-rose-50 text-rose-700',
        default => 'bg-primary-50 text-primary-700',
    };
    $tag = $href ? 'a' : 'div';
@endphp

<{{ $tag }} @if ($href) href="{{ $href }}" @endif
    {{ $attributes->merge(['class' => 'kartu kartu-hover flex items-center gap-4 p-5']) }}>
    <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl {{ $gaya }}">
        <x-dynamic-component :component="'heroicon-o-'.$ikon" class="h-6 w-6"/>
    </div>
    <div class="min-w-0">
        <p class="text-2xl font-extrabold leading-tight text-slate-900">{{ $nilai }}</p>
        <p class="truncate text-sm text-slate-500">{{ $label }}</p>
        @if ($keterangan)
            <p class="truncate text-xs text-slate-400">{{ $keterangan }}</p>
        @endif
    </div>
</{{ $tag }}>
