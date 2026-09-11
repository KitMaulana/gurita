<?php

namespace App\Models;

use App\Enums\HariEnum;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Jadwal extends Model
{
    use HasFactory, LogsActivity, SoftDeletes;

    protected $fillable = [
        'tahun_ajaran_id', 'kelas_id', 'nama_kelas_jadwal', 'mata_pelajaran_id', 'title', 'guru_id',
        'hari', 'jam_ke', 'jam_mulai', 'jam_selesai', 'ruang',
    ];

    protected function casts(): array
    {
        return [
            'hari' => HariEnum::class,
            'jam_ke' => 'integer',
        ];
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['kelas_id', 'nama_kelas_jadwal', 'mata_pelajaran_id', 'title', 'guru_id', 'hari', 'jam_ke'])
            ->logOnlyDirty()
            ->useLogName('jadwal');
    }

    public function isAgendaBersama(): bool
    {
        return ! empty($this->title);
    }

    public function getNamaTampilanAttribute(): string
    {
        return $this->title ?: ($this->mataPelajaran?->nama ?? '—');
    }

    public function getKelasTampilanAttribute(): string
    {
        if ($this->isAgendaBersama()) {
            return 'Semua Kelas';
        }

        if ($this->kelas) {
            return $this->kelas->nama;
        }

        if ($this->nama_kelas_jadwal) {
            return $this->nama_kelas_jadwal;
        }

        return '—';
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

    public function agendas(): HasMany
    {
        return $this->hasMany(Agenda::class);
    }

    public function scopeTahunAktif(Builder $query): Builder
    {
        return $query->where('tahun_ajaran_id', TahunAjaran::aktif()?->id);
    }

    public function scopeMilikGuru(Builder $query, int $guruId): Builder
    {
        return $query->where('guru_id', $guruId);
    }

    /**
     * Jam mulai aktif (menyesuaikan mode jadwal, mis. Mode Ramadhan).
     */
    public function getJamMulaiAktifAttribute(): string
    {
        $mode = \App\Support\JamPelajaran::modeAktif();
        if ($mode !== \App\Support\JamPelajaran::MODE_REGULER && $this->jam_ke && $this->hari) {
            $slot = \App\Support\JamPelajaran::getSlotTime($this->jam_ke, $this->hari->value, $mode);
            if ($slot && ! empty($slot['start'])) {
                return $slot['start'];
            }
        }

        return (string) $this->jam_mulai;
    }

    /**
     * Jam selesai aktif (menyesuaikan mode jadwal, mis. Mode Ramadhan).
     */
    public function getJamSelesaiAktifAttribute(): string
    {
        $mode = \App\Support\JamPelajaran::modeAktif();
        if ($mode !== \App\Support\JamPelajaran::MODE_REGULER && $this->jam_ke && $this->hari) {
            $slot = \App\Support\JamPelajaran::getSlotTime($this->jam_ke, $this->hari->value, $mode);
            if ($slot && ! empty($slot['end'])) {
                return $slot['end'];
            }
        }

        return (string) $this->jam_selesai;
    }

    public function getJamAttribute(): string
    {
        return substr((string) $this->jam_mulai_aktif, 0, 5).' – '.substr((string) $this->jam_selesai_aktif, 0, 5);
    }

    /** Apakah jadwal ini sedang berlangsung sekarang (untuk penanda di beranda). */
    public function sedangBerlangsung(): bool
    {
        $sekarang = now();

        if ($this->hari !== HariEnum::dariTanggal($sekarang)) {
            return false;
        }

        $jam = $sekarang->format('H:i:s');
        $mulai = (string) $this->jam_mulai_aktif;
        $selesai = (string) $this->jam_selesai_aktif;

        if (strlen($mulai) === 5) {
            $mulai .= ':00';
        }
        if (strlen($selesai) === 5) {
            $selesai .= ':00';
        }

        return $jam >= $mulai && $jam <= $selesai;
    }
}
