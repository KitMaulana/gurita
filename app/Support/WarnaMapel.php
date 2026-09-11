<?php

namespace App\Support;

use App\Enums\HariEnum;

class WarnaMapel
{
    /**
     * Palet warna tetap untuk mata pelajaran umum.
     * Menggunakan warna-warna modern, kontras tinggi, dan nyaman dibaca.
     */
    protected static array $paletKhusus = [
        // Bahasa
        'bahasa indonesia' => [
            'nama'       => 'Bahasa Indonesia',
            'accent'     => '#3b82f6', // blue-500
            'bg_card'    => '#eff6ff', // blue-50
            'border'     => '#bfdbfe', // blue-200
            'jp_bg'      => '#2563eb', // blue-600
            'jp_text'    => '#ffffff',
            'badge_bg'   => '#dbeafe', // blue-100
            'badge_text' => '#1e40af', // blue-800
            'dot'        => '#2563eb',
        ],
        'bahasa inggris' => [
            'nama'       => 'Bahasa Inggris',
            'accent'     => '#8b5cf6', // violet-500
            'bg_card'    => '#f5f3ff', // violet-50
            'border'     => '#ddd6fe', // violet-200
            'jp_bg'      => '#7c3aed', // violet-600
            'jp_text'    => '#ffffff',
            'badge_bg'   => '#ede9fe', // violet-100
            'badge_text' => '#5b21b6', // violet-800
            'dot'        => '#7c3aed',
        ],
        'bahasa inggris tingkat lanjut' => [
            'nama'       => 'Bahasa Inggris TL',
            'accent'     => '#7c3aed', // violet-600
            'bg_card'    => '#f5f3ff',
            'border'     => '#c4b5fd',
            'jp_bg'      => '#6d28d9',
            'jp_text'    => '#ffffff',
            'badge_bg'   => '#ede9fe',
            'badge_text' => '#4c1d95',
            'dot'        => '#6d28d9',
        ],
        'bahasa inggris wajib' => [
            'nama'       => 'Bahasa Inggris Wajib',
            'accent'     => '#8b5cf6',
            'bg_card'    => '#f5f3ff',
            'border'     => '#ddd6fe',
            'jp_bg'      => '#7c3aed',
            'jp_text'    => '#ffffff',
            'badge_bg'   => '#ede9fe',
            'badge_text' => '#5b21b6',
            'dot'        => '#7c3aed',
        ],

        // MIPA
        'matematika' => [
            'nama'       => 'Matematika',
            'accent'     => '#10b981', // emerald-500
            'bg_card'    => '#ecfdf5', // emerald-50
            'border'     => '#a7f3d0', // emerald-200
            'jp_bg'      => '#059669', // emerald-600
            'jp_text'    => '#ffffff',
            'badge_bg'   => '#d1fae5', // emerald-100
            'badge_text' => '#065f46', // emerald-800
            'dot'        => '#059669',
        ],
        'matematika wajib' => [
            'nama'       => 'Matematika Wajib',
            'accent'     => '#10b981',
            'bg_card'    => '#ecfdf5',
            'border'     => '#a7f3d0',
            'jp_bg'      => '#059669',
            'jp_text'    => '#ffffff',
            'badge_bg'   => '#d1fae5',
            'badge_text' => '#065f46',
            'dot'        => '#059669',
        ],
        'matematika tingkat lanjut' => [
            'nama'       => 'Matematika TL',
            'accent'     => '#059669',
            'bg_card'    => '#ecfdf5',
            'border'     => '#6ee7b7',
            'jp_bg'      => '#047857',
            'jp_text'    => '#ffffff',
            'badge_bg'   => '#a7f3d0',
            'badge_text' => '#064e3b',
            'dot'        => '#047857',
        ],
        'fisika' => [
            'nama'       => 'Fisika',
            'accent'     => '#0ea5e9', // sky-500
            'bg_card'    => '#f0f9ff', // sky-50
            'border'     => '#bae6fd', // sky-200
            'jp_bg'      => '#0284c7', // sky-600
            'jp_text'    => '#ffffff',
            'badge_bg'   => '#e0f2fe', // sky-100
            'badge_text' => '#075985', // sky-800
            'dot'        => '#0284c7',
        ],
        'kimia' => [
            'nama'       => 'Kimia',
            'accent'     => '#14b8a6', // teal-500
            'bg_card'    => '#f0fdfa', // teal-50
            'border'     => '#99f6e4', // teal-200
            'jp_bg'      => '#0d9488', // teal-600
            'jp_text'    => '#ffffff',
            'badge_bg'   => '#ccfbf1', // teal-100
            'badge_text' => '#115e59', // teal-800
            'dot'        => '#0d9488',
        ],
        'biologi' => [
            'nama'       => 'Biologi',
            'accent'     => '#84cc16', // lime-500
            'bg_card'    => '#f7fee7', // lime-50
            'border'     => '#d9f99d', // lime-200
            'jp_bg'      => '#65a30d', // lime-600
            'jp_text'    => '#ffffff',
            'badge_bg'   => '#ecfccb', // lime-100
            'badge_text' => '#3f6212', // lime-800
            'dot'        => '#65a30d',
        ],
        'ipa' => [
            'nama'       => 'IPA',
            'accent'     => '#0ea5e9',
            'bg_card'    => '#f0f9ff',
            'border'     => '#bae6fd',
            'jp_bg'      => '#0284c7',
            'jp_text'    => '#ffffff',
            'badge_bg'   => '#e0f2fe',
            'badge_text' => '#075985',
            'dot'        => '#0284c7',
        ],

        // IPS & Sejarah
        'sejarah' => [
            'nama'       => 'Sejarah',
            'accent'     => '#f59e0b', // amber-500
            'bg_card'    => '#fffbeb', // amber-50
            'border'     => '#fde68a', // amber-200
            'jp_bg'      => '#d97706', // amber-600
            'jp_text'    => '#ffffff',
            'badge_bg'   => '#fef3c7', // amber-100
            'badge_text' => '#92400e', // amber-800
            'dot'        => '#d97706',
        ],
        'sejarah indonesia' => [
            'nama'       => 'Sejarah Indonesia',
            'accent'     => '#f59e0b',
            'bg_card'    => '#fffbeb',
            'border'     => '#fde68a',
            'jp_bg'      => '#d97706',
            'jp_text'    => '#ffffff',
            'badge_bg'   => '#fef3c7',
            'badge_text' => '#92400e',
            'dot'        => '#d97706',
        ],
        'sejarah tingkat lanjut' => [
            'nama'       => 'Sejarah TL',
            'accent'     => '#d97706',
            'bg_card'    => '#fffbeb',
            'border'     => '#fcd34d',
            'jp_bg'      => '#b45309',
            'jp_text'    => '#ffffff',
            'badge_bg'   => '#fde68a',
            'badge_text' => '#78350f',
            'dot'        => '#b45309',
        ],
        'geografi' => [
            'nama'       => 'Geografi',
            'accent'     => '#ea580c', // orange-600
            'bg_card'    => '#fff7ed', // orange-50
            'border'     => '#fed7aa', // orange-200
            'jp_bg'      => '#c2410c', // orange-700
            'jp_text'    => '#ffffff',
            'badge_bg'   => '#ffedd5', // orange-100
            'badge_text' => '#9a3412', // orange-800
            'dot'        => '#c2410c',
        ],
        'ekonomi' => [
            'nama'       => 'Ekonomi',
            'accent'     => '#0284c7', // sky-600
            'bg_card'    => '#f0f9ff',
            'border'     => '#bae6fd',
            'jp_bg'      => '#0369a1',
            'jp_text'    => '#ffffff',
            'badge_bg'   => '#e0f2fe',
            'badge_text' => '#0c4a6e',
            'dot'        => '#0369a1',
        ],
        'sosiologi' => [
            'nama'       => 'Sosiologi',
            'accent'     => '#d97706', // amber-600
            'bg_card'    => '#fffbeb',
            'border'     => '#fde68a',
            'jp_bg'      => '#b45309',
            'jp_text'    => '#ffffff',
            'badge_bg'   => '#fef3c7',
            'badge_text' => '#78350f',
            'dot'        => '#b45309',
        ],

        // Lainnya
        'ppkn' => [
            'nama'       => 'PPKn',
            'accent'     => '#e11d48', // rose-600
            'bg_card'    => '#fff1f2', // rose-50
            'border'     => '#fecdd3', // rose-200
            'jp_bg'      => '#be123c', // rose-700
            'jp_text'    => '#ffffff',
            'badge_bg'   => '#ffe4e6', // rose-100
            'badge_text' => '#9f1239', // rose-800
            'dot'        => '#be123c',
        ],
        'pkn' => [
            'nama'       => 'PKn',
            'accent'     => '#e11d48',
            'bg_card'    => '#fff1f2',
            'border'     => '#fecdd3',
            'jp_bg'      => '#be123c',
            'jp_text'    => '#ffffff',
            'badge_bg'   => '#ffe4e6',
            'badge_text' => '#9f1239',
            'dot'        => '#be123c',
        ],
        'pai' => [
            'nama'       => 'Pendidikan Agama Islam',
            'accent'     => '#059669', // emerald-600
            'bg_card'    => '#ecfdf5',
            'border'     => '#a7f3d0',
            'jp_bg'      => '#047857',
            'jp_text'    => '#ffffff',
            'badge_bg'   => '#d1fae5',
            'badge_text' => '#064e3b',
            'dot'        => '#047857',
        ],
        'pendidikan agama islam' => [
            'nama'       => 'PAI',
            'accent'     => '#059669',
            'bg_card'    => '#ecfdf5',
            'border'     => '#a7f3d0',
            'jp_bg'      => '#047857',
            'jp_text'    => '#ffffff',
            'badge_bg'   => '#d1fae5',
            'badge_text' => '#064e3b',
            'dot'        => '#047857',
        ],
        'pjok' => [
            'nama'       => 'PJOK',
            'accent'     => '#f97316', // orange-500
            'bg_card'    => '#fff7ed',
            'border'     => '#fed7aa',
            'jp_bg'      => '#ea580c',
            'jp_text'    => '#ffffff',
            'badge_bg'   => '#ffedd5',
            'badge_text' => '#9a3412',
            'dot'        => '#ea580c',
        ],
        'seni budaya' => [
            'nama'       => 'Seni Budaya',
            'accent'     => '#c026d3', // fuchsia-600
            'bg_card'    => '#fdf4ff', // fuchsia-50
            'border'     => '#f5d0fe', // fuchsia-200
            'jp_bg'      => '#a21caf', // fuchsia-700
            'jp_text'    => '#ffffff',
            'badge_bg'   => '#fae8ff', // fuchsia-100
            'badge_text' => '#86198f', // fuchsia-800
            'dot'        => '#a21caf',
        ],
        'pkwu' => [
            'nama'       => 'PKWU',
            'accent'     => '#4f46e5', // indigo-600
            'bg_card'    => '#eef2ff', // indigo-50
            'border'     => '#c7d2fe', // indigo-200
            'jp_bg'      => '#4338ca', // indigo-700
            'jp_text'    => '#ffffff',
            'badge_bg'   => '#e0e7ff', // indigo-100
            'badge_text' => '#3730a3', // indigo-800
            'dot'        => '#4338ca',
        ],
        'prakarya' => [
            'nama'       => 'Prakarya',
            'accent'     => '#4f46e5',
            'bg_card'    => '#eef2ff',
            'border'     => '#c7d2fe',
            'jp_bg'      => '#4338ca',
            'jp_text'    => '#ffffff',
            'badge_bg'   => '#e0e7ff',
            'badge_text' => '#3730a3',
            'dot'        => '#4338ca',
        ],
        'tik' => [
            'nama'       => 'TIK',
            'accent'     => '#06b6d4', // cyan-500
            'bg_card'    => '#ecfeff', // cyan-50
            'border'     => '#a5f3fc', // cyan-200
            'jp_bg'      => '#0891b2', // cyan-600
            'jp_text'    => '#ffffff',
            'badge_bg'   => '#cffafe', // cyan-100
            'badge_text' => '#155e75', // cyan-800
            'dot'        => '#0891b2',
        ],
        'coding & tik' => [
            'nama'       => 'Coding & TIK',
            'accent'     => '#06b6d4',
            'bg_card'    => '#ecfeff',
            'border'     => '#a5f3fc',
            'jp_bg'      => '#0891b2',
            'jp_text'    => '#ffffff',
            'badge_bg'   => '#cffafe',
            'badge_text' => '#155e75',
            'dot'        => '#0891b2',
        ],
        'bp/bk' => [
            'nama'       => 'BP/BK',
            'accent'     => '#db2777', // pink-600
            'bg_card'    => '#fdf2f8', // pink-50
            'border'     => '#fbcfe8', // pink-200
            'jp_bg'      => '#be185d', // pink-700
            'jp_text'    => '#ffffff',
            'badge_bg'   => '#fce7f3', // pink-100
            'badge_text' => '#9d174d', // pink-800
            'dot'        => '#be185d',
        ],
        'bk' => [
            'nama'       => 'BK',
            'accent'     => '#db2777',
            'bg_card'    => '#fdf2f8',
            'border'     => '#fbcfe8',
            'jp_bg'      => '#be185d',
            'jp_text'    => '#ffffff',
            'badge_bg'   => '#fce7f3',
            'badge_text' => '#9d174d',
            'dot'        => '#be185d',
        ],
        'agenda bersama' => [
            'nama'       => 'Agenda Bersama',
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
     * Palet warna dinamis / fallback jika mata pelajaran belum terdaftar di palet khusus.
     * Dipilih secara deterministik menggunakan hash nama mata pelajaran.
     */
    protected static array $paletDinamis = [
        [
            'accent'     => '#3b82f6',
            'bg_card'    => '#eff6ff',
            'border'     => '#bfdbfe',
            'jp_bg'      => '#2563eb',
            'jp_text'    => '#ffffff',
            'badge_bg'   => '#dbeafe',
            'badge_text' => '#1e40af',
            'dot'        => '#2563eb',
        ],
        [
            'accent'     => '#10b981',
            'bg_card'    => '#ecfdf5',
            'border'     => '#a7f3d0',
            'jp_bg'      => '#059669',
            'jp_text'    => '#ffffff',
            'badge_bg'   => '#d1fae5',
            'badge_text' => '#065f46',
            'dot'        => '#059669',
        ],
        [
            'accent'     => '#8b5cf6',
            'bg_card'    => '#f5f3ff',
            'border'     => '#ddd6fe',
            'jp_bg'      => '#7c3aed',
            'jp_text'    => '#ffffff',
            'badge_bg'   => '#ede9fe',
            'badge_text' => '#5b21b6',
            'dot'        => '#7c3aed',
        ],
        [
            'accent'     => '#f59e0b',
            'bg_card'    => '#fffbeb',
            'border'     => '#fde68a',
            'jp_bg'      => '#d97706',
            'jp_text'    => '#ffffff',
            'badge_bg'   => '#fef3c7',
            'badge_text' => '#92400e',
            'dot'        => '#d97706',
        ],
        [
            'accent'     => '#0ea5e9',
            'bg_card'    => '#f0f9ff',
            'border'     => '#bae6fd',
            'jp_bg'      => '#0284c7',
            'jp_text'    => '#ffffff',
            'badge_bg'   => '#e0f2fe',
            'badge_text' => '#075985',
            'dot'        => '#0284c7',
        ],
        [
            'accent'     => '#e11d48',
            'bg_card'    => '#fff1f2',
            'border'     => '#fecdd3',
            'jp_bg'      => '#be123c',
            'jp_text'    => '#ffffff',
            'badge_bg'   => '#ffe4e6',
            'badge_text' => '#9f1239',
            'dot'        => '#be123c',
        ],
        [
            'accent'     => '#14b8a6',
            'bg_card'    => '#f0fdfa',
            'border'     => '#99f6e4',
            'jp_bg'      => '#0d9488',
            'jp_text'    => '#ffffff',
            'badge_bg'   => '#ccfbf1',
            'badge_text' => '#115e59',
            'dot'        => '#0d9488',
        ],
        [
            'accent'     => '#c026d3',
            'bg_card'    => '#fdf4ff',
            'border'     => '#f5d0fe',
            'jp_bg'      => '#a21caf',
            'jp_text'    => '#ffffff',
            'badge_bg'   => '#fae8ff',
            'badge_text' => '#86198f',
            'dot'        => '#a21caf',
        ],
        [
            'accent'     => '#ea580c',
            'bg_card'    => '#fff7ed',
            'border'     => '#fed7aa',
            'jp_bg'      => '#c2410c',
            'jp_text'    => '#ffffff',
            'badge_bg'   => '#ffedd5',
            'badge_text' => '#9a3412',
            'dot'        => '#c2410c',
        ],
        [
            'accent'     => '#65a30d',
            'bg_card'    => '#f7fee7',
            'border'     => '#d9f99d',
            'jp_bg'      => '#4d7c0f',
            'jp_text'    => '#ffffff',
            'badge_bg'   => '#ecfccb',
            'badge_text' => '#365314',
            'dot'        => '#4d7c0f',
        ],
        [
            'accent'     => '#db2777',
            'bg_card'    => '#fdf2f8',
            'border'     => '#fbcfe8',
            'jp_bg'      => '#be185d',
            'jp_text'    => '#ffffff',
            'badge_bg'   => '#fce7f3',
            'badge_text' => '#9d174d',
            'dot'        => '#be185d',
        ],
    ];

    /**
     * Dapatkan spesifikasi warna lengkap untuk mata pelajaran atau agenda tertentu.
     *
     * @param string|null $namaMapel Nama mata pelajaran atau judul agenda
     * @param bool $isAgendaBersama Apakah jadwal ini agenda bersama sekolah
     * @return array{accent: string, bg_card: string, border: string, jp_bg: string, jp_text: string, badge_bg: string, badge_text: string, dot: string}
     */
    public static function untuk(?string $namaMapel, bool $isAgendaBersama = false): array
    {
        if ($isAgendaBersama || ! $namaMapel) {
            return self::$paletKhusus['agenda bersama'];
        }

        $kunci = strtolower(trim($namaMapel));

        // Cek kecocokan langsung
        if (isset(self::$paletKhusus[$kunci])) {
            return self::$paletKhusus[$kunci];
        }

        // Cek kecocokan parsial
        foreach (self::$paletKhusus as $pola => $warna) {
            if (str_contains($kunci, $pola) || str_contains($pola, $kunci)) {
                return $warna;
            }
        }

        // Fallback deterministik berbasis hash
        $index = abs(crc32($kunci)) % count(self::$paletDinamis);
        return self::$paletDinamis[$index];
    }

    /**
     * Dapatkan gradien warna header untuk hari-hari sekolah (Senin - Sabtu).
     *
     * @param HariEnum|string $hari
     * @return array{header: string, badge_bg: string, badge_border: string, accent: string, icon: string}
     */
    public static function warnaHari(HariEnum|string $hari): array
    {
        $nilai = $hari instanceof HariEnum ? $hari->value : (string) $hari;

        return match ($nilai) {
            'senin' => [
                'header'       => 'linear-gradient(135deg, #1e3a8a 0%, #2563eb 100%)', // Deep Blue
                'badge_bg'     => 'rgba(255, 255, 255, 0.2)',
                'badge_border' => 'rgba(255, 255, 255, 0.35)',
                'accent'       => '#3b82f6',
                'ring'         => '#93c5fd',
                'label'        => 'Senin',
            ],
            'selasa' => [
                'header'       => 'linear-gradient(135deg, #065f46 0%, #059669 100%)', // Emerald Green
                'badge_bg'     => 'rgba(255, 255, 255, 0.2)',
                'badge_border' => 'rgba(255, 255, 255, 0.35)',
                'accent'       => '#10b981',
                'ring'         => '#6ee7b7',
                'label'        => 'Selasa',
            ],
            'rabu' => [
                'header'       => 'linear-gradient(135deg, #9a3412 0%, #d97706 100%)', // Warm Amber / Orange
                'badge_bg'     => 'rgba(255, 255, 255, 0.2)',
                'badge_border' => 'rgba(255, 255, 255, 0.35)',
                'accent'       => '#f59e0b',
                'ring'         => '#fcd34d',
                'label'        => 'Rabu',
            ],
            'kamis' => [
                'header'       => 'linear-gradient(135deg, #581c87 0%, #7c3aed 100%)', // Deep Purple / Violet
                'badge_bg'     => 'rgba(255, 255, 255, 0.2)',
                'badge_border' => 'rgba(255, 255, 255, 0.35)',
                'accent'       => '#8b5cf6',
                'ring'         => '#c4b5fd',
                'label'        => 'Kamis',
            ],
            'jumat' => [
                'header'       => 'linear-gradient(135deg, #115e59 0%, #0d9488 100%)', // Vibrant Teal
                'badge_bg'     => 'rgba(255, 255, 255, 0.2)',
                'badge_border' => 'rgba(255, 255, 255, 0.35)',
                'accent'       => '#14b8a6',
                'ring'         => '#5eead4',
                'label'        => 'Jumat',
            ],
            'sabtu' => [
                'header'       => 'linear-gradient(135deg, #881337 0%, #e11d48 100%)', // Rose / Ruby
                'badge_bg'     => 'rgba(255, 255, 255, 0.2)',
                'badge_border' => 'rgba(255, 255, 255, 0.35)',
                'accent'       => '#f43f5e',
                'ring'         => '#fda4af',
                'label'        => 'Sabtu',
            ],
            default => [
                'header'       => 'linear-gradient(135deg, #1e293b 0%, #475569 100%)', // Slate Gray
                'badge_bg'     => 'rgba(255, 255, 255, 0.2)',
                'badge_border' => 'rgba(255, 255, 255, 0.35)',
                'accent'       => '#64748b',
                'ring'         => '#cbd5e1',
                'label'        => ucfirst($nilai),
            ],
        };
    }
}
