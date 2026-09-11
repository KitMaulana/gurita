<?php

namespace App\Support;

class WarnaKelas
{
    /**
     * Palet warna tetap untuk kelas-kelas sekolah (X, XI, XII)
     * agar kontras antar-kelas sangat jelas dan estetis.
     */
    protected static array $paletKhusus = [
        // ==========================================
        // TINGKAT X (Sepuluh)
        // ==========================================
        'x-1' => [
            'accent'     => '#10b981', // Emerald
            'bg_card'    => '#ecfdf5',
            'border'     => '#a7f3d0',
            'jp_bg'      => '#059669',
            'jp_text'    => '#ffffff',
            'badge_bg'   => '#d1fae5',
            'badge_text' => '#065f46',
            'dot'        => '#059669',
        ],
        'x-2' => [
            'accent'     => '#f59e0b', // Amber / Gold
            'bg_card'    => '#fffbeb',
            'border'     => '#fde68a',
            'jp_bg'      => '#d97706',
            'jp_text'    => '#ffffff',
            'badge_bg'   => '#fef3c7',
            'badge_text' => '#92400e',
            'dot'        => '#d97706',
        ],
        'x-3' => [
            'accent'     => '#3b82f6', // Royal Blue
            'bg_card'    => '#eff6ff',
            'border'     => '#bfdbfe',
            'jp_bg'      => '#2563eb',
            'jp_text'    => '#ffffff',
            'badge_bg'   => '#dbeafe',
            'badge_text' => '#1e40af',
            'dot'        => '#2563eb',
        ],
        'x-4' => [
            'accent'     => '#f43f5e', // Rose / Ruby
            'bg_card'    => '#fff1f2',
            'border'     => '#fecdd3',
            'jp_bg'      => '#e11d48',
            'jp_text'    => '#ffffff',
            'badge_bg'   => '#ffe4e6',
            'badge_text' => '#9f1239',
            'dot'        => '#e11d48',
        ],
        'x-5' => [
            'accent'     => '#8b5cf6', // Violet / Purple
            'bg_card'    => '#f5f3ff',
            'border'     => '#ddd6fe',
            'jp_bg'      => '#7c3aed',
            'jp_text'    => '#ffffff',
            'badge_bg'   => '#ede9fe',
            'badge_text' => '#5b21b6',
            'dot'        => '#7c3aed',
        ],
        'x-6' => [
            'accent'     => '#14b8a6', // Deep Teal
            'bg_card'    => '#f0fdfa',
            'border'     => '#99f6e4',
            'jp_bg'      => '#0d9488',
            'jp_text'    => '#ffffff',
            'badge_bg'   => '#ccfbf1',
            'badge_text' => '#115e59',
            'dot'        => '#0d9488',
        ],
        'x-7' => [
            'accent'     => '#ea580c', // Coral Orange
            'bg_card'    => '#fff7ed',
            'border'     => '#fed7aa',
            'jp_bg'      => '#c2410c',
            'jp_text'    => '#ffffff',
            'badge_bg'   => '#ffedd5',
            'badge_text' => '#9a3412',
            'dot'        => '#c2410c',
        ],
        'x-8' => [
            'accent'     => '#06b6d4', // Cyan
            'bg_card'    => '#ecfeff',
            'border'     => '#a5f3fc',
            'jp_bg'      => '#0891b2',
            'jp_text'    => '#ffffff',
            'badge_bg'   => '#cffafe',
            'badge_text' => '#155e75',
            'dot'        => '#0891b2',
        ],
        'x-9' => [
            'accent'     => '#d946ef', // Fuchsia
            'bg_card'    => '#fdf4ff',
            'border'     => '#f5d0fe',
            'jp_bg'      => '#c026d3',
            'jp_text'    => '#ffffff',
            'badge_bg'   => '#fae8ff',
            'badge_text' => '#86198f',
            'dot'        => '#c026d3',
        ],
        'x-10' => [
            'accent'     => '#84cc16', // Lime
            'bg_card'    => '#f7fee7',
            'border'     => '#d9f99d',
            'jp_bg'      => '#65a30d',
            'jp_text'    => '#ffffff',
            'badge_bg'   => '#ecfccb',
            'badge_text' => '#365314',
            'dot'        => '#65a30d',
        ],
        'x-11' => [
            'accent'     => '#ec4899', // Berry Pink
            'bg_card'    => '#fdf2f8',
            'border'     => '#fbcfe8',
            'jp_bg'      => '#db2777',
            'jp_text'    => '#ffffff',
            'badge_bg'   => '#fce7f3',
            'badge_text' => '#9d174d',
            'dot'        => '#db2777',
        ],
        'x-12' => [
            'accent'     => '#64748b', // Slate
            'bg_card'    => '#f8fafc',
            'border'     => '#cbd5e1',
            'jp_bg'      => '#475569',
            'jp_text'    => '#ffffff',
            'badge_bg'   => '#e2e8f0',
            'badge_text' => '#1e293b',
            'dot'        => '#475569',
        ],

        // ==========================================
        // TINGKAT XI (Sebelas)
        // ==========================================
        'xi 1-1' => [
            'accent'     => '#14b8a6', // Deep Teal
            'bg_card'    => '#f0fdfa',
            'border'     => '#99f6e4',
            'jp_bg'      => '#0d9488',
            'jp_text'    => '#ffffff',
            'badge_bg'   => '#ccfbf1',
            'badge_text' => '#115e59',
            'dot'        => '#0d9488',
        ],
        'xi 1-2' => [
            'accent'     => '#f59e0b', // Amber / Gold
            'bg_card'    => '#fffbeb',
            'border'     => '#fde68a',
            'jp_bg'      => '#d97706',
            'jp_text'    => '#ffffff',
            'badge_bg'   => '#fef3c7',
            'badge_text' => '#92400e',
            'dot'        => '#d97706',
        ],
        'xi 1-3' => [
            'accent'     => '#10b981', // Emerald Green
            'bg_card'    => '#ecfdf5',
            'border'     => '#a7f3d0',
            'jp_bg'      => '#059669',
            'jp_text'    => '#ffffff',
            'badge_bg'   => '#d1fae5',
            'badge_text' => '#065f46',
            'dot'        => '#059669',
        ],
        'xi 1-4' => [
            'accent'     => '#3b82f6', // Royal Blue
            'bg_card'    => '#eff6ff',
            'border'     => '#bfdbfe',
            'jp_bg'      => '#2563eb',
            'jp_text'    => '#ffffff',
            'badge_bg'   => '#dbeafe',
            'badge_text' => '#1e40af',
            'dot'        => '#2563eb',
        ],
        'xi 1-5' => [
            'accent'     => '#8b5cf6', // Violet / Purple
            'bg_card'    => '#f5f3ff',
            'border'     => '#ddd6fe',
            'jp_bg'      => '#7c3aed',
            'jp_text'    => '#ffffff',
            'badge_bg'   => '#ede9fe',
            'badge_text' => '#5b21b6',
            'dot'        => '#7c3aed',
        ],
        'xi 1-6' => [
            'accent'     => '#f43f5e', // Rose / Ruby
            'bg_card'    => '#fff1f2',
            'border'     => '#fecdd3',
            'jp_bg'      => '#e11d48',
            'jp_text'    => '#ffffff',
            'badge_bg'   => '#ffe4e6',
            'badge_text' => '#9f1239',
            'dot'        => '#e11d48',
        ],
        'xi 1-7' => [
            'accent'     => '#06b6d4', // Cyan
            'bg_card'    => '#ecfeff',
            'border'     => '#a5f3fc',
            'jp_bg'      => '#0891b2',
            'jp_text'    => '#ffffff',
            'badge_bg'   => '#cffafe',
            'badge_text' => '#155e75',
            'dot'        => '#0891b2',
        ],
        'xi 2-1' => [
            'accent'     => '#d946ef', // Fuchsia
            'bg_card'    => '#fdf4ff',
            'border'     => '#f5d0fe',
            'jp_bg'      => '#c026d3',
            'jp_text'    => '#ffffff',
            'badge_bg'   => '#fae8ff',
            'badge_text' => '#86198f',
            'dot'        => '#c026d3',
        ],
        'xi 2-2' => [
            'accent'     => '#ea580c', // Orange
            'bg_card'    => '#fff7ed',
            'border'     => '#fed7aa',
            'jp_bg'      => '#c2410c',
            'jp_text'    => '#ffffff',
            'badge_bg'   => '#ffedd5',
            'badge_text' => '#9a3412',
            'dot'        => '#c2410c',
        ],
        'xi 2-3' => [
            'accent'     => '#84cc16', // Lime
            'bg_card'    => '#f7fee7',
            'border'     => '#d9f99d',
            'jp_bg'      => '#65a30d',
            'jp_text'    => '#ffffff',
            'badge_bg'   => '#ecfccb',
            'badge_text' => '#365314',
            'dot'        => '#65a30d',
        ],
        'xi 2-4' => [
            'accent'     => '#0ea5e9', // Sky Blue
            'bg_card'    => '#f0f9ff',
            'border'     => '#bae6fd',
            'jp_bg'      => '#0284c7',
            'jp_text'    => '#ffffff',
            'badge_bg'   => '#e0f2fe',
            'badge_text' => '#075985',
            'dot'        => '#0284c7',
        ],
        'xi 2-5' => [
            'accent'     => '#ec4899', // Berry Pink
            'bg_card'    => '#fdf2f8',
            'border'     => '#fbcfe8',
            'jp_bg'      => '#db2777',
            'jp_text'    => '#ffffff',
            'badge_bg'   => '#fce7f3',
            'badge_text' => '#9d174d',
            'dot'        => '#db2777',
        ],

        // ==========================================
        // TINGKAT XII (Dua Belas)
        // ==========================================
        'xii 1-1' => [
            'accent'     => '#14b8a6', // Teal
            'bg_card'    => '#f0fdfa',
            'border'     => '#99f6e4',
            'jp_bg'      => '#0d9488',
            'jp_text'    => '#ffffff',
            'badge_bg'   => '#ccfbf1',
            'badge_text' => '#115e59',
            'dot'        => '#0d9488',
        ],
        'xii 1-2' => [
            'accent'     => '#84cc16', // Lime
            'bg_card'    => '#f7fee7',
            'border'     => '#d9f99d',
            'jp_bg'      => '#65a30d',
            'jp_text'    => '#ffffff',
            'badge_bg'   => '#ecfccb',
            'badge_text' => '#365314',
            'dot'        => '#65a30d',
        ],
        'xii 1-3' => [
            'accent'     => '#10b981', // Emerald Green
            'bg_card'    => '#ecfdf5',
            'border'     => '#a7f3d0',
            'jp_bg'      => '#059669',
            'jp_text'    => '#ffffff',
            'badge_bg'   => '#d1fae5',
            'badge_text' => '#065f46',
            'dot'        => '#059669',
        ],
        'xii 1-4' => [
            'accent'     => '#f59e0b', // Amber / Warm Gold
            'bg_card'    => '#fffbeb',
            'border'     => '#fde68a',
            'jp_bg'      => '#d97706',
            'jp_text'    => '#ffffff',
            'badge_bg'   => '#fef3c7',
            'badge_text' => '#92400e',
            'dot'        => '#d97706',
        ],
        'xii 1-5' => [
            'accent'     => '#8b5cf6', // Violet / Purple
            'bg_card'    => '#f5f3ff',
            'border'     => '#ddd6fe',
            'jp_bg'      => '#7c3aed',
            'jp_text'    => '#ffffff',
            'badge_bg'   => '#ede9fe',
            'badge_text' => '#5b21b6',
            'dot'        => '#7c3aed',
        ],
        'xii 1-6' => [
            'accent'     => '#f43f5e', // Rose / Ruby
            'bg_card'    => '#fff1f2',
            'border'     => '#fecdd3',
            'jp_bg'      => '#e11d48',
            'jp_text'    => '#ffffff',
            'badge_bg'   => '#ffe4e6',
            'badge_text' => '#9f1239',
            'dot'        => '#e11d48',
        ],
        'xii 1-7' => [
            'accent'     => '#06b6d4', // Cyan
            'bg_card'    => '#ecfeff',
            'border'     => '#a5f3fc',
            'jp_bg'      => '#0891b2',
            'jp_text'    => '#ffffff',
            'badge_bg'   => '#cffafe',
            'badge_text' => '#155e75',
            'dot'        => '#0891b2',
        ],
        'xii 1-8' => [
            'accent'     => '#3b82f6', // Royal Blue
            'bg_card'    => '#eff6ff',
            'border'     => '#bfdbfe',
            'jp_bg'      => '#2563eb',
            'jp_text'    => '#ffffff',
            'badge_bg'   => '#dbeafe',
            'badge_text' => '#1e40af',
            'dot'        => '#2563eb',
        ],
        'xii 2-1' => [
            'accent'     => '#d946ef', // Fuchsia
            'bg_card'    => '#fdf4ff',
            'border'     => '#f5d0fe',
            'jp_bg'      => '#c026d3',
            'jp_text'    => '#ffffff',
            'badge_bg'   => '#fae8ff',
            'badge_text' => '#86198f',
            'dot'        => '#c026d3',
        ],
        'xii 2-2' => [
            'accent'     => '#ea580c', // Orange
            'bg_card'    => '#fff7ed',
            'border'     => '#fed7aa',
            'jp_bg'      => '#c2410c',
            'jp_text'    => '#ffffff',
            'badge_bg'   => '#ffedd5',
            'badge_text' => '#9a3412',
            'dot'        => '#c2410c',
        ],
        'xii 2-3' => [
            'accent'     => '#ec4899', // Pink
            'bg_card'    => '#fdf2f8',
            'border'     => '#fbcfe8',
            'jp_bg'      => '#db2777',
            'jp_text'    => '#ffffff',
            'badge_bg'   => '#fce7f3',
            'badge_text' => '#9d174d',
            'dot'        => '#db2777',
        ],
        'xii 2-4' => [
            'accent'     => '#0ea5e9', // Sky Blue
            'bg_card'    => '#f0f9ff',
            'border'     => '#bae6fd',
            'jp_bg'      => '#0284c7',
            'jp_text'    => '#ffffff',
            'badge_bg'   => '#e0f2fe',
            'badge_text' => '#075985',
            'dot'        => '#0284c7',
        ],
        'xii 2-5' => [
            'accent'     => '#6366f1', // Indigo
            'bg_card'    => '#eef2ff',
            'border'     => '#c7d2fe',
            'jp_bg'      => '#4f46e5',
            'jp_text'    => '#ffffff',
            'badge_bg'   => '#e0e7ff',
            'badge_text' => '#3730a3',
            'dot'        => '#4f46e5',
        ],
        'xii 2-6' => [
            'accent'     => '#14b8a6', // Teal
            'bg_card'    => '#f0fdfa',
            'border'     => '#99f6e4',
            'jp_bg'      => '#0d9488',
            'jp_text'    => '#ffffff',
            'badge_bg'   => '#ccfbf1',
            'badge_text' => '#115e59',
            'dot'        => '#0d9488',
        ],

        // Agenda Bersama
        'semua kelas' => [
            'accent'     => '#f59e0b',
            'bg_card'    => '#fffbeb',
            'border'     => '#fde68a',
            'jp_bg'      => '#d97706',
            'jp_text'    => '#ffffff',
            'badge_bg'   => '#fef3c7',
            'badge_text' => '#92400e',
            'dot'        => '#d97706',
        ],
    ];

