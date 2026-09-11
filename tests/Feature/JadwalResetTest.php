<?php

namespace Tests\Feature;

use App\Imports\JadwalImport;
use App\Models\Jadwal;
use App\Models\Kelas;
use App\Models\MataPelajaran;
use App\Models\TahunAjaran;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Maatwebsite\Excel\Facades\Excel;
use Tests\TestCase;

class JadwalResetTest extends TestCase
{
    protected function admin(): User
    {
        return User::where('email', 'admin@sman1ciruas.sch.id')->firstOrFail();
    }

    protected function csv(string $isi): UploadedFile
    {
        $path = tempnam(sys_get_temp_dir(), 'reset_impor').'.csv';
        file_put_contents($path, $isi);

        return new UploadedFile($path, 'reset_impor.csv', 'text/csv', null, true);
    }

    public function test_guru_tidak_dapat_mengakses_fitur_reset(): void
    {
        $guru = User::where('email', 'guru@sman1ciruas.sch.id')->first();
        if (! $guru) {
            $guru = User::create([
                'name' => 'Guru Uji',
                'email' => 'guru@sman1ciruas.sch.id',
                'password' => bcrypt('password'),
                'is_aktif' => true,
            ]);
            $guru->assignRole('guru');
        }

        $response = $this->actingAs($guru)->post(route('admin.jadwal.reset'));
        $response->assertForbidden();
    }

    public function test_tamu_diarahkan_ke_halaman_login_saat_mencoba_reset(): void
    {
        auth()->logout();
        $response = $this->post(route('admin.jadwal.reset'));
        $response->assertRedirect(route('login'));
    }

    public function test_admin_dapat_mereset_seluruh_jadwal_dan_data_terintegrasi(): void
    {
        $admin = $this->admin();
        $tahunAjaran = TahunAjaran::aktif();

        // 1. Eksekusi Reset
        $response = $this->actingAs($admin)->post(route('admin.jadwal.reset'));
        $response->assertRedirect(route('admin.jadwal.index'));
        $response->assertSessionHas('sukses');

        // 2. Verifikasi seluruh jadwal, kelas, dan mapel terhapus
        $this->assertSame(0, Jadwal::where('tahun_ajaran_id', $tahunAjaran->id)->count());
        $this->assertSame(0, Kelas::where('tahun_ajaran_id', $tahunAjaran->id)->count());
        $this->assertSame(0, MataPelajaran::count());

        // 3. Verifikasi akun admin tetap utuh dan aman
        $this->assertDatabaseHas('users', [
            'email' => 'admin@sman1ciruas.sch.id',
        ]);

        // 4. Verifikasi setelah reset, admin dapat langsung mengunggah jadwal baru dari nol
        $import = new JadwalImport;
        Excel::import($import, $this->csv(
            "hari,jam_ke,nama_kelas,nama_guru,mata_pelajaran\n"
            ."senin,1,X MIPA 1,Budi Santoso,Fisika Dasar\n"
            ."senin,2,X MIPA 1,Budi Santoso,Fisika Dasar\n"
            ."selasa,3,X MIPA 2,Dewi Sartika,Kimia Analitik\n"
        ));

        $this->assertSame(3, $import->jumlahTersimpan, implode(' | ', $import->galat));
        // Kelas tidak dibuat dari jadwal (hanya mengikuti data siswa)
        $this->assertSame(0, Kelas::where('tahun_ajaran_id', $tahunAjaran->id)->count());
        $this->assertSame(2, MataPelajaran::count());
        $this->assertDatabaseHas('users', ['name' => 'Budi Santoso']);
        $this->assertDatabaseHas('users', ['name' => 'Dewi Sartika']);

        // 5. Bersihkan data impor uji & pulihkan seeder agar tidak mengganggu test suite lain
        Kelas::where('nama', 'like', 'X MIPA%')->forceDelete();
        MataPelajaran::whereIn('nama', ['Fisika Dasar', 'Kimia Analitik'])->delete();
        User::whereIn('name', ['Budi Santoso', 'Dewi Sartika'])->forceDelete();
        Jadwal::query()->forceDelete();

        (new \Database\Seeders\DatabaseSeeder())->run();
        (new \Database\Seeders\DemoSeeder())->run();
    }
}
