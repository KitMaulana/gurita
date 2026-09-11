@extends('layouts.app')
@section('judul', 'Log Aktivitas')

@section('konten')
    <x-kepala-halaman judul="Log Aktivitas"
                      keterangan="Jejak perubahan nilai, presensi, agenda, jadwal, dan pembekuan rapor."/>

    <form method="GET" class="kartu mb-6 flex flex-wrap items-end gap-3 p-4">
        <x-bidang label="Jenis Log" nama="log" class="min-w-52 flex-1">
            <x-pilihan nama="log" :opsi="$namaLog->mapWithKeys(fn ($n) => [$n => ucfirst($n)])"
                       :terpilih="request('log')" kosong="Semua jenis"/>
        </x-bidang>
        <x-tombol gaya="utama" ikon="funnel">Tampilkan</x-tombol>
    </form>

    <x-tabel>
        <x-slot:kepala>
            <tr>
                <th class="px-4 py-3">Waktu</th>
                <th class="px-4 py-3">Jenis</th>
                <th class="px-4 py-3">Deskripsi</th>
                <th class="px-4 py-3">Objek</th>
                <th class="px-4 py-3">Oleh</th>
            </tr>
        </x-slot:kepala>

        @forelse ($daftar as $log)
            <tr class="hover:bg-slate-50">
                <td class="whitespace-nowrap px-4 py-3 text-sm text-slate-600">
                    {{ $log->created_at->format('d/m/Y H:i') }}
                </td>
                <td class="px-4 py-3">
                    <span class="rounded-full bg-slate-100 px-2 py-0.5 text-xs font-semibold text-slate-600">
                        {{ $log->log_name ?? 'umum' }}
                    </span>
                </td>
                <td class="px-4 py-3 text-slate-700">{{ $log->description }}</td>
                <td class="px-4 py-3 text-sm text-slate-500">
                    {{ class_basename($log->subject_type ?? '') }} #{{ $log->subject_id }}
                </td>
                <td class="px-4 py-3 text-sm text-slate-600">{{ $log->causer?->name ?? 'Sistem' }}</td>
            </tr>
        @empty
            <x-tabel.kosong :kolom="5" ikon="clock"
                            judul="Belum ada aktivitas tercatat"
                            pesan="Log akan terisi seiring perubahan data nilai, presensi, dan jadwal."/>
        @endforelse
    </x-tabel>

    <div class="mt-4">{{ $daftar->links() }}</div>
@endsection
