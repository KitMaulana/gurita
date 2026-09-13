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
    /* Status Legend Aesthetic Badges & Cards */
    .status-legend-card {
        display: flex !important;
        align-items: center !important;
        gap: 12px !important;
        padding: 10px 14px !important;
        border-radius: 12px !important;
        transition: all 0.2s ease !important;
    }
    .status-legend-card:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
    }
    .status-legend-badge {
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        width: 36px !important;
        height: 36px !important;
        min-width: 36px !important;
        min-height: 36px !important;
        border-radius: 10px !important;
        font-size: 15px !important;
        font-weight: 800 !important;
        line-height: 1 !important;
        text-align: center !important;
        color: #ffffff !important;
        flex-shrink: 0 !important;
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.12) !important;
    }

    /* Status Presensi Pill Buttons */
    .btn-presensi-status {
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        text-align: center !important;
        line-height: 1 !important;
        min-width: 2.25rem !important;
        height: 2rem !important;
        cursor: pointer;
        user-select: none;
        border-radius: 0.5rem;
        padding: 0 0.5rem;
        font-size: 0.75rem;
        font-weight: 700;
        transition: all 0.15s ease-in-out;
        background-color: #ffffff;
        color: #334155;
        border: 1px solid #cbd5e1;
    }
    .btn-presensi-status:hover {
        background-color: #f1f5f9;
        color: #0f172a;
        border-color: #94a3b8;
    }
    .btn-presensi-status.is-active-hadir {
        background-color: #059669 !important;
        color: #ffffff !important;
        border-color: #047857 !important;
        font-weight: 900 !important;
        box-shadow: 0 2px 6px rgba(5,150,105,0.4) !important;
        transform: scale(1.05);
    }
    .btn-presensi-status.is-active-sakit {
        background-color: #d97706 !important;
        color: #ffffff !important;
        border-color: #b45309 !important;
        font-weight: 900 !important;
        box-shadow: 0 2px 6px rgba(217,119,6,0.4) !important;
        transform: scale(1.05);
    }
    .btn-presensi-status.is-active-izin {
        background-color: #0284c7 !important;
        color: #ffffff !important;
        border-color: #0369a1 !important;
        font-weight: 900 !important;
        box-shadow: 0 2px 6px rgba(2,132,199,0.4) !important;
        transform: scale(1.05);
    }
    .btn-presensi-status.is-active-alfa {
        background-color: #e11d48 !important;
        color: #ffffff !important;
        border-color: #be123c !important;
        font-weight: 900 !important;
        box-shadow: 0 2px 6px rgba(225,29,72,0.4) !important;
        transform: scale(1.05);
    }
    .btn-presensi-status.is-active-bolos {
        background-color: #dc2626 !important;
        color: #ffffff !important;
        border-color: #b91c1c !important;
        font-weight: 900 !important;
        box-shadow: 0 2px 6px rgba(220,38,38,0.4) !important;
        transform: scale(1.05);
    }
    .btn-presensi-status.is-active-dispensasi {
        background-color: #7c3aed !important;
        color: #ffffff !important;
        border-color: #6d28d9 !important;
        font-weight: 900 !important;
        box-shadow: 0 2px 6px rgba(124,58,237,0.4) !important;
        transform: scale(1.05);
    }
