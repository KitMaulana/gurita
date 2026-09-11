@props([
    'kolom' => 1,
    'judul' => 'Belum ada data',
    'pesan' => 'Data akan muncul di sini setelah Anda menambahkannya.',
    'ikon' => 'inbox',
    'aksiUrl' => null,
    'aksiLabel' => null,
])

<tr>
    <td colspan="{{ $kolom }}" class="px-6 py-14 text-center">
        <div class="mx-auto flex max-w-sm flex-col items-center">
            <div class="mb-3 flex h-14 w-14 items-center justify-center rounded-full bg-slate-100 text-slate-400">
                <x-dynamic-component :component="'heroicon-o-'.$ikon" class="h-7 w-7"/>
            </div>
            <p class="text-base font-semibold text-slate-700">{{ $judul }}</p>
            <p class="mt-1 text-sm text-slate-500">{{ $pesan }}</p>
            @if ($aksiUrl && $aksiLabel)
                <a href="{{ $aksiUrl }}"
                   class="tombol-sentuh mt-4 inline-flex items-center gap-2 rounded-lg bg-accent px-4 py-2 text-sm font-semibold text-white hover:bg-accent-600">
                    <x-heroicon-o-plus class="h-4 w-4"/> {{ $aksiLabel }}
                </a>
            @endif
        </div>
    </td>
</tr>
