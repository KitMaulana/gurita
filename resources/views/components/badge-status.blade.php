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

<span {{ $attributes->merge(['class' => 'inline-flex items-center rounded-full border px-2.5 py-0.5 text-xs font-semibold '.$warna]) }}>
    {{ $teks }}
</span>
