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
        <div class="kartu mb-6 p-5 bg-gradient-to-r from-indigo-50/70 via-white to-teal-50/40">
            <p class="mb-1 text-xs font-extrabold uppercase tracking-wider text-primary">Capaian Pembelajaran (CP)</p>
            <p class="text-sm leading-relaxed text-slate-700">{{ $bab->capaian_pembelajaran }}</p>
        </div>
    @endif

    <div class="grid gap-6 lg:grid-cols-3">
        <div class="lg:col-span-2">
            <x-tabel>
                <x-slot:kepala>
                    <tr>
                        <th class="px-4 py-3.5">Kode</th>
                        <th class="px-4 py-3.5">Deskripsi Tujuan Pembelajaran</th>
                        <th class="px-4 py-3.5 text-center">Urutan</th>
                        <th class="px-4 py-3.5 text-right">Aksi</th>
                    </tr>
                </x-slot:kepala>

                @forelse ($bab->tujuanPembelajarans as $tp)
                    <tr class="hover:bg-indigo-50/30 transition">
                        <td class="whitespace-nowrap px-4 py-3.5">
                            <span class="rounded-lg bg-indigo-50 px-2.5 py-1 text-xs font-extrabold text-primary">
                                {{ $tp->kode }}
                            </span>
                        </td>
                        <td class="px-4 py-3.5 text-sm leading-relaxed text-slate-700">{{ $tp->deskripsi }}</td>
                        <td class="px-4 py-3.5 text-center font-bold text-slate-500">{{ $tp->urutan }}</td>
                        <td class="px-4 py-3.5 text-right">
                            <form method="POST" action="{{ route('tujuan.destroy', $tp) }}"
                                  onsubmit="return confirm('Hapus tujuan pembelajaran ini?')">
                                @csrf @method('DELETE')
                                <button type="submit" title="Hapus"
                                        class="inline-flex cursor-pointer items-center justify-center rounded-xl border border-slate-200 bg-white p-1.5 text-slate-400 hover:border-rose-200 hover:bg-rose-50 hover:text-rose-600 transition">
                                    <x-heroicon-o-trash class="h-4 w-4"/>
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
            <form method="POST" action="{{ route('tujuan.store', $bab) }}" class="kartu p-5 sm:p-6">
                @csrf
                <h3 class="mb-4 text-base font-extrabold text-slate-800">Tambah Tujuan Pembelajaran</h3>

                <div class="space-y-4">
                    <x-bidang label="Kode TP" nama="kode" :wajib="true" petunjuk="mis. TP 1.1">
                        <x-isian nama="kode" :value="old('kode', 'TP '.($bab->urutan ?? 1).'.'.($bab->tujuanPembelajarans->count() + 1))"/>
                    </x-bidang>

                    <x-bidang label="Deskripsi TP" nama="deskripsi" :wajib="true">
                        <textarea name="deskripsi" id="deskripsi" rows="4"
                                  placeholder="Peserta didik mampu menganalisis struktur teks editorial…"
                                  class="block w-full rounded-xl border-slate-200 text-sm shadow-xs focus:border-primary focus:ring-primary">{{ old('deskripsi') }}</textarea>
                        <div class="mt-2 flex flex-wrap gap-1.5">
                            <span class="w-full text-[10px] font-bold uppercase tracking-wider text-slate-400">Awalan Kata Kerja Operasional:</span>
                            @foreach (['Peserta didik mampu memahami ', 'Peserta didik mampu menganalisis ', 'Peserta didik mampu menyajikan ', 'Peserta didik mampu mengevaluasi '] as $awalan)
                                <button type="button"
                                        onclick="const el = document.getElementById('deskripsi'); el.value = '{{ $awalan }}'; el.focus();"
                                        class="rounded-lg border border-slate-200 bg-slate-50 px-2 py-0.5 text-[11px] font-semibold text-slate-600 hover:border-primary-300 hover:bg-primary-50 hover:text-primary transition">
                                    + {{ Str::replaceFirst('Peserta didik ', '', $awalan) }}…
                                </button>
                            @endforeach
                        </div>
                    </x-bidang>

                    <x-bidang label="Urutan" nama="urutan" :wajib="true">
                        <x-isian nama="urutan" tipe="number" min="1" max="100"
                                 :value="old('urutan', $bab->tujuanPembelajarans->count() + 1)"/>
                    </x-bidang>
                </div>

                <x-tombol gaya="aksen" ikon="plus" class="mt-5 w-full">Tambah TP</x-tombol>
            </form>
        </div>
    </div>
@endsection
