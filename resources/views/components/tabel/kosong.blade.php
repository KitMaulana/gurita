@props([
    'kolom' => 1,
    'judul' => 'Belum ada data',
    'pesan' => 'Data akan muncul di sini setelah Anda menambahkannya.',
    'ikon' => 'inbox',
    'aksiUrl' => null,
    'aksiLabel' => null,
])

<tr>
    <td colspan="{{ $kolom }}" class="px-6 py-16 text-center">
        <div class="mx-auto flex max-w-sm flex-col items-center">
            <div class="relative mb-4 flex h-16 w-16 items-center justify-center rounded-2xl bg-gradient-to-br from-primary-50 to-indigo-100/70 text-primary shadow-inner ring-1 ring-primary-100">
                <x-dynamic-component :component="'heroicon-o-'.$ikon" class="h-8 w-8"/>
                <span class=" -right-1 -top-1 absolute flex h-4 w-4 items-center justify-center rounded-full bg-accent text-[10px] font-bold text-white shadow-xs">✨</span>
            </div>
            <p class="text-base font-extrabold tracking-tight text-slate-800">{{ $judul }}</p>
            <p class="mt-1 text-xs sm:text-sm leading-relaxed text-slate-500">{{ $pesan }}</p>
            @if ($aksiUrl && $aksiLabel)
                <a href="{{ $aksiUrl }}"
                   class="tombol-sentuh mt-5 inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-accent to-emerald-600 px-4 py-2.5 text-xs sm:text-sm font-bold text-white shadow-sm shadow-accent/25 transition-all hover:from-accent-600 hover:to-emerald-700 hover:shadow-md">
                    <x-heroicon-o-plus class="h-4 w-4"/> {{ $aksiLabel }}
                </a>
            @endif
        </div>
    </td>
</tr>
