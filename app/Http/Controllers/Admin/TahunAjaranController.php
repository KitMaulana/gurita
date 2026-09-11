<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\TahunAjaranRequest;
use App\Models\TahunAjaran;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class TahunAjaranController extends Controller
{
    public function index(): View
    {
        return view('admin.tahun-ajaran.index', [
            'daftar' => TahunAjaran::withCount(['kelas', 'jadwals'])
                ->orderByDesc('nama')
                ->orderBy('semester')
                ->paginate(15),
        ]);
    }

    public function create(): View
    {
        return view('admin.tahun-ajaran.form', ['tahunAjaran' => new TahunAjaran]);
    }

    public function store(TahunAjaranRequest $request, \App\Services\SalinDataSemesterService $salinService): RedirectResponse
    {
        $ta = TahunAjaran::create($request->dataTersimpan());

        $pesanSalin = '';
        if ($request->boolean('otomatis_salin')) {
            $asal = $ta->semesterSebelumnya();
            if ($asal && $asal->kelas()->exists()) {
                try {
                    $hasil = $salinService->salin($asal, $ta, [
                        'salin_kelas' => true,
                        'salin_siswa' => true,
                        'salin_jadwal' => true,
                        'salin_kktp' => true,
                    ]);
                    $pesanSalin = " Sekaligus menyalin {$hasil['kelas']} kelas, {$hasil['siswa']} penempatan siswa, {$hasil['jadwal']} jadwal pelajaran, {$hasil['kktp']} KKTP dari {$asal->label}.";
                } catch (\Throwable $e) {
                    $pesanSalin = " (Pemberitahuan salin data: {$e->getMessage()})";
                }
            }
        }

        return redirect()->route('admin.tahun-ajaran.index')
            ->with('sukses', 'Tahun ajaran berhasil ditambahkan.'.$pesanSalin);
    }

    public function edit(TahunAjaran $tahunAjaran): View
    {
        return view('admin.tahun-ajaran.form', compact('tahunAjaran'));
    }

    public function update(TahunAjaranRequest $request, TahunAjaran $tahunAjaran): RedirectResponse
    {
        $tahunAjaran->update($request->dataTersimpan());

        return redirect()->route('admin.tahun-ajaran.index')
            ->with('sukses', 'Tahun ajaran berhasil diperbarui.');
    }

    public function destroy(TahunAjaran $tahunAjaran): RedirectResponse
    {
        if ($tahunAjaran->is_aktif) {
            return back()->with('galat', 'Tahun ajaran yang sedang aktif tidak dapat dihapus.');
        }

        if ($tahunAjaran->kelas()->exists()) {
            return back()->with('galat', 'Tahun ajaran masih memiliki kelas. Hapus kelasnya lebih dahulu.');
        }

        $tahunAjaran->delete();

        return back()->with('sukses', 'Tahun ajaran dihapus.');
    }

    public function aktifkan(TahunAjaran $tahunAjaran): RedirectResponse
    {
        // Observer memastikan hanya satu tahun ajaran yang aktif.
        $tahunAjaran->update(['is_aktif' => true]);

        return back()->with('sukses', 'Tahun ajaran '.$tahunAjaran->label.' kini aktif.');
    }

    /**
     * Salin data (kelas, siswa, jadwal, kktp) dari semester sebelumnya dalam tahun pelajaran yang sama.
     */
    public function salinData(
        \Illuminate\Http\Request $request,
        TahunAjaran $tahunAjaran,
        \App\Services\SalinDataSemesterService $service
    ): RedirectResponse {
        $validated = $request->validate([
            'semester_asal_id' => ['required', 'exists:tahun_ajarans,id'],
            'salin_kelas' => ['nullable', 'boolean'],
            'salin_siswa' => ['nullable', 'boolean'],
            'salin_jadwal' => ['nullable', 'boolean'],
            'salin_kktp' => ['nullable', 'boolean'],
        ], [
            'semester_asal_id.required' => 'Semester asal wajib dipilih.',
            'semester_asal_id.exists' => 'Semester asal tidak ditemukan.',
        ]);

        $semesterAsal = TahunAjaran::findOrFail($validated['semester_asal_id']);

        try {
            $hasil = $service->salin($semesterAsal, $tahunAjaran, [
                'salin_kelas' => $request->boolean('salin_kelas', true),
                'salin_siswa' => $request->boolean('salin_siswa', true),
                'salin_jadwal' => $request->boolean('salin_jadwal', true),
                'salin_kktp' => $request->boolean('salin_kktp', true),
            ]);

            $pesan = "Data dari {$semesterAsal->label} berhasil disalin ke {$tahunAjaran->label}: "
                ."{$hasil['kelas']} kelas, {$hasil['siswa']} penempatan siswa, {$hasil['jadwal']} jadwal pelajaran, {$hasil['kktp']} KKTP.";

            return back()->with('sukses', $pesan);
        } catch (\InvalidArgumentException $e) {
            return back()->with('galat', $e->getMessage());
        } catch (\Throwable $e) {
            return back()->with('galat', 'Gagal menyalin data semester: '.$e->getMessage());
        }
    }
}
