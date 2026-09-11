<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RaporMapel extends Model
{
    use HasFactory;

    protected $fillable = [
        'tahun_ajaran_id', 'kelas_id', 'mata_pelajaran_id', 'siswa_id',
        'nilai_formatif', 'nilai_sumatif_lingkup', 'nilai_sumatif_akhir',
        'nilai_akhir', 'predikat', 'is_tuntas', 'deskripsi_capaian',
        'jumlah_pertemuan', 'jumlah_hadir', 'jumlah_sakit', 'jumlah_izin',
        'jumlah_alfa', 'dibekukan_pada',
    ];

    protected function casts(): array
    {
        return [
            'nilai_formatif' => 'decimal:2',
            'nilai_sumatif_lingkup' => 'decimal:2',
            'nilai_sumatif_akhir' => 'decimal:2',
            'nilai_akhir' => 'decimal:2',
            'is_tuntas' => 'boolean',
            'dibekukan_pada' => 'datetime',
        ];
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

    public function siswa(): BelongsTo
    {
        return $this->belongsTo(Siswa::class);
    }
}
