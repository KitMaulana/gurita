<?php

namespace App\Http\Controllers\Guru;

use App\Enums\JenisPenilaian;
use App\Http\Controllers\Controller;
use App\Http\Requests\PenilaianRequest;
use App\Models\Bab;
use App\Models\MataPelajaran;
use App\Models\Nilai;
use App\Models\Penilaian;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PenilaianController extends Controller
{
    public function index(Request $request): View
    {
        return view('penilaian.index', [
            'daftar' => Penilaian::query()
                ->tahunAktif()
                ->where('guru_id', $request->user()->id)
                ->when($request->integer('kelas_id'), fn ($q, $id) => $q->where('kelas_id', $id))
                ->when($request->filled('jenis'), fn ($q) => $q->where('jenis', $request->string('jenis')))
                ->with(['kelas', 'mataPelajaran', 'bab'])
                ->withCount([
                    'nilais',
                    'nilais as belum_dinilai' => fn ($q) => $q->whereNull('nilai'),
                ])
                ->orderByDesc('tanggal')
                ->paginate(20)
                ->withQueryString(),
            'kelasList' => $request->user()->kelasBolehDilihat(),
            'jenisList' => JenisPenilaian::pilihan(),
        ]);
    }

    public function create(Request $request): View
    {
        return view('penilaian.form', $this->dataForm($request, new Penilaian([
            'tanggal' => today(),
            'nilai_maksimal' => 100,
            'kelas_id' => $request->integer('kelas_id') ?: null,
            'mata_pelajaran_id' => $request->integer('mata_pelajaran_id') ?: null,
        ])));
    }

    public function store(PenilaianRequest $request): RedirectResponse
    {
        $penilaian = DB::transaction(function () use ($request) {
            $penilaian = Penilaian::create($request->validated());
            $this->siapkanBarisNilai($penilaian);

            return $penilaian;
        });

        return redirect()
            ->route('nilai.index', [
                'kelas_id' => $penilaian->kelas_id,
                'mata_pelajaran_id' => $penilaian->mata_pelajaran_id,
            ])
            ->with('sukses', 'Penilaian dibuat. Silakan isi nilainya.');
    }

    public function edit(Request $request, Penilaian $penilaian): View
    {
        $this->authorize('update', $penilaian);

        return view('penilaian.form', $this->dataForm($request, $penilaian));
    }

    public function update(PenilaianRequest $request, Penilaian $penilaian): RedirectResponse
    {
        $penilaian->update($request->validated());
        $this->siapkanBarisNilai($penilaian);

        return redirect()->route('penilaian.index')->with('sukses', 'Penilaian berhasil diperbarui.');
    }

    public function destroy(Penilaian $penilaian): RedirectResponse
    {
        $this->authorize('delete', $penilaian);
        $penilaian->delete();

        return back()->with('sukses', 'Penilaian dihapus.');
    }

    public function bukaKunci(Penilaian $penilaian): RedirectResponse
    {
        $this->authorize('bukaKunci', $penilaian);
        $penilaian->update(['is_terkunci' => false]);

        activity('penilaian')->performedOn($penilaian)->log('Kunci penilaian dibuka oleh admin');

        return back()->with('sukses', 'Kunci penilaian dibuka.');
    }

    /** Buat baris nilai kosong untuk seluruh siswa di kelas tersebut. */
    protected function siapkanBarisNilai(Penilaian $penilaian): void
    {
        $siswaIds = $penilaian->kelas->siswas()->pluck('siswas.id');
        $sudahAda = $penilaian->nilais()->pluck('siswa_id')->all();

        $baru = $siswaIds->diff($sudahAda)->map(fn ($id) => [
            'penilaian_id' => $penilaian->id,
            'siswa_id' => $id,
            'nilai' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ])->all();

        if ($baru) {
            Nilai::insert($baru);
        }
    }

    protected function dataForm(Request $request, Penilaian $penilaian): array
    {
        $kelasList = $request->user()->kelasBolehDilihat();

        return [
            'penilaian' => $penilaian,
            'kelasList' => $kelasList->pluck('nama', 'id'),
            'mapelList' => MataPelajaran::orderBy('nama')->pluck('nama', 'id'),
            'jenisList' => JenisPenilaian::pilihan(),
            'babList' => Bab::with('tujuanPembelajarans')->orderBy('urutan')->get(),
        ];
    }
}
