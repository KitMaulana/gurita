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

    <form method="GET" class="kartu mb-6 flex flex-wrap items-end gap-3.5 p-4 sm:p-5">
        <x-bidang label="Kelas" nama="kelas_id" class="min-w-52 flex-1">
            <x-pilihan nama="kelas_id" :opsi="$kelasList->pluck('nama', 'id')" :terpilih="request('kelas_id')" kosong="Semua kelas"/>
        </x-bidang>
        <x-bidang label="Jenis Penilaian" nama="jenis" class="min-w-52 flex-1">
            <x-pilihan nama="jenis" :opsi="$jenisList" :terpilih="request('jenis')" kosong="Semua jenis"/>
        </x-bidang>
        <x-tombol gaya="utama" ikon="funnel">Tampilkan</x-tombol>
    </form>

    <x-tabel>
        <x-slot:kepala>
            <tr>
                <th class="px-4 py-3.5">Tanggal</th>
                <th class="px-4 py-3.5">Nama Penilaian</th>
                <th class="px-4 py-3.5">Kelas / Mapel</th>
                <th class="px-4 py-3.5">Jenis</th>
                <th class="px-4 py-3.5">Kelengkapan Nilai</th>
                <th class="px-4 py-3.5 text-right">Aksi Cepat</th>
            </tr>
        </x-slot:kepala>

        @forelse ($daftar as $penilaian)
            <tr class="hover:bg-indigo-50/30 transition">
                <td class="whitespace-nowrap px-4 py-3.5 font-semibold text-slate-600">{{ Tanggal::angka($penilaian->tanggal) }}</td>
                <td class="px-4 py-3.5">
                    <p class="font-bold text-slate-800">{{ $penilaian->nama }}</p>
                    <div class="mt-0.5 flex flex-wrap items-center gap-1.5 text-xs text-slate-500">
                        @if ($penilaian->bab)
                            <span class="rounded bg-indigo-50 px-1.5 py-0.5 text-[10px] font-bold text-primary">{{ $penilaian->bab->kode }}</span>
                        @endif
                        @if ($penilaian->is_remedial)
                            <span class="rounded bg-amber-100 px-1.5 py-0.5 text-[10px] font-bold text-amber-800">Remedial</span>
                        @endif
                        @if ($penilaian->is_terkunci)
                            <span class="rounded bg-slate-100 px-1.5 py-0.5 text-[10px] font-bold text-slate-600">🔒 Terkunci</span>
                        @endif
                    </div>
                </td>
                <td class="px-4 py-3.5">
                    <p class="font-bold text-slate-800">{{ $penilaian->kelas->nama }}</p>
                    <p class="text-xs text-slate-500">{{ $penilaian->mataPelajaran->nama }}</p>
                </td>
                <td class="px-4 py-3.5"><x-badge-status :status="$penilaian->jenis"/></td>
                <td class="px-4 py-3.5">
                    @if ($penilaian->belum_dinilai > 0)
                        <a href="{{ route('nilai.index', ['kelas_id' => $penilaian->kelas_id, 'mata_pelajaran_id' => $penilaian->mata_pelajaran_id]) }}"
                           class="inline-flex items-center gap-1 rounded-full bg-rose-50 px-2.5 py-1 text-xs font-bold text-rose-600 ring-1 ring-rose-200/80 hover:bg-rose-100 transition">
                            <span class="h-1.5 w-1.5 rounded-full bg-rose-500"></span>
                            {{ $penilaian->belum_dinilai }} belum dinilai
                        </a>
                    @else
                        <span class="inline-flex items-center gap-1 rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-bold text-emerald-700 ring-1 ring-emerald-200/80">
                            <x-heroicon-s-check-circle class="h-3.5 w-3.5 text-emerald-500"/>
                            Lengkap ({{ $penilaian->nilais_count }})
                        </span>
                    @endif
                </td>
                <td class="px-4 py-3.5">
                    <div class="flex justify-end items-center gap-1.5">
                        <a href="{{ route('nilai.index', ['kelas_id' => $penilaian->kelas_id, 'mata_pelajaran_id' => $penilaian->mata_pelajaran_id]) }}"
                           title="Isi nilai di Grid"
                           class="inline-flex items-center gap-1 rounded-xl border border-slate-200 bg-white px-2.5 py-1.5 text-xs font-bold text-slate-700 hover:border-primary-200 hover:bg-primary-50 hover:text-primary transition">
                            <x-heroicon-o-table-cells class="h-4 w-4"/>
                            <span class="hidden xl:inline">Isi Nilai</span>
                        </a>
                        @can('update', $penilaian)
                            <a href="{{ route('penilaian.edit', $penilaian) }}" title="Ubah"
                               class="inline-flex items-center justify-center rounded-xl border border-slate-200 bg-white p-1.5 text-slate-500 hover:border-primary-200 hover:bg-primary-50 hover:text-primary transition">
                                <x-heroicon-o-pencil-square class="h-4 w-4"/>
                            </a>
                            <form method="POST" action="{{ route('penilaian.destroy', $penilaian) }}"
                                  onsubmit="return confirm('Hapus penilaian ini beserta seluruh nilainya?')">
                                @csrf @method('DELETE')
                                <button type="submit" title="Hapus"
                                        class="inline-flex cursor-pointer items-center justify-center rounded-xl border border-slate-200 bg-white p-1.5 text-slate-400 hover:border-rose-200 hover:bg-rose-50 hover:text-rose-600 transition">
                                    <x-heroicon-o-trash class="h-4 w-4"/>
                                </button>
                            </form>
                        @endcan
                        @can('bukaKunci', $penilaian)
                            @if ($penilaian->is_terkunci)
                                <form method="POST" action="{{ route('penilaian.buka-kunci', $penilaian) }}">
                                    @csrf
                                    <button type="submit" title="Buka kunci"
                                            class="inline-flex cursor-pointer items-center justify-center rounded-xl border border-amber-200 bg-amber-50 p-1.5 text-amber-600 hover:bg-amber-100 transition">
                                        <x-heroicon-o-lock-open class="h-4 w-4"/>
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
