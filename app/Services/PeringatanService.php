<?php

namespace App\Services;

use App\Enums\HariEnum;
use App\Models\Agenda;
use App\Models\Jadwal;
use App\Models\Penilaian;
use App\Models\User;
use Illuminate\Support\Collection;

/**
 * Peringatan untuk lonceng topbar & kartu di beranda (§7.1):
 * agenda yang belum diisi lebih dari 2 hari, dan penilaian yang nilainya belum lengkap.
 */
class PeringatanService
{
    public const BATAS_HARI_AGENDA = 2;

    public function untuk(User $guru): Collection
    {
        return collect()
            ->merge($this->agendaBelumDiisi($guru))
            ->merge($this->presensiBelumDiisi($guru))
            ->merge($this->nilaiBelumLengkap($guru));
    }

    /** Jadwal yang seharusnya sudah punya agenda tetapi belum dibuat. */
    protected function agendaBelumDiisi(User $guru): Collection
    {
        $mulai = today()->subDays(14);
        $batas = today()->subDays(self::BATAS_HARI_AGENDA);

        $jadwals = Jadwal::query()
            ->tahunAktif()
            ->where('guru_id', $guru->id)
            ->with(['kelas', 'mataPelajaran'])
            ->get();

        if ($jadwals->isEmpty()) {
            return collect();
        }

        $agendaAda = Agenda::query()
            ->whereIn('jadwal_id', $jadwals->pluck('id'))
            ->whereBetween('tanggal', [$mulai->toDateString(), $batas->toDateString()])
            ->get()
            ->map(fn (Agenda $a) => $a->jadwal_id.'|'.$a->tanggal->toDateString())
            ->flip();

        $peringatan = collect();

        for ($tanggal = $mulai->copy(); $tanggal->lte($batas); $tanggal->addDay()) {
            $hari = HariEnum::dariTanggal($tanggal);

            if (! $hari) {
                continue;
            }

            foreach ($jadwals->where('hari', $hari) as $jadwal) {
                if ($agendaAda->has($jadwal->id.'|'.$tanggal->toDateString())) {
                    continue;
                }

                $namaKelas = $jadwal->kelas?->nama ?? $jadwal->kelas_tampilan;
                $mapel = $jadwal->mataPelajaran?->singkatan ?? $jadwal->mataPelajaran?->nama ?? ($jadwal->title ?: 'Pelajaran');

                $peringatan->push([
                    'tipe' => 'agenda',
                    'pesan' => 'Agenda '.$namaKelas.' ('.$mapel.') '
                        .$tanggal->format('d/m/Y').' belum diisi.',
                    'url' => route('agenda.dari-jadwal', ['tanggal' => $tanggal->toDateString()]),
                ]);
            }
        }

        return $peringatan->take(15);
    }

    /** Agenda terlaksana tetapi presensinya kosong. */
    protected function presensiBelumDiisi(User $guru): Collection
    {
        return Agenda::query()
            ->milikGuru($guru->id)
            ->pertemuanEfektif()
            ->whereDoesntHave('presensis')
            ->whereDate('tanggal', '>=', today()->subDays(30))
            ->with(['jadwal.kelas', 'jadwal.mataPelajaran'])
            ->orderByDesc('tanggal')
            ->limit(10)
            ->get()
            ->map(fn (Agenda $a) => [
                'tipe' => 'presensi',
                'pesan' => 'Presensi '.($a->jadwal?->kelas?->nama ?? $a->jadwal?->kelas_tampilan ?? '—').' '.$a->tanggal->format('d/m/Y').' belum diisi.',
                'url' => route('presensi.isi', $a),
            ]);
    }

    /** Penilaian yang masih menyisakan sel nilai kosong. */
    protected function nilaiBelumLengkap(User $guru): Collection
    {
        return Penilaian::query()
            ->tahunAktif()
            ->where('guru_id', $guru->id)
            ->where('is_terkunci', false)
            ->whereHas('nilais', fn ($q) => $q->whereNull('nilai'))
            ->with(['kelas', 'mataPelajaran'])
            ->withCount(['nilais as belum_dinilai' => fn ($q) => $q->whereNull('nilai')])
            ->orderByDesc('tanggal')
            ->limit(10)
            ->get()
            ->map(fn (Penilaian $p) => [
                'tipe' => 'nilai',
                'pesan' => '"'.$p->nama.'" ('.($p->kelas?->nama ?? '-').') — '.$p->belum_dinilai.' siswa belum dinilai.',
                'url' => route('nilai.index', ['kelas_id' => $p->kelas_id, 'mata_pelajaran_id' => $p->mata_pelajaran_id]),
            ]);
    }
}
