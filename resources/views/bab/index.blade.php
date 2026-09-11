@extends('layouts.app')
@section('judul', 'Bab & Tujuan Pembelajaran')

@section('konten')
    <x-kepala-halaman judul="Bab & Tujuan Pembelajaran"
                      keterangan="Struktur materi: Capaian Pembelajaran → Bab / Lingkup Materi → Tujuan Pembelajaran.">
        <x-slot:aksi>
            <x-tombol gaya="aksen" ikon="plus" :href="route('bab.create')">Tambah Bab</x-tombol>
        </x-slot:aksi>
    </x-kepala-halaman>

    <form method="GET" class="kartu mb-6 flex flex-wrap items-end gap-3 p-4">
        <x-bidang label="Mata Pelajaran" nama="mata_pelajaran_id" class="min-w-52 flex-1">
            <x-pilihan nama="mata_pelajaran_id" :opsi="$mapelList->pluck('nama', 'id')"
                       :terpilih="request('mata_pelajaran_id')" kosong="Semua mapel"/>
        </x-bidang>
        <x-bidang label="Tingkat" nama="tingkat" class="min-w-40">
            <x-pilihan nama="tingkat" :opsi="['X' => 'X', 'XI' => 'XI', 'XII' => 'XII']"
                       :terpilih="request('tingkat')" kosong="Semua tingkat"/>
        </x-bidang>
        <x-tombol gaya="utama" ikon="funnel">Tampilkan</x-tombol>
    </form>

    <x-tabel>
        <x-slot:kepala>
            <tr>
                <th class="px-4 py-3">Kode</th>
                <th class="px-4 py-3">Judul Bab</th>
                <th class="px-4 py-3">Mapel / Tingkat</th>
                <th class="px-4 py-3 text-center">Jumlah TP</th>
                <th class="px-4 py-3 text-right">Aksi</th>
            </tr>
        </x-slot:kepala>

        @forelse ($daftar as $bab)
            <tr class="hover:bg-slate-50">
                <td class="whitespace-nowrap px-4 py-3 font-semibold text-primary">{{ $bab->kode }}</td>
                <td class="px-4 py-3">
                    <p class="font-medium text-slate-800">{{ $bab->judul }}</p>
                    @if ($bab->capaian_pembelajaran)
                        <p class="line-clamp-1 text-xs text-slate-500">{{ $bab->capaian_pembelajaran }}</p>
                    @endif
                </td>
                <td class="px-4 py-3 text-sm text-slate-600">
                    {{ $bab->mataPelajaran->nama }} · Kelas {{ $bab->tingkat }}
                </td>
                <td class="px-4 py-3 text-center">
                    <span class="rounded-full bg-slate-100 px-2 py-0.5 text-xs font-semibold text-slate-700">
                        {{ $bab->tujuan_pembelajarans_count }}
                    </span>
                </td>
                <td class="px-4 py-3">
                    <div class="flex justify-end gap-1">
                        <a href="{{ route('bab.show', $bab) }}" title="Kelola TP"
                           class="tombol-sentuh rounded-lg p-2 text-slate-500 hover:bg-slate-100 hover:text-primary">
                            <x-heroicon-o-list-bullet class="h-5 w-5"/>
                        </a>
                        <a href="{{ route('bab.edit', $bab) }}" title="Ubah"
                           class="tombol-sentuh rounded-lg p-2 text-slate-500 hover:bg-slate-100 hover:text-primary">
                            <x-heroicon-o-pencil-square class="h-5 w-5"/>
                        </a>
                        <form method="POST" action="{{ route('bab.destroy', $bab) }}"
                              onsubmit="return confirm('Hapus bab ini beserta seluruh tujuan pembelajarannya?')">
                            @csrf @method('DELETE')
                            <button type="submit" title="Hapus"
                                    class="tombol-sentuh rounded-lg p-2 text-slate-500 hover:bg-rose-50 hover:text-rose-600">
                                <x-heroicon-o-trash class="h-5 w-5"/>
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
