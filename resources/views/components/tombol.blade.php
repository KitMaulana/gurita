@props(['gaya' => 'utama', 'href' => null, 'tipe' => 'submit', 'ikon' => null])

@php
    $kelas = match ($gaya) {
        'utama' => 'bg-primary text-white hover:bg-primary-600',
        'aksen' => 'bg-accent text-white hover:bg-accent-600',
        'garis' => 'border border-slate-300 bg-white text-slate-700 hover:bg-slate-50',
        'bahaya' => 'bg-rose-600 text-white hover:bg-rose-700',
        'halus' => 'bg-slate-100 text-slate-700 hover:bg-slate-200',
        default => 'bg-primary text-white hover:bg-primary-600',
    };
    $dasar = 'tombol-sentuh inline-flex items-center justify-center gap-2 rounded-lg px-4 py-2 text-sm font-semibold shadow-sm transition-colors disabled:cursor-not-allowed disabled:opacity-50 ';
@endphp

@if ($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $dasar.$kelas]) }}>
        @if ($ikon)<x-dynamic-component :component="'heroicon-o-'.$ikon" class="h-4 w-4"/>@endif
        {{ $slot }}
    </a>
@else
    <button type="{{ $tipe }}" {{ $attributes->merge(['class' => $dasar.$kelas]) }}>
        @if ($ikon)<x-dynamic-component :component="'heroicon-o-'.$ikon" class="h-4 w-4"/>@endif
        {{ $slot }}
    </button>
@endif
