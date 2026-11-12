@extends('layouts.app')
@section('judul', 'Presensi Siswa')

@php
    use App\Support\Tanggal;
    use App\Enums\StatusPresensi;

    $tabAktif = request('tab', $tab ?? 'input');
    if (request()->hasAny(['filter_kelas_id', 'dari', 'sampai', 'page'])) {
        $tabAktif = 'riwayat';
    }
@endphp

@push('kepala')
<style>
    /* Filter Bar Minimalis */
    .filter-presensi-card {
        background-color: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 14px;
        padding: 16px 18px;
        box-shadow: 0 1px 2px rgba(15, 23, 42, 0.04);
    }
    .filter-presensi-row {
        display: flex;
        flex-wrap: wrap;
        align-items: flex-end;
        justify-content: space-between;
        gap: 16px;
    }

    /* Status Presensi Segmented Pill Buttons — Selalu 1 Baris Horizontal */
    .presensi-btn-group {
        display: inline-flex;
        flex-wrap: nowrap;
        align-items: center;
        gap: 4px;
        white-space: nowrap;
    }
    .btn-presensi-status {
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        text-align: center !important;
        line-height: 1 !important;
        width: 2rem !important;
        min-width: 2rem !important;
        height: 2rem !important;
        cursor: pointer;
        user-select: none;
        border-radius: 8px;
        font-size: 0.75rem;
        font-weight: 700;
        transition: background-color 0.12s ease, border-color 0.12s ease, color 0.12s ease;
        background-color: #ffffff;
        color: #475569;
        border: 1px solid #cbd5e1;
        flex-shrink: 0;
    }
    .btn-presensi-status:hover {
        background-color: #f8fafc;
        color: #0f172a;
        border-color: #94a3b8;
    }
    .btn-presensi-status.is-active-hadir {
        background-color: #059669 !important;
        color: #ffffff !important;
        border-color: #059669 !important;
    }
    .btn-presensi-status.is-active-sakit {
        background-color: #d97706 !important;
        color: #ffffff !important;
        border-color: #d97706 !important;
    }
    .btn-presensi-status.is-active-izin {
        background-color: #0284c7 !important;
        color: #ffffff !important;
        border-color: #0284c7 !important;
    }
    .btn-presensi-status.is-active-alfa {
        background-color: #e11d48 !important;
        color: #ffffff !important;
        border-color: #e11d48 !important;
    }
    .btn-presensi-status.is-active-bolos {
        background-color: #dc2626 !important;
        color: #ffffff !important;
        border-color: #dc2626 !important;
    }
    .btn-presensi-status.is-active-dispensasi {
        background-color: #7c3aed !important;
        color: #ffffff !important;
        border-color: #7c3aed !important;
    }
</style>
<script>
window.formPresensi = function(dataAwal, totalSiswa) {
    return {
        siswaStatus: dataAwal || {},
        totalSiswa: totalSiswa || 0,
        cariSiswa: '',
        filterStatus: 'semua',
        tampilPanduan: false,
        hadirCount: 0,
        sakitCount: 0,
        izinCount: 0,
        alfaCount: 0,
        dispensasiCount: 0,
        init() {
            this.hitung();
        },
        hitung() {
            let h = 0, s = 0, i = 0, a = 0, d = 0;
            for (let id in this.siswaStatus) {
                let st = (this.siswaStatus[id] && this.siswaStatus[id].status) ? this.siswaStatus[id].status : 'hadir';
                if (st === 'hadir') h++;
                else if (st === 'sakit') s++;
                else if (st === 'izin') i++;
                else if (st === 'alfa' || st === 'bolos') a++;
                else if (st === 'dispensasi') d++;
            }
            this.hadirCount = h;
            this.sakitCount = s;
            this.izinCount = i;
            this.alfaCount = a;
            this.dispensasiCount = d;
        },
        persenHadir() {
            if (!this.totalSiswa) return 100;
            return Math.round(((this.hadirCount + this.dispensasiCount) / this.totalSiswa) * 100);
        },
        getStatus(id) {
            return (this.siswaStatus[id] && this.siswaStatus[id].status) ? this.siswaStatus[id].status : 'hadir';
        },
        setStatus(id, status) {
            if (!this.siswaStatus[id]) {
                this.siswaStatus[id] = { status: 'hadir', keterangan: '' };
            }
            this.siswaStatus[id].status = status;
            this.siswaStatus = Object.assign({}, this.siswaStatus);
            this.hitung();
        },
        setKeterangan(id, teks) {
            if (!this.siswaStatus[id]) {
                this.siswaStatus[id] = { status: 'hadir', keterangan: '' };
            }
            this.siswaStatus[id].keterangan = teks;
            this.siswaStatus = Object.assign({}, this.siswaStatus);
        },
        cocokFilter(id, nama, nisn) {
            const st = this.getStatus(id);
            if (this.filterStatus === 'tidak_hadir' && st === 'hadir') {
                return false;
            }
            const q = (this.cariSiswa || '').trim().toLowerCase();
            if (!q) return true;
            return (nama && nama.toLowerCase().includes(q)) || (nisn && nisn.toLowerCase().includes(q));
        },
        toggleHadir(id, checked) {
            this.setStatus(id, checked ? 'hadir' : 'alfa');
        },
        tandaiSemuaHadir() {
            for (let id in this.siswaStatus) {
                if (!this.siswaStatus[id]) {
                    this.siswaStatus[id] = { status: 'hadir', keterangan: '' };
                }
                this.siswaStatus[id].status = 'hadir';
            }
            this.siswaStatus = Object.assign({}, this.siswaStatus);
            this.hitung();
        },
        batalTandaiSemua() {
            for (let id in this.siswaStatus) {
                if (!this.siswaStatus[id]) {
                    this.siswaStatus[id] = { status: 'alfa', keterangan: '' };
                }
                this.siswaStatus[id].status = 'alfa';
            }
            this.siswaStatus = Object.assign({}, this.siswaStatus);
            this.hitung();
        },
        getBtnStyle(status) {
            const map = {
                'hadir': 'background-color: #059669 !important; color: #ffffff !important; border-color: #059669 !important;',
                'sakit': 'background-color: #d97706 !important; color: #ffffff !important; border-color: #d97706 !important;',
                'izin': 'background-color: #0284c7 !important; color: #ffffff !important; border-color: #0284c7 !important;',
                'alfa': 'background-color: #e11d48 !important; color: #ffffff !important; border-color: #e11d48 !important;',
                'bolos': 'background-color: #dc2626 !important; color: #ffffff !important; border-color: #dc2626 !important;',
                'dispensasi': 'background-color: #7c3aed !important; color: #ffffff !important; border-color: #7c3aed !important;'
            };
            return map[status] || '';
        }
    };
};
document.addEventListener('alpine:init', () => {
    if (window.Alpine && window.formPresensi) {
        window.Alpine.data('formPresensi', window.formPresensi);
    }
});
</script>
@endpush

