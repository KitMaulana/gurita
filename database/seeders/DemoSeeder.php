<?php

namespace Database\Seeders;

use App\Enums\HariEnum;
use App\Enums\JenisPenilaian;
use App\Enums\StatusAgenda;
use App\Enums\StatusPresensi;
use App\Models\Agenda;
use App\Models\Bab;
use App\Models\Jadwal;
use App\Models\Kelas;
use App\Models\MataPelajaran;
use App\Models\Nilai;
use App\Models\Penilaian;
use App\Models\Presensi;
use App\Models\Siswa;
use App\Models\TahunAjaran;
use App\Models\User;
use Faker\Factory as Faker;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

/**
 * Data contoh untuk mencoba fitur (36 siswa × 8 kelas, jadwal 2 JP/minggu,
 * agenda + presensi + penilaian beberapa pekan terakhir).
 *
 * Jalankan terpisah: php artisan db:seed --class=DemoSeeder
 * Data ini AMAN DIHAPUS sebelum aplikasi dipakai sungguhan.
 */
class DemoSeeder extends Seeder
{
    public function run(): void
    {
        $faker = Faker::create('id_ID');

        $tahunAjaran = TahunAjaran::aktif();
        $guru = User::where('email', 'guru@sman1ciruas.sch.id')->firstOrFail();
        $mapel = MataPelajaran::where('nama', 'Bahasa Indonesia')->firstOrFail();
        $kelasList = Kelas::where('tahun_ajaran_id', $tahunAjaran->id)->orderBy('nama')->get();

        if ($kelasList->isEmpty()) {
            $this->command?->warn('Belum ada kelas. Jalankan MasterDataSeeder lebih dahulu.');

            return;
        }

        $babs = Bab::where('mata_pelajaran_id', $mapel->id)->where('tingkat', 'XII')
            ->with('tujuanPembelajarans')->orderBy('urutan')->get();

        $nomorNisn = 71230000;

        foreach ($kelasList as $indexKelas => $kelas) {
            $this->command?->info("Menyiapkan data contoh untuk {$kelas->nama}…");

            // ── Siswa ────────────────────────────────────────────────
            for ($absen = 1; $absen <= 36; $absen++) {
                $jenisKelamin = $absen % 2 === 0 ? 'P' : 'L';

                $siswa = Siswa::firstOrCreate(
                    ['nisn' => (string) $nomorNisn++],
                    [
                        'nis' => (string) (12000 + $indexKelas * 100 + $absen),
                        'nama' => $jenisKelamin === 'L' ? $faker->name('male') : $faker->name('female'),
                        'jenis_kelamin' => $jenisKelamin,
                        'is_aktif' => true,
                    ]
                );

                $kelas->siswas()->syncWithoutDetaching([$siswa->id => ['no_absen' => $absen]]);
            }

            // ── Jadwal: 2 JP per minggu ──────────────────────────────
            $hari = HariEnum::cases()[$indexKelas % 5];   // Senin–Jumat bergilir

            foreach ([1, 2] as $offset) {
                $jamKe = 1 + ($indexKelas % 4) * 2 + ($offset - 1);
                $mulai = Carbon::createFromTime(7, 0)->addMinutes(45 * ($jamKe - 1));

                Jadwal::firstOrCreate([
                    'tahun_ajaran_id' => $tahunAjaran->id,
                    'kelas_id' => $kelas->id,
                    'mata_pelajaran_id' => $mapel->id,
                    'hari' => $hari->value,
                    'jam_ke' => $jamKe,
                ], [
                    'guru_id' => $guru->id,
                    'jam_mulai' => $mulai->format('H:i:s'),
                    'jam_selesai' => $mulai->copy()->addMinutes(45)->format('H:i:s'),
                    'ruang' => 'R.'.(10 + $indexKelas),
                ]);
            }
        }

        // ── Agenda + presensi untuk 6 pekan terakhir ─────────────────
        $jadwals = Jadwal::where('tahun_ajaran_id', $tahunAjaran->id)
            ->where('guru_id', $guru->id)
            ->with('kelas.siswas')
            ->get();

        $statusAcak = [
            StatusPresensi::Hadir, StatusPresensi::Hadir, StatusPresensi::Hadir,
            StatusPresensi::Hadir, StatusPresensi::Hadir, StatusPresensi::Hadir,
            StatusPresensi::Hadir, StatusPresensi::Hadir, StatusPresensi::Hadir,
            StatusPresensi::Sakit, StatusPresensi::Izin, StatusPresensi::Alfa,
        ];

        foreach ($jadwals as $jadwal) {
            if (! $jadwal->kelas) {
                continue;
            }

            $pertemuan = 0;

            for ($pekan = 6; $pekan >= 1; $pekan--) {
                $tanggal = now()->startOfWeek()->subWeeks($pekan)->addDays($jadwal->hari->nomor() - 1);

                if ($tanggal->isFuture()) {
                    continue;
                }

                $pertemuan++;
                $bab = $babs[intdiv($pertemuan - 1, 2) % max($babs->count(), 1)] ?? null;

                $agenda = Agenda::firstOrCreate(
                    ['jadwal_id' => $jadwal->id, 'tanggal' => $tanggal->toDateString()],
                    [
                        'pertemuan_ke' => $pertemuan,
                        'judul_materi' => $bab?->judul ?? 'Pembelajaran Bahasa Indonesia',
                        'uraian_kegiatan' => 'Diskusi kelompok, penugasan, dan presentasi hasil kerja.',
                        'metode' => $faker->randomElement(['Diskusi kelompok', 'Ceramah interaktif', 'Proyek', 'Tanya jawab']),
                        'bab_id' => $bab?->id,
                        'status' => StatusAgenda::Terlaksana->value,
                    ]
                );

                if ($agenda->presensis()->exists()) {
                    continue;
                }

                foreach ($jadwal->kelas->siswas as $siswa) {
                    $status = $faker->randomElement($statusAcak);

                    Presensi::create([
                        'agenda_id' => $agenda->id,
                        'siswa_id' => $siswa->id,
                        'status' => $status->value,
                        'keterangan' => $status->butuhKeterangan()
                            ? $faker->randomElement(['Surat dokter', 'Izin keluarga', 'Tanpa keterangan'])
                            : null,
                    ]);
                }
            }
        }

        // ── Penilaian + nilai ────────────────────────────────────────
        foreach ($kelasList as $kelas) {
            $siswas = $kelas->siswas()->get();

            $rencana = [
                [JenisPenilaian::Formatif, 'Tugas Menulis Paragraf Argumentatif', now()->subWeeks(5)],
                [JenisPenilaian::Formatif, 'Kuis Struktur Teks Editorial', now()->subWeeks(3)],
                [JenisPenilaian::SumatifLingkup, 'Sumatif Bab 1 — Teks Editorial', now()->subWeeks(2)],
                [JenisPenilaian::SumatifLingkup, 'Sumatif Bab 2 — Teks Cerita Sejarah', now()->subWeek()],
            ];

            foreach ($rencana as $i => [$jenis, $nama, $tanggal]) {
                $penilaian = Penilaian::firstOrCreate([
                    'tahun_ajaran_id' => $tahunAjaran->id,
                    'kelas_id' => $kelas->id,
                    'mata_pelajaran_id' => $mapel->id,
                    'nama' => $nama,
                ], [
                    'guru_id' => $guru->id,
                    'bab_id' => $babs[intdiv($i, 2)]->id ?? null,
                    'tujuan_pembelajaran_id' => $babs[intdiv($i, 2)]?->tujuanPembelajarans->first()?->id,
                    'jenis' => $jenis->value,
                    'tanggal' => $tanggal->toDateString(),
                    'nilai_maksimal' => 100,
                ]);

                foreach ($siswas as $siswa) {
                    Nilai::firstOrCreate(
                        ['penilaian_id' => $penilaian->id, 'siswa_id' => $siswa->id],
                        ['nilai' => $faker->numberBetween(60, 98)]
                    );
                }
            }
        }

        $this->command?->info('Data contoh selesai dibuat.');
    }
}
