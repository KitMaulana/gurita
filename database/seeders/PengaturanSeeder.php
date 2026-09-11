<?php

namespace Database\Seeders;

use App\Models\Pengaturan;
use Illuminate\Database\Seeder;

class PengaturanSeeder extends Seeder
{
    public function run(): void
    {
        foreach (Pengaturan::BAWAAN as $key => $value) {
            Pengaturan::firstOrCreate(['key' => $key], ['value' => $value]);
        }

        Pengaturan::lupakanCache();
    }
}
