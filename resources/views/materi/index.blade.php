@extends('layouts.app')
@section('judul', 'Bank Materi')

@section('konten')
    <x-kepala-halaman judul="Bank Materi"
                      keterangan="Kumpulan bahan ajar per mata pelajaran, dapat ditautkan ke kelas dan bab.">
        <x-slot:aksi>
            <x-tombol gaya="aksen" ikon="folder-plus" @click="$dispatch('buka-modal', 'folder-baru')">
                Folder Baru
            </x-tombol>
        </x-slot:aksi>
    </x-kepala-halaman>

    @if ($folders->isEmpty())
        <div class="kartu p-12 text-center">
            <x-heroicon-o-folder class="mx-auto h-14 w-14 text-slate-300"/>
            <p class="mt-3 text-lg font-semibold text-slate-700">Bank materi masih kosong</p>
            <p class="text-sm text-slate-500">Buat folder per mata pelajaran, lalu unggah berkas atau simpan tautan.</p>
            <x-tombol gaya="aksen" ikon="folder-plus" class="mt-4" @click="$dispatch('buka-modal', 'folder-baru')">
                Buat Folder Pertama
            </x-tombol>
        </div>
    @else
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($folders as $folder)
                <a href="{{ route('materi.folder.show', $folder) }}" class="kartu kartu-hover overflow-hidden">
                    <div class="bg-gradient-to-br {{ $folder->warna }} p-5 text-white">
                        <x-heroicon-o-folder class="h-8 w-8 opacity-90"/>
                        <p class="mt-3 truncate text-lg font-bold">{{ $folder->nama }}</p>
                        <p class="truncate text-xs text-white/75">{{ $folder->mataPelajaran->nama }}</p>
                    </div>
                    <div class="p-4">
                        @if ($folder->deskripsi)
                            <p class="line-clamp-2 text-sm text-slate-600">{{ $folder->deskripsi }}</p>
                        @endif
                        <div class="mt-3 flex flex-wrap items-center gap-2 text-xs text-slate-500">
                            <span class="inline-flex items-center gap-1">
                                <x-heroicon-o-document class="h-4 w-4"/> {{ $folder->materis_count }} materi
                            </span>
                            @if ($folder->anak_count)
                                <span class="inline-flex items-center gap-1">
                                    <x-heroicon-o-folder class="h-4 w-4"/> {{ $folder->anak_count }} subfolder
                                </span>
                            @endif
                        </div>
                        @if ($folder->kelas->isNotEmpty())
                            <div class="mt-2 flex flex-wrap gap-1">
                                @foreach ($folder->kelas as $kelas)
                                    <span class="rounded-full bg-slate-100 px-2 py-0.5 text-[11px] text-slate-600">{{ $kelas->nama }}</span>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </a>
            @endforeach
        </div>
    @endif

    {{-- Modal folder baru --}}
    <x-modal nama="folder-baru" judul="Folder Materi Baru">
        <form method="POST" action="{{ route('materi.folder.store') }}" id="form-folder-baru" class="space-y-4">
            @csrf

            <x-bidang label="Nama Folder" nama="nama" :wajib="true">
                <x-isian nama="nama" placeholder="mis. Teks Editorial Kelas XII"/>
            </x-bidang>

            <x-bidang label="Mata Pelajaran" nama="mata_pelajaran_id" :wajib="true">
                <x-pilihan nama="mata_pelajaran_id" :opsi="$mapelList" kosong="— Pilih mapel —"/>
            </x-bidang>

            <x-bidang label="Deskripsi" nama="deskripsi">
                <textarea name="deskripsi" id="deskripsi" rows="2"
                          class="block w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-primary focus:ring-primary"></textarea>
            </x-bidang>

            <x-bidang label="Warna Kartu" nama="warna" :wajib="true">
                <x-pilihan nama="warna" :opsi="$warnaList"/>
            </x-bidang>

            <x-bidang label="Tautkan ke Kelas" nama="kelas"
                      petunjuk="Boleh lebih dari satu kelas.">
                <div class="max-h-40 space-y-1.5 overflow-y-auto rounded-lg border border-slate-200 p-3">
                    @forelse ($kelasList as $kelas)
                        <label class="flex cursor-pointer items-center gap-2 text-sm text-slate-700">
                            <input type="checkbox" name="kelas[]" value="{{ $kelas->id }}"
                                   class="h-4 w-4 rounded border-slate-300 text-accent focus:ring-accent">
                            {{ $kelas->nama }}
                        </label>
                    @empty
                        <p class="text-sm text-slate-400">Belum ada kelas yang Anda ampu.</p>
                    @endforelse
                </div>
            </x-bidang>
        </form>

        <x-slot:kaki>
            <x-tombol tipe="button" gaya="halus" @click="$dispatch('tutup-modal', 'folder-baru')">Batal</x-tombol>
            <x-tombol gaya="aksen" ikon="check" form="form-folder-baru">Buat Folder</x-tombol>
        </x-slot:kaki>
    </x-modal>
@endsection
