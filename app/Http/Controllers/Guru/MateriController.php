<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Models\Bab;
use App\Models\FolderMateri;
use App\Models\MataPelajaran;
use App\Models\Materi;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\StreamedResponse;

class MateriController extends Controller
{
    /** Maksimal 20 MB per berkas (§7.6). */
    protected const MAKS_KB = 20480;

    public function index(Request $request): View
    {
        return view('materi.index', [
            'folders' => FolderMateri::query()
                ->where('guru_id', $request->user()->id)
                ->whereNull('parent_id')
                ->with(['mataPelajaran', 'kelas'])
                ->withCount(['materis', 'anak'])
                ->orderBy('nama')
                ->get(),
            'mapelList' => MataPelajaran::orderBy('nama')->pluck('nama', 'id'),
            'kelasList' => $request->user()->kelasBolehDilihat(),
            'warnaList' => FolderMateri::WARNA,
        ]);
    }

    public function show(Request $request, FolderMateri $folder): View
    {
        $this->authorize('view', $folder);

        $folder->load(['mataPelajaran', 'kelas', 'induk']);

        return view('materi.folder', [
            'folder' => $folder,
            'subFolder' => $folder->anak()->withCount('materis')->orderBy('nama')->get(),
            'materis' => $folder->materis()->with('bab')->orderByDesc('created_at')->get(),
            'babList' => Bab::where('mata_pelajaran_id', $folder->mata_pelajaran_id)
                ->orderBy('urutan')->get(),
            'mapelList' => MataPelajaran::orderBy('nama')->pluck('nama', 'id'),
            'kelasList' => $request->user()->kelasBolehDilihat(),
            'warnaList' => FolderMateri::WARNA,
        ]);
    }

    public function simpanFolder(Request $request): RedirectResponse
    {
        $data = $this->validasiFolder($request);
        $kelasIds = $data['kelas'] ?? [];

        $folder = FolderMateri::create([
            ...collect($data)->except('kelas')->all(),
            'guru_id' => $request->user()->id,
        ]);

        $folder->kelas()->sync($kelasIds);

        return back()->with('sukses', 'Folder materi dibuat.');
    }

    public function ubahFolder(Request $request, FolderMateri $folder): RedirectResponse
    {
        $this->authorize('update', $folder);

        $data = $this->validasiFolder($request);
        $folder->update(collect($data)->except('kelas')->all());
        $folder->kelas()->sync($data['kelas'] ?? []);

        return back()->with('sukses', 'Folder materi diperbarui.');
    }

    public function hapusFolder(FolderMateri $folder): RedirectResponse
    {
        $this->authorize('delete', $folder);

        // Hapus berkas fisik agar tidak menyisakan sampah di storage.
        foreach ($folder->materis as $materi) {
            if ($materi->path) {
                Storage::disk('public')->delete($materi->path);
            }
        }

        $folder->delete();

        return redirect()->route('materi.index')->with('sukses', 'Folder beserta isinya dihapus.');
    }

    public function simpanMateri(Request $request, FolderMateri $folder): RedirectResponse
    {
        $this->authorize('update', $folder);

        $data = $request->validate([
            'judul' => ['required', 'string', 'max:255'],
            'deskripsi' => ['nullable', 'string', 'max:1000'],
            'tipe' => ['required', Rule::in(['file', 'tautan', 'video'])],
            'bab_id' => ['nullable', 'exists:babs,id'],
            'berkas' => [
                Rule::requiredIf(fn () => $request->input('tipe') === 'file'),
                'nullable', 'file',
                'mimes:pdf,doc,docx,ppt,pptx,xls,xlsx,jpg,jpeg,png,gif,webp,zip',
                'max:'.self::MAKS_KB,
            ],
            'url' => [
                Rule::requiredIf(fn () => in_array($request->input('tipe'), ['tautan', 'video'], true)),
                'nullable', 'url', 'max:500',
            ],
        ], [
            'berkas.max' => 'Ukuran berkas maksimal 20 MB.',
            'berkas.required' => 'Pilih berkas yang akan diunggah.',
            'url.required' => 'Tautan wajib diisi untuk tipe tautan/video.',
        ], [
            'judul' => 'judul materi',
            'bab_id' => 'bab',
        ]);

        $path = null;
        $ukuran = null;

        if ($request->hasFile('berkas')) {
            $berkas = $request->file('berkas');
            $path = $berkas->store('materi/'.$folder->id, 'public');
            $ukuran = $berkas->getSize();
        }

        $folder->materis()->create([
            'judul' => $data['judul'],
            'deskripsi' => $data['deskripsi'] ?? null,
            'tipe' => $data['tipe'],
            'bab_id' => $data['bab_id'] ?? null,
            'path' => $path,
            'url' => $data['url'] ?? null,
            'ukuran' => $ukuran,
        ]);

        return back()->with('sukses', 'Materi berhasil ditambahkan.');
    }

    public function unduh(Materi $materi): StreamedResponse|RedirectResponse
    {
        $materi->increment('jumlah_unduh');

        if ($materi->tipe !== 'file' || ! $materi->path) {
            return redirect()->away($materi->url);
        }

        abort_unless(Storage::disk('public')->exists($materi->path), 404, 'Berkas tidak ditemukan.');

        return Storage::disk('public')->download(
            $materi->path,
            $materi->judul.'.'.$materi->ekstensi
        );
    }

    public function destroy(Materi $materi): RedirectResponse
    {
        $this->authorize('update', $materi->folderMateri);

        if ($materi->path) {
            Storage::disk('public')->delete($materi->path);
        }

        $materi->delete();

        return back()->with('sukses', 'Materi dihapus.');
    }

    protected function validasiFolder(Request $request): array
    {
        return $request->validate([
            'mata_pelajaran_id' => ['required', 'exists:mata_pelajarans,id'],
            'parent_id' => ['nullable', 'exists:folder_materis,id'],
            'nama' => ['required', 'string', 'max:255'],
            'deskripsi' => ['nullable', 'string', 'max:1000'],
            'warna' => ['required', Rule::in(array_keys(FolderMateri::WARNA))],
            'kelas' => ['nullable', 'array'],
            'kelas.*' => ['exists:kelas,id'],
        ], [], [
            'mata_pelajaran_id' => 'mata pelajaran',
            'nama' => 'nama folder',
            'warna' => 'warna kartu',
        ]);
    }
}
