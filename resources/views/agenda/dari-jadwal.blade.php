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
    <div class="kartu mb-6 p-4 sm:p-5" style="border-radius: 16px;">
        <form method="GET" action="{{ route('agenda.dari-jadwal') }}" class="flex flex-wrap items-center justify-between gap-3">
            <div class="flex flex-wrap items-center gap-2">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Tanggal Pertemuan:</span>
                <input type="date" name="tanggal" value="{{ $tanggal->toDateString() }}" onchange="this.form.submit()"
                       style="border-radius: 11px; padding: 7px 12px; font-weight: 600;"
                       class="border-slate-300 text-sm text-slate-800 shadow-sm focus:border-primary focus:ring-primary">
                <button type="submit" class="btn-filter-dark">
                    <x-heroicon-o-magnifying-glass class="h-3.5 w-3.5"/>
                    <span>Tampilkan</span>
                </button>
                <a href="{{ route('agenda.dari-jadwal', ['tanggal' => today()->toDateString()]) }}"
                   class="btn-filter-light">
                    Hari Ini
                </a>
                <div class="flex items-center gap-1">
                    <a href="{{ route('agenda.dari-jadwal', ['tanggal' => $tanggal->copy()->subDay()->toDateString()]) }}"
                       title="Hari Sebelumnya"
                       class="btn-filter-light" style="padding: 8px 9px !important;">
                        <x-heroicon-o-chevron-left class="h-4 w-4"/>
                    </a>
                    <a href="{{ route('agenda.dari-jadwal', ['tanggal' => $tanggal->copy()->addDay()->toDateString()]) }}"
                       title="Hari Berikutnya"
                       class="btn-filter-light" style="padding: 8px 9px !important;">
                        <x-heroicon-o-chevron-right class="h-4 w-4"/>
                    </a>
                </div>
            </div>

            <div class="flex items-center gap-2 text-xs sm:text-sm text-slate-600">
                <span class="inline-flex items-center justify-center rounded-lg bg-primary/10 p-1 text-primary">
                    <x-heroicon-o-calendar class="h-4 w-4"/>
                </span>
                <span>Hari <strong class="text-slate-900">{{ $hari?->label() ?? '—' }}</strong>, {{ Tanggal::lengkap($tanggal) }}</span>
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
            <div class="sesi-section-header">
                <div class="sesi-section-title-group">
                    <span class="sesi-section-icon">
                        <x-heroicon-o-clipboard-document-list class="h-5 w-5"/>
                    </span>
                    <div>
                        <h3 class="text-sm font-extrabold uppercase tracking-wider text-slate-800" style="line-height: 1.2;">
                            Sesi Pelajaran Hari {{ $hari->label() }} — Pilih Kelas
                        </h3>
                        <p class="text-xs text-slate-500 mt-0.5">
                            Klik kartu sesi kelas yang ingin Anda catat agendanya. Pengisian dilakukan per kelas sesuai jadwal.
                        </p>
                    </div>
                </div>
                <span class="inline-flex items-center gap-1.5 rounded-full bg-white px-3 py-1 text-xs font-bold text-slate-700 border border-slate-200 shadow-sm">
                    <span class="h-2 w-2 rounded-full bg-primary"></span>
                    <span>{{ $sesiList->count() }} Sesi Terjadwal ({{ $jadwals->count() }} JP)</span>
                </span>
            </div>

            <div class="sesi-cards-grid">
                @foreach ($sesiList as $sesi)
                    @php
                        $isAktif = $sesiAktif && $sesiAktif->id === $sesi->id;
                        $warnaK = $sesi->warna_kelas;
                        $warnaM = $sesi->warna_mapel;
                        $sudah = $sesi->agenda;
                        $jpSingkat = $sesi->label_jp_singkat ?? $sesi->label_jp;
                    @endphp

                    <a href="{{ route('agenda.dari-jadwal', ['tanggal' => $tanggal->toDateString(), 'jadwal_id' => $sesi->primary_schedule_id]) }}"
                       class="sesi-card {{ $isAktif ? 'is-active' : '' }}"
                       style="--sesi-accent: {{ $warnaK['accent'] }};">
                        <div>
                            {{-- Baris Atas: JP, Durasi & Status Agenda --}}
                            <div class="flex items-center justify-between gap-2">
                                <div class="flex items-center gap-1.5">
                                    <span class="inline-flex items-center gap-1 font-extrabold text-xs"
                                          style="padding: 4px 10px; border-radius: 9px; white-space: nowrap; {{ $isAktif ? 'background-color: rgba(255,255,255,0.2); color: #ffffff; border: 1px solid rgba(255,255,255,0.32);' : 'background-color: '.$warnaK['jp_bg'].'; color: #ffffff; box-shadow: 0 2px 5px rgba(0,0,0,0.1);' }}">
                                        <x-heroicon-o-clock class="h-3.5 w-3.5 shrink-0"/>
                                        <span>{{ $jpSingkat }}</span>
                                    </span>
                                    <span class="inline-flex items-center font-bold text-[11px]"
                                          style="padding: 3px 8px; border-radius: 8px; white-space: nowrap; {{ $isAktif ? 'background-color: rgba(255,255,255,0.12); color: #e2e8f0; border: 1px solid rgba(255,255,255,0.2);' : 'background-color: '.$warnaK['badge_bg'].'; color: '.$warnaK['badge_text'].'; border: 1px solid '.$warnaK['border'].';' }}">
                                        {{ $sesi->total_jp }} JP
                                    </span>
                                </div>

                                @if ($sudah)
                                    <span class="inline-flex items-center gap-1 rounded-full text-[11px] font-extrabold"
                                          style="padding: 3.5px 10px; white-space: nowrap; {{ $isAktif ? 'background-color: #10b981; color: #ffffff; border: 1px solid #34d399; box-shadow: 0 2px 8px rgba(16,185,129,0.35);' : 'background-color: #ecfdf5; color: #065f46; border: 1px solid #a7f3d0;' }}">
                                        <x-heroicon-s-check-circle class="h-3.5 w-3.5 shrink-0" style="{{ $isAktif ? 'color: #ffffff;' : 'color: #059669;' }}"/>
                                        <span>Pertemuan ke-{{ $sudah->pertemuan_ke }}</span>
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 rounded-full text-[11px] font-extrabold"
                                          style="padding: 3.5px 10px; white-space: nowrap; {{ $isAktif ? 'background-color: #f59e0b; color: #ffffff; border: 1px solid #fbbf24; box-shadow: 0 2px 8px rgba(245,158,11,0.35);' : 'background-color: #fffbeb; color: #92400e; border: 1px solid #fde68a;' }}">
                                        <span class="h-2 w-2 rounded-full shrink-0" style="background-color: {{ $isAktif ? '#ffffff' : '#f59e0b' }};"></span>
                                        <span>Belum Diisi</span>
                                    </span>
                                @endif
                            </div>

                            {{-- Baris Tengah: Avatar Sesi + Kelas & Mapel --}}
                            <div class="mt-3 flex items-center gap-3" style="margin-top: 14px;">
                                <div class="flex flex-col items-center justify-center shrink-0"
                                     style="width: 46px; height: 46px; min-width: 46px; border-radius: 13px; {{ $isAktif ? 'background-color: rgba(255,255,255,0.14); color: #ffffff; border: 1.5px solid rgba(255,255,255,0.28);' : 'background-color: '.$warnaK['bg_card'].'; color: '.$warnaK['badge_text'].'; border: 1.5px solid '.$warnaK['border'].';' }}">
                                    <span style="font-size: 8px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.08em; opacity: 0.8; line-height: 1;">SESI</span>
                                    <span style="font-size: 16px; font-weight: 900; line-height: 1.15; margin-top: 2px;">#{{ $sesi->urutan }}</span>
                                </div>

                                <div class="min-w-0 flex-1">
                                    <div class="flex items-center gap-2">
                                        <span class="truncate font-extrabold"
                                              style="font-size: 18px; line-height: 1.2; letter-spacing: -0.02em; color: {{ $isAktif ? '#ffffff' : '#0f172a' }};">
                                            {{ $sesi->kelas_tampilan }}
                                        </span>
                                        <span class="h-2 w-2 rounded-full shrink-0"
                                              style="background-color: {{ $warnaK['accent'] }};"></span>
                                        @if ($sesi->first_schedule->sedangBerlangsung())
                                            <span class="inline-flex items-center rounded-md px-1.5 py-0.5 text-[9px] font-black uppercase tracking-wider"
                                                  style="background-color: #ef4444; color: #ffffff;">
                                                LIVE
                                            </span>
                                        @endif
                                    </div>
                                    <p class="truncate text-xs font-semibold mt-1 flex items-center gap-1.5"
                                       style="color: {{ $isAktif ? 'rgba(255,255,255,0.85)' : '#475569' }};">
                                        <span class="h-1.5 w-1.5 rounded-full shrink-0" style="background-color: {{ $isAktif ? '#38bdf8' : $warnaM['dot'] }};"></span>
                                        <span class="truncate">{{ $sesi->nama_tampilan }}</span>
                                    </p>
                                </div>
                            </div>

                            {{-- Strip Info Waktu & Ruang --}}
                            <div class="flex items-center justify-between gap-2 text-xs font-bold"
                                 style="margin-top: 14px; padding: 8px 12px; border-radius: 11px; {{ $isAktif ? 'background-color: rgba(255,255,255,0.11); border: 1px solid rgba(255,255,255,0.18); color: #f8fafc;' : 'background-color: #f8fafc; border: 1px solid #e2e8f0; color: #334155;' }}">
                                <div class="flex items-center gap-1.5 truncate">
                                    <x-heroicon-o-clock class="h-3.5 w-3.5 shrink-0" style="color: {{ $isAktif ? '#93c5fd' : '#64748b' }};"/>
                                    <span>{{ $sesi->jam }} WIB</span>
                                </div>
                                @if ($sesi->ruang)
                                    <div class="flex items-center gap-1 shrink-0">
                                        <span>Ruang {{ $sesi->ruang }}</span>
                                    </div>
                                @endif
                            </div>
                        </div>

                        {{-- Baris Bawah: Status Agenda & Indikator Aksi --}}
                        <div class="flex items-center justify-between gap-2"
                             style="margin-top: 12px; padding-top: 10px; border-top: 1px dashed {{ $isAktif ? 'rgba(255,255,255,0.22)' : '#e2e8f0' }}; font-size: 11px; font-weight: 700;">
                            <span style="color: {{ $isAktif ? 'rgba(255,255,255,0.8)' : '#64748b' }};" class="truncate">
                                {{ $sudah ? Str::limit($sudah->judul_materi, 28) : 'Klik untuk isi agenda' }}
                            </span>

                            @if ($isAktif)
                                <span class="inline-flex items-center gap-1 rounded-full px-2.5 py-0.5 text-[10px] font-extrabold shrink-0"
                                      style="background-color: rgba(255,255,255,0.2); color: #ffffff; border: 1px solid rgba(255,255,255,0.3);">
                                    <span class="h-1.5 w-1.5 rounded-full" style="background-color: #34d399;"></span>
                                    <span>Aktif Dipilih</span>
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1 shrink-0 font-extrabold"
                                      style="color: {{ $warnaK['jp_bg'] }};">
                                    <span>Pilih Sesi</span>
                                    <span class="sesi-card-cta-arrow" style="display: inline-block; transition: transform 0.2s ease;">→</span>
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
