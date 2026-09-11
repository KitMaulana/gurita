@extends('layouts.app')
@section('judul', 'Tahun Ajaran')

@php use App\Support\Tanggal; @endphp

@section('konten')
    <x-kepala-halaman judul="Tahun Ajaran"
                      keterangan="Hanya satu tahun ajaran yang boleh aktif. Seluruh modul mengikuti tahun aktif.">
        <x-slot:aksi>
            <x-tombol gaya="garis" ikon="document-duplicate" @click="$dispatch('buka-modal', 'modal-salin-semester-global')">
                Salin Data Semester
            </x-tombol>
            <x-tombol gaya="aksen" ikon="plus" :href="route('admin.tahun-ajaran.create')">Tambah Tahun Ajaran</x-tombol>
        </x-slot:aksi>
    </x-kepala-halaman>

    <x-tabel>
        <x-slot:kepala>
            <tr>
                <th class="px-4 py-3">Tahun Ajaran</th>
                <th class="px-4 py-3">Semester</th>
                <th class="px-4 py-3">Periode</th>
                <th class="px-4 py-3 text-center">Kelas</th>
                <th class="px-4 py-3 text-center">Jadwal</th>
                <th class="px-4 py-3 text-center">Status</th>
                <th class="px-4 py-3 text-right">Aksi</th>
            </tr>
        </x-slot:kepala>

        @forelse ($daftar as $ta)
            <tr class="hover:bg-slate-50">
                <td class="px-4 py-3 font-semibold text-slate-800">{{ $ta->nama }}</td>
                <td class="px-4 py-3 capitalize text-slate-600">{{ $ta->semester }}</td>
                <td class="px-4 py-3 text-sm text-slate-600">
                    {{ Tanggal::pendek($ta->tanggal_mulai) }} – {{ Tanggal::pendek($ta->tanggal_selesai) }}
                </td>
                <td class="px-4 py-3 text-center text-slate-600">{{ $ta->kelas_count }}</td>
                <td class="px-4 py-3 text-center text-slate-600">{{ $ta->jadwals_count }}</td>
                <td class="px-4 py-3 text-center">
                    @if ($ta->is_aktif)
                        <span class="rounded-full bg-emerald-100 px-2.5 py-0.5 text-xs font-bold text-emerald-800">Aktif</span>
                    @else
                        <form method="POST" action="{{ route('admin.tahun-ajaran.aktifkan', $ta) }}"
                              onsubmit="return confirm('Aktifkan tahun ajaran ini? Tahun ajaran yang sedang aktif akan dinonaktifkan.')">
                            @csrf
                            <button type="submit" class="text-xs font-semibold text-accent-600 hover:underline">Aktifkan</button>
                        </form>
                    @endif
                </td>
                <td class="px-4 py-3">
                    <div class="flex justify-end items-center gap-1">
                        @if ($ta->semesterSebelumnya())
                            <button type="button" title="Salin Data dari {{ $ta->semesterSebelumnya()->label }}"
                                    @click="$dispatch('buka-modal', 'salin-semester-{{ $ta->id }}')"
                                    class="tombol-sentuh rounded-lg p-2 text-emerald-600 hover:bg-emerald-50 transition-colors">
                                <x-heroicon-o-document-duplicate class="h-5 w-5"/>
                            </button>
                        @elseif ($ta->semester === 'ganjil')
                            @php
                                $genap = $daftar->first(fn($item) => $item->nama === $ta->nama && $item->semester === 'genap');
                            @endphp
                            @if ($genap)
                                <button type="button" title="Salin Data ke {{ $genap->label }}"
                                        @click="$dispatch('buka-modal', 'salin-semester-{{ $genap->id }}')"
                                        class="tombol-sentuh rounded-lg p-2 text-emerald-600 hover:bg-emerald-50 transition-colors">
                                    <x-heroicon-o-document-duplicate class="h-5 w-5"/>
                                </button>
                            @else
                                <button type="button" title="Salin Data Semester {{ $ta->nama }} (Buat Genap)"
                                        @click="$dispatch('buka-modal', 'modal-info-salin-{{ $ta->id }}')"
                                        class="tombol-sentuh rounded-lg p-2 text-slate-400 hover:bg-slate-100 hover:text-slate-600 transition-colors">
                                    <x-heroicon-o-document-duplicate class="h-5 w-5"/>
                                </button>
                            @endif
                        @endif
                        <a href="{{ route('admin.tahun-ajaran.edit', $ta) }}" title="Ubah"
                           class="tombol-sentuh rounded-lg p-2 text-slate-500 hover:bg-slate-100 hover:text-primary">
                            <x-heroicon-o-pencil-square class="h-5 w-5"/>
                        </a>
                        <form method="POST" action="{{ route('admin.tahun-ajaran.destroy', $ta) }}"
                              onsubmit="return confirm('Hapus tahun ajaran ini?')">
                            @csrf @method('DELETE')
                            <button type="submit" title="Hapus"
                                    class="tombol-sentuh rounded-lg p-2 text-slate-500 hover:bg-rose-50 hover:text-rose-600">
                                <x-heroicon-o-trash class="h-5 w-5"/>
                            </button>
                        </form>
                    </div>
                </td>
            </tr>
        @empty
            <x-tabel.kosong :kolom="7" ikon="calendar"
                            judul="Belum ada tahun ajaran"
                            pesan="Buat tahun ajaran dan aktifkan sebelum mengisi data lain."
                            :aksi-url="route('admin.tahun-ajaran.create')"
                            aksi-label="Tambah Tahun Ajaran"/>
        @endforelse
    </x-tabel>

    <div class="mt-4">{{ $daftar->links() }}</div>

    {{-- Modals Salin Data Semester --}}
    @foreach ($daftar as $ta)
        @if ($ta->semesterSebelumnya())
            @php $asal = $ta->semesterSebelumnya(); @endphp
            <x-modal nama="salin-semester-{{ $ta->id }}" judul="Salin Data ke {{ $ta->label }}">
                <form method="POST" action="{{ route('admin.tahun-ajaran.salin-data', $ta) }}" id="form-salin-{{ $ta->id }}" class="space-y-4">
                    @csrf
                    <input type="hidden" name="semester_asal_id" value="{{ $asal->id }}">

                    <div class="rounded-xl border border-sky-200 bg-sky-50 p-4 text-xs text-sky-950 space-y-2">
                        <div class="flex items-center gap-2 font-bold text-sky-900 text-sm">
                            <x-heroicon-o-arrow-path-rounded-square class="h-5 w-5 text-sky-600 shrink-0"/>
                            Salin Data Antar Semester (Tahun Pelajaran Sama)
                        </div>
                        <p class="leading-relaxed text-sky-800">
                            Fitur ini menyalin data dari <strong>{{ $asal->label }}</strong> ke <strong>{{ $ta->label }}</strong> dalam Tahun Pelajaran yang sama (<strong>{{ $ta->nama }}</strong>).
                            Sangat cocok untuk perpindahan semester karena pergantian siswa & jadwal biasanya bersifat minor.
                        </p>
                        <p class="text-[11px] text-sky-700/80 font-medium">
                            💡 Data presensi harian, agenda mengajar, dan nilai siswa tetap bersih dan dimulai baru di semester tujuan.
                        </p>
                    </div>

                    <div class="rounded-lg border border-slate-200 bg-white p-3 space-y-2.5 text-xs">
                        <p class="font-bold text-slate-800">Pilih komponen data yang akan disalin:</p>
                        <label class="flex items-start gap-2.5 text-slate-700 cursor-pointer">
                            <input type="checkbox" name="salin_kelas" value="1" checked
                                   class="mt-0.5 rounded border-slate-300 text-accent focus:ring-accent">
                            <div>
                                <span class="font-semibold text-slate-900">Struktur Kelas & Wali Kelas</span>
                                <p class="text-slate-500 text-[11px]">Menyalin {{ $asal->kelas_count }} kelas beserta tingkat, jurusan, dan wali kelasnya.</p>
                            </div>
                        </label>
                        <label class="flex items-start gap-2.5 text-slate-700 cursor-pointer pl-6">
                            <input type="checkbox" name="salin_siswa" value="1" checked
                                   class="mt-0.5 rounded border-slate-300 text-accent focus:ring-accent">
                            <div>
                                <span class="font-semibold text-slate-900">Daftar Siswa & Nomor Absen</span>
                                <p class="text-slate-500 text-[11px]">Menempatkan siswa ke dalam kelas dan nomor absen yang sama.</p>
                            </div>
                        </label>
                        <label class="flex items-start gap-2.5 text-slate-700 cursor-pointer">
                            <input type="checkbox" name="salin_jadwal" value="1" checked
                                   class="mt-0.5 rounded border-slate-300 text-accent focus:ring-accent">
                            <div>
                                <span class="font-semibold text-slate-900">Jadwal Pelajaran</span>
                                <p class="text-slate-500 text-[11px]">Menyalin {{ $asal->jadwals_count }} jadwal pelajaran, otomatis dipetakan ke kelas baru.</p>
                            </div>
                        </label>
                        <label class="flex items-start gap-2.5 text-slate-700 cursor-pointer">
                            <input type="checkbox" name="salin_kktp" value="1" checked
                                   class="mt-0.5 rounded border-slate-300 text-accent focus:ring-accent">
                            <div>
                                <span class="font-semibold text-slate-900">Standar KKTP</span>
                                <p class="text-slate-500 text-[11px]">Menyalin kriteria ketuntasan per mata pelajaran dan tingkatan kelas.</p>
                            </div>
                        </label>
                    </div>

                    <div class="rounded-lg bg-amber-50 border border-amber-200 p-2.5 text-[11px] text-amber-900 flex items-start gap-2">
                        <x-heroicon-o-information-circle class="h-4 w-4 text-amber-600 shrink-0 mt-0.5"/>
                        <span>Jika nama kelas atau jadwal yang sama sudah terdaftar di semester tujuan, sistem tidak akan menggandakannya (aman dari duplikasi).</span>
                    </div>
                </form>

                <x-slot:kaki>
                    <x-tombol tipe="button" gaya="halus" @click="$dispatch('tutup-modal', 'salin-semester-{{ $ta->id }}')">Batal</x-tombol>
                    <x-tombol gaya="aksen" ikon="arrow-path-rounded-square" form="form-salin-{{ $ta->id }}">Salin Data Sekarang</x-tombol>
                </x-slot:kaki>
            </x-modal>
        @endif
    @endforeach

    {{-- Modal Informasi Salin Data untuk Semester Ganjil yang belum ada Semester Genap-nya --}}
    @foreach ($daftar as $ta)
        @if ($ta->semester === 'ganjil' && !$daftar->first(fn($item) => $item->nama === $ta->nama && $item->semester === 'genap'))
            <x-modal nama="modal-info-salin-{{ $ta->id }}" judul="Salin Data Semester — {{ $ta->label }}">
                <div class="space-y-4">
                    <div class="rounded-xl border border-sky-200 bg-sky-50 p-4 text-xs text-sky-950 space-y-2">
                        <div class="flex items-center gap-2 font-bold text-sky-900 text-sm">
                            <x-heroicon-o-information-circle class="h-5 w-5 text-sky-600 shrink-0"/>
                            Aturan Salin Data Semester (Tahun Pelajaran Sama)
                        </div>
                        <p class="leading-relaxed text-sky-800">
                            Fitur salin data digunakan untuk perpindahan semester dalam <strong>tahun pelajaran yang sama</strong> (misalnya dari Semester Ganjil ke Semester Genap).
                        </p>
                        <p class="leading-relaxed text-sky-800">
                            Tahun ajaran <strong>{{ $ta->label }}</strong> memiliki data yang siap disalin:
                        </p>
                        <ul class="list-disc list-inside space-y-1 font-semibold text-sky-900 pl-1">
                            <li>{{ $ta->kelas_count }} Kelas beserta Wali Kelas</li>
                            <li>{{ $ta->jadwals_count }} Jadwal Pelajaran</li>
                            <li>Daftar Siswa & Nomor Absen</li>
                            <li>Standar KKTP</li>
                        </ul>
                    </div>

                    <div class="rounded-lg border border-amber-200 bg-amber-50 p-3 text-xs text-amber-900 flex items-start gap-2.5">
                        <x-heroicon-o-exclamation-triangle class="h-5 w-5 text-amber-600 shrink-0 mt-0.5"/>
                        <div class="space-y-1">
                            <p class="font-bold">Semester Genap Belum Terdaftar</p>
                            <p class="leading-relaxed">
                                Semester Genap untuk tahun pelajaran <strong>{{ $ta->nama }}</strong> belum dibuat di sistem.
                                Klik tombol di bawah ini untuk membuat Semester Genap dan sistem akan otomatis menyalin seluruh kelas, siswa, jadwal, dan KKTP ke semester baru tersebut.
                            </p>
                        </div>
                    </div>
                </div>

                <x-slot:kaki>
                    <x-tombol tipe="button" gaya="halus" @click="$dispatch('tutup-modal', 'modal-info-salin-{{ $ta->id }}')">Tutup</x-tombol>
                    <x-tombol gaya="aksen" ikon="plus" :href="route('admin.tahun-ajaran.create', ['nama' => $ta->nama, 'semester' => 'genap'])">
                        Buat Semester Genap & Salin Data
                    </x-tombol>
                </x-slot:kaki>
            </x-modal>
        @endif
    @endforeach

    {{-- Modal Global Salin Data Semester --}}
    <x-modal nama="modal-salin-semester-global" judul="Salin Data Antar-Semester">
        <div class="space-y-4">
            <div class="rounded-xl border border-sky-200 bg-sky-50 p-4 text-xs text-sky-950 space-y-2">
                <div class="flex items-center gap-2 font-bold text-sky-900 text-sm">
                    <x-heroicon-o-arrow-path-rounded-square class="h-5 w-5 text-sky-600 shrink-0"/>
                    Ketentuan Salin Data Antar-Semester
                </div>
                <p class="leading-relaxed text-sky-800">
                    Sesuai aturan sekolah, fitur salin data hanya berlaku <strong>antar-semester dalam tahun pelajaran yang sama</strong> (misalnya dari <strong>Ganjil</strong> ke <strong>Genap</strong>), karena saat pergantian semester data siswa, rombel, dan jadwal umumnya tidak banyak berubah.
                </p>
                <p class="text-[11px] text-sky-700 font-medium">
                    💡 Jika berganti tahun pelajaran baru (misal: 2026/2027 ke 2027/2028), data harus diinput baru sesuai regulasi kenaikan kelas & kelulusan siswa.
                </p>
            </div>

            <div class="space-y-3">
                <p class="text-xs font-bold text-slate-700">Pilih Tahun Pelajaran:</p>
                @php
                    $grupTahun = $daftar->groupBy('nama');
                @endphp
                @foreach ($grupTahun as $namaTahun => $items)
                    @php
                        $itemGanjil = $items->firstWhere('semester', 'ganjil');
                        $itemGenap  = $items->firstWhere('semester', 'genap');
                    @endphp
                    <div class="rounded-xl border border-slate-200 bg-white p-3.5 shadow-sm space-y-2.5">
                        <div class="flex items-center justify-between">
                            <span class="font-bold text-sm text-slate-900">{{ $namaTahun }}</span>
                            @if ($itemGenap && $itemGanjil)
                                <span class="rounded-full bg-emerald-100 px-2.5 py-0.5 text-[11px] font-semibold text-emerald-800">Siap Disalin</span>
                            @elseif ($itemGanjil)
                                <span class="rounded-full bg-amber-100 px-2.5 py-0.5 text-[11px] font-semibold text-amber-800">Semester Genap Belum Ada</span>
                            @endif
                        </div>

                        <div class="grid grid-cols-2 gap-2 text-xs">
                            <div class="rounded-lg bg-slate-50 p-2.5 border border-slate-100">
                                <p class="font-semibold text-slate-700">Semester Ganjil</p>
                                @if ($itemGanjil)
                                    <p class="text-slate-500 text-[11px] mt-0.5">{{ $itemGanjil->kelas_count }} Kelas · {{ $itemGanjil->jadwals_count }} Jadwal</p>
                                @else
                                    <p class="text-slate-400 italic text-[11px] mt-0.5">Belum dibuat</p>
                                @endif
                            </div>
                            <div class="rounded-lg bg-slate-50 p-2.5 border border-slate-100">
                                <p class="font-semibold text-slate-700">Semester Genap</p>
                                @if ($itemGenap)
                                    <p class="text-slate-500 text-[11px] mt-0.5">{{ $itemGenap->kelas_count }} Kelas · {{ $itemGenap->jadwals_count }} Jadwal</p>
                                @else
                                    <p class="text-slate-400 italic text-[11px] mt-0.5">Belum dibuat</p>
                                @endif
                            </div>
                        </div>

                        <div class="pt-1 flex justify-end">
                            @if ($itemGenap && $itemGanjil)
                                <x-tombol tipe="button" gaya="aksen" ikon="arrow-path-rounded-square"
                                          @click="$dispatch('tutup-modal', 'modal-salin-semester-global'); $dispatch('buka-modal', 'salin-semester-{{ $itemGenap->id }}')">
                                    Salin Data ke Semester Genap
                                </x-tombol>
                            @elseif ($itemGanjil)
                                <x-tombol gaya="aksen" ikon="plus" :href="route('admin.tahun-ajaran.create', ['nama' => $namaTahun, 'semester' => 'genap'])">
                                    Buat Semester Genap & Salin Data
                                </x-tombol>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <x-slot:kaki>
            <x-tombol tipe="button" gaya="halus" @click="$dispatch('tutup-modal', 'modal-salin-semester-global')">Tutup</x-tombol>
        </x-slot:kaki>
    </x-modal>
@endsection
