<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Materi extends Model
{
    use HasFactory;

    protected $fillable = [
        'folder_materi_id', 'bab_id', 'judul', 'deskripsi', 'tipe',
        'path', 'url', 'ukuran', 'jumlah_unduh',
    ];

    protected function casts(): array
    {
        return [
            'ukuran' => 'integer',
            'jumlah_unduh' => 'integer',
        ];
    }

    public function folderMateri(): BelongsTo
    {
        return $this->belongsTo(FolderMateri::class);
    }

    public function bab(): BelongsTo
    {
        return $this->belongsTo(Bab::class);
    }

    public function getEkstensiAttribute(): string
    {
        return $this->path ? strtolower(pathinfo($this->path, PATHINFO_EXTENSION)) : '';
    }

    public function getUkuranTerbacaAttribute(): string
    {
        if (! $this->ukuran) {
            return '—';
        }

        $satuan = ['B', 'KB', 'MB', 'GB'];
        $ukuran = (float) $this->ukuran;
        $i = 0;

        while ($ukuran >= 1024 && $i < count($satuan) - 1) {
            $ukuran /= 1024;
            $i++;
        }

        return number_format($ukuran, $i === 0 ? 0 : 1, ',', '.').' '.$satuan[$i];
    }

    /** Ikon Heroicons per tipe/ekstensi berkas. */
    public function getIkonAttribute(): string
    {
        if ($this->tipe === 'tautan') {
            return 'link';
        }

        if ($this->tipe === 'video') {
            return 'video-camera';
        }

        return match ($this->ekstensi) {
            'pdf' => 'document-text',
            'doc', 'docx' => 'document',
            'ppt', 'pptx' => 'presentation-chart-bar',
            'xls', 'xlsx', 'csv' => 'table-cells',
            'jpg', 'jpeg', 'png', 'gif', 'webp' => 'photo',
            default => 'paper-clip',
        };
    }
}
