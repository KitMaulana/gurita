<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, HasRoles, Notifiable;

    /** @var list<string> */
    protected $fillable = [
        'name', 'nip', 'email', 'password', 'jabatan', 'quote', 'foto', 'is_aktif',
    ];

    /** @var list<string> */
    protected $hidden = ['password', 'remember_token'];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_aktif' => 'boolean',
        ];
    }

    public function jadwals(): HasMany
    {
        return $this->hasMany(Jadwal::class, 'guru_id');
    }

    public function penilaians(): HasMany
    {
        return $this->hasMany(Penilaian::class, 'guru_id');
    }

    public function kelasPerwalian(): HasMany
    {
        return $this->hasMany(Kelas::class, 'wali_kelas_id');
    }

    public function folderMateris(): HasMany
    {
        return $this->hasMany(FolderMateri::class, 'guru_id');
    }

    public function scopeAktif(Builder $query): Builder
    {
        return $query->where('is_aktif', true);
    }

    public function scopeGuru(Builder $query): Builder
    {
        return $query->whereHas('roles', fn (Builder $q) => $q->whereIn('name', ['guru', 'wali_kelas']));
    }

    public function isAdmin(): bool
    {
        return $this->hasRole('admin');
    }

    /** Id kelas yang diampu guru ini pada tahun ajaran aktif. */
    public function idKelasDiampu(): array
    {
        return Jadwal::query()
            ->tahunAktif()
            ->where('guru_id', $this->id)
            ->distinct()
            ->pluck('kelas_id')
            ->all();
    }

    /** Kelas yang boleh dilihat: yang diampu + kelas perwalian. */
    public function kelasBolehDilihat(): Collection
    {
        if ($this->isAdmin()) {
            return Kelas::tahunAktif()->orderBy('nama')->get();
        }

        $ids = array_unique(array_merge(
            $this->idKelasDiampu(),
            $this->kelasPerwalian()->where('tahun_ajaran_id', TahunAjaran::aktif()?->id)->pluck('id')->all(),
        ));

        return Kelas::whereIn('id', $ids)->orderBy('nama')->get();
    }

    public function getInisialAttribute(): string
    {
        return collect(explode(' ', trim($this->name)))
            ->take(2)
            ->map(fn ($kata) => mb_strtoupper(mb_substr($kata, 0, 1)))
            ->implode('');
    }

    public function getFotoUrlAttribute(): ?string
    {
        if (! $this->foto) {
            return null;
        }

        return \Illuminate\Support\Facades\Storage::disk('public')->url($this->foto);
    }
}
