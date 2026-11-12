@extends('layouts.app')
@section('judul', 'Bank Materi')

@section('konten')
    <x-kepala-halaman judul="Bank Materi & Bahan Ajar"
                      keterangan="Kumpulan bahan ajar per mata pelajaran, dapat ditautkan langsung ke kelas dan bab.">
        <x-slot:aksi>
            <x-tombol gaya="aksen" ikon="folder-plus" @click="$dispatch('buka-modal', 'folder-baru')">
                Folder Baru
            </x-tombol>
        </x-slot:aksi>
    </x-kepala-halaman>

    @if ($folders->isEmpty())
        <div class="kartu p-12 text-center">
            <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-2xl bg-indigo-50 text-primary">
                <x-heroicon-o-folder class="h-9 w-9"/>
            </div>
            <p class="mt-4 text-lg font-extrabold text-slate-800">Bank materi masih kosong</p>
            <p class="mt-1 text-sm text-slate-500">Buat folder per mata pelajaran, lalu unggah berkas modul/PPT atau simpan tautan video.</p>
            <x-tombol gaya="aksen" ikon="folder-plus" class="mt-5" @click="$dispatch('buka-modal', 'folder-baru')">
                Buat Folder Pertama
            </x-tombol>
        </div>
    @else
        <div x-data="{ cari: '' }">
            @if ($folders->count() > 3)
                <div class="mb-5 relative max-w-sm">
                    <x-heroicon-o-magnifying-glass class="pointer-events-none absolute left-3.5 top-1/2 -translate-y-1/2 h-4 w-4 text-slate-400"/>
                    <input type="text" x-model="cari" placeholder="Cari folder materi atau mata pelajaran..."
                           class="w-full rounded-xl border-slate-200 bg-white pl-10 pr-4 py-2 text-sm text-slate-800 shadow-xs focus:border-primary focus:ring-primary">
                </div>
            @endif

            <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($folders as $folder)
                    <a href="{{ route('materi.folder.show', $folder) }}"
                       x-show="!cari || {{ Js::from(strtolower($folder->nama.' '.$folder->mataPelajaran->nama)) }}.includes(cari.toLowerCase())"
                       class="kartu kartu-hover group overflow-hidden flex flex-col justify-between">
                        <div>
                            <div class="relative overflow-hidden bg-gradient-to-br {{ $folder->warna }} p-5 text-white">
                                <div class="pointer-events-none absolute -right-6 -top-6 h-24 w-24 rounded-full bg-white/10 blur-xl"></div>
                                <div class="flex items-center justify-between">
                                    <div class="flex h-11 w-11 items-center justify-center rounded-2xl bg-white/20 backdrop-blur-xs">
                                        <x-heroicon-o-folder class="h-6 w-6 text-white"/>
                                    </div>
                                    <span class="rounded-full bg-black/15 px-2.5 py-1 text-[11px] font-bold text-white/90 backdrop-blur-xs">
                                        {{ $folder->materis_count }} berkas
                                    </span>
                                </div>
                                <p class="mt-4 truncate text-lg font-extrabold tracking-tight">{{ $folder->nama }}</p>
                                <p class="truncate text-xs font-medium text-white/80">{{ $folder->mataPelajaran->nama }}</p>
                            </div>
                            <div class="p-4">
                                @if ($folder->deskripsi)
                                    <p class="line-clamp-2 text-xs leading-relaxed text-slate-600 mb-3">{{ $folder->deskripsi }}</p>
                                @endif
                                @if ($folder->kelas->isNotEmpty())
                                    <div class="flex flex-wrap gap-1.5">
                                        @foreach ($folder->kelas as $kelas)
                                            <span class="rounded-lg bg-slate-100 px-2 py-0.5 text-[11px] font-bold text-slate-600">{{ $kelas->nama }}</span>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        </div>

                        <div class="flex items-center justify-between border-t border-slate-100 px-4 py-3 text-xs font-bold text-slate-500">
                            <span>
                                @if ($folder->anak_count)
                                    {{ $folder->anak_count }} subfolder ·
                                @endif
                                {{ $folder->materis_count }} materi
                            </span>
                            <span class="text-primary group-hover:translate-x-1 transition-transform">Buka Folder →</span>
                        </div>
                    </a>
                @endforeach
            </div>
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
                          class="block w-full rounded-xl border-slate-200 text-sm shadow-xs focus:border-primary focus:ring-primary"></textarea>
            </x-bidang>

            <x-bidang label="Warna Kartu" nama="warna" :wajib="true">
                <x-pilihan nama="warna" :opsi="$warnaList"/>
            </x-bidang>

            <x-bidang label="Tautkan ke Kelas" nama="kelas"
                      petunjuk="Boleh memilih lebih dari satu kelas.">
                <div class="max-h-40 space-y-1.5 overflow-y-auto rounded-xl border border-slate-200 p-3">
                    @forelse ($kelasList as $kelas)
                        <label class="flex cursor-pointer items-center gap-2.5 text-sm font-medium text-slate-700">
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
