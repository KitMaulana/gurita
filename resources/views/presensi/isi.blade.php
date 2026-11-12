@extends('layouts.app')
@section('judul', 'Isi Presensi')

@php use App\Support\Tanggal; @endphp

@section('konten')
    <x-kepala-halaman :judul="'Presensi '.($agenda->jadwal?->kelas_tampilan ?? 'Kelas')"
                      :keterangan="($agenda->jadwal?->nama_tampilan ?? 'Mata Pelajaran').' · '.Tanggal::lengkap($agenda->tanggal).' · Pertemuan ke-'.$agenda->pertemuan_ke">
        <x-slot:aksi>
            <x-tombol gaya="garis" ikon="pencil-square" :href="route('agenda.edit', $agenda)">Ubah Agenda</x-tombol>
            <x-tombol gaya="halus" ikon="arrow-left" :href="route('presensi.index')">Kembali</x-tombol>
        </x-slot:aksi>
    </x-kepala-halaman>

    <div class="kartu mb-5 flex flex-wrap items-center justify-between gap-3 p-4 sm:p-5 bg-gradient-to-r from-indigo-50/70 via-white to-teal-50/40">
        <div class="flex items-center gap-3">
            <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-primary text-white shadow-sm shadow-primary/25">
                <x-heroicon-o-book-open class="h-5 w-5"/>
            </div>
            <div>
                <p class="text-[11px] font-extrabold uppercase tracking-wider text-slate-400">Materi Pertemuan Ke-{{ $agenda->pertemuan_ke }}</p>
                <p class="text-base font-extrabold text-slate-900">{{ $agenda->judul_materi }}</p>
            </div>
        </div>
        <div class="flex items-center gap-2 text-xs font-bold text-slate-600">
            <span class="rounded-xl bg-white px-3 py-1.5 border border-slate-200 shadow-2xs">
                Total: <strong>{{ $siswas->count() }} Siswa</strong>
            </span>
        </div>
    </div>

    @if (! $bisaSunting)
        <div class="mb-4 flex items-center gap-2.5 rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3.5 text-sm font-medium text-amber-900">
            <x-heroicon-o-lock-closed class="h-5 w-5 text-amber-600 shrink-0"/>
            <span>Pertemuan ini terkunci (rapor sudah dibekukan). Data hanya dapat dilihat.</span>
        </div>
    @endif

    <form method="POST" action="{{ route('presensi.simpan', $agenda) }}"
          x-data="{
              cari: '',
              statusList: {{ Js::from(collect($statusList)->map(fn ($s) => $s->value)) }},
              tandaiSemuaHadir() {
                  this.$root.querySelectorAll('input[type=radio][value=hadir]').forEach(r => {
                      r.checked = true;
                      r.dispatchEvent(new Event('change'));
                  });
              },
              batalTandaiSemua() {
                  this.$root.querySelectorAll('input[type=radio][value=alfa]').forEach(r => {
                      r.checked = true;
                      r.dispatchEvent(new Event('change'));
                  });
              }
          }">
        @csrf

        <div class="kartu tanpa-cetak mb-4 p-4 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            @if ($bisaSunting)
                <div class="flex flex-wrap items-center gap-2">
                    <x-tombol tipe="button" gaya="aksen" ikon="check-circle" @click="tandaiSemuaHadir()">
                        Tandai Semua Hadir
                    </x-tombol>
                    <button type="button" @click="batalTandaiSemua()"
                            style="background-color: #fff1f2; color: #9f1239; border: 1px solid #fecdd3;"
                            class="inline-flex cursor-pointer items-center gap-1.5 rounded-xl px-3.5 py-2 text-xs font-bold shadow-2xs hover:brightness-95 transition active:scale-95">
                        <x-heroicon-o-x-circle class="h-4 w-4 shrink-0" style="color: #e11d48;"/>
                        <span>Batal Tandai Semua</span>
                    </button>
                </div>
            @endif

            {{-- Pencarian Cepat Nama Siswa --}}
            <div class="relative flex-1 sm:max-w-xs">
                <x-heroicon-o-magnifying-glass class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-slate-400"/>
                <input type="text" x-model="cari"
                       placeholder="Cari nama siswa atau NISN..."
                       class="w-full rounded-xl border-slate-200 bg-slate-50/70 pl-9 pr-8 py-2 text-xs font-medium text-slate-800 placeholder:text-slate-400 focus:bg-white focus:border-primary focus:ring-primary">
                <button type="button" x-show="cari.length > 0" x-cloak @click="cari = ''"
                        class="absolute right-2.5 top-1/2 -translate-y-1/2 text-xs font-bold text-slate-400 hover:text-slate-600">
                    ✕
                </button>
            </div>
        </div>

        <div class="space-y-2.5">
            @forelse ($siswas as $siswa)
                @php
                    $tersimpan = $presensi->get($siswa->id);
                    $statusTerpilih = old("status.{$siswa->id}", $tersimpan?->status?->value ?? 'hadir');
                    $ketTersimpan = old("keterangan.{$siswa->id}", $tersimpan?->keterangan ?? '');
                @endphp

                <div class="kartu p-3.5 transition"
                     data-baris
                     x-data="{ status: '{{ $statusTerpilih }}', ket: {{ Js::from($ketTersimpan) }} }"
                     x-show="!cari || {{ Js::from(strtolower($siswa->nama.' '.($siswa->nisn ?? ''))) }}.includes(cari.toLowerCase())"
                     :class="status !== 'hadir' ? 'ring-1 ring-amber-300 bg-amber-50/30' : ''">
                    <div class="flex flex-wrap items-center gap-3">
                        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-indigo-50 text-sm font-extrabold text-primary">
                            {{ $siswa->pivot->no_absen }}
                        </div>

                        <div class="min-w-0 flex-1">
                            <p class="truncate font-bold text-slate-900">{{ $siswa->nama }}</p>
                            <p class="text-xs text-slate-500">{{ $siswa->nisn }} · {{ $siswa->label_jenis_kelamin }}</p>
                        </div>

                        {{-- Tombol status besar (bukan dropdown) agar nyaman di ponsel --}}
                        <div class="flex flex-wrap gap-1.5">
                            @foreach ($statusList as $status)
                                <label @class([
                                    'tombol-sentuh flex cursor-pointer items-center justify-center rounded-xl border px-3.5 text-sm font-extrabold transition-all',
                                    'opacity-60' => ! $bisaSunting,
                                ])
                                       :class="status === '{{ $status->value }}'
                                           ? '{{ $status->warna() }} ring-2 ring-offset-1 ring-primary/30 scale-105 shadow-xs'
                                           : 'border-slate-200 bg-white text-slate-500 hover:bg-slate-50'"
                                       title="{{ $status->label() }}">
                                    <input type="radio" name="status[{{ $siswa->id }}]" value="{{ $status->value }}"
                                           x-model="status" class="sr-only" @disabled(! $bisaSunting)>
                                    {{ $status->singkatan() }}
                                </label>
                            @endforeach
                        </div>
                    </div>

                    <div x-show="status !== 'hadir'" x-cloak x-transition class="mt-3 pt-2.5 border-t border-amber-200/60">
                        <div class="flex flex-col sm:flex-row sm:items-center gap-2">
                            <input type="text" name="keterangan[{{ $siswa->id }}]"
                                   x-model="ket"
                                   placeholder="Wajib diisi untuk status selain Hadir (mis. sakit demam, izin keluarga)"
                                   class="block w-full rounded-xl border-amber-300 bg-white text-xs font-medium shadow-2xs focus:border-primary focus:ring-primary"
                                   @disabled(! $bisaSunting)>
                        </div>
                        @if ($bisaSunting)
                            <div class="mt-2 flex flex-wrap items-center gap-1.5">
                                <span class="text-[10px] font-bold uppercase tracking-wider text-amber-800">Isi Cepat:</span>
                                @foreach (['Sakit demam', 'Surat dokter', 'Izin keluarga', 'Dispensasi lomba/OSIS', 'Tanpa keterangan'] as $chipKet)
                                    <button type="button"
                                            @click="ket = '{{ $chipKet }}'"
                                            class="rounded-lg border border-amber-200 bg-white px-2 py-0.5 text-[11px] font-semibold text-amber-900 hover:bg-amber-100 transition">
                                        + {{ $chipKet }}
                                    </button>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>
            @empty
                <div class="kartu p-10 text-center">
                    <x-heroicon-o-users class="mx-auto h-12 w-12 text-slate-300"/>
                    <p class="mt-3 font-semibold text-slate-700">Kelas ini belum punya siswa</p>
                    <p class="text-sm text-slate-500">Minta admin menambahkan siswa ke kelas terlebih dahulu.</p>
                </div>
            @endforelse
        </div>

        @if ($bisaSunting && $siswas->isNotEmpty())
            <div class="sticky bottom-0 mt-5 flex items-center justify-between gap-3 rounded-2xl border border-slate-200 bg-white/95 px-5 py-3.5 shadow-lg backdrop-blur">
                <p class="text-xs text-slate-500 hidden sm:block">Pastikan siswa yang tidak hadir sudah diberi keterangan singkat.</p>
                <div class="flex items-center gap-2 ml-auto">
                    <x-tombol gaya="halus" :href="route('presensi.index')">Batal</x-tombol>
                    <x-tombol gaya="aksen" ikon="check">Simpan Presensi</x-tombol>
                </div>
            </div>
        @endif
    </form>
@endsection
