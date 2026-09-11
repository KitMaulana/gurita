<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Models\Bab;
use App\Models\TujuanPembelajaran;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class TujuanPembelajaranController extends Controller
{
    public function store(Request $request, Bab $bab): RedirectResponse
    {
        $data = $this->validasi($request);

        $bab->tujuanPembelajarans()->create($data);

        return back()->with('sukses', 'Tujuan Pembelajaran ditambahkan.');
    }

    public function update(Request $request, TujuanPembelajaran $tujuan): RedirectResponse
    {
        $tujuan->update($this->validasi($request));

        return back()->with('sukses', 'Tujuan Pembelajaran diperbarui.');
    }

    public function destroy(TujuanPembelajaran $tujuan): RedirectResponse
    {
        if ($tujuan->penilaians()->exists()) {
            return back()->with('galat', 'Tujuan Pembelajaran masih dipakai pada penilaian.');
        }

        $tujuan->delete();

        return back()->with('sukses', 'Tujuan Pembelajaran dihapus.');
    }

    protected function validasi(Request $request): array
    {
        return $request->validate([
            'kode' => ['required', 'string', 'max:30'],
            'deskripsi' => ['required', 'string', 'max:1000'],
            'urutan' => ['required', 'integer', 'min:1', 'max:100'],
        ], [], [
            'kode' => 'kode tujuan pembelajaran',
        ]);
    }
}
