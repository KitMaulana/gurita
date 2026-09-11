@extends('layouts.app')
@section('judul', 'Isi Presensi')

@php use App\Support\Tanggal; @endphp

@section('konten')
    <x-kepala-halaman :judul="'Presensi '.($agenda->jadwal?->kelas_tampilan ?? 'Kelas')"
                      :keterangan="($agenda->jadwal?->nama_tampilan ?? 'Mata Pelajaran').' · '.Tanggal::lengkap($agenda->tanggal).' · Pertemuan ke-'.$agenda->pertemuan_ke">
        <x-slot:aksi>
            <x-tombol gaya="halus" ikon="arrow-left" :href="route('presensi.index')">Kembali</x-tombol>
        </x-slot:aksi>
    </x-kepala-halaman>

    <div class="kartu mb-4 p-4">
        <p class="text-sm text-slate-500">Materi pertemuan</p>
        <p class="font-semibold text-slate-800">{{ $agenda->judul_materi }}</p>
    </div>

    @if (! $bisaSunting)
        <div class="mb-4 flex items-center gap-2 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800">
            <x-heroicon-o-lock-closed class="h-5 w-5"/>
            Pertemuan ini terkunci (rapor sudah dibekukan). Data hanya dapat dilihat.
        </div>
    @endif

    <form method="POST" action="{{ route('presensi.simpan', $agenda) }}"
          x-data="{
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

        @if ($bisaSunting)
            <div class="tanpa-cetak mb-4 flex flex-wrap items-center justify-between gap-3">
                <div class="flex items-center gap-2">
                    <x-tombol tipe="button" gaya="aksen" ikon="check-circle" @click="tandaiSemuaHadir()">
                        Tandai Semua Hadir
                    </x-tombol>
                    <button type="button" @click="batalTandaiSemua()"
                            style="background-color: #fff1f2; color: #9f1239; border: 1px solid #fecdd3;"
                            class="inline-flex cursor-pointer items-center gap-1.5 rounded-xl px-3.5 py-2 text-xs font-bold shadow-sm hover:brightness-95 transition active:scale-95">
                        <x-heroicon-o-x-circle class="h-4 w-4 shrink-0" style="color: #e11d48;"/>
                        <span>Batal Tandai Semua</span>
                    </button>
                </div>
                <p class="text-sm text-slate-500">
                    Alur tercepat: tandai semua hadir, lalu ubah yang tidak hadir saja.
                </p>
            </div>
        @endif

        <div class="space-y-2">
            @forelse ($siswas as $siswa)
                @php
                    $tersimpan = $presensi->get($siswa->id);
                    $statusTerpilih = old("status.{$siswa->id}", $tersimpan?->status?->value ?? 'hadir');
                @endphp

                <div class="kartu p-3" data-baris x-data="{ status: '{{ $statusTerpilih }}' }">
                    <div class="flex flex-wrap items-center gap-3">
                        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-slate-100 text-sm font-bold text-primary">
                            {{ $siswa->pivot->no_absen }}
                        </div>

                        <div class="min-w-0 flex-1">
                            <p class="truncate font-semibold text-slate-800">{{ $siswa->nama }}</p>
                            <p class="text-xs text-slate-500">{{ $siswa->nisn }} · {{ $siswa->label_jenis_kelamin }}</p>
                        </div>

                        {{-- Tombol status besar (bukan dropdown) agar nyaman di ponsel --}}
                        <div class="flex flex-wrap gap-1.5">
                            @foreach ($statusList as $status)
                                <label @class([
                                    'tombol-sentuh flex cursor-pointer items-center justify-center rounded-lg border px-3 text-sm font-bold transition-colors',
                                    'opacity-60' => ! $bisaSunting,
                                ])
                                       :class="status === '{{ $status->value }}'
                                           ? '{{ $status->warna() }} ring-2 ring-offset-1 ring-primary/30'
                                           : 'border-slate-200 bg-white text-slate-400 hover:bg-slate-50'"
                                       title="{{ $status->label() }}">
                                    <input type="radio" name="status[{{ $siswa->id }}]" value="{{ $status->value }}"
                                           x-model="status" class="sr-only" @disabled(! $bisaSunting)>
                                    {{ $status->singkatan() }}
                                </label>
                            @endforeach
                        </div>
                    </div>

                    <div x-show="status !== 'hadir'" x-cloak x-transition class="mt-3">
                        <input type="text" name="keterangan[{{ $siswa->id }}]"
                               value="{{ old("keterangan.{$siswa->id}", $tersimpan?->keterangan) }}"
                               placeholder="Keterangan (opsional, mis. sakit demam, izin keluarga)"
                               class="block w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-primary focus:ring-primary"
                               @disabled(! $bisaSunting)>
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
            <div class="sticky bottom-0 mt-5 flex justify-end gap-2 border-t border-slate-200 bg-slate-50/95 py-3 backdrop-blur">
                <x-tombol gaya="halus" :href="route('presensi.index')">Batal</x-tombol>
                <x-tombol gaya="aksen" ikon="check">Simpan Presensi</x-tombol>
            </div>
        @endif
    </form>
@endsection