</style>
<script>
window.formPresensi = function(dataAwal, totalSiswa) {
    return {
        siswaStatus: dataAwal || {},
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
                'hadir': 'background-color: #059669 !important; color: #ffffff !important; border-color: #047857 !important; font-weight: 900 !important; box-shadow: 0 2px 6px rgba(5,150,105,0.4) !important;',
                'sakit': 'background-color: #d97706 !important; color: #ffffff !important; border-color: #b45309 !important; font-weight: 900 !important; box-shadow: 0 2px 6px rgba(217,119,6,0.4) !important;',
                'izin': 'background-color: #0284c7 !important; color: #ffffff !important; border-color: #0369a1 !important; font-weight: 900 !important; box-shadow: 0 2px 6px rgba(2,132,199,0.4) !important;',
                'alfa': 'background-color: #e11d48 !important; color: #ffffff !important; border-color: #be123c !important; font-weight: 900 !important; box-shadow: 0 2px 6px rgba(225,29,72,0.4) !important;',
                'bolos': 'background-color: #dc2626 !important; color: #ffffff !important; border-color: #b91c1c !important; font-weight: 900 !important; box-shadow: 0 2px 6px rgba(220,38,38,0.4) !important;',
                'dispensasi': 'background-color: #7c3aed !important; color: #ffffff !important; border-color: #6d28d9 !important; font-weight: 900 !important; box-shadow: 0 2px 6px rgba(124,58,237,0.4) !important;'
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
                      keterangan="Input kehadiran siswa secara cepat & pantau rekap pertemuan.">
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
        {{-- Tab Navigasi --}}
        <div class="mb-6 flex border-b border-slate-200">
            <button type="button" @click="pilihTab('input')"
                    class="flex cursor-pointer items-center gap-2 border-b-2 px-5 py-3 text-sm font-bold transition"
                    :class="tab === 'input'
                        ? 'border-primary text-primary'
                        : 'border-transparent text-slate-500 hover:border-slate-300 hover:text-slate-700'">
                <x-heroicon-o-pencil-square class="h-4 w-4"/>
                <span>Input Presensi Langsung</span>
                @if ($sesiMapel->isNotEmpty())
                    <span class="ml-1 rounded-full bg-primary/10 px-2 py-0.5 text-xs font-semibold text-primary">
                        {{ $sesiMapel->count() }} Sesi
                    </span>
                @endif
            </button>
            <button type="button" @click="pilihTab('riwayat')"
                    class="flex cursor-pointer items-center gap-2 border-b-2 px-5 py-3 text-sm font-bold transition"
                    :class="tab === 'riwayat'
                        ? 'border-primary text-primary'
                        : 'border-transparent text-slate-500 hover:border-slate-300 hover:text-slate-700'">
                <x-heroicon-o-clock class="h-4 w-4"/>
                <span>Riwayat Pertemuan</span>
                <span class="ml-1 rounded-full bg-slate-100 px-2 py-0.5 text-xs font-semibold text-slate-600">
                    {{ $daftar->total() }}
                </span>
            </button>
        </div>

        {{-- TAB 1: INPUT PRESENSI LANGSUNG (ALA ABSENSI-SISWA) --}}
        <div x-show="tab === 'input'" x-cloak class="space-y-6">
        {{-- Card Filter Tanggal & Pilihan Kelas --}}
        <div class="kartu p-4 sm:p-5">
            <form method="GET" action="{{ route('presensi.index') }}" id="formTanggal"
                  class="flex flex-wrap items-end gap-3 sm:gap-4">
                <input type="hidden" name="tab" value="input">

                <div>
                    <label for="tanggal" class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-500">
                        Tanggal Pelajaran
                    </label>
                    <div class="flex items-center gap-2">
                        <input id="tanggal" name="tanggal" type="date" value="{{ $tanggal->toDateString() }}"
                               onchange="this.form.submit()"
                               class="rounded-xl border-slate-200 text-sm shadow-sm focus:border-primary focus:ring-primary">
                        <button type="submit"
                                class="rounded-xl bg-slate-900 px-3.5 py-2 text-xs font-bold text-white shadow-sm hover:bg-slate-800 transition">
                            Tampilkan
                        </button>
                        <a href="{{ route('presensi.index', ['tanggal' => today()->toDateString(), 'tab' => 'input']) }}"
                           class="rounded-xl border border-slate-200 bg-white px-3.5 py-2 text-xs font-semibold text-slate-700 shadow-sm hover:bg-slate-50 transition">
                            Hari Ini
                        </a>
                    </div>
                </div>

                {{-- Pilih Kelas Lain / Diluar Jadwal Hari Ini --}}
                <div class="sm:ml-auto">
                    <label for="pilih_kelas" class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-500">
                        Pilih Kelas Lain
                    </label>
                    <select id="pilih_kelas" name="kelas_id" onchange="this.form.submit()"
                            class="rounded-xl border-slate-200 text-sm shadow-sm focus:border-primary focus:ring-primary">
                        <option value="">-- Pilih Kelas Lain --</option>
                        @foreach ($kelasList as $k)
                            <option value="{{ $k->id }}" @selected($kelasAktif?->id === $k->id && $sesiMapel->isEmpty())>
                                {{ $k->nama }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </form>

            <div class="mt-3 flex items-center gap-2 text-xs text-slate-500">
                <x-heroicon-o-calendar class="h-4 w-4 text-slate-400"/>
                <span>Hari <strong>{{ $hari?->label() ?? '—' }}</strong>, {{ Tanggal::lengkap($tanggal) }}</span>
            </div>
        </div>

        {{-- Daftar Sesi Pelajaran Hari Tersebut --}}
        @if ($sesiMapel->isNotEmpty())
            <div>
                <div class="mb-2.5 flex items-center justify-between">
                    <p class="text-xs font-bold uppercase tracking-wider text-slate-400">
                        Sesi Pelajaran Hari {{ $hari?->label() }}
                    </p>
                    <span class="text-xs text-slate-400">{{ $sesiMapel->count() }} Sesi Terjadwal</span>
                </div>

                <div class="flex gap-3 overflow-x-auto pb-2">
                    @foreach ($sesiMapel as $sesi)
                        @php
                            $isAktif = $sesiAktifId === $sesi->id;
                        @endphp
                        <a href="{{ route('presensi.index', ['tanggal' => $tanggal->toDateString(), 'sesi' => $sesi->id, 'tab' => 'input']) }}"
                           class="flex shrink-0 items-center gap-3 rounded-2xl border p-3.5 text-xs font-bold transition shadow-sm text-left {{ $isAktif ? 'border-primary bg-primary text-white shadow-md' : 'border-slate-200 bg-white text-slate-700 hover:bg-slate-50' }}">
                            <div class="grid h-10 w-10 place-items-center rounded-xl text-xs font-black shrink-0 {{ $isAktif ? 'bg-white/20 text-white' : 'bg-primary/10 text-primary' }}">
                                {{ $sesi->label_jp }}
                            </div>
                            <div>
                                <div class="flex items-center gap-2">
                                    <span class="font-bold text-sm tracking-tight">{{ $sesi->kelas_tampilan }}</span>
                                    @if ($sesi->terisi)
                                        <span class="inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-[10px] font-bold {{ $isAktif ? 'bg-emerald-400 text-emerald-950' : 'bg-emerald-100 text-emerald-800' }}">
                                            ✓ Terisi ({{ $sesi->jumlah_hadir }})
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-[10px] font-bold {{ $isAktif ? 'bg-amber-400 text-amber-950' : 'bg-amber-100 text-amber-800' }}">
                                            ! Belum Diisi
                                        </span>
                                    @endif
                                </div>
                                <p class="text-[11px] font-medium opacity-80 mt-0.5">
                                    {{ $sesi->nama_tampilan }}
                                    @if ($sesi->rentang_waktu) · {{ $sesi->rentang_waktu }} WIB @endif
                                </p>
                            </div>
                        </a>
                    @endforeach
                </div>
            </div>
        @elseif (! $kelasAktif)
            <div class="kartu p-8 text-center">
                <x-heroicon-o-calendar-days class="mx-auto h-12 w-12 text-slate-300"/>
                <p class="mt-3 font-bold text-slate-700">Tidak ada jadwal pelajaran pada hari {{ $hari?->label() ?? 'ini' }}</p>
                <p class="text-sm text-slate-500">Pilih tanggal lain di atas atau pilih kelas melalui dropdown "Pilih Kelas Lain".</p>
            </div>
        @endif

        {{-- FORM PRESENSI SISWA (LANGSUNG TAMPIL DENGAN DAFTAR NAMA) --}}
        @if ($kelasAktif)
            @if ($siswas->isEmpty())
                <div class="kartu p-10 text-center">
                    <x-heroicon-o-users class="mx-auto h-12 w-12 text-slate-300"/>
                    <p class="mt-3 font-semibold text-slate-700">Kelas {{ $kelasAktif->nama }} belum memiliki siswa aktif</p>
                    <p class="text-sm text-slate-500">Hubungi admin untuk menambahkan atau mengunggah data siswa di kelas ini.</p>
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
                @endphp

                <form method="POST" action="{{ route('presensi.simpan-langsung') }}"
                      x-data="formPresensi({{ Js::from($dataAwal) }}, {{ $siswas->count() }})" x-init="init()">
                    @csrf
                    <input type="hidden" name="tanggal" value="{{ $tanggal->toDateString() }}">
                    <input type="hidden" name="jadwal_id" value="{{ $jadwalAktif?->id }}">
                    <input type="hidden" name="sesi_id" value="{{ $sesiAktifId }}">

                    {{-- Card Info Sesi & Materi --}}
                    <div class="kartu mb-4 p-4 sm:p-5">
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-4 border-b border-slate-100">
                            <div>
                                <div class="flex items-center gap-2">
                                    <h3 class="text-base sm:text-lg font-bold text-slate-900">
                                        {{ $kelasAktif->nama }} — {{ $jadwalAktif?->nama_tampilan ?? 'Mata Pelajaran' }}
                                    </h3>
                                    @if ($sesiAktif)
                                        <span class="rounded-full bg-primary/10 px-2.5 py-0.5 text-xs font-bold text-primary">
                                            {{ $sesiAktif->label_jp }}
                                        </span>
                                    @endif
                                </div>
                                <p class="text-xs text-slate-500 mt-0.5">
                                    {{ Tanggal::lengkap($tanggal) }}
                                    @if ($sesiAktif?->rentang_waktu) · Pukul {{ $sesiAktif->rentang_waktu }} WIB @endif
                                    · Total {{ $siswas->count() }} Siswa
                                </p>
                            </div>

                            @if ($agendaAktif)
                                <div class="inline-flex items-center gap-1.5 rounded-xl bg-emerald-50 px-3 py-1.5 text-xs font-bold text-emerald-800 border border-emerald-200">
                                    <x-heroicon-s-check-circle class="h-4 w-4 text-emerald-600"/>
                                    Pertemuan ke-{{ $agendaAktif->pertemuan_ke }}
                                </div>
                            @endif
                        </div>

                        {{-- Isian Materi Pertemuan (Praktis, auto-create di agenda) --}}
                        <div class="mt-4 flex flex-col sm:flex-row items-start sm:items-center gap-3">
                            <label for="judul_materi" class="text-xs font-bold text-slate-600 shrink-0">
                                Materi / Topik Pertemuan:
                            </label>
                            <input type="text" id="judul_materi" name="judul_materi"
                                   value="{{ old('judul_materi', $agendaAktif?->judul_materi ?? 'Pembelajaran Tatap Muka') }}"
                                   placeholder="Contoh: Teks Eksplanasi, Latihan Soal Bab 2, dll."
                                   class="w-full sm:max-w-md rounded-xl border-slate-200 text-sm shadow-sm focus:border-primary focus:ring-primary">
                            <span class="text-xs text-slate-400 italic">
                                *Agenda mengajar akan dibuat otomatis saat presensi disimpan
                            </span>
                        </div>
                    </div>

                    {{-- Panduan / Keterangan Singkatan Status (Permintaan User) --}}
                    <div class="kartu mb-4 p-4 bg-white border-slate-200 shadow-sm">
                        <div class="flex items-center gap-2 mb-2.5 text-xs font-bold uppercase tracking-wider text-slate-500">
                            <x-heroicon-o-information-circle class="h-4 w-4 text-primary"/>
                            <span>Keterangan Pilihan Status Kehadiran:</span>
                        </div>
                        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3 text-xs">
                            <div class="status-legend-card" style="background-color: #ecfdf5; border: 1px solid #a7f3d0; display: flex; align-items: center; gap: 12px; padding: 10px 12px; border-radius: 12px;">
                                <span class="status-legend-badge" style="background-color: #059669; color: #ffffff; display: inline-flex; align-items: center; justify-content: center; width: 36px; height: 36px; min-width: 36px; min-height: 36px; border-radius: 10px; font-weight: 900; font-size: 15px; line-height: 1; text-align: center; flex-shrink: 0; box-shadow: 0 2px 4px rgba(5,150,105,0.25);">H</span>
                                <div style="min-width: 0; flex: 1;">
                                    <p class="font-bold leading-tight text-sm" style="color: #065f46;">Hadir</p>
                                    <p class="text-[11px] truncate" style="color: #047857; margin-top: 2px;">Masuk pelajaran</p>
                                </div>
                            </div>
                            <div class="status-legend-card" style="background-color: #fffbeb; border: 1px solid #fde68a; display: flex; align-items: center; gap: 12px; padding: 10px 12px; border-radius: 12px;">
                                <span class="status-legend-badge" style="background-color: #d97706; color: #ffffff; display: inline-flex; align-items: center; justify-content: center; width: 36px; height: 36px; min-width: 36px; min-height: 36px; border-radius: 10px; font-weight: 900; font-size: 15px; line-height: 1; text-align: center; flex-shrink: 0; box-shadow: 0 2px 4px rgba(217,119,6,0.25);">S</span>
                                <div style="min-width: 0; flex: 1;">
                                    <p class="font-bold leading-tight text-sm" style="color: #92400e;">Sakit</p>
                                    <p class="text-[11px] truncate" style="color: #b45309; margin-top: 2px;">Kondisi sakit</p>
                                </div>
                            </div>
                            <div class="status-legend-card" style="background-color: #f0f9ff; border: 1px solid #bae6fd; display: flex; align-items: center; gap: 12px; padding: 10px 12px; border-radius: 12px;">
                                <span class="status-legend-badge" style="background-color: #0284c7; color: #ffffff; display: inline-flex; align-items: center; justify-content: center; width: 36px; height: 36px; min-width: 36px; min-height: 36px; border-radius: 10px; font-weight: 900; font-size: 15px; line-height: 1; text-align: center; flex-shrink: 0; box-shadow: 0 2px 4px rgba(2,132,199,0.25);">I</span>
                                <div style="min-width: 0; flex: 1;">
                                    <p class="font-bold leading-tight text-sm" style="color: #075985;">Izin</p>
                                    <p class="text-[11px] truncate" style="color: #0369a1; margin-top: 2px;">Izin keperluan</p>
                                </div>
                            </div>
                            <div class="status-legend-card" style="background-color: #fff1f2; border: 1px solid #fecdd3; display: flex; align-items: center; gap: 12px; padding: 10px 12px; border-radius: 12px;">
                                <span class="status-legend-badge" style="background-color: #e11d48; color: #ffffff; display: inline-flex; align-items: center; justify-content: center; width: 36px; height: 36px; min-width: 36px; min-height: 36px; border-radius: 10px; font-weight: 900; font-size: 15px; line-height: 1; text-align: center; flex-shrink: 0; box-shadow: 0 2px 4px rgba(225,29,72,0.25);">A</span>
                                <div style="min-width: 0; flex: 1;">
                                    <p class="font-bold leading-tight text-sm" style="color: #9f1239;">Alfa</p>
                                    <p class="text-[11px] truncate" style="color: #be123c; margin-top: 2px;">Tanpa keterangan</p>
                                </div>
                            </div>
                            <div class="status-legend-card" style="background-color: #fef2f2; border: 1px solid #fca5a5; display: flex; align-items: center; gap: 12px; padding: 10px 12px; border-radius: 12px;">
                                <span class="status-legend-badge" style="background-color: #dc2626; color: #ffffff; display: inline-flex; align-items: center; justify-content: center; width: 36px; height: 36px; min-width: 36px; min-height: 36px; border-radius: 10px; font-weight: 900; font-size: 15px; line-height: 1; text-align: center; flex-shrink: 0; box-shadow: 0 2px 4px rgba(220,38,38,0.25);">B</span>
                                <div style="min-width: 0; flex: 1;">
                                    <p class="font-bold leading-tight text-sm" style="color: #991b1b;">Bolos</p>
                                    <p class="text-[11px] truncate" style="color: #b91c1c; margin-top: 2px;">Meninggalkan jam</p>
                                </div>
                            </div>
                            <div class="status-legend-card" style="background-color: #f5f3ff; border: 1px solid #ddd6fe; display: flex; align-items: center; gap: 12px; padding: 10px 12px; border-radius: 12px;">
                                <span class="status-legend-badge" style="background-color: #7c3aed; color: #ffffff; display: inline-flex; align-items: center; justify-content: center; width: 36px; height: 36px; min-width: 36px; min-height: 36px; border-radius: 10px; font-weight: 900; font-size: 15px; line-height: 1; text-align: center; flex-shrink: 0; box-shadow: 0 2px 4px rgba(124,58,237,0.25);">D</span>
                                <div style="min-width: 0; flex: 1;">
                                    <p class="font-bold leading-tight text-sm" style="color: #5b21b6;">Dispensasi</p>
                                    <p class="text-[11px] truncate" style="color: #6d28d9; margin-top: 2px;">Tugas sekolah</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Toolbar Ringkasan & Aksi Cepat --}}
                    <div class="kartu mb-4 p-4 flex flex-wrap items-center justify-between gap-3 bg-slate-50/80">
                        {{-- Live Counter Chips --}}
                        <div class="flex flex-wrap items-center gap-2 text-xs font-bold">
                            <span class="inline-flex items-center gap-1 rounded-xl px-3 py-1.5 shadow-xs" style="background-color: #d1fae5; color: #065f46; border: 1px solid #a7f3d0;">
                                Hadir: <strong class="tabular-nums text-sm font-black" x-text="hadirCount"></strong> / {{ $siswas->count() }}
                            </span>
                            <span x-show="sakitCount > 0" x-cloak class="inline-flex items-center gap-1 rounded-xl px-2.5 py-1.5 shadow-xs" style="background-color: #fef3c7; color: #92400e; border: 1px solid #fde68a;">
                                Sakit: <strong class="tabular-nums font-black" x-text="sakitCount"></strong>
                            </span>
                            <span x-show="izinCount > 0" x-cloak class="inline-flex items-center gap-1 rounded-xl px-2.5 py-1.5 shadow-xs" style="background-color: #e0f2fe; color: #075985; border: 1px solid #bae6fd;">
                                Izin: <strong class="tabular-nums font-black" x-text="izinCount"></strong>
                            </span>
                            <span x-show="alfaCount > 0" x-cloak class="inline-flex items-center gap-1 rounded-xl px-2.5 py-1.5 shadow-xs" style="background-color: #ffe4e6; color: #9f1239; border: 1px solid #fecdd3;">
                                Alfa: <strong class="tabular-nums font-black" x-text="alfaCount"></strong>
                            </span>
                            <span x-show="dispensasiCount > 0" x-cloak class="inline-flex items-center gap-1 rounded-xl px-2.5 py-1.5 shadow-xs" style="background-color: #ede9fe; color: #5b21b6; border: 1px solid #ddd6fe;">
                                Dispensasi: <strong class="tabular-nums font-black" x-text="dispensasiCount"></strong>
                            </span>
                        </div>

                        {{-- Tombol Aksi Cepat --}}
                        <div class="flex items-center gap-2">
                            <button type="button" @click="tandaiSemuaHadir()"
                                    style="background-color: #ecfdf5; color: #065f46; border: 1px solid #a7f3d0;"
                                    class="inline-flex cursor-pointer items-center gap-1.5 rounded-xl px-3.5 py-2 text-xs font-bold shadow-sm hover:brightness-95 transition active:scale-95">
                                <x-heroicon-o-check-circle class="h-4 w-4 shrink-0" style="color: #059669;"/>
                                <span>Tandai Semua Hadir</span>
                            </button>
                            <button type="button" @click="batalTandaiSemua()"
                                    style="background-color: #fff1f2; color: #9f1239; border: 1px solid #fecdd3;"
                                    class="inline-flex cursor-pointer items-center gap-1.5 rounded-xl px-3.5 py-2 text-xs font-bold shadow-sm hover:brightness-95 transition active:scale-95">
                                <x-heroicon-o-x-circle class="h-4 w-4 shrink-0" style="color: #e11d48;"/>
                                <span>Batal Tandai Semua</span>
                            </button>
                        </div>
                    </div>

                    {{-- Tabel Siswa (Ala absensi-siswa) --}}
                    <div class="kartu overflow-hidden">
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-slate-100 text-xs">
                                <thead class="bg-slate-50/80 text-left uppercase tracking-wider text-slate-500 font-bold">
                                    <tr>
                                        <th class="w-12 px-4 py-3 text-center">No</th>
                                        <th class="px-4 py-3">Nama Siswa</th>
                                        <th class="w-24 px-4 py-3 text-center">Hadir</th>
                                        <th class="px-4 py-3">Pilihan Status</th>
                                        <th class="px-4 py-3">Keterangan (Opsional)</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100">
                                    @foreach ($siswas as $s)
                                        <tr class="hover:bg-slate-50/60 transition"
                                            :class="getStatus({{ $s->id }}) !== 'hadir' ? 'bg-amber-50/30' : ''">
                                            {{-- No. Absen --}}
                                            <td class="px-4 py-3 text-center">
                                                <div class="mx-auto grid h-8 w-8 place-items-center rounded-lg bg-slate-100 text-xs font-bold text-slate-700">
                                                    {{ $s->pivot->no_absen ?? $loop->iteration }}
                                                </div>
                                            </td>

                                            {{-- Nama Siswa & NISN --}}
                                            <td class="px-4 py-3">
                                                <p class="font-bold text-slate-900 text-sm tracking-tight">{{ $s->nama }}</p>
                                                <p class="text-[11px] text-slate-400">
                                                    NISN: {{ $s->nisn ?: '-' }} · {{ $s->label_jenis_kelamin }}
                                                </p>
                                            </td>

                                            {{-- Checkbox Hadir Cepat --}}
                                            <td class="px-4 py-3 text-center">
                                                <input type="checkbox"
                                                       :checked="getStatus({{ $s->id }}) === 'hadir'"
                                                       @change="toggleHadir({{ $s->id }}, $event.target.checked)"
                                                       style="accent-color: #059669; width: 1.25rem; height: 1.25rem; cursor: pointer;"
                                                       class="rounded-lg border-slate-300 shadow-sm transition">
                                            </td>

                                            {{-- Pil Status (H, S, I, A, B, D) --}}
                                            <td class="px-4 py-3">
                                                <input type="hidden" name="status[{{ $s->id }}]" :value="getStatus({{ $s->id }})">

                                                <div class="flex flex-wrap items-center gap-1.5">
                                                    @foreach ($statusList as $status)
                                                        <button type="button"
                                                                @click="setStatus({{ $s->id }}, '{{ $status->value }}')"
                                                                class="btn-presensi-status"
                                                                :class="getStatus({{ $s->id }}) === '{{ $status->value }}' ? 'is-active-{{ $status->value }}' : ''"
                                                                :style="getStatus({{ $s->id }}) === '{{ $status->value }}' ? getBtnStyle('{{ $status->value }}') : ''"
                                                                title="{{ $status->label() }}: {{ $status->singkatan() }}">
                                                            {{ $status->singkatan() }}
                                                        </button>
                                                    @endforeach
                                                </div>
                                            </td>

                                            {{-- Input Keterangan --}}
                                            <td class="px-4 py-3">
                                                <input type="text" name="keterangan[{{ $s->id }}]"
                                                       x-model="siswaStatus[{{ $s->id }}].keterangan"
                                                       placeholder="Keterangan (opsional)"
                                                       class="w-full min-w-[12rem] rounded-xl border-slate-200 text-xs shadow-sm placeholder:text-slate-400 focus:border-primary focus:ring-primary">
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        {{-- Footer Simpan Presensi --}}
                        <div class="sticky bottom-0 flex flex-col sm:flex-row items-center justify-between gap-3 border-t border-slate-200 bg-white/95 px-5 py-4 backdrop-blur rounded-b-xl shadow-lg">
                            <div class="text-xs text-slate-500 text-center sm:text-left">
                                Pastikan kehadiran telah sesuai sebelum menekan tombol simpan.
                            </div>
                            <div class="flex items-center gap-2">
                                <button type="submit"
                                        class="inline-flex cursor-pointer items-center gap-2 rounded-xl bg-primary px-6 py-2.5 text-xs font-bold text-white shadow-md hover:bg-primary-700 transition active:scale-95">
                                    <x-heroicon-o-check class="h-4 w-4"/>
                                    <span>Simpan Presensi {{ $kelasAktif->nama }}</span>
                                </button>
                            </div>
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
                    <th class="px-4 py-3">Tanggal</th>
                    <th class="px-4 py-3">Kelas / Mapel</th>
                    <th class="px-4 py-3">Materi</th>
                    <th class="px-4 py-3">Rekap</th>
                    <th class="px-4 py-3 text-right">Aksi</th>
                </tr>
            </x-slot:kepala>

            @forelse ($daftar as $agenda)
                <tr class="hover:bg-slate-50">
                    <td class="whitespace-nowrap px-4 py-3">
                        <p class="font-semibold text-slate-800">{{ Tanggal::angka($agenda->tanggal) }}</p>
                        <p class="text-xs text-slate-500">Pertemuan ke-{{ $agenda->pertemuan_ke }}</p>
                    </td>
                    <td class="px-4 py-3">
                        <p class="font-medium text-slate-800">{{ $agenda->jadwal?->kelas_tampilan ?? '—' }}</p>
                        <p class="text-xs text-slate-500">{{ $agenda->jadwal?->nama_tampilan ?? '—' }}</p>
                    </td>
                    <td class="px-4 py-3 text-slate-700">{{ $agenda->judul_materi }}</td>
                    <td class="px-4 py-3">
                        @if ($agenda->presensis_count > 0)
                            <div class="flex flex-wrap gap-1 text-xs font-semibold">
                                <span class="rounded-full bg-emerald-100 px-2 py-0.5 text-emerald-800">H {{ $agenda->jumlah_hadir }}</span>
                                <span class="rounded-full bg-rose-100 px-2 py-0.5 text-rose-800">A {{ $agenda->jumlah_alfa }}</span>
                                <span class="text-slate-400">dari {{ $agenda->presensis_count }}</span>
                            </div>
                        @else
                            <span class="rounded-full bg-rose-50 px-2 py-0.5 text-xs font-semibold text-rose-600">Belum diisi</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-right">
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
// Pastikan skrip terdaftar dan terintegrasi dengan Livewire / Alpine jika dibutuhkan
if (window.Alpine && window.formPresensi) {
    window.Alpine.data('formPresensi', window.formPresensi);
}
</script>
@endpush

