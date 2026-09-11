<?php

namespace App\Services;

use App\Enums\StatusPresensi;
use App\Models\Agenda;
use App\Models\Kelas;
use App\Models\Presensi;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

/**
 * Rekap kehadiran sesuai CLAUDE.md §8.3.
 * Agregat TIDAK pernah disimpan sebagai kolom — selalu dihitung dari tabel `presensis`.
 */
class RekapPresensiService
{
    /**
     * Rekap per siswa untuk satu kelas (opsional dibatasi mapel & rentang tanggal).
     *
     * @return Collection<int, array{
     *     siswa_id:int, hadir:int, sakit:int, izin:int, alfa:int, bolos:int,
     *     dispensasi:int, pertemuan:int, persentase:float
     * }>
     */
    public function rekapKelas(
        Kelas $kelas,
        ?int $mataPelajaranId = null,
        ?Carbon $dari = null,
        ?Carbon $sampai = null,
    ): Collection {
        $agendaIds = $this->idAgendaEfektif($kelas, $mataPelajaranId, $dari, $sampai);
        $jumlahPertemuan = count($agendaIds);

        $hitungan = Presensi::query()
            ->whereIn('agenda_id', $agendaIds)
            ->selectRaw('siswa_id, status, COUNT(*) as jumlah')
            ->groupBy('siswa_id', 'status')
            ->get()
            ->groupBy('siswa_id');

        return $kelas->siswas()->get()->map(function ($siswa) use ($hitungan, $jumlahPertemuan) {
            $baris = $this->kerangkaKosong();
            $baris['siswa_id'] = $siswa->id;

            foreach ($hitungan->get($siswa->id, collect()) as $item) {
                $status = $item->status instanceof StatusPresensi
                    ? $item->status->value
                    : (string) $item->status;
                $baris[$status] = (int) $item->jumlah;
            }

            $hadirEfektif = $baris['hadir'] + $baris['dispensasi'];   // dispensasi dihitung hadir
            $baris['pertemuan'] = $jumlahPertemuan;
            $baris['persentase'] = $jumlahPertemuan > 0
                ? round($hadirEfektif / $jumlahPertemuan * 100, 1)
                : 0.0;

            return $baris;
        })->values();
    }

    /** Rekap satu siswa (dipakai saat membekukan rapor). */
    public function rekapSiswa(
        Kelas $kelas,
        int $siswaId,
        ?int $mataPelajaranId = null,
        ?Carbon $dari = null,
        ?Carbon $sampai = null,
    ): array {
        $agendaIds = $this->idAgendaEfektif($kelas, $mataPelajaranId, $dari, $sampai);

        $baris = $this->kerangkaKosong();
        $baris['siswa_id'] = $siswaId;

        $hitungan = Presensi::query()
            ->whereIn('agenda_id', $agendaIds)
            ->where('siswa_id', $siswaId)
            ->selectRaw('status, COUNT(*) as jumlah')
            ->groupBy('status')
            ->pluck('jumlah', 'status');

        foreach ($hitungan as $status => $jumlah) {
            $kunci = $status instanceof StatusPresensi ? $status->value : (string) $status;
            $baris[$kunci] = (int) $jumlah;
        }

        $baris['pertemuan'] = count($agendaIds);
        $baris['persentase'] = $baris['pertemuan'] > 0
            ? round(($baris['hadir'] + $baris['dispensasi']) / $baris['pertemuan'] * 100, 1)
            : 0.0;

        return $baris;
    }

    /** Rekap agregat hari ini per kelas — untuk kartu di beranda. */
    public function rekapHariIni(int $guruId): Collection
    {
        return Agenda::query()
            ->whereDate('tanggal', today())
            ->milikGuru($guruId)
            ->with(['jadwal.kelas', 'jadwal.mataPelajaran'])
            ->withCount([
                'presensis as hadir' => fn ($q) => $q->whereIn('status', ['hadir', 'dispensasi']),
                'presensis as sakit' => fn ($q) => $q->where('status', 'sakit'),
                'presensis as izin' => fn ($q) => $q->where('status', 'izin'),
                'presensis as alfa' => fn ($q) => $q->whereIn('status', ['alfa', 'bolos']),
            ])
            ->get();
    }

    /**
     * Matriks daftar hadir bulanan: baris = siswa, kolom = tanggal pertemuan.
     *
     * @return array{tanggal: Collection, baris: Collection}
     */
    public function matriksBulanan(
        Kelas $kelas,
        int $mataPelajaranId,
        Carbon $dari,
        Carbon $sampai,
    ): array {
        $agendas = Agenda::query()
            ->pertemuanEfektif()
            ->whereBetween('tanggal', [$dari->toDateString(), $sampai->toDateString()])
            ->whereHas('jadwal', fn ($q) => $q
                ->where('kelas_id', $kelas->id)
                ->where('mata_pelajaran_id', $mataPelajaranId))
            ->orderBy('tanggal')
            ->get();

        $presensis = Presensi::query()
            ->whereIn('agenda_id', $agendas->pluck('id'))
            ->get()
            ->groupBy('siswa_id');

        $baris = $kelas->siswas()->get()->map(function ($siswa) use ($agendas, $presensis) {
            $milikSiswa = $presensis->get($siswa->id, collect())->keyBy('agenda_id');

            return [
                'siswa' => $siswa,
                'sel' => $agendas->mapWithKeys(fn ($agenda) => [
                    $agenda->id => $milikSiswa->get($agenda->id)?->status?->singkatan() ?? '',
                ]),
            ];
        });

        return ['tanggal' => $agendas, 'baris' => $baris];
    }

    /** Siswa yang alfa-nya melewati ambang peringatan. */
    public function siswaPerluPerhatian(Collection $rekap, int $ambang): Collection
    {
        return $rekap->filter(fn (array $b) => ($b['alfa'] + $b['bolos']) >= $ambang);
    }

    /** @return array<int> */
    protected function idAgendaEfektif(
        Kelas $kelas,
        ?int $mataPelajaranId,
        ?Carbon $dari,
        ?Carbon $sampai,
    ): array {
        return Agenda::query()
            ->pertemuanEfektif()
            ->whereHas('jadwal', function ($q) use ($kelas, $mataPelajaranId) {
                $q->where('kelas_id', $kelas->id);
                if ($mataPelajaranId) {
                    $q->where('mata_pelajaran_id', $mataPelajaranId);
                }
            })
            ->when($dari, fn ($q) => $q->whereDate('tanggal', '>=', $dari))
            ->when($sampai, fn ($q) => $q->whereDate('tanggal', '<=', $sampai))
            ->pluck('id')
            ->all();
    }

    protected function kerangkaKosong(): array
    {
        return [
            'siswa_id' => 0,
            'hadir' => 0, 'sakit' => 0, 'izin' => 0,
            'alfa' => 0, 'bolos' => 0, 'dispensasi' => 0,
            'pertemuan' => 0, 'persentase' => 0.0,
        ];
    }
}
