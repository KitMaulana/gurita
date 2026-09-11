@extends('layouts.app')
@section('judul', 'Rekap Rapor')

@section('konten')
    <x-kepala-halaman judul="Rekap Rapor"
                      :keterangan="$kelas ? $kelas->nama.' — '.$mataPelajaran?->nama.' · KKTP '.$kktp : 'Pilih kelas dan mata pelajaran'">
        <x-slot:aksi>
            @if ($kelas)
                <x-tombol gaya="garis" ikon="arrow-down-tray" :href="route('rapor.ekspor', request()->query())">Excel</x-tombol>
                <x-tombol gaya="garis" ikon="printer" :href="route('rapor.cetak', request()->query())">Leger (F4)</x-tombol>
                <form method="POST" action="{{ route('rapor.bekukan', request()->query()) }}"
                      onsubmit="return confirm('Bekukan rapor? Penilaian dan agenda semester ini akan otomatis terkunci.')">
                    @csrf
                    <x-tombol gaya="aksen" ikon="lock-closed">Bekukan Rapor</x-tombol>
                </form>
            @endif
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

    @if ($sudahDibekukan)
        <div class="mb-4 flex items-center gap-2 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
            <x-heroicon-o-check-badge class="h-5 w-5"/>
            Rapor kelas ini sudah dibekukan. Membekukan ulang akan memperbarui nilai, tetapi deskripsi yang sudah Anda sunting tetap dipertahankan.
        </div>
    @endif

    <x-tabel>
        <x-slot:kepala>
            <tr>
                <th class="px-3 py-3">No</th>
                <th class="px-3 py-3">Nama Siswa</th>
                <th class="px-2 py-3 text-center">F</th>
                <th class="px-2 py-3 text-center">SL</th>
                <th class="px-2 py-3 text-center">SA</th>
                <th class="px-3 py-3 text-center">Nilai Akhir</th>
                <th class="px-2 py-3 text-center">Pred.</th>
                <th class="px-3 py-3 text-center">Status</th>
                <th class="px-3 py-3 text-center">Absensi (H/S/I/A)</th>
                <th class="px-4 py-3">Deskripsi Capaian</th>
                <th class="px-3 py-3 text-right">Aksi</th>
            </tr>
        </x-slot:kepala>

        @forelse ($baris as $b)
            @php $n = $b['nilai']; $p = $b['presensi']; @endphp
            <tr class="hover:bg-slate-50 align-top">
                <td class="px-3 py-3 text-slate-500">{{ $b['siswa']->pivot->no_absen }}</td>
                <td class="px-3 py-3">
                    <p class="whitespace-nowrap font-medium text-slate-800">{{ $b['siswa']->nama }}</p>
                    <p class="text-[11px] text-slate-400">{{ $b['siswa']->nisn }}</p>
                </td>
                <td class="px-2 py-3 text-center text-slate-600">{{ $n['formatif'] !== null ? number_format($n['formatif'], 1, ',', '.') : '—' }}</td>
                <td class="px-2 py-3 text-center text-slate-600">{{ $n['sumatif_lingkup'] !== null ? number_format($n['sumatif_lingkup'], 1, ',', '.') : '—' }}</td>
                <td class="px-2 py-3 text-center text-slate-600">{{ $n['sumatif_akhir'] !== null ? number_format($n['sumatif_akhir'], 1, ',', '.') : '—' }}</td>
                <td class="px-3 py-3 text-center">
                    <span class="font-bold text-primary">{{ round($n['nilai_akhir']) }}</span>
                    @if ($n['is_sementara'])
                        <span class="ml-1 rounded bg-amber-100 px-1 py-0.5 text-[9px] font-semibold text-amber-800">sementara</span>
                    @endif
                </td>
                <td class="px-2 py-3 text-center"><x-badge-predikat :predikat="$n['predikat']"/></td>
                <td class="px-3 py-3 text-center">
                    <span @class([
                        'rounded-full px-2 py-0.5 text-[11px] font-semibold',
                        'bg-emerald-100 text-emerald-800' => $n['is_tuntas'],
                        'bg-rose-100 text-rose-800' => ! $n['is_tuntas'],
                    ])>{{ $n['is_tuntas'] ? 'Tuntas' : 'Belum' }}</span>
                </td>
                <td class="whitespace-nowrap px-3 py-3 text-center text-xs text-slate-600">
                    {{ $p['hadir'] + $p['dispensasi'] }} / {{ $p['sakit'] }} / {{ $p['izin'] }} / {{ $p['alfa'] + $p['bolos'] }}
                </td>
                <td class="px-4 py-3">
                    <p class="max-w-md text-xs leading-relaxed text-slate-600">
                        {{ $b['rapor']?->deskripsi_capaian ?: $b['deskripsi'] }}
                    </p>
                </td>
                <td class="px-3 py-3">
                    <div class="flex justify-end gap-1">
                        @if ($b['rapor'])
                            <button type="button" title="Sunting deskripsi"
                                    @click="$dispatch('buka-modal', 'deskripsi-{{ $b['rapor']->id }}')"
                                    class="tombol-sentuh rounded-lg p-2 text-slate-500 hover:bg-slate-100 hover:text-primary">
                                <x-heroicon-o-pencil-square class="h-5 w-5"/>
                            </button>
                        @endif
                        <a href="{{ route('rapor.kartu', array_merge(request()->query(), ['siswa' => $b['siswa']->id])) }}"
                           title="Kartu nilai (PDF)"
                           class="tombol-sentuh rounded-lg p-2 text-slate-500 hover:bg-slate-100 hover:text-primary">
                            <x-heroicon-o-identification class="h-5 w-5"/>
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

    {{-- Modal sunting deskripsi per siswa --}}
    @foreach ($baris->filter(fn ($b) => $b['rapor']) as $b)
        <x-modal :nama="'deskripsi-'.$b['rapor']->id" :judul="'Deskripsi Capaian — '.$b['siswa']->nama">
            <form method="POST" action="{{ route('rapor.deskripsi', $b['rapor']) }}"
                  id="form-deskripsi-{{ $b['rapor']->id }}">
                @csrf @method('PUT')
                <textarea name="deskripsi_capaian" rows="5"
                          class="block w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-primary focus:ring-primary">{{ $b['rapor']->deskripsi_capaian ?: $b['deskripsi'] }}</textarea>
                <p class="mt-2 text-xs text-slate-500">
                    Deskripsi otomatis disusun dari Tujuan Pembelajaran dengan nilai tertinggi dan terendah.
                    Anda bebas menyuntingnya sebelum rapor dicetak.
                </p>
            </form>

            <x-slot:kaki>
                <x-tombol tipe="button" gaya="halus" @click="$dispatch('tutup-modal', 'deskripsi-{{ $b['rapor']->id }}')">Batal</x-tombol>
                <x-tombol gaya="aksen" ikon="check" form="form-deskripsi-{{ $b['rapor']->id }}">Simpan</x-tombol>
            </x-slot:kaki>
        </x-modal>
    @endforeach
@endsection
