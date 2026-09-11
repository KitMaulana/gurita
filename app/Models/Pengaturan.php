<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Pengaturan extends Model
{
    protected $fillable = ['key', 'value'];

    public $timestamps = true;

    /** @var array<string,string>|null */
    protected static ?array $cache = null;

    /** Nilai bawaan bila tabel belum terisi (lihat CLAUDE.md §5.5). */
    public const BAWAAN = [
        'nama_sekolah' => 'SMA Negeri 1 Ciruas',
        'npsn' => '20601234',
        'alamat_sekolah' => 'Jl. Raya Serang – Jakarta Km. 9, Ciruas, Kabupaten Serang, Banten',
        'kepala_sekolah' => '',
        'nip_kepala_sekolah' => '',
        'logo' => '',
        'bobot_formatif' => '20',
        'bobot_sumatif_lingkup' => '40',
        'bobot_sumatif_akhir' => '40',
        'ambang_predikat_a' => '90',
        'ambang_predikat_b' => '80',
        'ambang_predikat_c' => '70',
        'ambang_alfa_peringatan' => '3',
        'remedial_dibatasi_kktp' => '1',
    ];

    protected static function ambilSemuaDariDb(): array
    {
        return Cache::rememberForever('pengaturan_semua', function () {
            return static::pluck('value', 'key')->all();
        });
    }

    public static function ambil(string $key, mixed $bawaan = null): mixed
    {
        static::$cache ??= static::ambilSemuaDariDb();

        return static::$cache[$key] ?? $bawaan ?? self::BAWAAN[$key] ?? null;
    }

    public static function angka(string $key): float
    {
        return (float) static::ambil($key);
    }

    public static function simpan(string $key, mixed $value): void
    {
        static::updateOrCreate(['key' => $key], ['value' => (string) $value]);
        static::lupakanCache();
    }

    /** @return array<string,string> */
    public static function semua(): array
    {
        static::$cache ??= static::ambilSemuaDariDb();

        return array_merge(self::BAWAAN, static::$cache);
    }

    public static function lupakanCache(): void
    {
        static::$cache = null;
        Cache::forget('pengaturan_semua');
    }
}
