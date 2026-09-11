<?php

namespace App\Http\Controllers;

use App\Enums\HariEnum;
use App\Models\Agenda;
use App\Models\Jadwal;
use App\Models\Materi;
use App\Services\PeringatanService;
use App\Services\RekapPresensiService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class BerandaController extends Controller
{
    public function index(
        Request $request,
        RekapPresensiService $rekapPresensi,
        PeringatanService $peringatanService,
    ): View {
        $guru = $request->user();
        $hariIni = HariEnum::dariTanggal(now());

        $jadwals = Jadwal::query()
            ->tahunAktif()
            ->where('guru_id', $guru->id)
            ->with(['kelas', 'mataPelajaran'])
            ->get();

        $jadwalHariIni = $hariIni
            ? $jadwals->where('hari', $hariIni)->sortBy('jam_ke')->values()
            : collect();

        // Agenda yang sudah dibuat hari ini, untuk menandai jadwal yang sudah diisi.
        $agendaHariIni = Agenda::query()
            ->whereIn('jadwal_id', $jadwalHariIni->pluck('id'))
            ->whereDate('tanggal', today())
            ->get()
            ->keyBy('jadwal_id');

        $idKelas = $jadwals->pluck('kelas_id')->filter()->unique();

        $statistik = [
            'kelas_diampu' => $idKelas->count(),
            'materi_tersedia' => Materi::whereHas(
                'folderMateri',
                fn ($q) => $q->where('guru_id', $guru->id)
            )->count(),
            'total_siswa' => $idKelas->isNotEmpty()
                ? DB::table('kelas_siswa')
                    ->whereIn('kelas_id', $idKelas)
                    ->distinct('siswa_id')
                    ->count('siswa_id')
                : 0,
            'jadwal_hari_ini' => $jadwalHariIni->count(),
        ];

        return view('beranda.index', [
            'guru' => $guru,
            'statistik' => $statistik,
            'jadwalHariIni' => $jadwalHariIni,
            'agendaHariIni' => $agendaHariIni,
            'rekapHariIni' => $rekapPresensi->rekapHariIni($guru->id),
            'peringatan' => Cache::remember(
                "peringatan.{$guru->id}",
                now()->addMinutes(5),
                fn () => $peringatanService->untuk($guru)
            ),
        ]);
    }
}
