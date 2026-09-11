@extends('layouts.app')
@section('judul', $folder->nama)

@section('konten')
    <x-kepala-halaman :judul="$folder->nama"
                      :keterangan="$folder->mataPelajaran->nama.($folder->induk ? ' · di dalam '.$folder->induk->nama : '')">
        <x-slot:aksi>
            <x-tombol gaya="aksen" ikon="plus" @click="$dispatch('buka-modal', 'materi-baru')">Tambah Materi</x-tombol>
            <x-tombol gaya="garis" ikon="folder-plus" @click="$dispatch('buka-modal', 'subfolder-baru')">Subfolder</x-tombol>
            <x-tombol gaya="halus" ikon="arrow-left" :href="route('materi.index')">Kembali</x-tombol>
        </x-slot:aksi>
    </x-kepala-halaman>

    @if ($subFolder->isNotEmpty())
        <div class="mb-6 grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
            @foreach ($subFolder as $sub)
                <a href="{{ route('materi.folder.show', $sub) }}"
                   class="kartu kartu-hover flex items-center gap-3 p-4">
                    <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-gradient-to-br {{ $sub->warna }} text-white">
                        <x-heroicon-o-folder class="h-5 w-5"/>
                    </div>
                    <div class="min-w-0">
                        <p class="truncate font-semibold text-slate-800">{{ $sub->nama }}</p>
                        <p class="text-xs text-slate-500">{{ $sub->materis_count }} materi</p>
                    </div>
                </a>
            @endforeach
        </div>
    @endif

    <x-tabel>
        <x-slot:kepala>
            <tr>
                <th class="px-4 py-3">Materi</th>
                <th class="px-4 py-3">Bab</th>
                <th class="px-4 py-3">Tipe</th>
                <th class="px-4 py-3 text-center">Ukuran</th>
                <th class="px-4 py-3 text-center">Unduhan</th>
                <th class="px-4 py-3 text-right">Aksi</th>
            </tr>
        </x-slot:kepala>

        @forelse ($materis as $materi)
            <tr class="hover:bg-slate-50">
                <td class="px-4 py-3">
                    <div class="flex items-center gap-3">
                        <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-slate-100 text-primary">
                            <x-dynamic-component :component="'heroicon-o-'.$materi->ikon" class="h-5 w-5"/>
                        </div>
                        <div class="min-w-0">
                            <p class="truncate font-medium text-slate-800">{{ $materi->judul }}</p>
                            @if ($materi->deskripsi)
                                <p class="line-clamp-1 text-xs text-slate-500">{{ $materi->deskripsi }}</p>
                            @endif
                        </div>
                    </div>
                </td>
                <td class="px-4 py-3 text-sm text-slate-600">{{ $materi->bab?->kode ?? '—' }}</td>
                <td class="px-4 py-3">
                    <span class="rounded-full bg-slate-100 px-2 py-0.5 text-xs font-semibold text-slate-600">
                        {{ ucfirst($materi->tipe) }}{{ $materi->ekstensi ? ' · '.strtoupper($materi->ekstensi) : '' }}
                    </span>
                </td>
                <td class="px-4 py-3 text-center text-sm text-slate-500">{{ $materi->ukuran_terbaca }}</td>
                <td class="px-4 py-3 text-center text-sm text-slate-500">{{ $materi->jumlah_unduh }}</td>
                <td class="px-4 py-3">
                    <div class="flex justify-end gap-1">
                        <a href="{{ route('materi.unduh', $materi) }}" title="Buka / Unduh"
                           class="tombol-sentuh rounded-lg p-2 text-slate-500 hover:bg-slate-100 hover:text-primary">
                            <x-heroicon-o-arrow-down-tray class="h-5 w-5"/>
                        </a>
                        <form method="POST" action="{{ route('materi.destroy', $materi) }}"
                              onsubmit="return confirm('Hapus materi ini? Berkas ikut terhapus permanen.')">
                            @csrf @method('DELETE')
                            <button type="submit" title="Hapus"
                                    class="tombol-sentuh rounded-lg p-2 text-slate-500 hover:bg-rose-50 hover:text-rose-600">
                                <x-heroicon-o-trash class="h-5 w-5"/>
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
    <x-modal nama="materi-baru" judul="Tambah Materi">
        <form method="POST" action="{{ route('materi.store', $folder) }}" id="form-materi-baru"
              enctype="multipart/form-data" class="space-y-4" x-data="{ tipe: 'file' }">
            @csrf

            <x-bidang label="Judul Materi" nama="judul" :wajib="true">
                <x-isian nama="judul"/>
            </x-bidang>

            <x-bidang label="Tipe" nama="tipe" :wajib="true">
                <select name="tipe" id="tipe" x-model="tipe"
                        class="block w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-primary focus:ring-primary">
                    <option value="file">Berkas (PDF, Word, PPT, Gambar)</option>
                    <option value="tautan">Tautan (Google Drive, dll.)</option>
                    <option value="video">Video (YouTube)</option>
                </select>
            </x-bidang>

            <div x-show="tipe === 'file'">
                <x-bidang label="Berkas" nama="berkas" petunjuk="Maksimal 20 MB.">
                    <input type="file" name="berkas" id="berkas"
                           accept=".pdf,.doc,.docx,.ppt,.pptx,.xls,.xlsx,.jpg,.jpeg,.png,.gif,.webp,.zip"
                           class="block w-full text-sm file:mr-3 file:rounded-lg file:border-0 file:bg-slate-100 file:px-4 file:py-2 file:text-sm file:font-semibold">
                </x-bidang>
            </div>

            <div x-show="tipe !== 'file'" x-cloak>
                <x-bidang label="Tautan" nama="url">
                    <x-isian nama="url" tipe="url" placeholder="https://…"/>
                </x-bidang>
            </div>

            <x-bidang label="Tautkan ke Bab" nama="bab_id"
                      petunjuk="Materi akan muncul sebagai saran saat mengisi agenda.">
                <x-pilihan nama="bab_id" :opsi="$babList->pluck('label', 'id')" kosong="— Tidak ditautkan —"/>
            </x-bidang>

            <x-bidang label="Deskripsi" nama="deskripsi">
                <textarea name="deskripsi" id="deskripsi" rows="2"
                          class="block w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-primary focus:ring-primary"></textarea>
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
