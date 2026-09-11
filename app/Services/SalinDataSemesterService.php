<?php

namespace App\Services;

use App\Models\Jadwal;
use App\Models\Kelas;
use App\Models\Kktp;
use App\Models\TahunAjaran;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class SalinDataSemesterService
{
    /**
     * Menyalin data dari semester asal ke semester tujuan dalam tahun pelajaran yang sama.
     *
     * @param  array{salin_kelas?: bool, salin_siswa?: bool, salin_jadwal?: bool, salin_kktp?: bool}  $opsi
     * @return array{kelas: int, siswa: int, jadwal: int, kktp: int}
     *
     * @throws InvalidArgumentException
     */
    public function salin(TahunAjaran $semesterAsal, TahunAjaran $semesterTujuan, array $opsi = []): array
    {
        if ($semesterAsal->id === $semesterTujuan->id) {
            throw new InvalidArgumentException('Semester asal dan semester tujuan tidak boleh sama.');
        }

        // Aturan ketat: Hanya boleh dalam tahun pelajaran yang sama (misal sama-sama "2026/2027")
        if (trim($semesterAsal->nama) !== trim($semesterTujuan->nama)) {
            throw new InvalidArgumentException(
                "Penyalinan data hanya diizinkan untuk semester dalam tahun pelajaran yang sama (misal dari {$semesterAsal->nama} Ganjil ke {$semesterAsal->nama} Genap). " .
                "Untuk tahun pelajaran baru ({$semesterTujuan->nama}), data kelas dan siswa harus diinput ulang karena terjadi kenaikan kelas dan kelulusan."
            );
        }

        $salinKelas = $opsi['salin_kelas'] ?? true;
        $salinSiswa = $salinKelas && ($opsi['salin_siswa'] ?? true);
        $salinJadwal = $opsi['salin_jadwal'] ?? true;
        $salinKktp = $opsi['salin_kktp'] ?? true;

        return DB::transaction(function () use (
            $semesterAsal,
            $semesterTujuan,
            $salinKelas,
            $salinSiswa,
            $salinJadwal,
            $salinKktp
        ) {
            $petaKelasId = [];
            $petaKelasNama = [];
            $totalKelas = 0;
            $totalSiswa = 0;
            $totalJadwal = 0;
            $totalKktp = 0;

            // 1. Salin Kelas
            if ($salinKelas) {
                $kelases = $semesterAsal->kelas()->get();
                foreach ($kelases as $kelas) {
                    $kelasBaru = Kelas::firstOrCreate(
                        [
                            'tahun_ajaran_id' => $semesterTujuan->id,
                            'nama' => $kelas->nama,
                        ],
                        [
                            'tingkat' => $kelas->tingkat,
                            'jurusan' => $kelas->jurusan,
                            'wali_kelas_id' => $kelas->wali_kelas_id,
                        ]
                    );

                    $petaKelasId[$kelas->id] = $kelasBaru->id;
                    $petaKelasNama[$kelas->nama] = $kelasBaru;
                    $totalKelas++;

                    // 2. Salin Siswa ke Kelas Baru
                    if ($salinSiswa) {
                        $siswas = $kelas->siswas()->get();
                        foreach ($siswas as $siswa) {
                            $kelasBaru->siswas()->syncWithoutDetaching([
                                $siswa->id => ['no_absen' => $siswa->pivot->no_absen],
                            ]);
                            $totalSiswa++;
                        }
                    }
                }
            } else {
                // Jika tidak menyalin kelas baru, cari kelas yang sudah ada di semester tujuan berdasarkan nama
                foreach ($semesterTujuan->kelas as $kt) {
                    $petaKelasNama[$kt->nama] = $kt;
                }
            }

            // 3. Salin Jadwal Pelajaran
            if ($salinJadwal) {
                $jadwals = $semesterAsal->jadwals()->get();
                foreach ($jadwals as $j) {
                    $targetKelasId = null;
                    if ($j->kelas_id) {
                        $targetKelasId = $petaKelasId[$j->kelas_id] ?? null;
                        if (! $targetKelasId && $j->kelas) {
                            $targetKelasId = $petaKelasNama[$j->kelas->nama]->id ?? null;
                        }
                    }

                    // Hanya buat jadwal jika tidak bentrok slot persis
                    Jadwal::firstOrCreate(
                        [
                            'tahun_ajaran_id' => $semesterTujuan->id,
                            'hari' => $j->hari->value ?? (string) $j->hari,
                            'jam_ke' => $j->jam_ke,
                            'kelas_id' => $targetKelasId,
                            'guru_id' => $j->guru_id,
                            'mata_pelajaran_id' => $j->mata_pelajaran_id,
                            'title' => $j->title,
                        ],
                        [
                            'jam_mulai' => $j->jam_mulai,
                            'jam_selesai' => $j->jam_selesai,
                            'ruang' => $j->ruang,
                        ]
                    );

                    $totalJadwal++;
                }
            }

            // 4. Salin Standar KKTP
            if ($salinKktp) {
                $kktps = $semesterAsal->kktps()->get();
                foreach ($kktps as $k) {
                    Kktp::firstOrCreate(
                        [
                            'tahun_ajaran_id' => $semesterTujuan->id,
                            'mata_pelajaran_id' => $k->mata_pelajaran_id,
                            'tingkat' => $k->tingkat,
                        ],
                        [
                            'nilai' => $k->nilai,
                        ]
                    );
                    $totalKktp++;
                }
            }

            return [
                'kelas' => $totalKelas,
                'siswa' => $totalSiswa,
                'jadwal' => $totalJadwal,
                'kktp' => $totalKktp,
            ];
        });
    }
}
