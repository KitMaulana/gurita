@extends('layouts.app')
@section('judul', 'Agenda Mengajar')

@php
    use App\Support\Tanggal;
    use App\Support\WarnaKelas;
@endphp

@section('konten')
    <x-kepala-halaman judul="Agenda Mengajar"
                      keterangan="Catatan pertemuan nyata yang diturunkan otomatis dari jadwal mengajar Anda.">
        <x-slot:aksi>
            <x-tombol gaya="garis" ikon="arrow-down-tray" :href="route('agenda.ekspor', request()->query())">Excel</x-tombol>
            <x-tombol gaya="garis" ikon="printer" :href="route('agenda.cetak', request()->query())">Cetak</x-tombol>
            <x-tombol gaya="aksen" ikon="plus" :href="route('agenda.dari-jadwal')">Isi Agenda dari Jadwal</x-tombol>
        </x-slot:aksi>
    </x-kepala-halaman>

    <form method="GET" class="kartu tanpa-cetak mb-6 grid gap-3.5 p-4 sm:p-5 sm:grid-cols-2 lg:grid-cols-5">
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
                <th class="px-4 py-3.5">Tanggal</th>
                <th class="px-4 py-3.5">Kelas & Mapel</th>
                <th class="px-4 py-3.5 text-center">Pertemuan</th>
                <th class="px-4 py-3.5">Judul Materi & Metode</th>
                <th class="px-4 py-3.5">Status</th>
                <th class="px-4 py-3.5">Presensi</th>
                <th class="px-4 py-3.5 text-right">Aksi Cepat</th>
            </tr>
        </x-slot:kepala>

        @forelse ($daftar as $agenda)
            @php
                $namaKelas = $agenda->jadwal?->kelas_tampilan ?? '—';
                $warnaK = WarnaKelas::untuk($namaKelas);
            @endphp
            <tr class="hover:bg-indigo-50/30 transition">
                <td class="whitespace-nowrap px-4 py-3.5">
                    <p class="font-bold text-slate-800">{{ Tanggal::angka($agenda->tanggal) }}</p>
                    <p class="text-xs font-medium text-slate-400">{{ Tanggal::namaHari($agenda->tanggal) }}</p>
                </td>
                <td class="px-4 py-3.5">
                    <div class="flex items-center gap-2">
                        <span class="inline-flex items-center gap-1 rounded-lg px-2 py-0.5 text-xs font-extrabold"
                              style="background-color: {{ $warnaK['badge_bg'] }}; color: {{ $warnaK['badge_text'] }}; border: 1px solid {{ $warnaK['border'] }};">
                            <span class="h-1.5 w-1.5 rounded-full" style="background-color: {{ $warnaK['accent'] }};"></span>
                            {{ $namaKelas }}
                        </span>
                        <span class="rounded-md bg-slate-100 px-2 py-0.5 text-[11px] font-semibold text-slate-600">
                            {{ $agenda->label_jp }}
                        </span>
                    </div>
                    <p class="mt-1 text-xs font-medium text-slate-500">{{ $agenda->jadwal?->nama_tampilan ?? '—' }}</p>
                </td>
                <td class="px-4 py-3.5 text-center">
                    <span class="inline-flex items-center justify-center rounded-xl bg-slate-100 px-2.5 py-1 text-xs font-extrabold text-slate-700">
                        Ke-{{ $agenda->pertemuan_ke }}
                    </span>
                </td>
                <td class="px-4 py-3.5">
                    <p class="font-bold text-slate-800">{{ $agenda->judul_materi }}</p>
                    <div class="mt-0.5 flex flex-wrap items-center gap-2 text-xs text-slate-500">
                        @if ($agenda->bab)
                            <span class="rounded bg-indigo-50 px-1.5 py-0.5 text-[10px] font-bold text-primary">{{ $agenda->bab->kode }}</span>
                        @endif
                        @if ($agenda->metode)
                            <span>· {{ $agenda->metode }}</span>
                        @endif
                    </div>
                </td>
                <td class="px-4 py-3.5"><x-badge-status :status="$agenda->status"/></td>
                <td class="px-4 py-3.5">
                    @if ($agenda->presensis_count > 0)
                        <a href="{{ route('presensi.isi', $agenda) }}"
                           class="inline-flex items-center gap-1 rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-bold text-emerald-700 ring-1 ring-emerald-200/70 hover:bg-emerald-100 transition">
                            <x-heroicon-s-check-circle class="h-3.5 w-3.5 text-emerald-500"/>
                            {{ $agenda->presensis_count }} siswa
                        </a>
                    @else
                        <a href="{{ route('presensi.isi', $agenda) }}"
                           class="inline-flex items-center gap-1 rounded-full bg-amber-50 px-2.5 py-1 text-xs font-bold text-amber-800 ring-1 ring-amber-200/80 hover:bg-amber-100 transition">
                            <span class="h-1.5 w-1.5 rounded-full bg-amber-500"></span>
                            Isi Presensi →
                        </a>
                    @endif
                </td>
                <td class="px-4 py-3.5">
                    <div class="flex justify-end items-center gap-1.5">
                        <a href="{{ route('presensi.isi', $agenda) }}" title="Presensi Siswa"
                           class="inline-flex items-center gap-1 rounded-xl border border-slate-200 bg-white px-2.5 py-1.5 text-xs font-bold text-slate-700 hover:border-primary-200 hover:bg-primary-50 hover:text-primary transition">
                            <x-heroicon-o-user-group class="h-4 w-4"/>
                            <span class="hidden xl:inline">Presensi</span>
                        </a>
                        @can('update', $agenda)
                            <a href="{{ route('agenda.edit', $agenda) }}" title="Ubah Agenda"
                               class="inline-flex items-center justify-center rounded-xl border border-slate-200 bg-white p-1.5 text-slate-500 hover:border-primary-200 hover:bg-primary-50 hover:text-primary transition">
                                <x-heroicon-o-pencil-square class="h-4 w-4"/>
                            </a>
                            <form method="POST" action="{{ route('agenda.destroy', $agenda) }}"
                                  onsubmit="return confirm('Hapus agenda ini beserta presensinya? Tindakan ini tidak dapat dibatalkan.')">
                                @csrf @method('DELETE')
                                <button type="submit" title="Hapus"
                                        class="inline-flex cursor-pointer items-center justify-center rounded-xl border border-slate-200 bg-white p-1.5 text-slate-400 hover:border-rose-200 hover:bg-rose-50 hover:text-rose-600 transition">
                                    <x-heroicon-o-trash class="h-4 w-4"/>
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
                            judul="Belum ada agenda mengajar"
                            pesan="Mulai dengan membuat agenda dari jadwal mengajar Anda hari ini."
                            :aksi-url="route('agenda.dari-jadwal')"
                            aksi-label="Isi Agenda dari Jadwal"/>
        @endforelse
    </x-tabel>

    <div class="mt-4">{{ $daftar->links() }}</div>
@endsection
