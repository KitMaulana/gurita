@props(['predikat'])

@php
    $warna = match (strtoupper((string) $predikat)) {
        'A' => 'bg-emerald-100 text-emerald-800 border-emerald-200 ring-2 ring-emerald-500/15',
        'B' => 'bg-sky-100 text-sky-800 border-sky-200 ring-2 ring-sky-500/15',
        'C' => 'bg-amber-100 text-amber-800 border-amber-200 ring-2 ring-amber-500/15',
        'D' => 'bg-rose-100 text-rose-800 border-rose-200 ring-2 ring-rose-500/15',
        default => 'bg-slate-100 text-slate-600 border-slate-200',
    };
@endphp

<span {{ $attributes->merge(['class' => 'inline-flex h-7 w-7 items-center justify-center rounded-xl border text-xs font-black '.$warna]) }}>
    {{ strtoupper((string) $predikat) ?: '—' }}
</span>
