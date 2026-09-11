<?php

namespace App\Enums;

enum StatusPresensi: string
{
    case Hadir = 'hadir';
    case Sakit = 'sakit';
    case Izin = 'izin';
    case Alfa = 'alfa';
    case Bolos = 'bolos';
    case Dispensasi = 'dispensasi';

    public function label(): string
    {
        return match ($this) {
            self::Hadir => 'Hadir',
            self::Sakit => 'Sakit',
            self::Izin => 'Izin',
            self::Alfa => 'Alfa',
            self::Bolos => 'Bolos',
            self::Dispensasi => 'Dispensasi',
        };
    }

    /** Huruf singkat untuk tombol & rekap cetak. */
    public function singkatan(): string
    {
        return match ($this) {
            self::Hadir => 'H',
            self::Sakit => 'S',
            self::Izin => 'I',
            self::Alfa => 'A',
            self::Bolos => 'B',
            self::Dispensasi => 'D',
        };
    }

    public function warna(): string
    {
        return match ($this) {
            self::Hadir => 'bg-emerald-100 text-emerald-800 border-emerald-200',
            self::Sakit => 'bg-amber-100 text-amber-800 border-amber-200',
            self::Izin => 'bg-sky-100 text-sky-800 border-sky-200',
            self::Alfa => 'bg-rose-100 text-rose-800 border-rose-200',
            self::Bolos => 'bg-red-200 text-red-900 border-red-300',
            self::Dispensasi => 'bg-violet-100 text-violet-800 border-violet-200',
        };
    }

    /** Status yang dihitung sebagai kehadiran (§8.3: dispensasi dihitung hadir). */
    public function dihitungHadir(): bool
    {
        return in_array($this, [self::Hadir, self::Dispensasi], true);
    }

    /** Status selain hadir wajib diberi keterangan. */
    public function butuhKeterangan(): bool
    {
        return $this !== self::Hadir;
    }

    /** @return array<string,string> */
    public static function pilihan(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn (self $s) => [$s->value => $s->label()])
            ->all();
    }
}
