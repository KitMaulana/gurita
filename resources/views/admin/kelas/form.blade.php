@extends('layouts.app')
@section('judul', $kelas->exists ? 'Ubah Kelas' : 'Tambah Kelas')

@section('konten')
    <x-kepala-halaman :judul="$kelas->exists ? 'Ubah Kelas' : 'Tambah Kelas'">
        <x-slot:aksi>
            <x-tombol gaya="halus" ikon="arrow-left" :href="route('admin.kelas.index')">Kembali</x-tombol>
        </x-slot:aksi>
    </x-kepala-halaman>

    <form method="POST" action="{{ $kelas->exists ? route('admin.kelas.update', $kelas) : route('admin.kelas.store') }}"
          class="kartu max-w-2xl p-6">
        @csrf
        @if ($kelas->exists) @method('PUT') @endif

        <div class="grid gap-4 md:grid-cols-2">
            <x-bidang label="Tahun Ajaran" nama="tahun_ajaran_id" :wajib="true" class="md:col-span-2">
                <x-pilihan nama="tahun_ajaran_id" :opsi="$tahunAjarans->pluck('label', 'id')"
                           :terpilih="$kelas->tahun_ajaran_id"/>
            </x-bidang>

            <x-bidang label="Nama Kelas" nama="nama" :wajib="true" petunjuk="mis. XII IPA 1">
                <x-isian nama="nama" :value="old('nama', $kelas->nama)"/>
            </x-bidang>

            <x-bidang label="Tingkat" nama="tingkat" :wajib="true">
                <x-pilihan nama="tingkat" :opsi="['X' => 'X', 'XI' => 'XI', 'XII' => 'XII']"
                           :terpilih="$kelas->tingkat" kosong="— Pilih tingkat —"/>
            </x-bidang>

            <x-bidang label="Jurusan" nama="jurusan">
                <x-pilihan nama="jurusan" :opsi="['IPA' => 'IPA', 'IPS' => 'IPS']"
                           :terpilih="$kelas->jurusan" kosong="— Tanpa jurusan —"/>
            </x-bidang>

            <x-bidang label="Wali Kelas" nama="wali_kelas_id">
                <x-pilihan nama="wali_kelas_id" :opsi="$guruList->pluck('name', 'id')"
                           :terpilih="$kelas->wali_kelas_id" kosong="— Belum ditentukan —"/>
            </x-bidang>
        </div>

        <div class="mt-6 flex justify-end gap-2">
            <x-tombol gaya="halus" :href="route('admin.kelas.index')">Batal</x-tombol>
            <x-tombol gaya="aksen" ikon="check">Simpan</x-tombol>
        </div>
    </form>
@endsection
