@extends('layouts.app')
@section('judul', $penilaian->exists ? 'Ubah Penilaian' : 'Buat Penilaian')

@section('konten')
    <x-kepala-halaman :judul="$penilaian->exists ? 'Ubah Penilaian' : 'Buat Penilaian'"
                      keterangan="Nilai selalu terikat pada jenis penilaian dan (opsional) bab / tujuan pembelajaran.">
        <x-slot:aksi>
            <x-tombol gaya="halus" ikon="arrow-left" :href="route('penilaian.index')">Kembali</x-tombol>
        </x-slot:aksi>
    </x-kepala-halaman>

    <form method="POST"
          action="{{ $penilaian->exists ? route('penilaian.update', $penilaian) : route('penilaian.store') }}"
          class="kartu max-w-3xl p-6 sm:p-7"
          x-data="{
              babId: '{{ old('bab_id', $penilaian->bab_id) }}',
              tujuan: {{ Js::from($babList->mapWithKeys(fn ($b) => [$b->id => $b->tujuanPembelajarans->map(fn ($t) => ['id' => $t->id, 'label' => $t->kode.' — '.$t->deskripsi])])) }}
          }">
        @csrf
        @if ($penilaian->exists) @method('PUT') @endif

        <div class="grid gap-5 md:grid-cols-2">
            <x-bidang label="Kelas" nama="kelas_id" :wajib="true">
                <x-pilihan nama="kelas_id" :opsi="$kelasList" :terpilih="$penilaian->kelas_id" kosong="— Pilih kelas —"/>
            </x-bidang>

            <x-bidang label="Mata Pelajaran" nama="mata_pelajaran_id" :wajib="true">
                <x-pilihan nama="mata_pelajaran_id" :opsi="$mapelList" :terpilih="$penilaian->mata_pelajaran_id" kosong="— Pilih mapel —"/>
            </x-bidang>

            <x-bidang label="Jenis Penilaian" nama="jenis" :wajib="true">
                <x-pilihan nama="jenis" :opsi="$jenisList" :terpilih="$penilaian->jenis?->value"/>
            </x-bidang>

            <x-bidang label="Tanggal Pelaksanaan" nama="tanggal" :wajib="true">
                <x-isian nama="tanggal" tipe="date"
                         :value="old('tanggal', optional($penilaian->tanggal)->toDateString() ?? today()->toDateString())"/>
            </x-bidang>

            <x-bidang label="Nama Penilaian" nama="nama" :wajib="true" class="md:col-span-2">
                <x-isian nama="nama" :value="old('nama', $penilaian->nama)" placeholder="mis. Menulis Teks Editorial"/>
                <div class="mt-2 flex flex-wrap items-center gap-1.5">
                    <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Contoh Cepat:</span>
                    @foreach (['Tugas Harian 1', 'Kuis Formatif Bab 1', 'Sumatif Lingkup Materi 1', 'Praktik / Proyek Kelompok', 'Sumatif Akhir Semester (SAS)'] as $contohNama)
                        <button type="button"
                                onclick="document.getElementById('nama').value = '{{ $contohNama }}'"
                                class="rounded-lg border border-slate-200 bg-slate-50 px-2 py-0.5 text-[11px] font-semibold text-slate-600 hover:border-primary-300 hover:bg-primary-50 hover:text-primary transition">
                            + {{ $contohNama }}
                        </button>
                    @endforeach
                </div>
            </x-bidang>

            <x-bidang label="Bab / Lingkup Materi" nama="bab_id">
                <select name="bab_id" id="bab_id" x-model="babId"
                        class="block w-full rounded-xl border-slate-200 text-sm shadow-xs focus:border-primary focus:ring-primary">
                    <option value="">— Tidak ditautkan —</option>
                    @foreach ($babList as $bab)
                        <option value="{{ $bab->id }}" @selected(old('bab_id', $penilaian->bab_id) == $bab->id)>
                            {{ $bab->kode }} — {{ $bab->judul }}
                        </option>
                    @endforeach
                </select>
            </x-bidang>

            <x-bidang label="Tujuan Pembelajaran" nama="tujuan_pembelajaran_id"
                      petunjuk="Dipakai untuk menyusun deskripsi capaian di rapor secara otomatis.">
                <select name="tujuan_pembelajaran_id" id="tujuan_pembelajaran_id"
                        class="block w-full rounded-xl border-slate-200 text-sm shadow-xs focus:border-primary focus:ring-primary">
                    <option value="">— Tidak ditautkan —</option>
                    <template x-for="tp in (tujuan[babId] || [])" :key="tp.id">
                        <option :value="tp.id" x-text="tp.label"
                                :selected="tp.id == {{ (int) old('tujuan_pembelajaran_id', $penilaian->tujuan_pembelajaran_id) }}"></option>
                    </template>
                </select>
            </x-bidang>

            <x-bidang label="Nilai Maksimal" nama="nilai_maksimal" :wajib="true"
                      petunjuk="Nilai akan diskalakan ke 0–100 saat dihitung.">
                <x-isian nama="nilai_maksimal" tipe="number" min="1" max="1000"
                         :value="old('nilai_maksimal', $penilaian->nilai_maksimal ?? 100)"/>
            </x-bidang>

            <x-bidang label="Bobot Tambahan" nama="bobot"
                      petunjuk="Kosongkan untuk memakai bobot bawaan dari Pengaturan Sekolah.">
                <x-isian nama="bobot" tipe="number" step="0.01" min="0" max="100"
                         :value="old('bobot', $penilaian->bobot)"/>
            </x-bidang>

            <div class="md:col-span-2 rounded-2xl border border-slate-200/80 bg-slate-50/70 p-4">
                <label class="flex cursor-pointer items-center gap-3 text-sm font-medium text-slate-700">
                    <input type="checkbox" name="is_remedial" value="1"
                           @checked(old('is_remedial', $penilaian->is_remedial))
                           class="h-4 w-4 rounded border-slate-300 text-accent focus:ring-accent">
                    <span>Penilaian remedial (nilai akhir memakai nilai tertinggi, dibatasi maksimal sebesar KKTP)</span>
                </label>
            </div>
        </div>

        <div class="mt-6 flex justify-end gap-2.5 border-t border-slate-100 pt-5">
            <x-tombol gaya="halus" :href="route('penilaian.index')">Batal</x-tombol>
            <x-tombol gaya="aksen" ikon="check">{{ $penilaian->exists ? 'Simpan Perubahan' : 'Buat Penilaian' }}</x-tombol>
        </div>
    </form>
@endsection
