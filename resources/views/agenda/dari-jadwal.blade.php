@extends('layouts.app')
@section('judul', 'Isi Agenda Mengajar')

@php
    use App\Support\Tanggal;
    use Illuminate\Support\Str;
@endphp

@section('konten')
    <x-kepala-halaman judul="Isi Agenda Mengajar"
                      keterangan="Pilih sesi kelas sesuai jadwal Anda, lalu catat agenda pembelajaran per kelas.">
        <x-slot:aksi>
            <x-tombol gaya="halus" ikon="arrow-left" :href="route('agenda.index')">Daftar Agenda</x-tombol>
        </x-slot:aksi>
    </x-kepala-halaman>

    {{-- Filter & Navigasi Tanggal --}}
    <div class="kartu mb-6 p-4">
        <form method="GET" action="{{ route('agenda.dari-jadwal') }}" class="flex flex-wrap items-center justify-between gap-3">
            <div class="flex flex-wrap items-center gap-2">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Tanggal Pertemuan:</span>
                <input type="date" name="tanggal" value="{{ $tanggal->toDateString() }}" onchange="this.form.submit()"
                       class="rounded-xl border-slate-200 text-sm shadow-sm focus:border-primary focus:ring-primary">
                <button type="submit" class="rounded-xl bg-slate-900 px-3.5 py-2 text-xs font-bold text-white shadow-sm hover:bg-slate-800 transition">
                    Tampilkan
                </button>
                <a href="{{ route('agenda.dari-jadwal', ['tanggal' => today()->toDateString()]) }}"
                   class="rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs font-semibold text-slate-700 shadow-sm hover:bg-slate-50 transition">
                    Hari Ini
                </a>
                <a href="{{ route('agenda.dari-jadwal', ['tanggal' => $tanggal->copy()->subDay()->toDateString()]) }}"
                   title="Hari Sebelumnya"
                   class="rounded-xl border border-slate-200 bg-white p-2 text-slate-500 shadow-sm hover:bg-slate-50 transition">
                    <x-heroicon-o-chevron-left class="h-4 w-4"/>
                </a>
                <a href="{{ route('agenda.dari-jadwal', ['tanggal' => $tanggal->copy()->addDay()->toDateString()]) }}"
                   title="Hari Berikutnya"
                   class="rounded-xl border border-slate-200 bg-white p-2 text-slate-500 shadow-sm hover:bg-slate-50 transition">
                    <x-heroicon-o-chevron-right class="h-4 w-4"/>
                </a>
            </div>

            <div class="flex items-center gap-2 text-sm text-slate-600">
                <x-heroicon-o-calendar class="h-4 w-4 text-slate-400"/>
                <span>Hari <strong>{{ $hari?->label() ?? '—' }}</strong>, {{ Tanggal::lengkap($tanggal) }}</span>
                @if (\App\Support\JamPelajaran::isModeRamadhan())
                    <span class="inline-flex items-center gap-1 rounded-full bg-emerald-100 px-2.5 py-0.5 text-[11px] font-bold text-emerald-800 border border-emerald-200">
                        🌙 Mode Ramadhan
                    </span>
                @endif
            </div>
        </form>
    </div>

    @if (! $hari)
        <div class="kartu p-10 text-center">
            <x-heroicon-o-face-smile class="mx-auto h-12 w-12 text-slate-300"/>
            <p class="mt-3 font-semibold text-slate-700">Tanggal yang dipilih jatuh pada hari Minggu</p>
            <p class="text-sm text-slate-500">Tidak ada jadwal mengajar pada hari Minggu.</p>
        </div>
    @elseif ($sesiList->isEmpty())
        <div class="kartu p-10 text-center">
            <x-heroicon-o-calendar-days class="mx-auto h-12 w-12 text-slate-300"/>
            <p class="mt-3 font-semibold text-slate-700">Tidak ada jadwal mengajar pada hari {{ $hari->label() }}</p>
            <p class="text-sm text-slate-500">Silakan pilih tanggal lain yang memiliki jadwal mengajar.</p>
        </div>
    @else
        {{-- 1. DAFTAR SESI JADWAL HARIAN (PILIH KELAS) --}}
        <div class="mb-6">
            <div class="mb-3 flex flex-wrap items-center justify-between gap-2">
                <div>
                    <h3 class="font-bold text-slate-800 text-sm uppercase tracking-wider">
                        Sesi Mengajar Hari {{ $hari->label() }} — Pilih Kelas
                    </h3>
                    <p class="text-xs text-slate-500">
                        Klik kelas yang ingin Anda catat agendanya. Pengisian dilakukan per kelas sesuai jadwal.
                    </p>
                </div>
                <span class="text-xs font-semibold text-slate-500">
                    {{ $sesiList->count() }} Sesi Terjadwal ({{ $jadwals->count() }} JP)
                </span>
            </div>

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($sesiList as $sesi)
                    @php
                        $isAktif = $sesiAktif && $sesiAktif->id === $sesi->id;
                        $warnaK = $sesi->warna_kelas;
                        $warnaM = $sesi->warna_mapel;
                        $sudah = $sesi->agenda;
                    @endphp

                    <a href="{{ route('agenda.dari-jadwal', ['tanggal' => $tanggal->toDateString(), 'jadwal_id' => $sesi->primary_schedule_id]) }}"
                       @class([
                           'group relative block rounded-2xl p-4 sm:p-5 transition-all duration-200',
                           'shadow-md scale-[1.02] ring-2' => $isAktif,
                           'hover:shadow-md hover:scale-[1.01] hover:brightness-[0.98]' => ! $isAktif,
                       ])
                       style="
                           background-color: {{ $warnaK['bg_card'] }};
                           border: 1.5px solid {{ $isAktif ? $warnaK['accent'] : $warnaK['border'] }};
                           border-left: 6px solid {{ $warnaK['accent'] }};
                           @if ($isAktif) --tw-ring-color: {{ $warnaK['accent'] }}; @endif
                       ">
                        {{-- Baris Atas: JP, Kelas, Penanda Berlangsung --}}
                        <div class="flex items-start justify-between gap-2">
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="inline-flex items-center justify-center rounded-xl px-2.5 py-1 text-xs font-black shadow-xs"
                                      style="background-color: {{ $warnaK['jp_bg'] }}; color: {{ $warnaK['jp_text'] }};">
                                    {{ $sesi->label_jp }}
                                </span>
                                <span class="inline-flex items-center gap-1.5 rounded-xl px-3 py-1 text-base font-black tracking-tight shadow-2xs"
                                      style="background-color: {{ $warnaK['badge_bg'] }}; color: {{ $warnaK['badge_text'] }}; border: 1.5px solid {{ $warnaK['border'] }};">
                                    <span class="h-2 w-2 rounded-full shrink-0 shadow-xs" style="background-color: {{ $warnaK['accent'] }};"></span>
                                    <strong>{{ $sesi->kelas_tampilan }}</strong>
                                </span>
                            </div>

                            @if ($sesi->first_schedule->sedangBerlangsung())
                                <span class="inline-flex items-center gap-1 rounded-full px-2.5 py-0.5 text-[10px] font-black uppercase tracking-wider text-white shadow-xs animate-pulse"
                                      style="background-color: {{ $warnaK['accent'] }};">
                                    BERLANGSUNG
                                </span>
                            @endif
                        </div>

                        {{-- Baris Tengah: Mapel & Jam --}}
                        <div class="mt-3 flex flex-wrap items-center gap-2">
                            <span class="inline-flex items-center gap-1.5 rounded-full px-3 py-1 text-xs font-bold shadow-2xs bg-white/95 border border-slate-200/80 text-slate-800">
                                <span class="h-2 w-2 rounded-full shrink-0 shadow-xs" style="background-color: {{ $warnaM['dot'] }};"></span>
                                <span class="truncate max-w-[200px]">{{ $sesi->nama_tampilan }}</span>
                            </span>
                        </div>

                        <p class="mt-2 text-xs font-bold text-slate-600 flex items-center gap-1.5">
                            <x-heroicon-o-clock class="h-3.5 w-3.5 text-slate-500 shrink-0"/>
                            <span>{{ $sesi->jam }} WIB</span>
                            @if ($sesi->ruang)
                                <span class="text-slate-400">·</span>
                                <span class="font-bold text-slate-700">Ruang {{ $sesi->ruang }}</span>
                            @endif
                        </p>

                        {{-- Baris Bawah: Status Agenda & Indikator Aksi --}}
                        <div class="mt-4 flex items-center justify-between border-t pt-3 text-xs"
                             style="border-color: {{ $warnaK['border'] }};">
                            @if ($sudah)
                                <span class="inline-flex items-center gap-1.5 font-bold text-emerald-800 bg-white/90 border border-emerald-300 rounded-full px-3 py-1 shadow-2xs text-[11px]">
                                    <x-heroicon-s-check-circle class="h-3.5 w-3.5 text-emerald-600"/>
                                    Pertemuan ke-{{ $sudah->pertemuan_ke }}
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1.5 font-bold text-amber-900 bg-white/90 border border-amber-300 rounded-full px-3 py-1 shadow-2xs text-[11px]">
                                    <x-heroicon-s-exclamation-circle class="h-3.5 w-3.5 text-amber-500"/>
                                    Belum Diisi
                                </span>
                            @endif

                            @if ($isAktif)
                                <span class="inline-flex items-center gap-1 rounded-full px-3 py-1 text-xs font-black text-white shadow-xs"
                                      style="background-color: {{ $warnaK['jp_bg'] }};">
                                    <x-heroicon-s-check class="h-3.5 w-3.5"/>
                                    Aktif Dipilih
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1 text-xs font-extrabold transition group-hover:translate-x-0.5"
                                      style="color: {{ $warnaK['jp_bg'] }};">
                                    Pilih Kelas Ini →
                                </span>
                            @endif
                        </div>
                    </a>
                @endforeach
            </div>
        </div>

        {{-- 2. FORMULIR AGENDA KHUSUS KELAS TERPILIH --}}
        @if ($sesiAktif)
            @php
                $sudah = $sesiAktif->agenda;
                $warnaAktifK = $sesiAktif->warna_kelas;
                $warnaAktifM = $sesiAktif->warna_mapel;
            @endphp

            <div class="kartu p-5 sm:p-6 shadow-sm" style="border-top: 6px solid {{ $warnaAktifK['accent'] }};">
                {{-- Header Kelas Terpilih --}}
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-5 border-b border-slate-100 mb-5">
                    <div>
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="inline-flex items-center justify-center rounded-xl px-2.5 py-1 text-xs font-black shadow-xs"
                                  style="background-color: {{ $warnaAktifK['jp_bg'] }}; color: {{ $warnaAktifK['jp_text'] }};">
                                {{ $sesiAktif->label_jp }}
                            </span>
                            <span class="inline-flex items-center gap-1.5 rounded-xl px-3 py-1 text-base font-black tracking-tight shadow-2xs"
                                  style="background-color: {{ $warnaAktifK['badge_bg'] }}; color: {{ $warnaAktifK['badge_text'] }}; border: 1.5px solid {{ $warnaAktifK['border'] }};">
                                <span class="h-2 w-2 rounded-full shrink-0 shadow-xs" style="background-color: {{ $warnaAktifK['accent'] }};"></span>
                                <strong>{{ $sesiAktif->kelas_tampilan }}</strong>
                            </span>
                            <h2 class="text-lg sm:text-xl font-black text-slate-900 tracking-tight">
                                — {{ $sesiAktif->nama_tampilan }}
                            </h2>
                        </div>
                        <p class="text-xs text-slate-500 mt-1.5 flex flex-wrap items-center gap-1.5">
                            <span>Pukul <strong>{{ $sesiAktif->jam }} WIB</strong></span>
                            @if ($sesiAktif->ruang)
                                <span>· Ruang <strong>{{ $sesiAktif->ruang }}</strong></span>
                            @endif
                            <span>· {{ Tanggal::lengkap($tanggal) }}</span>
                        </p>
                    </div>

                    <div class="flex items-center gap-2">
                        <span class="inline-flex items-center gap-1.5 rounded-xl px-3.5 py-1.5 text-xs font-black shadow-2xs"
                              style="background-color: {{ $warnaAktifK['badge_bg'] }}; color: {{ $warnaAktifK['badge_text'] }}; border: 1.5px solid {{ $warnaAktifK['border'] }};">
                            <x-heroicon-o-bookmark class="h-4 w-4" style="color: {{ $warnaAktifK['accent'] }};"/>
                            Pertemuan ke-{{ $sudah ? $sudah->pertemuan_ke : $pertemuanKe }}
                        </span>
                    </div>
                </div>

                {{-- Alert jika agenda sudah pernah disimpan --}}
                @if ($sudah)
                    <div class="mb-5 flex flex-col sm:flex-row sm:items-center justify-between gap-3 rounded-2xl border border-emerald-200 bg-emerald-50/70 p-4">
                        <div class="flex items-start sm:items-center gap-3">
                            <x-heroicon-s-check-circle class="h-6 w-6 text-emerald-600 shrink-0 mt-0.5 sm:mt-0"/>
                            <div>
                                <p class="text-sm font-bold text-emerald-900">
                                    Agenda kelas ini sudah tersimpan (Pertemuan ke-{{ $sudah->pertemuan_ke }})
                                </p>
                                <p class="text-xs text-emerald-700 mt-0.5">
                                    Status: <strong>{{ $sudah->status->label() }}</strong>
                                    @if ($sudah->presensis_count > 0)
                                        · Presensi siswa sudah terisi ({{ $sudah->presensis_count }} siswa)
                                    @else
                                        · <span class="font-bold text-amber-800">Presensi siswa belum diisi</span>
                                    @endif
                                </p>
                            </div>
                        </div>

                        <div class="flex items-center gap-2 self-end sm:self-center">
                            <x-tombol gaya="aksen" :href="route('presensi.isi', $sudah)" ikon="user-group">
                                {{ $sudah->presensis_count > 0 ? 'Lihat / Ubah Presensi' : 'Isi Presensi Sekarang' }}
                            </x-tombol>
                        </div>
                    </div>
                @endif

                {{-- Improvisasi Pintar: Pengingat Pertemuan Sebelumnya --}}
                @if ($agendaSebelumnya)
                    <div class="mb-5 rounded-2xl border border-sky-100 bg-sky-50/60 p-4 text-xs text-slate-700">
                        <div class="flex flex-wrap items-center justify-between gap-2 mb-2">
                            <span class="inline-flex items-center gap-1.5 font-bold text-sky-950 uppercase tracking-wider text-[11px]">
                                <x-heroicon-o-light-bulb class="h-4 w-4 text-sky-600"/>
                                Materi Pertemuan Sebelumnya (Pertemuan ke-{{ $agendaSebelumnya->pertemuan_ke }}, {{ Tanggal::lengkap($agendaSebelumnya->tanggal) }}):
                            </span>
                            <button type="button"
                                    onclick="document.getElementById('judul_materi').value = {{ Js::from($agendaSebelumnya->judul_materi) }}; document.getElementById('judul_materi').focus();"
                                    class="font-bold text-sky-700 hover:text-sky-900 hover:underline">
                                Gunakan Judul Ini →
                            </button>
                        </div>
                        <p class="font-bold text-slate-900 text-sm">
                            "{{ $agendaSebelumnya->judul_materi }}"
                        </p>
                        <div class="mt-1 flex flex-wrap items-center gap-3 text-slate-500 text-xs">
                            @if ($agendaSebelumnya->bab)
                                <span>Bab: <strong>{{ $agendaSebelumnya->bab->judul }}</strong></span>
                            @endif
                            @if ($agendaSebelumnya->metode)
                                <span>Metode: <strong>{{ $agendaSebelumnya->metode }}</strong></span>
                            @endif
                            @if ($agendaSebelumnya->uraian_kegiatan)
                                <span class="italic max-w-lg truncate">"{{ Str::limit($agendaSebelumnya->uraian_kegiatan, 100) }}"</span>
                            @endif
                        </div>
                    </div>
                @endif

                {{-- Formulir Input Agenda Kelas --}}
                <form method="POST" action="{{ route('agenda.simpan-massal') }}" id="formAgendaKelas">
                    @csrf
                    <input type="hidden" name="tanggal" value="{{ $tanggal->toDateString() }}">
                    <input type="hidden" name="jadwal_id" value="{{ $sesiAktif->primary_schedule_id }}">
                    <input type="hidden" name="lanjut_presensi" id="inputLanjutPresensi" value="1">

                    <div class="grid gap-4 md:grid-cols-2">
                        {{-- Judul Materi --}}
                        <x-bidang label="Judul Materi / Topik Pembelajaran" nama="judul_materi" :wajib="true">
                            <input type="text" id="judul_materi" name="judul_materi"
                                   list="datalist-bab-tp"
                                   value="{{ old('judul_materi', $sudah?->judul_materi) }}"
                                   placeholder="mis. Menulis Teks Editorial"
                                   required
                                   class="block w-full rounded-xl border-slate-300 text-sm shadow-sm focus:border-primary focus:ring-primary">
                            <datalist id="datalist-bab-tp">
                                @foreach ($babs as $bab)
                                    <option value="{{ $bab->judul }}"></option>
                                    @foreach ($bab->tujuanPembelajarans as $tp)
                                        <option value="{{ $tp->deskripsi }}"></option>
                                    @endforeach
                                @endforeach
                            </datalist>
                        </x-bidang>

                        {{-- Bab / Lingkup Materi --}}
                        <x-bidang label="Bab / Lingkup Materi" nama="bab_id">
                            <select id="bab_id" name="bab_id"
                                    class="block w-full rounded-xl border-slate-300 text-sm shadow-sm focus:border-primary focus:ring-primary">
                                <option value="">— Tidak ditautkan / Pilih Bab —</option>
                                @foreach ($babs as $bab)
                                    <option value="{{ $bab->id }}" @selected(($sudah?->bab_id ?? old('bab_id')) === $bab->id)>
                                        {{ $bab->kode }} — {{ $bab->judul }}
                                    </option>
                                @endforeach
                            </select>
                        </x-bidang>

                        {{-- Metode Pembelajaran --}}
                        <x-bidang label="Metode Pembelajaran" nama="metode">
                            <input type="text" id="metode" name="metode"
                                   list="datalist-metode"
                                   value="{{ old('metode', $sudah?->metode) }}"
                                   placeholder="mis. Diskusi kelompok, Praktik, dll."
                                   class="block w-full rounded-xl border-slate-300 text-sm shadow-sm focus:border-primary focus:ring-primary">
                            <datalist id="datalist-metode">
                                <option value="Ceramah interaktif"></option>
                                <option value="Diskusi kelompok & presentasi"></option>
                                <option value="Praktik / Demonstrasi laboratorium"></option>
                                <option value="Problem Based Learning (PBL)"></option>
                                <option value="Project Based Learning (PjBL)"></option>
                                <option value="Penugasan mandiri & latihan soal"></option>
                                <option value="Tanya jawab & refleksi"></option>
                            </datalist>
                        </x-bidang>

                        {{-- Status Pertemuan --}}
                        <x-bidang label="Status Pertemuan" nama="status" :wajib="true">
                            <select id="status" name="status" required
                                    class="block w-full rounded-xl border-slate-300 text-sm shadow-sm focus:border-primary focus:ring-primary">
                                @foreach ($statusList as $nilai => $label)
                                    <option value="{{ $nilai }}"
                                        @selected(($sudah?->status?->value ?? old('status', 'terlaksana')) === $nilai)>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                        </x-bidang>

                        {{-- Uraian Kegiatan --}}
                        <x-bidang label="Uraian Kegiatan Pembelajaran" nama="uraian_kegiatan" class="md:col-span-2">
                            <textarea id="uraian_kegiatan" name="uraian_kegiatan" rows="3"
                                      placeholder="Ringkasan kegiatan pembelajaran, materi inti yang dibahas, atau catatan khusus kelas ini"
                                      class="block w-full rounded-xl border-slate-300 text-sm shadow-sm focus:border-primary focus:ring-primary">{{ old('uraian_kegiatan', $sudah?->uraian_kegiatan) }}</textarea>
                        </x-bidang>
                    </div>

                    {{-- Tombol Aksi --}}
                    <div class="mt-6 flex flex-wrap items-center justify-between gap-3 border-t border-slate-100 pt-5">
                        <div>
                            @if ($sudah)
                                <button type="submit" form="formHapusAgenda"
                                        class="inline-flex items-center gap-1.5 text-xs font-bold text-rose-600 hover:text-rose-800 transition">
                                    <x-heroicon-o-trash class="h-4 w-4"/>
                                    Hapus Agenda Kelas Ini
                                </button>
                            @endif
                        </div>

                        <div class="flex flex-wrap items-center gap-2.5">
                            <x-tombol gaya="halus" :href="route('agenda.index')">Batal</x-tombol>

                            <button type="submit"
                                    onclick="document.getElementById('inputLanjutPresensi').value = '0'"
                                    class="inline-flex items-center gap-1.5 rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 shadow-sm hover:bg-slate-50 transition">
                                <x-heroicon-o-check class="h-4 w-4 text-slate-500"/>
                                Simpan Agenda Saja
                            </button>

                            <button type="submit"
                                    onclick="document.getElementById('inputLanjutPresensi').value = '1'"
                                    class="inline-flex items-center gap-2 rounded-xl bg-accent px-5 py-2.5 text-sm font-bold text-white shadow-sm hover:bg-accent-600 transition">
                                <x-heroicon-o-arrow-right-circle class="h-5 w-5"/>
                                Simpan & Lanjut Presensi
                            </button>
                        </div>
                    </div>
                </form>

                @if ($sudah)
                    <form id="formHapusAgenda" method="POST" action="{{ route('agenda.destroy', $sudah) }}"
                          onsubmit="return confirm('Hapus agenda kelas {{ $sesiAktif->kelas_tampilan }} beserta presensinya? Tindakan ini tidak dapat dibatalkan.')">
                        @csrf
                        @method('DELETE')
                    </form>
                @endif
            </div>
        @endif
    @endif
@endsection
