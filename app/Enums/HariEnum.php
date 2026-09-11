<?php

namespace App\Enums;

enum HariEnum: string
{
    case Senin = 'senin';
    case Selasa = 'selasa';
    case Rabu = 'rabu';
    case Kamis = 'kamis';
    case Jumat = 'jumat';
    case Sabtu = 'sabtu';

    public function label(): string
    {
        return match ($this) {
            self::Senin => 'Senin',
            self::Selasa => 'Selasa',
            self::Rabu => 'Rabu',
            self::Kamis => 'Kamis',
            self::Jumat => 'Jumat',
            self::Sabtu => 'Sabtu',
        };
    }

    /** Nomor hari ISO-8601 (Senin = 1). */
    public function nomor(): int
    {
        return match ($this) {
            self::Senin => 1,
            self::Selasa => 2,
            self::Rabu => 3,
            self::Kamis => 4,
            self::Jumat => 5,
            self::Sabtu => 6,
        };
    }

    public static function dariTanggal(\DateTimeInterface $tanggal): ?self
    {
        return match ((int) $tanggal->format('N')) {
            1 => self::Senin,
            2 => self::Selasa,
            3 => self::Rabu,
            4 => self::Kamis,
            5 => self::Jumat,
            6 => self::Sabtu,
            default => null,        // Minggu: tidak ada jadwal
        };
    }

    /** @return array<string,string> nilai => label */
    public static function pilihan(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn (self $h) => [$h->value => $h->label()])
            ->all();
    }
}
