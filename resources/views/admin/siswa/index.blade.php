@extends('layouts.app')
@section('judul', 'Siswa')

@section('konten')
    <x-kepala-halaman judul="Data Siswa">
        <x-slot:aksi>
            <button type="button"
                    @click="$dispatch('buka-modal', 'reset-siswa')"
                    class="inline-flex items-center gap-1.5 rounded-lg border border-rose-200 bg-rose-50/80 px-3 py-2 text-xs font-bold text-rose-700 hover:bg-rose-100 hover:border-rose-300 transition-all shadow-sm">
                <x-heroicon-o-arrow-path class="h-4 w-4 text-rose-600"/>
                Reset Siswa & Kelas
            </button>
            <x-tombol gaya="garis" ikon="document-arrow-down" :href="route('admin.siswa.template')">Unduh Template</x-tombol>
            <x-tombol gaya="garis" ikon="arrow-down-tray" :href="route('admin.siswa.ekspor', request()->query())">Ekspor CSV</x-tombol>
            <x-tombol gaya="garis" ikon="arrow-up-tray" @click="$dispatch('buka-modal', 'impor-siswa')">Impor</x-tombol>
            <x-tombol gaya="aksen" ikon="plus" :href="route('admin.siswa.create')">Tambah Siswa</x-tombol>
        </x-slot:aksi>
    </x-kepala-halaman>

    @if (session('kelasBaruCreated'))
        <div class="mb-4 rounded-xl border border-emerald-200 bg-emerald-50 p-4 text-sm text-emerald-900">
            <p class="font-semibold">Kelas baru otomatis dibuat dari data siswa ({{ count(session('kelasBaruCreated')) }} kelas):</p>
            <div class="mt-1 flex flex-wrap gap-1">
                @foreach (session('kelasBaruCreated') as $namaKelas)
                    <span class="rounded bg-emerald-100 px-2 py-0.5 text-xs font-semibold text-emerald-800">{{ $namaKelas }}</span>
                @endforeach
            </div>
        </div>
    @endif

    @if (session('galatImpor'))
        <div class="mb-4 rounded-xl border border-amber-200 bg-amber-50 p-4 text-sm text-amber-900">
            <p class="font-semibold">Catatan impor:</p>
            <ul class="mt-1 max-h-56 list-inside list-disc space-y-0.5 overflow-y-auto">
                @foreach (session('galatImpor') as $pesan)
                    <li>{{ $pesan }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="GET" class="kartu mb-6 flex flex-wrap items-end gap-3 p-4">
        <x-bidang label="Cari" nama="cari" class="min-w-56 flex-1">
            <x-isian nama="cari" :value="request('cari')" placeholder="Nama, NISN, atau NIS"/>
        </x-bidang>
        <x-bidang label="Kelas" nama="kelas_id" class="min-w-48">
            <x-pilihan nama="kelas_id" :opsi="$kelasList->pluck('nama', 'id')" :terpilih="$kelasId" kosong="Semua kelas"/>
        </x-bidang>
        <x-tombol gaya="utama" ikon="magnifying-glass">Cari</x-tombol>
        <x-tombol gaya="halus" :href="route('admin.siswa.index')">Reset</x-tombol>
    </form>

    <x-tabel>
        <x-slot:kepala>
            <tr>
                <th class="px-4 py-3">NISN</th>
                <th class="px-4 py-3">Nama</th>
                <th class="px-4 py-3">L/P</th>
                <th class="px-4 py-3">Kelas</th>
                <th class="px-4 py-3 text-center">Status</th>
                <th class="px-4 py-3 text-right">Aksi</th>
            </tr>
        </x-slot:kepala>

        @forelse ($daftar as $siswa)
            <tr class="hover:bg-slate-50">
                <td class="px-4 py-3 text-slate-600">{{ $siswa->nisn }}</td>
                <td class="px-4 py-3">
                    <p class="font-medium text-slate-800">{{ $siswa->nama }}</p>
                    @if ($siswa->nis)
                        <p class="text-xs text-slate-400">NIS {{ $siswa->nis }}</p>
                    @endif
                </td>
                <td class="px-4 py-3 text-slate-600">{{ $siswa->jenis_kelamin }}</td>
                <td class="px-4 py-3">
                    @forelse ($siswa->kelas as $kelas)
                        <span class="rounded-full bg-slate-100 px-2 py-0.5 text-xs text-slate-600">
                            {{ $kelas->nama }} · abs {{ $kelas->pivot->no_absen }}
                        </span>
                    @empty
                        <span class="text-xs text-slate-400">Belum masuk kelas</span>
                    @endforelse
                </td>
                <td class="px-4 py-3 text-center">
                    <span @class([
                        'rounded-full px-2 py-0.5 text-xs font-semibold',
                        'bg-emerald-100 text-emerald-800' => $siswa->is_aktif,
                        'bg-slate-100 text-slate-500' => ! $siswa->is_aktif,
                    ])>{{ $siswa->is_aktif ? 'Aktif' : 'Nonaktif' }}</span>
                </td>
                <td class="px-4 py-3">
                    <div class="flex justify-end gap-1">
                        <a href="{{ route('admin.siswa.edit', $siswa) }}" title="Ubah"
                           class="tombol-sentuh rounded-lg p-2 text-slate-500 hover:bg-slate-100 hover:text-primary">
                            <x-heroicon-o-pencil-square class="h-5 w-5"/>
                        </a>
                        <form method="POST" action="{{ route('admin.siswa.destroy', $siswa) }}"
                              onsubmit="return confirm('Nonaktifkan siswa ini? Data nilai & presensi tetap tersimpan.')">
                            @csrf @method('DELETE')
                            <button type="submit" title="Nonaktifkan"
                                    class="tombol-sentuh rounded-lg p-2 text-slate-500 hover:bg-rose-50 hover:text-rose-600">
                                <x-heroicon-o-trash class="h-5 w-5"/>
                            </button>
                        </form>
                    </div>
                </td>
            </tr>
        @empty
            <x-tabel.kosong :kolom="6" ikon="users"
                            judul="Belum ada siswa"
                            pesan="Tambahkan satu per satu, atau impor massal dari berkas CSV."
                            :aksi-url="route('admin.siswa.create')"
                            aksi-label="Tambah Siswa"/>
        @endforelse
    </x-tabel>

    <div class="mt-4">{{ $daftar->links() }}</div>

    <x-modal nama="impor-siswa" judul="Impor Siswa dari CSV / Excel">
        <form method="POST" action="{{ route('admin.siswa.impor') }}" id="form-impor-siswa"
              enctype="multipart/form-data" class="space-y-4">
            @csrf
            <div class="rounded-lg bg-slate-50 p-3 text-sm text-slate-600 space-y-2">
                <p class="font-semibold text-slate-700">Format kolom CSV / Excel:</p>
                <code class="block text-xs font-mono text-primary font-bold bg-white p-2 rounded border border-slate-200">nisn, nis, nama, jenis_kelamin, kelas, no_absen</code>
                <ul class="list-disc list-inside text-xs text-slate-600 space-y-1">
                    <li><strong>NISN bersifat opsional:</strong> jika kosong, data siswa tetap berhasil disimpan.</li>
                    <li><strong>Kelas otomatis dibuat:</strong> jika kelas pada berkas belum ada di sistem, sistem akan otomatis mendaftarkannya.</li>
                    <li><strong>Pencocokan data:</strong> data dicocokkan berdasarkan NISN, NIS, atau Nama siswa di kelas terkait.</li>
                </ul>
                @if (isset($kelasList) && $kelasList->isNotEmpty())
                    <div class="mt-3 border-t border-slate-200 pt-2 text-xs">
                        <span class="font-medium text-slate-700">Kelas terdaftar di tahun ajaran aktif ({{ $kelasList->count() }}):</span>
                        <div class="mt-1.5 flex max-h-24 flex-wrap gap-1 overflow-y-auto rounded border border-slate-200 bg-white p-2">
                            @foreach ($kelasList as $k)
                                <span class="rounded bg-slate-100 px-1.5 py-0.5 text-[11px] font-mono text-slate-700">{{ $k->nama }}</span>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>

            <x-bidang label="Berkas" nama="berkas" :wajib="true">
                <input type="file" name="berkas" id="berkas" accept=".csv,.xlsx,.xls" required
                       class="block w-full text-sm file:mr-3 file:rounded-lg file:border-0 file:bg-slate-100 file:px-4 file:py-2 file:text-sm file:font-semibold">
            </x-bidang>

            <a href="{{ route('admin.siswa.template') }}" class="inline-block text-sm font-semibold text-accent-600 hover:underline">
                Unduh template_siswa.csv
            </a>
        </form>

        <x-slot:kaki>
            <x-tombol tipe="button" gaya="halus" @click="$dispatch('tutup-modal', 'impor-siswa')">Batal</x-tombol>
            <x-tombol gaya="aksen" ikon="arrow-up-tray" form="form-impor-siswa">Impor</x-tombol>
        </x-slot:kaki>
    </x-modal>

    {{-- Modal Konfirmasi Reset Siswa & Kelas --}}
    <x-modal nama="reset-siswa" judul="Reset Data Siswa & Kelas">
        <form method="POST" action="{{ route('admin.siswa.reset') }}" id="form-reset-siswa" class="space-y-4">
            @csrf
            <div class="rounded-xl border border-rose-200 bg-rose-50 p-4 text-sm text-rose-950 space-y-3">
                <div class="flex items-start gap-3">
                    <x-heroicon-o-exclamation-triangle class="h-6 w-6 text-rose-600 shrink-0 mt-0.5"/>
                    <div>
                        <p class="font-bold text-rose-900 text-sm">Peringatan Tindakan Permanen!</p>
                        <p class="text-xs text-rose-800 mt-1 leading-relaxed">
                            Fitur ini menghapus seluruh data siswa dan kelas pada tahun ajaran aktif secara permanen agar Anda dapat mengunggah berkas siswa baru dari awal.
                        </p>
                    </div>
                </div>

                <div class="rounded-lg bg-white/90 p-3 border border-rose-200 text-xs text-slate-700 space-y-1">
                    <p class="font-bold text-slate-900">Ringkasan data yang akan direset:</p>
                    <div class="flex items-center gap-4 text-slate-600 pt-1">
                        <span>Total Siswa: <strong class="text-rose-700 font-mono">{{ $totalSiswa ?? 0 }}</strong></span>
                        <span>Total Kelas: <strong class="text-rose-700 font-mono">{{ $totalKelas ?? 0 }}</strong></span>
                    </div>
                </div>

                <ul class="list-disc list-inside text-xs text-rose-900/90 space-y-1 pl-2 font-medium">
                    <li>Seluruh data Siswa dan akun siswa akan dihapus permanen</li>
                    <li>Seluruh Kelas pada tahun ajaran aktif akan dihapus permanen</li>
                    <li>Seluruh riwayat nilai siswa, presensi siswa, dan rapor mapel akan dibersihkan</li>
                </ul>

                <div class="rounded-lg bg-emerald-50 p-2.5 text-xs text-emerald-900 border border-emerald-200 font-semibold flex items-center gap-2">
                    <x-heroicon-o-shield-check class="h-5 w-5 text-emerald-600 shrink-0"/>
                    <span>Jadwal Pelajaran & Akun Guru TETAP AMAN (keterikatan kelas dilepas menjadi kosong dan siap dipakai kembali).</span>
                </div>
            </div>

            <p class="text-xs text-slate-500">
                Setelah reset selesai, Anda dapat langsung mengunggah berkas siswa baru via <strong>Impor</strong>. Kelas akan otomatis dibuat mengikuti data siswa di berkas.
            </p>
        </form>

        <x-slot:kaki>
            <x-tombol tipe="button" gaya="halus" @click="$dispatch('tutup-modal', 'reset-siswa')">Batal</x-tombol>
            <button type="submit" form="form-reset-siswa"
                    class="inline-flex items-center gap-1.5 rounded-lg bg-rose-600 px-4 py-2 text-xs font-bold text-white shadow-sm hover:bg-rose-700 transition-all">
                <x-heroicon-o-trash class="h-4 w-4"/>
                Ya, Reset Semua Siswa & Kelas
            </button>
        </x-slot:kaki>
    </x-modal>
@endsection
