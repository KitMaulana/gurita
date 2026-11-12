@extends('layouts.app')
@section('judul', 'Isi Agenda Mengajar')

@php
    use App\Support\Tanggal;
    use Illuminate\Support\Str;
@endphp

@push('kepala')
<style>
    .sesi-section-header {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        margin-bottom: 14px;
    }
    .sesi-section-title-group {
        display: flex;
        align-items: center;
        gap: 10px;
    }
    .sesi-section-icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 36px;
        height: 36px;
        border-radius: 10px;
        background-color: #0f172a;
        color: #ffffff;
        flex-shrink: 0;
    }
    .sesi-cards-grid {
        display: grid;
        grid-template-columns: repeat(1, minmax(0, 1fr));
        gap: 14px;
    }
    @media (min-width: 640px) {
        .sesi-cards-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
    }
    @media (min-width: 1024px) {
        .sesi-cards-grid { grid-template-columns: repeat(3, minmax(0, 1fr)); }
    }
    .sesi-card {
        position: relative;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        border-radius: 14px;
        background-color: #ffffff;
        border: 1px solid #e2e8f0;
        padding: 16px 16px 13px 16px;
        text-decoration: none !important;
        overflow: hidden;
        transition: border-color 0.15s ease, box-shadow 0.15s ease;
        box-shadow: 0 1px 2px rgba(15, 23, 42, 0.04);
    }
    .sesi-card::before {
        content: "";
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 3px;
        background: var(--sesi-accent, #4f46e5);
    }
    .sesi-card:hover {
        border-color: #cbd5e1;
        box-shadow: 0 4px 12px -2px rgba(15, 23, 42, 0.07);
    }
    .sesi-card.is-active {
        background-color: #0f172a !important;
        border-color: #0f172a !important;
        color: #ffffff !important;
        box-shadow: 0 8px 20px -6px rgba(15, 23, 42, 0.25) !important;
    }
</style>
@endpush

@section('konten')
    <x-kepala-halaman judul="Isi Agenda Mengajar"
                      keterangan="Pilih sesi kelas sesuai jadwal Anda, lalu catat agenda pembelajaran per kelas.">
        <x-slot:aksi>
            <x-tombol gaya="garis" ikon="arrow-left" :href="route('agenda.index')">Daftar Agenda</x-tombol>
        </x-slot:aksi>
    </x-kepala-halaman>

    {{-- Filter & Navigasi Tanggal --}}
    <div class="kartu mb-6 p-4 sm:p-5">
        <form method="GET" action="{{ route('agenda.dari-jadwal') }}" class="flex flex-wrap items-center justify-between gap-3">
            <div class="flex flex-wrap items-center gap-2">
                <span class="text-xs font-semibold text-slate-600 whitespace-nowrap">Tanggal Pertemuan:</span>
                <input type="date" name="tanggal" value="{{ $tanggal->toDateString() }}" onchange="this.form.submit()"
                       class="rounded-lg border-slate-200 px-3 py-1.5 text-sm font-medium text-slate-800 focus:border-primary focus:ring-primary">
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
                <x-heroicon-o-calendar class="h-4 w-4 text-slate-400 shrink-0"/>
                <span>Hari <strong class="font-semibold text-slate-900">{{ $hari?->label() ?? '—' }}</strong>, {{ Tanggal::lengkap($tanggal) }}</span>
                @if (\App\Support\JamPelajaran::isModeRamadhan())
                    <span class="inline-flex items-center rounded-md bg-emerald-50 px-2 py-0.5 text-[11px] font-semibold text-emerald-700 border border-emerald-200 whitespace-nowrap">
                        Mode Ramadhan
                    </span>
                @endif
            </div>
        </form>
    </div>

    @if (! $hari)
        <div class="kartu p-10 text-center">
            <x-heroicon-o-calendar class="mx-auto h-10 w-10 text-slate-300"/>
            <p class="mt-3 font-semibold text-slate-800">Tanggal yang dipilih jatuh pada hari Minggu</p>
            <p class="mt-1 text-sm text-slate-500">Tidak ada jadwal mengajar pada hari Minggu.</p>
        </div>
    @elseif ($sesiList->isEmpty())
        <div class="kartu p-10 text-center">
            <x-heroicon-o-calendar-days class="mx-auto h-10 w-10 text-slate-300"/>
            <p class="mt-3 font-semibold text-slate-800">Tidak ada jadwal mengajar pada hari {{ $hari->label() }}</p>
            <p class="mt-1 text-sm text-slate-500">Silakan pilih tanggal lain yang memiliki jadwal mengajar.</p>
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
                        <h3 class="text-sm font-bold text-slate-900">
                            Sesi Mengajar Hari {{ $hari->label() }} — Pilih Kelas
                        </h3>
                        <p class="text-xs text-slate-500">
                            Klik kartu sesi kelas yang ingin Anda catat agendanya. Pengisian dilakukan per kelas sesuai jadwal.
                        </p>
                    </div>
                </div>
                <span class="inline-flex items-center gap-1.5 rounded-lg bg-white px-3 py-1 text-xs font-semibold text-slate-700 border border-slate-200 whitespace-nowrap">
                    {{ $sesiList->count() }} Sesi ({{ $jadwals->count() }} JP)
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
                            <div class="flex items-center justify-between gap-2 flex-nowrap">
                                <div class="flex items-center gap-1.5 flex-nowrap">
                                    <span class="inline-flex items-center gap-1 font-semibold text-xs whitespace-nowrap shrink-0 rounded-md px-2 py-0.5"
                                          style="{{ $isAktif ? 'background-color: rgba(255,255,255,0.16); color: #ffffff;' : 'background-color: '.$warnaK['jp_bg'].'; color: #ffffff;' }}">
                                        <span>{{ $jpSingkat }}</span>
                                    </span>
                                    <span class="inline-flex items-center font-semibold text-[11px] whitespace-nowrap shrink-0 rounded-md px-2 py-0.5"
                                          style="{{ $isAktif ? 'background-color: rgba(255,255,255,0.08); color: #cbd5e1;' : 'background-color: '.$warnaK['badge_bg'].'; color: '.$warnaK['badge_text'].';' }}">
                                        {{ $sesi->total_jp }} JP
                                    </span>
                                </div>

                                @if ($sudah)
                                    <span class="inline-flex items-center gap-1 rounded-md px-2 py-0.5 text-[11px] font-semibold whitespace-nowrap shrink-0"
                                          style="{{ $isAktif ? 'background-color: #059669; color: #ffffff;' : 'background-color: #ecfdf5; color: #065f46; border: 1px solid #a7f3d0;' }}">
                                        <x-heroicon-s-check-circle class="h-3.5 w-3.5 shrink-0"/>
                                        <span>Pertemuan {{ $sudah->pertemuan_ke }}</span>
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 rounded-md px-2 py-0.5 text-[11px] font-semibold whitespace-nowrap shrink-0"
                                          style="{{ $isAktif ? 'background-color: #d97706; color: #ffffff;' : 'background-color: #fffbeb; color: #92400e; border: 1px solid #fde68a;' }}">
                                        <span>Belum Diisi</span>
                                    </span>
                                @endif
                            </div>

                            {{-- Baris Tengah: Kelas & Mapel --}}
                            <div class="mt-3.5 flex items-center gap-3">
                                <div class="flex flex-col items-center justify-center shrink-0 w-11 h-11 rounded-lg"
                                     style="{{ $isAktif ? 'background-color: rgba(255,255,255,0.12); color: #ffffff;' : 'background-color: '.$warnaK['bg_card'].'; color: '.$warnaK['badge_text'].'; border: 1px solid '.$warnaK['border'].';' }}">
                                    <span class="text-[8px] font-bold uppercase opacity-75 leading-none">SESI</span>
                                    <span class="text-sm font-bold leading-tight mt-0.5">#{{ $sesi->urutan }}</span>
                                </div>

                                <div class="min-w-0 flex-1">
                                    <div class="flex items-center gap-2">
                                        <span class="truncate text-base font-bold"
                                              style="color: {{ $isAktif ? '#ffffff' : '#0f172a' }};">
                                            {{ $sesi->kelas_tampilan }}
                                        </span>
                                        @if ($sesi->first_schedule->sedangBerlangsung())
                                            <span class="rounded bg-rose-600 px-1.5 py-0.5 text-[9px] font-bold uppercase text-white shrink-0">
                                                Aktif
                                            </span>
                                        @endif
                                    </div>
                                    <p class="truncate text-xs mt-0.5"
                                       style="color: {{ $isAktif ? '#cbd5e1' : '#64748b' }};">
                                        {{ $sesi->nama_tampilan }}
                                    </p>
                                </div>
                            </div>

                            {{-- Strip Info Waktu & Ruang --}}
                            <div class="mt-3.5 flex items-center justify-between gap-2 rounded-lg px-3 py-2 text-xs font-medium"
                                 style="{{ $isAktif ? 'background-color: rgba(255,255,255,0.08); color: #e2e8f0;' : 'background-color: #f8fafc; border: 1px solid #e2e8f0; color: #475569;' }}">
                                <div class="flex items-center gap-1.5 whitespace-nowrap">
                                    <x-heroicon-o-clock class="h-3.5 w-3.5 opacity-75 shrink-0"/>
                                    <span>{{ $sesi->jam }} WIB</span>
                                </div>
                                @if ($sesi->ruang)
                                    <span class="whitespace-nowrap">Ruang {{ $sesi->ruang }}</span>
                                @endif
                            </div>
                        </div>

                        {{-- Baris Bawah --}}
                        <div class="mt-3 flex items-center justify-between gap-2 pt-2.5 text-[11px] font-medium"
                             style="border-top: 1px solid {{ $isAktif ? 'rgba(255,255,255,0.12)' : '#f1f5f9' }};">
                            <span style="color: {{ $isAktif ? '#cbd5e1' : '#64748b' }};" class="truncate">
                                {{ $sudah ? Str::limit($sudah->judul_materi, 28) : 'Klik untuk isi agenda' }}
                            </span>

                            @if ($isAktif)
                                <span class="shrink-0 font-semibold text-emerald-400 whitespace-nowrap">Dipilih</span>
                            @else
                                <span class="shrink-0 font-semibold text-primary whitespace-nowrap">Pilih →</span>
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
            @endphp

            <div class="kartu p-5 sm:p-6" style="border-top: 3px solid {{ $warnaAktifK['accent'] }};">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-4 border-b border-slate-100 mb-5">
                    <div>
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="rounded-md px-2.5 py-0.5 text-xs font-bold whitespace-nowrap"
                                  style="background-color: {{ $warnaAktifK['jp_bg'] }}; color: {{ $warnaAktifK['jp_text'] }};">
                                {{ $sesiAktif->label_jp }}
                            </span>
                            <span class="rounded-md px-2.5 py-0.5 text-sm font-bold whitespace-nowrap"
                                  style="background-color: {{ $warnaAktifK['badge_bg'] }}; color: {{ $warnaAktifK['badge_text'] }}; border: 1px solid {{ $warnaAktifK['border'] }};">
                                {{ $sesiAktif->kelas_tampilan }}
                            </span>
                            <h2 class="text-base sm:text-lg font-bold text-slate-900">
                                {{ $sesiAktif->nama_tampilan }}
                            </h2>
                        </div>
                        <p class="text-xs text-slate-500 mt-1.5 flex flex-wrap items-center gap-1.5">
                            <span>Pukul <strong class="text-slate-700">{{ $sesiAktif->jam }} WIB</strong></span>
                            @if ($sesiAktif->ruang)
                                <span>· Ruang <strong class="text-slate-700">{{ $sesiAktif->ruang }}</strong></span>
                            @endif
                            <span>· {{ Tanggal::lengkap($tanggal) }}</span>
                        </p>
                    </div>

                    <span class="inline-flex items-center gap-1.5 rounded-lg bg-slate-100 px-3 py-1.5 text-xs font-semibold text-slate-700 whitespace-nowrap self-start sm:self-center">
                        <x-heroicon-o-bookmark class="h-4 w-4 text-slate-500"/>
                        Pertemuan ke-{{ $sudah ? $sudah->pertemuan_ke : $pertemuanKe }}
                    </span>
                </div>

                @if ($sudah)
                    <div class="mb-5 flex flex-col sm:flex-row sm:items-center justify-between gap-3 rounded-xl border border-emerald-200 bg-emerald-50/70 p-4">
                        <div class="flex items-start sm:items-center gap-3">
                            <x-heroicon-s-check-circle class="h-5 w-5 text-emerald-600 shrink-0 mt-0.5 sm:mt-0"/>
                            <div>
                                <p class="text-sm font-semibold text-emerald-950">
                                    Agenda kelas ini sudah tersimpan (Pertemuan ke-{{ $sudah->pertemuan_ke }})
                                </p>
                                <p class="text-xs text-emerald-800 mt-0.5">
                                    Status: <strong>{{ $sudah->status->label() }}</strong>
                                    @if ($sudah->presensis_count > 0)
                                        · Presensi siswa sudah terisi ({{ $sudah->presensis_count }} siswa)
                                    @else
                                        · <span class="font-semibold text-amber-800">Presensi siswa belum diisi</span>
                                    @endif
                                </p>
                            </div>
                        </div>

                        <x-tombol gaya="aksen" :href="route('presensi.isi', $sudah)" ikon="user-group">
                            {{ $sudah->presensis_count > 0 ? 'Lihat / Ubah Presensi' : 'Isi Presensi Sekarang' }}
                        </x-tombol>
                    </div>
                @endif

                @if ($agendaSebelumnya)
                    <div class="mb-5 rounded-xl border border-slate-200 bg-slate-50/80 p-4 text-xs text-slate-700">
                        <div class="flex flex-wrap items-center justify-between gap-2 mb-1.5">
                            <span class="inline-flex items-center gap-1.5 font-semibold text-slate-600">
                                <x-heroicon-o-clock class="h-4 w-4 text-slate-400"/>
                                Materi Pertemuan Sebelumnya (Pertemuan ke-{{ $agendaSebelumnya->pertemuan_ke }}, {{ Tanggal::lengkap($agendaSebelumnya->tanggal) }})
                            </span>
                            <button type="button"
                                    onclick="document.getElementById('judul_materi').value = {{ Js::from($agendaSebelumnya->judul_materi) }}; document.getElementById('judul_materi').focus();"
                                    class="rounded-md border border-slate-200 bg-white px-2.5 py-1 text-xs font-semibold text-primary hover:bg-slate-50 transition-colors whitespace-nowrap">
                                Gunakan Judul Ini →
                            </button>
                        </div>
                        <p class="font-semibold text-slate-900 text-sm">
                            {{ $agendaSebelumnya->judul_materi }}
                        </p>
                        <div class="mt-1 flex flex-wrap items-center gap-3 text-slate-500">
                            @if ($agendaSebelumnya->bab)
                                <span>Bab: <strong class="text-slate-700">{{ $agendaSebelumnya->bab->judul }}</strong></span>
                            @endif
                            @if ($agendaSebelumnya->metode)
                                <span>Metode: <strong class="text-slate-700">{{ $agendaSebelumnya->metode }}</strong></span>
                            @endif
                        </div>
                    </div>
                @endif

                <form method="POST" action="{{ route('agenda.simpan-massal') }}" id="formAgendaKelas">
                    @csrf
                    <input type="hidden" name="tanggal" value="{{ $tanggal->toDateString() }}">
                    <input type="hidden" name="jadwal_id" value="{{ $sesiAktif->primary_schedule_id }}">
                    <input type="hidden" name="lanjut_presensi" id="inputLanjutPresensi" value="1">

                    <div class="grid gap-5 md:grid-cols-2">
                        <x-bidang label="Judul Materi / Topik Pembelajaran" nama="judul_materi" :wajib="true">
                            <input type="text" id="judul_materi" name="judul_materi"
                                   list="datalist-bab-tp"
                                   value="{{ old('judul_materi', $sudah?->judul_materi) }}"
                                   placeholder="mis. Menulis Teks Editorial"
                                   required
                                   class="block w-full rounded-lg border-slate-200 text-sm focus:border-primary focus:ring-primary">
                            <datalist id="datalist-bab-tp">
                                @foreach ($babs as $bab)
                                    <option value="{{ $bab->judul }}"></option>
                                    @foreach ($bab->tujuanPembelajarans as $tp)
                                        <option value="{{ $tp->deskripsi }}"></option>
                                    @endforeach
                                @endforeach
                            </datalist>

                            @if ($babs->isNotEmpty())
                                <div class="mt-2 flex flex-wrap items-center gap-1.5">
                                    <span class="text-[11px] font-medium text-slate-400">Pilih Bab:</span>
                                    @foreach ($babs->take(5) as $bab)
                                        <button type="button"
                                                onclick="document.getElementById('bab_id').value = '{{ $bab->id }}'; if(!document.getElementById('judul_materi').value) document.getElementById('judul_materi').value = {{ Js::from($bab->judul) }};"
                                                class="rounded-md border border-slate-200 bg-slate-50 px-2 py-0.5 text-[11px] font-medium text-slate-600 hover:bg-slate-100 hover:text-slate-900 transition-colors">
                                            {{ $bab->kode }}: {{ Str::limit($bab->judul, 24) }}
                                        </button>
                                    @endforeach
                                </div>
                            @endif
                        </x-bidang>

                        <x-bidang label="Bab / Lingkup Materi" nama="bab_id">
                            <select id="bab_id" name="bab_id"
                                    class="block w-full rounded-lg border-slate-200 text-sm focus:border-primary focus:ring-primary">
                                <option value="">— Tidak ditautkan / Pilih Bab —</option>
                                @foreach ($babs as $bab)
                                    <option value="{{ $bab->id }}" @selected(($sudah?->bab_id ?? old('bab_id')) === $bab->id)>
                                        {{ $bab->kode }} — {{ $bab->judul }}
                                    </option>
                                @endforeach
                            </select>
                        </x-bidang>

                        <x-bidang label="Metode Pembelajaran" nama="metode">
                            <input type="text" id="metode" name="metode"
                                   list="datalist-metode"
                                   value="{{ old('metode', $sudah?->metode) }}"
                                   placeholder="mis. Diskusi kelompok, Praktik, dll."
                                   class="block w-full rounded-lg border-slate-200 text-sm focus:border-primary focus:ring-primary">
                            <datalist id="datalist-metode">
                                <option value="Ceramah interaktif"></option>
                                <option value="Diskusi kelompok & presentasi"></option>
                                <option value="Praktik / Demonstrasi laboratorium"></option>
                                <option value="Problem Based Learning (PBL)"></option>
                                <option value="Project Based Learning (PjBL)"></option>
                                <option value="Penugasan mandiri & latihan soal"></option>
                                <option value="Tanya jawab & refleksi"></option>
                            </datalist>
                            <div class="mt-2 flex flex-wrap items-center gap-1.5">
                                <span class="text-[11px] font-medium text-slate-400">Opsi Cepat:</span>
                                @foreach (['Ceramah Interaktif', 'Diskusi Kelompok', 'Praktik / Demonstrasi', 'PBL', 'PjBL', 'Latihan Soal & Kuis'] as $chipMetode)
                                    <button type="button"
                                            onclick="document.getElementById('metode').value = '{{ $chipMetode }}'"
                                            class="rounded-md border border-slate-200 bg-slate-50 px-2 py-0.5 text-[11px] font-medium text-slate-600 hover:bg-slate-100 hover:text-slate-900 transition-colors">
                                        {{ $chipMetode }}
                                    </button>
                                @endforeach
                            </div>
                        </x-bidang>

                        <x-bidang label="Status Pertemuan" nama="status" :wajib="true">
                            <select id="status" name="status" required
                                    class="block w-full rounded-lg border-slate-200 text-sm focus:border-primary focus:ring-primary">
                                @foreach ($statusList as $nilai => $label)
                                    <option value="{{ $nilai }}"
                                        @selected(($sudah?->status?->value ?? old('status', 'terlaksana')) === $nilai)>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                        </x-bidang>

                        <x-bidang label="Uraian Kegiatan Pembelajaran" nama="uraian_kegiatan" class="md:col-span-2">
                            <textarea id="uraian_kegiatan" name="uraian_kegiatan" rows="3"
                                      placeholder="Ringkasan kegiatan pembelajaran, materi inti yang dibahas, atau catatan khusus kelas ini"
                                      class="block w-full rounded-lg border-slate-200 text-sm focus:border-primary focus:ring-primary">{{ old('uraian_kegiatan', $sudah?->uraian_kegiatan) }}</textarea>
                        </x-bidang>
                    </div>

                    <div class="mt-6 flex flex-wrap items-center justify-between gap-3 border-t border-slate-100 pt-5">
                        <div>
                            @if ($sudah)
                                <button type="submit" form="formHapusAgenda"
                                        class="inline-flex items-center gap-1.5 text-xs font-semibold text-rose-600 hover:text-rose-800 transition-colors">
                                    <x-heroicon-o-trash class="h-4 w-4"/>
                                    Hapus Agenda Kelas Ini
                                </button>
                            @endif
                        </div>

                        <div class="flex flex-wrap items-center gap-2">
                            <x-tombol gaya="halus" :href="route('agenda.index')">Batal</x-tombol>

                            <button type="submit"
                                    onclick="document.getElementById('inputLanjutPresensi').value = '0'"
                                    class="inline-flex cursor-pointer items-center gap-1.5 rounded-lg border border-slate-200 bg-white px-4 py-2 text-xs sm:text-sm font-semibold text-slate-700 hover:bg-slate-50 transition-colors">
                                <x-heroicon-o-check class="h-4 w-4 text-slate-500"/>
                                Simpan Agenda Saja
                            </button>

                            <button type="submit"
                                    onclick="document.getElementById('inputLanjutPresensi').value = '1'"
                                    class="inline-flex cursor-pointer items-center gap-2 rounded-lg bg-slate-900 px-4 py-2 text-xs sm:text-sm font-semibold text-white hover:bg-slate-800 transition-colors">
                                <x-heroicon-o-arrow-right-circle class="h-4 w-4"/>
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
