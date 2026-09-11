<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Kelas;
use App\Models\Siswa;
use App\Models\TahunAjaran;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class KelasController extends Controller
{
    public function index(Request $request): View
    {
        $tahunAjaranId = $request->integer('tahun_ajaran_id') ?: TahunAjaran::aktif()?->id;

        return view('admin.kelas.index', [
            'daftar' => Kelas::query()
                ->where('tahun_ajaran_id', $tahunAjaranId)
                ->with(['waliKelas', 'tahunAjaran'])
                ->withCount('siswas')
                ->orderBy('nama')
                ->paginate(20)
                ->withQueryString(),
            'tahunAjarans' => TahunAjaran::orderByDesc('nama')->get(),
            'tahunAjaranId' => $tahunAjaranId,
        ]);
    }

    public function create(): View
    {
        return view('admin.kelas.form', [
            'kelas' => new Kelas(['tahun_ajaran_id' => TahunAjaran::aktif()?->id]),
            'tahunAjarans' => TahunAjaran::orderByDesc('nama')->get(),
            'guruList' => User::aktif()->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $kelas = Kelas::create($this->validasi($request));

        // Hubungkan jadwal tertunda yang nama_kelas_jadwal-nya cocok
        \App\Models\Jadwal::where('tahun_ajaran_id', $kelas->tahun_ajaran_id)
            ->whereNull('kelas_id')
            ->where(function ($q) use ($kelas) {
                $q->where('nama_kelas_jadwal', $kelas->nama)
                  ->orWhereRaw('LOWER(nama_kelas_jadwal) = ?', [mb_strtolower($kelas->nama)]);
            })
            ->update(['kelas_id' => $kelas->id]);

        return redirect()->route('admin.kelas.index')->with('sukses', 'Kelas berhasil ditambahkan.');
    }

    public function show(Kelas $kelas): View
    {
        $kelas->load(['tahunAjaran', 'waliKelas']);

        $siswaTerdaftar = $kelas->siswas()->get();

        return view('admin.kelas.show', [
            'kelas' => $kelas,
            'siswas' => $siswaTerdaftar,
            // Siswa aktif yang belum masuk kelas mana pun pada tahun ajaran ini.
            'siswaTersedia' => Siswa::aktif()
                ->whereDoesntHave('kelas', fn ($q) => $q->where('kelas.tahun_ajaran_id', $kelas->tahun_ajaran_id))
                ->orderBy('nama')
                ->get(),
            'nomorBerikutnya' => ((int) $kelas->siswas()->max('no_absen')) + 1,
        ]);
    }

    public function edit(Kelas $kelas): View
    {
        return view('admin.kelas.form', [
            'kelas' => $kelas,
            'tahunAjarans' => TahunAjaran::orderByDesc('nama')->get(),
            'guruList' => User::aktif()->orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, Kelas $kelas): RedirectResponse
    {
        $kelas->update($this->validasi($request, $kelas->id));

        return redirect()->route('admin.kelas.index')->with('sukses', 'Kelas berhasil diperbarui.');
    }

    public function destroy(Kelas $kelas): RedirectResponse
    {
        if ($kelas->jadwals()->exists()) {
            return back()->with('galat', 'Kelas masih memiliki jadwal. Hapus jadwalnya lebih dahulu.');
        }

        $kelas->delete();   // soft delete

        return back()->with('sukses', 'Kelas dihapus.');
    }

    public function tambahSiswa(Request $request, Kelas $kelas): RedirectResponse
    {
        $data = $request->validate([
            'siswa_id' => ['required', 'exists:siswas,id'],
            'no_absen' => [
                'required', 'integer', 'min:1', 'max:255',
                Rule::unique('kelas_siswa', 'no_absen')->where(fn ($q) => $q->where('kelas_id', $kelas->id)),
            ],
        ], [
            'no_absen.unique' => 'Nomor absen tersebut sudah dipakai di kelas ini.',
        ], [
            'siswa_id' => 'siswa',
            'no_absen' => 'nomor absen',
        ]);

        $kelas->siswas()->syncWithoutDetaching([
            $data['siswa_id'] => ['no_absen' => $data['no_absen']],
        ]);

        return back()->with('sukses', 'Siswa ditambahkan ke kelas.');
    }

    public function keluarkanSiswa(Kelas $kelas, Siswa $siswa): RedirectResponse
    {
        $kelas->siswas()->detach($siswa->id);

        return back()->with('sukses', 'Siswa dikeluarkan dari kelas.');
    }

    protected function validasi(Request $request, ?int $id = null): array
    {
        return $request->validate([
            'tahun_ajaran_id' => ['required', 'exists:tahun_ajarans,id'],
            'nama' => [
                'required', 'string', 'max:50',
                Rule::unique('kelas', 'nama')
                    ->where(fn ($q) => $q->where('tahun_ajaran_id', $request->input('tahun_ajaran_id'))
                        ->whereNull('deleted_at'))
                    ->ignore($id),
            ],
            'tingkat' => ['required', Rule::in(['X', 'XI', 'XII'])],
            'jurusan' => ['nullable', Rule::in(['IPA', 'IPS'])],
            'wali_kelas_id' => ['nullable', 'exists:users,id'],
        ], [
            'nama.unique' => 'Nama kelas tersebut sudah ada pada tahun ajaran ini.',
        ], [
            'tahun_ajaran_id' => 'tahun ajaran',
            'nama' => 'nama kelas',
            'wali_kelas_id' => 'wali kelas',
        ]);
    }
}
