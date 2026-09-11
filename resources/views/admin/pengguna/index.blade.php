@extends('layouts.app')
@section('judul', 'Pengguna & Peran')

@section('konten')
    <x-kepala-halaman judul="Pengguna & Peran">
        <x-slot:aksi>
            <x-tombol gaya="aksen" ikon="plus" :href="route('admin.pengguna.create')">Tambah Pengguna</x-tombol>
        </x-slot:aksi>
    </x-kepala-halaman>

    <form method="GET" class="kartu mb-6 flex flex-wrap items-end gap-3 p-4">
        <x-bidang label="Cari" nama="cari" class="min-w-56 flex-1">
            <x-isian nama="cari" :value="request('cari')" placeholder="Nama, email, atau NIP"/>
        </x-bidang>
        <x-tombol gaya="utama" ikon="magnifying-glass">Cari</x-tombol>
        <x-tombol gaya="halus" :href="route('admin.pengguna.index')">Reset</x-tombol>
    </form>

    <x-tabel>
        <x-slot:kepala>
            <tr>
                <th class="px-4 py-3">Nama</th>
                <th class="px-4 py-3">NIP</th>
                <th class="px-4 py-3">Email</th>
                <th class="px-4 py-3">Peran</th>
                <th class="px-4 py-3 text-center">Status</th>
                <th class="px-4 py-3 text-right">Aksi</th>
            </tr>
        </x-slot:kepala>

        @forelse ($daftar as $pengguna)
            <tr class="hover:bg-slate-50">
                <td class="px-4 py-3">
                    <p class="font-medium text-slate-800">{{ $pengguna->name }}</p>
                    @if ($pengguna->jabatan)
                        <p class="text-xs text-slate-500">{{ $pengguna->jabatan }}</p>
                    @endif
                </td>
                <td class="px-4 py-3 text-slate-600">{{ $pengguna->nip ?? '—' }}</td>
                <td class="px-4 py-3 text-slate-600">{{ $pengguna->email }}</td>
                <td class="px-4 py-3">
                    <div class="flex flex-wrap gap-1">
                        @foreach ($pengguna->roles as $peran)
                            <span class="rounded-full bg-primary-50 px-2 py-0.5 text-xs font-semibold text-primary-700">
                                {{ str($peran->name)->replace('_', ' ')->title() }}
                            </span>
                        @endforeach
                    </div>
                </td>
                <td class="px-4 py-3 text-center">
                    <span @class([
                        'rounded-full px-2 py-0.5 text-xs font-semibold',
                        'bg-emerald-100 text-emerald-800' => $pengguna->is_aktif,
                        'bg-slate-100 text-slate-500' => ! $pengguna->is_aktif,
                    ])>{{ $pengguna->is_aktif ? 'Aktif' : 'Nonaktif' }}</span>
                </td>
                <td class="px-4 py-3">
                    <div class="flex justify-end gap-1">
                        <a href="{{ route('admin.pengguna.edit', $pengguna) }}" title="Ubah"
                           class="tombol-sentuh rounded-lg p-2 text-slate-500 hover:bg-slate-100 hover:text-primary">
                            <x-heroicon-o-pencil-square class="h-5 w-5"/>
                        </a>
                        <form method="POST" action="{{ route('admin.pengguna.destroy', $pengguna) }}"
                              onsubmit="return confirm('Hapus/nonaktifkan pengguna ini?')">
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
            <x-tabel.kosong :kolom="6" ikon="identification"
                            judul="Belum ada pengguna"
                            :aksi-url="route('admin.pengguna.create')"
                            aksi-label="Tambah Pengguna"/>
        @endforelse
    </x-tabel>

    <div class="mt-4">{{ $daftar->links() }}</div>
@endsection
