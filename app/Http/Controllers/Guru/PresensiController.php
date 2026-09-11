<?php

namespace App\Http\Controllers\Guru;

use App\Enums\HariEnum;
use App\Enums\StatusAgenda;
use App\Enums\StatusPresensi;
use App\Exports\PresensiExport;
use App\Http\Controllers\Controller;
use App\Models\Agenda;
use App\Models\Jadwal;
use App\Models\Kelas;
use App\Models\MataPelajaran;
use App\Models\Pengaturan;
use App\Models\Presensi;
use App\Services\RekapPresensiService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response;

class PresensiController extends Controller
{
    public function __construct(protected RekapPresensiService $rekap) {}

    /** Daftar pertemuan & input presensi langsung ala absensi-siswa. */
    public function index(Request $request): View
    {
        $guruId = $request->user()->id;
        $tanggal = $request->filled('tanggal') ? Carbon::parse($request->input('tanggal')) : today();
        $hari = HariEnum::dariTanggal($tanggal);

        // Ambil jadwal guru untuk hari yang dipilih
        $jadwalsHariIni = $hari
            ? Jadwal::query()
                ->tahunAktif()
                ->where('guru_id', $guruId)
                ->where('hari', $hari)
                ->with(['kelas', 'mataPelajaran'])
                ->orderBy('jam_ke')
                ->get()
            : collect();

        // Kelompokkan JP berurutan dengan kelas dan mapel sama menjadi sesi
        $sesiMapel = $this->kelompokkanSesiMapel($jadwalsHariIni, $tanggal);

        // Tentukan sesi atau jadwal yang sedang aktif
        $sesiAktifId = $request->input('sesi', $sesiMapel->first()->id ?? null);
        $sesiAktif = $sesiMapel->firstWhere('id', $sesiAktifId) ?? $sesiMapel->first();

        // Jika ada request jadwal_id spesifik
        if ($request->filled('jadwal_id') && ! $request->filled('sesi')) {
            $targetJadwalId = (int) $request->input('jadwal_id');
            $foundSesi = $sesiMapel->first(fn ($s) => in_array($targetJadwalId, $s->schedule_ids, true));
            if ($foundSesi) {
                $sesiAktif = $foundSesi;
                $sesiAktifId = $foundSesi->id;
            } elseif ($jadwalLain = Jadwal::with(['kelas', 'mataPelajaran'])->find($targetJadwalId)) {
                $sesiAktif = $this->formatSesiObject(collect([$jadwalLain]), 1, collect());
                $sesiAktifId = $sesiAktif->id;
                if (! $sesiMapel->contains(fn ($s) => in_array($targetJadwalId, $s->schedule_ids, true))) {
                    $sesiMapel->push($sesiAktif);
                }
            }
        }

        // Tentukan kelas aktif dan jadwal aktif
        $kelasAktif = null;
        $jadwalAktif = null;
        $agendaAktif = null;

        if ($sesiAktif) {
            $kelasAktif = $sesiAktif->kelas;
            $jadwalAktif = $sesiAktif->schedules->first();
            $agendaAktif = $sesiAktif->agenda;
        } elseif ($request->filled('kelas_id')) {
            $kelasAktif = Kelas::find($request->input('kelas_id'));
            $jadwalAktif = Jadwal::query()
                ->tahunAktif()
                ->where('guru_id', $guruId)
                ->where('kelas_id', $kelasAktif?->id)
                ->first();
            if ($jadwalAktif) {
                $agendaAktif = Agenda::query()
                    ->where('jadwal_id', $jadwalAktif->id)
                    ->whereDate('tanggal', $tanggal)
                    ->first();
            }
        }

        // Ambil daftar siswa aktif kelas terpilih
        $siswas = $kelasAktif
            ? $kelasAktif->siswas()->orderBy('kelas_siswa.no_absen')->get()
            : collect();

        // Ambil presensi tersimpan (jika ada)
        $presensiTersimpan = $agendaAktif
            ? $agendaAktif->presensis()->get()->keyBy('siswa_id')
            : collect();

        // Hitung statistik sesi aktif
        $totalSiswa = $siswas->count();
        $jumlahHadir = $presensiTersimpan->whereIn('status', [StatusPresensi::Hadir, StatusPresensi::Dispensasi])->count();
        $jumlahSakit = $presensiTersimpan->where('status', StatusPresensi::Sakit)->count();
        $jumlahIzin = $presensiTersimpan->where('status', StatusPresensi::Izin)->count();
        $jumlahAlfa = $presensiTersimpan->whereIn('status', [StatusPresensi::Alfa, StatusPresensi::Bolos])->count();
        $jumlahDispensasi = $presensiTersimpan->where('status', StatusPresensi::Dispensasi)->count();

        // Riwayat agenda/presensi terdahulu (Tab 2)
        $daftar = Agenda::query()
            ->milikGuru($guruId)
            ->tahunAktif()
            ->pertemuanEfektif()
            ->when($request->integer('filter_kelas_id'), fn ($q, $id) => $q
                ->whereHas('jadwal', fn ($s) => $s->where('kelas_id', $id)))
            ->when($request->filled('dari'), fn ($q) => $q->whereDate('tanggal', '>=', $request->date('dari')))
            ->when($request->filled('sampai'), fn ($q) => $q->whereDate('tanggal', '<=', $request->date('sampai')))
            ->with(['jadwal.kelas', 'jadwal.mataPelajaran'])
            ->withCount([
                'presensis',
                'presensis as jumlah_hadir' => fn ($q) => $q->whereIn('status', ['hadir', 'dispensasi']),
                'presensis as jumlah_alfa' => fn ($q) => $q->whereIn('status', ['alfa', 'bolos']),
            ])
            ->orderByDesc('tanggal')
            ->paginate(20)
            ->withQueryString();

        return view('presensi.index', [
            'tanggal' => $tanggal,
            'hari' => $hari,
            'sesiMapel' => $sesiMapel,
            'sesiAktif' => $sesiAktif,
            'sesiAktifId' => $sesiAktifId,
            'kelasAktif' => $kelasAktif,
            'jadwalAktif' => $jadwalAktif,
            'agendaAktif' => $agendaAktif,
            'siswas' => $siswas,
            'presensiTersimpan' => $presensiTersimpan,
            'statusList' => StatusPresensi::cases(),
            'kelasList' => $request->user()->kelasBolehDilihat(),
            'totalSiswa' => $totalSiswa,
            'jumlahHadir' => $jumlahHadir,
            'jumlahSakit' => $jumlahSakit,
            'jumlahIzin' => $jumlahIzin,
            'jumlahAlfa' => $jumlahAlfa,
            'jumlahDispensasi' => $jumlahDispensasi,
            'daftar' => $daftar,
            'tab' => $request->input('tab', 'input'),
        ]);
    }

