<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MataPelajaran;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class MataPelajaranController extends Controller
{
    public function index(): View
    {
        return view('admin.mata-pelajaran.index', [
            'daftar' => MataPelajaran::withCount(['jadwals', 'babs'])->orderBy('nama')->paginate(20),
        ]);
    }

    public function create(): View
    {
        return view('admin.mata-pelajaran.form', ['mataPelajaran' => new MataPelajaran]);
    }

    public function store(Request $request): RedirectResponse
    {
        MataPelajaran::create($this->validasi($request));

        return redirect()->route('admin.mata-pelajaran.index')
            ->with('sukses', 'Mata pelajaran berhasil ditambahkan.');
    }

    public function edit(MataPelajaran $mataPelajaran): View
    {
        return view('admin.mata-pelajaran.form', compact('mataPelajaran'));
    }

    public function update(Request $request, MataPelajaran $mataPelajaran): RedirectResponse
    {
        $mataPelajaran->update($this->validasi($request, $mataPelajaran->id));

        return redirect()->route('admin.mata-pelajaran.index')
            ->with('sukses', 'Mata pelajaran berhasil diperbarui.');
    }

    public function destroy(MataPelajaran $mataPelajaran): RedirectResponse
    {
        if ($mataPelajaran->jadwals()->exists()) {
            return back()->with('galat', 'Mata pelajaran masih dipakai pada jadwal dan tidak dapat dihapus.');
        }

        $mataPelajaran->delete();

        return back()->with('sukses', 'Mata pelajaran dihapus.');
    }

    protected function validasi(Request $request, ?int $id = null): array
    {
        return $request->validate([
            'nama' => ['required', 'string', 'max:100', Rule::unique('mata_pelajarans', 'nama')->ignore($id)],
            'singkatan' => ['required', 'string', 'max:20'],
            'kelompok' => ['required', Rule::in(['umum', 'pilihan', 'muatan_lokal'])],
        ], [], [
            'nama' => 'nama mata pelajaran',
            'kelompok' => 'kelompok mata pelajaran',
        ]);
    }
}
