@extends('layouts.app')
@section('judul', $folder->nama)

@section('konten')
    <x-kepala-halaman :judul="$folder->nama"
                      :keterangan="$folder->mataPelajaran->nama.($folder->induk ? ' · di dalam '.$folder->induk->nama : '')">
        <x-slot:aksi>
            <x-tombol gaya="aksen" ikon="plus" @click="$dispatch('buka-modal', 'materi-baru')">Tambah Materi</x-tombol>
            <x-tombol gaya="garis" ikon="folder-plus" @click="$dispatch('buka-modal', 'subfolder-baru')">Subfolder</x-tombol>
            <x-tombol gaya="halus" ikon="arrow-left" :href="$folder->induk ? route('materi.folder.show', $folder->induk) : route('materi.index')">Kembali</x-tombol>
        </x-slot:aksi>
    </x-kepala-halaman>

    @if ($subFolder->isNotEmpty())
        <div class="mb-6 grid gap-3.5 sm:grid-cols-2 lg:grid-cols-4">
            @foreach ($subFolder as $sub)
                <a href="{{ route('materi.folder.show', $sub) }}"
                   class="kartu kartu-hover flex items-center gap-3.5 p-4">
                    <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-gradient-to-br {{ $sub->warna }} text-white shadow-xs">
                        <x-heroicon-o-folder class="h-5 w-5"/>
                    </div>
                    <div class="min-w-0">
                        <p class="truncate font-bold text-slate-800">{{ $sub->nama }}</p>
                        <p class="text-xs font-medium text-slate-500">{{ $sub->materis_count }} materi</p>
                    </div>
                </a>
            @endforeach
        </div>
    @endif

    <x-tabel>
        <x-slot:kepala>
            <tr>
                <th class="px-4 py-3.5">Materi Pembelajaran</th>
                <th class="px-4 py-3.5">Bab</th>
                <th class="px-4 py-3.5">Tipe</th>
                <th class="px-4 py-3.5 text-center">Ukuran</th>
                <th class="px-4 py-3.5 text-center">Unduhan</th>
                <th class="px-4 py-3.5 text-right">Aksi</th>
            </tr>
        </x-slot:kepala>

        @forelse ($materis as $materi)
            <tr class="hover:bg-indigo-50/30 transition">
                <td class="px-4 py-3.5">
                    <div class="flex items-center gap-3">
                        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-indigo-50 text-primary">
                            <x-dynamic-component :component="'heroicon-o-'.$materi->ikon" class="h-5 w-5"/>
                        </div>
                        <div class="min-w-0">
                            <p class="truncate font-bold text-slate-800">{{ $materi->judul }}</p>
                            @if ($materi->deskripsi)
                                <p class="line-clamp-1 text-xs text-slate-500">{{ $materi->deskripsi }}</p>
                            @endif
                        </div>
                    </div>
                </td>
                <td class="px-4 py-3.5 text-sm font-semibold text-primary">{{ $materi->bab?->kode ?? '—' }}</td>
                <td class="px-4 py-3.5">
                    <span class="rounded-full bg-slate-100 px-2.5 py-0.5 text-xs font-bold text-slate-600">
                        {{ ucfirst($materi->tipe) }}{{ $materi->ekstensi ? ' · '.strtoupper($materi->ekstensi) : '' }}
                    </span>
                </td>
                <td class="px-4 py-3.5 text-center text-xs font-semibold text-slate-500">{{ $materi->ukuran_terbaca }}</td>
                <td class="px-4 py-3.5 text-center text-xs font-bold text-slate-600">{{ $materi->jumlah_unduh }}x</td>
                <td class="px-4 py-3.5">
                    <div class="flex justify-end items-center gap-1.5">
                        <a href="{{ route('materi.unduh', $materi) }}" title="Buka / Unduh"
                           class="inline-flex items-center gap-1 rounded-xl border border-slate-200 bg-white px-2.5 py-1.5 text-xs font-bold text-slate-700 hover:border-primary-200 hover:bg-primary-50 hover:text-primary transition">
                            <x-heroicon-o-arrow-down-tray class="h-4 w-4"/>
                            <span>Buka</span>
                        </a>
                        <form method="POST" action="{{ route('materi.destroy', $materi) }}"
                              onsubmit="return confirm('Hapus materi ini? Berkas ikut terhapus permanen.')">
                            @csrf @method('DELETE')
                            <button type="submit" title="Hapus"
                                    class="inline-flex cursor-pointer items-center justify-center rounded-xl border border-slate-200 bg-white p-1.5 text-slate-400 hover:border-rose-200 hover:bg-rose-50 hover:text-rose-600 transition">
                                <x-heroicon-o-trash class="h-4 w-4"/>
                            </button>
                        </form>
                    </div>
                </td>
            </tr>
        @empty
            <x-tabel.kosong :kolom="6" ikon="document"
                            judul="Folder ini masih kosong"
                            pesan="Unggah berkas (maks. 20 MB) atau simpan tautan Google Drive / YouTube."/>
        @endforelse
    </x-tabel>

    {{-- Modal tambah materi --}}
    <x-modal nama="materi-baru" judul="Tambah Materi Pembelajaran">
        <form method="POST" action="{{ route('materi.store', $folder) }}" id="form-materi-baru"
              enctype="multipart/form-data" class="space-y-4" x-data="{ tipe: 'file' }">
            @csrf

            <x-bidang label="Judul Materi" nama="judul" :wajib="true">
                <x-isian nama="judul" placeholder="mis. Modul Ajar Bab 1"/>
            </x-bidang>

            <x-bidang label="Tipe Materi" nama="tipe" :wajib="true">
                <select name="tipe" id="tipe" x-model="tipe"
                        class="block w-full rounded-xl border-slate-200 text-sm shadow-xs focus:border-primary focus:ring-primary">
                    <option value="file">Berkas (PDF, Word, PPT, Gambar)</option>
                    <option value="tautan">Tautan (Google Drive, Canva, dll.)</option>
                    <option value="video">Video (YouTube)</option>
                </select>
            </x-bidang>

            <div x-show="tipe === 'file'">
                <x-bidang label="Berkas" nama="berkas" petunjuk="Maksimal 20 MB.">
                    <input type="file" name="berkas" id="berkas"
                           accept=".pdf,.doc,.docx,.ppt,.pptx,.xls,.xlsx,.jpg,.jpeg,.png,.gif,.webp,.zip"
                           class="block w-full text-sm file:mr-3 file:rounded-xl file:border-0 file:bg-indigo-50 file:px-4 file:py-2 file:text-xs file:font-bold file:text-primary hover:file:bg-indigo-100">
                </x-bidang>
            </div>

            <div x-show="tipe !== 'file'" x-cloak>
                <x-bidang label="Tautan URL" nama="url">
                    <x-isian nama="url" tipe="url" placeholder="https://…"/>
                </x-bidang>
            </div>

            <x-bidang label="Tautkan ke Bab" nama="bab_id"
                      petunjuk="Materi akan muncul sebagai saran saat mengisi agenda.">
                <x-pilihan nama="bab_id" :opsi="$babList->pluck('label', 'id')" kosong="— Tidak ditautkan —"/>
            </x-bidang>

            <x-bidang label="Deskripsi Singkat" nama="deskripsi">
                <textarea name="deskripsi" id="deskripsi" rows="2"
                          class="block w-full rounded-xl border-slate-200 text-sm shadow-xs focus:border-primary focus:ring-primary"></textarea>
            </x-bidang>
        </form>

        <x-slot:kaki>
            <x-tombol tipe="button" gaya="halus" @click="$dispatch('tutup-modal', 'materi-baru')">Batal</x-tombol>
            <x-tombol gaya="aksen" ikon="check" form="form-materi-baru">Simpan Materi</x-tombol>
        </x-slot:kaki>
    </x-modal>

    {{-- Modal subfolder --}}
    <x-modal nama="subfolder-baru" judul="Subfolder Baru">
        <form method="POST" action="{{ route('materi.folder.store') }}" id="form-subfolder" class="space-y-4">
            @csrf
            <input type="hidden" name="parent_id" value="{{ $folder->id }}">
            <input type="hidden" name="mata_pelajaran_id" value="{{ $folder->mata_pelajaran_id }}">

            <x-bidang label="Nama Subfolder" nama="nama" :wajib="true">
                <x-isian nama="nama"/>
            </x-bidang>

            <x-bidang label="Warna Kartu" nama="warna" :wajib="true">
                <x-pilihan nama="warna" :opsi="$warnaList" :terpilih="$folder->warna"/>
            </x-bidang>
        </form>

        <x-slot:kaki>
            <x-tombol tipe="button" gaya="halus" @click="$dispatch('tutup-modal', 'subfolder-baru')">Batal</x-tombol>
            <x-tombol gaya="aksen" ikon="check" form="form-subfolder">Buat Subfolder</x-tombol>
        </x-slot:kaki>
    </x-modal>
@endsection
