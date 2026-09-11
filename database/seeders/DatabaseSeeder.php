<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seeder inti — aman dijalankan di lingkungan produksi.
     * Data contoh dipisahkan di DemoSeeder: php artisan db:seed --class=DemoSeeder
     */
    public function run(): void
    {
        $this->call([
            PeranSeeder::class,
            PengaturanSeeder::class,
            PenggunaSeeder::class,
            MasterDataSeeder::class,
        ]);
    }
}
