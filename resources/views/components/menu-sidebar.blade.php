@props(['href', 'ikon' => 'square-3-stack-3d', 'aktif' => false, 'lencana' => null])

<a href="{{ $href }}" wire:navigate.hover
   @class([
       'group relative flex items-center gap-3 rounded-xl px-3.5 py-2.5 text-sm font-semibold transition-all duration-200 tombol-sentuh',
       'bg-gradient-to-r from-primary to-indigo-500 text-white shadow-md shadow-primary/25' => $aktif,
       'text-slate-600 hover:bg-slate-100/90 hover:text-primary' => ! $aktif,
   ])>
    @if ($aktif)
        <span class="absolute left-0 top-1/2 h-5 w-1 -translate-y-1/2 rounded-r-full bg-white/90"></span>
    @endif
    <div @class([
        'flex h-8 w-8 shrink-0 items-center justify-center rounded-lg transition-transform duration-200 group-hover:scale-105',
        'bg-white/20 text-white' => $aktif,
        'bg-slate-100 text-slate-500 group-hover:bg-primary-50 group-hover:text-primary' => ! $aktif,
    ])>
        <x-dynamic-component :component="'heroicon-o-'.$ikon" class="h-4.5 w-4.5 shrink-0"/>
    </div>
    <span class="flex-1 truncate tracking-tight">{{ $slot }}</span>
    @if ($lencana)
        <span @class([
            'rounded-full px-2 py-0.5 text-[10px] font-extrabold',
            'bg-white/25 text-white' => $aktif,
            'bg-primary-50 text-primary-700' => ! $aktif,
        ])>{{ $lencana }}</span>
    @endif
</a>
