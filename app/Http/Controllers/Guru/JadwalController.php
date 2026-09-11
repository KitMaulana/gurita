<?php

namespace App\Http\Controllers\Guru;

use App\Enums\HariEnum;
use App\Http\Controllers\Controller;
use App\Models\Jadwal;
use App\Models\Pengaturan;
use App\Models\TahunAjaran;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Symfony\Component\HttpFoundation\Response;

class JadwalController extends Controller
{
    public function index(Request $request): View
    {
        $tahunAjaran = $this->tahunAjaranTerpilih($request);
        $guru = $this->guruTerpilih($request);

        return view('jadwal.index', [
            'jadwalPerHari' => $this->jadwalPerHari($tahunAjaran->id, $guru->id),
            'tahunAjaran' => $tahunAjaran,
            'tahunAjarans' => TahunAjaran::orderByDesc('nama')->get(),
            'guru' => $guru,
            'guruList' => $request->user()->isAdmin()
                ? User::aktif()->orderBy('name')->get()
                : collect(),
        ]);
    }

    /** Cetak PDF F4 landscape (§7.2). */
    public function cetak(Request $request): Response
    {
        $tahunAjaran = $this->tahunAjaranTerpilih($request);
        $guru = $this->guruTerpilih($request);

        $pdf = Pdf::loadView('cetak.jadwal', [
            'jadwalPerHari' => $this->jadwalPerHari($tahunAjaran->id, $guru->id),
            'tahunAjaran' => $tahunAjaran,
            'guru' => $guru,
            'pengaturan' => Pengaturan::semua(),
        ])->setPaper([0, 0, 609.45, 935.43], 'landscape');   // F4 / Folio

        return $pdf->stream('jadwal-mengajar-'.str($guru->name)->slug().'.pdf');
    }

    /** @return Collection<string, Collection<int, Jadwal>> */
    protected function jadwalPerHari(int $tahunAjaranId, int $guruId): Collection
    {
        $jadwals = Jadwal::query()
            ->where('tahun_ajaran_id', $tahunAjaranId)
            ->where('guru_id', $guruId)
            ->with(['kelas', 'mataPelajaran'])
            ->orderBy('jam_ke')
            ->get();

        return collect(HariEnum::cases())
            ->mapWithKeys(fn (HariEnum $hari) => [
                $hari->value => $jadwals->where('hari', $hari)->values(),
            ]);
    }

    protected function tahunAjaranTerpilih(Request $request): TahunAjaran
    {
        return TahunAjaran::find($request->integer('tahun_ajaran_id')) ?? TahunAjaran::aktif();
    }

    /** Admin boleh melihat jadwal guru lain; guru hanya jadwalnya sendiri (§10). */
    protected function guruTerpilih(Request $request): User
    {
        $pengguna = $request->user();

        if (! $pengguna->isAdmin() || ! $request->filled('guru_id')) {
            return $pengguna;
        }

        return User::findOrFail($request->integer('guru_id'));
    }
}
