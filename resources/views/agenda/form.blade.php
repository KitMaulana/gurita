@extends('layouts.app')
@section('judul', 'Ubah Agenda')

@php use App\Support\Tanggal; @endphp

@section('konten')
    <x-kepala-halaman judul="Ubah Agenda Mengajar"
                      :keterangan="($agenda->jadwal?->kelas_tampilan ?? '—').' — '.($agenda->jadwal?->nama_tampilan ?? '—').' ('.$agenda->label_jp.') · '.Tanggal::lengkap($agenda->tanggal)">
        <x-slot:aksi>
            <x-tombol gaya="garis" ikon="user-group" :href="route('presensi.isi', $agenda)">Presensi Siswa</x-tombol>
            <x-tombol gaya="halus" ikon="arrow-left" :href="route('agenda.index')">Kembali</x-tombol>
        </x-slot:aksi>
    </x-kepala-halaman>

    <form method="POST" action="{{ route('agenda.update', $agenda) }}" class="kartu max-w-3xl p-6 sm:p-7">
        @csrf @method('PUT')

        <div class="grid gap-5 md:grid-cols-2">
            <x-bidang label="Judul Materi / Topik" nama="judul_materi" :wajib="true" class="md:col-span-2">
                <x-isian nama="judul_materi" :value="old('judul_materi', $agenda->judul_materi)"/>
            </x-bidang>

            <x-bidang label="Bab / Lingkup Materi" nama="bab_id">
                <x-pilihan nama="bab_id" :opsi="$babList->pluck('label', 'id')"
                           :terpilih="$agenda->bab_id" kosong="— Tidak ditautkan —"/>
            </x-bidang>

            <x-bidang label="Status Pertemuan" nama="status" :wajib="true">
                <x-pilihan nama="status" :opsi="$statusList" :terpilih="$agenda->status->value"/>
            </x-bidang>

            <x-bidang label="Metode Pembelajaran" nama="metode" class="md:col-span-2">
                <x-isian nama="metode" :value="old('metode', $agenda->metode)" placeholder="mis. Diskusi kelompok, presentasi"/>
                <div class="mt-2 flex flex-wrap items-center gap-1.5">
                    <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Klik Cepat:</span>
                    @foreach (['Ceramah Interaktif', 'Diskusi Kelompok', 'Praktik / Demonstrasi', 'PBL', 'PjBL', 'Latihan Soal & Kuis'] as $chipMetode)
                        <button type="button"
                                onclick="document.getElementById('metode').value = '{{ $chipMetode }}'"
                                class="rounded-lg border border-slate-200 bg-slate-50 px-2 py-0.5 text-[11px] font-semibold text-slate-600 hover:border-primary-300 hover:bg-primary-50 hover:text-primary transition">
                            + {{ $chipMetode }}
                        </button>
                    @endforeach
                </div>
            </x-bidang>

            <x-bidang label="Uraian Kegiatan" nama="uraian_kegiatan" class="md:col-span-2">
                <textarea name="uraian_kegiatan" id="uraian_kegiatan" rows="4"
                          class="block w-full rounded-xl border-slate-200 text-sm shadow-xs focus:border-primary focus:ring-primary">{{ old('uraian_kegiatan', $agenda->uraian_kegiatan) }}</textarea>
            </x-bidang>

            <x-bidang label="Catatan Tambahan" nama="catatan" class="md:col-span-2"
                      petunjuk="Kejadian khusus, tindak lanjut, atau catatan untuk pertemuan berikutnya.">
                <textarea name="catatan" id="catatan" rows="3"
                          class="block w-full rounded-xl border-slate-200 text-sm shadow-xs focus:border-primary focus:ring-primary">{{ old('catatan', $agenda->catatan) }}</textarea>
            </x-bidang>
        </div>

        <div class="mt-6 flex justify-end gap-2.5 border-t border-slate-100 pt-5">
            <x-tombol gaya="halus" :href="route('agenda.index')">Batal</x-tombol>
            <x-tombol gaya="aksen" ikon="check">Simpan Perubahan</x-tombol>
        </div>
    </form>
@endsection
