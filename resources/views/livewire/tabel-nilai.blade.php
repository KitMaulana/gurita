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
    @if ($totalSiswaGrid > 0)
        <div class="mb-4 grid grid-cols-2 gap-3 lg:grid-cols-4">
            <div class="kartu flex items-center gap-3 p-3.5">
                <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-indigo-50 text-primary">
                    <x-heroicon-o-calculator class="h-4.5 w-4.5"/>
                </div>
                <div class="min-w-0">
                    <p class="text-[11px] font-medium text-slate-500 truncate">Rata-rata Kelas</p>
                    <p class="text-lg font-bold text-slate-900 tabular-nums">{{ number_format($rataKelas, 1, ',', '.') }}</p>
                </div>
            </div>
            <div class="kartu flex items-center gap-3 p-3.5">
                <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-teal-50 text-teal-700">
                    <x-heroicon-o-trophy class="h-4.5 w-4.5"/>
                </div>
                <div class="min-w-0">
                    <p class="text-[11px] font-medium text-slate-500 truncate">Nilai Tertinggi</p>
                    <p class="text-lg font-bold text-slate-900 tabular-nums">{{ number_format($nilaiTertinggi, 1, ',', '.') }}</p>
                </div>
            </div>
            <div class="kartu flex items-center gap-3 p-3.5">
                <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-emerald-50 text-emerald-700">
                    <x-heroicon-o-check-badge class="h-4.5 w-4.5"/>
                </div>
                <div class="min-w-0">
                    <p class="text-[11px] font-medium text-slate-500 truncate">Tuntas (≥ {{ $kktp }})</p>
                    <p class="text-lg font-bold text-emerald-700 tabular-nums">{{ $jumlahTuntas }} <span class="text-xs font-normal text-slate-400">/ {{ $totalSiswaGrid }}</span></p>
                </div>
            </div>
            <div class="kartu flex items-center gap-3 p-3.5">
                <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-rose-50 text-rose-700">
                    <x-heroicon-o-exclamation-circle class="h-4.5 w-4.5"/>
                </div>
                <div class="min-w-0">
                    <p class="text-[11px] font-medium text-slate-500 truncate">Belum Tuntas</p>
                    <p class="text-lg font-bold text-rose-600 tabular-nums">{{ $jumlahBelumTuntas }} <span class="text-xs font-normal text-slate-400">Siswa</span></p>
                </div>
            </div>
        </div>
    @endif

    <div class="kartu mb-4 p-3.5 flex flex-col lg:flex-row lg:items-center justify-between gap-3">
        <div class="flex flex-wrap items-center gap-2">
            <div class="relative w-full sm:w-64">
                <x-heroicon-o-magnifying-glass class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-slate-400"/>
                <input type="text" x-model="cari" placeholder="Cari nama siswa..."
                       class="w-full rounded-lg border-slate-200 bg-slate-50/70 pl-9 pr-8 py-1.5 text-xs text-slate-800 placeholder:text-slate-400 focus:bg-white focus:border-primary focus:ring-primary">
                <button type="button" x-show="cari.length > 0" x-cloak @click="cari = ''"
                        class="absolute right-2.5 top-1/2 -translate-y-1/2 text-xs font-semibold text-slate-400 hover:text-slate-600">✕</button>
            </div>

            <div class="flex items-center gap-1 flex-nowrap">
                <button type="button" @click="filterKetuntasan = 'semua'"
                        class="cursor-pointer rounded-lg px-2.5 py-1.5 text-xs font-semibold transition-colors whitespace-nowrap"
                        :class="filterKetuntasan === 'semua' ? 'bg-slate-900 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'">
                    Semua ({{ $totalSiswaGrid }})
                </button>
                <button type="button" @click="filterKetuntasan = 'belum'"
                        class="cursor-pointer rounded-lg px-2.5 py-1.5 text-xs font-semibold transition-colors whitespace-nowrap"
                        :class="filterKetuntasan === 'belum' ? 'bg-rose-600 text-white' : 'bg-rose-50 text-rose-700 border border-rose-200 hover:bg-rose-100'">
                    Di Bawah KKTP ({{ $jumlahBelumTuntas }})
                </button>
                <button type="button" @click="filterKetuntasan = 'tuntas'"
                        class="cursor-pointer rounded-lg px-2.5 py-1.5 text-xs font-semibold transition-colors whitespace-nowrap"
                        :class="filterKetuntasan === 'tuntas' ? 'bg-emerald-600 text-white' : 'bg-emerald-50 text-emerald-700 border border-emerald-200 hover:bg-emerald-100'">
                    Tuntas ({{ $jumlahTuntas }})
                </button>
            </div>
        </div>

        <div class="flex flex-wrap items-center gap-3 text-xs text-slate-500">
            <span class="inline-flex items-center gap-1.5 whitespace-nowrap">
                <span class="h-2.5 w-2.5 rounded-full bg-rose-500"></span> &lt; KKTP ({{ $kktp }})
            </span>
            <span class="inline-flex items-center gap-1 text-emerald-700 font-medium whitespace-nowrap">
                <x-heroicon-o-check-circle class="h-4 w-4 text-emerald-600"/> Auto-Save Aktif
            </span>
            <span class="hidden sm:inline-flex items-center gap-1 rounded bg-slate-100 px-2 py-0.5 text-[11px] font-medium text-slate-600 whitespace-nowrap">
                Enter / Panah : Pindah baris
            </span>
        </div>
    </div>

    <div class="kartu overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead class="bg-slate-50 text-[11px] font-semibold uppercase tracking-wider text-slate-500">
                    <tr>
                        <th class="sticky left-0 z-10 bg-slate-50 px-3 py-3 text-left whitespace-nowrap">No</th>
                        <th class="sticky left-10 z-10 bg-slate-50 px-3 py-3 text-left whitespace-nowrap">Nama Siswa</th>
                        @foreach ($penilaians as $penilaian)
                            <th class="px-2 py-3 text-center align-bottom whitespace-nowrap">
                                <span class="inline-block whitespace-nowrap text-[10px] font-semibold {{ $penilaian->jenis->warna() }} rounded px-1.5 py-0.5">
                                    {{ $penilaian->jenis->labelPendek() }}
                                </span>
                                <span class="mt-1 block max-w-28 truncate normal-case font-semibold text-slate-700" title="{{ $penilaian->nama }}">
                                    {{ $penilaian->nama }}
                                </span>
                                @if ($penilaian->is_remedial)
                                    <span class="block text-[10px] font-medium normal-case text-amber-600">remedial</span>
                                @endif
                                @if ($penilaian->is_terkunci)
                                    <span class="block text-[10px] font-normal normal-case text-slate-400">terkunci</span>
                                @endif
                            </th>
                        @endforeach
                        <th class="bg-slate-100/90 px-2.5 py-3 text-center whitespace-nowrap">Rata F</th>
                        <th class="bg-slate-100/90 px-2.5 py-3 text-center whitespace-nowrap">Rata SL</th>
                        <th class="bg-slate-100/90 px-2.5 py-3 text-center whitespace-nowrap">SA</th>
                        <th class="bg-indigo-50/70 px-3 py-3 text-center text-primary whitespace-nowrap">Nilai Akhir</th>
                        <th class="bg-indigo-50/70 px-2.5 py-3 text-center text-primary whitespace-nowrap">Pred.</th>
                        <th class="bg-indigo-50/70 px-3 py-3 text-center text-primary whitespace-nowrap">Status</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-slate-100 bg-white">
                    @foreach ($siswas as $siswa)
                        @php $r = $rekap[$siswa->id]; @endphp
                        <tr x-show="(filterKetuntasan === 'semua' || (filterKetuntasan === 'tuntas' && {{ $r['is_tuntas'] ? 'true' : 'false' }}) || (filterKetuntasan === 'belum' && {{ ! $r['is_tuntas'] ? 'true' : 'false' }})) && (!cari || {{ Js::from(strtolower($siswa->nama.' '.($siswa->nisn ?? ''))) }}.includes(cari.toLowerCase()))"
                            class="hover:bg-slate-50/80 transition-colors">
                            <td class="sticky left-0 z-10 bg-white px-3 py-2.5 font-medium text-slate-400 tabular-nums whitespace-nowrap">{{ $siswa->pivot->no_absen }}</td>
                            <td class="sticky left-10 z-10 bg-white px-3 py-2.5 whitespace-nowrap">
                                <p class="font-semibold text-slate-800">{{ $siswa->nama }}</p>
                                <p class="text-[11px] text-slate-400">{{ $siswa->nisn }}</p>
                            </td>

                            @foreach ($penilaians as $idxKolom => $penilaian)
                                @php
                                    $nilaiSel = $nilai[$siswa->id][$penilaian->id] ?? null;
                                    $dibawahKktp = is_numeric($nilaiSel) && (float) $nilaiSel < $kktp;
                                    $kunciSel = $siswa->id.'-'.$penilaian->id;
                                @endphp
                                <td class="px-1.5 py-1.5 text-center whitespace-nowrap">
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
                                                   'w-16 rounded-lg border-slate-200 py-1.5 text-center text-sm font-semibold tabular-nums focus:border-primary focus:ring-primary disabled:bg-slate-50 disabled:text-slate-400',
                                                   'nilai-merah border-red-300' => $dibawahKktp,
                                               ])>
                                        @if ($selTersimpan === $kunciSel)
                                            <span class="absolute -right-1.5 -top-1.5 rounded-full bg-white text-emerald-600" title="Tersimpan">
                                                <x-heroicon-s-check-circle class="h-4 w-4"/>
                                            </span>
                                        @endif
                                    </div>
                                    @error("nilai.{$siswa->id}.{$penilaian->id}")
                                        <p class="mt-0.5 text-[10px] font-medium text-rose-600">{{ $message }}</p>
                                    @enderror
                                </td>
                            @endforeach

                            <td class="bg-slate-50/80 px-2.5 py-2.5 text-center text-slate-600 tabular-nums whitespace-nowrap">
                                {{ $r['formatif'] !== null ? number_format($r['formatif'], 1, ',', '.') : '—' }}
                            </td>
                            <td class="bg-slate-50/80 px-2.5 py-2.5 text-center text-slate-600 tabular-nums whitespace-nowrap">
                                {{ $r['sumatif_lingkup'] !== null ? number_format($r['sumatif_lingkup'], 1, ',', '.') : '—' }}
                            </td>
                            <td class="bg-slate-50/80 px-2.5 py-2.5 text-center text-slate-600 tabular-nums whitespace-nowrap">
                                {{ $r['sumatif_akhir'] !== null ? number_format($r['sumatif_akhir'], 1, ',', '.') : '—' }}
                            </td>
                            <td class="bg-indigo-50/40 px-3 py-2.5 text-center whitespace-nowrap">
                                <span class="font-bold text-primary tabular-nums">{{ number_format($r['nilai_akhir'], 1, ',', '.') }}</span>
                                @if ($r['is_sementara'])
                                    <span class="ml-1 rounded bg-amber-100 px-1 py-0.5 text-[9px] font-semibold text-amber-800 whitespace-nowrap">sementara</span>
                                @endif
                            </td>
                            <td class="bg-indigo-50/40 px-2.5 py-2.5 text-center whitespace-nowrap">
                                <x-badge-predikat :predikat="$r['predikat']"/>
                            </td>
                            <td class="bg-indigo-50/40 px-3 py-2.5 text-center whitespace-nowrap">
                                <span @class([
                                    'rounded-md px-2 py-0.5 text-[11px] font-semibold whitespace-nowrap',
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

    <div wire:loading class="mt-2 inline-flex items-center gap-1.5 rounded-lg border border-slate-200 bg-white px-3 py-1 text-xs font-medium text-slate-600">
        Menyimpan perubahan nilai…
    </div>
</div>
