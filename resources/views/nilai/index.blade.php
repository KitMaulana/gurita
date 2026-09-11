@extends('layouts.app')
@section('judul', 'Daftar Nilai')

@section('konten')
    <x-kepala-halaman judul="Daftar Nilai"
                      :keterangan="$kelas?->nama ? $kelas->nama.' — '.$mapelList->firstWhere('id', $mataPelajaranId)?->nama : 'Pilih kelas dan mata pelajaran'">
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

    <form method="GET" class="kartu tanpa-cetak mb-6 flex flex-wrap items-end gap-3 p-4">
        <x-bidang label="Kelas" nama="kelas_id" class="min-w-52 flex-1">
            <x-pilihan nama="kelas_id" :opsi="$kelasList->pluck('nama', 'id')" :terpilih="$kelas?->id"/>
        </x-bidang>
        <x-bidang label="Mata Pelajaran" nama="mata_pelajaran_id" class="min-w-52 flex-1">
            <x-pilihan nama="mata_pelajaran_id" :opsi="$mapelList->pluck('nama', 'id')" :terpilih="$mataPelajaranId"/>
        </x-bidang>
        <x-tombol gaya="utama" ikon="funnel">Tampilkan</x-tombol>
    </form>

    @if (! $kelas)
        <div class="kartu p-10 text-center">
            <x-heroicon-o-academic-cap class="mx-auto h-12 w-12 text-slate-300"/>
            <p class="mt-3 font-semibold text-slate-700">Belum ada kelas yang Anda ampu</p>
            <p class="text-sm text-slate-500">Hubungi admin untuk pengaturan jadwal mengajar Anda.</p>
        </div>
    @elseif ($penilaians->isEmpty())
        <div class="kartu p-10 text-center">
            <x-heroicon-o-clipboard-document-check class="mx-auto h-12 w-12 text-slate-300"/>
            <p class="mt-3 font-semibold text-slate-700">Belum ada penilaian untuk kelas ini</p>
            <p class="text-sm text-slate-500">Buat penilaian (formatif / sumatif) lebih dahulu, baru isi nilainya.</p>
            <x-tombol gaya="aksen" ikon="plus" class="mt-4"
                      :href="route('penilaian.create', ['kelas_id' => $kelas->id, 'mata_pelajaran_id' => $mataPelajaranId])">
                Buat Penilaian
            </x-tombol>
        </div>
    @else
        @livewire('tabel-nilai', ['kelasId' => $kelas->id, 'mataPelajaranId' => $mataPelajaranId], key($kelas->id.'-'.$mataPelajaranId))

        {{-- Impor nilai per penilaian --}}
        <div class="kartu tanpa-cetak mt-6 p-5">
            <h3 class="mb-3 font-bold text-primary">Impor Nilai dari Excel/CSV</h3>
            <div class="space-y-2">
                @foreach ($penilaians as $penilaian)
                    <form method="POST" action="{{ route('nilai.impor', $penilaian) }}"
                          enctype="multipart/form-data"
                          class="flex flex-wrap items-center gap-2 rounded-lg border border-slate-200 p-3">
                        @csrf
                        <div class="min-w-0 flex-1">
                            <p class="truncate text-sm font-semibold text-slate-800">{{ $penilaian->nama }}</p>
                            <p class="text-xs text-slate-500">{{ $penilaian->jenis->labelPendek() }}</p>
                        </div>
                        <a href="{{ route('nilai.template', ['penilaian_id' => $penilaian->id]) }}"
                           class="text-sm font-semibold text-accent-600 hover:underline">Unduh Template</a>
                        <input type="file" name="berkas" accept=".csv,.xlsx,.xls" required
                               class="text-sm file:mr-2 file:rounded-lg file:border-0 file:bg-slate-100 file:px-3 file:py-1.5 file:text-sm file:font-semibold">
                        <x-tombol gaya="garis" ikon="arrow-up-tray">Impor</x-tombol>
                    </form>
                @endforeach
            </div>
        </div>
    @endif

    @if (session('galatImpor'))
        <div class="mt-4 rounded-xl border border-amber-200 bg-amber-50 p-4 text-sm text-amber-900">
            <p class="font-semibold">Catatan impor:</p>
            <ul class="mt-1 list-inside list-disc space-y-0.5">
                @foreach (session('galatImpor') as $pesan)
                    <li>{{ $pesan }}</li>
                @endforeach
            </ul>
        </div>
    @endif
@endsection
