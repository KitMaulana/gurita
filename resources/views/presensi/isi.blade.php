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

    <div class="kartu mb-5 flex flex-wrap items-center justify-between gap-3 p-4 sm:p-5">
        <div class="flex items-center gap-3 min-w-0">
            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-slate-900 text-white">
                <x-heroicon-o-book-open class="h-5 w-5"/>
            </div>
            <div class="min-w-0">
                <p class="text-[11px] font-semibold uppercase tracking-wider text-slate-400">Materi Pertemuan Ke-{{ $agenda->pertemuan_ke }}</p>
                <p class="truncate text-sm sm:text-base font-bold text-slate-900">{{ $agenda->judul_materi }}</p>
            </div>
        </div>
        <span class="rounded-lg bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-700 whitespace-nowrap">
            {{ $siswas->count() }} Siswa
        </span>
    </div>

    @if (! $bisaSunting)
        <div class="mb-4 flex items-center gap-2.5 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm font-medium text-amber-900">
            <x-heroicon-o-lock-closed class="h-4 w-4 text-amber-600 shrink-0"/>
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
                            class="inline-flex cursor-pointer items-center gap-1.5 rounded-lg border border-rose-200 bg-rose-50 px-3 py-2 text-xs font-semibold text-rose-800 hover:bg-rose-100 transition-colors whitespace-nowrap">
                        <x-heroicon-o-x-circle class="h-4 w-4 text-rose-600 shrink-0"/>
                        <span>Batal Tandai Semua</span>
                    </button>
                </div>
            @endif

            <div class="relative flex-1 sm:max-w-xs">
                <x-heroicon-o-magnifying-glass class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-slate-400"/>
                <input type="text" x-model="cari"
                       placeholder="Cari nama siswa atau NISN..."
                       class="w-full rounded-lg border-slate-200 bg-slate-50/70 pl-9 pr-8 py-1.5 text-xs text-slate-800 placeholder:text-slate-400 focus:bg-white focus:border-primary focus:ring-primary">
                <button type="button" x-show="cari.length > 0" x-cloak @click="cari = ''"
                        class="absolute right-2.5 top-1/2 -translate-y-1/2 text-xs font-semibold text-slate-400 hover:text-slate-600">
                    ✕
                </button>
            </div>
        </div>

        <div class="space-y-2">
            @forelse ($siswas as $siswa)
                @php
                    $tersimpan = $presensi->get($siswa->id);
                    $statusTerpilih = old("status.{$siswa->id}", $tersimpan?->status?->value ?? 'hadir');
                    $ketTersimpan = old("keterangan.{$siswa->id}", $tersimpan?->keterangan ?? '');
                @endphp

                <div class="kartu p-3.5 transition-colors"
                     data-baris
                     x-data="{ status: '{{ $statusTerpilih }}', ket: {{ Js::from($ketTersimpan) }} }"
                     x-show="!cari || {{ Js::from(strtolower($siswa->nama.' '.($siswa->nisn ?? ''))) }}.includes(cari.toLowerCase())"
                     :class="status !== 'hadir' ? 'border-amber-300 bg-amber-50/25' : ''">
                    <div class="flex flex-col sm:flex-row sm:items-center gap-3">
                        <div class="flex items-center gap-3 min-w-0 flex-1">
                            <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-slate-100 text-xs font-bold text-slate-700 tabular-nums">
                                {{ $siswa->pivot->no_absen }}
                            </div>

                            <div class="min-w-0 flex-1">
                                <p class="truncate font-semibold text-slate-900 text-sm">{{ $siswa->nama }}</p>
                                <p class="text-xs text-slate-500">{{ $siswa->nisn }} · {{ $siswa->label_jenis_kelamin }}</p>
                            </div>
                        </div>

                        {{-- 6 tombol status selalu 1 baris horizontal --}}
                        <div class="inline-flex flex-nowrap items-center gap-1 overflow-x-auto shrink-0">
                            @foreach ($statusList as $status)
                                <label @class([
                                    'flex h-9 w-9 shrink-0 cursor-pointer items-center justify-center rounded-lg border text-xs font-bold transition-colors select-none',
                                    'opacity-60' => ! $bisaSunting,
                                ])
                                       :class="status === '{{ $status->value }}'
                                           ? '{{ $status->warna() }} ring-1 ring-slate-900/15'
                                           : 'border-slate-200 bg-white text-slate-500 hover:bg-slate-50'"
                                       title="{{ $status->label() }}">
                                    <input type="radio" name="status[{{ $siswa->id }}]" value="{{ $status->value }}"
                                           x-model="status" class="sr-only" @disabled(! $bisaSunting)>
                                    {{ $status->singkatan() }}
                                </label>
                            @endforeach
                        </div>
                    </div>

                    <div x-show="status !== 'hadir'" x-cloak x-transition class="mt-3 pt-2.5 border-t border-slate-100">
                        <input type="text" name="keterangan[{{ $siswa->id }}]"
                               x-model="ket"
                               placeholder="Keterangan wajib diisi untuk status selain Hadir (mis. sakit demam, izin keluarga)"
                               class="block w-full rounded-lg border-slate-200 bg-white text-xs focus:border-primary focus:ring-primary"
                               @disabled(! $bisaSunting)>
                        @if ($bisaSunting)
                            <div class="mt-2 flex flex-wrap items-center gap-1.5">
                                <span class="text-[11px] font-medium text-slate-400">Opsi Cepat:</span>
                                @foreach (['Sakit demam', 'Surat dokter', 'Izin keluarga', 'Dispensasi lomba/OSIS', 'Tanpa keterangan'] as $chipKet)
                                    <button type="button"
                                            @click="ket = '{{ $chipKet }}'"
                                            class="rounded border border-slate-200 bg-slate-50 px-2 py-0.5 text-[11px] font-medium text-slate-600 hover:bg-slate-100 hover:text-slate-900">
                                        {{ $chipKet }}
                                    </button>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>
            @empty
                <div class="kartu p-10 text-center">
                    <x-heroicon-o-users class="mx-auto h-10 w-10 text-slate-300"/>
                    <p class="mt-3 font-semibold text-slate-700">Kelas ini belum punya siswa</p>
                    <p class="text-sm text-slate-500">Minta admin menambahkan siswa ke kelas terlebih dahulu.</p>
                </div>
            @endforelse
        </div>

        @if ($bisaSunting && $siswas->isNotEmpty())
            <div class="sticky bottom-0 mt-5 flex items-center justify-between gap-3 rounded-xl border border-slate-200 bg-white/95 px-5 py-3.5 shadow-lg backdrop-blur-sm">
                <p class="text-xs text-slate-500 hidden sm:block">Pastikan siswa yang tidak hadir sudah diberi keterangan singkat.</p>
                <div class="flex items-center gap-2 ml-auto">
                    <x-tombol gaya="halus" :href="route('presensi.index')">Batal</x-tombol>
                    <x-tombol gaya="aksen" ikon="check">Simpan Presensi</x-tombol>
                </div>
            </div>
        @endif
    </form>
@endsection
