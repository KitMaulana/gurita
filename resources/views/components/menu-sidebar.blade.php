@props(['href', 'ikon' => 'square-3-stack-3d', 'aktif' => false])

<a href="{{ $href }}" wire:navigate.hover
   @class([
       'flex items-center gap-3 rounded-lg px-3 py-2.5 font-medium transition-colors tombol-sentuh',
       'bg-primary text-white shadow-sm' => $aktif,
       'text-slate-600 hover:bg-slate-100 hover:text-primary' => ! $aktif,
   ])>
    <x-dynamic-component :component="'heroicon-o-'.$ikon" class="h-5 w-5 shrink-0"/>
    <span class="truncate">{{ $slot }}</span>
</a>
