<?php

namespace App\Models;

use App\Enums\StatusAgenda;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Agenda extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'jadwal_id', 'tanggal', 'pertemuan_ke', 'judul_materi', 'uraian_kegiatan',
        'metode', 'bab_id', 'catatan', 'status', 'is_terkunci',
    ];

    protected function casts(): array
    {
        return [
            'tanggal' => 'date',
            'status' => StatusAgenda::class,
            'is_terkunci' => 'boolean',
            'pertemuan_ke' => 'integer',
        ];
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['judul_materi', 'status', 'is_terkunci'])
            ->logOnlyDirty()
            ->useLogName('agenda');
    }

    public function jadwal(): BelongsTo
    {
        return $this->belongsTo(Jadwal::class);
    }

    public function bab(): BelongsTo
    {
        return $this->belongsTo(Bab::class);
    }

    public function presensis(): HasMany
    {
        return $this->hasMany(Presensi::class);
    }

    public function scopeTahunAktif(Builder $query): Builder
    {
        return $query->whereHas('jadwal', fn (Builder $q) => $q->tahunAktif());
    }

    public function scopeMilikGuru(Builder $query, int $guruId): Builder
    {
        return $query->whereHas('jadwal', fn (Builder $q) => $q->where('guru_id', $guruId));
    }

    /** Pertemuan efektif = ikut jadi pembagi persentase kehadiran (§8.3). */
    public function scopePertemuanEfektif(Builder $query): Builder
    {
        return $query->whereIn('status', [
            StatusAgenda::Terlaksana->value,
            StatusAgenda::TugasMandiri->value,
            StatusAgenda::Kosong->value,
        ]);
    }

    public function presensiSudahDiisi(): bool
    {
        return $this->presensis()->exists();
    }

    /** Label JP untuk agenda ini, misal: "JP 1–3" atau "JP 1". */
    public function getLabelJpAttribute(): string
    {
        return $this->jadwal?->label_blok_jp ?? '—';
    }

    /** Rentang angka JP untuk agenda ini, misal: "1–3" atau "1". */
    public function getRentangJpAttribute(): string
    {
        return $this->jadwal?->rentang_jp ?? (string) ($this->jadwal?->jam_ke ?? '—');
    }
}

