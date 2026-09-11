@php
    $pesan = collect([
        ['tipe' => 'sukses', 'teks' => session('sukses')],
        ['tipe' => 'peringatan', 'teks' => session('peringatan')],
        ['tipe' => 'galat', 'teks' => session('galat')],
        ['tipe' => 'info', 'teks' => session('info')],
    ])->filter(fn ($p) => filled($p['teks']));
@endphp

{{-- Toast kanan bawah, hilang otomatis setelah 3 detik (pengganti showNotification()). --}}
@if ($pesan->isNotEmpty())
    <div class="tanpa-cetak fixed bottom-4 right-4 z-50 w-80 space-y-2">
        @foreach ($pesan as $p)
            @php
                $gaya = match ($p['tipe']) {
                    'sukses' => ['bg-emerald-600', 'check-circle'],
                    'peringatan' => ['bg-amber-600', 'exclamation-triangle'],
                    'galat' => ['bg-rose-600', 'x-circle'],
                    default => ['bg-primary', 'information-circle'],
                };
            @endphp
            <div x-data="{ tampil: true }" x-init="setTimeout(() => tampil = false, 3000)"
                 x-show="tampil" x-transition x-cloak
                 class="flex items-start gap-3 rounded-xl {{ $gaya[0] }} px-4 py-3 text-sm text-white shadow-lg">
                <x-dynamic-component :component="'heroicon-o-'.$gaya[1]" class="mt-0.5 h-5 w-5 shrink-0"/>
                <p class="flex-1">{{ $p['teks'] }}</p>
                <button type="button" @click="tampil = false" class="shrink-0 opacity-70 hover:opacity-100" aria-label="Tutup">
                    <x-heroicon-o-x-mark class="h-4 w-4"/>
                </button>
            </div>
        @endforeach
    </div>
@endif

@if ($errors->any() && ! $errors->has('email'))
    <div class="mb-4 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800">
        <p class="font-semibold">Periksa kembali isian berikut:</p>
        <ul class="mt-1 list-inside list-disc space-y-0.5">
            @foreach ($errors->all() as $galat)
                <li>{{ $galat }}</li>
            @endforeach
        </ul>
    </div>
@endif
