<?php

namespace Tests\Feature;

use App\Imports\JadwalImport;
use App\Imports\SiswaImport;
use App\Models\Jadwal;
use App\Models\Kelas;
use App\Models\MataPelajaran;
use App\Models\Siswa;
use App\Models\TahunAjaran;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Maatwebsite\Excel\Facades\Excel;
use Tests\TestCase;

/** Uji impor CSV sesuai template §9. */
class ImporTest extends TestCase
{
    protected function csv(string $isi): UploadedFile
    {
        $path = tempnam(sys_get_temp_dir(), 'impor').'.csv';
        file_put_contents($path, $isi);

        return new UploadedFile($path, 'impor.csv', 'text/csv', null, true);
    }

    public function test_impor_siswa_menambah_baru_dan_memperbarui_nisn_yang_sudah_ada(): void
    {
        $kelas = Kelas::tahunAktif()->orderBy('nama')->firstOrFail();
        $nisn = '9999000111';

        Siswa::where('nisn', $nisn)->forceDelete();

        $import = new SiswaImport;
        Excel::import($import, $this->csv(
            "nisn,nis,nama,jenis_kelamin,kelas,no_absen\n"
            ."{$nisn},77001,Budi Uji Coba,L,{$kelas->nama},99\n"
        ));

        $this->assertSame(1, $import->jumlahBaru, implode(' | ', $import->galat));

        $siswa = Siswa::where('nisn', $nisn)->firstOrFail();
        $this->assertSame('Budi Uji Coba', $siswa->nama);
        $this->assertTrue($kelas->siswas()->where('siswas.id', $siswa->id)->exists());

        // Impor ulang dengan nama berbeda → diperbarui, bukan digandakan.
        $importUlang = new SiswaImport;
        Excel::import($importUlang, $this->csv(
            "nisn,nis,nama,jenis_kelamin,kelas,no_absen\n"
            ."{$nisn},77001,Budi Uji Coba Revisi,L,{$kelas->nama},99\n"
        ));

        $this->assertSame(1, $importUlang->jumlahDiperbarui);
        $this->assertSame(1, Siswa::where('nisn', $nisn)->count());
        $this->assertSame('Budi Uji Coba Revisi', $siswa->fresh()->nama);

        $kelas->siswas()->detach($siswa->id);
        $siswa->forceDelete();
    }

    public function test_impor_siswa_otomatis_membuat_kelas_baru_jika_belum_ada(): void
    {
        $namaKelasBaru = 'XII IPA 99';
        Kelas::where('nama', $namaKelasBaru)->forceDelete();
        $nisn = '9999000222';
        Siswa::where('nisn', $nisn)->forceDelete();

        $import = new SiswaImport;
        Excel::import($import, $this->csv(
            "nisn,nis,nama,jenis_kelamin,kelas,no_absen\n"
            ."{$nisn},77002,Ani Uji,P,{$namaKelasBaru},1\n"
        ));

        $this->assertSame(1, $import->jumlahBaru);
        $this->assertEmpty($import->galat);
        $this->assertContains($namaKelasBaru, $import->kelasBaru);

        $kelas = Kelas::tahunAktif()->where('nama', $namaKelasBaru)->first();
        $this->assertNotNull($kelas);
        $this->assertSame('XII', $kelas->tingkat);
        $this->assertSame('IPA', $kelas->jurusan);

        // Bersihkan
        $siswa = Siswa::where('nisn', $nisn)->first();
        $kelas->siswas()->detach($siswa?->id);
        $siswa?->forceDelete();
        $kelas->forceDelete();
    }

    public function test_impor_siswa_mengenali_variasi_penulisan_kelas_spasi_dan_strip(): void
    {
        $kelas = Kelas::tahunAktif()->where('nama', 'like', '%-%')->first();
        if (! $kelas) {
            $kelas = Kelas::create([
                'tahun_ajaran_id' => \App\Models\TahunAjaran::aktif()?->id,
                'nama' => 'X-1',
                'tingkat' => 'X',
            ]);
        }

        // Variasi penulisan dengan spasi: "X 1" untuk kelas "X-1"
        $namaKelasVariasi = str_replace('-', ' ', $kelas->nama);
        $nisn = '9999000333';

        Siswa::where('nisn', $nisn)->forceDelete();

        $import = new SiswaImport;
        Excel::import($import, $this->csv(
            "nisn,nis,nama,jenis_kelamin,kelas,no_absen\n"
            ."{$nisn},77003,Siswa Variasi Kelas,L,{$namaKelasVariasi},10\n"
        ));

        $this->assertSame(1, $import->jumlahBaru, implode(' | ', $import->galat));
        $siswa = Siswa::where('nisn', $nisn)->firstOrFail();
        $this->assertTrue($kelas->siswas()->where('siswas.id', $siswa->id)->exists());

        $kelas->siswas()->detach($siswa->id);
        $siswa->forceDelete();
    }

