<?php

namespace App\Http\Controllers\Guru;

use App\Exports\NilaiExport;
use App\Http\Controllers\Controller;
use App\Imports\NilaiImport;
use App\Models\Kktp;
use App\Models\MataPelajaran;
use App\Models\Penilaian;
use App\Models\Pengaturan;
use App\Services\PerhitunganNilaiService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class NilaiController extends Controller
{
    public function index(Request $request): View
    {
        $konteks = $this->konteks($request);

        return view('nilai.index', $konteks);
    }

    public function ekspor(Request $request): BinaryFileResponse
    {
        $konteks = $this->konteks($request);

        abort_unless($konteks['kelas'] && $konteks['mataPelajaranId'], 404, 'Pilih kelas dan mata pelajaran.');

        return Excel::download(
            new NilaiExport($konteks['kelas'], $konteks['mataPelajaranId']),
            'daftar-nilai-'.str($konteks['kelas']->nama)->slug().'.xlsx'
        );
    }

    public function cetak(Request $request): Response
    {
        $konteks = $this->konteks($request);

        abort_unless($konteks['kelas'] && $konteks['mataPelajaranId'], 404, 'Pilih kelas dan mata pelajaran.');

        $penilaians = Penilaian::query()
            ->tahunAktif()
            ->where('kelas_id', $konteks['kelas']->id)
            ->where('mata_pelajaran_id', $konteks['mataPelajaranId'])
            ->with('nilais')
            ->orderBy('jenis')->orderBy('tanggal')
            ->get();

        $perhitungan = app(PerhitunganNilaiService::class);
        $kktp = Kktp::untuk($konteks['mataPelajaranId'], $konteks['kelas']->tingkat);

        $pdf = Pdf::loadView('cetak.daftar-nilai', [
            'kelas' => $konteks['kelas'],
            'mataPelajaran' => MataPelajaran::find($konteks['mataPelajaranId']),
            'penilaians' => $penilaians,
            'siswas' => $konteks['kelas']->siswas()->get(),
            'perhitungan' => $perhitungan,
            'kktp' => $kktp,
            'guru' => $request->user(),
            'pengaturan' => Pengaturan::semua(),
        ])->setPaper([0, 0, 609.45, 935.43], 'landscape');

        return $pdf->stream('daftar-nilai.pdf');
    }

    /** Unduh template_nilai.csv untuk satu penilaian, sudah terisi NISN & nama siswa. */
    public function template(Request $request): StreamedResponse
    {
        $penilaian = Penilaian::with('kelas.siswas')->findOrFail($request->integer('penilaian_id'));
        $this->authorize('view', $penilaian);

        $baris = [['nisn', 'nama', 'nilai']];

        foreach ($penilaian->kelas->siswas as $siswa) {
            $baris[] = [$siswa->nisn, $siswa->nama, ''];
        }

        return response()->streamDownload(function () use ($baris) {
            $keluaran = fopen('php://output', 'w');
            fwrite($keluaran, "\xEF\xBB\xBF");
            foreach ($baris as $item) {
                fputcsv($keluaran, $item);
            }
            fclose($keluaran);
        }, 'template_nilai.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    public function impor(Request $request, Penilaian $penilaian): RedirectResponse
    {
        $this->authorize('update', $penilaian);

        $request->validate([
            'berkas' => ['required', 'file', 'mimes:csv,txt,xlsx,xls', 'max:5120'],
        ], [], ['berkas' => 'berkas impor']);

        $import = new NilaiImport($penilaian);
        Excel::import($import, $request->file('berkas'));

        if ($import->galat) {
            return back()
                ->with('peringatan', 'Impor selesai sebagian: '.$import->ringkasan())
                ->with('galatImpor', $import->galat);
        }

        return back()->with('sukses', 'Impor berhasil: '.$import->ringkasan());
    }

    protected function konteks(Request $request): array
    {
        $kelasList = $request->user()->kelasBolehDilihat();
        $kelas = $kelasList->firstWhere('id', $request->integer('kelas_id')) ?? $kelasList->first();

        $mapelList = $kelas
            ? MataPelajaran::whereIn('id', $kelas->jadwals()->distinct()->pluck('mata_pelajaran_id'))
                ->orderBy('nama')->get()
            : collect();

        if ($mapelList->isEmpty()) {
            $mapelList = MataPelajaran::orderBy('nama')->get();
        }

        $mataPelajaranId = $mapelList->firstWhere('id', $request->integer('mata_pelajaran_id'))?->id
            ?? $mapelList->first()?->id;

        return [
            'kelasList' => $kelasList,
            'kelas' => $kelas,
            'mapelList' => $mapelList,
            'mataPelajaranId' => $mataPelajaranId,
            'penilaians' => $kelas && $mataPelajaranId
                ? Penilaian::tahunAktif()
                    ->where('kelas_id', $kelas->id)
                    ->where('mata_pelajaran_id', $mataPelajaranId)
                    ->orderBy('tanggal')->get()
                : collect(),
        ];
    }
}
