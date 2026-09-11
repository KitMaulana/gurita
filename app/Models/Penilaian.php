<?php

namespace App\Models;

use App\Enums\JenisPenilaian;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Penilaian extends Model
{
    use HasFactory, LogsActivity, SoftDeletes;

    protected $fillable = [
        'tahun_ajaran_id', 'kelas_id', 'mata_pelajaran_id', 'guru_id', 'bab_id',
        'tujuan_pembelajaran_id', 'jenis', 'nama', 'tanggal', 'bobot',
        'nilai_maksimal', 'is_remedial', 'is_terkunci',
    ];

    protected function casts(): array
    {
        return [
            'tanggal' => 'date',
            'jenis' => JenisPenilaian::class,
            'bobot' => 'decimal:2',
            'nilai_maksimal' => 'integer',
            'is_remedial' => 'boolean',
            'is_terkunci' => 'boolean',
        ];
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['nama', 'jenis', 'bobot', 'is_terkunci'])
            ->logOnlyDirty()
            ->useLogName('penilaian');
    }

    public function tahunAjaran(): BelongsTo
    {
        return $this->belongsTo(TahunAjaran::class);
    }

    public function kelas(): BelongsTo
    {
        return $this->belongsTo(Kelas::class);
    }

    public function mataPelajaran(): BelongsTo
    {
        return $this->belongsTo(MataPelajaran::class);
    }

    public function guru(): BelongsTo
    {
        return $this->belongsTo(User::class, 'guru_id');
    }

    public function bab(): BelongsTo
    {
        return $this->belongsTo(Bab::class);
    }

    public function tujuanPembelajaran(): BelongsTo
    {
        return $this->belongsTo(TujuanPembelajaran::class);
    }

    public function nilais(): HasMany
    {
        return $this->hasMany(Nilai::class);
    }

    public function scopeTahunAktif(Builder $query): Builder
    {
        return $query->where('tahun_ajaran_id', TahunAjaran::aktif()?->id);
    }

    public function jumlahBelumDinilai(): int
    {
        return $this->nilais()->whereNull('nilai')->count();
    }
}
