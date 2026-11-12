@props(['gaya' => 'utama', 'href' => null, 'tipe' => 'submit', 'ikon' => null])

@php
    $kelas = match ($gaya) {
        'utama' => 'bg-gradient-to-r from-primary to-indigo-600 text-white shadow-sm shadow-primary/25 hover:from-primary-600 hover:to-indigo-700 hover:shadow-md hover:shadow-primary/30',
        'aksen' => 'bg-gradient-to-r from-accent to-emerald-600 text-white shadow-sm shadow-accent/25 hover:from-accent-600 hover:to-emerald-700 hover:shadow-md hover:shadow-accent/30',
        'garis' => 'border border-slate-200 bg-white text-slate-700 shadow-2xs hover:border-primary/40 hover:bg-primary-50/40 hover:text-primary',
        'bahaya' => 'bg-gradient-to-r from-rose-600 to-red-600 text-white shadow-sm shadow-rose-500/25 hover:from-rose-700 hover:to-red-700',
        'halus' => 'bg-slate-100/90 text-slate-700 hover:bg-slate-200/80 hover:text-slate-900',
        default => 'bg-gradient-to-r from-primary to-indigo-600 text-white shadow-sm shadow-primary/25 hover:from-primary-600 hover:to-indigo-700',
    };
    $dasar = 'tombol-sentuh inline-flex cursor-pointer items-center justify-center gap-2 rounded-xl px-4 py-2.5 text-xs sm:text-sm font-bold tracking-tight transition-all duration-150 active:scale-[0.98] disabled:cursor-not-allowed disabled:opacity-50 ';
@endphp

@if ($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $dasar.$kelas]) }}>
        @if ($ikon)<x-dynamic-component :component="'heroicon-o-'.$ikon" class="h-4 w-4 shrink-0"/>@endif
        {{ $slot }}
    </a>
@else
    <button type="{{ $tipe }}" {{ $attributes->merge(['class' => $dasar.$kelas]) }}>
        @if ($ikon)<x-dynamic-component :component="'heroicon-o-'.$ikon" class="h-4 w-4 shrink-0"/>@endif
        {{ $slot }}
    </button>
@endif
