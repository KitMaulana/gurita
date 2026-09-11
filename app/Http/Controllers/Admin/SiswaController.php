<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Imports\SiswaImport;
use App\Models\Jadwal;
use App\Models\Kelas;
use App\Models\Penilaian;
use App\Models\Siswa;
use App\Models\TahunAjaran;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SiswaController extends Controller
{
    public function index(Request $request): View
    {
        $tahunAjaranId = TahunAjaran::aktif()?->id;
        $kelasId = $request->integer('kelas_id');

        return view('admin.siswa.index', [
            'daftar' => Siswa::query()
                ->when($request->filled('cari'), function ($q) use ($request) {
                    $cari = $request->string('cari');
                    $q->where(fn ($sub) => $sub->where('nama', 'like', "%{$cari}%")
                        ->orWhere('nisn', 'like', "%{$cari}%")
                        ->orWhere('nis', 'like', "%{$cari}%"));
                })
                ->when($kelasId, fn ($q) => $q->whereHas('kelas', fn ($sub) => $sub->where('kelas.id', $kelasId)))
                ->with(['kelas' => fn ($q) => $q->where('kelas.tahun_ajaran_id', $tahunAjaranId)])
                ->orderBy('nama')
                ->paginate(25)
                ->withQueryString(),
            'kelasList' => Kelas::where('tahun_ajaran_id', $tahunAjaranId)->orderBy('nama')->get(),
            'kelasId' => $kelasId,
            'totalSiswa' => Siswa::count(),
            'totalKelas' => Kelas::where('tahun_ajaran_id', $tahunAjaranId)->count(),
        ]);
    }

    public function create(): View
    {
        return view('admin.siswa.form', ['siswa' => new Siswa(['is_aktif' => true])]);
    }

    public function store(Request $request): RedirectResponse
    {
        Siswa::create($this->validasi($request));

        return redirect()->route('admin.siswa.index')->with('sukses', 'Siswa berhasil ditambahkan.');
    }

    public function edit(Siswa $siswa): View
    {
        return view('admin.siswa.form', compact('siswa'));
    }

    public function update(Request $request, Siswa $siswa): RedirectResponse
    {
        $siswa->update($this->validasi($request, $siswa->id));

        return redirect()->route('admin.siswa.index')->with('sukses', 'Data siswa berhasil diperbarui.');
    }

    public function destroy(Siswa $siswa): RedirectResponse
    {
        $siswa->delete();   // soft delete, data nilai & presensi tetap utuh

        return back()->with('sukses', 'Siswa dinonaktifkan (soft delete).');
    }

    /** Unduh template_siswa.csv (§9). */
    public function template(): StreamedResponse
    {
        $kelasContoh1 = Kelas::tahunAktif()->orderBy('nama')->first()?->nama ?? 'X-1';
        $kelasContoh2 = Kelas::tahunAktif()->orderBy('nama')->skip(1)->first()?->nama ?? $kelasContoh1;

        $baris = [
            ['nisn', 'nis', 'nama', 'jenis_kelamin', 'kelas', 'no_absen'],
            ['0071234567', '12345', 'Ahmad Rizki', 'L', $kelasContoh1, '1'],
            ['', '12346', 'Siti Nurhaliza', 'P', $kelasContoh2, '2'],
        ];

        return $this->unduhCsv('template_siswa.csv', $baris);
    }

    /**
     * Reset seluruh data siswa dan data kelas pada tahun ajaran aktif secara permanen.
     * Jadwal pelajaran tetap aman (relasi kelas_id pada jadwal dilepas menjadi null).
     */
    public function reset(Request $request): RedirectResponse
    {
        @set_time_limit(180);
        @ini_set('max_execution_time', '180');

        $tahunAjaranId = TahunAjaran::aktif()?->id;

        DB::transaction(function () use ($tahunAjaranId) {
            // 1. Dapatkan daftar ID kelas yang terkait
            $kelasQuery = Kelas::query();
            if ($tahunAjaranId) {
                $kelasQuery->where('tahun_ajaran_id', $tahunAjaranId);
            }
            $kelasIds = $kelasQuery->pluck('id')->all();

            // 2. Lepaskan hubungan kelas pada jadwal pelajaran agar jadwal TIDAK terhapus oleh cascade onDelete
            if (! empty($kelasIds)) {
                Jadwal::whereIn('kelas_id', $kelasIds)->update(['kelas_id' => null]);
            } else {
                Jadwal::query()->update(['kelas_id' => null]);
            }

            // 3. Bersihkan data nilai, presensi, dan rapor yang terikat pada siswa
            DB::table('nilais')->delete();
            DB::table('presensis')->delete();
            DB::table('rapor_mapels')->delete();

            // 4. Bersihkan penilaian dan folder materi yang terikat pada kelas yang direset
            if (! empty($kelasIds)) {
                Penilaian::whereIn('kelas_id', $kelasIds)->forceDelete();
                DB::table('folder_materi_kelas')->whereIn('kelas_id', $kelasIds)->delete();
            } else {
                Penilaian::query()->forceDelete();
                DB::table('folder_materi_kelas')->delete();
            }

            // 5. Bersihkan relasi kelas_siswa
            DB::table('kelas_siswa')->delete();

            // 6. Hapus seluruh data siswa secara permanen
            Siswa::withTrashed()->forceDelete();

            // 7. Hapus seluruh data kelas yang bersangkutan secara permanen
            if (! empty($kelasIds)) {
                Kelas::whereIn('id', $kelasIds)->forceDelete();
            } else {
                Kelas::withTrashed()->forceDelete();
            }
        });

        return redirect()->route('admin.siswa.index')
            ->with('sukses', 'Seluruh data siswa dan kelas berhasil direset. Jadwal pelajaran tetap tersimpan aman tanpa kelas.');
    }

    public function impor(Request $request): RedirectResponse
    {
        $request->validate([
            'berkas' => ['required', 'file', 'mimes:csv,txt,xlsx,xls', 'max:5120'],
        ], [], ['berkas' => 'berkas impor']);

        $import = new SiswaImport;
        Excel::import($import, $request->file('berkas'));

        $redirect = back();
        if (! empty($import->kelasBaru)) {
            $redirect->with('kelasBaruCreated', array_values(array_unique($import->kelasBaru)));
        }

        if ($import->galat) {
            return $redirect
                ->with('peringatan', 'Impor selesai sebagian: '.$import->ringkasan())
                ->with('galatImpor', $import->galat);
        }

        return $redirect->with('sukses', 'Impor berhasil: '.$import->ringkasan());
    }

    public function ekspor(Request $request): StreamedResponse
    {
        $tahunAjaranId = TahunAjaran::aktif()?->id;

        $siswas = Siswa::query()
            ->when($request->integer('kelas_id'), fn ($q, $id) => $q->whereHas('kelas', fn ($s) => $s->where('kelas.id', $id)))
            ->with(['kelas' => fn ($q) => $q->where('kelas.tahun_ajaran_id', $tahunAjaranId)])
            ->orderBy('nama')
            ->get();

        $baris = [['nisn', 'nis', 'nama', 'jenis_kelamin', 'kelas', 'no_absen']];

        foreach ($siswas as $siswa) {
            $kelas = $siswa->kelas->first();
            $baris[] = [
                $siswa->nisn, $siswa->nis, $siswa->nama, $siswa->jenis_kelamin,
                $kelas?->nama ?? '', $kelas?->pivot->no_absen ?? '',
            ];
        }

        return $this->unduhCsv('daftar_siswa.csv', $baris);
    }

    protected function validasi(Request $request, ?int $id = null): array
    {
        $data = $request->validate([
            'nisn' => ['nullable', 'string', 'max:20', Rule::unique('siswas', 'nisn')->ignore($id)],
            'nis' => ['nullable', 'string', 'max:20'],
            'nama' => ['required', 'string', 'max:255'],
            'jenis_kelamin' => ['required', Rule::in(['L', 'P'])],
            'is_aktif' => ['nullable', 'boolean'],
        ], [
            'nisn.unique' => 'NISN tersebut sudah terdaftar.',
        ], [
            'nama' => 'nama siswa',
            'jenis_kelamin' => 'jenis kelamin',
        ]);

        $data['nisn'] = ! empty(trim((string) ($data['nisn'] ?? ''))) ? trim((string) $data['nisn']) : null;
        $data['nis'] = ! empty(trim((string) ($data['nis'] ?? ''))) ? trim((string) $data['nis']) : null;

        return $data;
    }

    /** @param  array<int,array<int,string|null>>  $baris */
    protected function unduhCsv(string $namaBerkas, array $baris): StreamedResponse
    {
        return response()->streamDownload(function () use ($baris) {
            $keluaran = fopen('php://output', 'w');
            fwrite($keluaran, "\xEF\xBB\xBF");   // BOM agar Excel membaca UTF-8

            foreach ($baris as $item) {
                fputcsv($keluaran, $item);
            }

            fclose($keluaran);
        }, $namaBerkas, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }
}
