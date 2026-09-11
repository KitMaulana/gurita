@extends('layouts.app')
@section('judul', 'Kelas')

@section('konten')
    <x-kepala-halaman judul="Kelas / Rombongan Belajar">
        <x-slot:aksi>
            <x-tombol gaya="garis" ikon="document-duplicate" :href="route('admin.tahun-ajaran.index')">
                Salin Data Semester
            </x-tombol>
            <x-tombol gaya="aksen" ikon="plus" :href="route('admin.kelas.create')">Tambah Kelas</x-tombol>
        </x-slot:aksi>
    </x-kepala-halaman>

    <form method="GET" class="kartu mb-6 flex flex-wrap items-end gap-3 p-4">
        <x-bidang label="Tahun Ajaran" nama="tahun_ajaran_id" class="min-w-56 flex-1">
            <x-pilihan nama="tahun_ajaran_id" :opsi="$tahunAjarans->pluck('label', 'id')" :terpilih="$tahunAjaranId"/>
        </x-bidang>
        <x-tombol gaya="utama" ikon="funnel">Tampilkan</x-tombol>
    </form>

    <x-tabel>
        <x-slot:kepala>
            <tr>
                <th class="px-4 py-3">Nama Kelas</th>
                <th class="px-4 py-3">Tingkat / Jurusan</th>
                <th class="px-4 py-3">Wali Kelas</th>
                <th class="px-4 py-3 text-center">Jumlah Siswa</th>
                <th class="px-4 py-3 text-right">Aksi</th>
            </tr>
        </x-slot:kepala>

        @forelse ($daftar as $kelas)
            <tr class="hover:bg-slate-50">
                <td class="px-4 py-3 font-semibold text-slate-800">{{ $kelas->nama }}</td>
                <td class="px-4 py-3 text-slate-600">
                    Kelas {{ $kelas->tingkat }}{{ $kelas->jurusan ? ' · '.$kelas->jurusan : '' }}
                </td>
                <td class="px-4 py-3 text-slate-600">{{ $kelas->waliKelas?->name ?? '—' }}</td>
                <td class="px-4 py-3 text-center">
                    <span class="rounded-full bg-slate-100 px-2 py-0.5 text-xs font-semibold text-slate-700">
                        {{ $kelas->siswas_count }}
                    </span>
                </td>
                <td class="px-4 py-3">
                    <div class="flex justify-end gap-1">
                        <a href="{{ route('admin.kelas.show', $kelas) }}" title="Kelola siswa"
                           class="tombol-sentuh rounded-lg p-2 text-slate-500 hover:bg-slate-100 hover:text-primary">
                            <x-heroicon-o-users class="h-5 w-5"/>
                        </a>
                        <a href="{{ route('admin.kelas.edit', $kelas) }}" title="Ubah"
                           class="tombol-sentuh rounded-lg p-2 text-slate-500 hover:bg-slate-100 hover:text-primary">
                            <x-heroicon-o-pencil-square class="h-5 w-5"/>
                        </a>
                        <form method="POST" action="{{ route('admin.kelas.destroy', $kelas) }}"
                              onsubmit="return confirm('Hapus kelas ini? Data lama tetap tersimpan (soft delete).')">
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
            <x-tabel.kosong :kolom="5" ikon="building-office"
                            judul="Belum ada kelas"
                            pesan="Tambahkan kelas untuk tahun ajaran ini."
                            :aksi-url="route('admin.kelas.create')"
                            aksi-label="Tambah Kelas"/>
        @endforelse
    </x-tabel>

    <div class="mt-4">{{ $daftar->links() }}</div>
@endsection
