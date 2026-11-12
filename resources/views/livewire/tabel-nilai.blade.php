@php
    $koleksiRekap = collect($rekap);
    $totalSiswaGrid = $siswas->count();
    $rataKelas = $totalSiswaGrid > 0 ? $koleksiRekap->avg('nilai_akhir') : 0;
    $nilaiTertinggi = $totalSiswaGrid > 0 ? $koleksiRekap->max('nilai_akhir') : 0;
    $jumlahTuntas = $koleksiRekap->where('is_tuntas', true)->count();
    $jumlahBelumTuntas = $totalSiswaGrid - $jumlahTuntas;
@endphp

<div x-data="{
    cari: '',
    filterKetuntasan: 'semua',
    pindahBaris(e, idxKolom, arah) {
        const inputs = Array.from(this.$root.querySelectorAll(`input[data-kolom='${idxKolom}']:not(:disabled)`)).filter(el => el.offsetParent !== null);
        const pos = inputs.indexOf(e.target);
        if (pos !== -1 && inputs[pos + arah]) {
            e.preventDefault();
            inputs[pos + arah].focus();
            inputs[pos + arah].select();
        }
    }
}">
    {{-- Ringkasan Statistik Nilai Kelas --}}
    @if ($totalSiswaGrid > 0)
        <div class="mb-4 grid grid-cols-2 gap-3 lg:grid-cols-4">
            <div class="kartu flex items-center gap-3 p-3.5">
                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-indigo-50 text-primary">
                    <x-heroicon-o-calculator class="h-5 w-5"/>
                </div>
                <div>
                    <p class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Rata-rata Kelas</p>
                    <p class="text-lg font-extrabold text-slate-900 tabular-nums">{{ number_format($rataKelas, 1, ',', '.') }}</p>
                </div>
            </div>
            <div class="kartu flex items-center gap-3 p-3.5">
                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-teal-50 text-accent">
                    <x-heroicon-o-trophy class="h-5 w-5"/>
                </div>
                <div>
                    <p class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Nilai Tertinggi</p>
                    <p class="text-lg font-extrabold text-slate-900 tabular-nums">{{ number_format($nilaiTertinggi, 1, ',', '.') }}</p>
                </div>
            </div>
            <div class="kartu flex items-center gap-3 p-3.5">
                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-emerald-50 text-emerald-600">
                    <x-heroicon-o-check-badge class="h-5 w-5"/>
                </div>
                <div>
                    <p class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Sudah Tuntas (≥ {{ $kktp }})</p>
                    <p class="text-lg font-extrabold text-emerald-700 tabular-nums">{{ $jumlahTuntas }} <span class="text-xs font-semibold text-slate-400">/ {{ $totalSiswaGrid }}</span></p>
                </div>
            </div>
            <div class="kartu flex items-center gap-3 p-3.5">
                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-rose-50 text-rose-600">
                    <x-heroicon-o-exclamation-circle class="h-5 w-5"/>
                </div>
                <div>
                    <p class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Perlu Remedial (< {{ $kktp }})</p>
                    <p class="text-lg font-extrabold text-rose-600 tabular-nums">{{ $jumlahBelumTuntas }} <span class="text-xs font-semibold text-slate-400">Siswa</span></p>
                </div>
            </div>
        </div>
    @endif

    {{-- Toolbar Pencarian, Filter & Panduan Keyboard --}}
    <div class="kartu mb-4 p-3.5 flex flex-col lg:flex-row lg:items-center justify-between gap-3">
        <div class="flex flex-wrap items-center gap-2">
            <div class="relative w-full sm:w-64">
                <x-heroicon-o-magnifying-glass class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-slate-400"/>
                <input type="text" x-model="cari" placeholder="Cari cepat nama siswa..."
                       class="w-full rounded-xl border-slate-200 bg-slate-50/70 pl-9 pr-8 py-1.5 text-xs font-medium text-slate-800 placeholder:text-slate-400 focus:bg-white focus:border-primary focus:ring-primary">
                <button type="button" x-show="cari.length > 0" x-cloak @click="cari = ''"
                        class="absolute right-2.5 top-1/2 -translate-y-1/2 text-xs font-bold text-slate-400 hover:text-slate-600">✕</button>
            </div>

            <div class="flex items-center gap-1">
                <button type="button" @click="filterKetuntasan = 'semua'"
                        class="rounded-xl px-3 py-1.5 text-xs font-bold transition"
                        :class="filterKetuntasan === 'semua' ? 'bg-primary text-white shadow-2xs' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'">
                    Semua ({{ $totalSiswaGrid }})
                </button>
                <button type="button" @click="filterKetuntasan = 'belum'"
                        class="rounded-xl px-3 py-1.5 text-xs font-bold transition"
                        :class="filterKetuntasan === 'belum' ? 'bg-rose-600 text-white shadow-2xs' : 'bg-rose-50 text-rose-700 border border-rose-200 hover:bg-rose-100'">
                    Di Bawah KKTP ({{ $jumlahBelumTuntas }})
                </button>
                <button type="button" @click="filterKetuntasan = 'tuntas'"
                        class="rounded-xl px-3 py-1.5 text-xs font-bold transition"
                        :class="filterKetuntasan === 'tuntas' ? 'bg-emerald-600 text-white shadow-2xs' : 'bg-emerald-50 text-emerald-700 border border-emerald-200 hover:bg-emerald-100'">
                    Tuntas ({{ $jumlahTuntas }})
                </button>
            </div>
        </div>

        <div class="flex flex-wrap items-center gap-3 text-xs text-slate-500">
            <span class="inline-flex items-center gap-1.5">
                <span class="h-2.5 w-2.5 rounded-full bg-rose-500"></span> &lt; KKTP ({{ $kktp }})
            </span>
            <span class="inline-flex items-center gap-1.5 text-emerald-700 font-semibold">
                <x-heroicon-o-check-circle class="h-4 w-4 text-emerald-500"/> Auto-Save Aktif
            </span>
            <span class="hidden sm:inline-flex items-center gap-1 rounded-lg bg-slate-100 px-2 py-1 text-[11px] font-semibold text-slate-600">
                ⌨️ Enter / ↓ : Pindah baris
            </span>
        </div>
    </div>

    <div class="kartu overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead class="bg-slate-50/95 text-xs font-extrabold uppercase tracking-wider text-slate-500">
                    <tr>
                        <th class="sticky left-0 z-10 bg-slate-50 px-3 py-3.5 text-left">No</th>
                        <th class="sticky left-10 z-10 bg-slate-50 px-3 py-3.5 text-left">Nama Siswa</th>
                        @foreach ($penilaians as $penilaian)
                            <th class="px-2 py-3 text-center align-bottom">
                                <span class="inline-block whitespace-nowrap text-[10px] font-extrabold {{ $penilaian->jenis->warna() }} rounded-md px-1.5 py-0.5">
                                    {{ $penilaian->jenis->labelPendek() }}
                                </span>
                                <span class="mt-1 block max-w-28 truncate normal-case font-bold text-slate-700" title="{{ $penilaian->nama }}">
                                    {{ $penilaian->nama }}
                                </span>
                                @if ($penilaian->is_remedial)
                                    <span class="block text-[10px] font-semibold normal-case text-amber-600">remedial</span>
                                @endif
                                @if ($penilaian->is_terkunci)
                                    <span class="block text-[10px] font-normal normal-case text-slate-400">🔒 terkunci</span>
                                @endif
                            </th>
                        @endforeach
                        <th class="bg-slate-100/90 px-2.5 py-3 text-center">Rata F</th>
                        <th class="bg-slate-100/90 px-2.5 py-3 text-center">Rata SL</th>
                        <th class="bg-slate-100/90 px-2.5 py-3 text-center">SA</th>
                        <th class="bg-primary-50 px-3 py-3 text-center text-primary">Nilai Akhir</th>
                        <th class="bg-primary-50 px-2.5 py-3 text-center text-primary">Pred.</th>
                        <th class="bg-primary-50 px-3 py-3 text-center text-primary">Status</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-slate-100 bg-white">
                    @foreach ($siswas as $siswa)
                        @php $r = $rekap[$siswa->id]; @endphp
                        <tr x-show="(filterKetuntasan === 'semua' || (filterKetuntasan === 'tuntas' && {{ $r['is_tuntas'] ? 'true' : 'false' }}) || (filterKetuntasan === 'belum' && {{ ! $r['is_tuntas'] ? 'true' : 'false' }})) && (!cari || {{ Js::from(strtolower($siswa->nama.' '.($siswa->nisn ?? ''))) }}.includes(cari.toLowerCase()))"
                            class="hover:bg-indigo-50/25 transition">
                            <td class="sticky left-0 z-10 bg-white px-3 py-2.5 font-bold text-slate-400">{{ $siswa->pivot->no_absen }}</td>
                            <td class="sticky left-10 z-10 bg-white px-3 py-2.5">
                                <p class="whitespace-nowrap font-bold text-slate-800">{{ $siswa->nama }}</p>
                                <p class="text-[11px] text-slate-400">{{ $siswa->nisn }}</p>
                            </td>

                            @foreach ($penilaians as $idxKolom => $penilaian)
                                @php
                                    $nilaiSel = $nilai[$siswa->id][$penilaian->id] ?? null;
                                    $dibawahKktp = is_numeric($nilaiSel) && (float) $nilaiSel < $kktp;
                                    $kunciSel = $siswa->id.'-'.$penilaian->id;
                                @endphp
                                <td class="px-1.5 py-1.5 text-center">
                                    <div class="relative inline-block">
                                        <input type="text" inputmode="decimal"
                                               data-kolom="{{ $idxKolom }}"
                                               @keydown.enter="pindahBaris($event, {{ $idxKolom }}, 1)"
                                               @keydown.arrow-down="pindahBaris($event, {{ $idxKolom }}, 1)"
                                               @keydown.arrow-up="pindahBaris($event, {{ $idxKolom }}, -1)"
                                               wire:model="nilai.{{ $siswa->id }}.{{ $penilaian->id }}"
                                               wire:change="simpanSel({{ $siswa->id }}, {{ $penilaian->id }})"
                                               @disabled($penilaian->is_terkunci || ! auth()->user()->can('update', $penilaian))
                                               @class([
                                                   'w-16 rounded-xl border-slate-200 py-1.5 text-center text-sm font-bold tabular-nums shadow-2xs transition focus:border-primary focus:ring-primary disabled:bg-slate-50 disabled:text-slate-400',
                                                   'nilai-merah border-red-300' => $dibawahKktp,
                                               ])>
                                        @if ($selTersimpan === $kunciSel)
                                            <span class="absolute -right-1.5 -top-1.5 rounded-full bg-white text-emerald-500 shadow-2xs" title="Tersimpan">
                                                <x-heroicon-s-check-circle class="h-4 w-4"/>
                                            </span>
                                        @endif
                                    </div>
                                    @error("nilai.{$siswa->id}.{$penilaian->id}")
                                        <p class="mt-0.5 text-[10px] font-semibold text-rose-600">{{ $message }}</p>
                                    @enderror
                                </td>
                            @endforeach

                            <td class="bg-slate-50/80 px-2.5 py-2.5 text-center font-semibold text-slate-600 tabular-nums">
                                {{ $r['formatif'] !== null ? number_format($r['formatif'], 1, ',', '.') : '—' }}
                            </td>
                            <td class="bg-slate-50/80 px-2.5 py-2.5 text-center font-semibold text-slate-600 tabular-nums">
                                {{ $r['sumatif_lingkup'] !== null ? number_format($r['sumatif_lingkup'], 1, ',', '.') : '—' }}
                            </td>
                            <td class="bg-slate-50/80 px-2.5 py-2.5 text-center font-semibold text-slate-600 tabular-nums">
                                {{ $r['sumatif_akhir'] !== null ? number_format($r['sumatif_akhir'], 1, ',', '.') : '—' }}
                            </td>
                            <td class="bg-primary-50/60 px-3 py-2.5 text-center">
                                <span class="font-extrabold text-primary tabular-nums">{{ number_format($r['nilai_akhir'], 1, ',', '.') }}</span>
                                @if ($r['is_sementara'])
                                    <span class="ml-1 rounded bg-amber-100 px-1 py-0.5 text-[9px] font-bold text-amber-800">sementara</span>
                                @endif
                            </td>
                            <td class="bg-primary-50/60 px-2.5 py-2.5 text-center">
                                <x-badge-predikat :predikat="$r['predikat']"/>
                            </td>
                            <td class="bg-primary-50/60 px-3 py-2.5 text-center">
                                <span @class([
                                    'rounded-full px-2.5 py-0.5 text-[11px] font-extrabold',
                                    'bg-emerald-100 text-emerald-800' => $r['is_tuntas'],
                                    'bg-rose-100 text-rose-800' => ! $r['is_tuntas'],
                                ])>{{ $r['is_tuntas'] ? 'Tuntas' : 'Belum' }}</span>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <div wire:loading class="mt-2.5 inline-flex items-center gap-1.5 rounded-xl bg-indigo-50 px-3 py-1.5 text-xs font-bold text-primary">
        <span class="h-2 w-2 rounded-full bg-primary animate-ping"></span>
        Menyimpan perubahan nilai…
    </div>
</div>