    /**
     * Palet warna dinamis / siklus untuk kelas apa pun yang tidak tercantum di atas.
     */
    protected static array $paletDinamis = [
        [
            'accent'     => '#10b981', // Emerald
            'bg_card'    => '#ecfdf5',
            'border'     => '#a7f3d0',
            'jp_bg'      => '#059669',
            'jp_text'    => '#ffffff',
            'badge_bg'   => '#d1fae5',
            'badge_text' => '#065f46',
            'dot'        => '#059669',
        ],
        [
            'accent'     => '#f59e0b', // Amber
            'bg_card'    => '#fffbeb',
            'border'     => '#fde68a',
            'jp_bg'      => '#d97706',
            'jp_text'    => '#ffffff',
            'badge_bg'   => '#fef3c7',
            'badge_text' => '#92400e',
            'dot'        => '#d97706',
        ],
        [
            'accent'     => '#8b5cf6', // Violet
            'bg_card'    => '#f5f3ff',
            'border'     => '#ddd6fe',
            'jp_bg'      => '#7c3aed',
            'jp_text'    => '#ffffff',
            'badge_bg'   => '#ede9fe',
            'badge_text' => '#5b21b6',
            'dot'        => '#7c3aed',
        ],
        [
            'accent'     => '#f43f5e', // Rose
            'bg_card'    => '#fff1f2',
            'border'     => '#fecdd3',
            'jp_bg'      => '#e11d48',
            'jp_text'    => '#ffffff',
            'badge_bg'   => '#ffe4e6',
            'badge_text' => '#9f1239',
            'dot'        => '#e11d48',
        ],
        [
            'accent'     => '#06b6d4', // Cyan
            'bg_card'    => '#ecfeff',
            'border'     => '#a5f3fc',
            'jp_bg'      => '#0891b2',
            'jp_text'    => '#ffffff',
            'badge_bg'   => '#cffafe',
            'badge_text' => '#155e75',
            'dot'        => '#0891b2',
        ],
        [
            'accent'     => '#3b82f6', // Blue
            'bg_card'    => '#eff6ff',
            'border'     => '#bfdbfe',
            'jp_bg'      => '#2563eb',
            'jp_text'    => '#ffffff',
            'badge_bg'   => '#dbeafe',
            'badge_text' => '#1e40af',
            'dot'        => '#2563eb',
        ],
        [
            'accent'     => '#14b8a6', // Teal
            'bg_card'    => '#f0fdfa',
            'border'     => '#99f6e4',
            'jp_bg'      => '#0d9488',
            'jp_text'    => '#ffffff',
            'badge_bg'   => '#ccfbf1',
            'badge_text' => '#115e59',
            'dot'        => '#0d9488',
        ],
        [
            'accent'     => '#ea580c', // Orange
            'bg_card'    => '#fff7ed',
            'border'     => '#fed7aa',
            'jp_bg'      => '#c2410c',
            'jp_text'    => '#ffffff',
            'badge_bg'   => '#ffedd5',
            'badge_text' => '#9a3412',
            'dot'        => '#c2410c',
        ],
        [
            'accent'     => '#d946ef', // Fuchsia
            'bg_card'    => '#fdf4ff',
            'border'     => '#f5d0fe',
            'jp_bg'      => '#c026d3',
            'jp_text'    => '#ffffff',
            'badge_bg'   => '#fae8ff',
            'badge_text' => '#86198f',
            'dot'        => '#c026d3',
        ],
        [
            'accent'     => '#84cc16', // Lime
            'bg_card'    => '#f7fee7',
            'border'     => '#d9f99d',
            'jp_bg'      => '#65a30d',
            'jp_text'    => '#ffffff',
            'badge_bg'   => '#ecfccb',
            'badge_text' => '#365314',
            'dot'        => '#65a30d',
        ],
        [
            'accent'     => '#ec4899', // Pink
            'bg_card'    => '#fdf2f8',
            'border'     => '#fbcfe8',
            'jp_bg'      => '#db2777',
            'jp_text'    => '#ffffff',
            'badge_bg'   => '#fce7f3',
            'badge_text' => '#9d174d',
            'dot'        => '#db2777',
        ],
        [
            'accent'     => '#6366f1', // Indigo
            'bg_card'    => '#eef2ff',
            'border'     => '#c7d2fe',
            'jp_bg'      => '#4f46e5',
            'jp_text'    => '#ffffff',
            'badge_bg'   => '#e0e7ff',
            'badge_text' => '#3730a3',
            'dot'        => '#4f46e5',
        ],
        [
            'accent'     => '#0ea5e9', // Sky
            'bg_card'    => '#f0f9ff',
            'border'     => '#bae6fd',
            'jp_bg'      => '#0284c7',
            'jp_text'    => '#ffffff',
            'badge_bg'   => '#e0f2fe',
            'badge_text' => '#075985',
            'dot'        => '#0284c7',
        ],
        [
            'accent'     => '#64748b', // Slate
            'bg_card'    => '#f8fafc',
            'border'     => '#cbd5e1',
            'jp_bg'      => '#475569',
            'jp_text'    => '#ffffff',
            'badge_bg'   => '#e2e8f0',
            'badge_text' => '#1e293b',
            'dot'        => '#475569',
        ],
    ];

