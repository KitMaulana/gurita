@extends('layouts.app')
@section('judul', $bab->exists ? 'Ubah Bab' : 'Tambah Bab')

@section('konten')
    <x-kepala-halaman :judul="$bab->exists ? 'Ubah Bab' : 'Tambah Bab'">
        <x-slot:aksi>
            <x-tombol gaya="halus" ikon="arrow-left" :href="route('bab.index')">Kembali</x-tombol>
        </x-slot:aksi>
    </x-kepala-halaman>

    <form method="POST" action="{{ $bab->exists ? route('bab.update', $bab) : route('bab.store') }}"
          class="kartu max-w-3xl p-6">
        @csrf
        @if ($bab->exists) @method('PUT') @endif

        <div class="grid gap-4 md:grid-cols-2">
            <x-bidang label="Mata Pelajaran" nama="mata_pelajaran_id" :wajib="true">
                <x-pilihan nama="mata_pelajaran_id" :opsi="$mapelList" :terpilih="$bab->mata_pelajaran_id" kosong="— Pilih mapel —"/>
            </x-bidang>

            <x-bidang label="Tingkat" nama="tingkat" :wajib="true">
                <x-pilihan nama="tingkat" :opsi="['X' => 'Kelas X', 'XI' => 'Kelas XI', 'XII' => 'Kelas XII']"
                           :terpilih="$bab->tingkat" kosong="— Pilih tingkat —"/>
            </x-bidang>

            <x-bidang label="Kode Bab" nama="kode" :wajib="true" petunjuk="mis. BAB 1">
                <x-isian nama="kode" :value="old('kode', $bab->kode)"/>
            </x-bidang>

            <x-bidang label="Urutan" nama="urutan" :wajib="true">
                <x-isian nama="urutan" tipe="number" min="1" max="100" :value="old('urutan', $bab->urutan ?? 1)"/>
            </x-bidang>

            <x-bidang label="Judul Bab" nama="judul" :wajib="true" class="md:col-span-2">
                <x-isian nama="judul" :value="old('judul', $bab->judul)" placeholder="mis. Teks Editorial"/>
            </x-bidang>

            <x-bidang label="Capaian Pembelajaran (CP)" nama="capaian_pembelajaran" class="md:col-span-2">
                <textarea name="capaian_pembelajaran" id="capaian_pembelajaran" rows="4"
                          class="block w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-primary focus:ring-primary">{{ old('capaian_pembelajaran', $bab->capaian_pembelajaran) }}</textarea>
            </x-bidang>
        </div>

        <div class="mt-6 flex justify-end gap-2">
            <x-tombol gaya="halus" :href="route('bab.index')">Batal</x-tombol>
            <x-tombol gaya="aksen" ikon="check">Simpan</x-tombol>
        </div>
    </form>
@endsection
