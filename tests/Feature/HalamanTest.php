<?php

namespace Tests\Feature;

use App\Models\Agenda;
use App\Models\Bab;
use App\Models\FolderMateri;
use App\Models\Kelas;
use App\Models\MataPelajaran;
use App\Models\Penilaian;
use App\Models\Siswa;
use App\Models\User;
use Tests\TestCase;

/**
 * Uji asap: memastikan setiap halaman utama dapat dibuka tanpa galat.
 * Memakai basis data yang sudah di-seed (tidak memakai RefreshDatabase agar
 * ikut menguji data nyata hasil MasterDataSeeder + DemoSeeder).
 */
class HalamanTest extends TestCase
{
    protected function guru(): User
    {
        return User::where('email', 'guru@sman1ciruas.sch.id')->firstOrFail();
    }

    protected function admin(): User
    {
        return User::where('email', 'admin@sman1ciruas.sch.id')->firstOrFail();
    }

    public function test_halaman_guru_dapat_dibuka(): void
    {
        $guru = $this->guru();
        $kelas = Kelas::tahunAktif()->first();
        $mapel = MataPelajaran::where('nama', 'Bahasa Indonesia')->first();

        $halaman = [
            route('beranda'),
            route('jadwal.index'),
            route('agenda.index'),
            route('agenda.dari-jadwal'),
            route('presensi.index'),
            route('presensi.rekap', ['kelas_id' => $kelas?->id, 'mata_pelajaran_id' => $mapel?->id]),
            route('nilai.index', ['kelas_id' => $kelas?->id, 'mata_pelajaran_id' => $mapel?->id]),
            route('penilaian.index'),
            route('penilaian.create'),
            route('bab.index'),
            route('bab.create'),
            route('materi.index'),
            route('rapor.index', ['kelas_id' => $kelas?->id, 'mata_pelajaran_id' => $mapel?->id]),
            route('profile.edit'),
        ];

        foreach ($halaman as $url) {
            $this->actingAs($guru)->get($url)->assertOk();
        }
    }

    public function test_halaman_admin_dapat_dibuka(): void
    {
        $admin = $this->admin();
        $kelas = Kelas::tahunAktif()->first();

        $halaman = [
            route('admin.tahun-ajaran.index'),
            route('admin.tahun-ajaran.create'),
            route('admin.mata-pelajaran.index'),
            route('admin.mata-pelajaran.create'),
            route('admin.kelas.index'),
            route('admin.kelas.create'),
            route('admin.kelas.show', $kelas),
            route('admin.siswa.index'),
            route('admin.siswa.create'),
            route('admin.jadwal.index'),
            route('admin.jadwal.create'),
            route('admin.pengguna.index'),
            route('admin.pengguna.create'),
            route('admin.kktp.index'),
            route('admin.pengaturan.edit'),
            route('admin.log.index'),
        ];

        foreach ($halaman as $url) {
            $this->actingAs($admin)->get($url)->assertOk();
        }
    }

    public function test_halaman_detail_dapat_dibuka(): void
    {
        $guru = $this->guru();

        if ($agenda = Agenda::milikGuru($guru->id)->first()) {
            $this->actingAs($guru)->get(route('agenda.edit', $agenda))->assertOk();
            $this->actingAs($guru)->get(route('presensi.isi', $agenda))->assertOk();
        }

        if ($penilaian = Penilaian::where('guru_id', $guru->id)->first()) {
            $this->actingAs($guru)->get(route('penilaian.edit', $penilaian))->assertOk();
        }

        if ($bab = Bab::first()) {
            $this->actingAs($guru)->get(route('bab.show', $bab))->assertOk();
            $this->actingAs($guru)->get(route('bab.edit', $bab))->assertOk();
        }

        if ($folder = FolderMateri::where('guru_id', $guru->id)->first()) {
            $this->actingAs($guru)->get(route('materi.folder.show', $folder))->assertOk();
        }
    }

    public function test_dokumen_cetak_dan_ekspor_dihasilkan(): void
    {
        $guru = $this->guru();
        $kelas = Kelas::tahunAktif()->first();
        $mapel = MataPelajaran::where('nama', 'Bahasa Indonesia')->first();
        $siswa = $kelas?->siswas()->first();
        $filter = ['kelas_id' => $kelas?->id, 'mata_pelajaran_id' => $mapel?->id];

        $this->actingAs($guru)->get(route('jadwal.cetak'))->assertOk();
        $this->actingAs($guru)->get(route('agenda.cetak'))->assertOk();
        $this->actingAs($guru)->get(route('agenda.ekspor'))->assertOk();
        $this->actingAs($guru)->get(route('presensi.cetak', $filter))->assertOk();
        $this->actingAs($guru)->get(route('presensi.ekspor', $filter))->assertOk();
        $this->actingAs($guru)->get(route('nilai.cetak', $filter))->assertOk();
        $this->actingAs($guru)->get(route('nilai.ekspor', $filter))->assertOk();
        $this->actingAs($guru)->get(route('rapor.cetak', $filter))->assertOk();
        $this->actingAs($guru)->get(route('rapor.ekspor', $filter))->assertOk();

        if ($siswa) {
            $this->actingAs($guru)->get(route('rapor.kartu', array_merge($filter, ['siswa' => $siswa->id])))->assertOk();
        }
    }

    public function test_template_impor_dapat_diunduh(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)->get(route('admin.siswa.template'))->assertOk();
        $this->actingAs($admin)->get(route('admin.jadwal.template'))->assertOk();
        $this->actingAs($admin)->get(route('admin.siswa.ekspor'))->assertOk();
    }

    public function test_guru_tidak_dapat_membuka_menu_administrasi(): void
    {
        $this->actingAs($this->guru())
            ->get(route('admin.pengguna.index'))
            ->assertForbidden();
    }

    public function test_guru_tidak_dapat_mengubah_agenda_guru_lain(): void
    {
        $agenda = Agenda::with('jadwal')->first();

        if (! $agenda) {
            $this->markTestSkipped('Tidak ada agenda pada basis data.');
        }

        $penyusup = User::factory()->create(['is_aktif' => true]);
        $penyusup->syncRoles(['guru']);

        $this->actingAs($penyusup)
            ->get(route('agenda.edit', $agenda))
            ->assertForbidden();

        $penyusup->forceDelete();
    }

    public function test_tamu_diarahkan_ke_halaman_masuk(): void
    {
        $this->get(route('beranda'))->assertRedirect(route('login'));
    }

    public function test_masuk_dengan_nip_berhasil(): void
    {
        $this->post(route('login'), [
            'login' => '198001012005011001',
            'password' => 'password',
        ])->assertRedirect(route('beranda'));

        $this->assertAuthenticated();
    }

    public function test_siswa_terdaftar_lengkap(): void
    {
        $this->assertGreaterThan(0, Siswa::count());
    }
}
