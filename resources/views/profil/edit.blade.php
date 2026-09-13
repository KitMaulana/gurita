@extends('layouts.app')
@section('judul', 'Profil Saya')

@section('konten')
    @php $pengguna = auth()->user(); @endphp

    <x-kepala-halaman judul="Profil Saya"
                      keterangan="Data ini muncul pada kartu sambutan beranda dan tanda tangan dokumen cetak."/>

    <div class="grid max-w-4xl gap-6 lg:grid-cols-2">
        <form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data" class="kartu p-6">
            @csrf @method('PATCH')

            <h3 class="mb-4 font-bold text-primary">Informasi Profil</h3>

            <div class="space-y-4">
                <div class="flex items-center gap-4">
                    @if ($pengguna->foto)
                        <img src="{{ $pengguna->foto_url ?? Storage::url($pengguna->foto) }}" alt="Foto profil"
                             class="h-16 w-16 rounded-full object-cover"
                             onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                        <div class="hidden h-16 w-16 items-center justify-center rounded-full bg-primary-50 text-xl font-bold text-primary">
                            {{ $pengguna->inisial }}
                        </div>
                    @else
                        <div class="flex h-16 w-16 items-center justify-center rounded-full bg-primary-50 text-xl font-bold text-primary">
                            {{ $pengguna->inisial }}
                        </div>
                    @endif
                    <div class="flex-1">
                        <x-bidang label="Foto Profil" nama="foto" petunjuk="JPG/PNG, maksimal 2 MB.">
                            <input type="file" name="foto" id="foto" accept="image/*"
                                   class="block w-full text-sm file:mr-3 file:rounded-lg file:border-0 file:bg-slate-100 file:px-3 file:py-1.5 file:text-sm file:font-semibold">
                        </x-bidang>
                    </div>
                </div>

                <x-bidang label="Nama Lengkap" nama="name" :wajib="true">
                    <x-isian nama="name" :value="old('name', $pengguna->name)"/>
                </x-bidang>

                <x-bidang label="Email" nama="email" :wajib="true">
                    <x-isian nama="email" tipe="email" :value="old('email', $pengguna->email)"/>
                </x-bidang>

                <x-bidang label="NIP" nama="nip_tampil" petunjuk="Hanya admin yang dapat mengubah NIP.">
                    <input type="text" value="{{ $pengguna->nip ?? '—' }}" disabled
                           class="block w-full rounded-lg border-slate-200 bg-slate-50 text-sm text-slate-500">
                </x-bidang>

                <x-bidang label="Jabatan" nama="jabatan">
                    <x-isian nama="jabatan" :value="old('jabatan', $pengguna->jabatan)"
                             placeholder="mis. Guru Bahasa Indonesia & Kepala Perpustakaan"/>
                </x-bidang>

                <x-bidang label="Kutipan Motivasi" nama="quote">
                    <textarea name="quote" id="quote" rows="3"
                              placeholder="Kalimat yang ingin Anda lihat setiap membuka portal…"
                              class="block w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-primary focus:ring-primary">{{ old('quote', $pengguna->quote) }}</textarea>
                </x-bidang>
            </div>

            <x-tombol gaya="aksen" ikon="check" class="mt-5">Simpan Profil</x-tombol>
        </form>

        <form method="POST" action="{{ route('password.update') }}" class="kartu h-fit p-6">
            @csrf @method('PUT')

            <h3 class="mb-4 font-bold text-primary">Ganti Kata Sandi</h3>

            <div class="space-y-4">
                <x-bidang label="Kata Sandi Saat Ini" nama="current_password" :wajib="true">
                    <x-isian nama="current_password" tipe="password" autocomplete="current-password"/>
                </x-bidang>

                <x-bidang label="Kata Sandi Baru" nama="password" :wajib="true" petunjuk="Minimal 8 karakter.">
                    <x-isian nama="password" tipe="password" autocomplete="new-password"/>
                </x-bidang>

                <x-bidang label="Ulangi Kata Sandi Baru" nama="password_confirmation" :wajib="true">
                    <x-isian nama="password_confirmation" tipe="password" autocomplete="new-password"/>
                </x-bidang>
            </div>

            <x-tombol gaya="utama" ikon="key" class="mt-5">Perbarui Kata Sandi</x-tombol>
        </form>
    </div>
@endsection
