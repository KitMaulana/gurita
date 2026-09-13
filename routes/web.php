<?php

use App\Http\Controllers\Admin\JadwalAdminController;
use App\Http\Controllers\Admin\KelasController;
use App\Http\Controllers\Admin\KktpController;
use App\Http\Controllers\Admin\LogAktivitasController;
use App\Http\Controllers\Admin\MataPelajaranController;
use App\Http\Controllers\Admin\PengaturanController;
use App\Http\Controllers\Admin\PenggunaController;
use App\Http\Controllers\Admin\SiswaController;
use App\Http\Controllers\Admin\TahunAjaranController;
use App\Http\Controllers\BerandaController;
use App\Http\Controllers\Guru\AgendaController;
use App\Http\Controllers\Guru\BabController;
use App\Http\Controllers\Guru\JadwalController;
use App\Http\Controllers\Guru\MateriController;
use App\Http\Controllers\Guru\NilaiController;
use App\Http\Controllers\Guru\PenilaianController;
use App\Http\Controllers\Guru\PresensiController;
use App\Http\Controllers\Guru\RaporController;
use App\Http\Controllers\Guru\TujuanPembelajaranController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;

Route::get('/', fn () => redirect()->route('beranda'));

// Fallback penyajian file publik jika symlink public/storage belum dibuat di hosting (misal cPanel/Hostinger)
Route::get('/storage/{path}', function (string $path) {
    if (str_contains($path, '..')) {
        abort(404);
    }

    $disk = Storage::disk('public');
    if (! $disk->exists($path)) {
        abort(404);
    }

    return response()->file($disk->path($path), [
        'Cache-Control' => 'public, max-age=86400',
    ]);
})->where('path', '.*')->name('storage.fallback');

