@extends('layouts.app')
@section('judul', $tahunAjaran->exists ? 'Ubah Tahun Ajaran' : 'Tambah Tahun Ajaran')

@section('konten')
    <x-kepala-halaman :judul="$tahunAjaran->exists ? 'Ubah Tahun Ajaran' : 'Tambah Tahun Ajaran'">
        <x-slot:aksi>
            <x-tombol gaya="halus" ikon="arrow-left" :href="route('admin.tahun-ajaran.index')">Kembali</x-tombol>
        </x-slot:aksi>
    </x-kepala-halaman>

    <form method="POST"
          action="{{ $tahunAjaran->exists ? route('admin.tahun-ajaran.update', $tahunAjaran) : route('admin.tahun-ajaran.store') }}"
          class="kartu max-w-2xl p-6">
        @csrf
        @if ($tahunAjaran->exists) @method('PUT') @endif

        <div class="grid gap-4 md:grid-cols-2">
            <x-bidang label="Nama Tahun Ajaran" nama="nama" :wajib="true" petunjuk="Format: 2026/2027">
                <x-isian nama="nama" :value="old('nama', $tahunAjaran->nama ?: request('nama'))" placeholder="2026/2027"/>
            </x-bidang>

            <x-bidang label="Semester" nama="semester" :wajib="true">
                <x-pilihan nama="semester" :opsi="['ganjil' => 'Ganjil', 'genap' => 'Genap']"
                           :terpilih="old('semester', $tahunAjaran->semester ?: request('semester'))" kosong="— Pilih semester —"/>
            </x-bidang>

            <x-bidang label="Tanggal Mulai" nama="tanggal_mulai" :wajib="true">
                <x-isian nama="tanggal_mulai" tipe="date"
                         :value="old('tanggal_mulai', optional($tahunAjaran->tanggal_mulai)->toDateString())"/>
            </x-bidang>

            <x-bidang label="Tanggal Selesai" nama="tanggal_selesai" :wajib="true">
                <x-isian nama="tanggal_selesai" tipe="date"
                         :value="old('tanggal_selesai', optional($tahunAjaran->tanggal_selesai)->toDateString())"/>
            </x-bidang>

            <div class="md:col-span-2">
                <label class="flex cursor-pointer items-center gap-2 text-sm text-slate-700">
                    <input type="checkbox" name="is_aktif" value="1" @checked(old('is_aktif', $tahunAjaran->is_aktif))
                           class="h-4 w-4 rounded border-slate-300 text-accent focus:ring-accent">
                    Jadikan tahun ajaran aktif (tahun ajaran lain otomatis dinonaktifkan)
                </label>
            </div>

            @if (!$tahunAjaran->exists)
                <div class="md:col-span-2 rounded-xl border border-sky-200 bg-sky-50 p-4 text-xs text-sky-950">
                    <label class="flex items-start gap-2.5 cursor-pointer">
                        <input type="checkbox" name="otomatis_salin" value="1" @checked(old('otomatis_salin', true))
                               class="mt-0.5 rounded border-sky-300 text-accent focus:ring-accent">
                        <div>
                            <span class="font-bold text-sky-900 text-sm">Otomatis salin data dari semester ganjil (jika ada)</span>
                            <p class="text-sky-800 text-xs mt-0.5 leading-relaxed">
                                Jika Anda membuat <strong>Semester Genap</strong> dan Semester Ganjil di tahun yang sama sudah memiliki kelas/jadwal, sistem akan otomatis menyalin seluruh kelas, penempatan siswa, jadwal, dan KKTP ke semester baru ini.
                            </p>
                        </div>
                    </label>
                </div>
            @endif
        </div>

        <div class="mt-6 flex justify-end gap-2">
            <x-tombol gaya="halus" :href="route('admin.tahun-ajaran.index')">Batal</x-tombol>
            <x-tombol gaya="aksen" ikon="check">Simpan</x-tombol>
        </div>
    </form>
@endsection
