<?php

namespace App\Http\Controllers\Guru;

use App\Exports\RaporExport;
use App\Http\Controllers\Controller;
use App\Models\Agenda;
use App\Models\Kktp;
use App\Models\MataPelajaran;
use App\Models\Penilaian;
use App\Models\Pengaturan;
use App\Models\RaporMapel;
use App\Models\Siswa;
use App\Services\DeskripsiRaporService;
use App\Services\PerhitunganNilaiService;
use App\Services\RekapPresensiService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response;

class RaporController extends Controller
{
    public function __construct(
        protected PerhitunganNilaiService $perhitungan,
        protected RekapPresensiService $rekapPresensi,
        protected DeskripsiRaporService $deskripsi,
    ) {}

    public function index(Request $request): View
    {
        return view('rapor.index', $this->konteks($request));
    }

    /** Bekukan hasil rekap ke tabel rapor_mapels + kunci penilaian & agenda (§7.7, §8.5). */
    public function bekukan(Request $request): RedirectResponse
    {
        $konteks = $this->konteks($request);
        $kelas = $konteks['kelas'];
        $mataPelajaranId = $konteks['mataPelajaranId'];

        abort_unless($kelas && $mataPelajaranId, 404, 'Pilih kelas dan mata pelajaran.');
        $this->authorize('view', $kelas);

        DB::transaction(function () use ($konteks, $kelas, $mataPelajaranId) {
            foreach ($konteks['baris'] as $baris) {
                RaporMapel::updateOrCreate([
                    'tahun_ajaran_id' => $kelas->tahun_ajaran_id,
                    'kelas_id' => $kelas->id,
                    'mata_pelajaran_id' => $mataPelajaranId,
                    'siswa_id' => $baris['siswa']->id,
                ], [
                    'nilai_formatif' => $baris['nilai']['formatif'],
                    'nilai_sumatif_lingkup' => $baris['nilai']['sumatif_lingkup'],
                    'nilai_sumatif_akhir' => $baris['nilai']['sumatif_akhir'],
                    'nilai_akhir' => round($baris['nilai']['nilai_akhir']),
                    'predikat' => $baris['nilai']['predikat'],
                    'is_tuntas' => $baris['nilai']['is_tuntas'],
                    // Deskripsi yang sudah disunting guru tidak ditimpa.
                    'deskripsi_capaian' => $baris['rapor']?->deskripsi_capaian ?: $baris['deskripsi'],
                    'jumlah_pertemuan' => $baris['presensi']['pertemuan'],
                    'jumlah_hadir' => $baris['presensi']['hadir'] + $baris['presensi']['dispensasi'],
                    'jumlah_sakit' => $baris['presensi']['sakit'],
                    'jumlah_izin' => $baris['presensi']['izin'],
                    'jumlah_alfa' => $baris['presensi']['alfa'] + $baris['presensi']['bolos'],
                    'dibekukan_pada' => now(),
                ]);
            }

            Penilaian::where('kelas_id', $kelas->id)
                ->where('mata_pelajaran_id', $mataPelajaranId)
                ->where('tahun_ajaran_id', $kelas->tahun_ajaran_id)
                ->update(['is_terkunci' => true]);

            Agenda::whereHas('jadwal', fn ($q) => $q
                ->where('kelas_id', $kelas->id)
                ->where('mata_pelajaran_id', $mataPelajaranId)
                ->where('tahun_ajaran_id', $kelas->tahun_ajaran_id))
                ->update(['is_terkunci' => true]);
        });

        activity('rapor')
            ->withProperties(['kelas' => $kelas->nama, 'mapel_id' => $mataPelajaranId])
            ->log('Rapor dibekukan');

        return back()->with('sukses', 'Rapor dibekukan. Penilaian & agenda semester ini otomatis terkunci.');
    }

    public function ubahDeskripsi(Request $request, RaporMapel $rapor): RedirectResponse
    {
        $this->authorize('view', $rapor->kelas);

        $data = $request->validate([
            'deskripsi_capaian' => ['required', 'string', 'max:1000'],
        ], [], ['deskripsi_capaian' => 'deskripsi capaian']);

        $rapor->update($data);

        return back()->with('sukses', 'Deskripsi capaian diperbarui.');
    }

    public function ekspor(Request $request): BinaryFileResponse
    {
        $konteks = $this->konteks($request);

        abort_unless($konteks['kelas'], 404, 'Pilih kelas terlebih dahulu.');

        return Excel::download(
            new RaporExport($konteks['baris'], $konteks['kelas'], $konteks['mataPelajaran']),
            'leger-'.str($konteks['kelas']->nama)->slug().'.xlsx'
        );
    }

