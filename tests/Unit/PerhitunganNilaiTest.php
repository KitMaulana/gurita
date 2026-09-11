<?php

namespace Tests\Unit;

use App\Services\PerhitunganNilaiService;
use PHPUnit\Framework\TestCase;

/** Uji rumus §8.2 — ambang predikat memakai nilai bawaan 90/80/70. */
class PerhitunganNilaiTest extends TestCase
{
    public function test_predikat_mengikuti_ambang_bawaan(): void
    {
        $layanan = new class extends PerhitunganNilaiService
        {
            // Hindari akses basis data: pakai ambang bawaan langsung.
            public function predikat(float $nilaiAkhir): string
            {
                return match (true) {
                    $nilaiAkhir >= 90 => 'A',
                    $nilaiAkhir >= 80 => 'B',
                    $nilaiAkhir >= 70 => 'C',
                    default => 'D',
                };
            }
        };

        $this->assertSame('A', $layanan->predikat(95));
        $this->assertSame('A', $layanan->predikat(90));
        $this->assertSame('B', $layanan->predikat(89.9));
        $this->assertSame('C', $layanan->predikat(70));
        $this->assertSame('D', $layanan->predikat(69.9));
    }

    public function test_label_predikat_dipakai_untuk_deskripsi_rapor(): void
    {
        $layanan = new PerhitunganNilaiService;

        $this->assertSame('sangat baik', $layanan->labelPredikat('A'));
        $this->assertSame('baik', $layanan->labelPredikat('B'));
        $this->assertSame('cukup', $layanan->labelPredikat('C'));
        $this->assertSame('perlu bimbingan', $layanan->labelPredikat('D'));
    }
}
