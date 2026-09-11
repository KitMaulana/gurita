@props(['predikat'])

@php
    $warna = match (strtoupper((string) $predikat)) {
        'A' => 'bg-emerald-100 text-emerald-800 border-emerald-200',
        'B' => 'bg-sky-100 text-sky-800 border-sky-200',
        'C' => 'bg-amber-100 text-amber-800 border-amber-200',
        'D' => 'bg-rose-100 text-rose-800 border-rose-200',
        default => 'bg-slate-100 text-slate-600 border-slate-200',
    };
@endphp

<span {{ $attributes->merge(['class' => 'inline-flex h-7 w-7 items-center justify-center rounded-full border text-sm font-bold '.$warna]) }}>
    {{ strtoupper((string) $predikat) ?: '—' }}
</span>
