@extends('layouts.app')
@section('judul', 'Kelola Jadwal')

@section('konten')
    <div x-data="{
        selected: [],
        allIds: {{ json_encode($daftar->pluck('id')->all()) }},
        get allSelected() {
            return this.allIds.length > 0 && this.selected.length === this.allIds.length;
        },
        toggleAll() {
            if (this.allSelected) {
                this.selected = [];
            } else {
                this.selected = [...this.allIds];
            }
        }
    }">
        <x-kepala-halaman judul="Kelola Jadwal"
                          keterangan="Sistem menolak jadwal yang bentrok. Mendukung jadwal mata pelajaran dan agenda bersama sekolah.">
            <x-slot:aksi>
                {{-- Tombol Bulk Delete (muncul saat ada checkbox dicentang) --}}
                <form id="form-bulk-delete" method="POST" action="{{ route('admin.jadwal.bulk-delete') }}"
                      x-show="selected.length > 0" x-cloak
                      onsubmit="return confirm('Apakah Anda yakin ingin menghapus ' + this.__x.$data.selected.length + ' jadwal terpilih?')">
                    @csrf
                    <template x-for="id in selected" :key="id">
                        <input type="hidden" name="ids[]" :value="id">
                    </template>
                    <button type="submit"
                            class="inline-flex items-center gap-1.5 rounded-lg bg-rose-600 px-3 py-2 text-xs font-bold text-white shadow-sm hover:bg-rose-700 transition-all">
                        <x-heroicon-o-trash class="h-4 w-4"/>
                        Hapus Terpilih (<span x-text="selected.length"></span>)
                    </button>
                </form>

                <button type="button"
                        @click="$dispatch('buka-modal', 'modal-mode-jadwal')"
                        class="inline-flex items-center gap-1.5 rounded-lg border px-3 py-2 text-xs font-bold transition-all shadow-sm {{ $modeAktif === 'ramadhan' ? 'border-emerald-300 bg-emerald-50 text-emerald-800 hover:bg-emerald-100' : ($modeAktif === 'khusus' ? 'border-amber-300 bg-amber-50 text-amber-800 hover:bg-amber-100' : 'border-slate-300 bg-white text-slate-700 hover:bg-slate-50') }}">
                    @if ($modeAktif === 'ramadhan')
                        <span>🌙</span>
                    @elseif ($modeAktif === 'khusus')
                        <x-heroicon-o-academic-cap class="h-4 w-4 text-amber-600"/>
                    @else
                        <x-heroicon-o-clock class="h-4 w-4 text-slate-500"/>
                    @endif
                    Mode: {{ $daftarMode[$modeAktif]['label'] ?? 'Reguler' }}
                </button>

                <button type="button"
                        @click="$dispatch('buka-modal', 'reset-jadwal')"
                        class="inline-flex items-center gap-1.5 rounded-lg border border-rose-200 bg-rose-50/80 px-3 py-2 text-xs font-bold text-rose-700 hover:bg-rose-100 hover:border-rose-300 transition-all shadow-sm">
                    <x-heroicon-o-arrow-path class="h-4 w-4 text-rose-600"/>
                    Reset Jadwal
                </button>
                <x-tombol gaya="garis" ikon="document-arrow-down" :href="route('admin.jadwal.template')">Unduh Template</x-tombol>
                <x-tombol gaya="garis" ikon="arrow-up-tray" @click="$dispatch('buka-modal', 'impor-jadwal')">Impor CSV</x-tombol>
                <x-tombol gaya="garis" ikon="squares-plus" :href="route('admin.jadwal.bulk')">Input Massal</x-tombol>
                <x-tombol gaya="aksen" ikon="plus" :href="route('admin.jadwal.create')">Tambah Jadwal</x-tombol>
            </x-slot:aksi>
        </x-kepala-halaman>

        @if ($modeAktif === 'ramadhan')
            <div class="mb-5 rounded-xl border border-emerald-300 bg-gradient-to-r from-emerald-50 via-teal-50 to-emerald-50 p-4 text-xs text-emerald-950 shadow-sm flex flex-wrap items-center justify-between gap-3">
                <div class="flex items-center gap-3">
                    <span class="text-3xl">🌙</span>
                    <div>
                        <p class="font-bold text-sm text-emerald-900">Mode Bulan Ramadhan Sedang Aktif</p>
                        <p class="text-emerald-800 mt-0.5 leading-relaxed">
                            Jam pelajaran di seluruh akun guru, beranda sekolah, jadwal kelas, dan cetak jadwal otomatis mengikuti slot waktu Ramadhan (JP dipersingkat 30 menit).
                            <span class="font-semibold text-emerald-950">Data agenda mengajar & presensi historis tetap aman dan tidak berubah.</span>
                        </p>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <button type="button" @click="$dispatch('buka-modal', 'modal-mode-jadwal')"
                            class="rounded-lg bg-emerald-600 px-3 py-1.5 font-bold text-white shadow-sm hover:bg-emerald-700 transition-colors text-xs">
                        Ganti Mode Jadwal
                    </button>
                </div>
            </div>
        @elseif ($modeAktif === 'khusus')
            <div class="mb-5 rounded-xl border border-amber-300 bg-gradient-to-r from-amber-50 via-yellow-50 to-amber-50 p-4 text-xs text-amber-950 shadow-sm flex flex-wrap items-center justify-between gap-3">
                <div class="flex items-center gap-3">
                    <x-heroicon-o-academic-cap class="h-7 w-7 text-amber-600 shrink-0"/>
                    <div>
                        <p class="font-bold text-sm text-amber-900">Mode Ujian / Khusus Sedang Aktif</p>
                        <p class="text-amber-800 mt-0.5 leading-relaxed">
                            Jam pelajaran saat ini mengikuti slot waktu ujian/asesmen sekolah. Agenda mengajar dan presensi historis tetap aman.
                        </p>
                    </div>
                </div>
                <button type="button" @click="$dispatch('buka-modal', 'modal-mode-jadwal')"
                        class="rounded-lg bg-amber-600 px-3 py-1.5 font-bold text-white shadow-sm hover:bg-amber-700 transition-colors text-xs">
                    Ganti Mode Jadwal
                </button>
            </div>
        @endif

        @if (session('galatImpor'))
            <div class="mb-4 rounded-xl border border-amber-200 bg-amber-50 p-4 text-sm text-amber-900">
                <p class="font-semibold">Bentrok / kesalahan yang ditemukan pada file impor:</p>
                <ul class="mt-1 max-h-56 list-inside list-disc space-y-0.5 overflow-y-auto">
                    @foreach (session('galatImpor') as $pesan)
                        <li>{{ $pesan }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @if (session('peringatanImpor'))
            <div class="mb-4 rounded-xl border border-sky-200 bg-sky-50/90 p-4 text-sm text-sky-950 shadow-sm">
                <div class="flex items-center gap-2 font-bold text-sky-900">
                    <x-heroicon-o-information-circle class="h-5 w-5 text-sky-600 shrink-0"/>
                    <span>Catatan Jadwal Bersamaan / Paralel (Tetap Berhasil Diinput):</span>
                </div>
                <p class="text-xs text-sky-800 mt-1">
                    Jadwal berikut memiliki guru atau jam yang bersamaan, namun tetap disimpan sesuai kebutuhan operasional sekolah:
                </p>
                <ul class="mt-2 max-h-56 list-inside list-disc space-y-0.5 overflow-y-auto text-xs text-sky-900 font-mono">
                    @foreach (session('peringatanImpor') as $pesan)
                        <li>{{ $pesan }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @if (session('guruBaruCreated') || session('kelasBaruCreated') || session('mapelBaruCreated'))
            <div class="mb-6 rounded-xl border border-emerald-200 bg-emerald-50/90 p-5 text-sm text-emerald-950 shadow-sm space-y-3">
                <div class="flex items-center gap-2 font-bold text-emerald-900 text-base">
                    <x-heroicon-o-check-circle class="h-6 w-6 text-emerald-600"/>
                    Master Data Berhasil Dibuat Otomatis dari Berkas Jadwal
                </div>
                <p class="text-xs text-emerald-800">
                    Sistem otomatis mendaftarkan kelas, mata pelajaran, dan akun guru baru sesuai jadwal yang diunggah.
                </p>

                @if (session('guruBaruCreated'))
                    <div class="mt-3 rounded-lg border border-emerald-200 bg-white p-3">
                        <p class="font-bold text-xs uppercase tracking-wider text-slate-500 mb-2">
                            Daftar Akun Guru Baru (Kata Sandi Bawaan: <code class="bg-emerald-100 px-1.5 py-0.5 rounded font-mono text-emerald-800 font-bold">12345</code>)
                        </p>
                        <div class="overflow-x-auto">
                            <table class="w-full text-xs text-left">
                                <thead class="border-b border-slate-100 text-slate-400">
                                    <tr>
                                        <th class="py-1.5 pr-3">Nama Guru</th>
                                        <th class="py-1.5 pr-3">Email Login</th>
                                        <th class="py-1.5 pr-3">NIP</th>
                                        <th class="py-1.5">Kata Sandi</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100 font-medium text-slate-700">
                                    @foreach (session('guruBaruCreated') as $guru)
                                        <tr>
                                            <td class="py-1.5 pr-3 font-semibold text-slate-900">{{ $guru['nama'] }}</td>
                                            <td class="py-1.5 pr-3 font-mono text-primary">{{ $guru['email'] }}</td>
                                            <td class="py-1.5 pr-3 font-mono text-slate-500">{{ $guru['nip'] ?: '—' }}</td>
                                            <td class="py-1.5"><span class="rounded bg-slate-100 px-2 py-0.5 font-mono text-slate-800">12345</span></td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endif

                @if (session('kelasBaruCreated') || session('mapelBaruCreated'))
                    <div class="flex flex-wrap gap-4 text-xs text-emerald-900 pt-1">
                        @if (session('kelasBaruCreated'))
                            <div>
                                <span class="font-bold">Kelas Baru:</span>
                                {{ implode(', ', session('kelasBaruCreated')) }}
                            </div>
                        @endif
                        @if (session('mapelBaruCreated'))
                            <div>
                                <span class="font-bold">Mata Pelajaran Baru:</span>
                                {{ implode(', ', session('mapelBaruCreated')) }}
                            </div>
                        @endif
                    </div>
                @endif
            </div>
        @endif

        <form method="GET" class="kartu mb-6 flex flex-wrap items-end gap-3 p-4">
            <x-bidang label="Kelas" nama="kelas_id" class="min-w-44 flex-1">
                <x-pilihan nama="kelas_id" :opsi="$kelasList->pluck('nama', 'id')" :terpilih="request('kelas_id')" kosong="Semua kelas"/>
            </x-bidang>
            <x-bidang label="Guru" nama="guru_id" class="min-w-44 flex-1">
                <x-pilihan nama="guru_id" :opsi="$guruList->pluck('name', 'id')" :terpilih="request('guru_id')" kosong="Semua guru"/>
            </x-bidang>
            <x-bidang label="Hari" nama="hari" class="min-w-36">
                <x-pilihan nama="hari" :opsi="$hariList" :terpilih="request('hari')" kosong="Semua hari"/>
            </x-bidang>
            <x-tombol gaya="utama" ikon="funnel">Tampilkan</x-tombol>
        </form>

        <x-tabel>
            <x-slot:kepala>
                <tr>
                    <th class="w-10 px-4 py-3 text-center">
                        <input type="checkbox" @click="toggleAll()" :checked="allSelected"
                               class="h-4 w-4 rounded border-slate-300 text-primary focus:ring-primary cursor-pointer"
                               title="Pilih Semua di Halaman Ini">
                    </th>
                    <th class="px-4 py-3">Hari</th>
                    <th class="px-4 py-3">JP</th>
                    <th class="px-4 py-3">Jam</th>
                    <th class="px-4 py-3">Kelas</th>
                    <th class="px-4 py-3">Mata Pelajaran / Agenda</th>
                    <th class="px-4 py-3">Guru</th>
                    <th class="px-4 py-3">Ruang</th>
                    <th class="px-4 py-3 text-right">Aksi</th>
                </tr>
            </x-slot:kepala>

            @forelse ($daftar as $jadwal)
                <tr class="hover:bg-slate-50 transition-colors"
                    :class="selected.includes({{ $jadwal->id }}) ? 'bg-primary/5' : ''">
                    <td class="px-4 py-3 text-center">
                        <input type="checkbox" :value="{{ $jadwal->id }}" x-model="selected"
                               class="h-4 w-4 rounded border-slate-300 text-primary focus:ring-primary cursor-pointer">
                    </td>
                    <td class="px-4 py-3 font-medium text-slate-700">
                        @php $warnaHari = \App\Support\WarnaMapel::warnaHari($jadwal->hari); @endphp
                        <span class="inline-flex rounded-md px-2.5 py-1 text-xs font-bold text-white shadow-2xs"
                              style="background: {{ $warnaHari['header'] }};">
                            {{ $jadwal->hari->label() }}
                        </span>
                    </td>
                    <td class="px-4 py-3 font-bold text-slate-800">
                        @php $warna = \App\Support\WarnaMapel::untuk($jadwal->nama_tampilan, $jadwal->isAgendaBersama()); @endphp
                        <span class="inline-flex items-center justify-center rounded-lg px-2.5 py-1 text-xs font-black shadow-2xs"
                              style="background-color: {{ $warna['jp_bg'] }}; color: {{ $warna['jp_text'] }};">
                            JP {{ $jadwal->jam_ke }}
                        </span>
                    </td>
                    <td class="whitespace-nowrap px-4 py-3 text-xs font-mono text-slate-600">{{ $jadwal->jam }}</td>
                    <td class="px-4 py-3">
                        @if ($jadwal->isAgendaBersama())
                            <span class="inline-flex items-center gap-1 rounded bg-amber-100 px-2 py-0.5 text-xs font-bold text-amber-800">
                                <x-heroicon-o-globe-alt class="h-3.5 w-3.5"/>
                                Semua Kelas
                            </span>
                        @else
                            @php $warnaKelas = \App\Support\WarnaKelas::untuk($jadwal->kelas_tampilan); @endphp
                            <span class="inline-flex items-center gap-1.5 rounded-lg px-2.5 py-1 text-xs font-black shadow-2xs"
                                  style="background-color: {{ $warnaKelas['badge_bg'] }}; color: {{ $warnaKelas['badge_text'] }}; border: 1.5px solid {{ $warnaKelas['border'] }};">
                                <span class="h-2 w-2 rounded-full shrink-0 shadow-xs" style="background-color: {{ $warnaKelas['accent'] }};"></span>
                                <span>{{ $jadwal->kelas?->nama ?? '—' }}</span>
                            </span>
                        @endif
                    </td>
                    <td class="px-4 py-3">
                        @if ($jadwal->isAgendaBersama())
                            <span class="font-bold text-amber-700 flex items-center gap-1.5">
                                <x-heroicon-o-megaphone class="h-4 w-4 text-amber-500"/>
                                {{ $jadwal->title }}
                            </span>
                        @else
                            <span class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-xs font-black shadow-2xs"
                                  style="background-color: {{ $warna['badge_bg'] }}; color: {{ $warna['badge_text'] }}; border: 1px solid {{ $warna['border'] }};">
                                <span class="h-1.5 w-1.5 rounded-full shrink-0" style="background-color: {{ $warna['dot'] }};"></span>
                                <span>{{ $jadwal->mataPelajaran?->nama ?? '—' }}</span>
                            </span>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-slate-600">
                        @if ($jadwal->isAgendaBersama())
                            <span class="text-xs italic text-slate-400">— Bersama —</span>
                        @else
                            {{ $jadwal->guru?->name ?? '—' }}
                        @endif
                    </td>
                    <td class="px-4 py-3 text-slate-500">{{ $jadwal->ruang ?? '—' }}</td>
                    <td class="px-4 py-3">
                        <div class="flex justify-end gap-1">
                            <a href="{{ route('admin.jadwal.edit', $jadwal) }}" title="Ubah"
                               class="tombol-sentuh rounded-lg p-2 text-slate-500 hover:bg-slate-100 hover:text-primary">
                                <x-heroicon-o-pencil-square class="h-5 w-5"/>
                            </a>
                            <form method="POST" action="{{ route('admin.jadwal.destroy', $jadwal) }}"
                                  onsubmit="return confirm('Hapus jadwal ini?')">
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
                <x-tabel.kosong :kolom="9" ikon="table-cells"
                                judul="Belum ada jadwal"
                                pesan="Tambahkan satu per satu, gunakan Input Massal, atau impor dari berkas CSV."
                                :aksi-url="route('admin.jadwal.create')"
                                aksi-label="Tambah Jadwal"/>
            @endforelse
        </x-tabel>

        <div class="mt-4">{{ $daftar->links() }}</div>

        <x-modal nama="impor-jadwal" judul="Impor Jadwal dari CSV / Excel">
            <form method="POST" action="{{ route('admin.jadwal.impor') }}" id="form-impor-jadwal"
                  enctype="multipart/form-data" class="space-y-4">
                @csrf
                <div class="rounded-lg bg-slate-50 p-3 text-sm text-slate-600 space-y-2">
                    <p class="font-semibold text-slate-700">Format Kolom CSV (Kompatibel SIDACHEERS):</p>
                    <code class="block text-xs font-mono text-primary font-bold bg-white p-2 rounded border border-slate-200">hari, jam_ke, nama_kelas, nama_guru, mata_pelajaran</code>
                    <div class="rounded bg-emerald-50 border border-emerald-200 p-2 text-xs text-emerald-900">
                        <span class="font-bold text-emerald-800">✨ Alur Otomatis (Schedule-First):</span>
                        Kelas, mata pelajaran, dan akun guru yang belum ada akan langsung dibuatkan oleh sistem. Akun guru baru dapat langsung login dengan kata sandi bawaan: <code class="font-mono font-bold bg-emerald-100 px-1 py-0.5 rounded text-emerald-800">12345</code>.
                    </div>
                    <p class="text-xs text-slate-500">
                        * Waktu mulai & selesai otomatis dihitung dari slot JP hari terkait. Kolom opsional: <code>guru_nip</code>, <code>ruang</code>, <code>title</code>.
                    </p>
                </div>

                <x-bidang label="Berkas CSV / Excel" nama="berkas" :wajib="true">
                    <input type="file" name="berkas" id="berkas" accept=".csv,.xlsx,.xls,.txt" required
                           class="block w-full text-sm file:mr-3 file:rounded-lg file:border-0 file:bg-slate-100 file:px-4 file:py-2 file:text-sm file:font-semibold">
                </x-bidang>
            </form>

            <x-slot:kaki>
                <x-tombol tipe="button" gaya="halus" @click="$dispatch('tutup-modal', 'impor-jadwal')">Batal</x-tombol>
                <x-tombol gaya="aksen" ikon="arrow-up-tray" form="form-impor-jadwal">Impor Sekarang</x-tombol>
            </x-slot:kaki>
        </x-modal>

        {{-- Modal Konfirmasi Reset Total --}}
        <x-modal nama="reset-jadwal" judul="Reset Jadwal & Data Terintegrasi">
            <form method="POST" action="{{ route('admin.jadwal.reset') }}" id="form-reset-jadwal" class="space-y-4">
                @csrf
                <div class="rounded-xl border border-rose-200 bg-rose-50 p-4 text-sm text-rose-950 space-y-3">
                    <div class="flex items-start gap-3">
                        <x-heroicon-o-exclamation-triangle class="h-6 w-6 text-rose-600 shrink-0 mt-0.5"/>
                        <div>
                            <p class="font-bold text-rose-900 text-sm">Peringatan Tindakan Permanen!</p>
                            <p class="text-xs text-rose-800 mt-1 leading-relaxed">
                                Fitur ini digunakan jika terjadi kesalahan berkas atau Anda ingin mengunggah jadwal pelajaran baru dari awal. 
                                Data jadwal pada tahun ajaran aktif akan <strong>dihapus secara permanen</strong>:
                            </p>
                        </div>
                    </div>

                    <ul class="list-disc list-inside text-xs text-rose-900/90 space-y-1 pl-2 font-medium">
                        <li>Seluruh Jadwal Pelajaran & Agenda Bersama Sekolah</li>
                        <li>Seluruh Agenda Mengajar & Rekap Presensi Siswa yang terkait dengan jadwal ini</li>
                    </ul>

                    <div class="rounded-lg bg-white/80 p-2.5 text-xs text-emerald-800 border border-emerald-200 font-semibold flex items-center gap-2">
                        <x-heroicon-o-shield-check class="h-5 w-5 text-emerald-600 shrink-0"/>
                        <span>Data Kelas, Siswa, Guru, dan Mata Pelajaran tetap aman dan TIDAK akan terhapus.</span>
                    </div>
                </div>

                <p class="text-xs text-slate-500">
                    Setelah reset selesai, Anda dapat langsung mengunggah berkas jadwal baru via <strong>Impor CSV</strong>.
                </p>
            </form>

            <x-slot:kaki>
                <x-tombol tipe="button" gaya="halus" @click="$dispatch('tutup-modal', 'reset-jadwal')">Batal</x-tombol>
                <button type="submit" form="form-reset-jadwal"
                        class="inline-flex items-center gap-1.5 rounded-lg bg-rose-600 px-4 py-2 text-xs font-bold text-white shadow-sm hover:bg-rose-700 transition-all">
                    <x-heroicon-o-trash class="h-4 w-4"/>
                    Ya, Reset Jadwal Pelajaran
                </button>
            </x-slot:kaki>
        </x-modal>

        {{-- Modal Pemilihan Mode Jadwal --}}
        <x-modal nama="modal-mode-jadwal" judul="Pengaturan Mode Jadwal Pelajaran">
            <form method="POST" action="{{ route('admin.jadwal.ubah-mode') }}" id="form-ubah-mode" class="space-y-4">
                @csrf
                <div class="rounded-xl border border-sky-200 bg-sky-50 p-4 text-xs text-sky-950 space-y-2">
                    <div class="flex items-center gap-2 font-bold text-sky-900 text-sm">
                        <x-heroicon-o-adjustments-horizontal class="h-5 w-5 text-sky-600 shrink-0"/>
                        Penyesuaian Waktu Fleksibel Multi-Mode
                    </div>
                    <p class="leading-relaxed text-sky-800">
                        Pilih mode operasional jadwal sekolah. Ketika mode diubah, jam pelajaran di akun guru, beranda sekolah, jadwal kelas, dan cetak jadwal otomatis menyesuaikan slot waktu mode tersebut.
                    </p>
                    <p class="text-[11px] text-emerald-800 font-semibold flex items-center gap-1.5">
                        <x-heroicon-o-check-badge class="h-4 w-4 text-emerald-600 shrink-0"/>
                        Presensi siswa dan agenda mengajar historis tetap aman & tidak berubah.
                    </p>
                </div>

                <div class="space-y-3">
                    @foreach ($daftarMode as $keyMode => $mode)
                        <label class="block cursor-pointer rounded-xl border p-4 transition-all {{ $modeAktif === $keyMode ? 'border-primary bg-primary/5 ring-1 ring-primary' : 'border-slate-200 hover:border-slate-300 hover:bg-slate-50/50' }}">
                            <div class="flex items-start gap-3">
                                <input type="radio" name="mode" value="{{ $keyMode }}" {{ $modeAktif === $keyMode ? 'checked' : '' }}
                                       class="mt-1 text-primary focus:ring-primary">
                                <div class="flex-1">
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center gap-2">
                                            @if ($keyMode === 'ramadhan')
                                                <span class="text-lg">🌙</span>
                                            @elseif ($keyMode === 'khusus')
                                                <x-heroicon-o-academic-cap class="h-5 w-5 text-amber-600"/>
                                            @else
                                                <x-heroicon-o-clock class="h-5 w-5 text-slate-500"/>
                                            @endif
                                            <span class="font-bold text-sm text-slate-900">{{ $mode['label'] }}</span>
                                        </div>
                                        @if ($modeAktif === $keyMode)
                                            <span class="rounded-full bg-primary/10 px-2 py-0.5 text-[11px] font-bold text-primary">Sedang Aktif</span>
                                        @endif
                                    </div>
                                    <p class="mt-1 text-xs text-slate-600 leading-relaxed">{{ $mode['deskripsi'] }}</p>

                                    @if ($keyMode === 'ramadhan')
                                        <div class="mt-2.5 rounded-lg border border-emerald-200 bg-emerald-50/70 p-2.5 text-[11px] text-emerald-900 space-y-1">
                                            <p class="font-bold text-emerald-950">Contoh Waktu Mode Ramadhan:</p>
                                            <div class="grid grid-cols-2 gap-2 text-slate-700 font-mono">
                                                <div>• Senin JP 1: 08:00 – 08:30</div>
                                                <div>• Senin JP 8: 11:50 – 12:20</div>
                                                <div>• Sel-Kamis JP 1: 07:30 – 08:00</div>
                                                <div>• Sel-Kamis JP 10: 12:20 – 12:50</div>
                                            </div>
                                        </div>
                                    @elseif ($keyMode === 'reguler')
                                        <div class="mt-2.5 rounded-lg border border-slate-200 bg-slate-50 p-2.5 text-[11px] text-slate-700 space-y-1">
                                            <p class="font-bold text-slate-800">Waktu Reguler Sekolah:</p>
                                            <div class="grid grid-cols-2 gap-2 font-mono text-slate-600">
                                                <div>• Senin: 08:10 – 15:00</div>
                                                <div>• Sel-Kamis: 07:00 – 15:00</div>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </label>
                    @endforeach
                </div>
            </form>

            <x-slot:kaki>
                <x-tombol tipe="button" gaya="halus" @click="$dispatch('tutup-modal', 'modal-mode-jadwal')">Batal</x-tombol>
                <x-tombol gaya="aksen" ikon="check" form="form-ubah-mode">Terapkan Mode Jadwal</x-tombol>
            </x-slot:kaki>
        </x-modal>
    </div>
@endsection
