<?php

namespace App\Services;

use App\Models\Jadwal;
use App\Models\MataPelajaran;

/**
 * Aturan bentrok jadwal (CLAUDE.md §5.2):
 *  - Satu kelas tidak boleh punya dua jadwal pada hari + jam ke yang sama.
 *  - Satu guru tidak boleh punya dua jadwal pada hari + jam ke yang sama,
 *    kecuali mapel PJOK yang boleh mengajar lebih dari satu kelas dalam 1 JP.
 */
class PemeriksaBentrokJadwal
{
    /**
     * @return array<int,string> daftar pesan bentrok (kosong = aman)
     */
    public function periksa(array $data, ?int $abaikanId = null): array
    {
        $pesan = [];

        $dasar = fn () => Jadwal::query()
            ->where('tahun_ajaran_id', $data['tahun_ajaran_id'])
            ->where('hari', $data['hari'])
            ->where('jam_ke', $data['jam_ke'])
            ->when($abaikanId, fn ($q) => $q->whereKeyNot($abaikanId))
            ->with(['kelas', 'mataPelajaran', 'guru']);

        if (! empty($data['kelas_id'])) {
            $bentrokKelas = (clone $dasar())
                ->where('kelas_id', $data['kelas_id'])
                ->first();

            if ($bentrokKelas) {
                $pesan[] = sprintf(
                    'Bentrok: kelas %s sudah terisi %s (%s) pada %s jam ke-%d.',
                    $bentrokKelas->kelas?->nama ?? 'Semua Kelas',
                    $bentrokKelas->title ?: ($bentrokKelas->mataPelajaran?->nama ?? 'Kegiatan'),
                    $bentrokKelas->guru?->name ?? '—',
                    $bentrokKelas->hari->label(),
                    $bentrokKelas->jam_ke,
                );
            }
        }

        if (! empty($data['mata_pelajaran_id']) && ! empty($data['guru_id'])) {
            $mapel = MataPelajaran::find($data['mata_pelajaran_id']);

            if ($mapel && ! $mapel->bolehParalel()) {
                $bentrokGuru = (clone $dasar())
                    ->where('guru_id', $data['guru_id'])
                    ->whereHas('mataPelajaran', fn ($q) => $q
                        ->whereRaw("UPPER(CONCAT(singkatan,' ',nama)) NOT LIKE '%PJOK%'")
                        ->whereRaw("UPPER(nama) NOT LIKE '%JASMANI%'"))
                    ->first();

                if ($bentrokGuru) {
                    $pesan[] = sprintf(
                        'Bentrok: %s sudah mengajar %s pada %s jam ke-%d.',
                        $bentrokGuru->guru?->name ?? 'Guru',
                        $bentrokGuru->kelas?->nama ?? 'Kelas',
                        $bentrokGuru->hari->label(),
                        $bentrokGuru->jam_ke,
                    );
                }
            }
        }

        return $pesan;
    }
}
