<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TujuanPembelajaran extends Model
{
    use HasFactory;

    protected $fillable = ['bab_id', 'kode', 'deskripsi', 'urutan'];

    public function bab(): BelongsTo
    {
        return $this->belongsTo(Bab::class);
    }

    public function penilaians(): HasMany
    {
        return $this->hasMany(Penilaian::class);
    }

    public function getLabelAttribute(): string
    {
        return $this->kode.' — '.$this->deskripsi;
    }
}
