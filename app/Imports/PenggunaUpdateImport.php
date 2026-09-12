<?php

namespace App\Imports;

use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

/**
 * Update massal data pengguna (email & NIP) dari berkas CSV/Excel.
 *
 * Format kolom: email_lama, email_baru, nip_baru
 *
 * Pencocokan pengguna berdasarkan email_lama.
 * Kolom email_baru dan nip_baru bersifat opsional — hanya kolom yang diisi yang akan diperbarui.
 */
class PenggunaUpdateImport implements ToCollection, WithHeadingRow
{
    /** @var array<int,string> */
    public array $galat = [];

    public int $jumlahDiperbarui = 0;

    public int $jumlahDilewati = 0;

    public function collection(Collection $baris): void
    {
        foreach ($baris as $i => $data) {
            $nomorBaris = $i + 2;   // +1 header, +1 basis satu

            $emailLama = mb_strtolower(trim((string) ($data['email_lama'] ?? '')));
            $emailBaru = trim((string) ($data['email_baru'] ?? '')) ?: null;
            $nipBaru   = trim((string) ($data['nip_baru'] ?? '')) ?: null;

            // Baris kosong → lewati tanpa pesan error
            if ($emailLama === '' && $emailBaru === null && $nipBaru === null) {
                continue;
            }

            // email_lama wajib diisi sebagai kunci pencocokan
            if ($emailLama === '') {
                $this->galat[] = "Baris {$nomorBaris}: kolom email_lama wajib diisi.";
                continue;
            }

            // Cari pengguna berdasarkan email_lama
            $pengguna = User::where('email', $emailLama)->first();

            if (! $pengguna) {
                $this->galat[] = "Baris {$nomorBaris}: pengguna dengan email \"{$emailLama}\" tidak ditemukan.";
                continue;
            }

            // Tidak ada data yang perlu diperbarui
            if ($emailBaru === null && $nipBaru === null) {
                $this->jumlahDilewati++;
                continue;
            }

            // Normalisasi email_baru
            if ($emailBaru !== null) {
                $emailBaru = mb_strtolower($emailBaru);
            }

            // Validasi email_baru dan nip_baru
            $aturan = [];
            $dataCek = [];

            if ($emailBaru !== null) {
                $dataCek['email_baru'] = $emailBaru;
                $aturan['email_baru'] = ['email', 'max:255', "unique:users,email,{$pengguna->id}"];
            }

            if ($nipBaru !== null) {
                $dataCek['nip_baru'] = $nipBaru;
                $aturan['nip_baru'] = ['string', 'max:30', "unique:users,nip,{$pengguna->id}"];
            }

            $validator = Validator::make($dataCek, $aturan, [
                'email_baru.email'  => "Baris {$nomorBaris}: format email_baru tidak valid.",
                'email_baru.unique' => "Baris {$nomorBaris}: email \"{$emailBaru}\" sudah dipakai pengguna lain.",
                'nip_baru.unique'   => "Baris {$nomorBaris}: NIP \"{$nipBaru}\" sudah dipakai pengguna lain.",
                'nip_baru.max'      => "Baris {$nomorBaris}: NIP maksimal 30 karakter.",
            ]);

            if ($validator->fails()) {
                foreach ($validator->errors()->all() as $pesan) {
                    $this->galat[] = $pesan;
                }
                continue;
            }

            // Update data pengguna
            DB::transaction(function () use ($pengguna, $emailBaru, $nipBaru) {
                $perubahan = [];

                if ($emailBaru !== null) {
                    $perubahan['email'] = $emailBaru;
                }

                if ($nipBaru !== null) {
                    $perubahan['nip'] = $nipBaru;
                }

                if (! empty($perubahan)) {
                    $pengguna->update($perubahan);
                }
            });

            $this->jumlahDiperbarui++;
        }
    }

    public function ringkasan(): string
    {
        $teks = "{$this->jumlahDiperbarui} pengguna diperbarui.";

        if ($this->jumlahDilewati > 0) {
            $teks .= " {$this->jumlahDilewati} baris dilewati (tidak ada perubahan).";
        }

        return $teks;
    }
}
