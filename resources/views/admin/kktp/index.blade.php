@extends('layouts.app')
@section('judul', 'KKTP')

@section('konten')
    <x-kepala-halaman judul="KKTP — Kriteria Ketercapaian Tujuan Pembelajaran"
                      :keterangan="'Tahun ajaran '.($tahunAjaran?->label ?? '—').'. Bawaan 75 bila belum diisi.'"/>

    <form method="POST" action="{{ route('admin.kktp.simpan') }}">
        @csrf

        <x-tabel>
            <x-slot:kepala>
                <tr>
                    <th class="px-4 py-3">Mata Pelajaran</th>
                    @foreach ($tingkatList as $tingkat)
                        <th class="px-4 py-3 text-center">Kelas {{ $tingkat }}</th>
                    @endforeach
                </tr>
            </x-slot:kepala>

            @forelse ($mapelList as $mapel)
                <tr class="hover:bg-slate-50">
                    <td class="px-4 py-3 font-medium text-slate-800">{{ $mapel->nama }}</td>
                    @foreach ($tingkatList as $tingkat)
                        <td class="px-4 py-2 text-center">
                            <input type="number" min="0" max="100"
                                   name="kktp[{{ $mapel->id }}-{{ $tingkat }}]"
                                   value="{{ $nilai[$mapel->id.'-'.$tingkat] ?? 75 }}"
                                   class="w-20 rounded-lg border-slate-300 py-1.5 text-center text-sm focus:border-primary focus:ring-primary">
                        </td>
                    @endforeach
                </tr>
            @empty
                <x-tabel.kosong :kolom="count($tingkatList) + 1" ikon="check-badge"
                                judul="Belum ada mata pelajaran"
                                pesan="Tambahkan mata pelajaran terlebih dahulu."
                                :aksi-url="route('admin.mata-pelajaran.create')"
                                aksi-label="Tambah Mata Pelajaran"/>
            @endforelse
        </x-tabel>

        @if ($mapelList->isNotEmpty())
            <div class="mt-5 flex justify-end">
                <x-tombol gaya="aksen" ikon="check">Simpan KKTP</x-tombol>
            </div>
        @endif
    </form>
@endsection
