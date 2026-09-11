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

class JadwalBulkTest extends TestCase
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

    public function test_halaman_input_massal_dapat_diakses_admin(): void
    {
        $response = $this->get(route('admin.jadwal.bulk'));
        $response->assertOk();
        $response->assertSee('Input Jadwal Massal');
    }

    public function test_simpan_jadwal_massal_multi_jp_berhasil(): void
    {
        $kelas = Kelas::tahunAktif()->where('nama', 'XII IPA 8')->firstOrFail();
        $mapel = MataPelajaran::where('nama', 'Matematika')->firstOrFail();
        $guru = User::where('email', 'guru@sman1ciruas.sch.id')->firstOrFail();

        // Pastikan bersih di hari sabtu
        Jadwal::where('kelas_id', $kelas->id)->where('hari', 'sabtu')->forceDelete();

        $response = $this->post(route('admin.jadwal.bulk.store'), [
            'hari' => 'sabtu',
            'kelas_id' => $kelas->id,
            'mata_pelajaran_id' => $mapel->id,
            'guru_id' => $guru->id,
            'jam_ke' => [1, 2, 3],
            'ruang' => 'R.8',
        ]);

        $response->assertRedirect(route('admin.jadwal.index'));
        $response->assertSessionHas('sukses');

        $this->assertDatabaseHas('jadwals', [
            'kelas_id' => $kelas->id,
            'hari' => 'sabtu',
            'jam_ke' => 1,
            'jam_mulai' => '07:00:00',
            'jam_selesai' => '07:35:00',
        ]);

        $this->assertDatabaseHas('jadwals', [
            'kelas_id' => $kelas->id,
            'hari' => 'sabtu',
            'jam_ke' => 2,
            'jam_mulai' => '07:35:00',
            'jam_selesai' => '08:10:00',
        ]);

        $this->assertDatabaseHas('jadwals', [
            'kelas_id' => $kelas->id,
            'hari' => 'sabtu',
            'jam_ke' => 3,
            'jam_mulai' => '08:10:00',
            'jam_selesai' => '08:45:00',
        ]);
    }

    public function test_simpan_jadwal_agenda_bersama_semua_kelas(): void
    {
        Jadwal::where('hari', 'sabtu')->where('jam_ke', 4)->forceDelete();

        $response = $this->post(route('admin.jadwal.bulk.store'), [
            'hari' => 'sabtu',
            'title' => 'Makan Bergizi Gratis Bersama (MBG)',
            'jam_ke' => [4],
        ]);

        $response->assertRedirect(route('admin.jadwal.index'));
        $response->assertSessionHas('sukses');

        $this->assertDatabaseHas('jadwals', [
            'hari' => 'sabtu',
            'jam_ke' => 4,
            'title' => 'Makan Bergizi Gratis Bersama (MBG)',
            'kelas_id' => null,
            'mata_pelajaran_id' => null,
            'guru_id' => null,
        ]);
    }

    public function test_hapus_jadwal_secara_massal_bulk_delete(): void
    {
        $jadwal1 = Jadwal::create([
            'tahun_ajaran_id' => TahunAjaran::aktif()->id,
            'title' => 'Kegiatan Bersama A',
            'hari' => 'sabtu',
            'jam_ke' => 6,
            'jam_mulai' => '10:35',
            'jam_selesai' => '11:10',
        ]);

        $jadwal2 = Jadwal::create([
            'tahun_ajaran_id' => TahunAjaran::aktif()->id,
            'title' => 'Kegiatan Bersama B',
            'hari' => 'sabtu',
            'jam_ke' => 7,
            'jam_mulai' => '11:10',
            'jam_selesai' => '11:45',
        ]);

        $response = $this->post(route('admin.jadwal.bulk-delete'), [
            'ids' => [$jadwal1->id, $jadwal2->id],
        ]);

        $response->assertRedirect(route('admin.jadwal.index'));
        $response->assertSessionHas('sukses');

        $this->assertSoftDeleted('jadwals', ['id' => $jadwal1->id]);
        $this->assertSoftDeleted('jadwals', ['id' => $jadwal2->id]);
    }

    public function test_impor_csv_format_sidacheers_tanpa_kolom_jam_mulai_selesai(): void
    {
        $kelas = Kelas::tahunAktif()->where('nama', 'XII IPA 7')->firstOrFail();
        $guru = User::where('email', 'guru@sman1ciruas.sch.id')->firstOrFail();

        // Bersihkan jadwal sabtu JP 4 & 5
        Jadwal::where('hari', 'sabtu')->whereIn('jam_ke', [4, 5])->forceDelete();

        $import = new JadwalImport;
        Excel::import($import, $this->csv(
            "hari,jam_ke,nama_kelas,nama_guru,mata_pelajaran\n"
            ."sabtu,4,{$kelas->nama},{$guru->name},Bahasa Indonesia\n"
            ."sabtu,5,{$kelas->nama},{$guru->name},Bahasa Indonesia\n"
        ));

        $this->assertSame(2, $import->jumlahTersimpan, implode(' | ', $import->galat));

        $this->assertDatabaseHas('jadwals', [
            'kelas_id' => $kelas->id,
            'hari' => 'sabtu',
            'jam_ke' => 4,
            'jam_mulai' => '08:45:00',
            'jam_selesai' => '09:20:00',
        ]);

        $this->assertDatabaseHas('jadwals', [
            'kelas_id' => $kelas->id,
            'hari' => 'sabtu',
            'jam_ke' => 5,
            'jam_mulai' => '09:20:00',
            'jam_selesai' => '09:55:00',
        ]);
    }
}
