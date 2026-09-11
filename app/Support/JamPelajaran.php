<?php

namespace App\Support;

use App\Models\Pengaturan;

class JamPelajaran
{
    public const MODE_REGULER = 'reguler';
    public const MODE_RAMADHAN = 'ramadhan';
    public const MODE_KHUSUS = 'khusus';

    /**
     * Mendapatkan mode jadwal yang sedang aktif di sistem.
     */
    public static function modeAktif(): string
    {
        return Pengaturan::ambil('mode_jadwal', self::MODE_REGULER);
    }

    /**
     * Apakah sistem saat ini sedang dalam Mode Bulan Ramadhan.
     */
    public static function isModeRamadhan(): bool
    {
        return self::modeAktif() === self::MODE_RAMADHAN;
    }

    /**
     * Daftar seluruh mode jadwal yang didukung sistem.
     *
     * @return array<string, array{label: string, deskripsi: string, badge_class: string}>
     */
    public static function daftarMode(): array
    {
        return [
            self::MODE_REGULER => [
                'label' => 'Reguler / Normal',
                'deskripsi' => 'Jam pelajaran standar hari biasa (durasi 35–45 menit per JP).',
                'badge_class' => 'bg-slate-100 text-slate-800 border-slate-300',
            ],
            self::MODE_RAMADHAN => [
                'label' => 'Bulan Ramadhan',
                'deskripsi' => 'Penyesuaian jam belajar bulan puasa (durasi 25–30 menit per JP, kepulangan lebih awal).',
                'badge_class' => 'bg-emerald-100 text-emerald-800 border-emerald-300',
            ],
            self::MODE_KHUSUS => [
                'label' => 'Ujian / Khusus',
                'deskripsi' => 'Penyesuaian waktu untuk pekan asesmen, gladi, atau kegiatan khusus sekolah.',
                'badge_class' => 'bg-amber-100 text-amber-800 border-amber-300',
            ],
        ];
    }

    /**
     * Mengambil daftar slot waktu jam pelajaran berdasarkan hari dan mode aktif.
     *
     * @return array<int, array{number: int, start: string, end: string, label: string}>
     */
    public static function getTimeSlotsForDay(string $hari, ?string $mode = null): array
    {
        $hari = trim(mb_strtolower($hari));
        $mode = $mode ?: self::modeAktif();

        // Cek apakah ada kustomisasi manual dari pengaturan admin
        $kustom = Pengaturan::ambil("slot_jam_{$mode}_{$hari}");
        if ($kustom && is_string($kustom)) {
            $decoded = json_decode($kustom, true);
            if (is_array($decoded) && ! empty($decoded)) {
                return $decoded;
            }
        }

        if ($mode === self::MODE_RAMADHAN) {
            return self::getRamadhanSlots($hari);
        }

        if ($mode === self::MODE_KHUSUS) {
            return self::getKhususSlots($hari);
        }

        return self::getRegulerSlots($hari);
    }

