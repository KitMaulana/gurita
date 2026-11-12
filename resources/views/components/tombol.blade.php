@props(['gaya' => 'utama', 'href' => null, 'tipe' => 'submit', 'ikon' => null])

@php
    $kelas = match ($gaya) {
        'utama' => 'bg-primary text-white border border-primary hover:bg-primary-600 shadow-[0_1px_2px_rgba(15,23,42,0.08)]',
        'aksen' => 'bg-slate-900 text-white border border-slate-900 hover:bg-slate-800 shadow-[0_1px_2px_rgba(15,23,42,0.08)]',
        'garis' => 'border border-slate-200 bg-white text-slate-700 hover:bg-slate-50 hover:text-slate-900 hover:border-slate-300 shadow-[0_1px_2px_rgba(15,23,42,0.04)]',
        'bahaya' => 'bg-rose-600 text-white border border-rose-600 hover:bg-rose-700 shadow-[0_1px_2px_rgba(15,23,42,0.08)]',
        'halus' => 'bg-slate-100 text-slate-700 border border-transparent hover:bg-slate-200/80 hover:text-slate-900',
        default => 'bg-primary text-white border border-primary hover:bg-primary-600 shadow-[0_1px_2px_rgba(15,23,42,0.08)]',
    };
    $dasar = 'tombol-sentuh inline-flex cursor-pointer items-center justify-center gap-2 whitespace-nowrap rounded-lg px-3.5 py-2 text-xs sm:text-sm font-semibold tracking-tight transition-colors duration-150 active:opacity-90 disabled:cursor-not-allowed disabled:opacity-50 ';
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