@section('konten')
    <x-kepala-halaman judul="Presensi Siswa"
                      keterangan="Input kehadiran siswa secara cepat dan pantau rekap pertemuan.">
        <x-slot:aksi>
            <x-tombol gaya="garis" ikon="chart-bar" :href="route('presensi.rekap')">Rekap Kehadiran</x-tombol>
        </x-slot:aksi>
    </x-kepala-halaman>

    {{-- Container Utama Tab Presensi --}}
    <div x-data="{
        tab: '{{ $tabAktif }}',
        pilihTab(t) {
            this.tab = t;
            const u = new URL(window.location.href);
            u.searchParams.set('tab', t);
            window.history.replaceState({}, '', u.toString());
        }
    }">
        {{-- Tab Navigasi Minimalis --}}
        <div class="mb-6 flex border-b border-slate-200">
            <button type="button" @click="pilihTab('input')"
                    class="flex cursor-pointer items-center gap-2 border-b-2 px-4 py-2.5 text-xs sm:text-sm font-semibold transition-colors whitespace-nowrap"
                    :class="tab === 'input'
                        ? 'border-slate-900 text-slate-900'
                        : 'border-transparent text-slate-500 hover:border-slate-300 hover:text-slate-700'">
                <x-heroicon-o-pencil-square class="h-4 w-4"/>
                <span>Input Presensi Langsung</span>
                @if ($sesiMapel->isNotEmpty())
                    <span class="rounded-md bg-slate-100 px-2 py-0.5 text-[11px] font-semibold text-slate-700">
                        {{ $sesiMapel->count() }} Sesi
                    </span>
                @endif
            </button>
            <button type="button" @click="pilihTab('riwayat')"
                    class="flex cursor-pointer items-center gap-2 border-b-2 px-4 py-2.5 text-xs sm:text-sm font-semibold transition-colors whitespace-nowrap"
                    :class="tab === 'riwayat'
                        ? 'border-slate-900 text-slate-900'
                        : 'border-transparent text-slate-500 hover:border-slate-300 hover:text-slate-700'">
                <x-heroicon-o-clock class="h-4 w-4"/>
                <span>Riwayat Pertemuan</span>
                <span class="rounded-md bg-slate-100 px-2 py-0.5 text-[11px] font-semibold text-slate-700">
                    {{ $daftar->total() }}
                </span>
            </button>
        </div>

        {{-- TAB 1: INPUT PRESENSI LANGSUNG --}}
        <div x-show="tab === 'input'" x-cloak class="space-y-6">
        {{-- Card Filter Tanggal & Pilihan Kelas --}}
        <div class="filter-presensi-card">
            <form method="GET" action="{{ route('presensi.index') }}" id="formTanggal"
                  class="filter-presensi-row">
                <input type="hidden" name="tab" value="input">

                <div>
                    <label for="tanggal" class="mb-1.5 block text-xs font-semibold text-slate-600">
                        Tanggal Pelajaran
                    </label>
                    <div class="flex flex-wrap items-center gap-2">
                        <input id="tanggal" name="tanggal" type="date" value="{{ $tanggal->toDateString() }}"
                               onchange="this.form.submit()"
                               class="rounded-lg border-slate-200 px-3 py-1.5 text-sm font-medium text-slate-800 focus:border-primary focus:ring-primary">
                        <button type="submit" class="btn-filter-dark">
                            <x-heroicon-o-magnifying-glass class="h-3.5 w-3.5"/>
                            <span>Tampilkan</span>
                        </button>
                        <a href="{{ route('presensi.index', ['tanggal' => today()->toDateString(), 'tab' => 'input']) }}"
                           class="btn-filter-light">
                            Hari Ini
                        </a>
                        <div class="flex items-center gap-1">
                            <a href="{{ route('presensi.index', ['tanggal' => $tanggal->copy()->subDay()->toDateString(), 'tab' => 'input']) }}"
                               title="Hari Sebelumnya"
                               class="btn-filter-light" style="padding: 8px 9px !important;">
                                <x-heroicon-o-chevron-left class="h-4 w-4"/>
                            </a>
                            <a href="{{ route('presensi.index', ['tanggal' => $tanggal->copy()->addDay()->toDateString(), 'tab' => 'input']) }}"
                               title="Hari Berikutnya"
                               class="btn-filter-light" style="padding: 8px 9px !important;">
                                <x-heroicon-o-chevron-right class="h-4 w-4"/>
                            </a>
                        </div>
                    </div>
                </div>

                <div style="min-width: 220px;">
                    <label for="pilih_kelas" class="mb-1.5 block text-xs font-semibold text-slate-600">
                        Pilih Kelas Lain
                    </label>
                    <select id="pilih_kelas" name="kelas_id" onchange="this.form.submit()"
                            class="w-full rounded-lg border-slate-200 px-3 py-1.5 text-sm font-medium text-slate-700 focus:border-primary focus:ring-primary">
                        <option value="">— Sesuai Jadwal Hari Ini —</option>
                        @foreach ($kelasList as $k)
                            <option value="{{ $k->id }}" @selected($kelasAktif?->id === $k->id && $sesiMapel->isEmpty())>
                                Kelas {{ $k->nama }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </form>

            <div class="mt-3 pt-3 border-t border-slate-100 flex flex-wrap items-center justify-between gap-2 text-xs text-slate-600">
                <div class="flex items-center gap-2">
                    <x-heroicon-o-calendar class="h-4 w-4 text-slate-400"/>
                    <span>Jadwal Hari <strong class="font-semibold text-slate-900">{{ $hari?->label() ?? '—' }}</strong>, {{ Tanggal::lengkap($tanggal) }}</span>
                </div>
                @if ($sesiMapel->isNotEmpty())
                    @php
                        $jumlahSesiTerisi = $sesiMapel->where('terisi', true)->count();
                    @endphp
                    <span class="inline-flex items-center gap-1.5 rounded-md px-2.5 py-0.5 text-[11px] font-semibold whitespace-nowrap {{ $jumlahSesiTerisi === $sesiMapel->count() ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-slate-100 text-slate-700' }}">
                        Progres: {{ $jumlahSesiTerisi }}/{{ $sesiMapel->count() }} Sesi Terisi
                    </span>
                @endif
            </div>
        </div>

        {{-- Daftar Sesi Pelajaran Hari Tersebut --}}
        @if ($sesiMapel->isNotEmpty())
            <div>
                <div class="sesi-section-header">
                    <div class="sesi-section-title-group">
                        <span class="sesi-section-icon">
                            <x-heroicon-o-academic-cap class="h-5 w-5"/>
                        </span>
                        <div>
                            <h3 class="text-sm font-bold text-slate-900">
                                Sesi Pelajaran Hari {{ $hari?->label() }}
                            </h3>
                            <p class="text-xs text-slate-500">
                                Pilih kartu sesi kelas untuk mengisi atau memperbarui presensi siswa
                            </p>
                        </div>
                    </div>
                    <span class="inline-flex items-center rounded-lg bg-white px-3 py-1 text-xs font-semibold text-slate-700 border border-slate-200 whitespace-nowrap">
                        {{ $sesiMapel->count() }} Sesi ({{ $sesiMapel->sum('total_jp') }} JP)
                    </span>
                </div>

                <div class="sesi-cards-grid">
                    @foreach ($sesiMapel as $sesi)
                        @php
                            $isAktif = $sesiAktifId === $sesi->id;
                            $warnaK = $sesi->warna_kelas ?? \App\Support\WarnaKelas::untuk($sesi->kelas_tampilan);
                            $jpSingkat = $sesi->label_jp_singkat ?? $sesi->label_jp;
                            $totalSiswaSesi = $sesi->total_siswa_kelas ?? ($isAktif ? $siswas->count() : 0);
                        @endphp
                        <a href="{{ route('presensi.index', ['tanggal' => $tanggal->toDateString(), 'sesi' => $sesi->id, 'tab' => 'input']) }}"
                           class="sesi-card {{ $isAktif ? 'is-active' : '' }}"
                           style="--sesi-accent: {{ $warnaK['accent'] }};">

                            <div>
                                <div class="flex items-center justify-between gap-2 flex-nowrap">
                                    <div class="flex items-center gap-1.5 flex-nowrap">
                                        <span class="inline-flex items-center font-semibold text-xs whitespace-nowrap shrink-0 rounded-md px-2 py-0.5"
                                              style="{{ $isAktif ? 'background-color: rgba(255,255,255,0.16); color: #ffffff;' : 'background-color: '.$warnaK['jp_bg'].'; color: #ffffff;' }}">
                                            {{ $jpSingkat }}
                                        </span>
                                        <span class="inline-flex items-center font-semibold text-[11px] whitespace-nowrap shrink-0 rounded-md px-2 py-0.5"
                                              style="{{ $isAktif ? 'background-color: rgba(255,255,255,0.08); color: #cbd5e1;' : 'background-color: '.$warnaK['badge_bg'].'; color: '.$warnaK['badge_text'].';' }}">
                                            {{ $sesi->total_jp }} JP
                                        </span>
                                    </div>

                                    @if ($sesi->terisi)
                                        <span class="inline-flex items-center gap-1 rounded-md px-2 py-0.5 text-[11px] font-semibold whitespace-nowrap shrink-0"
                                              style="{{ $isAktif ? 'background-color: #059669; color: #ffffff;' : 'background-color: #ecfdf5; color: #065f46; border: 1px solid #a7f3d0;' }}">
                                            <x-heroicon-s-check-circle class="h-3.5 w-3.5 shrink-0"/>
                                            <span>Terisi</span>
                                        </span>
                                    @else
                                        <span class="inline-flex items-center rounded-md px-2 py-0.5 text-[11px] font-semibold whitespace-nowrap shrink-0"
                                              style="{{ $isAktif ? 'background-color: #d97706; color: #ffffff;' : 'background-color: #fffbeb; color: #92400e; border: 1px solid #fde68a;' }}">
                                            Belum Diisi
                                        </span>
                                    @endif
                                </div>

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
                                            @if ($sesi->sedang_berlangsung ?? false)
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

                                <div class="mt-3.5 flex items-center justify-between gap-2 rounded-lg px-3 py-2 text-xs font-medium"
                                     style="{{ $isAktif ? 'background-color: rgba(255,255,255,0.08); color: #e2e8f0;' : 'background-color: #f8fafc; border: 1px solid #e2e8f0; color: #475569;' }}">
                                    <span class="whitespace-nowrap">{{ $sesi->rentang_waktu ? $sesi->rentang_waktu . ' WIB' : 'Jadwal Harian' }}</span>
                                    <span class="whitespace-nowrap">{{ $totalSiswaSesi }} Siswa</span>
                                </div>
                            </div>

                            <div class="mt-3 flex items-center justify-between gap-2 pt-2.5 text-[11px] font-medium"
                                 style="border-top: 1px solid {{ $isAktif ? 'rgba(255,255,255,0.12)' : '#f1f5f9' }};">
                                <div class="flex items-center gap-1 flex-nowrap">
                                    @if ($sesi->terisi)
                                        <span class="rounded px-1.5 py-0.5 text-[10px] font-semibold whitespace-nowrap"
                                              style="{{ $isAktif ? 'background-color: rgba(16,185,129,0.25); color: #a7f3d0;' : 'background-color: #d1fae5; color: #065f46;' }}">
                                            H: {{ $sesi->jumlah_hadir }}
                                        </span>
                                        @if (($sesi->jumlah_sakit ?? 0) > 0)
                                            <span class="rounded px-1.5 py-0.5 text-[10px] font-semibold whitespace-nowrap"
                                                  style="{{ $isAktif ? 'background-color: rgba(245,158,11,0.28); color: #fde68a;' : 'background-color: #fef3c7; color: #92400e;' }}">
                                                S: {{ $sesi->jumlah_sakit }}
                                            </span>
                                        @endif
                                        @if (($sesi->jumlah_izin ?? 0) > 0)
                                            <span class="rounded px-1.5 py-0.5 text-[10px] font-semibold whitespace-nowrap"
                                                  style="{{ $isAktif ? 'background-color: rgba(14,165,233,0.28); color: #bae6fd;' : 'background-color: #e0f2fe; color: #075985;' }}">
                                                I: {{ $sesi->jumlah_izin }}
                                            </span>
                                        @endif
                                        @if (($sesi->jumlah_alfa ?? 0) > 0)
                                            <span class="rounded px-1.5 py-0.5 text-[10px] font-semibold whitespace-nowrap"
                                                  style="{{ $isAktif ? 'background-color: rgba(244,63,94,0.3); color: #fecdd3;' : 'background-color: #ffe4e6; color: #9f1239;' }}">
                                                A: {{ $sesi->jumlah_alfa }}
                                            </span>
                                        @endif
                                    @else
                                        <span style="color: {{ $isAktif ? '#cbd5e1' : '#64748b' }};">
                                            {{ $isAktif ? 'Dibuka di bawah' : 'Klik untuk isi presensi' }}
                                        </span>
                                    @endif
                                </div>

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
        @elseif (! $kelasAktif)
            <div class="kartu p-8 text-center">
                <x-heroicon-o-calendar-days class="mx-auto h-10 w-10 text-slate-300"/>
                <p class="mt-3 font-semibold text-slate-800">Tidak ada jadwal pelajaran pada hari {{ $hari?->label() ?? 'ini' }}</p>
                <p class="mt-1 text-sm text-slate-500">Pilih tanggal lain di atas atau pilih kelas melalui dropdown "Pilih Kelas Lain".</p>
            </div>
        @endif

        {{-- FORM PRESENSI SISWA --}}
        @if ($kelasAktif)
            @if ($siswas->isEmpty())
                <div class="kartu p-10 text-center">
                    <x-heroicon-o-users class="mx-auto h-10 w-10 text-slate-300"/>
                    <p class="mt-3 font-semibold text-slate-800">Kelas {{ $kelasAktif->nama }} belum memiliki siswa aktif</p>
                    <p class="mt-1 text-sm text-slate-500">Hubungi admin untuk menambahkan atau mengunggah data siswa di kelas ini.</p>
                </div>
            @else
                @php
                    $dataAwal = [];
                    foreach ($siswas as $s) {
                        $rec = $presensiTersimpan->get($s->id);
                        $dataAwal[$s->id] = [
                            'status' => old("status.{$s->id}", $rec?->status?->value ?? 'hadir'),
                            'keterangan' => old("keterangan.{$s->id}", $rec?->keterangan ?? ''),
                        ];
                    }
                    $warnaAktifK = $sesiAktif?->warna_kelas ?? \App\Support\WarnaKelas::untuk($kelasAktif->nama);
                @endphp

                <form method="POST" action="{{ route('presensi.simpan-langsung') }}"
                      x-data="formPresensi({{ Js::from($dataAwal) }}, {{ $siswas->count() }})" x-init="init()">
                    @csrf
                    <input type="hidden" name="tanggal" value="{{ $tanggal->toDateString() }}">
                    <input type="hidden" name="jadwal_id" value="{{ $jadwalAktif?->id }}">
                    <input type="hidden" name="sesi_id" value="{{ $sesiAktifId }}">

                    {{-- Card Info Sesi & Materi --}}
                    <div class="kartu mb-4 p-4 sm:p-5" style="border-left: 4px solid {{ $warnaAktifK['accent'] }};">
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-4 border-b border-slate-100">
                            <div>
                                <div class="flex flex-wrap items-center gap-2">
                                    <span class="rounded-md px-2.5 py-0.5 text-xs font-bold whitespace-nowrap"
                                          style="background-color: {{ $warnaAktifK['badge_bg'] }}; color: {{ $warnaAktifK['badge_text'] }}; border: 1px solid {{ $warnaAktifK['border'] }};">
                                        Kelas {{ $kelasAktif->nama }}
                                    </span>
                                    <h3 class="text-base font-bold text-slate-900">
                                        {{ $jadwalAktif?->nama_tampilan ?? 'Mata Pelajaran' }}
                                    </h3>
                                    @if ($sesiAktif)
                                        <span class="rounded-md px-2.5 py-0.5 text-xs font-semibold whitespace-nowrap"
                                              style="background-color: {{ $warnaAktifK['jp_bg'] }}; color: #ffffff;">
                                            {{ $sesiAktif->label_jp }}
                                        </span>
                                    @endif
                                </div>
                                <p class="text-xs text-slate-500 mt-1.5 flex flex-wrap items-center gap-1.5">
                                    <span>{{ Tanggal::lengkap($tanggal) }}</span>
                                    @if ($sesiAktif?->rentang_waktu)
                                        <span>·</span>
                                        <strong class="text-slate-700">{{ $sesiAktif->rentang_waktu }} WIB</strong>
                                    @endif
                                    <span>·</span>
                                    <strong class="text-slate-700">{{ $siswas->count() }} Siswa</strong>
                                </p>
                            </div>

                            <div class="flex items-center gap-2">
                                <button type="button" @click="tampilPanduan = !tampilPanduan"
                                        class="inline-flex cursor-pointer items-center gap-1.5 rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-xs font-semibold text-slate-600 hover:bg-slate-50 transition-colors whitespace-nowrap">
                                    <x-heroicon-o-information-circle class="h-4 w-4 text-slate-400"/>
                                    <span x-text="tampilPanduan ? 'Tutup Kode' : 'Info Kode Status'"></span>
                                </button>
                                @if ($agendaAktif)
                                    <span class="inline-flex items-center gap-1.5 rounded-lg bg-emerald-50 px-3 py-1.5 text-xs font-semibold text-emerald-700 border border-emerald-200 whitespace-nowrap">
                                        <x-heroicon-s-check-circle class="h-4 w-4 text-emerald-600"/>
                                        Pertemuan ke-{{ $agendaAktif->pertemuan_ke }}
                                    </span>
                                @else
                                    <span class="inline-flex items-center rounded-lg bg-amber-50 px-3 py-1.5 text-xs font-semibold text-amber-800 border border-amber-200 whitespace-nowrap">
                                        Pertemuan Baru
                                    </span>
                                @endif
                            </div>
                        </div>

                        <div class="mt-4 flex flex-col sm:flex-row items-start sm:items-center gap-3 rounded-lg bg-slate-50 p-3 border border-slate-200/80">
                            <label for="judul_materi" class="flex items-center gap-1.5 text-xs font-semibold text-slate-700 shrink-0 whitespace-nowrap">
                                <x-heroicon-o-book-open class="h-4 w-4 text-slate-500"/>
                                <span>Materi Pertemuan:</span>
                            </label>
                            <input type="text" id="judul_materi" name="judul_materi"
                                   value="{{ old('judul_materi', $agendaAktif?->judul_materi ?? 'Pembelajaran Tatap Muka') }}"
                                   placeholder="Contoh: Teks Eksplanasi, Latihan Soal Bab 2, dll."
                                   class="w-full sm:max-w-md rounded-lg border-slate-200 bg-white px-3 py-1.5 text-sm text-slate-800 focus:border-primary focus:ring-primary">
                            <span class="text-xs text-slate-500">
                                Agenda tercatat otomatis saat presensi disimpan.
                            </span>
                        </div>
                    </div>

                    {{-- Panduan Singkatan Status --}}
                    <div x-show="tampilPanduan" x-cloak x-transition class="kartu mb-4 p-4">
                        <div class="flex items-center justify-between gap-2 mb-2.5 text-xs font-semibold text-slate-600">
                            <span>Keterangan Kode Status Kehadiran</span>
                            <button type="button" @click="tampilPanduan = false" class="text-slate-400 hover:text-slate-600">Tutup</button>
                        </div>
                        <div class="status-legend-grid text-xs">
                            <div class="status-legend-card" style="background-color: #ecfdf5; border: 1px solid #a7f3d0;">
                                <span class="status-legend-badge" style="background-color: #059669;">H</span>
                                <div class="min-w-0 flex-1">
                                    <p class="font-semibold text-xs" style="color: #065f46;">Hadir</p>
                                    <p class="text-[11px] truncate" style="color: #047857;">Masuk kelas</p>
                                </div>
                            </div>
                            <div class="status-legend-card" style="background-color: #fffbeb; border: 1px solid #fde68a;">
                                <span class="status-legend-badge" style="background-color: #d97706;">S</span>
                                <div class="min-w-0 flex-1">
                                    <p class="font-semibold text-xs" style="color: #92400e;">Sakit</p>
                                    <p class="text-[11px] truncate" style="color: #b45309;">Kondisi sakit</p>
                                </div>
                            </div>
                            <div class="status-legend-card" style="background-color: #f0f9ff; border: 1px solid #bae6fd;">
                                <span class="status-legend-badge" style="background-color: #0284c7;">I</span>
                                <div class="min-w-0 flex-1">
                                    <p class="font-semibold text-xs" style="color: #075985;">Izin</p>
                                    <p class="text-[11px] truncate" style="color: #0369a1;">Izin keperluan</p>
                                </div>
                            </div>
                            <div class="status-legend-card" style="background-color: #fff1f2; border: 1px solid #fecdd3;">
                                <span class="status-legend-badge" style="background-color: #e11d48;">A</span>
                                <div class="min-w-0 flex-1">
                                    <p class="font-semibold text-xs" style="color: #9f1239;">Alfa</p>
                                    <p class="text-[11px] truncate" style="color: #be123c;">Tanpa kabar</p>
                                </div>
                            </div>
                            <div class="status-legend-card" style="background-color: #fef2f2; border: 1px solid #fca5a5;">
                                <span class="status-legend-badge" style="background-color: #dc2626;">B</span>
                                <div class="min-w-0 flex-1">
                                    <p class="font-semibold text-xs" style="color: #991b1b;">Bolos</p>
                                    <p class="text-[11px] truncate" style="color: #b91c1c;">Tinggalkan jam</p>
                                </div>
                            </div>
                            <div class="status-legend-card" style="background-color: #f5f3ff; border: 1px solid #ddd6fe;">
                                <span class="status-legend-badge" style="background-color: #7c3aed;">D</span>
                                <div class="min-w-0 flex-1">
                                    <p class="font-semibold text-xs" style="color: #5b21b6;">Dispensasi</p>
                                    <p class="text-[11px] truncate" style="color: #6d28d9;">Tugas sekolah</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Toolbar Ringkasan, Pencarian Siswa & Aksi Cepat --}}
                    <div class="kartu mb-4 p-4 space-y-3">
                        <div class="flex flex-wrap items-center justify-between gap-3">
                            <div class="flex flex-wrap items-center gap-2 text-xs font-semibold">
                                <span class="inline-flex items-center gap-1.5 rounded-lg px-2.5 py-1 whitespace-nowrap" style="background-color: #ecfdf5; color: #065f46; border: 1px solid #a7f3d0;">
                                    <span>Hadir:</span>
                                    <strong class="tabular-nums font-bold" x-text="hadirCount"></strong>
                                    <span>/ {{ $siswas->count() }}</span>
                                    <span class="rounded bg-emerald-700/10 px-1.5 py-0.5 text-[10px]" x-text="persenHadir() + '%'"></span>
                                </span>
                                <span x-show="sakitCount > 0" x-cloak class="inline-flex items-center gap-1 rounded-lg px-2.5 py-1 whitespace-nowrap" style="background-color: #fffbeb; color: #92400e; border: 1px solid #fde68a;">
                                    Sakit: <strong class="tabular-nums" x-text="sakitCount"></strong>
                                </span>
                                <span x-show="izinCount > 0" x-cloak class="inline-flex items-center gap-1 rounded-lg px-2.5 py-1 whitespace-nowrap" style="background-color: #f0f9ff; color: #075985; border: 1px solid #bae6fd;">
                                    Izin: <strong class="tabular-nums" x-text="izinCount"></strong>
                                </span>
                                <span x-show="alfaCount > 0" x-cloak class="inline-flex items-center gap-1 rounded-lg px-2.5 py-1 whitespace-nowrap" style="background-color: #fff1f2; color: #9f1239; border: 1px solid #fecdd3;">
                                    Alfa/Bolos: <strong class="tabular-nums" x-text="alfaCount"></strong>
                                </span>
                                <span x-show="dispensasiCount > 0" x-cloak class="inline-flex items-center gap-1 rounded-lg px-2.5 py-1 whitespace-nowrap" style="background-color: #f5f3ff; color: #5b21b6; border: 1px solid #ddd6fe;">
                                    Dispensasi: <strong class="tabular-nums" x-text="dispensasiCount"></strong>
                                </span>
                            </div>

                            <div class="flex flex-wrap items-center gap-2">
                                <button type="button" @click="tandaiSemuaHadir()"
                                        class="inline-flex cursor-pointer items-center gap-1.5 rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-1.5 text-xs font-semibold text-emerald-800 hover:bg-emerald-100 transition-colors whitespace-nowrap">
                                    <x-heroicon-o-check-circle class="h-4 w-4 text-emerald-600 shrink-0"/>
                                    <span>Tandai Semua Hadir</span>
                                </button>
                                <button type="button" @click="batalTandaiSemua()"
                                        class="inline-flex cursor-pointer items-center gap-1.5 rounded-lg border border-rose-200 bg-rose-50 px-3 py-1.5 text-xs font-semibold text-rose-800 hover:bg-rose-100 transition-colors whitespace-nowrap">
                                    <x-heroicon-o-x-circle class="h-4 w-4 text-rose-600 shrink-0"/>
                                    <span>Batal Tandai Semua</span>
                                </button>
                            </div>
                        </div>

                        <div class="flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-2.5 pt-2.5 border-t border-slate-100">
                            <div class="relative flex-1 max-w-sm">
                                <x-heroicon-o-magnifying-glass class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-slate-400"/>
                                <input type="text" x-model="cariSiswa"
                                       placeholder="Cari nama siswa atau NISN..."
                                       class="w-full rounded-lg border-slate-200 bg-slate-50/70 pl-9 pr-8 py-1.5 text-xs text-slate-800 placeholder:text-slate-400 focus:bg-white focus:border-primary focus:ring-primary">
                                <button type="button" x-show="cariSiswa.length > 0" x-cloak @click="cariSiswa = ''"
                                        class="absolute right-2.5 top-1/2 -translate-y-1/2 text-xs font-semibold text-slate-400 hover:text-slate-600">
                                    ✕
                                </button>
                            </div>

                            <div class="flex items-center gap-1.5">
                                <button type="button" @click="filterStatus = 'semua'"
                                        class="cursor-pointer rounded-lg px-3 py-1.5 text-xs font-semibold transition-colors whitespace-nowrap"
                                        :class="filterStatus === 'semua' ? 'bg-slate-900 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'">
                                    Semua ({{ $siswas->count() }})
                                </button>
                                <button type="button" @click="filterStatus = 'tidak_hadir'"
                                        class="cursor-pointer rounded-lg px-3 py-1.5 text-xs font-semibold transition-colors whitespace-nowrap"
                                        :class="filterStatus === 'tidak_hadir' ? 'bg-amber-600 text-white' : 'bg-amber-50 text-amber-800 border border-amber-200 hover:bg-amber-100'">
                                    Tidak Hadir (<span x-text="sakitCount + izinCount + alfaCount + dispensasiCount"></span>)
                                </button>
                            </div>
                        </div>
                    </div>

                    {{-- Tabel Siswa (Seluruh pilihan status 1 baris simetris) --}}
                    <div class="kartu overflow-hidden">
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-slate-200/80 text-xs">
                                <thead class="bg-slate-50 text-left text-[11px] font-semibold uppercase tracking-wider text-slate-500">
                                    <tr>
                                        <th class="w-12 px-3 py-3 text-center whitespace-nowrap">No</th>
                                        <th class="px-4 py-3 whitespace-nowrap">Nama Siswa</th>
                                        <th class="w-16 px-3 py-3 text-center whitespace-nowrap">Hadir</th>
                                        <th class="px-4 py-3 whitespace-nowrap">Pilihan Status</th>
                                        <th class="px-4 py-3 whitespace-nowrap">Keterangan</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100 bg-white">
                                    @foreach ($siswas as $s)
                                        <tr x-show="cocokFilter({{ $s->id }}, {{ Js::from($s->nama) }}, {{ Js::from($s->nisn ?? '') }})"
                                            class="hover:bg-slate-50/80 transition-colors"
                                            :class="getStatus({{ $s->id }}) !== 'hadir' ? 'bg-amber-50/30' : ''">
                                            <td class="px-3 py-3 text-center">
                                                <span class="inline-flex h-7 w-7 items-center justify-center rounded-md bg-slate-100 text-xs font-semibold text-slate-700 tabular-nums">
                                                    {{ $s->pivot->no_absen ?? $loop->iteration }}
                                                </span>
                                            </td>

                                            <td class="px-4 py-3 min-w-[180px]">
                                                <p class="font-semibold text-slate-900 text-sm">{{ $s->nama }}</p>
                                                <p class="text-[11px] text-slate-400">
                                                    NISN: {{ $s->nisn ?: '-' }} · {{ $s->label_jenis_kelamin }}
                                                </p>
                                            </td>

                                            <td class="px-3 py-3 text-center">
                                                <input type="checkbox"
                                                       :checked="getStatus({{ $s->id }}) === 'hadir'"
                                                       @change="toggleHadir({{ $s->id }}, $event.target.checked)"
                                                       style="accent-color: #059669; width: 1.15rem; height: 1.15rem; cursor: pointer;"
                                                       class="rounded border-slate-300">
                                            </td>

                                            <td class="px-4 py-3 whitespace-nowrap">
                                                <input type="hidden" name="status[{{ $s->id }}]" :value="getStatus({{ $s->id }})">

                                                <div class="presensi-btn-group">
                                                    @foreach ($statusList as $status)
                                                        <button type="button"
                                                                @click="setStatus({{ $s->id }}, '{{ $status->value }}')"
                                                                class="btn-presensi-status"
                                                                :class="getStatus({{ $s->id }}) === '{{ $status->value }}' ? 'is-active-{{ $status->value }}' : ''"
                                                                :style="getStatus({{ $s->id }}) === '{{ $status->value }}' ? getBtnStyle('{{ $status->value }}') : ''"
                                                                title="{{ $status->label() }}">
                                                            {{ $status->singkatan() }}
                                                        </button>
                                                    @endforeach
                                                </div>
                                            </td>

                                            <td class="px-4 py-3 min-w-[200px]">
                                                <input type="text" name="keterangan[{{ $s->id }}]"
                                                       x-model="siswaStatus[{{ $s->id }}].keterangan"
                                                       :placeholder="getStatus({{ $s->id }}) === 'hadir' ? 'Keterangan (opsional)' : 'Alasan ketidakhadiran...'"
                                                       class="w-full rounded-lg border-slate-200 px-3 py-1.5 text-xs placeholder:text-slate-400 focus:border-primary focus:ring-primary">
                                                <div x-show="getStatus({{ $s->id }}) !== 'hadir'" x-cloak class="mt-1.5 flex flex-wrap gap-1">
                                                    @foreach (['Sakit demam', 'Surat dokter', 'Izin keluarga', 'Dispensasi', 'Tanpa kabar'] as $alasan)
                                                        <button type="button"
                                                                @click="setKeterangan({{ $s->id }}, '{{ $alasan }}')"
                                                                class="rounded border border-slate-200 bg-slate-50 px-1.5 py-0.5 text-[10px] font-medium text-slate-600 hover:bg-slate-100 hover:text-slate-900 whitespace-nowrap">
                                                            {{ $alasan }}
                                                        </button>
                                                    @endforeach
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="sticky bottom-0 flex flex-col sm:flex-row items-center justify-between gap-3 border-t border-slate-200 bg-white/95 px-5 py-3.5 backdrop-blur-sm">
                            <p class="text-xs text-slate-500 text-center sm:text-left">
                                Pastikan status kehadiran telah sesuai sebelum menyimpan.
                            </p>
                            <button type="submit"
                                    class="inline-flex cursor-pointer items-center gap-2 rounded-lg bg-slate-900 px-5 py-2.5 text-xs font-semibold text-white hover:bg-slate-800 transition-colors whitespace-nowrap">
                                <x-heroicon-o-check class="h-4 w-4"/>
                                <span>Simpan Presensi {{ $kelasAktif->nama }}</span>
                            </button>
                        </div>
                    </div>
                </form>
            @endif
        @endif
    </div>

    {{-- TAB 2: RIWAYAT PERTEMUAN & REKAP --}}
    <div x-show="tab === 'riwayat'" x-cloak class="space-y-4">
        <form method="GET" action="{{ route('presensi.index') }}" class="kartu tanpa-cetak grid gap-3 p-4 sm:grid-cols-2 lg:grid-cols-4">
            <input type="hidden" name="tab" value="riwayat">
            <x-bidang label="Kelas" nama="filter_kelas_id">
                <x-pilihan nama="filter_kelas_id" :opsi="$kelasList->pluck('nama', 'id')" :terpilih="request('filter_kelas_id')" kosong="Semua kelas"/>
            </x-bidang>
            <x-bidang label="Dari" nama="dari">
                <x-isian nama="dari" tipe="date" :value="request('dari')"/>
            </x-bidang>
            <x-bidang label="Sampai" nama="sampai">
                <x-isian nama="sampai" tipe="date" :value="request('sampai')"/>
            </x-bidang>
            <div class="flex items-end gap-2">
                <x-tombol gaya="utama" ikon="funnel">Tampilkan</x-tombol>
                <x-tombol gaya="halus" :href="route('presensi.index', ['tab' => 'riwayat'])">Reset</x-tombol>
            </div>
        </form>

        <x-tabel>
            <x-slot:kepala>
                <tr>
                    <th class="px-4 py-3 whitespace-nowrap">Tanggal</th>
                    <th class="px-4 py-3 whitespace-nowrap">Kelas / Mapel</th>
                    <th class="px-4 py-3">Materi</th>
                    <th class="px-4 py-3 whitespace-nowrap">Rekap</th>
                    <th class="px-4 py-3 text-right whitespace-nowrap">Aksi</th>
                </tr>
            </x-slot:kepala>

            @forelse ($daftar as $agenda)
                <tr class="hover:bg-slate-50/80 transition-colors">
                    <td class="whitespace-nowrap px-4 py-3.5">
                        <p class="font-semibold text-slate-800">{{ Tanggal::angka($agenda->tanggal) }}</p>
                        <p class="text-xs text-slate-500">Pertemuan ke-{{ $agenda->pertemuan_ke }}</p>
                    </td>
                    <td class="whitespace-nowrap px-4 py-3.5">
                        <p class="font-semibold text-slate-800">{{ $agenda->jadwal?->kelas_tampilan ?? '—' }}</p>
                        <p class="text-xs text-slate-500">{{ $agenda->jadwal?->nama_tampilan ?? '—' }}</p>
                    </td>
                    <td class="px-4 py-3.5 text-slate-700">{{ $agenda->judul_materi }}</td>
                    <td class="whitespace-nowrap px-4 py-3.5">
                        @if ($agenda->presensis_count > 0)
                            <div class="flex items-center gap-1.5 flex-nowrap text-xs font-semibold">
                                <span class="rounded-md bg-emerald-100 px-2 py-0.5 text-emerald-800 whitespace-nowrap">H: {{ $agenda->jumlah_hadir }}</span>
                                @if ($agenda->jumlah_alfa > 0)
                                    <span class="rounded-md bg-rose-100 px-2 py-0.5 text-rose-800 whitespace-nowrap">A: {{ $agenda->jumlah_alfa }}</span>
                                @endif
                                <span class="text-slate-400 font-normal whitespace-nowrap">dari {{ $agenda->presensis_count }}</span>
                            </div>
                        @else
                            <span class="rounded-md bg-rose-50 px-2 py-0.5 text-xs font-semibold text-rose-600 border border-rose-200 whitespace-nowrap">Belum diisi</span>
                        @endif
                    </td>
                    <td class="whitespace-nowrap px-4 py-3.5 text-right">
                        <x-tombol gaya="{{ $agenda->presensis_count ? 'garis' : 'aksen' }}"
                                  :href="route('presensi.isi', $agenda)"
                                  ikon="{{ $agenda->presensis_count ? 'eye' : 'pencil-square' }}">
                            {{ $agenda->presensis_count ? 'Lihat / Ubah' : 'Isi Presensi' }}
                        </x-tombol>
                    </td>
                </tr>
            @empty
                <x-tabel.kosong :kolom="5" ikon="user-group"
                                judul="Belum ada pertemuan"
                                pesan="Presensi yang telah disimpan akan tampil di riwayat ini."/>
            @endforelse
        </x-tabel>

        <div class="mt-4">{{ $daftar->links() }}</div>
    </div>
</div>
@endsection

@push('skrip')
<script>
if (window.Alpine && window.formPresensi) {
    window.Alpine.data('formPresensi', window.formPresensi);
}
</script>
@endpush
