@extends('layouts.app')
@section('judul', 'Siswa '.$kelas->nama)

@section('konten')
    <x-kepala-halaman :judul="'Anggota Kelas '.$kelas->nama"
                      :keterangan="$kelas->tahunAjaran->label.' · Wali kelas: '.($kelas->waliKelas?->name ?? 'belum ditentukan')">
        <x-slot:aksi>
            <x-tombol gaya="halus" ikon="arrow-left" :href="route('admin.kelas.index')">Kembali</x-tombol>
        </x-slot:aksi>
    </x-kepala-halaman>

    <div class="grid gap-6 lg:grid-cols-3">
        <div class="lg:col-span-2">
            <x-tabel>
                <x-slot:kepala>
                    <tr>
                        <th class="px-4 py-3">No. Absen</th>
                        <th class="px-4 py-3">NISN</th>
                        <th class="px-4 py-3">Nama Siswa</th>
                        <th class="px-4 py-3">L/P</th>
                        <th class="px-4 py-3 text-right">Aksi</th>
                    </tr>
                </x-slot:kepala>

                @forelse ($siswas as $siswa)
                    <tr class="hover:bg-slate-50">
                        <td class="px-4 py-3 font-semibold text-slate-700">{{ $siswa->pivot->no_absen }}</td>
                        <td class="px-4 py-3 text-slate-600">{{ $siswa->nisn }}</td>
                        <td class="px-4 py-3 font-medium text-slate-800">{{ $siswa->nama }}</td>
                        <td class="px-4 py-3 text-slate-600">{{ $siswa->jenis_kelamin }}</td>
                        <td class="px-4 py-3 text-right">
                            <form method="POST" action="{{ route('admin.kelas.siswa.keluarkan', [$kelas, $siswa]) }}"
                                  onsubmit="return confirm('Keluarkan siswa ini dari kelas? Data nilai & presensinya tetap tersimpan.')">
                                @csrf @method('DELETE')
                                <button type="submit" title="Keluarkan dari kelas"
                                        class="tombol-sentuh rounded-lg p-2 text-slate-500 hover:bg-rose-50 hover:text-rose-600">
                                    <x-heroicon-o-user-minus class="h-5 w-5"/>
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <x-tabel.kosong :kolom="5" ikon="users"
                                    judul="Kelas ini belum punya siswa"
                                    pesan="Tambahkan siswa lewat formulir di samping, atau impor massal dari menu Siswa."/>
                @endforelse
            </x-tabel>
        </div>

        <div>
            <form method="POST" action="{{ route('admin.kelas.siswa.tambah', $kelas) }}" class="kartu p-5">
                @csrf
                <h3 class="mb-4 font-bold text-primary">Tambah Siswa ke Kelas</h3>

                <div class="space-y-4">
                    <x-bidang label="Siswa" nama="siswa_id" :wajib="true"
                              petunjuk="Hanya siswa yang belum masuk kelas mana pun di tahun ajaran ini.">
                        <x-pilihan nama="siswa_id"
                                   :opsi="$siswaTersedia->mapWithKeys(fn ($s) => [$s->id => $s->nama.' ('.$s->nisn.')'])"
                                   kosong="— Pilih siswa —"/>
                    </x-bidang>

                    <x-bidang label="Nomor Absen" nama="no_absen" :wajib="true">
                        <x-isian nama="no_absen" tipe="number" min="1" max="255" :value="old('no_absen', $nomorBerikutnya)"/>
                    </x-bidang>
                </div>

                <x-tombol gaya="aksen" ikon="user-plus" class="mt-4 w-full">Tambahkan</x-tombol>
            </form>

            <div class="kartu mt-4 p-5">
                <p class="text-sm text-slate-600">
                    Butuh menambahkan banyak siswa sekaligus?
                </p>
                <x-tombol gaya="garis" ikon="arrow-up-tray" class="mt-3 w-full" :href="route('admin.siswa.index')">
                    Impor Siswa dari CSV
                </x-tombol>
            </div>
        </div>
    </div>
@endsection
