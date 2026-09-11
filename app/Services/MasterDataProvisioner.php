<?php

namespace App\Services;

use App\Models\Kelas;
use App\Models\Kktp;
use App\Models\MataPelajaran;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class MasterDataProvisioner
{
    /** Hash password default 12345 dihitung satu kali saja agar hemat CPU */
    protected static ?string $defaultPasswordHash = null;

    /** Cache memori untuk mencegah ribuan query berulang saat impor massal */
    protected array $cacheKelas = [];
    protected array $cacheKelasCanonical = [];
    protected array $cacheMapel = [];
    protected array $cacheGuruNama = [];
    protected array $cacheGuruNip = [];
    protected bool $isPreloaded = false;

    /**
     * Memuat seluruh kelas, mata pelajaran, dan guru ke dalam memori
     * agar pencocokan 1000+ baris berjalan dalam hitungan milidetik.
     */
    public function preload(int $tahunAjaranId): void
    {
        if ($this->isPreloaded) {
            return;
        }

        foreach (Kelas::where('tahun_ajaran_id', $tahunAjaranId)->get() as $k) {
            $this->cacheKelas[$tahunAjaranId.'_'.mb_strtolower(trim($k->nama))] = $k;
            $canonical = Kelas::canonicalizeName($k->nama);
            if ($canonical !== '') {
                $this->cacheKelasCanonical[$tahunAjaranId.'_'.$canonical] ??= $k;
            }
        }

        foreach (MataPelajaran::all() as $m) {
            $this->cacheMapel[mb_strtolower(trim($m->nama))] = $m;
        }

        foreach (User::all() as $u) {
            $this->cacheGuruNama[mb_strtolower(trim($u->name))] = $u;
            if (! empty($u->nip)) {
                $this->cacheGuruNip[trim((string) $u->nip)] = $u;
            }
        }

        $this->isPreloaded = true;
    }

    /**
     * Cari kelas berdasarkan nama dan tahun ajaran tanpa membuat baru.
     */
    public function cariKelas(string $namaKelas, int $tahunAjaranId): ?Kelas
    {
        $namaClean = trim($namaKelas);
        $cacheKey = $tahunAjaranId.'_'.mb_strtolower($namaClean);

        if (isset($this->cacheKelas[$cacheKey])) {
            return $this->cacheKelas[$cacheKey];
        }

        $canonicalKey = $tahunAjaranId.'_'.Kelas::canonicalizeName($namaClean);
        if (isset($this->cacheKelasCanonical[$canonicalKey])) {
            return $this->cacheKelasCanonical[$canonicalKey];
        }

        $kelas = Kelas::where('tahun_ajaran_id', $tahunAjaranId)
            ->whereRaw('LOWER(nama) = ?', [mb_strtolower($namaClean)])
            ->first();

        if (! $kelas) {
            $canonical = Kelas::canonicalizeName($namaClean);
            if ($canonical !== '') {
                $semuaKelas = Kelas::where('tahun_ajaran_id', $tahunAjaranId)->get();
                foreach ($semuaKelas as $k) {
                    if (Kelas::canonicalizeName($k->nama) === $canonical) {
                        $kelas = $k;
                        break;
                    }
                }
            }
        }

        if ($kelas) {
            $this->cacheKelas[$cacheKey] = $kelas;
            if (isset($canonicalKey)) {
                $this->cacheKelasCanonical[$canonicalKey] = $kelas;
            }
        }

        return $kelas;
    }

    /**
     * Cari kelas berdasarkan nama dan tahun ajaran, atau buat baru bila belum ada.
     */
    public function temukanAtauBuatKelas(string $namaKelas, int $tahunAjaranId, ?bool &$dibuatBaru = null): Kelas
    {
        $dibuatBaru = false;
        $kelas = $this->cariKelas($namaKelas, $tahunAjaranId);
        if ($kelas) {
            return $kelas;
        }

        $namaClean = trim($namaKelas);
        $cacheKey = $tahunAjaranId.'_'.mb_strtolower($namaClean);
        $tingkat = $this->deteksiTingkat($namaClean);
        $jurusan = $this->deteksiJurusan($namaClean);

        $kelas = Kelas::create([
            'tahun_ajaran_id' => $tahunAjaranId,
            'nama' => $namaClean,
            'tingkat' => $tingkat,
            'jurusan' => $jurusan,
        ]);

        $this->cacheKelas[$cacheKey] = $kelas;
        $dibuatBaru = true;

        return $kelas;
    }

    /**
     * Cari mata pelajaran berdasarkan nama tanpa membuat baru.
     */
    public function cariMataPelajaran(string $namaMapel): ?MataPelajaran
    {
        $namaClean = trim($namaMapel);
        $cacheKey = mb_strtolower($namaClean);

        if (isset($this->cacheMapel[$cacheKey])) {
            return $this->cacheMapel[$cacheKey];
        }

        $mapel = MataPelajaran::whereRaw('LOWER(nama) = ?', [mb_strtolower($namaClean)])->first();

        if ($mapel) {
            $this->cacheMapel[$cacheKey] = $mapel;
        }

        return $mapel;
    }

    /**
     * Cari mata pelajaran berdasarkan nama, atau buat baru bila belum ada.
     * Otomatis membuatkan standar KKTP default (75) untuk tingkat X, XI, XII.
     */
    public function temukanAtauBuatMataPelajaran(string $namaMapel, int $tahunAjaranId, ?bool &$dibuatBaru = null): MataPelajaran
    {
        $dibuatBaru = false;
        $mapel = $this->cariMataPelajaran($namaMapel);
        if ($mapel) {
            return $mapel;
        }

        $namaClean = trim($namaMapel);
        $cacheKey = mb_strtolower($namaClean);
        $singkatan = $this->buatSingkatan($namaClean);

        $mapel = MataPelajaran::create([
            'nama' => $namaClean,
            'singkatan' => $singkatan,
            'kelompok' => 'umum',
        ]);

        // Inisialisasi KKTP default 75 untuk semua tingkat
        foreach (['X', 'XI', 'XII'] as $tingkat) {
            Kktp::firstOrCreate([
                'tahun_ajaran_id' => $tahunAjaranId,
                'mata_pelajaran_id' => $mapel->id,
                'tingkat' => $tingkat,
            ], [
                'nilai' => 75,
            ]);
        }

        $this->cacheMapel[$cacheKey] = $mapel;
        $dibuatBaru = true;

        return $mapel;
    }

    /**
     * Cari guru berdasarkan NIP atau nama tanpa membuat akun baru.
     */
    public function cariGuru(string $namaGuru, ?string $nip = null): ?User
    {
        $namaClean = trim($namaGuru);
        $nipClean = trim((string) $nip) ?: null;

        // 1. Cari via NIP di memori/cache jika ada
        if ($nipClean && isset($this->cacheGuruNip[$nipClean])) {
            return $this->cacheGuruNip[$nipClean];
        }

        // 2. Cari via nama di memori/cache
        $namaKey = mb_strtolower($namaClean);
        if ($namaKey !== '' && isset($this->cacheGuruNama[$namaKey])) {
            $guru = $this->cacheGuruNama[$namaKey];
            if ($nipClean && empty($guru->nip)) {
                $guru->update(['nip' => $nipClean]);
                $this->cacheGuruNip[$nipClean] = $guru;
            }
            return $guru;
        }

        // 3. Cari di DB jika belum ada di cache
        if ($nipClean) {
            $guru = User::where('nip', $nipClean)->first();
            if ($guru) {
                $this->cacheGuruNip[$nipClean] = $guru;
                $this->cacheGuruNama[mb_strtolower($guru->name)] = $guru;
                return $guru;
            }
        }

        if ($namaKey !== '') {
            $guru = User::whereRaw('LOWER(name) = ?', [$namaKey])->first();
            if ($guru) {
                if ($nipClean && empty($guru->nip)) {
                    $guru->update(['nip' => $nipClean]);
                    $this->cacheGuruNip[$nipClean] = $guru;
                }
                $this->cacheGuruNama[$namaKey] = $guru;
                return $guru;
            }
        }

        return null;
    }

    /**
     * Cari guru berdasarkan NIP atau nama, atau buatkan akun baru bila belum terdaftar.
     * Kata sandi bawaan ditetapkan ke '12345' (di-hash sekali) dan langsung diberi peran 'guru'.
     */
    public function temukanAtauBuatGuru(string $namaGuru, ?string $nip = null, ?bool &$dibuatBaru = null): User
    {
        $dibuatBaru = false;
        $guru = $this->cariGuru($namaGuru, $nip);
        if ($guru) {
            return $guru;
        }

        $namaClean = trim($namaGuru);
        $nipClean = trim((string) $nip) ?: null;
        $namaKey = mb_strtolower($namaClean);

        // Buat akun baru dengan email unik sekolah
        $email = $this->generateEmailGuru($namaClean);

        $guru = User::create([
            'name' => $namaClean,
            'nip' => $nipClean,
            'email' => $email,
            'password' => $this->getDefaultPasswordHash(), // Menggunakan hash yang sudah dicache
            'jabatan' => 'Guru Mata Pelajaran',
            'is_aktif' => true,
            'email_verified_at' => now(),
        ]);

        $guru->assignRole('guru');

        // Simpan ke memori
        $this->cacheGuruNama[$namaKey] = $guru;
        if ($nipClean) {
            $this->cacheGuruNip[$nipClean] = $guru;
        }

        $dibuatBaru = true;

        return $guru;
    }

    /**
     * Menghasilkan hash Bcrypt untuk password '12345' sekali saja.
     */
    protected function getDefaultPasswordHash(): string
    {
        if (self::$defaultPasswordHash === null) {
            self::$defaultPasswordHash = Hash::make('12345');
        }

        return self::$defaultPasswordHash;
    }

    protected function deteksiTingkat(string $namaKelas): string
    {
        $upper = strtoupper($namaKelas);

        if (preg_match('/\b(XII|12)\b/', $upper) || str_starts_with($upper, 'XII') || str_starts_with($upper, '12')) {
            return 'XII';
        }
        if (preg_match('/\b(XI|11)\b/', $upper) || str_starts_with($upper, 'XI') || str_starts_with($upper, '11')) {
            return 'XI';
        }
        if (preg_match('/\b(X|10)\b/', $upper) || str_starts_with($upper, 'X') || str_starts_with($upper, '10')) {
            return 'X';
        }

        return 'X';
    }

    protected function deteksiJurusan(string $namaKelas): ?string
    {
        $upper = strtoupper($namaKelas);

        if (preg_match('/(IPA|MIPA|SAINS)/', $upper)) {
            return 'IPA';
        }
        if (preg_match('/(IPS|SOSIAL)/', $upper)) {
            return 'IPS';
        }

        return null;
    }

    protected function buatSingkatan(string $nama): string
    {
        $kata = preg_split('/\s+/', trim($nama));

        if (count($kata) >= 2) {
            $singkatan = '';
            foreach ($kata as $k) {
                $singkatan .= mb_strtoupper(mb_substr($k, 0, 1));
            }
            $singkatan = mb_substr($singkatan, 0, 6);
        } else {
            $singkatan = mb_strtoupper(mb_substr($nama, 0, 4));
        }

        // Pastikan singkatan unik
        $base = $singkatan;
        $counter = 1;
        while (MataPelajaran::where('singkatan', $singkatan)->exists()) {
            $singkatan = $base.$counter;
            $counter++;
        }

        return $singkatan;
    }

    protected function generateEmailGuru(string $nama): string
    {
        $slug = Str::slug($nama, '.');
        if (empty($slug)) {
            $slug = 'guru.'.Str::random(5);
        }

        $baseEmail = "{$slug}@sman1ciruas.sch.id";
        $email = $baseEmail;
        $counter = 2;

        while (User::where('email', $email)->exists()) {
            $email = "{$slug}{$counter}@sman1ciruas.sch.id";
            $counter++;
        }

        return $email;
    }
}