    /**
     * Dapatkan spesifikasi warna lengkap untuk kelas tertentu.
     *
     * @param string|null $namaKelas Nama kelas (contoh: X-1, XI 1-3, XII 1-4)
     * @param bool $isAgendaBersama
     * @return array{accent: string, bg_card: string, border: string, jp_bg: string, jp_text: string, badge_bg: string, badge_text: string, dot: string}
     */
    public static function untuk(?string $namaKelas, bool $isAgendaBersama = false): array
    {
        if ($isAgendaBersama || ! $namaKelas || $namaKelas === '—') {
            return self::$paletKhusus['semua kelas'];
        }

        $kunci = strtolower(trim($namaKelas));

        // Normalisasi format tanda hubung / spasi (misal 'x 1' menjadi 'x-1')
        $kunciVariasi = [
            $kunci,
            str_replace(' ', '-', $kunci),
            str_replace('-', ' ', $kunci),
            str_replace('.', '-', $kunci),
        ];

        foreach ($kunciVariasi as $var) {
            if (isset(self::$paletKhusus[$var])) {
                return self::$paletKhusus[$var];
            }
        }

        // Cek kecocokan pola angka di akhir nama kelas (contoh: 'X-5' -> 5, 'Kelas 10' -> 10)
        if (preg_match('/(\d+)$/', $kunci, $matches)) {
            $nomor = (int) $matches[1];
            $index = ($nomor - 1) % count(self::$paletDinamis);
            if ($index < 0) {
                $index = 0;
            }
            return self::$paletDinamis[$index];
        }

        // Fallback deterministik berbasis hash nama kelas
        $index = abs(crc32($kunci)) % count(self::$paletDinamis);
        return self::$paletDinamis[$index];
    }
}
