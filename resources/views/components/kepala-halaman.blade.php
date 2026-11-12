@props(['judul', 'keterangan' => null])

<div {{ $attributes->merge(['class' => 'mb-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between border-b border-slate-200/80 pb-4']) }}>
    <div class="min-w-0">
        <h2 class="text-xl font-bold tracking-tight text-slate-900 sm:text-2xl">{{ $judul }}</h2>
        @if ($keterangan)
            <p class="mt-0.5 text-xs sm:text-sm text-slate-500">{{ $keterangan }}</p>
        @endif
    </div>
    @isset($aksi)
        <div class="tanpa-cetak flex flex-wrap items-center gap-2 shrink-0">{{ $aksi }}</div>
    @endisset
</div>
