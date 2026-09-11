<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Spatie\Permission\Models\Role;

class PenggunaController extends Controller
{
    public function index(Request $request): View
    {
        return view('admin.pengguna.index', [
            'daftar' => User::query()
                ->when($request->filled('cari'), function ($q) use ($request) {
                    $cari = $request->string('cari');
                    $q->where(fn ($sub) => $sub->where('name', 'like', "%{$cari}%")
                        ->orWhere('email', 'like', "%{$cari}%")
                        ->orWhere('nip', 'like', "%{$cari}%"));
                })
                ->with('roles')
                ->orderBy('name')
                ->paginate(20)
                ->withQueryString(),
        ]);
    }

    public function create(): View
    {
        return view('admin.pengguna.form', [
            'pengguna' => new User(['is_aktif' => true]),
            'peranList' => Role::orderBy('name')->pluck('name', 'name'),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validasi($request);

        $pengguna = User::create([
            ...collect($data)->except(['peran', 'password'])->all(),
            'password' => Hash::make($data['password']),
        ]);

        $pengguna->syncRoles($data['peran']);

        return redirect()->route('admin.pengguna.index')->with('sukses', 'Pengguna berhasil ditambahkan.');
    }

    public function edit(User $pengguna): View
    {
        return view('admin.pengguna.form', [
            'pengguna' => $pengguna,
            'peranList' => Role::orderBy('name')->pluck('name', 'name'),
        ]);
    }

    public function update(Request $request, User $pengguna): RedirectResponse
    {
        $data = $this->validasi($request, $pengguna->id);

        $pengguna->update(collect($data)->except(['peran', 'password'])->all());

        if (filled($data['password'] ?? null)) {
            $pengguna->update(['password' => Hash::make($data['password'])]);
        }

        $pengguna->syncRoles($data['peran']);

        return redirect()->route('admin.pengguna.index')->with('sukses', 'Pengguna berhasil diperbarui.');
    }

    public function destroy(Request $request, User $pengguna): RedirectResponse
    {
        if ($pengguna->id === $request->user()->id) {
            return back()->with('galat', 'Anda tidak dapat menghapus akun sendiri.');
        }

        if ($pengguna->jadwals()->exists() || $pengguna->penilaians()->exists()) {
            $pengguna->update(['is_aktif' => false]);

            return back()->with('peringatan', 'Pengguna masih terkait data mengajar, jadi dinonaktifkan (tidak dihapus).');
        }

        $pengguna->delete();

        return back()->with('sukses', 'Pengguna dihapus.');
    }

    protected function validasi(Request $request, ?int $id = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'nip' => ['nullable', 'string', 'max:30', Rule::unique('users', 'nip')->ignore($id)],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($id)],
            'password' => [$id ? 'nullable' : 'required', 'confirmed', Password::min(8)],
            'jabatan' => ['nullable', 'string', 'max:255'],
            'quote' => ['nullable', 'string', 'max:500'],
            'is_aktif' => ['nullable', 'boolean'],
            'peran' => ['required', 'array', 'min:1'],
            'peran.*' => ['string', 'exists:roles,name'],
        ], [
            'nip.unique' => 'NIP tersebut sudah dipakai pengguna lain.',
            'email.unique' => 'Email tersebut sudah terdaftar.',
            'peran.required' => 'Pilih minimal satu peran.',
        ], [
            'name' => 'nama',
            'password' => 'kata sandi',
            'quote' => 'kutipan motivasi',
        ]);
    }
}
