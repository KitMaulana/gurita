@extends('layouts.app')
@section('judul', $bab->kode.' — '.$bab->judul)

@section('konten')
    <x-kepala-halaman :judul="$bab->kode.' — '.$bab->judul"
                      :keterangan="$bab->mataPelajaran->nama.' · Kelas '.$bab->tingkat">
        <x-slot:aksi>
            <x-tombol gaya="garis" ikon="pencil-square" :href="route('bab.edit', $bab)">Ubah Bab</x-tombol>
            <x-tombol gaya="halus" ikon="arrow-left" :href="route('bab.index')">Kembali</x-tombol>
        </x-slot:aksi>
    </x-kepala-halaman>

    @if ($bab->capaian_pembelajaran)
        <div class="kartu mb-6 p-5">
            <p class="mb-1 text-xs font-bold uppercase tracking-wide text-slate-400">Capaian Pembelajaran</p>
            <p class="text-sm leading-relaxed text-slate-700">{{ $bab->capaian_pembelajaran }}</p>
        </div>
    @endif

    <div class="grid gap-6 lg:grid-cols-3">
        <div class="lg:col-span-2">
            <x-tabel>
                <x-slot:kepala>
                    <tr>
                        <th class="px-4 py-3">Kode</th>
                        <th class="px-4 py-3">Deskripsi Tujuan Pembelajaran</th>
                        <th class="px-4 py-3 text-center">Urutan</th>
                        <th class="px-4 py-3 text-right">Aksi</th>
                    </tr>
                </x-slot:kepala>

                @forelse ($bab->tujuanPembelajarans as $tp)
                    <tr class="hover:bg-slate-50">
                        <td class="whitespace-nowrap px-4 py-3 font-semibold text-primary">{{ $tp->kode }}</td>
                        <td class="px-4 py-3 text-slate-700">{{ $tp->deskripsi }}</td>
                        <td class="px-4 py-3 text-center text-slate-500">{{ $tp->urutan }}</td>
                        <td class="px-4 py-3 text-right">
                            <form method="POST" action="{{ route('tujuan.destroy', $tp) }}"
                                  onsubmit="return confirm('Hapus tujuan pembelajaran ini?')">
                                @csrf @method('DELETE')
                                <button type="submit" title="Hapus"
                                        class="tombol-sentuh rounded-lg p-2 text-slate-500 hover:bg-rose-50 hover:text-rose-600">
                                    <x-heroicon-o-trash class="h-5 w-5"/>
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <x-tabel.kosong :kolom="4" ikon="list-bullet"
                                    judul="Belum ada Tujuan Pembelajaran"
                                    pesan="Tambahkan TP melalui formulir di samping."/>
                @endforelse
            </x-tabel>
        </div>

        <div>
            <form method="POST" action="{{ route('tujuan.store', $bab) }}" class="kartu p-5">
                @csrf
                <h3 class="mb-4 font-bold text-primary">Tambah Tujuan Pembelajaran</h3>

                <div class="space-y-4">
                    <x-bidang label="Kode" nama="kode" :wajib="true" petunjuk="mis. TP 1.1">
                        <x-isian nama="kode" :value="old('kode')"/>
                    </x-bidang>

                    <x-bidang label="Deskripsi" nama="deskripsi" :wajib="true">
                        <textarea name="deskripsi" id="deskripsi" rows="4"
                                  placeholder="Peserta didik mampu menganalisis struktur teks editorial…"
                                  class="block w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-primary focus:ring-primary">{{ old('deskripsi') }}</textarea>
                    </x-bidang>

                    <x-bidang label="Urutan" nama="urutan" :wajib="true">
                        <x-isian nama="urutan" tipe="number" min="1" max="100"
                                 :value="old('urutan', $bab->tujuanPembelajarans->count() + 1)"/>
                    </x-bidang>
                </div>

                <x-tombol gaya="aksen" ikon="plus" class="mt-4 w-full">Tambah TP</x-tombol>
            </form>
        </div>
    </div>
@endsection
