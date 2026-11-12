@extends('layouts.app')
@section('judul', 'Rekap Rapor')

@php
    $totalSiswaRapor = $baris->count();
    $rataRapor = $totalSiswaRapor > 0 ? $baris->avg(fn ($b) => $b['nilai']['nilai_akhir']) : 0;
    $tuntasRapor = $baris->filter(fn ($b) => $b['nilai']['is_tuntas'])->count();
@endphp

@section('konten')
    <x-kepala-halaman judul="Rekap Nilai Rapor"
                      :keterangan="$kelas ? 'Kelas '.$kelas->nama.' — '.$mataPelajaran?->nama.' · KKTP '.$kktp : 'Pilih kelas dan mata pelajaran'">
        <x-slot:aksi>
            @if ($kelas)
                <x-tombol gaya="garis" ikon="arrow-down-tray" :href="route('rapor.ekspor', request()->query())">Excel</x-tombol>
                <x-tombol gaya="garis" ikon="printer" :href="route('rapor.cetak', request()->query())">Leger (F4)</x-tombol>
                <form method="POST" action="{{ route('rapor.bekukan', request()->query()) }}"
                      onsubmit="return confirm('Bekukan rapor? Penilaian dan agenda semester ini akan otomatis terkunci.')">
                    @csrf
                    <x-tombol gaya="aksen" ikon="lock-closed">{{ $sudahDibekukan ? 'Perbarui Pembekuan' : 'Bekukan Rapor' }}</x-tombol>
                </form>
            @endif
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

    @if ($totalSiswaRapor > 0)
        <div class="mb-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <x-kartu-statistik label="Total Siswa" :nilai="$totalSiswaRapor" :keterangan="'Kelas '.($kelas?->nama ?? '')" ikon="users" warna="utama"/>
            <x-kartu-statistik label="Rata-rata Rapor" :nilai="number_format($rataRapor, 1, ',', '.')" :keterangan="'KKTP: '.$kktp" ikon="academic-cap" warna="aksen"/>
            <x-kartu-statistik label="Ketuntasan Kelas" :nilai="$tuntasRapor.' / '.$totalSiswaRapor" keterangan="Siswa mencapai KKTP" ikon="check-badge" warna="aksen"/>
            <x-kartu-statistik label="Status Rapor" :nilai="$sudahDibekukan ? 'Dibekukan' : 'Draf Aktif'" :keterangan="$sudahDibekukan ? 'Siap cetak & sunting deskripsi' : 'Belum dibekukan'" ikon="lock-closed" :warna="$sudahDibekukan ? 'utama' : 'peringatan'"/>
        </div>
    @endif

    @if ($sudahDibekukan)
        <div class="mb-5 flex items-center gap-3 rounded-2xl border border-emerald-200 bg-emerald-50/80 px-4 py-3.5 text-sm text-emerald-900">
            <x-heroicon-s-check-badge class="h-6 w-6 text-emerald-600 shrink-0"/>
            <span>Rapor kelas ini sudah dibekukan. Membekukan ulang akan memperbarui nilai, tetapi deskripsi capaian yang sudah Anda sunting tetap dipertahankan.</span>
        </div>
    @endif

    <div x-data="{ cari: '' }">
        {{-- Keterangan Singkatan Kolom Penilaian & Pencarian --}}
        <div class="mb-3 flex flex-wrap items-center justify-between gap-3 text-xs text-slate-500">
            <div class="flex flex-wrap items-center gap-2.5">
                <span class="font-bold text-slate-600">Komponen:</span>
                <span class="inline-flex items-center gap-1"><strong class="rounded-md bg-sky-100 px-1.5 py-0.5 font-extrabold text-sky-800">F</strong> Formatif</span>
                <span class="inline-flex items-center gap-1"><strong class="rounded-md bg-emerald-100 px-1.5 py-0.5 font-extrabold text-emerald-800">SL</strong> Sumatif Lingkup Materi</span>
                <span class="inline-flex items-center gap-1"><strong class="rounded-md bg-primary-100 px-1.5 py-0.5 font-extrabold text-primary-800">SA</strong> Sumatif Akhir Semester</span>
            </div>

            @if ($totalSiswaRapor > 0)
                <div class="relative w-full sm:w-64">
                    <x-heroicon-o-magnifying-glass class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-slate-400"/>
                    <input type="text" x-model="cari" placeholder="Cari nama siswa..."
                           class="w-full rounded-xl border-slate-200 bg-white pl-9 pr-4 py-1.5 text-xs font-medium text-slate-800 shadow-2xs focus:border-primary focus:ring-primary">
                </div>
            @endif
        </div>

        <x-tabel>
            <x-slot:kepala>
                <tr>
                    <th class="px-3 py-3.5">No</th>
                    <th class="px-3 py-3.5">Nama Siswa</th>
                    <th class="px-2 py-3.5 text-center cursor-help" title="Formatif: rata-rata nilai proses / tugas harian">F</th>
                    <th class="px-2 py-3.5 text-center cursor-help" title="Sumatif Lingkup Materi: rata-rata ulangan per bab">SL</th>
                    <th class="px-2 py-3.5 text-center cursor-help" title="Sumatif Akhir Semester: nilai ujian akhir">SA</th>
                    <th class="px-3 py-3.5 text-center">Nilai Akhir</th>
                    <th class="px-2 py-3.5 text-center">Pred.</th>
                    <th class="px-3 py-3.5 text-center">Status</th>
                    <th class="px-3 py-3.5 text-center">Absensi (H/S/I/A)</th>
                    <th class="px-4 py-3.5">Deskripsi Capaian</th>
                    <th class="px-3 py-3.5 text-right">Aksi</th>
                </tr>
            </x-slot:kepala>

            @forelse ($baris as $b)
                @php $n = $b['nilai']; $p = $b['presensi']; @endphp
                <tr x-show="!cari || {{ Js::from(strtolower($b['siswa']->nama.' '.($b['siswa']->nisn ?? ''))) }}.includes(cari.toLowerCase())"
                    class="hover:bg-indigo-50/30 transition align-top">
                    <td class="px-3 py-3.5 font-bold text-slate-400">{{ $b['siswa']->pivot->no_absen }}</td>
                    <td class="px-3 py-3.5">
                        <p class="whitespace-nowrap font-bold text-slate-800">{{ $b['siswa']->nama }}</p>
                        <p class="text-[11px] text-slate-400">{{ $b['siswa']->nisn }}</p>
                    </td>
                    <td class="px-2 py-3.5 text-center font-medium text-slate-600 tabular-nums">{{ $n['formatif'] !== null ? number_format($n['formatif'], 1, ',', '.') : '—' }}</td>
                    <td class="px-2 py-3.5 text-center font-medium text-slate-600 tabular-nums">{{ $n['sumatif_lingkup'] !== null ? number_format($n['sumatif_lingkup'], 1, ',', '.') : '—' }}</td>
                    <td class="px-2 py-3.5 text-center font-medium text-slate-600 tabular-nums">{{ $n['sumatif_akhir'] !== null ? number_format($n['sumatif_akhir'], 1, ',', '.') : '—' }}</td>
                    <td class="px-3 py-3.5 text-center">
                        <span class="font-extrabold text-primary tabular-nums text-base">{{ round($n['nilai_akhir']) }}</span>
                        @if ($n['is_sementara'])
                            <span class="block mt-0.5 rounded bg-amber-100 px-1 py-0.5 text-[9px] font-bold text-amber-800">sementara</span>
                        @endif
                    </td>
                    <td class="px-2 py-3.5 text-center"><x-badge-predikat :predikat="$n['predikat']"/></td>
                    <td class="px-3 py-3.5 text-center">
                        <span @class([
                            'rounded-full px-2.5 py-0.5 text-[11px] font-extrabold',
                            'bg-emerald-100 text-emerald-800' => $n['is_tuntas'],
                            'bg-rose-100 text-rose-800' => ! $n['is_tuntas'],
                        ])>{{ $n['is_tuntas'] ? 'Tuntas' : 'Belum' }}</span>
                    </td>
                    <td class="whitespace-nowrap px-3 py-3.5 text-center text-xs font-semibold text-slate-600 tabular-nums">
                        {{ $p['hadir'] + $p['dispensasi'] }} / {{ $p['sakit'] }} / {{ $p['izin'] }} / {{ $p['alfa'] + $p['bolos'] }}
                    </td>
                    <td class="px-4 py-3.5">
                        <p class="max-w-md text-xs leading-relaxed text-slate-600">
                            {{ $b['rapor']?->deskripsi_capaian ?: $b['deskripsi'] }}
                        </p>
                    </td>
                    <td class="px-3 py-3.5">
                        <div class="flex justify-end items-center gap-1.5">
                            @if ($b['rapor'])
                                <button type="button" title="Sunting deskripsi"
                                        @click="$dispatch('buka-modal', 'deskripsi-{{ $b['rapor']->id }}')"
                                        class="inline-flex cursor-pointer items-center justify-center rounded-xl border border-slate-200 bg-white p-2 text-slate-500 hover:border-primary-200 hover:bg-primary-50 hover:text-primary transition">
                                    <x-heroicon-o-pencil-square class="h-4 w-4"/>
                                </button>
                            @endif
                            <a href="{{ route('rapor.kartu', array_merge(request()->query(), ['siswa' => $b['siswa']->id])) }}"
                               title="Unduh Kartu Nilai (PDF)"
                               class="inline-flex items-center gap-1 rounded-xl border border-slate-200 bg-white px-2.5 py-1.5 text-xs font-bold text-slate-700 hover:border-primary-200 hover:bg-primary-50 hover:text-primary transition">
                                <x-heroicon-o-identification class="h-4 w-4"/>
                                <span class="hidden xl:inline">Kartu</span>
                            </a>
                        </div>
                    </td>
                </tr>
            @empty
                <x-tabel.kosong :kolom="11" ikon="document-chart-bar"
                                judul="Belum ada data rapor"
                                pesan="Isi nilai dan presensi terlebih dahulu, rekap akan dihitung otomatis."/>
            @endforelse
        </x-tabel>
    </div>

    {{-- Modal sunting deskripsi per siswa --}}
    @foreach ($baris->filter(fn ($b) => $b['rapor']) as $b)
        <x-modal :nama="'deskripsi-'.$b['rapor']->id" :judul="'Deskripsi Capaian — '.$b['siswa']->nama">
            <form method="POST" action="{{ route('rapor.deskripsi', $b['rapor']) }}"
                  id="form-deskripsi-{{ $b['rapor']->id }}">
                @csrf @method('PUT')
                <textarea name="deskripsi_capaian" rows="5"
                          class="block w-full rounded-xl border-slate-200 text-sm shadow-xs focus:border-primary focus:ring-primary">{{ $b['rapor']->deskripsi_capaian ?: $b['deskripsi'] }}</textarea>
                <p class="mt-2.5 text-xs text-slate-500">
                    💡 Deskripsi otomatis disusun dari Tujuan Pembelajaran dengan nilai tertinggi dan terendah. Anda bebas menyuntingnya sebelum rapor dicetak.
                </p>
            </form>

            <x-slot:kaki>
                <x-tombol tipe="button" gaya="halus" @click="$dispatch('tutup-modal', 'deskripsi-{{ $b['rapor']->id }}')">Batal</x-tombol>
                <x-tombol gaya="aksen" ikon="check" form="form-deskripsi-{{ $b['rapor']->id }}">Simpan Deskripsi</x-tombol>
            </x-slot:kaki>
        </x-modal>
    @endforeach
@endsection