    /**
     * Slot Waktu Mode Reguler (Bawaan Sekolah / SIDACHEERS).
     */
    protected static function getRegulerSlots(string $hari): array
    {
        if ($hari === 'senin') {
            return [
                1 => ['number' => 1, 'start' => '08:10', 'end' => '08:45', 'label' => 'JP 1 (08:10 – 08:45)'],
                2 => ['number' => 2, 'start' => '08:45', 'end' => '09:20', 'label' => 'JP 2 (08:45 – 09:20)'],
                3 => ['number' => 3, 'start' => '09:20', 'end' => '09:55', 'label' => 'JP 3 (09:20 – 09:55)'],
                4 => ['number' => 4, 'start' => '10:35', 'end' => '11:10', 'label' => 'JP 4 (10:35 – 11:10)'],
                5 => ['number' => 5, 'start' => '11:10', 'end' => '11:45', 'label' => 'JP 5 (11:10 – 11:45)'],
                6 => ['number' => 6, 'start' => '13:00', 'end' => '13:40', 'label' => 'JP 6 (13:00 – 13:40)'],
                7 => ['number' => 7, 'start' => '13:40', 'end' => '14:20', 'label' => 'JP 7 (13:40 – 14:20)'],
                8 => ['number' => 8, 'start' => '14:20', 'end' => '15:00', 'label' => 'JP 8 (14:20 – 15:00)'],
            ];
        }

        if ($hari === 'jumat') {
            return [
                1 => ['number' => 1, 'start' => '08:20', 'end' => '09:00', 'label' => 'JP 1 (08:20 – 09:00)'],
                2 => ['number' => 2, 'start' => '09:00', 'end' => '09:40', 'label' => 'JP 2 (09:00 – 09:40)'],
                3 => ['number' => 3, 'start' => '10:20', 'end' => '11:00', 'label' => 'JP 3 (10:20 – 11:00)'],
                4 => ['number' => 4, 'start' => '11:00', 'end' => '11:40', 'label' => 'JP 4 (11:00 – 11:40)'],
            ];
        }

        // Selasa, Rabu, Kamis, Sabtu
        return [
            1 => ['number' => 1, 'start' => '07:00', 'end' => '07:35', 'label' => 'JP 1 (07:00 – 07:35)'],
            2 => ['number' => 2, 'start' => '07:35', 'end' => '08:10', 'label' => 'JP 2 (07:35 – 08:10)'],
            3 => ['number' => 3, 'start' => '08:10', 'end' => '08:45', 'label' => 'JP 3 (08:10 – 08:45)'],
            4 => ['number' => 4, 'start' => '08:45', 'end' => '09:20', 'label' => 'JP 4 (08:45 – 09:20)'],
            5 => ['number' => 5, 'start' => '09:20', 'end' => '09:55', 'label' => 'JP 5 (09:20 – 09:55)'],
            6 => ['number' => 6, 'start' => '10:35', 'end' => '11:10', 'label' => 'JP 6 (10:35 – 11:10)'],
            7 => ['number' => 7, 'start' => '11:10', 'end' => '11:45', 'label' => 'JP 7 (11:10 – 11:45)'],
            8 => ['number' => 8, 'start' => '13:00', 'end' => '13:40', 'label' => 'JP 8 (13:00 – 13:40)'],
            9 => ['number' => 9, 'start' => '13:40', 'end' => '14:20', 'label' => 'JP 9 (13:40 – 14:20)'],
            10 => ['number' => 10, 'start' => '14:20', 'end' => '15:00', 'label' => 'JP 10 (14:20 – 15:00)'],
        ];
    }

    /**
     * Slot Waktu Mode Bulan Ramadhan (Durasi dipersingkat 25-30 menit, kepulangan lebih awal).
     */
    protected static function getRamadhanSlots(string $hari): array
    {
        if ($hari === 'senin') {
            return [
                1 => ['number' => 1, 'start' => '08:00', 'end' => '08:30', 'label' => 'JP 1 (08:00 – 08:30)'],
                2 => ['number' => 2, 'start' => '08:30', 'end' => '09:00', 'label' => 'JP 2 (08:30 – 09:00)'],
                3 => ['number' => 3, 'start' => '09:00', 'end' => '09:30', 'label' => 'JP 3 (09:00 – 09:30)'],
                4 => ['number' => 4, 'start' => '09:50', 'end' => '10:20', 'label' => 'JP 4 (09:50 – 10:20)'],
                5 => ['number' => 5, 'start' => '10:20', 'end' => '10:50', 'label' => 'JP 5 (10:20 – 10:50)'],
                6 => ['number' => 6, 'start' => '10:50', 'end' => '11:20', 'label' => 'JP 6 (10:50 – 11:20)'],
                7 => ['number' => 7, 'start' => '11:20', 'end' => '11:50', 'label' => 'JP 7 (11:20 – 11:50)'],
                8 => ['number' => 8, 'start' => '11:50', 'end' => '12:20', 'label' => 'JP 8 (11:50 – 12:20)'],
            ];
        }

        if ($hari === 'jumat') {
            return [
                1 => ['number' => 1, 'start' => '08:00', 'end' => '08:30', 'label' => 'JP 1 (08:00 – 08:30)'],
                2 => ['number' => 2, 'start' => '08:30', 'end' => '09:00', 'label' => 'JP 2 (08:30 – 09:00)'],
                3 => ['number' => 3, 'start' => '09:00', 'end' => '09:30', 'label' => 'JP 3 (09:00 – 09:30)'],
                4 => ['number' => 4, 'start' => '09:30', 'end' => '10:00', 'label' => 'JP 4 (09:30 – 10:00)'],
            ];
        }

        // Selasa, Rabu, Kamis, Sabtu (Ramadhan)
        return [
            1 => ['number' => 1, 'start' => '07:30', 'end' => '08:00', 'label' => 'JP 1 (07:30 – 08:00)'],
            2 => ['number' => 2, 'start' => '08:00', 'end' => '08:30', 'label' => 'JP 2 (08:00 – 08:30)'],
            3 => ['number' => 3, 'start' => '08:30', 'end' => '09:00', 'label' => 'JP 3 (08:30 – 09:00)'],
            4 => ['number' => 4, 'start' => '09:00', 'end' => '09:30', 'label' => 'JP 4 (09:00 – 09:30)'],
            5 => ['number' => 5, 'start' => '09:50', 'end' => '10:20', 'label' => 'JP 5 (09:50 – 10:20)'],
            6 => ['number' => 6, 'start' => '10:20', 'end' => '10:50', 'label' => 'JP 6 (10:20 – 10:50)'],
            7 => ['number' => 7, 'start' => '10:50', 'end' => '11:20', 'label' => 'JP 7 (10:50 – 11:20)'],
            8 => ['number' => 8, 'start' => '11:20', 'end' => '11:50', 'label' => 'JP 8 (11:20 – 11:50)'],
            9 => ['number' => 9, 'start' => '11:50', 'end' => '12:20', 'label' => 'JP 9 (11:50 – 12:20)'],
            10 => ['number' => 10, 'start' => '12:20', 'end' => '12:50', 'label' => 'JP 10 (12:20 – 12:50)'],
        ];
    }

