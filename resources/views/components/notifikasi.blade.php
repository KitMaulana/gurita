@php
    $pesan = collect([
        ['tipe' => 'sukses', 'teks' => session('sukses')],
        ['tipe' => 'peringatan', 'teks' => session('peringatan')],
        ['tipe' => 'galat', 'teks' => session('galat')],
        ['tipe' => 'info', 'teks' => session('info')],
    ])->filter(fn ($p) => filled($p['teks']));
@endphp

{{-- Toast kanan bawah, hilang otomatis setelah 4 detik (pengganti showNotification()). --}}
@if ($pesan->isNotEmpty())
    <div class="tanpa-cetak fixed bottom-20 right-4 z-50 w-80 sm:w-96 space-y-2.5 lg:bottom-6">
        @foreach ($pesan as $p)
            @php
                $gaya = match ($p['tipe']) {
                    'sukses' => ['bg-gradient-to-r from-emerald-600 to-teal-600 shadow-emerald-600/25', 'check-circle', 'Berhasil!'],
                    'peringatan' => ['bg-gradient-to-r from-amber-500 to-orange-600 shadow-amber-500/25', 'exclamation-triangle', 'Perhatian'],
                    'galat' => ['bg-gradient-to-r from-rose-600 to-red-600 shadow-rose-600/25', 'x-circle', 'Terjadi Kendala'],
                    default => ['bg-gradient-to-r from-primary to-indigo-600 shadow-primary/25', 'information-circle', 'Informasi'],
                };
            @endphp
            <div x-data="{ tampil: true }" x-init="setTimeout(() => tampil = false, 4000)"
                 x-show="tampil"
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0 translate-y-3 scale-95"
                 x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                 x-transition:leave="transition ease-in duration-150"
                 x-transition:leave-start="opacity-100 scale-100"
                 x-transition:leave-end="opacity-0 scale-95"
                 x-cloak
                 class="flex items-start gap-3 rounded-2xl {{ $gaya[0] }} px-4 py-3.5 text-sm text-white shadow-xl">
                <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-xl bg-white/20">
                    <x-dynamic-component :component="'heroicon-o-'.$gaya[1]" class="h-5 w-5 shrink-0"/>
                </div>
                <div class="min-w-0 flex-1">
                    <p class="text-xs font-extrabold uppercase tracking-wider text-white/85">{{ $gaya[2] }}</p>
                    <p class="mt-0.5 text-xs sm:text-sm font-semibold leading-snug text-white">{{ $p['teks'] }}</p>
                </div>
                <button type="button" @click="tampil = false" class="rounded-lg p-1 text-white/75 transition hover:bg-white/15 hover:text-white" aria-label="Tutup">
                    <x-heroicon-o-x-mark class="h-4 w-4"/>
                </button>
            </div>
        @endforeach
    </div>
@endif

@if ($errors->any() && ! $errors->has('email') && ! $errors->has('login'))
    <div class="mb-5 rounded-2xl border border-rose-200 bg-gradient-to-r from-rose-50 to-red-50/60 p-4 text-sm text-rose-900 shadow-xs">
        <div class="flex items-center gap-2 font-extrabold text-rose-800">
            <x-heroicon-s-exclamation-triangle class="h-5 w-5 text-rose-600 shrink-0"/>
            <span>Mohon periksa kembali isian berikut:</span>
        </div>
        <ul class="mt-2 list-inside list-disc space-y-1 pl-1 text-xs sm:text-sm font-medium text-rose-700">
            @foreach ($errors->all() as $galat)
                <li>{{ $galat }}</li>
            @endforeach
        </ul>
    </div>
@endif