Route::middleware(['auth'])->group(function () {
    Route::get('/profil', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profil', [ProfileController::class, 'update'])->name('profile.update');
});

Route::middleware(['auth', 'tahun.aktif'])->group(function () {

    Route::get('/beranda', [BerandaController::class, 'index'])->name('beranda');

    /* ── Jadwal Mengajar (guru: lihat saja) ─────────────────────────── */
    Route::get('/jadwal', [JadwalController::class, 'index'])->name('jadwal.index');
    Route::get('/jadwal/cetak', [JadwalController::class, 'cetak'])->name('jadwal.cetak');

    /* ── Agenda Mengajar ────────────────────────────────────────────── */
    Route::get('/agenda', [AgendaController::class, 'index'])->name('agenda.index');
    Route::get('/agenda/dari-jadwal', [AgendaController::class, 'dariJadwal'])->name('agenda.dari-jadwal');
    Route::post('/agenda/dari-jadwal', [AgendaController::class, 'simpanMassal'])->name('agenda.simpan-massal');
    Route::get('/agenda/ekspor', [AgendaController::class, 'ekspor'])->name('agenda.ekspor');
    Route::get('/agenda/cetak', [AgendaController::class, 'cetak'])->name('agenda.cetak');
    Route::get('/agenda/{agenda}/ubah', [AgendaController::class, 'edit'])->name('agenda.edit');
    Route::put('/agenda/{agenda}', [AgendaController::class, 'update'])->name('agenda.update');
    Route::delete('/agenda/{agenda}', [AgendaController::class, 'destroy'])->name('agenda.destroy');
    Route::post('/agenda/{agenda}/buka-kunci', [AgendaController::class, 'bukaKunci'])->name('agenda.buka-kunci');

    /* ── Presensi Siswa ─────────────────────────────────────────────── */
    Route::get('/presensi', [PresensiController::class, 'index'])->name('presensi.index');
    Route::post('/presensi/langsung', [PresensiController::class, 'simpanLangsung'])->name('presensi.simpan-langsung');
    Route::get('/presensi/rekap', [PresensiController::class, 'rekap'])->name('presensi.rekap');
    Route::get('/presensi/rekap/ekspor', [PresensiController::class, 'ekspor'])->name('presensi.ekspor');
    Route::get('/presensi/rekap/cetak', [PresensiController::class, 'cetak'])->name('presensi.cetak');
    Route::get('/presensi/{agenda}', [PresensiController::class, 'isi'])->name('presensi.isi');
    Route::post('/presensi/{agenda}', [PresensiController::class, 'simpan'])->name('presensi.simpan');

    /* ── Penilaian & Daftar Nilai ───────────────────────────────────── */
    Route::get('/nilai', [NilaiController::class, 'index'])->name('nilai.index');
    Route::get('/nilai/ekspor', [NilaiController::class, 'ekspor'])->name('nilai.ekspor');
    Route::get('/nilai/cetak', [NilaiController::class, 'cetak'])->name('nilai.cetak');
    Route::get('/nilai/template', [NilaiController::class, 'template'])->name('nilai.template');
    Route::post('/nilai/{penilaian}/impor', [NilaiController::class, 'impor'])->name('nilai.impor');

    Route::get('/penilaian', [PenilaianController::class, 'index'])->name('penilaian.index');
    Route::get('/penilaian/buat', [PenilaianController::class, 'create'])->name('penilaian.create');
    Route::post('/penilaian', [PenilaianController::class, 'store'])->name('penilaian.store');
    Route::get('/penilaian/{penilaian}/ubah', [PenilaianController::class, 'edit'])->name('penilaian.edit');
    Route::put('/penilaian/{penilaian}', [PenilaianController::class, 'update'])->name('penilaian.update');
    Route::delete('/penilaian/{penilaian}', [PenilaianController::class, 'destroy'])->name('penilaian.destroy');
    Route::post('/penilaian/{penilaian}/buka-kunci', [PenilaianController::class, 'bukaKunci'])->name('penilaian.buka-kunci');

    /* ── Bab & Tujuan Pembelajaran ──────────────────────────────────── */
    Route::get('/bab', [BabController::class, 'index'])->name('bab.index');
    Route::get('/bab/buat', [BabController::class, 'create'])->name('bab.create');
    Route::post('/bab', [BabController::class, 'store'])->name('bab.store');
    Route::get('/bab/{bab}', [BabController::class, 'show'])->name('bab.show');
    Route::get('/bab/{bab}/ubah', [BabController::class, 'edit'])->name('bab.edit');
    Route::put('/bab/{bab}', [BabController::class, 'update'])->name('bab.update');
    Route::delete('/bab/{bab}', [BabController::class, 'destroy'])->name('bab.destroy');
    Route::post('/bab/{bab}/tujuan', [TujuanPembelajaranController::class, 'store'])->name('tujuan.store');
    Route::put('/tujuan/{tujuan}', [TujuanPembelajaranController::class, 'update'])->name('tujuan.update');
    Route::delete('/tujuan/{tujuan}', [TujuanPembelajaranController::class, 'destroy'])->name('tujuan.destroy');

    /* ── Bank Materi ────────────────────────────────────────────────── */
    Route::get('/materi', [MateriController::class, 'index'])->name('materi.index');
    Route::post('/materi/folder', [MateriController::class, 'simpanFolder'])->name('materi.folder.store');
    Route::put('/materi/folder/{folder}', [MateriController::class, 'ubahFolder'])->name('materi.folder.update');
    Route::delete('/materi/folder/{folder}', [MateriController::class, 'hapusFolder'])->name('materi.folder.destroy');
    Route::get('/materi/folder/{folder}', [MateriController::class, 'show'])->name('materi.folder.show');
    Route::post('/materi/folder/{folder}/berkas', [MateriController::class, 'simpanMateri'])->name('materi.store');
    Route::get('/materi/{materi}/unduh', [MateriController::class, 'unduh'])->name('materi.unduh');
    Route::delete('/materi/{materi}', [MateriController::class, 'destroy'])->name('materi.destroy');

    /* ── Rekap Rapor ────────────────────────────────────────────────── */
    Route::get('/rapor', [RaporController::class, 'index'])->name('rapor.index');
    Route::post('/rapor/bekukan', [RaporController::class, 'bekukan'])->name('rapor.bekukan');
    Route::put('/rapor/{rapor}/deskripsi', [RaporController::class, 'ubahDeskripsi'])->name('rapor.deskripsi');
    Route::get('/rapor/ekspor', [RaporController::class, 'ekspor'])->name('rapor.ekspor');
    Route::get('/rapor/cetak', [RaporController::class, 'cetak'])->name('rapor.cetak');
    Route::get('/rapor/kartu/{siswa}', [RaporController::class, 'kartu'])->name('rapor.kartu');

    /* ── Administrasi ───────────────────────────────────────────────── */
    Route::middleware('role:admin')->prefix('admin')->name('admin.')->group(function () {
        Route::resource('tahun-ajaran', TahunAjaranController::class)->except(['show'])
            ->parameters(['tahun-ajaran' => 'tahunAjaran']);
        Route::post('tahun-ajaran/{tahunAjaran}/aktifkan', [TahunAjaranController::class, 'aktifkan'])
            ->name('tahun-ajaran.aktifkan');
        Route::post('tahun-ajaran/{tahunAjaran}/salin-data', [TahunAjaranController::class, 'salinData'])
            ->name('tahun-ajaran.salin-data');

        Route::resource('mata-pelajaran', MataPelajaranController::class)->except(['show'])
            ->parameters(['mata-pelajaran' => 'mataPelajaran']);

        Route::resource('kelas', KelasController::class)->parameters(['kelas' => 'kelas']);
        Route::post('kelas/{kelas}/siswa', [KelasController::class, 'tambahSiswa'])->name('kelas.siswa.tambah');
        Route::delete('kelas/{kelas}/siswa/{siswa}', [KelasController::class, 'keluarkanSiswa'])->name('kelas.siswa.keluarkan');

        Route::get('siswa/template', [SiswaController::class, 'template'])->name('siswa.template');
        Route::post('siswa/impor', [SiswaController::class, 'impor'])->name('siswa.impor');
        Route::get('siswa/ekspor', [SiswaController::class, 'ekspor'])->name('siswa.ekspor');
        Route::post('siswa/reset', [SiswaController::class, 'reset'])->name('siswa.reset');
        Route::resource('siswa', SiswaController::class)->except(['show']);

        Route::get('jadwal/template', [JadwalAdminController::class, 'template'])->name('jadwal.template');
        Route::post('jadwal/impor', [JadwalAdminController::class, 'impor'])->name('jadwal.impor');
        Route::get('jadwal/bulk', [JadwalAdminController::class, 'createBulk'])->name('jadwal.bulk');
        Route::post('jadwal/bulk', [JadwalAdminController::class, 'storeBulk'])->name('jadwal.bulk.store');
        Route::post('jadwal/bulk-delete', [JadwalAdminController::class, 'bulkDestroy'])->name('jadwal.bulk-delete');
        Route::post('jadwal/reset', [JadwalAdminController::class, 'reset'])->name('jadwal.reset');
        Route::post('jadwal/ubah-mode', [JadwalAdminController::class, 'ubahMode'])->name('jadwal.ubah-mode');
        Route::post('jadwal/simpan-slot', [JadwalAdminController::class, 'simpanSlotMode'])->name('jadwal.simpan-slot');
        Route::post('jadwal/reset-slot', [JadwalAdminController::class, 'resetSlotMode'])->name('jadwal.reset-slot');
        Route::resource('jadwal', JadwalAdminController::class)->except(['show']);

        Route::get('pengguna/template-update', [PenggunaController::class, 'templateUpdate'])->name('pengguna.template-update');
        Route::get('pengguna/ekspor', [PenggunaController::class, 'ekspor'])->name('pengguna.ekspor');
        Route::post('pengguna/update-massal', [PenggunaController::class, 'updateMassal'])->name('pengguna.update-massal');
        Route::resource('pengguna', PenggunaController::class)->except(['show']);

        Route::get('kktp', [KktpController::class, 'index'])->name('kktp.index');
        Route::post('kktp', [KktpController::class, 'simpan'])->name('kktp.simpan');

        Route::get('pengaturan', [PengaturanController::class, 'edit'])->name('pengaturan.edit');
        Route::put('pengaturan', [PengaturanController::class, 'update'])->name('pengaturan.update');

        Route::get('log', [LogAktivitasController::class, 'index'])->name('log.index');
    });
});

require __DIR__.'/auth.php';
