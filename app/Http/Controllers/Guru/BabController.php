<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Models\Bab;
use App\Models\MataPelajaran;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class BabController extends Controller
{
    public function index(Request $request): View
    {
        return view('bab.index', [
            'daftar' => Bab::query()
                ->when($request->integer('mata_pelajaran_id'), fn ($q, $id) => $q->where('mata_pelajaran_id', $id))
                ->when($request->filled('tingkat'), fn ($q) => $q->where('tingkat', $request->string('tingkat')))
                ->with('mataPelajaran')
                ->withCount('tujuanPembelajarans')
                ->orderBy('mata_pelajaran_id')->orderBy('tingkat')->orderBy('urutan')
                ->paginate(25)
                ->withQueryString(),
            'mapelList' => MataPelajaran::orderBy('nama')->get(),
        ]);
    }

    public function create(): View
    {
        return view('bab.form', [
            'bab' => new Bab(['urutan' => 1]),
            'mapelList' => MataPelajaran::orderBy('nama')->pluck('nama', 'id'),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $bab = Bab::create($this->validasi($request));

        return redirect()->route('bab.show', $bab)
            ->with('sukses', 'Bab dibuat. Tambahkan Tujuan Pembelajaran di bawah.');
    }

    public function show(Bab $bab): View
    {
        $bab->load(['mataPelajaran', 'tujuanPembelajarans']);

        return view('bab.show', compact('bab'));
    }

    public function edit(Bab $bab): View
    {
        return view('bab.form', [
            'bab' => $bab,
            'mapelList' => MataPelajaran::orderBy('nama')->pluck('nama', 'id'),
        ]);
    }

    public function update(Request $request, Bab $bab): RedirectResponse
    {
        $bab->update($this->validasi($request, $bab->id));

        return redirect()->route('bab.show', $bab)->with('sukses', 'Bab berhasil diperbarui.');
    }

    public function destroy(Bab $bab): RedirectResponse
    {
        if ($bab->penilaians()->exists()) {
            return back()->with('galat', 'Bab masih dipakai pada penilaian dan tidak dapat dihapus.');
        }

        $bab->delete();

        return redirect()->route('bab.index')->with('sukses', 'Bab dihapus.');
    }

    protected function validasi(Request $request, ?int $id = null): array
    {
        return $request->validate([
            'mata_pelajaran_id' => ['required', 'exists:mata_pelajarans,id'],
            'tingkat' => ['required', Rule::in(['X', 'XI', 'XII'])],
            'kode' => ['required', 'string', 'max:30'],
            'judul' => ['required', 'string', 'max:255'],
            'capaian_pembelajaran' => ['nullable', 'string', 'max:2000'],
            'urutan' => ['required', 'integer', 'min:1', 'max:100'],
        ], [], [
            'mata_pelajaran_id' => 'mata pelajaran',
            'kode' => 'kode bab',
            'judul' => 'judul bab',
            'capaian_pembelajaran' => 'capaian pembelajaran',
        ]);
    }
}
