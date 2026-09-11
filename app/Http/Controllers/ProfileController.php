<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;

class ProfileController extends Controller
{
    public function edit(): View
    {
        return view('profil.edit');
    }

    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $pengguna = $request->user();
        $data = $request->safe()->except('foto');

        if ($request->hasFile('foto')) {
            if ($pengguna->foto) {
                Storage::disk('public')->delete($pengguna->foto);
            }

            $data['foto'] = $request->file('foto')->store('profil', 'public');
        }

        $pengguna->fill($data);

        if ($pengguna->isDirty('email')) {
            $pengguna->email_verified_at = null;
        }

        $pengguna->save();

        return back()->with('sukses', 'Profil berhasil diperbarui.');
    }
}
