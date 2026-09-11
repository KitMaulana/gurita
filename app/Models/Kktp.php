<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Kktp extends Model
{
    use HasFactory;

    protected $table = 'kktps';

    protected $fillable = ['tahun_ajaran_id', 'mata_pelajaran_id', 'tingkat', 'nilai'];

    protected function casts(): array
    {
        return ['nilai' => 'integer'];
    }

    public function tahunAjaran(): BelongsTo
    {
        return $this->belongsTo(TahunAjaran::class);
    }

    public function mataPelajaran(): BelongsTo
    {
        return $this->belongsTo(MataPelajaran::class);
    }

    /** Nilai KKTP untuk kombinasi mapel + tingkat pada tahun aktif; 75 bila belum diatur. */
    public static function untuk(int $mataPelajaranId, string $tingkat, ?int $tahunAjaranId = null): int
    {
        $tahunAjaranId ??= TahunAjaran::aktif()?->id;

        return (int) (static::query()
            ->where('tahun_ajaran_id', $tahunAjaranId)
            ->where('mata_pelajaran_id', $mataPelajaranId)
            ->where('tingkat', $tingkat)
            ->value('nilai') ?? 75);
    }
}
