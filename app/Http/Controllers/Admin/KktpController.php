<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Kktp;
use App\Models\MataPelajaran;
use App\Models\TahunAjaran;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class KktpController extends Controller
{
    protected const TINGKAT = ['X', 'XI', 'XII'];

    public function index(): View
    {
        $tahunAjaran = TahunAjaran::aktif();

        $nilai = Kktp::where('tahun_ajaran_id', $tahunAjaran?->id)
            ->get()
            ->mapWithKeys(fn (Kktp $k) => [$k->mata_pelajaran_id.'-'.$k->tingkat => $k->nilai])
            ->all();

        return view('admin.kktp.index', [
            'mapelList' => MataPelajaran::orderBy('nama')->get(),
            'tingkatList' => self::TINGKAT,
            'nilai' => $nilai,
            'tahunAjaran' => $tahunAjaran,
        ]);
    }

    public function simpan(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'kktp' => ['required', 'array'],
            'kktp.*' => ['nullable', 'integer', 'min:0', 'max:100'],
        ], [], ['kktp.*' => 'nilai KKTP']);

        $tahunAjaranId = TahunAjaran::aktif()?->id;

        foreach ($data['kktp'] as $kunci => $nilai) {
            if ($nilai === null || $nilai === '') {
                continue;
            }

            [$mapelId, $tingkat] = explode('-', $kunci);

            if (! in_array($tingkat, self::TINGKAT, true)) {
                continue;
            }

            Kktp::updateOrCreate([
                'tahun_ajaran_id' => $tahunAjaranId,
                'mata_pelajaran_id' => (int) $mapelId,
                'tingkat' => $tingkat,
            ], ['nilai' => (int) $nilai]);
        }

        return back()->with('sukses', 'KKTP berhasil disimpan.');
    }
}