    /**
     * Slot Waktu Mode Ujian / Khusus (Blok sesi asesmen).
     */
    protected static function getKhususSlots(string $hari): array
    {
        if ($hari === 'jumat') {
            return [
                1 => ['number' => 1, 'start' => '07:30', 'end' => '09:00', 'label' => 'Sesi 1 (07:30 – 09:00)'],
                2 => ['number' => 2, 'start' => '09:30', 'end' => '11:00', 'label' => 'Sesi 2 (09:30 – 11:00)'],
            ];
        }

        return [
            1 => ['number' => 1, 'start' => '07:30', 'end' => '09:00', 'label' => 'Sesi 1 (07:30 – 09:00)'],
            2 => ['number' => 2, 'start' => '09:00', 'end' => '09:30', 'label' => 'Istirahat (09:00 – 09:30)'],
            3 => ['number' => 3, 'start' => '09:30', 'end' => '11:00', 'label' => 'Sesi 2 (09:30 – 11:00)'],
            4 => ['number' => 4, 'start' => '11:30', 'end' => '13:00', 'label' => 'Sesi 3 (11:30 – 13:00)'],
            5 => ['number' => 5, 'start' => '13:00', 'end' => '14:30', 'label' => 'Sesi 4 (13:00 – 14:30)'],
        ];
    }

    /**
     * Mendapatkan waktu jam mulai dan selesai untuk JP tertentu pada hari tertentu dan mode aktif.
     *
     * @return array{number: int, start: string, end: string, label: string}|null
     */
    public static function getSlotTime(int $jamKe, string $hari, ?string $mode = null): ?array
    {
        $slots = self::getTimeSlotsForDay($hari, $mode);

        return $slots[$jamKe] ?? null;
    }

    /**
     * Seluruh slot untuk kebutuhan client-side JavaScript.
     */
    public static function getAllSlotsJson(?string $mode = null): array
    {
        return [
            'senin' => self::getTimeSlotsForDay('senin', $mode),
            'selasa' => self::getTimeSlotsForDay('selasa', $mode),
            'rabu' => self::getTimeSlotsForDay('rabu', $mode),
            'kamis' => self::getTimeSlotsForDay('kamis', $mode),
            'jumat' => self::getTimeSlotsForDay('jumat', $mode),
            'sabtu' => self::getTimeSlotsForDay('sabtu', $mode),
        ];
    }

    /**
     * Daftar rekomendasi kegiatan bersama / agenda sekolah.
     */
    public static function rekomendasiAgendaBersama(): array
    {
        return [
            'UPACARA BENDERA / PEMBIASAAN',
            'Makan Bergizi Gratis Bersama (MBG)',
            'SHOLAT DHUHUR BERJAMAAH & ISTIRAHAT',
            'PEMBIASAAN / KAJIAN ISLAMI (RAMADHAN)',
            'SHOLAT DHUHA BERSAMA (RAMADHAN)',
            'ISTIRAHAT (SHOLAT JUMAT BERJAMAAH)',
            'EKSTRAKURIKULER',
        ];
    }
}
