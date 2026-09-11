<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Pengaturan;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PengaturanController extends Controller
{
    public function edit(): View
    {
        return view('admin.pengaturan.edit', ['pengaturan' => Pengaturan::semua()]);
    }

    public function update(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'nama_sekolah' => ['required', 'string', 'max:255'],
            'npsn' => ['nullable', 'string', 'max:20'],
            'alamat_sekolah' => ['nullable', 'string', 'max:500'],
            'kepala_sekolah' => ['nullable', 'string', 'max:255'],
            'nip_kepala_sekolah' => ['nullable', 'string', 'max:30'],
            'bobot_formatif' => ['required', 'numeric', 'min:0', 'max:100'],
            'bobot_sumatif_lingkup' => ['required', 'numeric', 'min:0', 'max:100'],
            'bobot_sumatif_akhir' => ['required', 'numeric', 'min:0', 'max:100'],
            'ambang_predikat_a' => ['required', 'numeric', 'min:0', 'max:100'],
            'ambang_predikat_b' => ['required', 'numeric', 'min:0', 'max:100'],
            'ambang_predikat_c' => ['required', 'numeric', 'min:0', 'max:100'],
            'ambang_alfa_peringatan' => ['required', 'integer', 'min:1', 'max:50'],
            'remedial_dibatasi_kktp' => ['nullable', 'boolean'],
            'logo' => ['nullable', 'image', 'max:1024'],
        ], [], [
            'nama_sekolah' => 'nama sekolah',
            'bobot_formatif' => 'bobot formatif',
            'bobot_sumatif_lingkup' => 'bobot sumatif lingkup',
            'bobot_sumatif_akhir' => 'bobot sumatif akhir',
            'ambang_alfa_peringatan' => 'ambang alfa peringatan',
        ]);

        $totalBobot = $data['bobot_formatif'] + $data['bobot_sumatif_lingkup'] + $data['bobot_sumatif_akhir'];

        if (abs($totalBobot - 100) > 0.01) {
            return back()
                ->withInput()
                ->withErrors(['bobot_formatif' => 'Total bobot harus tepat 100. Saat ini '.$totalBobot.'.']);
        }

        if ($request->hasFile('logo')) {
            $lama = Pengaturan::ambil('logo');

            if ($lama) {
                Storage::disk('public')->delete($lama);
            }

            Pengaturan::simpan('logo', $request->file('logo')->store('sekolah', 'public'));
        }

        $data['remedial_dibatasi_kktp'] = $request->boolean('remedial_dibatasi_kktp') ? '1' : '0';

        foreach (collect($data)->except('logo') as $key => $value) {
            Pengaturan::simpan($key, $value ?? '');
        }

        return back()->with('sukses', 'Pengaturan sekolah berhasil disimpan.');
    }
}
