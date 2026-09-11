@extends('layouts.app')
@section('judul', $siswa->exists ? 'Ubah Siswa' : 'Tambah Siswa')

@section('konten')
    <x-kepala-halaman :judul="$siswa->exists ? 'Ubah Data Siswa' : 'Tambah Siswa'">
        <x-slot:aksi>
            <x-tombol gaya="halus" ikon="arrow-left" :href="route('admin.siswa.index')">Kembali</x-tombol>
        </x-slot:aksi>
    </x-kepala-halaman>

    <form method="POST" action="{{ $siswa->exists ? route('admin.siswa.update', $siswa) : route('admin.siswa.store') }}"
          class="kartu max-w-2xl p-6">
        @csrf
        @if ($siswa->exists) @method('PUT') @endif

        <div class="grid gap-4 md:grid-cols-2">
            <x-bidang label="NISN (Opsional)" nama="nisn">
                <x-isian nama="nisn" :value="old('nisn', $siswa->nisn)"/>
            </x-bidang>

            <x-bidang label="NIS" nama="nis">
                <x-isian nama="nis" :value="old('nis', $siswa->nis)"/>
            </x-bidang>

            <x-bidang label="Nama Lengkap" nama="nama" :wajib="true" class="md:col-span-2">
                <x-isian nama="nama" :value="old('nama', $siswa->nama)"/>
            </x-bidang>

            <x-bidang label="Jenis Kelamin" nama="jenis_kelamin" :wajib="true">
                <x-pilihan nama="jenis_kelamin" :opsi="['L' => 'Laki-laki', 'P' => 'Perempuan']"
                           :terpilih="$siswa->jenis_kelamin" kosong="— Pilih —"/>
            </x-bidang>

            <div class="flex items-end">
                <label class="flex cursor-pointer items-center gap-2 text-sm text-slate-700">
                    <input type="checkbox" name="is_aktif" value="1" @checked(old('is_aktif', $siswa->is_aktif ?? true))
                           class="h-4 w-4 rounded border-slate-300 text-accent focus:ring-accent">
                    Siswa aktif
                </label>
            </div>
        </div>

        <div class="mt-6 flex justify-end gap-2">
            <x-tombol gaya="halus" :href="route('admin.siswa.index')">Batal</x-tombol>
            <x-tombol gaya="aksen" ikon="check">Simpan</x-tombol>
        </div>
    </form>
@endsection