    /** Leger nilai F4 dengan kop sekolah & tanda tangan. */
    public function cetak(Request $request): Response
    {
        $konteks = $this->konteks($request);

        abort_unless($konteks['kelas'], 404, 'Pilih kelas terlebih dahulu.');

        $pdf = Pdf::loadView('cetak.leger', [
            ...$konteks,
            'guru' => $request->user(),
            'pengaturan' => Pengaturan::semua(),
        ])->setPaper([0, 0, 609.45, 935.43], 'landscape');

        return $pdf->stream('leger-nilai.pdf');
    }

    /** Kartu nilai satu siswa (A4 portrait). */
    public function kartu(Request $request, Siswa $siswa): Response
    {
        $konteks = $this->konteks($request);
        $baris = collect($konteks['baris'])->firstWhere('siswa.id', $siswa->id);

        abort_unless($baris, 404, 'Siswa tidak terdaftar di kelas terpilih.');

        $pdf = Pdf::loadView('cetak.kartu-nilai', [
            ...$konteks,
            'baris' => $baris,
            'siswa' => $siswa,
            'guru' => $request->user(),
            'pengaturan' => Pengaturan::semua(),
        ])->setPaper('a4', 'portrait');

        return $pdf->stream('kartu-nilai-'.str($siswa->nama)->slug().'.pdf');
    }

    /**
     * Menyusun seluruh data satu kelas + mapel: nilai, presensi, deskripsi, dan
     * baris rapor yang sudah dibekukan (bila ada).
     */
    protected function konteks(Request $request): array
    {
        $kelasList = $request->user()->kelasBolehDilihat();
        $kelas = $kelasList->firstWhere('id', $request->integer('kelas_id')) ?? $kelasList->first();

        $mapelList = MataPelajaran::orderBy('nama')->get();
        $mataPelajaranId = $mapelList->firstWhere('id', $request->integer('mata_pelajaran_id'))?->id
            ?? $mapelList->first()?->id;
        $mataPelajaran = $mapelList->firstWhere('id', $mataPelajaranId);

        if (! $kelas || ! $mataPelajaranId) {
            return [
                'kelasList' => $kelasList, 'kelas' => $kelas, 'mapelList' => $mapelList,
                'mataPelajaranId' => $mataPelajaranId, 'mataPelajaran' => $mataPelajaran,
                'baris' => collect(), 'kktp' => 75, 'sudahDibekukan' => false,
            ];
        }

        $penilaians = Penilaian::query()
            ->where('tahun_ajaran_id', $kelas->tahun_ajaran_id)
            ->where('kelas_id', $kelas->id)
            ->where('mata_pelajaran_id', $mataPelajaranId)
            ->with(['nilais', 'bab', 'tujuanPembelajaran', 'kelas'])
            ->orderBy('tanggal')
            ->get();

        $kktp = Kktp::untuk($mataPelajaranId, $kelas->tingkat, $kelas->tahun_ajaran_id);
        $rekapPresensi = $this->rekapPresensi->rekapKelas($kelas, $mataPelajaranId)->keyBy('siswa_id');

        $raporTersimpan = RaporMapel::query()
            ->where('kelas_id', $kelas->id)
            ->where('mata_pelajaran_id', $mataPelajaranId)
            ->where('tahun_ajaran_id', $kelas->tahun_ajaran_id)
            ->get()
            ->keyBy('siswa_id');

        $baris = $kelas->siswas()->get()->map(function (Siswa $siswa) use ($penilaians, $kktp, $rekapPresensi, $raporTersimpan) {
            $nilai = $this->perhitungan->hitungSiswa($siswa->id, $penilaians, $kktp);

            return [
                'siswa' => $siswa,
                'nilai' => $nilai,
                'presensi' => $rekapPresensi->get($siswa->id, [
                    'hadir' => 0, 'sakit' => 0, 'izin' => 0, 'alfa' => 0,
                    'bolos' => 0, 'dispensasi' => 0, 'pertemuan' => 0, 'persentase' => 0.0,
                ]),
                'deskripsi' => $this->deskripsi->untukSiswa($siswa, $penilaians, $nilai['predikat']),
                'rapor' => $raporTersimpan->get($siswa->id),
            ];
        });

        return [
            'kelasList' => $kelasList,
            'kelas' => $kelas,
            'mapelList' => $mapelList,
            'mataPelajaranId' => $mataPelajaranId,
            'mataPelajaran' => $mataPelajaran,
            'baris' => $baris,
            'kktp' => $kktp,
            'sudahDibekukan' => $raporTersimpan->isNotEmpty(),
        ];
    }
}
