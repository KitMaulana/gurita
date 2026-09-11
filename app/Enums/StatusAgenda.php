<?php

namespace App\Enums;

enum StatusAgenda: string
{
    case Terlaksana = 'terlaksana';
    case TugasMandiri = 'tugas_mandiri';
    case Kosong = 'kosong';
    case Libur = 'libur';
    case KegiatanSekolah = 'kegiatan_sekolah';

    public function label(): string
    {
        return match ($this) {
            self::Terlaksana => 'Terlaksana',
            self::TugasMandiri => 'Tugas Mandiri',
            self::Kosong => 'Kosong',
            self::Libur => 'Libur',
            self::KegiatanSekolah => 'Kegiatan Sekolah',
        };
    }

    public function warna(): string
    {
        return match ($this) {
            self::Terlaksana => 'bg-emerald-100 text-emerald-800 border-emerald-200',
            self::TugasMandiri => 'bg-sky-100 text-sky-800 border-sky-200',
            self::Kosong => 'bg-rose-100 text-rose-800 border-rose-200',
            self::Libur => 'bg-slate-100 text-slate-700 border-slate-200',
            self::KegiatanSekolah => 'bg-violet-100 text-violet-800 border-violet-200',
        };
    }

    /**
     * Apakah pertemuan ini dihitung sebagai pertemuan efektif (§8.3).
     * Libur & kegiatan sekolah tidak dihitung sebagai pembagi persentase kehadiran.
     */
    public function dihitungSebagaiPertemuan(): bool
    {
        return in_array($this, [self::Terlaksana, self::TugasMandiri, self::Kosong], true);
    }

    /** Hanya status ini yang memerlukan pengisian presensi siswa. */
    public function perluPresensi(): bool
    {
        return in_array($this, [self::Terlaksana, self::TugasMandiri], true);
    }

    /** @return array<string,string> */
    public static function pilihan(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn (self $s) => [$s->value => $s->label()])
            ->all();
    }
}
