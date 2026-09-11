@extends('layouts.app')
@section('judul', 'Penilaian')

@php use App\Support\Tanggal; @endphp

@section('konten')
    <x-kepala-halaman judul="Daftar Penilaian"
                      keterangan="Definisi asesmen: formatif, sumatif lingkup materi, dan sumatif akhir semester.">
        <x-slot:aksi>
            <x-tombol gaya="garis" ikon="table-cells" :href="route('nilai.index')">Grid Nilai</x-tombol>
            <x-tombol gaya="aksen" ikon="plus" :href="route('penilaian.create')">Buat Penilaian</x-tombol>
        </x-slot:aksi>
    </x-kepala-halaman>

    <form method="GET" class="kartu mb-6 flex flex-wrap items-end gap-3 p-4">
        <x-bidang label="Kelas" nama="kelas_id" class="min-w-52 flex-1">
            <x-pilihan nama="kelas_id" :opsi="$kelasList->pluck('nama', 'id')" :terpilih="request('kelas_id')" kosong="Semua kelas"/>
        </x-bidang>
        <x-bidang label="Jenis" nama="jenis" class="min-w-52 flex-1">
            <x-pilihan nama="jenis" :opsi="$jenisList" :terpilih="request('jenis')" kosong="Semua jenis"/>
        </x-bidang>
        <x-tombol gaya="utama" ikon="funnel">Tampilkan</x-tombol>
    </form>

    <x-tabel>
        <x-slot:kepala>
            <tr>
                <th class="px-4 py-3">Tanggal</th>
                <th class="px-4 py-3">Nama Penilaian</th>
                <th class="px-4 py-3">Kelas / Mapel</th>
                <th class="px-4 py-3">Jenis</th>
                <th class="px-4 py-3">Kelengkapan</th>
                <th class="px-4 py-3 text-right">Aksi</th>
            </tr>
        </x-slot:kepala>

        @forelse ($daftar as $penilaian)
            <tr class="hover:bg-slate-50">
                <td class="whitespace-nowrap px-4 py-3 text-slate-600">{{ Tanggal::angka($penilaian->tanggal) }}</td>
                <td class="px-4 py-3">
                    <p class="font-medium text-slate-800">{{ $penilaian->nama }}</p>
                    <p class="text-xs text-slate-500">
                        {{ $penilaian->bab?->kode }}
                        @if ($penilaian->is_remedial)
                            <span class="ml-1 rounded bg-amber-100 px-1.5 py-0.5 font-semibold text-amber-800">remedial</span>
                        @endif
                        @if ($penilaian->is_terkunci)
                            <span class="ml-1 rounded bg-slate-100 px-1.5 py-0.5 font-semibold text-slate-600">terkunci</span>
                        @endif
                    </p>
                </td>
                <td class="px-4 py-3">
                    <p class="text-slate-800">{{ $penilaian->kelas->nama }}</p>
                    <p class="text-xs text-slate-500">{{ $penilaian->mataPelajaran->nama }}</p>
                </td>
                <td class="px-4 py-3"><x-badge-status :status="$penilaian->jenis"/></td>
                <td class="px-4 py-3">
                    @if ($penilaian->belum_dinilai > 0)
                        <span class="text-xs font-semibold text-rose-600">{{ $penilaian->belum_dinilai }} belum dinilai</span>
                    @else
                        <span class="text-xs font-semibold text-emerald-700">Lengkap ({{ $penilaian->nilais_count }})</span>
                    @endif
                </td>
                <td class="px-4 py-3">
                    <div class="flex justify-end gap-1">
                        <a href="{{ route('nilai.index', ['kelas_id' => $penilaian->kelas_id, 'mata_pelajaran_id' => $penilaian->mata_pelajaran_id]) }}"
                           title="Isi nilai"
                           class="tombol-sentuh rounded-lg p-2 text-slate-500 hover:bg-slate-100 hover:text-primary">
                            <x-heroicon-o-table-cells class="h-5 w-5"/>
                        </a>
                        @can('update', $penilaian)
                            <a href="{{ route('penilaian.edit', $penilaian) }}" title="Ubah"
                               class="tombol-sentuh rounded-lg p-2 text-slate-500 hover:bg-slate-100 hover:text-primary">
                                <x-heroicon-o-pencil-square class="h-5 w-5"/>
                            </a>
                            <form method="POST" action="{{ route('penilaian.destroy', $penilaian) }}"
                                  onsubmit="return confirm('Hapus penilaian ini beserta seluruh nilainya?')">
                                @csrf @method('DELETE')
                                <button type="submit" title="Hapus"
                                        class="tombol-sentuh rounded-lg p-2 text-slate-500 hover:bg-rose-50 hover:text-rose-600">
                                    <x-heroicon-o-trash class="h-5 w-5"/>
                                </button>
                            </form>
                        @endcan
                        @can('bukaKunci', $penilaian)
                            @if ($penilaian->is_terkunci)
                                <form method="POST" action="{{ route('penilaian.buka-kunci', $penilaian) }}">
                                    @csrf
                                    <button type="submit" title="Buka kunci"
                                            class="tombol-sentuh rounded-lg p-2 text-slate-500 hover:bg-amber-50 hover:text-amber-600">
                                        <x-heroicon-o-lock-open class="h-5 w-5"/>
                                    </button>
                                </form>
                            @endif
                        @endcan
                    </div>
                </td>
            </tr>
        @empty
            <x-tabel.kosong :kolom="6" ikon="clipboard-document-check"
                            judul="Belum ada penilaian"
                            pesan="Buat penilaian terlebih dahulu agar nilai siswa dapat diisi."
                            :aksi-url="route('penilaian.create')"
                            aksi-label="Buat Penilaian"/>
        @endforelse
    </x-tabel>

    <div class="mt-4">{{ $daftar->links() }}</div>
@endsection
