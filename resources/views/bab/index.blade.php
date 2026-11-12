@extends('layouts.app')
@section('judul', 'Bab & Tujuan Pembelajaran')

@section('konten')
    <x-kepala-halaman judul="Bab & Tujuan Pembelajaran"
                      keterangan="Struktur materi: Capaian Pembelajaran → Bab / Lingkup Materi → Tujuan Pembelajaran.">
        <x-slot:aksi>
            <x-tombol gaya="aksen" ikon="plus" :href="route('bab.create')">Tambah Bab</x-tombol>
        </x-slot:aksi>
    </x-kepala-halaman>

    <form method="GET" class="kartu mb-6 flex flex-wrap items-end gap-3.5 p-4">
        <x-bidang label="Mata Pelajaran" nama="mata_pelajaran_id" class="min-w-52 flex-1">
            <x-pilihan nama="mata_pelajaran_id" :opsi="$mapelList->pluck('nama', 'id')"
                       :terpilih="request('mata_pelajaran_id')" kosong="Semua mapel"/>
        </x-bidang>
        <x-bidang label="Tingkat" nama="tingkat" class="min-w-40">
            <x-pilihan nama="tingkat" :opsi="['X' => 'Kelas X', 'XI' => 'Kelas XI', 'XII' => 'Kelas XII']"
                       :terpilih="request('tingkat')" kosong="Semua tingkat"/>
        </x-bidang>
        <x-tombol gaya="utama" ikon="funnel">Tampilkan</x-tombol>
    </form>

    <x-tabel>
        <x-slot:kepala>
            <tr>
                <th class="px-4 py-3 whitespace-nowrap">Kode</th>
                <th class="px-4 py-3">Judul Bab & Capaian Pembelajaran</th>
                <th class="px-4 py-3 whitespace-nowrap">Mapel / Tingkat</th>
                <th class="px-4 py-3 text-center whitespace-nowrap">Jumlah TP</th>
                <th class="px-4 py-3 text-right whitespace-nowrap">Aksi</th>
            </tr>
        </x-slot:kepala>

        @forelse ($daftar as $bab)
            <tr class="hover:bg-slate-50/80 transition-colors">
                <td class="whitespace-nowrap px-4 py-3.5">
                    <span class="rounded-md bg-indigo-50 px-2 py-0.5 text-xs font-semibold text-primary border border-indigo-100 whitespace-nowrap">
                        {{ $bab->kode }}
                    </span>
                </td>
                <td class="px-4 py-3.5 min-w-[200px]">
                    <a href="{{ route('bab.show', $bab) }}" class="font-semibold text-slate-800 hover:text-primary transition-colors">
                        {{ $bab->judul }}
                    </a>
                    @if ($bab->capaian_pembelajaran)
                        <p class="mt-0.5 line-clamp-1 text-xs text-slate-500">{{ $bab->capaian_pembelajaran }}</p>
                    @endif
                </td>
                <td class="px-4 py-3.5 text-sm text-slate-600 whitespace-nowrap">
                    {{ $bab->mataPelajaran->nama }} · <strong class="font-semibold text-slate-800">Kelas {{ $bab->tingkat }}</strong>
                </td>
                <td class="px-4 py-3.5 text-center whitespace-nowrap">
                    <a href="{{ route('bab.show', $bab) }}"
                       class="inline-flex items-center rounded-md bg-slate-100 px-2 py-0.5 text-xs font-semibold text-slate-700 hover:bg-slate-200 transition-colors whitespace-nowrap">
                        {{ $bab->tujuan_pembelajarans_count }} TP
                    </a>
                </td>
                <td class="px-4 py-3.5 whitespace-nowrap">
                    <div class="flex justify-end items-center gap-1.5 flex-nowrap">
                        <a href="{{ route('bab.show', $bab) }}" title="Kelola Tujuan Pembelajaran"
                           class="inline-flex items-center gap-1 rounded-lg border border-slate-200 bg-white px-2.5 py-1.5 text-xs font-semibold text-slate-700 hover:bg-slate-50 hover:text-slate-900 transition-colors whitespace-nowrap">
                            <x-heroicon-o-list-bullet class="h-4 w-4 shrink-0"/>
                            <span>Kelola TP</span>
                        </a>
                        <a href="{{ route('bab.edit', $bab) }}" title="Ubah"
                           class="inline-flex items-center justify-center rounded-lg border border-slate-200 bg-white p-1.5 text-slate-500 hover:bg-slate-50 hover:text-slate-900 transition-colors">
                            <x-heroicon-o-pencil-square class="h-4 w-4"/>
                        </a>
                        <form method="POST" action="{{ route('bab.destroy', $bab) }}"
                              onsubmit="return confirm('Hapus bab ini beserta seluruh tujuan pembelajarannya?')">
                            @csrf @method('DELETE')
                            <button type="submit" title="Hapus"
                                    class="inline-flex cursor-pointer items-center justify-center rounded-lg border border-slate-200 bg-white p-1.5 text-slate-400 hover:border-rose-200 hover:bg-rose-50 hover:text-rose-600 transition-colors">
                                <x-heroicon-o-trash class="h-4 w-4"/>
                            </button>
                        </form>
                    </div>
                </td>
            </tr>
        @empty
            <x-tabel.kosong :kolom="5" ikon="book-open"
                            judul="Belum ada bab"
                            pesan="Susun struktur materi agar agenda, penilaian, dan deskripsi rapor bisa saling terhubung."
                            :aksi-url="route('bab.create')"
                            aksi-label="Tambah Bab"/>
        @endforelse
    </x-tabel>

    <div class="mt-4">{{ $daftar->links() }}</div>
@endsection
