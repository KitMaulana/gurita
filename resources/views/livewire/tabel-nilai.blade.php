<div>
    <div class="mb-3 flex flex-wrap items-center gap-3 text-xs text-slate-500">
        <span class="inline-flex items-center gap-1.5">
            <span class="h-3 w-3 rounded bg-red-100"></span> Nilai di bawah KKTP ({{ $kktp }})
        </span>
        <span class="inline-flex items-center gap-1.5">
            <x-heroicon-o-check-circle class="h-4 w-4 text-emerald-500"/> Tersimpan otomatis saat keluar dari kotak isian
        </span>
        <span class="inline-flex items-center gap-1.5">
            <span class="rounded bg-amber-100 px-1.5 py-0.5 font-semibold text-amber-800">sementara</span>
            Komponen penilaian belum lengkap
        </span>
    </div>

    <div class="kartu overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead class="bg-slate-50 text-xs font-bold uppercase tracking-wide text-slate-500">
                    <tr>
                        <th class="sticky left-0 z-10 bg-slate-50 px-3 py-3 text-left">No</th>
                        <th class="sticky left-10 z-10 bg-slate-50 px-3 py-3 text-left">Nama Siswa</th>
                        @foreach ($penilaians as $penilaian)
                            <th class="px-2 py-3 text-center align-bottom">
                                <span class="block whitespace-nowrap text-[10px] font-semibold {{ $penilaian->jenis->warna() }} rounded px-1.5 py-0.5">
                                    {{ $penilaian->jenis->labelPendek() }}
                                </span>
                                <span class="mt-1 block max-w-24 truncate normal-case" title="{{ $penilaian->nama }}">
                                    {{ $penilaian->nama }}
                                </span>
                                @if ($penilaian->is_remedial)
                                    <span class="block text-[10px] font-normal normal-case text-amber-600">remedial</span>
                                @endif
                                @if ($penilaian->is_terkunci)
                                    <span class="block text-[10px] font-normal normal-case text-slate-400">terkunci</span>
                                @endif
                            </th>
                        @endforeach
                        <th class="bg-slate-100 px-2 py-3 text-center">Rata F</th>
                        <th class="bg-slate-100 px-2 py-3 text-center">Rata SL</th>
                        <th class="bg-slate-100 px-2 py-3 text-center">SA</th>
                        <th class="bg-primary-50 px-2 py-3 text-center text-primary">Nilai Akhir</th>
                        <th class="bg-primary-50 px-2 py-3 text-center text-primary">Pred.</th>
                        <th class="bg-primary-50 px-2 py-3 text-center text-primary">Status</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-slate-100 bg-white">
                    @foreach ($siswas as $siswa)
                        @php $r = $rekap[$siswa->id]; @endphp
                        <tr class="hover:bg-slate-50">
                            <td class="sticky left-0 z-10 bg-white px-3 py-2 text-slate-500">{{ $siswa->pivot->no_absen }}</td>
                            <td class="sticky left-10 z-10 bg-white px-3 py-2">
                                <p class="whitespace-nowrap font-medium text-slate-800">{{ $siswa->nama }}</p>
                                <p class="text-[11px] text-slate-400">{{ $siswa->nisn }}</p>
                            </td>

                            @foreach ($penilaians as $penilaian)
                                @php
                                    $nilaiSel = $nilai[$siswa->id][$penilaian->id] ?? null;
                                    $dibawahKktp = is_numeric($nilaiSel) && (float) $nilaiSel < $kktp;
                                    $kunciSel = $siswa->id.'-'.$penilaian->id;
                                @endphp
                                <td class="px-1 py-1.5 text-center">
                                    <div class="relative">
                                        <input type="text" inputmode="decimal"
                                               wire:model="nilai.{{ $siswa->id }}.{{ $penilaian->id }}"
                                               wire:change="simpanSel({{ $siswa->id }}, {{ $penilaian->id }})"
                                               @disabled($penilaian->is_terkunci || ! auth()->user()->can('update', $penilaian))
                                               @class([
                                                   'w-16 rounded-lg border-slate-200 py-1.5 text-center text-sm focus:border-primary focus:ring-primary disabled:bg-slate-50 disabled:text-slate-400',
                                                   'nilai-merah border-red-200' => $dibawahKktp,
                                               ])>
                                        @if ($selTersimpan === $kunciSel)
                                            <span class="absolute -right-1 -top-1 text-emerald-500" title="Tersimpan">
                                                <x-heroicon-s-check-circle class="h-4 w-4"/>
                                            </span>
                                        @endif
                                    </div>
                                    @error("nilai.{$siswa->id}.{$penilaian->id}")
                                        <p class="mt-0.5 text-[10px] text-rose-600">{{ $message }}</p>
                                    @enderror
                                </td>
                            @endforeach

                            <td class="bg-slate-50 px-2 py-2 text-center text-slate-600">
                                {{ $r['formatif'] !== null ? number_format($r['formatif'], 1, ',', '.') : '—' }}
                            </td>
                            <td class="bg-slate-50 px-2 py-2 text-center text-slate-600">
                                {{ $r['sumatif_lingkup'] !== null ? number_format($r['sumatif_lingkup'], 1, ',', '.') : '—' }}
                            </td>
                            <td class="bg-slate-50 px-2 py-2 text-center text-slate-600">
                                {{ $r['sumatif_akhir'] !== null ? number_format($r['sumatif_akhir'], 1, ',', '.') : '—' }}
                            </td>
                            <td class="bg-primary-50 px-2 py-2 text-center">
                                <span class="font-bold text-primary">{{ number_format($r['nilai_akhir'], 1, ',', '.') }}</span>
                                @if ($r['is_sementara'])
                                    <span class="ml-1 rounded bg-amber-100 px-1 py-0.5 text-[9px] font-semibold text-amber-800">sementara</span>
                                @endif
                            </td>
                            <td class="bg-primary-50 px-2 py-2 text-center">
                                <x-badge-predikat :predikat="$r['predikat']"/>
                            </td>
                            <td class="bg-primary-50 px-2 py-2 text-center">
                                <span @class([
                                    'rounded-full px-2 py-0.5 text-[11px] font-semibold',
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

    <div wire:loading class="mt-2 text-xs text-slate-500">Menyimpan…</div>
</div>
