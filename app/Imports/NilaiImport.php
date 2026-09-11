<?php

namespace App\Imports;

use App\Models\Nilai;
use App\Models\Penilaian;
use App\Models\Siswa;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

/**
 * Import nilai per penilaian dari template_nilai.csv: nisn,nama,nilai
 */
class NilaiImport implements ToCollection, WithHeadingRow
{
    /** @var array<int,string> */
    public array $galat = [];

    public int $jumlahTersimpan = 0;

    public function __construct(protected Penilaian $penilaian) {}

    public function collection(Collection $baris): void
    {
        $siswaIds = $this->penilaian->kelas->siswas()->pluck('siswas.id')->all();

        $siswaList = Siswa::whereIn('id', $siswaIds)->get();
        $siswaPerNisn = $siswaList->whereNotNull('nisn')->keyBy('nisn');
        $siswaPerNama = $siswaList->keyBy(fn ($s) => strtolower(trim($s->nama)));

        foreach ($baris as $i => $data) {
            $nomorBaris = $i + 2;
            $nisn = trim((string) ($data['nisn'] ?? ''));
            $nama = trim((string) ($data['nama'] ?? ''));
            $nilaiMentah = $data['nilai'] ?? null;

            if ($nisn === '' && $nama === '') {
                continue;
            }

            $siswa = null;
            if ($nisn !== '') {
                $siswa = $siswaPerNisn->get($nisn);
            }
            if (! $siswa && $nama !== '') {
                $siswa = $siswaPerNama->get(strtolower($nama));
            }

            if (! $siswa) {
                $identitas = $nisn !== '' ? "NISN {$nisn}" : "siswa '{$nama}'";
                $this->galat[] = "Baris {$nomorBaris}: {$identitas} bukan siswa kelas ini.";

                continue;
            }

            if ($nilaiMentah === null || $nilaiMentah === '') {
                continue;   // biarkan kosong = belum dinilai
            }

            $nilai = str_replace(',', '.', trim((string) $nilaiMentah));

            if (! is_numeric($nilai) || $nilai < 0 || $nilai > $this->penilaian->nilai_maksimal) {
                $this->galat[] = "Baris {$nomorBaris}: nilai harus 0–{$this->penilaian->nilai_maksimal}.";

                continue;
            }

            Nilai::updateOrCreate(
                ['penilaian_id' => $this->penilaian->id, 'siswa_id' => $siswa->id],
                ['nilai' => round((float) $nilai, 2)]
            );

            $this->jumlahTersimpan++;
        }
    }

    public function ringkasan(): string
    {
        return "{$this->jumlahTersimpan} nilai tersimpan.";
    }
}
