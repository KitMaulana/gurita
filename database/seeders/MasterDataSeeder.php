<?php

namespace Database\Seeders;

use App\Models\Bab;
use App\Models\Kelas;
use App\Models\Kktp;
use App\Models\MataPelajaran;
use App\Models\TahunAjaran;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Master data minimum agar aplikasi langsung bisa dipakai:
 * tahun ajaran aktif, mapel contoh, kelas XII IPA 1–8, bab & TP Bahasa Indonesia.
 */
class MasterDataSeeder extends Seeder
{
    public function run(): void
    {
        $tahunAjaran = TahunAjaran::updateOrCreate(
            ['nama' => '2026/2027', 'semester' => 'ganjil'],
            [
                'tanggal_mulai' => '2026-07-13',
                'tanggal_selesai' => '2026-12-19',
                'is_aktif' => true,
            ]
        );

        $mapels = collect([
            ['nama' => 'Bahasa Indonesia', 'singkatan' => 'BIN', 'kelompok' => 'umum'],
            ['nama' => 'Matematika', 'singkatan' => 'MAT', 'kelompok' => 'umum'],
            ['nama' => 'Bahasa Inggris', 'singkatan' => 'BIG', 'kelompok' => 'umum'],
            ['nama' => 'Pendidikan Jasmani, Olahraga, dan Kesehatan', 'singkatan' => 'PJOK', 'kelompok' => 'umum'],
        ])->map(fn (array $data) => MataPelajaran::updateOrCreate(['nama' => $data['nama']], $data));

        $waliKelas = User::where('email', 'guru@sman1ciruas.sch.id')->first();

        for ($i = 1; $i <= 8; $i++) {
            Kelas::updateOrCreate(
                ['tahun_ajaran_id' => $tahunAjaran->id, 'nama' => "XII IPA {$i}"],
                [
                    'tingkat' => 'XII',
                    'jurusan' => 'IPA',
                    'wali_kelas_id' => $i === 1 ? $waliKelas?->id : null,
                ]
            );
        }

        // KKTP 75 untuk semua mapel di semua tingkat.
        foreach ($mapels as $mapel) {
            foreach (['X', 'XI', 'XII'] as $tingkat) {
                Kktp::updateOrCreate([
                    'tahun_ajaran_id' => $tahunAjaran->id,
                    'mata_pelajaran_id' => $mapel->id,
                    'tingkat' => $tingkat,
                ], ['nilai' => 75]);
            }
        }

        $this->babBahasaIndonesia($mapels->firstWhere('nama', 'Bahasa Indonesia'));
    }

    protected function babBahasaIndonesia(MataPelajaran $mapel): void
    {
        $struktur = [
            [
                'kode' => 'BAB 1',
                'judul' => 'Teks Editorial',
                'cp' => 'Peserta didik mampu memahami, menanggapi, dan menulis teks editorial dengan '
                    .'argumentasi yang logis serta didukung data yang memadai.',
                'tp' => [
                    'Mengidentifikasi struktur dan kaidah kebahasaan teks editorial',
                    'Menganalisis sikap penulis dalam teks editorial',
                    'Menulis teks editorial dengan argumentasi yang logis',
                ],
            ],
            [
                'kode' => 'BAB 2',
                'judul' => 'Teks Cerita Sejarah',
                'cp' => 'Peserta didik mampu menganalisis dan menyusun teks cerita sejarah '
                    .'dengan memperhatikan struktur dan kaidah kebahasaannya.',
                'tp' => [
                    'Mengidentifikasi informasi penting dalam teks cerita sejarah',
                    'Menganalisis struktur dan kaidah kebahasaan teks cerita sejarah',
                    'Menyusun teks cerita sejarah berdasarkan peristiwa nyata',
                ],
            ],
            [
                'kode' => 'BAB 3',
                'judul' => 'Novel',
                'cp' => 'Peserta didik mampu menganalisis unsur intrinsik dan ekstrinsik novel '
                    .'serta menyajikan tanggapan atasnya.',
                'tp' => [
                    'Menganalisis unsur intrinsik novel',
                    'Menganalisis unsur ekstrinsik dan nilai-nilai dalam novel',
                    'Menyajikan tanggapan terhadap isi novel secara lisan dan tulis',
                ],
            ],
            [
                'kode' => 'BAB 4',
                'judul' => 'Surat Lamaran Pekerjaan',
                'cp' => 'Peserta didik mampu menyusun surat lamaran pekerjaan sesuai kaidah '
                    .'bahasa Indonesia yang baik dan benar.',
                'tp' => [
                    'Mengidentifikasi sistematika surat lamaran pekerjaan',
                    'Menganalisis kebahasaan surat lamaran pekerjaan',
                    'Menyusun surat lamaran pekerjaan yang efektif',
                ],
            ],
            [
                'kode' => 'BAB 5',
                'judul' => 'Kritik dan Esai',
                'cp' => 'Peserta didik mampu membandingkan kritik sastra dan esai serta '
                    .'menyusun keduanya secara sistematis.',
                'tp' => [
                    'Membandingkan kritik sastra dan esai',
                    'Menganalisis sistematika dan kebahasaan kritik dan esai',
                    'Menyusun kritik atau esai berdasarkan karya sastra',
                ],
            ],
        ];

        foreach ($struktur as $urutan => $data) {
            $bab = Bab::updateOrCreate(
                ['mata_pelajaran_id' => $mapel->id, 'tingkat' => 'XII', 'kode' => $data['kode']],
                [
                    'judul' => $data['judul'],
                    'capaian_pembelajaran' => $data['cp'],
                    'urutan' => $urutan + 1,
                ]
            );

            foreach ($data['tp'] as $i => $deskripsi) {
                $bab->tujuanPembelajarans()->updateOrCreate(
                    ['kode' => 'TP '.($urutan + 1).'.'.($i + 1)],
                    ['deskripsi' => $deskripsi, 'urutan' => $i + 1]
                );
            }
        }
    }
}
