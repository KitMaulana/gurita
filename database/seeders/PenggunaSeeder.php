<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class PenggunaSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::updateOrCreate(
            ['email' => 'admin@sman1ciruas.sch.id'],
            [
                'name' => 'Administrator Portal',
                'nip' => '000000000000000001',
                'password' => Hash::make('password'),
                'jabatan' => 'Waka Kurikulum',
                'quote' => 'Administrasi yang rapi adalah separuh dari pembelajaran yang baik.',
                'is_aktif' => true,
                'email_verified_at' => now(),
            ]
        );
        $admin->syncRoles(['admin']);

        $guru = User::updateOrCreate(
            ['email' => 'guru@sman1ciruas.sch.id'],
            [
                'name' => 'Siti Rahmawati',
                'nip' => '198001012005011001',
                'password' => Hash::make('password'),
                'jabatan' => 'Guru Bahasa Indonesia & Kepala Perpustakaan',
                'quote' => 'Membaca membuka jendela dunia, menulis membukakan pintunya.',
                'is_aktif' => true,
                'email_verified_at' => now(),
            ]
        );
        $guru->syncRoles(['guru', 'wali_kelas']);
    }
}
