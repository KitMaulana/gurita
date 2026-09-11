@extends('layouts.app')
@section('judul', $jadwal->exists ? 'Ubah Jadwal' : 'Tambah Jadwal')

@section('konten')
    <x-kepala-halaman :judul="$jadwal->exists ? 'Ubah Jadwal' : 'Tambah Jadwal'"
                      keterangan="Pemeriksaan bentrok dijalankan otomatis. Waktu jam pelajaran disesuaikan dengan aturan sekolah.">
        <x-slot:aksi>
            <x-tombol gaya="halus" ikon="arrow-left" :href="route('admin.jadwal.index')">Kembali</x-tombol>
        </x-slot:aksi>
    </x-kepala-halaman>

    <div x-data="{
        hari: '{{ old('hari', $jadwal->hari?->value ?? 'senin') }}',
        tipe: '{{ old('title', $jadwal->title) ? 'global' : 'mapel' }}',
        jamKe: '{{ old('jam_ke', $jadwal->jam_ke ?? 1) }}',
        jamMulai: '{{ old('jam_mulai', $jadwal->jam_mulai ? substr((string)$jadwal->jam_mulai, 0, 5) : '08:10') }}',
        jamSelesai: '{{ old('jam_selesai', $jadwal->jam_selesai ? substr((string)$jadwal->jam_selesai, 0, 5) : '08:45') }}',
        slotsPerDay: {{ json_encode($dailySlots) }},
        init() {
            this.updateSlots();
        },
        updateSlots() {
            const slots = this.slotsPerDay[this.hari] || this.slotsPerDay['selasa'] || {};
            const currentSlot = slots[this.jamKe];
            if (currentSlot && (!this.jamMulai || !this.jamSelesai || this.$el.dataset.autoUpdate === 'true')) {
                this.jamMulai = currentSlot.start;
                this.jamSelesai = currentSlot.end;
            }
        },
        onDayChange() {
            this.updateSlotTimes();
        },
        onJamKeChange() {
            this.updateSlotTimes();
        },
        updateSlotTimes() {
            const slots = this.slotsPerDay[this.hari] || this.slotsPerDay['selasa'] || {};
            const slot = slots[this.jamKe];
            if (slot) {
                this.jamMulai = slot.start;
                this.jamSelesai = slot.end;
            }
        }
    }" class="kartu max-w-3xl p-6">
        <form method="POST" action="{{ $jadwal->exists ? route('admin.jadwal.update', $jadwal) : route('admin.jadwal.store') }}">
            @csrf
            @if ($jadwal->exists) @method('PUT') @endif

            <div class="space-y-6">
                {{-- Pemilihan Hari dan Jam Pelajaran --}}
                <div class="grid gap-4 md:grid-cols-2">
                    <div>
                        <label class="mb-1 block text-sm font-semibold text-slate-700">Hari <span class="text-rose-500">*</span></label>
                        <select name="hari" x-model="hari" @change="onDayChange()" required
                                class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary">
                            @foreach ($hariList as $k => $label)
                                <option value="{{ $k }}">{{ $label }}</option>
                            @endforeach
                        </select>
                        @error('hari') <p class="mt-1 text-xs text-rose-500">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="mb-1 block text-sm font-semibold text-slate-700">Jam Pelajaran Ke- (JP) <span class="text-rose-500">*</span></label>
                        <select name="jam_ke" x-model="jamKe" @change="onJamKeChange()" required
                                class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm font-medium focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary">
                            <template x-for="(slot, num) in (slotsPerDay[hari] || slotsPerDay['selasa'])" :key="num">
                                <option :value="slot.number" :selected="slot.number == jamKe"
                                        x-text="`JP ${slot.number} — ${slot.start} s.d ${slot.end}`"></option>
                            </template>
                        </select>
                        @error('jam_ke') <p class="mt-1 text-xs text-rose-500">{{ $message }}</p> @enderror
                    </div>
                </div>

                {{-- Waktu Mulai & Selesai Otomatis --}}
                <div class="grid grid-cols-2 gap-4 rounded-xl border border-slate-200 bg-slate-50/70 p-3 text-xs text-slate-600">
                    <div>
                        <label class="block font-semibold text-slate-600">Jam Mulai</label>
                        <input type="time" name="jam_mulai" x-model="jamMulai" required
                               class="mt-1 w-full rounded border border-slate-300 bg-white px-2 py-1 text-sm font-mono focus:border-primary focus:outline-none">
                    </div>
                    <div>
                        <label class="block font-semibold text-slate-600">Jam Selesai</label>
                        <input type="time" name="jam_selesai" x-model="jamSelesai" required
                               class="mt-1 w-full rounded border border-slate-300 bg-white px-2 py-1 text-sm font-mono focus:border-primary focus:outline-none">
                    </div>
                </div>

                {{-- Jenis / Tipe Jadwal (Sesuai SIDACHEERS) --}}
                <div>
                    <label class="mb-1 block text-sm font-semibold text-slate-700">Tipe Jadwal <span class="text-rose-500">*</span></label>
                    <div class="grid grid-cols-2 gap-2">
                        <button type="button" @click="tipe = 'mapel'"
                                :class="tipe === 'mapel' ? 'border-primary bg-primary/10 text-primary font-bold shadow-sm' : 'border-slate-200 bg-white text-slate-600 hover:bg-slate-50'"
                                class="rounded-xl border p-3 text-left transition-all">
                            <span class="block text-sm">Mata Pelajaran</span>
                            <span class="block text-xs font-normal text-slate-500">Kelas dan Guru tertentu</span>
                        </button>

                        <button type="button" @click="tipe = 'global'"
                                :class="tipe === 'global' ? 'border-amber-500 bg-amber-50 text-amber-900 font-bold shadow-sm' : 'border-slate-200 bg-white text-slate-600 hover:bg-slate-50'"
                                class="rounded-xl border p-3 text-left transition-all">
                            <span class="block text-sm">Agenda Bersama</span>
                            <span class="block text-xs font-normal text-slate-500">Semua Kelas (Upacara, MBG, Sholat)</span>
                        </button>
                    </div>
                </div>

                {{-- Field Khusus Agenda Bersama --}}
                <div x-show="tipe === 'global'" x-cloak class="rounded-xl border border-amber-200 bg-amber-50/50 p-4 space-y-3">
                    <label class="block text-sm font-semibold text-amber-950">Nama Agenda / Kegiatan <span class="text-rose-500">*</span></label>
                    <input type="text" name="title" list="daftar-agenda-saran"
                           value="{{ old('title', $jadwal->title) }}"
                           :required="tipe === 'global'"
                           placeholder="Contoh: Upacara Bendera, Makan Bergizi Gratis (MBG), Sholat Dzuhur"
                           class="w-full rounded-lg border border-amber-300 bg-white px-3 py-2 text-sm focus:border-amber-500 focus:outline-none focus:ring-1 focus:ring-amber-500">
                    <datalist id="daftar-agenda-saran">
                        @foreach ($rekomendasiAgenda as $saran)
                            <option value="{{ $saran }}">
                        @endforeach
                    </datalist>
                    <p class="text-xs text-amber-800">Agenda ini akan otomatis berlaku untuk seluruh kelas tanpa mengikat guru atau mata pelajaran.</p>
                    @error('title') <p class="text-xs text-rose-500">{{ $message }}</p> @enderror
                </div>

                {{-- Field Khusus Mata Pelajaran --}}
                <div x-show="tipe === 'mapel'" class="grid gap-4 md:grid-cols-2">
                    <div>
                        <label class="mb-1 block text-sm font-semibold text-slate-700">Mata Pelajaran <span class="text-rose-500">*</span></label>
                        <select name="mata_pelajaran_id" :required="tipe === 'mapel'"
                                class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary">
                            <option value="">— Pilih Mata Pelajaran —</option>
                            @foreach ($mapelList as $id => $nama)
                                <option value="{{ $id }}" {{ old('mata_pelajaran_id', $jadwal->mata_pelajaran_id) == $id ? 'selected' : '' }}>
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
                                <option value="{{ $id }}" {{ old('guru_id', $jadwal->guru_id) == $id ? 'selected' : '' }}>
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
                                <option value="{{ $id }}" {{ old('kelas_id', $jadwal->kelas_id) == $id ? 'selected' : '' }}>
                                    {{ $nama }}
                                </option>
                            @endforeach
                        </select>
                        @error('kelas_id') <p class="mt-1 text-xs text-rose-500">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="mb-1 block text-sm font-semibold text-slate-700">Ruangan (Opsional)</label>
                        <input type="text" name="ruang" value="{{ old('ruang', $jadwal->ruang) }}"
                               placeholder="mis. R.12 / Lab Bahasa"
                               class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary">
                        @error('ruang') <p class="mt-1 text-xs text-rose-500">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="flex items-center justify-end gap-2 pt-4 border-t border-slate-200">
                    <x-tombol gaya="halus" :href="route('admin.jadwal.index')">Batal</x-tombol>
                    <x-tombol gaya="aksen" ikon="check">Simpan Jadwal</x-tombol>
                </div>
            </div>
        </form>
    </div>
@endsection
