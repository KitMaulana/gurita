@extends('layouts.app')
@section('judul', 'Profil Saya')

@section('konten')
    @php $pengguna = auth()->user(); @endphp

    <x-kepala-halaman judul="Profil & Akun Saya"
                      keterangan="Data ini muncul pada kartu sambutan beranda dan tanda tangan dokumen cetak resmi."/>

    {{-- Preview Kartu Profil Guru --}}
    <div class="kartu mb-6 max-w-4xl overflow-hidden bg-gradient-to-r from-[#1E1B4B] via-[#312E81] to-primary p-6 text-white shadow-lg shadow-indigo-950/10">
        <div class="flex flex-col sm:flex-row sm:items-center gap-4">
            @if ($pengguna->foto)
                <img src="{{ $pengguna->foto_url ?? Storage::url($pengguna->foto) }}" alt="Foto profil"
                     class="h-16 w-16 rounded-2xl object-cover ring-2 ring-white/30"
                     onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                <div class="hidden h-16 w-16 items-center justify-center rounded-2xl bg-white/15 text-2xl font-extrabold text-white ring-2 ring-white/30">
                    {{ $pengguna->inisial }}
                </div>
            @else
                <div class="flex h-16 w-16 items-center justify-center rounded-2xl bg-white/15 text-2xl font-extrabold text-white ring-2 ring-white/30">
                    {{ $pengguna->inisial }}
                </div>
            @endif
            <div class="min-w-0 flex-1">
                <span class="inline-flex items-center rounded-full bg-white/15 px-2.5 py-0.5 text-[10px] font-extrabold uppercase tracking-wider text-indigo-100">
                    {{ $pengguna->isAdmin() ? 'Administrator Sekolah' : 'Pendidik / Guru' }}
                </span>
                <h2 class="mt-1 truncate text-xl font-extrabold tracking-tight">{{ $pengguna->name }}</h2>
                <p class="text-xs text-indigo-200">
                    {{ $pengguna->jabatan ?: 'Guru Mata Pelajaran' }}
                    @if ($pengguna->nip)
                        · NIP {{ $pengguna->nip }}
                    @endif
                </p>
                @if ($pengguna->quote)
                    <p class="mt-2 text-xs italic text-indigo-100/90">“{{ $pengguna->quote }}”</p>
                @endif
            </div>
        </div>
    </div>

    <div class="grid max-w-4xl gap-6 lg:grid-cols-2">
        <form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data" class="kartu p-6">
            @csrf @method('PATCH')

            <h3 class="mb-4 text-base font-extrabold text-slate-800">Informasi Profil</h3>

            <div class="space-y-4">
                <x-bidang label="Ganti Foto Profil" nama="foto" petunjuk="JPG/PNG, maksimal 2 MB.">
                    <input type="file" name="foto" id="foto" accept="image/*"
                           class="block w-full text-sm file:mr-3 file:rounded-xl file:border-0 file:bg-indigo-50 file:px-3.5 file:py-2 file:text-xs file:font-bold file:text-primary hover:file:bg-indigo-100">
                </x-bidang>

                <x-bidang label="Nama Lengkap & Gelar" nama="name" :wajib="true">
                    <x-isian nama="name" :value="old('name', $pengguna->name)"/>
                </x-bidang>

                <x-bidang label="Email" nama="email" :wajib="true">
                    <x-isian nama="email" tipe="email" :value="old('email', $pengguna->email)"/>
                </x-bidang>

                <x-bidang label="NIP" nama="nip_tampil" petunjuk="Hanya admin yang dapat mengubah NIP.">
                    <input type="text" value="{{ $pengguna->nip ?? '—' }}" disabled
                           class="block w-full rounded-xl border-slate-200 bg-slate-50 text-sm text-slate-500">
                </x-bidang>

                <x-bidang label="Jabatan / Tugas Tambahan" nama="jabatan">
                    <x-isian nama="jabatan" :value="old('jabatan', $pengguna->jabatan)"
                             placeholder="mis. Guru Bahasa Indonesia & Wali Kelas XII.1"/>
                </x-bidang>

                <x-bidang label="Kutipan Motivasi Beranda" nama="quote">
                    <textarea name="quote" id="quote" rows="3"
                              placeholder="Kalimat inspiratif yang ingin Anda lihat setiap membuka portal…"
                              class="block w-full rounded-xl border-slate-200 text-sm shadow-xs focus:border-primary focus:ring-primary">{{ old('quote', $pengguna->quote) }}</textarea>
                </x-bidang>
            </div>

            <x-tombol gaya="aksen" ikon="check" class="mt-5">Simpan Profil</x-tombol>
        </form>

        <form method="POST" action="{{ route('password.update') }}" class="kartu h-fit p-6">
            @csrf @method('PUT')

            <h3 class="mb-4 text-base font-extrabold text-slate-800">Ganti Kata Sandi</h3>

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
