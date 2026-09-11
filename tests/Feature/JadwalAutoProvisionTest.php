<?php

namespace Tests\Feature;

use App\Imports\JadwalImport;
use App\Models\Jadwal;
use App\Models\Kelas;
use App\Models\Kktp;
use App\Models\MataPelajaran;
use App\Models\TahunAjaran;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Facades\Excel;
use Tests\TestCase;

class JadwalAutoProvisionTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $admin = User::where('email', 'admin@sman1ciruas.sch.id')->first();
        $this->actingAs($admin);
    }

    protected function csv(string $isi): UploadedFile
    {
        $path = tempnam(sys_get_temp_dir(), 'impor').'.csv';
        file_put_contents($path, $isi);

        return new UploadedFile($path, 'impor.csv', 'text/csv', null, true);
    }

    public function test_impor_jadwal_otomatis_membuat_kelas_mapel_dan_akun_guru_baru(): void
    {
        $namaKelasBaru = 'X MIPA 9';
        $namaMapelBaru = 'Sosiologi Pendidikan';
        $namaGuruBaru = 'Ahmad Fauzi Pratama';

        // Pastikan bersih sebelum uji
        Kelas::where('nama', $namaKelasBaru)->forceDelete();
        MataPelajaran::where('nama', $namaMapelBaru)->delete();
        User::where('name', $namaGuruBaru)->forceDelete();

        $import = new JadwalImport;
        Excel::import($import, $this->csv(
            "hari,jam_ke,nama_kelas,nama_guru,mata_pelajaran\n"
            ."selasa,1,{$namaKelasBaru},{$namaGuruBaru},{$namaMapelBaru}\n"
            ."selasa,2,{$namaKelasBaru},{$namaGuruBaru},{$namaMapelBaru}\n"
        ));

        // 1. Verifikasi jadwal tersimpan
        $this->assertSame(2, $import->jumlahTersimpan, implode(' | ', $import->galat));

        // 2. Verifikasi kelas TIDAK dibuat dari data jadwal (kelas hanya mengikuti data siswa)
        $kelas = Kelas::tahunAktif()->where('nama', $namaKelasBaru)->first();
        $this->assertNull($kelas);

        // 3. Verifikasi mata pelajaran baru terbuat otomatis beserta KKTP
        $mapel = MataPelajaran::where('nama', $namaMapelBaru)->first();
        $this->assertNotNull($mapel);
        $this->assertNotEmpty($mapel->singkatan);

        $kktp = Kktp::where('mata_pelajaran_id', $mapel->id)->where('tingkat', 'X')->first();
        $this->assertNotNull($kktp);
        $this->assertSame(75, $kktp->nilai);

        // 4. Verifikasi akun guru baru terbuat otomatis dengan password 12345
        $guru = User::where('name', $namaGuruBaru)->first();
        $this->assertNotNull($guru);
        $this->assertTrue($guru->hasRole('guru'));
        $this->assertTrue($guru->is_aktif);
        $this->assertTrue(Hash::check('12345', $guru->password));
        $this->assertStringEndsWith('@sman1ciruas.sch.id', $guru->email);

        // 5. Verifikasi statistik provisioner di import object
        $this->assertArrayHasKey($guru->id, $import->guruBaru);
        $this->assertSame('12345', $import->guruBaru[$guru->id]['password']);

        // 6. Verifikasi guru baru dapat login langsung dengan email dan password 12345
        auth()->logout();
        $loginResponse = $this->post(route('login'), [
            'login' => $guru->email,
            'password' => '12345',
        ]);
        $loginResponse->assertRedirect(route('beranda'));
        $this->assertAuthenticatedAs($guru);

        // Bersihkan
        Jadwal::whereNull('kelas_id')->where('mata_pelajaran_id', $mapel->id)->forceDelete();
        $mapel->delete();
        $guru->forceDelete();
    }
}
