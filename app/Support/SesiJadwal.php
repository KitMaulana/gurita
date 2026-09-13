<?php

namespace App\Support;

use App\Models\Agenda;
use App\Models\Jadwal;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

/**
 * Helper untuk mengelompokkan jam pelajaran (Jadwal) yang berurutan
 * dengan kelas dan mata pelajaran yang sama menjadi satu Sesi Mengajar.
 */
class SesiJadwal
{
    /** @var array<string, Collection<int, object>> */
    protected static array $cacheSesi = [];

    /**
     * Mengelompokkan daftar jadwal harian seorang guru menjadi sesi-sesi mengajar.
     * Jadwal dengan kelas sama, mata pelajaran sama, dan jam_ke berurutan digabung jadi 1 sesi.
     *
     * @param  Collection<int, Jadwal>  $jadwals
     * @return Collection<int, object>
     */
    public static function dariJadwalHarian(Collection $jadwals, Carbon|string|null $tanggal = null): Collection
    {
        $tanggalObj = $tanggal ? Carbon::parse($tanggal) : null;
        $sesiList = collect();
        $currentBlock = collect();

        $agendaTersimpan = ($tanggalObj && $jadwals->isNotEmpty())
            ? Agenda::query()
                ->whereIn('jadwal_id', $jadwals->pluck('id'))
                ->whereDate('tanggal', $tanggalObj)
                ->with(['presensis', 'bab'])
                ->get()
                ->keyBy('jadwal_id')
            : collect();

        foreach ($jadwals->sortBy('jam_ke') as $j) {
            if ($currentBlock->isEmpty()) {
                $currentBlock->push($j);
                continue;
            }

            $last = $currentBlock->last();
            if (self::apakahSatuSesi($last, $j)) {
                $currentBlock->push($j);
            } else {
                $sesiList->push(self::formatSesiObject($currentBlock, $sesiList->count() + 1, $agendaTersimpan));
                $currentBlock = collect([$j]);
            }
        }

        if ($currentBlock->isNotEmpty()) {
            $sesiList->push(self::formatSesiObject($currentBlock, $sesiList->count() + 1, $agendaTersimpan));
        }

        return $sesiList;
    }

    /**
     * Mengecek apakah jadwal $a dan jadwal $b berada dalam satu sesi (kelas & mapel sama, JP berurutan).
     */
    public static function apakahSatuSesi(Jadwal $a, Jadwal $b): bool
    {
        // 1. JP harus berurutan persis (mis. JP 1 ke JP 2)
        if ((int) $b->jam_ke !== (int) $a->jam_ke + 1) {
            return false;
        }

        // 2. Agenda bersama vs mapel biasa
        if ($a->isAgendaBersama() || $b->isAgendaBersama()) {
            return $a->isAgendaBersama()
                && $b->isAgendaBersama()
                && trim((string) $a->title) === trim((string) $b->title);
        }

        // 3. Mata pelajaran harus sama
        if ($a->mata_pelajaran_id !== $b->mata_pelajaran_id) {
            return false;
        }

        // 4. Kelas harus sama (baik via kelas_id atau nama_kelas_jadwal)
        if ($a->kelas_id && $b->kelas_id) {
            return $a->kelas_id === $b->kelas_id;
        }

        if ($a->nama_kelas_jadwal && $b->nama_kelas_jadwal) {
            return mb_strtolower(trim($a->nama_kelas_jadwal)) === mb_strtolower(trim($b->nama_kelas_jadwal));
        }

        return false;
    }

