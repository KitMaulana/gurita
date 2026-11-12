@extends('layouts.app')
@section('judul', 'Daftar Nilai')

@section('konten')
    <x-kepala-halaman judul="Daftar Nilai Siswa"
                      :keterangan="$kelas?->nama ? 'Kelas '.$kelas->nama.' — '.$mapelList->firstWhere('id', $mataPelajaranId)?->nama : 'Pilih kelas dan mata pelajaran untuk mulai mengisi nilai.'">
        <x-slot:aksi>
            @if ($kelas && $mataPelajaranId)
                <x-tombol gaya="garis" ikon="arrow-down-tray" :href="route('nilai.ekspor', request()->query())">Excel</x-tombol>
                <x-tombol gaya="garis" ikon="printer" :href="route('nilai.cetak', request()->query())">Cetak</x-tombol>
            @endif
            <x-tombol gaya="aksen" ikon="plus"
                      :href="route('penilaian.create', ['kelas_id' => $kelas?->id, 'mata_pelajaran_id' => $mataPelajaranId])">
                Buat Penilaian
            </x-tombol>
        </x-slot:aksi>
    </x-kepala-halaman>

    <form method="GET" class="kartu tanpa-cetak mb-6 flex flex-wrap items-end gap-3.5 p-4 sm:p-5">
        <x-bidang label="Kelas" nama="kelas_id" class="min-w-52 flex-1">
            <x-pilihan nama="kelas_id" :opsi="$kelasList->pluck('nama', 'id')" :terpilih="$kelas?->id"/>
        </x-bidang>
        <x-bidang label="Mata Pelajaran" nama="mata_pelajaran_id" class="min-w-52 flex-1">
            <x-pilihan nama="mata_pelajaran_id" :opsi="$mapelList->pluck('nama', 'id')" :terpilih="$mataPelajaranId"/>
        </x-bidang>
        <x-tombol gaya="utama" ikon="funnel">Tampilkan</x-tombol>
    </form>

    @if (! $kelas)
        <div class="kartu p-12 text-center">
            <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-2xl bg-indigo-50 text-primary">
                <x-heroicon-o-academic-cap class="h-8 w-8"/>
            </div>
            <p class="mt-4 font-extrabold text-slate-800">Belum ada kelas yang Anda ampu</p>
            <p class="mt-1 text-sm text-slate-500">Hubungi admin untuk pengaturan jadwal mengajar Anda.</p>
        </div>
    @elseif ($penilaians->isEmpty())
        <div class="kartu p-12 text-center">
            <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-2xl bg-indigo-50 text-primary">
                <x-heroicon-o-clipboard-document-check class="h-8 w-8"/>
            </div>
            <p class="mt-4 font-extrabold text-slate-800">Belum ada penilaian untuk kelas ini</p>
            <p class="mt-1 text-sm text-slate-500">Buat penilaian (formatif / sumatif) lebih dahulu, baru isi nilainya di grid interaktif.</p>
            <x-tombol gaya="aksen" ikon="plus" class="mt-5"
                      :href="route('penilaian.create', ['kelas_id' => $kelas->id, 'mata_pelajaran_id' => $mataPelajaranId])">
                Buat Penilaian Pertama
            </x-tombol>
        </div>
    @else
        @livewire('tabel-nilai', ['kelasId' => $kelas->id, 'mataPelajaranId' => $mataPelajaranId], key($kelas->id.'-'.$mataPelajaranId))

        {{-- Impor nilai per penilaian (Collapsible agar rapi) --}}
        <div x-data="{ bukaImpor: false }" class="kartu tanpa-cetak mt-6 p-5">
            <button type="button" @click="bukaImpor = !bukaImpor"
                    class="flex w-full cursor-pointer items-center justify-between text-left">
                <div class="flex items-center gap-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-indigo-50 text-primary">
                        <x-heroicon-o-arrow-up-tray class="h-5 w-5"/>
                    </div>
                    <div>
                        <h3 class="font-extrabold text-slate-800">Impor Nilai dari Excel / CSV</h3>
                        <p class="text-xs text-slate-500">Unduh template per penilaian, isi nilai di Excel, lalu unggah kembali.</p>
                    </div>
                </div>
                <span class="inline-flex items-center gap-1 rounded-xl border border-slate-200 bg-slate-50 px-3 py-1.5 text-xs font-bold text-slate-600">
                    <span x-text="bukaImpor ? 'Tutup Panel' : 'Buka Panel Impor'"></span>
                </span>
            </button>

            <div x-show="bukaImpor" x-cloak x-transition class="mt-4 pt-4 border-t border-slate-100 space-y-2.5">
                @foreach ($penilaians as $penilaian)
                    <form method="POST" action="{{ route('nilai.impor', $penilaian) }}"
                          enctype="multipart/form-data"
                          class="flex flex-wrap items-center gap-3 rounded-xl border border-slate-200/80 bg-slate-50/50 p-3.5">
                        @csrf
                        <div class="min-w-0 flex-1">
                            <p class="truncate text-sm font-bold text-slate-800">{{ $penilaian->nama }}</p>
                            <p class="text-xs font-medium text-slate-500">{{ $penilaian->jenis->labelPendek() }}</p>
                        </div>
                        <a href="{{ route('nilai.template', ['penilaian_id' => $penilaian->id]) }}"
                           class="inline-flex items-center gap-1 rounded-lg bg-white px-2.5 py-1.5 text-xs font-bold text-accent-600 ring-1 ring-slate-200 hover:bg-teal-50 transition">
                            <x-heroicon-o-arrow-down-tray class="h-3.5 w-3.5"/>
                            Unduh Template
                        </a>
                        <input type="file" name="berkas" accept=".csv,.xlsx,.xls" required
                               class="text-xs file:mr-2 file:rounded-xl file:border-0 file:bg-indigo-50 file:px-3 file:py-1.5 file:text-xs file:font-bold file:text-primary hover:file:bg-indigo-100">
                        <x-tombol gaya="utama" ikon="arrow-up-tray">Impor</x-tombol>
                    </form>
                @endforeach
            </div>
        </div>
    @endif

    @if (session('galatImpor'))
        <div class="mt-4 rounded-2xl border border-amber-200 bg-amber-50 p-4 text-sm text-amber-900">
            <p class="font-bold">Catatan impor:</p>
            <ul class="mt-1 list-inside list-disc space-y-0.5 text-xs">
                @foreach (session('galatImpor') as $pesan)
                    <li>{{ $pesan }}</li>
                @endforeach
            </ul>
        </div>
    @endif
@endsection
