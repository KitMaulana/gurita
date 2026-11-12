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
        'accent', 'aksen' => 'bg-teal-50 text-teal-700 ring-1 ring-teal-200/70',
        'amber', 'peringatan' => 'bg-amber-50 text-amber-700 ring-1 ring-amber-200/70',
        'sky' => 'bg-sky-50 text-sky-700 ring-1 ring-sky-200/70',
        'rose', 'bahaya' => 'bg-rose-50 text-rose-700 ring-1 ring-rose-200/70',
        default => 'bg-indigo-50 text-indigo-700 ring-1 ring-indigo-200/70',
    };
    $tag = $href ? 'a' : 'div';
@endphp

<{{ $tag }} @if ($href) href="{{ $href }}" @endif
    {{ $attributes->merge(['class' => 'kartu group flex items-center gap-4 p-4 sm:p-5 ' . ($href ? 'kartu-hover cursor-pointer' : '')]) }}>
    <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-lg {{ $tema }}">
        <x-dynamic-component :component="'heroicon-o-'.$ikon" class="h-5 w-5"/>
    </div>

    <div class="min-w-0 flex-1">
        <p class="truncate text-xs font-medium text-slate-500">{{ $label }}</p>
        <p class="mt-0.5 text-2xl font-bold tracking-tight text-slate-900 tabular-nums">{{ $nilai }}</p>
        @if ($keterangan)
            <p class="mt-0.5 truncate text-[11px] text-slate-400">{{ $keterangan }}</p>
        @endif
    </div>

    @if ($href)
        <x-heroicon-o-chevron-right class="h-4 w-4 shrink-0 text-slate-300 transition-transform duration-150 group-hover:translate-x-0.5 group-hover:text-slate-500"/>
    @endif
</{{ $tag }}>
