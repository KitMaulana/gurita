@extends('layouts.app')
@section('judul', 'Agenda Mengajar')

@php use App\Support\Tanggal; @endphp

@section('konten')
    <x-kepala-halaman judul="Agenda Mengajar"
                      keterangan="Catatan pertemuan nyata, diturunkan dari jadwal mengajar Anda.">
        <x-slot:aksi>
            <x-tombol gaya="garis" ikon="arrow-down-tray" :href="route('agenda.ekspor', request()->query())">Excel</x-tombol>
            <x-tombol gaya="garis" ikon="printer" :href="route('agenda.cetak', request()->query())">Cetak</x-tombol>
            <x-tombol gaya="aksen" ikon="plus" :href="route('agenda.dari-jadwal')">Buat Agenda dari Jadwal</x-tombol>
        </x-slot:aksi>
    </x-kepala-halaman>

    <form method="GET" class="kartu tanpa-cetak mb-6 grid gap-3 p-4 sm:grid-cols-2 lg:grid-cols-5">
        <x-bidang label="Dari Tanggal" nama="dari">
            <x-isian nama="dari" tipe="date" :value="request('dari')"/>
        </x-bidang>
        <x-bidang label="Sampai Tanggal" nama="sampai">
            <x-isian nama="sampai" tipe="date" :value="request('sampai')"/>
        </x-bidang>
        <x-bidang label="Kelas" nama="kelas_id">
            <x-pilihan nama="kelas_id" :opsi="$kelasList->pluck('nama', 'id')" :terpilih="request('kelas_id')" kosong="Semua kelas"/>
        </x-bidang>
        <x-bidang label="Kata Kunci Materi" nama="cari">
            <x-isian nama="cari" :value="request('cari')" placeholder="mis. teks editorial"/>
        </x-bidang>
        <div class="flex items-end gap-2">
            <x-tombol gaya="utama" ikon="magnifying-glass">Cari</x-tombol>
            <x-tombol gaya="halus" :href="route('agenda.index')">Reset</x-tombol>
        </div>
    </form>

    <x-tabel>
        <x-slot:kepala>
            <tr>
                <th class="px-4 py-3">Tanggal</th>
                <th class="px-4 py-3">Kelas / Mapel</th>
                <th class="px-4 py-3">Pert.</th>
                <th class="px-4 py-3">Judul Materi</th>
                <th class="px-4 py-3">Status</th>
                <th class="px-4 py-3">Presensi</th>
                <th class="px-4 py-3 text-right">Aksi</th>
            </tr>
        </x-slot:kepala>

        @forelse ($daftar as $agenda)
            <tr class="hover:bg-slate-50">
                <td class="whitespace-nowrap px-4 py-3">
                    <p class="font-semibold text-slate-800">{{ Tanggal::angka($agenda->tanggal) }}</p>
                    <p class="text-xs text-slate-500">{{ Tanggal::namaHari($agenda->tanggal) }}</p>
                </td>
                <td class="px-4 py-3">
                    <p class="font-medium text-slate-800">{{ $agenda->jadwal?->kelas_tampilan ?? '—' }}</p>
                    <p class="text-xs text-slate-500">{{ $agenda->jadwal?->nama_tampilan ?? '—' }} · JP {{ $agenda->jadwal?->jam_ke ?? '—' }}</p>
                </td>
                <td class="px-4 py-3 text-slate-600">{{ $agenda->pertemuan_ke }}</td>
                <td class="px-4 py-3">
                    <p class="font-medium text-slate-800">{{ $agenda->judul_materi }}</p>
                    @if ($agenda->bab)
                        <p class="text-xs text-slate-500">{{ $agenda->bab->kode }}</p>
                    @endif
                </td>
                <td class="px-4 py-3"><x-badge-status :status="$agenda->status"/></td>
                <td class="px-4 py-3">
                    @if ($agenda->presensis_count > 0)
                        <span class="text-xs font-semibold text-emerald-700">{{ $agenda->presensis_count }} siswa</span>
                    @else
                        <span class="text-xs text-rose-600">Belum diisi</span>
                    @endif
                </td>
                <td class="px-4 py-3">
                    <div class="flex justify-end gap-1">
                        <a href="{{ route('presensi.isi', $agenda) }}" title="Presensi"
                           class="tombol-sentuh rounded-lg p-2 text-slate-500 hover:bg-slate-100 hover:text-primary">
                            <x-heroicon-o-user-group class="h-5 w-5"/>
                        </a>
                        @can('update', $agenda)
                            <a href="{{ route('agenda.edit', $agenda) }}" title="Ubah"
                               class="tombol-sentuh rounded-lg p-2 text-slate-500 hover:bg-slate-100 hover:text-primary">
                                <x-heroicon-o-pencil-square class="h-5 w-5"/>
                            </a>
                            <form method="POST" action="{{ route('agenda.destroy', $agenda) }}"
                                  onsubmit="return confirm('Hapus agenda ini beserta presensinya? Tindakan ini tidak dapat dibatalkan.')">
                                @csrf @method('DELETE')
                                <button type="submit" title="Hapus"
                                        class="tombol-sentuh rounded-lg p-2 text-slate-500 hover:bg-rose-50 hover:text-rose-600">
                                    <x-heroicon-o-trash class="h-5 w-5"/>
                                </button>
                            </form>
                        @else
                            <span class="inline-flex items-center px-2 text-xs text-slate-400" title="Terkunci setelah rapor dibekukan">
                                <x-heroicon-o-lock-closed class="h-4 w-4"/>
                            </span>
                        @endcan
                    </div>
                </td>
            </tr>
        @empty
            <x-tabel.kosong :kolom="7" ikon="clipboard-document-list"
                            judul="Belum ada agenda"
                            pesan="Mulai dengan membuat agenda dari jadwal mengajar Anda."
                            :aksi-url="route('agenda.dari-jadwal')"
                            aksi-label="Buat Agenda dari Jadwal"/>
        @endforelse
    </x-tabel>

    <div class="mt-4">{{ $daftar->links() }}</div>
@endsection
