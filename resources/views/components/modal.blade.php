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
    <div x-show="tampil" x-transition.opacity
         class="fixed inset-0 z-50 flex items-end justify-center bg-slate-900/50 p-4 backdrop-blur-sm sm:items-center">
        <div x-show="tampil" x-transition
             @click.outside="tampil = false"
             class="w-full {{ $lebar }} overflow-hidden rounded-xl bg-white shadow-xl">
            <div class="flex items-center justify-between border-b border-slate-200 px-5 py-4">
                <h3 class="text-base font-bold text-primary">{{ $judul }}</h3>
                <button type="button" @click="tampil = false"
                        class="tombol-sentuh rounded-lg p-1.5 text-slate-400 hover:bg-slate-100"
                        aria-label="Tutup">
                    <x-heroicon-o-x-mark class="h-5 w-5"/>
                </button>
            </div>
            <div class="max-h-[70vh] overflow-y-auto px-5 py-4">
                {{ $slot }}
            </div>
            @isset($kaki)
                <div class="flex justify-end gap-2 border-t border-slate-200 bg-slate-50 px-5 py-3">
                    {{ $kaki }}
                </div>
            @endisset
        </div>
    </div>
</div>
