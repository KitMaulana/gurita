@php
    $pesan = collect([
        ['tipe' => 'sukses', 'teks' => session('sukses')],
        ['tipe' => 'peringatan', 'teks' => session('peringatan')],
        ['tipe' => 'galat', 'teks' => session('galat')],
        ['tipe' => 'info', 'teks' => session('info')],
    ])->filter(fn ($p) => filled($p['teks']));
@endphp

{{-- Toast kanan bawah, hilang otomatis setelah 4 detik --}}
@if ($pesan->isNotEmpty())
    <div class="tanpa-cetak fixed bottom-20 right-4 z-50 w-80 sm:w-96 space-y-2 lg:bottom-6">
        @foreach ($pesan as $p)
            @php
                $gaya = match ($p['tipe']) {
                    'sukses' => ['border-emerald-200 bg-white text-slate-800', 'bg-emerald-50 text-emerald-600', 'check-circle', 'Berhasil'],
                    'peringatan' => ['border-amber-200 bg-white text-slate-800', 'bg-amber-50 text-amber-600', 'exclamation-triangle', 'Perhatian'],
                    'galat' => ['border-rose-200 bg-white text-slate-800', 'bg-rose-50 text-rose-600', 'x-circle', 'Gagal'],
                    default => ['border-slate-200 bg-white text-slate-800', 'bg-indigo-50 text-primary', 'information-circle', 'Informasi'],
                };
            @endphp
            <div x-data="{ tampil: true }" x-init="setTimeout(() => tampil = false, 4000)"
                 x-show="tampil"
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0 translate-y-2"
                 x-transition:enter-end="opacity-100 translate-y-0"
                 x-transition:leave="transition ease-in duration-150"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 x-cloak
                 class="flex items-start gap-3 rounded-xl border {{ $gaya[0] }} p-3.5 text-sm shadow-lg shadow-slate-900/10">
                <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg {{ $gaya[1] }}">
                    <x-dynamic-component :component="'heroicon-o-'.$gaya[2]" class="h-5 w-5 shrink-0"/>
                </div>
                <div class="min-w-0 flex-1">
                    <p class="text-xs font-semibold text-slate-900">{{ $gaya[3] }}</p>
                    <p class="mt-0.5 text-xs text-slate-600">{{ $p['teks'] }}</p>
                </div>
                <button type="button" @click="tampil = false" class="rounded-md p-1 text-slate-400 hover:bg-slate-100 hover:text-slate-600" aria-label="Tutup">
                    <x-heroicon-o-x-mark class="h-4 w-4"/>
                </button>
            </div>
        @endforeach
    </div>
@endif

@if ($errors->any() && ! $errors->has('email') && ! $errors->has('login'))
    <div class="mb-5 rounded-xl border border-rose-200 bg-rose-50/70 p-4 text-sm text-rose-900">
        <div class="flex items-center gap-2 font-semibold text-rose-800">
            <x-heroicon-s-exclamation-triangle class="h-4 w-4 text-rose-600 shrink-0"/>
            <span>Periksa kembali isian berikut:</span>
        </div>
        <ul class="mt-1.5 list-inside list-disc space-y-0.5 pl-1 text-xs text-rose-700">
            @foreach ($errors->all() as $galat)
                <li>{{ $galat }}</li>
            @endforeach
        </ul>
    </div>
@endif
