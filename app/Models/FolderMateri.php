<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FolderMateri extends Model
{
    use HasFactory;

    protected $fillable = [
        'mata_pelajaran_id', 'parent_id', 'guru_id', 'nama', 'deskripsi', 'warna',
    ];

    /** Pilihan gradient kartu (mengikuti tampilan prototipe). */
    public const WARNA = [
        'from-primary to-primary-600' => 'Navy',
        'from-accent to-accent-600' => 'Sage',
        'from-sky-500 to-sky-700' => 'Biru Langit',
        'from-amber-500 to-orange-600' => 'Jingga',
        'from-rose-500 to-rose-700' => 'Merah Muda',
        'from-violet-500 to-violet-700' => 'Ungu',
    ];

    public function mataPelajaran(): BelongsTo
    {
        return $this->belongsTo(MataPelajaran::class);
    }

    public function guru(): BelongsTo
    {
        return $this->belongsTo(User::class, 'guru_id');
    }

    public function induk(): BelongsTo
    {
        return $this->belongsTo(FolderMateri::class, 'parent_id');
    }

    public function anak(): HasMany
    {
        return $this->hasMany(FolderMateri::class, 'parent_id');
    }

    public function materis(): HasMany
    {
        return $this->hasMany(Materi::class);
    }

    public function kelas(): BelongsToMany
    {
        return $this->belongsToMany(Kelas::class, 'folder_materi_kelas', 'folder_materi_id', 'kelas_id');
    }
}
