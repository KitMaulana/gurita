<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Siswa extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['nisn', 'nis', 'nama', 'jenis_kelamin', 'is_aktif'];

    protected function casts(): array
    {
        return ['is_aktif' => 'boolean'];
    }

    public function kelas(): BelongsToMany
    {
        return $this->belongsToMany(Kelas::class, 'kelas_siswa', 'siswa_id', 'kelas_id')
            ->withPivot('no_absen')
            ->withTimestamps();
    }

    public function nilais(): HasMany
    {
        return $this->hasMany(Nilai::class);
    }

    public function presensis(): HasMany
    {
        return $this->hasMany(Presensi::class);
    }

    public function raporMapels(): HasMany
    {
        return $this->hasMany(RaporMapel::class);
    }

    public function scopeAktif(Builder $query): Builder
    {
        return $query->where('is_aktif', true);
    }

    public function getLabelJenisKelaminAttribute(): string
    {
        return $this->jenis_kelamin === 'L' ? 'Laki-laki' : 'Perempuan';
    }

    /** Kelas siswa pada tahun ajaran aktif. */
    public function kelasAktif(): ?Kelas
    {
        return $this->kelas()
            ->where('kelas.tahun_ajaran_id', TahunAjaran::aktif()?->id)
            ->first();
    }
}
