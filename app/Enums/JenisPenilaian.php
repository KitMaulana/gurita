<?php

namespace App\Enums;

enum JenisPenilaian: string
{
    case Formatif = 'formatif';
    case SumatifLingkup = 'sumatif_lingkup';
    case SumatifAkhir = 'sumatif_akhir';

    public function label(): string
    {
        return match ($this) {
            self::Formatif => 'Formatif',
            self::SumatifLingkup => 'Sumatif Lingkup Materi',
            self::SumatifAkhir => 'Sumatif Akhir Semester',
        };
    }

    public function labelPendek(): string
    {
        return match ($this) {
            self::Formatif => 'Formatif',
            self::SumatifLingkup => 'Sumatif Lingkup',
            self::SumatifAkhir => 'Sumatif Akhir',
        };
    }

    public function warna(): string
    {
        return match ($this) {
            self::Formatif => 'bg-sky-100 text-sky-800 border-sky-200',
            self::SumatifLingkup => 'bg-accent-100 text-accent-700 border-accent-100',
            self::SumatifAkhir => 'bg-primary-100 text-primary-700 border-primary-100',
        };
    }

    /** Kunci bobot pada tabel `pengaturans`. */
    public function kunciBobot(): string
    {
        return match ($this) {
            self::Formatif => 'bobot_formatif',
            self::SumatifLingkup => 'bobot_sumatif_lingkup',
            self::SumatifAkhir => 'bobot_sumatif_akhir',
        };
    }

    /** @return array<string,string> */
    public static function pilihan(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn (self $j) => [$j->value => $j->label()])
            ->all();
    }
}
