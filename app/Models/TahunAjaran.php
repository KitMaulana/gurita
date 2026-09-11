<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Cache;

class TahunAjaran extends Model
{
    use HasFactory;

    protected $fillable = [
        'nama', 'semester', 'tanggal_mulai', 'tanggal_selesai', 'is_aktif',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_mulai' => 'date',
            'tanggal_selesai' => 'date',
            'is_aktif' => 'boolean',
        ];
    }

    public function kelas(): HasMany
    {
        return $this->hasMany(Kelas::class);
    }

    public function jadwals(): HasMany
    {
        return $this->hasMany(Jadwal::class);
    }

    public function penilaians(): HasMany
    {
        return $this->hasMany(Penilaian::class);
    }

    public function kktps(): HasMany
    {
        return $this->hasMany(Kktp::class);
    }

    /** Cache per-request dan lintas-request agar tidak query berulang di setiap view/halaman. */
    protected static self|false|null $cacheAktif = null;

    public static function aktif(): ?self
    {
        if (static::$cacheAktif === null) {
            static::$cacheAktif = Cache::remember('tahun_ajaran_aktif', now()->addDay(), function () {
                return static::where('is_aktif', true)->first() ?: false;
            });
        }

        return static::$cacheAktif ?: null;
    }

    /** Dipanggil observer setelah pergantian tahun ajaran aktif. */
    public static function lupakanCache(): void
    {
        static::$cacheAktif = null;
        Cache::forget('tahun_ajaran_aktif');
    }

    public function getLabelAttribute(): string
    {
        return $this->nama.' — Semester '.ucfirst($this->semester);
    }

    /** Mencari semester lain dalam tahun pelajaran yang sama (mis. Ganjil jika saat ini Genap). */
    public function semesterSebelumnya(): ?self
    {
        return static::where('nama', $this->nama)
            ->where('id', '!=', $this->id)
            ->where('semester', $this->semester === 'genap' ? 'ganjil' : 'genap')
            ->first();
    }

    /** Seluruh semester lain dalam tahun pelajaran yang sama. */
    public function semesterSeTahun(): \Illuminate\Database\Eloquent\Collection
    {
        return static::where('nama', $this->nama)->where('id', '!=', $this->id)->get();
    }
}
