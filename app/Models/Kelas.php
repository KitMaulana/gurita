<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Kelas extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'kelas';

    protected $fillable = [
        'tahun_ajaran_id', 'nama', 'tingkat', 'jurusan', 'wali_kelas_id',
    ];

    public function tahunAjaran(): BelongsTo
    {
        return $this->belongsTo(TahunAjaran::class);
    }

    public function waliKelas(): BelongsTo
    {
        return $this->belongsTo(User::class, 'wali_kelas_id');
    }

    public function siswas(): BelongsToMany
    {
        return $this->belongsToMany(Siswa::class, 'kelas_siswa', 'kelas_id', 'siswa_id')
            ->withPivot('no_absen')
            ->withTimestamps()
            ->orderBy('kelas_siswa.no_absen');
    }

    public function jadwals(): HasMany
    {
        return $this->hasMany(Jadwal::class);
    }

    public function penilaians(): HasMany
    {
        return $this->hasMany(Penilaian::class);
    }

    public function raporMapels(): HasMany
    {
        return $this->hasMany(RaporMapel::class);
    }

    public function folderMateris(): BelongsToMany
    {
        return $this->belongsToMany(FolderMateri::class, 'folder_materi_kelas', 'kelas_id', 'folder_materi_id');
    }

    /** Batasi ke tahun ajaran yang sedang aktif (§6). */
    public function scopeTahunAktif(Builder $query): Builder
    {
        return $query->where('tahun_ajaran_id', TahunAjaran::aktif()?->id);
    }

    /**
     * Normalisasi nama kelas untuk pencocokan fleksibel (mengabaikan spasi, strip, titik, dan prefix 'kelas').
     * Contoh: "X 1", "X-1", "X.1", "Kelas X 1" semuanya menghasilkan "x1".
     */
    public static function canonicalizeName(?string $nama): string
    {
        if ($nama === null || trim($nama) === '') {
            return '';
        }

        $clean = mb_strtolower(trim($nama));
        $clean = preg_replace('/^(kelas|kls)\s+/i', '', $clean);

        return preg_replace('/[^a-z0-9]/', '', $clean);
    }
}