    /**
     * Membentuk objek DTO Sesi dari kumpulan Jadwal yang sekelompok.
     */
    public static function formatSesiObject(Collection $schedules, int $urutan, Collection $agendaTersimpan): object
    {
        $first = $schedules->first();
        $last = $schedules->last();
        $jamKeList = $schedules->pluck('jam_ke')->all();
        $totalJp = count($jamKeList);
        $scheduleIds = $schedules->pluck('id')->all();

        $minJp = min($jamKeList);
        $maxJp = max($jamKeList);

        $labelJp = $totalJp > 1
            ? sprintf('JP %d–%d (%d JP)', $minJp, $maxJp, $totalJp)
            : sprintf('JP %d (1 JP)', $first->jam_ke);

        $labelJpSingkat = $totalJp > 1
            ? sprintf('JP %d–%d', $minJp, $maxJp)
            : sprintf('JP %d', $first->jam_ke);

        $rentangJamKe = $totalJp > 1
            ? sprintf('%d–%d', $minJp, $maxJp)
            : (string) $first->jam_ke;

        // Jam aktif menyesuaikan mode jadwal (mis. Ramadhan)
        $jamMulai = substr((string) $first->jam_mulai_aktif, 0, 5);
        $jamSelesai = substr((string) $last->jam_selesai_aktif, 0, 5);
        $jam = ($jamMulai && $jamSelesai) ? "{$jamMulai} – {$jamSelesai}" : $first->jam;

        // Cari agenda jika ada pada salah satu jadwal di sesi ini
        $agenda = null;
        foreach ($scheduleIds as $sid) {
            if ($agendaTersimpan->has($sid)) {
                $agenda = $agendaTersimpan->get($sid);
                break;
            }
        }

        $presensisCount = $agenda ? $agenda->presensis->count() : 0;
        $warnaKelas = WarnaKelas::untuk($first->kelas_tampilan, $first->isAgendaBersama());
        $warnaMapel = WarnaMapel::untuk($first->nama_tampilan, $first->isAgendaBersama());

        return (object) [
            'id' => 'sesi_'.$urutan,
            'urutan' => $urutan,
            'primary_schedule_id' => $first->id,
            'first_schedule' => $first,
            'last_schedule' => $last,
            'schedules' => $schedules,
            'schedule_ids' => $scheduleIds,
            'kelas' => $first->kelas,
            'kelas_id' => $first->kelas_id,
            'kelas_tampilan' => $first->kelas_tampilan,
            'mata_pelajaran' => $first->mataPelajaran,
            'mata_pelajaran_id' => $first->mata_pelajaran_id,
            'nama_tampilan' => $first->nama_tampilan,
            'is_agenda_bersama' => $first->isAgendaBersama(),
            'jam_ke_list' => $jamKeList,
            'min_jp' => $minJp,
            'max_jp' => $maxJp,
            'total_jp' => $totalJp,
            'label_jp' => $labelJp,
            'label_jp_singkat' => $labelJpSingkat,
            'rentang_jam_ke' => $rentangJamKe,
            'jam_mulai' => $jamMulai,
            'jam_selesai' => $jamSelesai,
            'jam' => $jam,
            'ruang' => $first->ruang,
            'agenda' => $agenda,
            'terisi' => (bool) $agenda,
            'presensis_count' => $presensisCount,
            'warna_kelas' => $warnaKelas,
            'warna_mapel' => $warnaMapel,
        ];
    }

    /**
     * Cari Sesi yang memuat jadwal tertentu dengan dukungan in-memory cache.
     */
    public static function cariSesiUntukJadwal(Jadwal $jadwal): ?object
    {
        $kunci = "{$jadwal->tahun_ajaran_id}_{$jadwal->guru_id}_".($jadwal->hari?->value ?? 'null');

        if (! isset(self::$cacheSesi[$kunci])) {
            $jadwals = Jadwal::query()
                ->where('tahun_ajaran_id', $jadwal->tahun_ajaran_id)
                ->where('guru_id', $jadwal->guru_id)
                ->where('hari', $jadwal->hari)
                ->with(['kelas', 'mataPelajaran'])
                ->orderBy('jam_ke')
                ->get();

            self::$cacheSesi[$kunci] = self::dariJadwalHarian($jadwals);
        }

        return self::$cacheSesi[$kunci]->first(
            fn ($s) => in_array($jadwal->id, $s->schedule_ids, true)
        );
    }

    /**
     * Bersihkan cache sesi jika ada perubahan jadwal.
     */
    public static function bersihkanCache(): void
    {
        self::$cacheSesi = [];
    }
}
