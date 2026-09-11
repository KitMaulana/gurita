<?php

namespace Tests\Feature;

use App\Enums\StatusPresensi;
use App\Livewire\TabelNilai;
use App\Models\Agenda;
use App\Models\Jadwal;
use App\Models\Kelas;
use App\Models\MataPelajaran;
use App\Models\Nilai;
use App\Models\Penilaian;
use App\Models\RaporMapel;
use App\Models\User;
use Livewire\Livewire;
use Tests\TestCase;

/** Menguji alur tulis inti: agenda → presensi → nilai → rapor. */
class AlurKerjaTest extends TestCase
{
    protected function guru(): User
    {
        return User::where('email', 'guru@sman1ciruas.sch.id')->firstOrFail();
    }

    public function test_agenda_dibuat_massal_dari_jadwal_lalu_diarahkan_ke_presensi(): void
    {
        $guru = $this->guru();
        $jadwal = Jadwal::where('guru_id', $guru->id)->firstOrFail();

        // Tanggal jauh di masa lalu agar tidak menabrak agenda hasil DemoSeeder.
        $tanggal = now()->startOfWeek()->subWeeks(20)
            ->addDays($jadwal->hari->nomor() - 1)->toDateString();

        $respons = $this->actingAs($guru)->post(route('agenda.simpan-massal'), [
            'tanggal' => $tanggal,
            'pilih' => [$jadwal->id],
            'agenda' => [
                $jadwal->id => [
                    'judul_materi' => 'Uji Coba Agenda Otomatis',
                    'status' => 'terlaksana',
                    'uraian_kegiatan' => 'Kegiatan uji.',
                ],
            ],
        ]);

        $agenda = Agenda::where('jadwal_id', $jadwal->id)->whereDate('tanggal', $tanggal)->first();

        $this->assertNotNull($agenda, 'Agenda seharusnya tersimpan.');
        $respons->assertRedirect(route('presensi.isi', $agenda));

        // ── Presensi: semua hadir kecuali satu siswa sakit ────────────
        $siswas = $jadwal->kelas->siswas()->get();
        $status = $siswas->mapWithKeys(fn ($s) => [$s->id => 'hadir'])->all();
        $keterangan = [];

        $pertama = $siswas->first();
        $status[$pertama->id] = 'sakit';
        $keterangan[$pertama->id] = 'Surat dokter';

        $this->actingAs($guru)
            ->post(route('presensi.simpan', $agenda), compact('status', 'keterangan'))
            ->assertRedirect(route('presensi.index'));

        $this->assertSame($siswas->count(), $agenda->presensis()->count());
        $this->assertSame(
            StatusPresensi::Sakit,
            $agenda->presensis()->where('siswa_id', $pertama->id)->value('status')
        );

        $agenda->presensis()->delete();
        $agenda->delete();
    }

    public function test_presensi_menolak_status_tidak_hadir_tanpa_keterangan(): void
    {
        $guru = $this->guru();
        $agenda = Agenda::milikGuru($guru->id)->where('is_terkunci', false)->firstOrFail();
        $siswa = $agenda->jadwal->kelas->siswas()->firstOrFail();

        $this->actingAs($guru)
            ->post(route('presensi.simpan', $agenda), [
                'status' => [$siswa->id => 'alfa'],
                'keterangan' => [$siswa->id => ''],
            ])
            ->assertSessionHasErrors('keterangan');
    }

    public function test_jadwal_bentrok_ditolak_dengan_pesan_jelas(): void
    {
        $admin = User::where('email', 'admin@sman1ciruas.sch.id')->firstOrFail();
        $jadwal = Jadwal::with('kelas')->firstOrFail();

        $this->actingAs($admin)
            ->post(route('admin.jadwal.store'), [
                'kelas_id' => $jadwal->kelas_id,
                'mata_pelajaran_id' => $jadwal->mata_pelajaran_id,
                'guru_id' => $jadwal->guru_id,
                'hari' => $jadwal->hari->value,
                'jam_ke' => $jadwal->jam_ke,
                'jam_mulai' => '07:00',
                'jam_selesai' => '07:45',
            ])
            ->assertSessionHasErrors('jam_ke');
    }

    public function test_grid_nilai_menyimpan_sel_dan_menolak_nilai_di_luar_rentang(): void
    {
        $guru = $this->guru();
        $penilaian = Penilaian::where('guru_id', $guru->id)->where('is_terkunci', false)->firstOrFail();
        $siswa = $penilaian->kelas->siswas()->firstOrFail();

        $komponen = Livewire::actingAs($guru)->test(TabelNilai::class, [
            'kelasId' => $penilaian->kelas_id,
            'mataPelajaranId' => $penilaian->mata_pelajaran_id,
        ]);

        $komponen->set("nilai.{$siswa->id}.{$penilaian->id}", '88')
            ->call('simpanSel', $siswa->id, $penilaian->id)
            ->assertHasNoErrors();

        $this->assertEquals(
            88.0,
            (float) Nilai::where('penilaian_id', $penilaian->id)->where('siswa_id', $siswa->id)->value('nilai')
        );

        $komponen->set("nilai.{$siswa->id}.{$penilaian->id}", '150')
            ->call('simpanSel', $siswa->id, $penilaian->id)
            ->assertHasErrors("nilai.{$siswa->id}.{$penilaian->id}");
    }

    public function test_rapor_dibekukan_dan_mengunci_penilaian(): void
    {
        $guru = $this->guru();
        $kelas = Kelas::tahunAktif()->orderByDesc('id')->firstOrFail();   // kelas terakhir, agar tidak mengganggu uji lain
        $mapel = MataPelajaran::where('nama', 'Bahasa Indonesia')->firstOrFail();
        $filter = ['kelas_id' => $kelas->id, 'mata_pelajaran_id' => $mapel->id];

        $this->actingAs($guru)
            ->post(route('rapor.bekukan', $filter))
            ->assertRedirect();

        $this->assertGreaterThan(0, RaporMapel::where('kelas_id', $kelas->id)
            ->where('mata_pelajaran_id', $mapel->id)->count());

        $this->assertSame(0, Penilaian::where('kelas_id', $kelas->id)
            ->where('mata_pelajaran_id', $mapel->id)
            ->where('is_terkunci', false)
            ->count());

        // Setelah dibekukan, guru tidak boleh lagi mengubah penilaiannya.
        $terkunci = Penilaian::where('kelas_id', $kelas->id)
            ->where('mata_pelajaran_id', $mapel->id)->firstOrFail();

        $this->assertFalse($guru->can('update', $terkunci));
    }

    public function test_folder_materi_dan_unggah_tautan(): void
    {
        $guru = $this->guru();
        $mapel = MataPelajaran::where('nama', 'Bahasa Indonesia')->firstOrFail();

        $this->actingAs($guru)->post(route('materi.folder.store'), [
            'nama' => 'Folder Uji Coba',
            'mata_pelajaran_id' => $mapel->id,
            'warna' => 'from-accent to-accent-600',
        ])->assertRedirect();

        $folder = \App\Models\FolderMateri::where('nama', 'Folder Uji Coba')->firstOrFail();

        $this->actingAs($guru)->post(route('materi.store', $folder), [
            'judul' => 'Video Pembelajaran Teks Editorial',
            'tipe' => 'video',
            'url' => 'https://www.youtube.com/watch?v=contoh',
        ])->assertRedirect();

        $this->assertSame(1, $folder->materis()->count());

        $folder->materis()->delete();
        $folder->delete();
    }
}
