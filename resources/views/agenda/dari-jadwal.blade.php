@extends('layouts.app')
@section('judul', 'Buat Agenda dari Jadwal')

@php use App\Support\Tanggal; @endphp

@section('konten')
    <x-kepala-halaman judul="Buat Agenda dari Jadwal"
                      keterangan="Pilih tanggal, centang jadwal kelas yang terlaksana, lalu isi materinya sekaligus.">
        <x-slot:aksi>
            <x-tombol gaya="halus" ikon="arrow-left" :href="route('agenda.index')">Kembali</x-tombol>
        </x-slot:aksi>
    </x-kepala-halaman>

    <form method="GET" class="kartu mb-6 flex flex-wrap items-end gap-3 p-4">
        <x-bidang label="Tanggal Pertemuan" nama="tanggal" class="min-w-56">
            <x-isian nama="tanggal" tipe="date" :value="$tanggal->toDateString()"/>
        </x-bidang>
        <x-tombol gaya="utama" ikon="calendar-days">Tampilkan Jadwal</x-tombol>
        <p class="ml-auto text-sm text-slate-500">{{ Tanggal::lengkap($tanggal) }}</p>
    </form>

    @if (! $hari)
        <div class="kartu p-10 text-center">
            <x-heroicon-o-face-smile class="mx-auto h-12 w-12 text-slate-300"/>
            <p class="mt-3 font-semibold text-slate-700">Tanggal yang dipilih jatuh pada hari Minggu</p>
            <p class="text-sm text-slate-500">Tidak ada jadwal mengajar pada hari Minggu.</p>
        </div>
    @elseif ($sesiList->isEmpty())
        <div class="kartu p-10 text-center">
            <x-heroicon-o-calendar-days class="mx-auto h-12 w-12 text-slate-300"/>
            <p class="mt-3 font-semibold text-slate-700">Tidak ada jadwal pada hari {{ $hari->label() }}</p>
            <p class="text-sm text-slate-500">Silakan pilih tanggal lain.</p>
        </div>
    @else
        <form method="POST" action="{{ route('agenda.simpan-massal') }}" x-data="{ semua: false }">
            @csrf
            <input type="hidden" name="tanggal" value="{{ $tanggal->toDateString() }}">

            <div class="mb-3 flex items-center justify-between">
                <label class="flex cursor-pointer items-center gap-2 text-sm font-semibold text-slate-700">
                    <input type="checkbox" x-model="semua"
                           @change="$root.querySelectorAll('input[name=\'pilih[]\']:not(:disabled)').forEach(c => c.checked = semua)"
                           class="h-4 w-4 rounded border-slate-300 text-accent focus:ring-accent">
                    Centang semua sesi
                </label>
                <span class="text-sm text-slate-500">{{ $sesiList->count() }} sesi ({{ $jadwals->count() }} JP) pada {{ $hari->label() }}</span>
            </div>

            <div class="space-y-4">
                @foreach ($sesiList as $sesi)
                    @php
                        $sudah = $sesi->agenda;
                        $babs = $babList[$sesi->primary_schedule_id] ?? collect();
                        $warna = $sesi->warna_mapel;
                    @endphp

                    <div @class([
                        'kartu p-4 transition-all',
                        'border-emerald-200 bg-emerald-50/40' => $sudah,
                    ]) style="border-left: 5px solid {{ $warna['accent'] }};">
                        <div class="flex flex-wrap items-start gap-3">
                            <label class="flex cursor-pointer items-center pt-1">
                                <input type="checkbox" name="pilih[]" value="{{ $sesi->primary_schedule_id }}"
                                       @checked(old('pilih') && in_array($sesi->primary_schedule_id, (array) old('pilih')))
                                       class="h-5 w-5 rounded border-slate-300 text-accent focus:ring-accent">
                            </label>

                            <div class="min-w-0 flex-1">
                                <div class="flex flex-wrap items-center gap-2">
                                    <span class="inline-flex items-center justify-center rounded px-2.5 py-1 text-xs font-black shadow-2xs"
                                          style="background-color: {{ $warna['jp_bg'] }}; color: {{ $warna['jp_text'] }};">
                                        {{ $sesi->label_jp }}
                                    </span>
                                    <span class="font-extrabold text-slate-900 text-base">{{ $sesi->kelas_tampilan }}</span>
                                    <span class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-0.5 text-xs font-semibold"
                                          style="background-color: {{ $warna['badge_bg'] }}; color: {{ $warna['badge_text'] }}; border: 1px solid {{ $warna['border'] }};">
                                        <span class="h-1.5 w-1.5 rounded-full shrink-0" style="background-color: {{ $warna['dot'] }};"></span>
                                        <span>{{ $sesi->nama_tampilan }}</span>
                                    </span>
                                </div>
                                <p class="text-xs text-slate-500 mt-1">
                                    {{ $sesi->jam }} @if ($sesi->ruang) · Ruang {{ $sesi->ruang }} @endif
                                    @if ($sudah)
                                        <span class="ml-1 font-semibold text-emerald-700">
                                            (Agenda sudah ada — pertemuan ke-{{ $sudah->pertemuan_ke }}, akan diperbarui)
                                        </span>
                                    @endif
                                </p>

                                <div class="mt-3 grid gap-3 md:grid-cols-2">
                                    <x-bidang label="Judul Materi" :nama="'agenda.'.$sesi->primary_schedule_id.'.judul_materi'" :wajib="true">
                                        <input type="text" name="agenda[{{ $sesi->primary_schedule_id }}][judul_materi]"
                                               list="bab-{{ $sesi->primary_schedule_id }}"
                                               value="{{ old('agenda.'.$sesi->primary_schedule_id.'.judul_materi', $sudah?->judul_materi) }}"
                                               placeholder="mis. Menulis Teks Editorial"
                                               class="block w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-primary focus:ring-primary">
                                        <datalist id="bab-{{ $sesi->primary_schedule_id }}">
                                            @foreach ($babs as $bab)
                                                <option value="{{ $bab->judul }}"></option>
                                                @foreach ($bab->tujuanPembelajarans as $tp)
                                                    <option value="{{ $tp->deskripsi }}"></option>
                                                @endforeach
                                            @endforeach
                                        </datalist>
                                    </x-bidang>

                                    <x-bidang label="Bab / Lingkup Materi" :nama="'agenda.'.$sesi->primary_schedule_id.'.bab_id'">
                                        <select name="agenda[{{ $sesi->primary_schedule_id }}][bab_id]"
                                                class="block w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-primary focus:ring-primary">
                                            <option value="">— Tidak ditautkan —</option>
                                            @foreach ($babs as $bab)
                                                <option value="{{ $bab->id }}" @selected(($sudah?->bab_id ?? old('agenda.'.$sesi->primary_schedule_id.'.bab_id')) === $bab->id)>
                                                    {{ $bab->kode }} — {{ $bab->judul }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </x-bidang>

                                    <x-bidang label="Metode" :nama="'agenda.'.$sesi->primary_schedule_id.'.metode'">
                                        <input type="text" name="agenda[{{ $sesi->primary_schedule_id }}][metode]"
                                               value="{{ old('agenda.'.$sesi->primary_schedule_id.'.metode', $sudah?->metode) }}"
                                               placeholder="mis. Diskusi kelompok"
                                               class="block w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-primary focus:ring-primary">
                                    </x-bidang>

                                    <x-bidang label="Status Pertemuan" :nama="'agenda.'.$sesi->primary_schedule_id.'.status'" :wajib="true">
                                        <select name="agenda[{{ $sesi->primary_schedule_id }}][status]"
                                                class="block w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-primary focus:ring-primary">
                                            @foreach ($statusList as $nilai => $label)
                                                <option value="{{ $nilai }}"
                                                    @selected(($sudah?->status?->value ?? old('agenda.'.$sesi->primary_schedule_id.'.status', 'terlaksana')) === $nilai)>{{ $label }}</option>
                                            @endforeach
                                        </select>
                                    </x-bidang>

                                    <x-bidang label="Uraian Kegiatan" :nama="'agenda.'.$sesi->primary_schedule_id.'.uraian_kegiatan'" class="md:col-span-2">
                                        <textarea name="agenda[{{ $sesi->primary_schedule_id }}][uraian_kegiatan]" rows="2"
                                                  placeholder="Ringkasan kegiatan pembelajaran"
                                                  class="block w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-primary focus:ring-primary">{{ old('agenda.'.$sesi->primary_schedule_id.'.uraian_kegiatan', $sudah?->uraian_kegiatan) }}</textarea>
                                    </x-bidang>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="mt-5 flex justify-end gap-2">
                <x-tombol gaya="halus" :href="route('agenda.index')">Batal</x-tombol>
                <x-tombol gaya="aksen" ikon="check">Simpan Agenda Terpilih</x-tombol>
            </div>
        </form>
    @endif
@endsection
