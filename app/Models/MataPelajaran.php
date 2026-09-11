<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MataPelajaran extends Model
{
    use HasFactory;

    protected $fillable = ['nama', 'singkatan', 'kelompok'];

    public function jadwals(): HasMany
    {
        return $this->hasMany(Jadwal::class);
    }

    public function babs(): HasMany
    {
        return $this->hasMany(Bab::class);
    }

    public function penilaians(): HasMany
    {
        return $this->hasMany(Penilaian::class);
    }

    public function kktps(): HasMany
    {
        return $this->hasMany(Kktp::class);
    }

    public function folderMateris(): HasMany
    {
        return $this->hasMany(FolderMateri::class);
    }

    /**
     * PJOK boleh diampu satu guru untuk lebih dari satu kelas pada JP yang sama (§5.2).
     */
    public function bolehParalel(): bool
    {
        $teks = mb_strtoupper($this->singkatan.' '.$this->nama);

        return str_contains($teks, 'PJOK') || str_contains($teks, 'JASMANI');
    }

    public function getLabelKelompokAttribute(): string
    {
        return match ($this->kelompok) {
            'umum' => 'Umum',
            'pilihan' => 'Pilihan',
            'muatan_lokal' => 'Muatan Lokal',
            default => ucfirst((string) $this->kelompok),
        };
    }
}
