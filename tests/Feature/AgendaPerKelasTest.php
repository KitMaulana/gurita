<?php

namespace Tests\Feature;

use App\Models\Agenda;
use App\Models\Jadwal;
use App\Models\User;
use Tests\TestCase;

class AgendaPerKelasTest extends TestCase
{
    protected function guru(): User
    {
        return User::where('email', 'guru@sman1ciruas.sch.id')->firstOrFail();
    }

    public function test_halaman_agenda_dari_jadwal_dapat_dibuka_dan_menampilkan_sesi(): void
    {
        $guru = $this->guru();
        $jadwal = Jadwal::where('guru_id', $guru->id)->whereNotNull('kelas_id')->firstOrFail();

        $tanggal = now()->startOfWeek()->subWeeks(15)
            ->addDays($jadwal->hari->nomor() - 1)->toDateString();

        $respons = $this->actingAs($guru)->get(route('agenda.dari-jadwal', [
            'tanggal' => $tanggal,
            'jadwal_id' => $jadwal->id,
        ]));

        $respons->assertOk();
        $respons->assertSee('Sesi Mengajar Hari');
        $respons->assertSee($jadwal->kelas_tampilan);
    }

    public function test_simpan_agenda_per_kelas_dan_lanjut_ke_presensi(): void
    {
        $guru = $this->guru();
        $jadwal = Jadwal::where('guru_id', $guru->id)->whereNotNull('kelas_id')->firstOrFail();

        $tanggal = now()->startOfWeek()->subWeeks(18)
            ->addDays($jadwal->hari->nomor() - 1)->toDateString();

        // Bersihkan agenda uji jika ada
        Agenda::where('jadwal_id', $jadwal->id)->whereDate('tanggal', $tanggal)->delete();

        $respons = $this->actingAs($guru)->post(route('agenda.simpan-massal'), [
            'tanggal' => $tanggal,
            'jadwal_id' => $jadwal->id,
            'judul_materi' => 'Materi Uji Coba Per Kelas',
            'status' => 'terlaksana',
            'metode' => 'Diskusi kelompok',
            'uraian_kegiatan' => 'Kegiatan pembelajaran per kelas berjalan lancar.',
            'lanjut_presensi' => 1,
        ]);

        $agenda = Agenda::where('jadwal_id', $jadwal->id)->whereDate('tanggal', $tanggal)->first();

        $this->assertNotNull($agenda, 'Agenda per kelas harus berhasil disimpan.');
        $this->assertEquals('Materi Uji Coba Per Kelas', $agenda->judul_materi);
        $this->assertEquals('Diskusi kelompok', $agenda->metode);
        $respons->assertRedirect(route('presensi.isi', $agenda));
    }

    public function test_simpan_agenda_per_kelas_tanpa_lanjut_presensi(): void
    {
        $guru = $this->guru();
        $jadwal = Jadwal::where('guru_id', $guru->id)->whereNotNull('kelas_id')->firstOrFail();

        $tanggal = now()->startOfWeek()->subWeeks(17)
            ->addDays($jadwal->hari->nomor() - 1)->toDateString();

        // Bersihkan agenda uji jika ada
        Agenda::where('jadwal_id', $jadwal->id)->whereDate('tanggal', $tanggal)->delete();

        $respons = $this->actingAs($guru)->post(route('agenda.simpan-massal'), [
            'tanggal' => $tanggal,
            'jadwal_id' => $jadwal->id,
            'judul_materi' => 'Materi Uji Coba Simpan Saja',
            'status' => 'terlaksana',
            'metode' => 'Ceramah interaktif',
            'uraian_kegiatan' => 'Catatan tersimpan.',
            'lanjut_presensi' => 0,
        ]);

        $agenda = Agenda::where('jadwal_id', $jadwal->id)->whereDate('tanggal', $tanggal)->first();

        $this->assertNotNull($agenda);
        $respons->assertRedirect(route('agenda.dari-jadwal', [
            'tanggal' => $tanggal,
            'jadwal_id' => $agenda->jadwal_id,
        ]));
    }
}