    /** Simpan presensi langsung dari antarmuka ala absensi-siswa (auto-create agenda jika belum ada). */
    public function simpanLangsung(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'tanggal' => ['required', 'date'],
            'jadwal_id' => ['required', 'exists:jadwals,id'],
            'sesi_id' => ['nullable', 'string'],
            'judul_materi' => ['nullable', 'string', 'max:255'],
            'status' => ['required', 'array'],
            'status.*' => ['required', 'string', 'in:'.implode(',', array_keys(StatusPresensi::pilihan()))],
            'keterangan' => ['nullable', 'array'],
            'keterangan.*' => ['nullable', 'string', 'max:255'],
        ], [
            'status.required' => 'Belum ada status presensi yang dipilih.',
            'jadwal_id.required' => 'Jadwal pelajaran belum dipilih.',
        ]);

        $jadwal = Jadwal::with('kelas')->findOrFail($data['jadwal_id']);

        abort_unless(
            $jadwal->guru_id === $request->user()->id || $request->user()->isAdmin(),
            403,
            'Anda tidak memiliki izin untuk menginput presensi pada jadwal ini.'
        );

        $tanggal = Carbon::parse($data['tanggal'])->toDateString();

        // Cari atau buat Agenda otomatis
        $agenda = Agenda::where('jadwal_id', $jadwal->id)
            ->whereDate('tanggal', $tanggal)
            ->first();

        if (! $agenda) {
            $pertemuanKe = Agenda::where('jadwal_id', $jadwal->id)
                ->whereDate('tanggal', '<=', $tanggal)
                ->count() + 1;

            $judulMateri = trim((string) ($data['judul_materi'] ?? '')) ?: 'Pembelajaran Tatap Muka';

            $agenda = Agenda::create([
                'jadwal_id' => $jadwal->id,
                'tanggal' => $tanggal,
                'pertemuan_ke' => $pertemuanKe,
                'judul_materi' => $judulMateri,
                'status' => StatusAgenda::Terlaksana,
                'is_terkunci' => false,
            ]);
        } elseif (! empty($data['judul_materi'])) {
            $agenda->update([
                'judul_materi' => trim((string) $data['judul_materi']),
            ]);
        }

        $siswas = $jadwal->kelas ? $jadwal->kelas->siswas()->get()->keyBy('id') : collect();
        $jumlahSiswa = 0;

        DB::transaction(function () use ($data, $agenda, $siswas, &$jumlahSiswa) {
            foreach ($data['status'] as $siswaId => $status) {
                if (! $siswas->has($siswaId)) {
                    continue;
                }

                $keterangan = trim((string) ($data['keterangan'][$siswaId] ?? '')) ?: null;

                Presensi::updateOrCreate(
                    ['agenda_id' => $agenda->id, 'siswa_id' => $siswaId],
                    [
                        'status' => $status,
                        'keterangan' => $keterangan,
                    ]
                );
                $jumlahSiswa++;
            }
        });

        cache()->forget("peringatan.{$request->user()->id}");

        $namaKelas = $jadwal->kelas_tampilan;
        $params = [
            'tanggal' => $tanggal,
            'jadwal_id' => $jadwal->id,
        ];
        if (! empty($data['sesi_id'])) {
            $params['sesi'] = $data['sesi_id'];
        }

        return redirect()->route('presensi.index', $params)
            ->with('sukses', "Presensi {$namaKelas} tanggal {$tanggal} berhasil disimpan ({$jumlahSiswa} siswa).");
    }

    /** Halaman input presensi satu pertemuan. */
    public function isi(Request $request, Agenda $agenda): View
    {
        $this->authorize('view', $agenda);
        $agenda->load(['jadwal.kelas', 'jadwal.mataPelajaran']);

        $siswas = $agenda->jadwal?->kelas ? $agenda->jadwal->kelas->siswas()->get() : collect();
        $presensiTersimpan = $agenda->presensis()->get()->keyBy('siswa_id');

        return view('presensi.isi', [
            'agenda' => $agenda,
            'siswas' => $siswas,
            'presensi' => $presensiTersimpan,
            'statusList' => StatusPresensi::cases(),
            'bisaSunting' => $request->user()->can('update', $agenda),
        ]);
    }

    public function simpan(Request $request, Agenda $agenda): RedirectResponse
    {
        $this->authorize('update', $agenda);

        $data = $request->validate([
            'status' => ['required', 'array'],
            'status.*' => ['required', 'string', 'in:'.implode(',', array_keys(StatusPresensi::pilihan()))],
            'keterangan' => ['nullable', 'array'],
            'keterangan.*' => ['nullable', 'string', 'max:255'],
        ], [
            'status.required' => 'Belum ada status presensi yang dipilih.',
        ]);

        $siswas = $agenda->jadwal?->kelas ? $agenda->jadwal->kelas->siswas()->get()->keyBy('id') : collect();

        // Keterangan bersifat opsional agar tidak memblokir input cepat guru
        DB::transaction(function () use ($data, $agenda, $siswas) {
            foreach ($data['status'] as $siswaId => $status) {
                if (! $siswas->has($siswaId)) {
                    continue;   // siswa bukan anggota kelas ini
                }

                $keterangan = trim((string) ($data['keterangan'][$siswaId] ?? '')) ?: null;

                Presensi::updateOrCreate(
                    ['agenda_id' => $agenda->id, 'siswa_id' => $siswaId],
                    [
                        'status' => $status,
                        'keterangan' => $keterangan,
                    ]
                );
            }
        });

        cache()->forget("peringatan.{$request->user()->id}");

        $namaKelas = $agenda->jadwal?->kelas?->nama ?? $agenda->jadwal?->kelas_tampilan ?? 'Kelas';

        return redirect()->route('presensi.index')
            ->with('sukses', 'Presensi '.$namaKelas.' berhasil disimpan.');
    }

    /** Mengelompokkan daftar JP berurutan dengan mata pelajaran & kelas sama menjadi satu Sesi */
    private function kelompokkanSesiMapel(Collection $jadwals, Carbon $tanggal): Collection
    {
        $sesiList = collect();
        $currentBlock = collect();

        $agendaTersimpan = Agenda::query()
            ->whereIn('jadwal_id', $jadwals->pluck('id'))
            ->whereDate('tanggal', $tanggal)
            ->with('presensis')
            ->get()
            ->keyBy('jadwal_id');

        foreach ($jadwals as $j) {
            if ($currentBlock->isEmpty()) {
                $currentBlock->push($j);
                continue;
            }

            $last = $currentBlock->last();
            if ($last->kelas_id === $j->kelas_id
                && $last->mata_pelajaran_id === $j->mata_pelajaran_id
                && (int) $j->jam_ke === (int) $last->jam_ke + 1) {
                $currentBlock->push($j);
            } else {
                $sesiList->push($this->formatSesiObject($currentBlock, $sesiList->count() + 1, $agendaTersimpan));
                $currentBlock = collect([$j]);
            }
        }

        if ($currentBlock->isNotEmpty()) {
            $sesiList->push($this->formatSesiObject($currentBlock, $sesiList->count() + 1, $agendaTersimpan));
        }

        return $sesiList;
    }

    private function formatSesiObject(Collection $schedules, int $urutan, Collection $agendaTersimpan): object
    {
        $first = $schedules->first();
        $last = $schedules->last();
        $jamKeList = $schedules->pluck('jam_ke')->all();
        $totalJp = count($jamKeList);
        $scheduleIds = $schedules->pluck('id')->all();

        $labelJp = $totalJp > 1
            ? sprintf('JP %d–%d (%d JP)', min($jamKeList), max($jamKeList), $totalJp)
            : sprintf('JP %d (1 JP)', $first->jam_ke);

        $jamMulai = $first->jam_mulai ? substr((string) $first->jam_mulai, 0, 5) : null;
        $jamSelesai = $last->jam_selesai ? substr((string) $last->jam_selesai, 0, 5) : null;
        $rentangWaktu = ($jamMulai && $jamSelesai) ? "{$jamMulai}–{$jamSelesai}" : null;

        $agenda = null;
        foreach ($scheduleIds as $sid) {
            if ($agendaTersimpan->has($sid)) {
                $agenda = $agendaTersimpan->get($sid);
                break;
            }
        }

        $presensisCount = $agenda ? $agenda->presensis->count() : 0;
        $jumlahHadir = $agenda ? $agenda->presensis->whereIn('status', [StatusPresensi::Hadir, StatusPresensi::Dispensasi])->count() : 0;
        $terisi = $presensisCount > 0;

        return (object) [
            'id' => 'sesi_' . $urutan,
            'urutan' => $urutan,
            'kelas' => $first->kelas,
            'kelas_id' => $first->kelas_id,
            'kelas_tampilan' => $first->kelas_tampilan,
            'mata_pelajaran' => $first->mataPelajaran,
            'nama_tampilan' => $first->nama_tampilan,
            'schedules' => $schedules,
            'schedule_ids' => $scheduleIds,
            'first_schedule_id' => $first->id,
            'jam_ke_list' => $jamKeList,
            'label_jp' => $labelJp,
            'total_jp' => $totalJp,
            'rentang_waktu' => $rentangWaktu,
            'agenda' => $agenda,
            'terisi' => $terisi,
            'presensis_count' => $presensisCount,
            'jumlah_hadir' => $jumlahHadir,
        ];
    }

    public function rekap(Request $request): View
    {
        $konteks = $this->konteksRekap($request);

        return view('presensi.rekap', $konteks);
    }

    public function ekspor(Request $request): BinaryFileResponse
    {
        $konteks = $this->konteksRekap($request);

        abort_unless($konteks['kelas'], 404, 'Pilih kelas terlebih dahulu.');

        return Excel::download(
            new PresensiExport($konteks['kelas'], $konteks['rekap'], $konteks['siswas']),
            'rekap-presensi-'.str($konteks['kelas']->nama)->slug().'.xlsx'
        );
    }

    /** Daftar hadir bulanan: baris = siswa, kolom = tanggal pertemuan (F4). */
    public function cetak(Request $request): Response
    {
        $konteks = $this->konteksRekap($request);

        abort_unless($konteks['kelas'] && $konteks['mataPelajaranId'], 404, 'Pilih kelas dan mata pelajaran.');

        $matriks = $this->rekap->matriksBulanan(
            $konteks['kelas'],
            $konteks['mataPelajaranId'],
            $konteks['dari'],
            $konteks['sampai'],
        );

        $pdf = Pdf::loadView('cetak.daftar-hadir', [
            'kelas' => $konteks['kelas'],
            'mataPelajaran' => MataPelajaran::find($konteks['mataPelajaranId']),
            'matriks' => $matriks,
            'dari' => $konteks['dari'],
            'sampai' => $konteks['sampai'],
            'guru' => $request->user(),
            'pengaturan' => Pengaturan::semua(),
        ])->setPaper([0, 0, 609.45, 935.43], 'landscape');

        return $pdf->stream('daftar-hadir.pdf');
    }

    protected function konteksRekap(Request $request): array
    {
        $kelasList = $request->user()->kelasBolehDilihat();
        $kelas = $kelasList->firstWhere('id', $request->integer('kelas_id')) ?? $kelasList->first();

        $mataPelajaranId = $request->integer('mata_pelajaran_id') ?: null;
        $dari = $request->filled('dari')
            ? Carbon::parse($request->date('dari'))
            : now()->startOfMonth();
        $sampai = $request->filled('sampai')
            ? Carbon::parse($request->date('sampai'))
            : now()->endOfMonth();

        $rekap = $kelas
            ? $this->rekap->rekapKelas($kelas, $mataPelajaranId, $dari, $sampai)
            : collect();

        return [
            'kelasList' => $kelasList,
            'kelas' => $kelas,
            'siswas' => $kelas ? $kelas->siswas()->get()->keyBy('id') : collect(),
            'mapelList' => MataPelajaran::orderBy('nama')->get(),
            'mataPelajaranId' => $mataPelajaranId,
            'dari' => $dari,
            'sampai' => $sampai,
            'rekap' => $rekap,
            'ambangAlfa' => (int) Pengaturan::ambil('ambang_alfa_peringatan'),
        ];
    }
}
