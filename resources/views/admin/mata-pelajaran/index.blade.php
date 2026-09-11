@extends('layouts.app')
@section('judul', 'Mata Pelajaran')

@section('konten')
    <x-kepala-halaman judul="Mata Pelajaran">
        <x-slot:aksi>
            <x-tombol gaya="aksen" ikon="plus" :href="route('admin.mata-pelajaran.create')">Tambah Mata Pelajaran</x-tombol>
        </x-slot:aksi>
    </x-kepala-halaman>

    <x-tabel>
        <x-slot:kepala>
            <tr>
                <th class="px-4 py-3">Nama</th>
                <th class="px-4 py-3">Singkatan</th>
                <th class="px-4 py-3">Kelompok</th>
                <th class="px-4 py-3 text-center">Jadwal</th>
                <th class="px-4 py-3 text-center">Bab</th>
                <th class="px-4 py-3 text-right">Aksi</th>
            </tr>
        </x-slot:kepala>

        @forelse ($daftar as $mapel)
            <tr class="hover:bg-slate-50">
                <td class="px-4 py-3 font-medium text-slate-800">{{ $mapel->nama }}</td>
                <td class="px-4 py-3 text-slate-600">{{ $mapel->singkatan }}</td>
                <td class="px-4 py-3">
                    <span class="rounded-full bg-slate-100 px-2 py-0.5 text-xs font-semibold text-slate-600">
                        {{ $mapel->label_kelompok }}
                    </span>
                </td>
                <td class="px-4 py-3 text-center text-slate-600">{{ $mapel->jadwals_count }}</td>
                <td class="px-4 py-3 text-center text-slate-600">{{ $mapel->babs_count }}</td>
                <td class="px-4 py-3">
                    <div class="flex justify-end gap-1">
                        <a href="{{ route('admin.mata-pelajaran.edit', $mapel) }}" title="Ubah"
                           class="tombol-sentuh rounded-lg p-2 text-slate-500 hover:bg-slate-100 hover:text-primary">
                            <x-heroicon-o-pencil-square class="h-5 w-5"/>
                        </a>
                        <form method="POST" action="{{ route('admin.mata-pelajaran.destroy', $mapel) }}"
                              onsubmit="return confirm('Hapus mata pelajaran ini?')">
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
            <x-tabel.kosong :kolom="6" ikon="book-open"
                            judul="Belum ada mata pelajaran"
                            pesan="Tambahkan mata pelajaran sebelum menyusun jadwal."
                            :aksi-url="route('admin.mata-pelajaran.create')"
                            aksi-label="Tambah Mata Pelajaran"/>
        @endforelse
    </x-tabel>

    <div class="mt-4">{{ $daftar->links() }}</div>
@endsection
