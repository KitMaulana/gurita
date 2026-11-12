@props(['status'])

@php
    /** @var \App\Enums\StatusPresensi|\App\Enums\StatusAgenda|\App\Enums\JenisPenilaian|string $status */
    $warna = is_object($status) && method_exists($status, 'warna')
        ? $status->warna()
        : 'bg-slate-100 text-slate-700 border-slate-200';
    $teks = is_object($status) && method_exists($status, 'label')
        ? $status->label()
        : ucfirst(str_replace('_', ' ', (string) $status));
@endphp

<span {{ $attributes->merge(['class' => 'inline-flex items-center gap-1.5 rounded-full border px-2.5 py-0.5 text-xs font-bold shadow-2xs '.$warna]) }}>
    <span class="h-1.5 w-1.5 rounded-full bg-current opacity-75"></span>
    {{ $teks }}
</span>
