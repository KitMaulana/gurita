<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Nilai extends Model
{
    use HasFactory, LogsActivity;

    protected $table = 'nilais';

    protected $fillable = ['penilaian_id', 'siswa_id', 'nilai', 'catatan'];

    protected function casts(): array
    {
        return ['nilai' => 'decimal:2'];
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['nilai'])
            ->logOnlyDirty()
            ->useLogName('nilai');
    }

    public function penilaian(): BelongsTo
    {
        return $this->belongsTo(Penilaian::class);
    }

    public function siswa(): BelongsTo
    {
        return $this->belongsTo(Siswa::class);
    }
}