    public function test_impor_jadwal_bentrok_tetap_disimpan_dan_dicatat_sebagai_peringatan(): void
    {
        $admin = User::where('email', 'admin@sman1ciruas.sch.id')->first();
        if ($admin) {
            $this->actingAs($admin);
        }

        $namaGuru = 'Bangkit Uji Coba';
        $namaKelasA = 'XII IPA 88';
        $namaKelasB = 'XII IPS 88';
        $namaMapel = 'Fisika Eksperimen';

        // Bersihkan sebelum uji
        Kelas::whereIn('nama', [$namaKelasA, $namaKelasB])->forceDelete();
        MataPelajaran::where('nama', $namaMapel)->delete();
        User::where('name', $namaGuru)->forceDelete();
        Jadwal::whereHas('guru', fn ($q) => $q->where('name', $namaGuru))->forceDelete();

        $adaSebelum = Jadwal::count();

        // Unggah 2 jadwal pada hari dan jam_ke yang sama untuk guru yang sama (bentrok jadwal / guru mengajar bersamaan di 2 kelas)
        $import = new JadwalImport;
        Excel::import($import, $this->csv(
            "hari,jam_ke,nama_kelas,nama_guru,mata_pelajaran\n"
            ."jumat,1,{$namaKelasA},{$namaGuru},{$namaMapel}\n"
            ."jumat,1,{$namaKelasB},{$namaGuru},{$namaMapel}\n"
        ));

        // Sesuai kebutuhan sekolah: jadwal bentrok tetap diinput dan disimpan
        $this->assertSame(2, $import->jumlahTersimpan);
        $this->assertEmpty($import->galat);
        $this->assertNotEmpty($import->peringatan);
        $this->assertStringContainsString('tetap diinput', $import->peringatan[0]);
        $this->assertSame($adaSebelum + 2, Jadwal::count());

        // Bersihkan setelah uji
        Jadwal::whereHas('guru', fn ($q) => $q->where('name', $namaGuru))->forceDelete();
        Kelas::whereIn('nama', [$namaKelasA, $namaKelasB])->forceDelete();
        MataPelajaran::where('nama', $namaMapel)->delete();
        User::where('name', $namaGuru)->forceDelete();
    }

    public function test_impor_siswa_nisn_kosong_tetap_berhasil_terinput(): void
    {
        $namaKelas = 'X IPA TEST NIHIL';
        Kelas::where('nama', $namaKelas)->forceDelete();
        Siswa::whereIn('nama', ['Siswa Tanpa NISN 1', 'Siswa Tanpa NISN 2'])->forceDelete();

        $import = new SiswaImport;
        Excel::import($import, $this->csv(
            "nisn,nis,nama,jenis_kelamin,kelas,no_absen\n"
            .",11001,Siswa Tanpa NISN 1,L,{$namaKelas},1\n"
            .",11002,Siswa Tanpa NISN 2,P,{$namaKelas},2\n"
        ));

        $this->assertSame(2, $import->jumlahBaru, implode(' | ', $import->galat));
        $this->assertEmpty($import->galat);

        $siswa1 = Siswa::where('nama', 'Siswa Tanpa NISN 1')->first();
        $siswa2 = Siswa::where('nama', 'Siswa Tanpa NISN 2')->first();

        $this->assertNotNull($siswa1);
        $this->assertNotNull($siswa2);
        $this->assertNull($siswa1->nisn);
        $this->assertNull($siswa2->nisn);

        $kelas = Kelas::where('nama', $namaKelas)->first();
        $this->assertNotNull($kelas);
        $this->assertTrue($kelas->siswas()->where('siswas.id', $siswa1->id)->exists());
        $this->assertTrue($kelas->siswas()->where('siswas.id', $siswa2->id)->exists());

        // Bersihkan
        $kelas->siswas()->detach([$siswa1->id, $siswa2->id]);
        $siswa1->forceDelete();
        $siswa2->forceDelete();
        $kelas->forceDelete();
    }

    public function test_reset_siswa_dan_kelas_menghapus_siswa_dan_kelas_serta_mempertahankan_jadwal(): void
    {
        $admin = User::where('email', 'admin@sman1ciruas.sch.id')->first();
        if ($admin) {
            $this->actingAs($admin);
        }

        $ta = TahunAjaran::aktif();

        // Bersihkan data uji jika tersisa dari uji sebelumnya
        $oldKelas = Kelas::where('nama', 'KELAS RESET TEST')->first();
        if ($oldKelas) {
            Jadwal::where('kelas_id', $oldKelas->id)->forceDelete();
            $oldKelas->siswas()->detach();
            $oldKelas->forceDelete();
        }
        Siswa::where('nama', 'Siswa Uji Reset')->forceDelete();

        // Buat kelas uji, siswa uji, dan jadwal uji
        $kelas = Kelas::create([
            'tahun_ajaran_id' => $ta?->id,
            'nama' => 'KELAS RESET TEST',
            'tingkat' => 'X',
        ]);

        $siswa = Siswa::create([
            'nama' => 'Siswa Uji Reset',
            'jenis_kelamin' => 'L',
        ]);
        $kelas->siswas()->attach($siswa->id, ['no_absen' => 1]);

        $guru = User::factory()->create();
        $mapel = MataPelajaran::first() ?? MataPelajaran::create(['kode' => 'TST', 'nama' => 'Test Mapel']);
        $jadwal = Jadwal::create([
            'tahun_ajaran_id' => $ta?->id,
            'kelas_id' => $kelas->id,
            'mata_pelajaran_id' => $mapel->id,
            'guru_id' => $guru->id,
            'hari' => 'senin',
            'jam_ke' => 1,
            'jam_mulai' => '07:00:00',
            'jam_selesai' => '07:45:00',
        ]);

        $response = $this->post(route('admin.siswa.reset'));
        $response->assertRedirect(route('admin.siswa.index'));
        $response->assertSessionHas('sukses');

        // Siswa dan kelas harus terhapus
        $this->assertNull(Siswa::find($siswa->id));
        $this->assertNull(Kelas::find($kelas->id));

        // Jadwal tetap ada, tetapi kelas_id menjadi null
        $jadwalSegar = Jadwal::find($jadwal->id);
        $this->assertNotNull($jadwalSegar);
        $this->assertNull($jadwalSegar->kelas_id);

        // Bersihkan jadwal & guru uji
        $jadwalSegar->forceDelete();
        $guru->forceDelete();

        // Pulihkan seeder agar tidak mengganggu test suite lain
        (new \Database\Seeders\DatabaseSeeder())->run();
        (new \Database\Seeders\DemoSeeder())->run();
    }
}
