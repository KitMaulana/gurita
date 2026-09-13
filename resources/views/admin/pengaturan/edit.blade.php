@extends('layouts.app')
@section('judul', 'Pengaturan Sekolah')

@section('konten')
    <x-kepala-halaman judul="Pengaturan Sekolah"
                      keterangan="Dipakai pada kop dokumen cetak dan perhitungan nilai akhir."/>

    <form method="POST" action="{{ route('admin.pengaturan.update') }}" enctype="multipart/form-data"
          class="max-w-4xl space-y-6">
        @csrf @method('PUT')

        <div class="kartu p-6">
            <h3 class="mb-4 font-bold text-primary">Identitas Sekolah</h3>
            <div class="grid gap-4 md:grid-cols-2">
                <x-bidang label="Nama Sekolah" nama="nama_sekolah" :wajib="true" class="md:col-span-2">
                    <x-isian nama="nama_sekolah" :value="old('nama_sekolah', $pengaturan['nama_sekolah'])"/>
                </x-bidang>

                <x-bidang label="NPSN" nama="npsn">
                    <x-isian nama="npsn" :value="old('npsn', $pengaturan['npsn'])"/>
                </x-bidang>

                <x-bidang label="Logo Sekolah" nama="logo" petunjuk="PNG/JPG, maksimal 1 MB.">
                    <input type="file" name="logo" id="logo" accept="image/*"
                           class="block w-full text-sm file:mr-3 file:rounded-lg file:border-0 file:bg-slate-100 file:px-4 file:py-2 file:text-sm file:font-semibold">
                    @if ($pengaturan['logo'])
                        <img src="{{ asset('storage/' . $pengaturan['logo']) }}" alt="Logo sekolah" class="mt-2 h-16" onerror="this.style.display='none'">
                    @endif
                </x-bidang>

                <x-bidang label="Alamat Sekolah" nama="alamat_sekolah" class="md:col-span-2">
                    <textarea name="alamat_sekolah" id="alamat_sekolah" rows="2"
                              class="block w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-primary focus:ring-primary">{{ old('alamat_sekolah', $pengaturan['alamat_sekolah']) }}</textarea>
                </x-bidang>

                <x-bidang label="Kepala Sekolah" nama="kepala_sekolah">
                    <x-isian nama="kepala_sekolah" :value="old('kepala_sekolah', $pengaturan['kepala_sekolah'])"/>
                </x-bidang>

                <x-bidang label="NIP Kepala Sekolah" nama="nip_kepala_sekolah">
                    <x-isian nama="nip_kepala_sekolah" :value="old('nip_kepala_sekolah', $pengaturan['nip_kepala_sekolah'])"/>
                </x-bidang>
            </div>
        </div>

        <div class="kartu p-6">
            <h3 class="mb-1 font-bold text-primary">Bobot Penilaian</h3>
            <p class="mb-4 text-sm text-slate-500">Total ketiga bobot harus tepat 100.</p>
            <div class="grid gap-4 md:grid-cols-3">
                <x-bidang label="Bobot Formatif (%)" nama="bobot_formatif" :wajib="true">
                    <x-isian nama="bobot_formatif" tipe="number" step="0.01" min="0" max="100"
                             :value="old('bobot_formatif', $pengaturan['bobot_formatif'])"/>
                </x-bidang>
                <x-bidang label="Bobot Sumatif Lingkup (%)" nama="bobot_sumatif_lingkup" :wajib="true">
                    <x-isian nama="bobot_sumatif_lingkup" tipe="number" step="0.01" min="0" max="100"
                             :value="old('bobot_sumatif_lingkup', $pengaturan['bobot_sumatif_lingkup'])"/>
                </x-bidang>
                <x-bidang label="Bobot Sumatif Akhir (%)" nama="bobot_sumatif_akhir" :wajib="true">
                    <x-isian nama="bobot_sumatif_akhir" tipe="number" step="0.01" min="0" max="100"
                             :value="old('bobot_sumatif_akhir', $pengaturan['bobot_sumatif_akhir'])"/>
                </x-bidang>
            </div>
        </div>

        <div class="kartu p-6">
            <h3 class="mb-4 font-bold text-primary">Ambang Predikat & Aturan Lain</h3>
            <div class="grid gap-4 md:grid-cols-4">
                <x-bidang label="Predikat A ≥" nama="ambang_predikat_a" :wajib="true">
                    <x-isian nama="ambang_predikat_a" tipe="number" step="0.01" min="0" max="100"
                             :value="old('ambang_predikat_a', $pengaturan['ambang_predikat_a'])"/>
                </x-bidang>
                <x-bidang label="Predikat B ≥" nama="ambang_predikat_b" :wajib="true">
                    <x-isian nama="ambang_predikat_b" tipe="number" step="0.01" min="0" max="100"
                             :value="old('ambang_predikat_b', $pengaturan['ambang_predikat_b'])"/>
                </x-bidang>
                <x-bidang label="Predikat C ≥" nama="ambang_predikat_c" :wajib="true">
                    <x-isian nama="ambang_predikat_c" tipe="number" step="0.01" min="0" max="100"
                             :value="old('ambang_predikat_c', $pengaturan['ambang_predikat_c'])"/>
                </x-bidang>
                <x-bidang label="Ambang Alfa Peringatan" nama="ambang_alfa_peringatan" :wajib="true"
                          petunjuk="Siswa ditandai merah bila alfa ≥ nilai ini.">
                    <x-isian nama="ambang_alfa_peringatan" tipe="number" min="1" max="50"
                             :value="old('ambang_alfa_peringatan', $pengaturan['ambang_alfa_peringatan'])"/>
                </x-bidang>
            </div>

            <label class="mt-4 flex cursor-pointer items-center gap-2 text-sm text-slate-700">
                <input type="checkbox" name="remedial_dibatasi_kktp" value="1"
                       @checked(old('remedial_dibatasi_kktp', $pengaturan['remedial_dibatasi_kktp'] == '1'))
                       class="h-4 w-4 rounded border-slate-300 text-accent focus:ring-accent">
                Nilai remedial dibatasi maksimal KKTP
            </label>
        </div>

        <div class="flex justify-end">
            <x-tombol gaya="aksen" ikon="check">Simpan Pengaturan</x-tombol>
        </div>
    </form>
@endsection
