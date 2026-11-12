@extends('layouts.app')
@section('judul', 'Profil Saya')

@section('konten')
    @php $pengguna = auth()->user(); @endphp

    <x-kepala-halaman judul="Profil & Akun Saya"
                      keterangan="Data ini muncul pada kartu sambutan beranda dan tanda tangan dokumen cetak resmi."/>

    {{-- Kartu Ringkas Profil Guru --}}
    <div class="kartu mb-6 max-w-4xl p-5 sm:p-6">
        <div class="flex flex-col sm:flex-row sm:items-center gap-4">
            @if ($pengguna->foto)
                <img src="{{ $pengguna->foto_url ?? Storage::url($pengguna->foto) }}" alt="Foto profil"
                     class="h-16 w-16 shrink-0 rounded-full object-cover ring-1 ring-slate-200"
                     onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                <div class="hidden h-16 w-16 shrink-0 items-center justify-center rounded-full bg-slate-900 text-xl font-bold text-white">
                    {{ $pengguna->inisial }}
                </div>
            @else
                <div class="flex h-16 w-16 shrink-0 items-center justify-center rounded-full bg-slate-900 text-xl font-bold text-white">
                    {{ $pengguna->inisial }}
                </div>
            @endif
            <div class="min-w-0 flex-1">
                <span class="inline-flex items-center rounded-md bg-slate-100 px-2 py-0.5 text-[11px] font-semibold text-slate-600">
                    {{ $pengguna->isAdmin() ? 'Administrator Sekolah' : 'Pendidik / Guru' }}
                </span>
                <h2 class="mt-1 truncate text-lg font-bold text-slate-900">{{ $pengguna->name }}</h2>
                <p class="text-xs text-slate-500">
                    {{ $pengguna->jabatan ?: 'Guru Mata Pelajaran' }}
                    @if ($pengguna->nip)
                        · NIP {{ $pengguna->nip }}
                    @endif
                </p>
                @if ($pengguna->quote)
                    <p class="mt-2 border-l-2 border-slate-200 pl-3 text-xs italic text-slate-500">“{{ $pengguna->quote }}”</p>
                @endif
            </div>
        </div>
    </div>

    <div class="grid max-w-4xl gap-6 lg:grid-cols-2">
        <form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data" class="kartu p-6">
            @csrf @method('PATCH')

            <h3 class="mb-4 text-base font-bold text-slate-900">Informasi Profil</h3>

            <div class="space-y-4">
                <x-bidang label="Ganti Foto Profil" nama="foto" petunjuk="JPG/PNG, maksimal 2 MB.">
                    <input type="file" name="foto" id="foto" accept="image/*"
                           class="block w-full text-sm file:mr-3 file:rounded-lg file:border-0 file:bg-slate-100 file:px-3.5 file:py-2 file:text-xs file:font-semibold file:text-slate-700 hover:file:bg-slate-200">
                </x-bidang>

                <x-bidang label="Nama Lengkap & Gelar" nama="name" :wajib="true">
                    <x-isian nama="name" :value="old('name', $pengguna->name)"/>
                </x-bidang>

                <x-bidang label="Email" nama="email" :wajib="true">
                    <x-isian nama="email" tipe="email" :value="old('email', $pengguna->email)"/>
                </x-bidang>

                <x-bidang label="NIP" nama="nip_tampil" petunjuk="Hanya admin yang dapat mengubah NIP.">
                    <input type="text" value="{{ $pengguna->nip ?? '—' }}" disabled
                           class="block w-full rounded-lg border-slate-200 bg-slate-50 text-sm text-slate-500">
                </x-bidang>

                <x-bidang label="Jabatan / Tugas Tambahan" nama="jabatan">
                    <x-isian nama="jabatan" :value="old('jabatan', $pengguna->jabatan)"
                             placeholder="mis. Guru Bahasa Indonesia & Wali Kelas XII.1"/>
                </x-bidang>

                <x-bidang label="Kutipan Motivasi Beranda" nama="quote">
                    <textarea name="quote" id="quote" rows="3"
                              placeholder="Kalimat inspiratif yang ingin Anda lihat di beranda…"
                              class="block w-full rounded-lg border-slate-200 text-sm focus:border-primary focus:ring-primary">{{ old('quote', $pengguna->quote) }}</textarea>
                </x-bidang>
            </div>

            <x-tombol gaya="aksen" ikon="check" class="mt-5">Simpan Profil</x-tombol>
        </form>

        <form method="POST" action="{{ route('password.update') }}" class="kartu h-fit p-6">
            @csrf @method('PUT')

            <h3 class="mb-4 text-base font-bold text-slate-900">Ganti Kata Sandi</h3>

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
