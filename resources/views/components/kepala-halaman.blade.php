@props(['judul', 'keterangan' => null])

<div {{ $attributes->merge(['class' => 'mb-6 flex flex-col gap-3.5 sm:flex-row sm:items-center sm:justify-between']) }}>
    <div class="min-w-0">
        <div class="flex items-center gap-2.5">
            <span class="h-6 w-1.5 shrink-0 rounded-full bg-gradient-to-b from-primary to-accent"></span>
            <h2 class="text-xl font-black tracking-tight text-slate-900 sm:text-2xl">{{ $judul }}</h2>
        </div>
        @if ($keterangan)
            <p class="mt-1 pl-4 text-xs sm:text-sm font-medium text-slate-500">{{ $keterangan }}</p>
        @endif
    </div>
    @isset($aksi)
        <div class="tanpa-cetak flex flex-wrap items-center gap-2">{{ $aksi }}</div>
    @endisset
</div>
