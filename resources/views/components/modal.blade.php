@props([
    'nama',
    'judul' => '',
    'lebar' => 'max-w-lg',
])

{{--
    Pengganti modal-backdrop manual pada prototipe.
    Buka dengan: <button @click="$dispatch('buka-modal', 'nama-modal')">
--}}
<div x-data="{ tampil: false }"
     x-on:buka-modal.window="if ($event.detail === '{{ $nama }}') tampil = true"
     x-on:tutup-modal.window="if ($event.detail === '{{ $nama }}' || $event.detail === undefined) tampil = false"
     x-on:keydown.escape.window="tampil = false"
     x-cloak>
    <div x-show="tampil" x-transition.opacity.duration.200ms
         class="fixed inset-0 z-50 flex items-end justify-center bg-slate-900/55 p-3 sm:p-4 backdrop-blur-xs sm:items-center">
        <div x-show="tampil"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
             x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
             x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
             @click.outside="tampil = false"
             class="w-full {{ $lebar }} overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-2xl">
            <div class="flex items-center justify-between border-b border-slate-100 bg-slate-50/60 px-5 py-4">
                <div class="flex items-center gap-2.5">
                    <span class="h-4 w-1.5 rounded-full bg-gradient-to-b from-primary to-accent"></span>
                    <h3 class="text-base font-extrabold tracking-tight text-slate-900">{{ $judul }}</h3>
                </div>
                <button type="button" @click="tampil = false"
                        class="tombol-sentuh inline-flex items-center justify-center rounded-xl p-1.5 text-slate-400 transition hover:bg-slate-200/60 hover:text-slate-700"
                        aria-label="Tutup">
                    <x-heroicon-o-x-mark class="h-5 w-5"/>
                </button>
            </div>
            <div class="max-h-[72vh] overflow-y-auto px-5 py-5">
                {{ $slot }}
            </div>
            @isset($kaki)
                <div class="flex flex-wrap justify-end gap-2 border-t border-slate-100 bg-slate-50/80 px-5 py-3.5">
                    {{ $kaki }}
                </div>
            @endisset
        </div>
    </div>
</div>
