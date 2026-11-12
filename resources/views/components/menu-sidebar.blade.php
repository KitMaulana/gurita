@props(['href', 'ikon' => 'square-3-stack-3d', 'aktif' => false, 'lencana' => null])

<a href="{{ $href }}" wire:navigate.hover
   @class([
       'group flex items-center gap-2.5 rounded-lg px-3 py-2 text-[13px] transition-colors duration-150',
       'bg-indigo-50/90 font-semibold text-primary' => $aktif,
       'font-medium text-slate-600 hover:bg-slate-100 hover:text-slate-900' => ! $aktif,
   ])>
    <x-dynamic-component :component="'heroicon-o-'.$ikon" @class([
        'h-4 w-4 shrink-0 transition-colors',
        'text-primary' => $aktif,
        'text-slate-400 group-hover:text-slate-600' => ! $aktif,
    ])/>
    <span class="flex-1 truncate">{{ $slot }}</span>
    @if ($lencana)
        <span @class([
            'rounded-md px-1.5 py-0.5 text-[10px] font-semibold tabular-nums',
            'bg-primary/10 text-primary' => $aktif,
            'bg-slate-100 text-slate-600' => ! $aktif,
        ])>{{ $lencana }}</span>
    @endif
</a>
