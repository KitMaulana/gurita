@extends('layouts.app')
@section('judul', 'Pengguna & Peran')

@section('konten')
    <x-kepala-halaman judul="Pengguna & Peran">
        <x-slot:aksi>
            <x-tombol gaya="garis" ikon="arrow-down-tray" :href="route('admin.pengguna.ekspor')">Ekspor CSV</x-tombol>
            <x-tombol gaya="garis" ikon="arrow-up-tray" @click="$dispatch('buka-modal', 'update-massal-pengguna')">Update Massal</x-tombol>
            <x-tombol gaya="aksen" ikon="plus" :href="route('admin.pengguna.create')">Tambah Pengguna</x-tombol>
        </x-slot:aksi>
    </x-kepala-halaman>

    @if (session('galatImpor'))
        <div class="mb-4 rounded-xl border border-amber-200 bg-amber-50 p-4 text-sm text-amber-900">
            <p class="font-semibold">Catatan update massal:</p>
            <ul class="mt-1 max-h-56 list-inside list-disc space-y-0.5 overflow-y-auto">
                @foreach (session('galatImpor') as $pesan)
                    <li>{{ $pesan }}</li>
                @endforeach
            </ul>
        </div>
    @endif

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

    {{-- Modal Update Massal Pengguna --}}
    <x-modal nama="update-massal-pengguna" judul="Update Massal Email & NIP Pengguna">
        <form method="POST" action="{{ route('admin.pengguna.update-massal') }}" id="form-update-massal-pengguna"
              enctype="multipart/form-data" class="space-y-4">
            @csrf
            <div class="rounded-lg bg-slate-50 p-3 text-sm text-slate-600 space-y-2">
                <p class="font-semibold text-slate-700">Cara menggunakan fitur ini:</p>
                <ol class="list-decimal list-inside text-xs text-slate-600 space-y-1">
                    <li>Klik <strong>Ekspor CSV</strong> untuk mengunduh data pengguna saat ini.</li>
                    <li>Buka file CSV, lalu isi kolom <code class="font-mono text-primary bg-white px-1 rounded">email_baru</code> dan/atau <code class="font-mono text-primary bg-white px-1 rounded">nip_baru</code> pada baris yang ingin diubah.</li>
                    <li>Simpan file, lalu unggah kembali di sini.</li>
                </ol>

                <div class="mt-2 border-t border-slate-200 pt-2">
                    <p class="font-semibold text-slate-700">Format kolom CSV:</p>
                    <code class="block text-xs font-mono text-primary font-bold bg-white p-2 rounded border border-slate-200">email_lama, email_baru, nip_baru</code>
                    <ul class="list-disc list-inside text-xs text-slate-600 space-y-1 mt-2">
                        <li><strong>email_lama</strong> (wajib): email pengguna yang sudah terdaftar, sebagai kunci pencocokan.</li>
                        <li><strong>email_baru</strong> (opsional): isi jika ingin mengubah email pengguna.</li>
                        <li><strong>nip_baru</strong> (opsional): isi jika ingin mengubah NIP pengguna.</li>
                        <li>Kolom <code class="font-mono text-primary bg-white px-1 rounded">nama</code>, <code class="font-mono text-primary bg-white px-1 rounded">nip_lama</code>, dan <code class="font-mono text-primary bg-white px-1 rounded">peran</code> pada file ekspor hanya sebagai referensi dan akan diabaikan saat proses update.</li>
                    </ul>
                </div>
            </div>

            <x-bidang label="Berkas" nama="berkas" :wajib="true">
                <input type="file" name="berkas" id="berkas-update-massal" accept=".csv,.xlsx,.xls" required
                       class="block w-full text-sm file:mr-3 file:rounded-lg file:border-0 file:bg-slate-100 file:px-4 file:py-2 file:text-sm file:font-semibold">
            </x-bidang>

            <div class="flex gap-3">
                <a href="{{ route('admin.pengguna.template-update') }}" class="inline-block text-sm font-semibold text-accent-600 hover:underline">
                    Unduh template_update_pengguna.csv
                </a>
                <span class="text-slate-300">|</span>
                <a href="{{ route('admin.pengguna.ekspor') }}" class="inline-block text-sm font-semibold text-accent-600 hover:underline">
                    Ekspor data pengguna saat ini
                </a>
            </div>
        </form>

        <x-slot:kaki>
            <x-tombol tipe="button" gaya="halus" @click="$dispatch('tutup-modal', 'update-massal-pengguna')">Batal</x-tombol>
            <x-tombol gaya="aksen" ikon="arrow-up-tray" form="form-update-massal-pengguna">Proses Update</x-tombol>
        </x-slot:kaki>
    </x-modal>
@endsection
