@extends('layouts.app')
@section('judul', $mataPelajaran->exists ? 'Ubah Mata Pelajaran' : 'Tambah Mata Pelajaran')

@section('konten')
    <x-kepala-halaman :judul="$mataPelajaran->exists ? 'Ubah Mata Pelajaran' : 'Tambah Mata Pelajaran'">
        <x-slot:aksi>
            <x-tombol gaya="halus" ikon="arrow-left" :href="route('admin.mata-pelajaran.index')">Kembali</x-tombol>
        </x-slot:aksi>
    </x-kepala-halaman>

    <form method="POST"
          action="{{ $mataPelajaran->exists ? route('admin.mata-pelajaran.update', $mataPelajaran) : route('admin.mata-pelajaran.store') }}"
          class="kartu max-w-2xl p-6">
        @csrf
        @if ($mataPelajaran->exists) @method('PUT') @endif

        <div class="grid gap-4 md:grid-cols-2">
            <x-bidang label="Nama Mata Pelajaran" nama="nama" :wajib="true" class="md:col-span-2">
                <x-isian nama="nama" :value="old('nama', $mataPelajaran->nama)" placeholder="mis. Bahasa Indonesia"/>
            </x-bidang>

            <x-bidang label="Singkatan" nama="singkatan" :wajib="true">
                <x-isian nama="singkatan" :value="old('singkatan', $mataPelajaran->singkatan)" placeholder="mis. BIN"/>
            </x-bidang>

            <x-bidang label="Kelompok" nama="kelompok" :wajib="true">
                <x-pilihan nama="kelompok"
                           :opsi="['umum' => 'Umum', 'pilihan' => 'Pilihan', 'muatan_lokal' => 'Muatan Lokal']"
                           :terpilih="$mataPelajaran->kelompok ?? 'umum'"/>
            </x-bidang>
        </div>

        <div class="mt-6 flex justify-end gap-2">
            <x-tombol gaya="halus" :href="route('admin.mata-pelajaran.index')">Batal</x-tombol>
            <x-tombol gaya="aksen" ikon="check">Simpan</x-tombol>
        </div>
    </form>
@endsection
