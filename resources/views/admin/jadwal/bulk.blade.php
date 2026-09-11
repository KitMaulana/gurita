@extends('layouts.app')
@section('judul', 'Input Jadwal Massal (Bulk Input)')

@section('konten')
    <x-kepala-halaman judul="Input Jadwal Massal (Bulk)"
                      keterangan="Tambahkan jadwal beberapa jam pelajaran sekaligus untuk satu mata pelajaran atau kegiatan bersama.">
        <x-slot:aksi>
            <x-tombol gaya="halus" ikon="arrow-left" :href="route('admin.jadwal.index')">Kembali</x-tombol>
        </x-slot:aksi>
    </x-kepala-halaman>

    @if ($errors->has('jam_ke'))
        <div class="mb-4 rounded-xl border border-rose-200 bg-rose-50 p-4 text-sm text-rose-900">
            <p class="font-bold flex items-center gap-1.5">
                <x-heroicon-o-exclamation-triangle class="h-5 w-5 text-rose-600"/>
                Terdeteksi bentrok pada jam pelajaran yang dipilih:
            </p>
            <ul class="mt-2 list-inside list-disc space-y-1 text-xs">
                @foreach ($errors->get('jam_ke') as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div x-data="{
        hari: '{{ old('hari', 'senin') }}',
        tipe: '{{ old('title') ? 'global' : 'mapel' }}',
        selectedJp: {{ json_encode(array_map('intval', old('jam_ke', []))) }},
        slotsPerDay: {{ json_encode($dailySlots) }},
        pilihSemua() {
            const slots = this.slotsPerDay[this.hari] || [];
            this.selectedJp = Object.values(slots).map(s => s.number);
        },
        bersihkan() {
            this.selectedJp = [];
        },
        toggleJp(num) {
            num = parseInt(num);
            const idx = this.selectedJp.indexOf(num);
            if (idx > -1) {
                this.selectedJp.splice(idx, 1);
            } else {
                this.selectedJp.push(num);
                this.selectedJp.sort((a, b) => a - b);
            }
        }
    }" class="kartu max-w-4xl p-6">
        <form method="POST" action="{{ route('admin.jadwal.bulk.store') }}">
            @csrf

            <div class="space-y-6">
                {{-- Hari & Tipe --}}
                <div class="grid gap-4 md:grid-cols-2">
                    <div>
                        <label class="mb-1 block text-sm font-semibold text-slate-700">Hari <span class="text-rose-500">*</span></label>
                        <select name="hari" x-model="hari" @change="selectedJp = []" required
                                class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary">
                            @foreach ($hariList as $k => $label)
                                <option value="{{ $k }}">{{ $label }}</option>
                            @endforeach
                        </select>
                        @error('hari') <p class="mt-1 text-xs text-rose-500">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="mb-1 block text-sm font-semibold text-slate-700">Tipe Jadwal <span class="text-rose-500">*</span></label>
                        <div class="grid grid-cols-2 gap-2">
                            <button type="button" @click="tipe = 'mapel'"
                                    :class="tipe === 'mapel' ? 'border-primary bg-primary/10 text-primary font-bold shadow-sm' : 'border-slate-200 bg-white text-slate-600 hover:bg-slate-50'"
                                    class="rounded-lg border p-2 text-center text-xs transition-all">
                                Mata Pelajaran
                            </button>
                            <button type="button" @click="tipe = 'global'"
                                    :class="tipe === 'global' ? 'border-amber-500 bg-amber-50 text-amber-900 font-bold shadow-sm' : 'border-slate-200 bg-white text-slate-600 hover:bg-slate-50'"
                                    class="rounded-lg border p-2 text-center text-xs transition-all">
                                Agenda Bersama
                            </button>
                        </div>
                    </div>
                </div>

                {{-- Agenda Bersama --}}
                <div x-show="tipe === 'global'" x-cloak class="rounded-xl border border-amber-200 bg-amber-50/50 p-4 space-y-2">
                    <label class="block text-sm font-semibold text-amber-950">Nama Agenda / Kegiatan Sekolah <span class="text-rose-500">*</span></label>
                    <input type="text" name="title" list="daftar-agenda-bulk"
                           value="{{ old('title') }}"
                           :required="tipe === 'global'"
                           placeholder="Contoh: Upacara Bendera, Makan Bergizi Gratis (MBG), Sholat Dhuhur Berjamaah"
                           class="w-full rounded-lg border border-amber-300 bg-white px-3 py-2 text-sm focus:border-amber-500 focus:outline-none focus:ring-1 focus:ring-amber-500">
                    <datalist id="daftar-agenda-bulk">
                        @foreach ($rekomendasiAgenda as $saran)
                            <option value="{{ $saran }}">
                        @endforeach
                    </datalist>
                    <p class="text-xs text-amber-800">Agenda ini berlaku untuk semua kelas sekaligus pada JP yang dipilih.</p>
                </div>

                {{-- Mata Pelajaran --}}
                <div x-show="tipe === 'mapel'" class="grid gap-4 md:grid-cols-2">
                    <div>
                        <label class="mb-1 block text-sm font-semibold text-slate-700">Mata Pelajaran <span class="text-rose-500">*</span></label>
                        <select name="mata_pelajaran_id" :required="tipe === 'mapel'"
                                class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary">
                            <option value="">— Pilih Mata Pelajaran —</option>
                            @foreach ($mapelList as $id => $nama)
                                <option value="{{ $id }}" {{ old('mata_pelajaran_id') == $id ? 'selected' : '' }}>
                                    {{ $nama }}
                                </option>
                            @endforeach
                        </select>
                        @error('mata_pelajaran_id') <p class="mt-1 text-xs text-rose-500">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="mb-1 block text-sm font-semibold text-slate-700">Guru Pengajar <span class="text-rose-500">*</span></label>
                        <select name="guru_id" :required="tipe === 'mapel'"
                                class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary">
                            <option value="">— Pilih Guru —</option>
                            @foreach ($guruList as $id => $nama)
                                <option value="{{ $id }}" {{ old('guru_id') == $id ? 'selected' : '' }}>
                                    {{ $nama }}
                                </option>
                            @endforeach
                        </select>
                        @error('guru_id') <p class="mt-1 text-xs text-rose-500">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="mb-1 block text-sm font-semibold text-slate-700">Kelas <span class="text-xs font-normal text-slate-400">(Opsional)</span></label>
                        <select name="kelas_id"
                                class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary">
                            <option value="">— Tanpa Kelas / Kosong (Opsional) —</option>
                            @foreach ($kelasList as $id => $nama)
                                <option value="{{ $id }}" {{ old('kelas_id') == $id ? 'selected' : '' }}>
                                    {{ $nama }}
                                </option>
                            @endforeach
                        </select>
                        @error('kelas_id') <p class="mt-1 text-xs text-rose-500">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="mb-1 block text-sm font-semibold text-slate-700">Ruangan (Opsional)</label>
                        <input type="text" name="ruang" value="{{ old('ruang') }}"
                               placeholder="mis. R.12 / Lab Bahasa"
                               class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary">
                        @error('ruang') <p class="mt-1 text-xs text-rose-500">{{ $message }}</p> @enderror
                    </div>
                </div>

                {{-- Pemilihan Multi Jam Pelajaran (JP) --}}
                <div class="rounded-xl border border-slate-200 bg-slate-50/50 p-4">
                    <div class="flex flex-wrap items-center justify-between gap-2 pb-3 border-b border-slate-200">
                        <div>
                            <h3 class="text-sm font-bold text-slate-800">Pilih Jam Pelajaran (Multi-JP) <span class="text-rose-500">*</span></h3>
                            <p class="text-xs text-slate-500">Centang JP yang akan dimasukkan sekaligus (misal JP 1, 2, dan 3)</p>
                        </div>
                        <div class="flex items-center gap-1 text-xs">
                            <button type="button" @click="pilihSemua()" class="rounded border border-slate-300 bg-white px-2 py-1 text-slate-600 hover:bg-slate-100">
                                Pilih Semua
                            </button>
                            <button type="button" @click="bersihkan()" class="rounded border border-slate-300 bg-white px-2 py-1 text-slate-600 hover:bg-slate-100">
                                Bersihkan
                            </button>
                        </div>
                    </div>

                    <div class="mt-3 grid gap-2 sm:grid-cols-2 lg:grid-cols-3">
                        <template x-for="(slot, num) in (slotsPerDay[hari] || slotsPerDay['selasa'])" :key="num">
                            <label class="flex cursor-pointer items-center gap-3 rounded-lg border p-2.5 text-xs transition-all"
                                   :class="selectedJp.includes(parseInt(slot.number))
                                       ? 'border-primary bg-primary/5 font-semibold text-primary ring-1 ring-primary'
                                       : 'border-slate-200 bg-white text-slate-700 hover:border-slate-300'">
                                <input type="checkbox" name="jam_ke[]" :value="slot.number"
                                       :checked="selectedJp.includes(parseInt(slot.number))"
                                       @change="toggleJp(slot.number)"
                                       class="h-4 w-4 rounded border-slate-300 text-primary focus:ring-primary">
                                <div class="min-w-0 flex-1">
                                    <span class="block font-bold" x-text="`JP ${slot.number}`"></span>
                                    <span class="text-[11px] text-slate-500" x-text="`${slot.start} – ${slot.end}`"></span>
                                </div>
                            </label>
                        </template>
                    </div>

                    <div class="mt-3 text-xs text-slate-600">
                        Total JP dipilih: <span class="font-bold text-primary" x-text="selectedJp.length">0</span> jam pelajaran
                    </div>
                </div>

                <div class="flex items-center justify-end gap-2 pt-4 border-t border-slate-200">
                    <x-tombol gaya="halus" :href="route('admin.jadwal.index')">Batal</x-tombol>
                    <x-tombol gaya="aksen" ikon="check" :disabled="false">Simpan Semua Jadwal</x-tombol>
                </div>
            </div>
        </form>
    </div>
@endsection
