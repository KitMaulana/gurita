@extends('layouts.app')
@section('judul', $pengguna->exists ? 'Ubah Pengguna' : 'Tambah Pengguna')

@section('konten')
    <x-kepala-halaman :judul="$pengguna->exists ? 'Ubah Pengguna' : 'Tambah Pengguna'">
        <x-slot:aksi>
            <x-tombol gaya="halus" ikon="arrow-left" :href="route('admin.pengguna.index')">Kembali</x-tombol>
        </x-slot:aksi>
    </x-kepala-halaman>

    <form method="POST"
          action="{{ $pengguna->exists ? route('admin.pengguna.update', $pengguna) : route('admin.pengguna.store') }}"
          class="kartu max-w-3xl p-6">
        @csrf
        @if ($pengguna->exists) @method('PUT') @endif

        <div class="grid gap-4 md:grid-cols-2">
            <x-bidang label="Nama Lengkap" nama="name" :wajib="true" class="md:col-span-2">
                <x-isian nama="name" :value="old('name', $pengguna->name)"/>
            </x-bidang>

            <x-bidang label="NIP" nama="nip" petunjuk="Dapat dipakai untuk masuk selain email.">
                <x-isian nama="nip" :value="old('nip', $pengguna->nip)"/>
            </x-bidang>

            <x-bidang label="Email" nama="email" :wajib="true">
                <x-isian nama="email" tipe="email" :value="old('email', $pengguna->email)"/>
            </x-bidang>

            <x-bidang label="Kata Sandi" nama="password" :wajib="! $pengguna->exists"
                      :petunjuk="$pengguna->exists ? 'Kosongkan bila tidak ingin mengganti kata sandi.' : 'Minimal 8 karakter.'">
                <x-isian nama="password" tipe="password" autocomplete="new-password"/>
            </x-bidang>

            <x-bidang label="Ulangi Kata Sandi" nama="password_confirmation">
                <x-isian nama="password_confirmation" tipe="password" autocomplete="new-password"/>
            </x-bidang>

            <x-bidang label="Jabatan" nama="jabatan" class="md:col-span-2">
                <x-isian nama="jabatan" :value="old('jabatan', $pengguna->jabatan)"
                         placeholder="mis. Guru Bahasa Indonesia & Kepala Perpustakaan"/>
            </x-bidang>

            <x-bidang label="Kutipan Motivasi" nama="quote" class="md:col-span-2"
                      petunjuk="Ditampilkan pada kartu sambutan di beranda.">
                <textarea name="quote" id="quote" rows="2"
                          class="block w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-primary focus:ring-primary">{{ old('quote', $pengguna->quote) }}</textarea>
            </x-bidang>

            <x-bidang label="Peran" nama="peran" :wajib="true" class="md:col-span-2">
                <div class="flex flex-wrap gap-4 rounded-lg border border-slate-200 p-3">
                    @foreach ($peranList as $nilai => $label)
                        <label class="flex cursor-pointer items-center gap-2 text-sm text-slate-700">
                            <input type="checkbox" name="peran[]" value="{{ $nilai }}"
                                   @checked(in_array($nilai, old('peran', $pengguna->roles->pluck('name')->all())))
                                   class="h-4 w-4 rounded border-slate-300 text-accent focus:ring-accent">
                            {{ str($label)->replace('_', ' ')->title() }}
                        </label>
                    @endforeach
                </div>
            </x-bidang>

            <div class="md:col-span-2">
                <label class="flex cursor-pointer items-center gap-2 text-sm text-slate-700">
                    <input type="checkbox" name="is_aktif" value="1" @checked(old('is_aktif', $pengguna->is_aktif ?? true))
                           class="h-4 w-4 rounded border-slate-300 text-accent focus:ring-accent">
                    Akun aktif (dapat masuk ke portal)
                </label>
            </div>
        </div>

        <div class="mt-6 flex justify-end gap-2">
            <x-tombol gaya="halus" :href="route('admin.pengguna.index')">Batal</x-tombol>
            <x-tombol gaya="aksen" ikon="check">Simpan</x-tombol>
        </div>
    </form>
@endsection
