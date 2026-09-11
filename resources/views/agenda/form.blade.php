@extends('layouts.app')
@section('judul', 'Ubah Agenda')

@php use App\Support\Tanggal; @endphp

@section('konten')
    <x-kepala-halaman judul="Ubah Agenda Mengajar"
                      :keterangan="($agenda->jadwal?->kelas_tampilan ?? '—').' — '.($agenda->jadwal?->nama_tampilan ?? '—').' · '.Tanggal::lengkap($agenda->tanggal)">
        <x-slot:aksi>
            <x-tombol gaya="halus" ikon="arrow-left" :href="route('agenda.index')">Kembali</x-tombol>
        </x-slot:aksi>
    </x-kepala-halaman>

    <form method="POST" action="{{ route('agenda.update', $agenda) }}" class="kartu max-w-3xl p-6">
        @csrf @method('PUT')

        <div class="grid gap-4 md:grid-cols-2">
            <x-bidang label="Judul Materi" nama="judul_materi" :wajib="true" class="md:col-span-2">
                <x-isian nama="judul_materi" :value="old('judul_materi', $agenda->judul_materi)"/>
            </x-bidang>

            <x-bidang label="Bab / Lingkup Materi" nama="bab_id">
                <x-pilihan nama="bab_id" :opsi="$babList->pluck('label', 'id')"
                           :terpilih="$agenda->bab_id" kosong="— Tidak ditautkan —"/>
            </x-bidang>

            <x-bidang label="Status Pertemuan" nama="status" :wajib="true">
                <x-pilihan nama="status" :opsi="$statusList" :terpilih="$agenda->status->value"/>
            </x-bidang>

            <x-bidang label="Metode" nama="metode" class="md:col-span-2">
                <x-isian nama="metode" :value="old('metode', $agenda->metode)" placeholder="mis. Diskusi kelompok, presentasi"/>
            </x-bidang>

            <x-bidang label="Uraian Kegiatan" nama="uraian_kegiatan" class="md:col-span-2">
                <textarea name="uraian_kegiatan" id="uraian_kegiatan" rows="4"
                          class="block w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-primary focus:ring-primary">{{ old('uraian_kegiatan', $agenda->uraian_kegiatan) }}</textarea>
            </x-bidang>

            <x-bidang label="Catatan" nama="catatan" class="md:col-span-2"
                      petunjuk="Kejadian khusus, tindak lanjut, atau catatan untuk pertemuan berikutnya.">
                <textarea name="catatan" id="catatan" rows="3"
                          class="block w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-primary focus:ring-primary">{{ old('catatan', $agenda->catatan) }}</textarea>
            </x-bidang>
        </div>

        <div class="mt-6 flex justify-end gap-2">
            <x-tombol gaya="halus" :href="route('agenda.index')">Batal</x-tombol>
            <x-tombol gaya="aksen" ikon="check">Simpan Perubahan</x-tombol>
        </div>
    </form>
@endsection
