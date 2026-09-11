<?php

namespace App\Support;

use Illuminate\Support\Carbon;

/** Pemformatan tanggal Bahasa Indonesia tanpa bergantung ekstensi intl. */
class Tanggal
{
    public const NAMA_HARI = [
        1 => 'Senin', 2 => 'Selasa', 3 => 'Rabu', 4 => 'Kamis',
        5 => 'Jumat', 6 => 'Sabtu', 7 => 'Minggu',
    ];

    public const NAMA_BULAN = [
        1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
        5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
        9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
    ];

    /** "Kamis, 6 Agustus 2026" */
    public static function lengkap(Carbon|string|null $tanggal): string
    {
        if (! $tanggal) {
            return '—';
        }

        $t = $tanggal instanceof Carbon ? $tanggal : Carbon::parse($tanggal);

        return self::NAMA_HARI[$t->dayOfWeekIso].', '.self::pendek($t);
    }

    /** "6 Agustus 2026" */
    public static function pendek(Carbon|string|null $tanggal): string
    {
        if (! $tanggal) {
            return '—';
        }

        $t = $tanggal instanceof Carbon ? $tanggal : Carbon::parse($tanggal);

        return $t->day.' '.self::NAMA_BULAN[$t->month].' '.$t->year;
    }

    /** "06/08/2026" */
    public static function angka(Carbon|string|null $tanggal): string
    {
        if (! $tanggal) {
            return '—';
        }

        $t = $tanggal instanceof Carbon ? $tanggal : Carbon::parse($tanggal);

        return $t->format('d/m/Y');
    }

    /** "Agustus 2026" */
    public static function bulanTahun(Carbon|string|null $tanggal): string
    {
        if (! $tanggal) {
            return '—';
        }

        $t = $tanggal instanceof Carbon ? $tanggal : Carbon::parse($tanggal);

        return self::NAMA_BULAN[$t->month].' '.$t->year;
    }

    public static function namaHari(Carbon|string|null $tanggal): string
    {
        if (! $tanggal) {
            return '—';
        }

        $t = $tanggal instanceof Carbon ? $tanggal : Carbon::parse($tanggal);

        return self::NAMA_HARI[$t->dayOfWeekIso];
    }
}
