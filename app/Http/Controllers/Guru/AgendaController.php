<?php

namespace App\Http\Controllers\Guru;

use App\Enums\HariEnum;
use App\Enums\StatusAgenda;
use App\Exports\AgendaExport;
use App\Http\Controllers\Controller;
use App\Http\Requests\AgendaRequest;
use App\Models\Agenda;
use App\Models\Bab;
use App\Models\Jadwal;
use App\Models\Pengaturan;
use App\Support\SesiJadwal;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response;

class AgendaController extends Controller
{
    public function index(Request $request): View
    {
        return view('agenda.index', [
            'daftar' => $this->kueri($request)->paginate(20)->withQueryString(),
            'kelasList' => $request->user()->kelasBolehDilihat(),
            'filter' => $this->filter($request),
        ]);
    }

    /**
     * "Buat Agenda dari Jadwal" (§7.3): pilih tanggal → tampilkan jadwal hari itu →
     * guru centang yang terlaksana → agenda dibuat massal.
     */
    public function dariJadwal(Request $request): View
    {
        $tanggal = $request->date('tanggal') ?? today();
        $hari = HariEnum::dariTanggal($tanggal);

        $jadwals = $hari
            ? Jadwal::query()
                ->tahunAktif()
                ->where('guru_id', $request->user()->id)
                ->where('hari', $hari)
                ->with(['kelas', 'mataPelajaran'])
                ->orderBy('jam_ke')
                ->get()
            : collect();

        $sesiList = SesiJadwal::dariJadwalHarian($jadwals, $tanggal);

        // Tentukan sesi yang sedang aktif dipilih
        $sesiAktif = null;
        if ($request->filled('jadwal_id')) {
            $targetJadwalId = $request->integer('jadwal_id');
            $sesiAktif = $sesiList->first(fn ($s) => in_array($targetJadwalId, $s->schedule_ids, true));
        } elseif ($request->filled('sesi')) {
            $targetSesiId = $request->string('sesi')->toString();
            $sesiAktif = $sesiList->first(fn ($s) => $s->id === $targetSesiId || (string) $s->urutan === $targetSesiId);
        }

        if (! $sesiAktif && $sesiList->isNotEmpty()) {
            // 1. Cek sesi yang sedang berlangsung sekarang (jika tanggal hari ini)
            if ($tanggal->isToday()) {
                $sesiAktif = $sesiList->first(fn ($s) => $s->first_schedule->sedangBerlangsung());
            }

            // 2. Jika tidak ada yang sedang berlangsung, pilih sesi pertama yang belum diisi
            if (! $sesiAktif) {
                $sesiAktif = $sesiList->first(fn ($s) => ! $s->terisi);
            }

            // 3. Fallback ke sesi pertama
            if (! $sesiAktif) {
                $sesiAktif = $sesiList->first();
            }
        }

        // Konteks pembelajaran sesi aktif
        $pertemuanKe = 1;
        $agendaSebelumnya = null;
        $babsAktif = collect();

        if ($sesiAktif) {
            $pertemuanKe = Agenda::whereIn('jadwal_id', $sesiAktif->schedule_ids)
                ->whereDate('tanggal', '<=', $tanggal)
                ->where('tanggal', '!=', $tanggal->toDateString())
                ->count() + 1;

            $agendaSebelumnya = Agenda::whereIn('jadwal_id', $sesiAktif->schedule_ids)
                ->whereDate('tanggal', '<', $tanggal)
                ->with(['bab'])
                ->orderByDesc('tanggal')
                ->orderByDesc('id')
                ->first();

            $babsAktif = Bab::where('mata_pelajaran_id', $sesiAktif->mata_pelajaran_id)
                ->when($sesiAktif->kelas?->tingkat, fn ($q, $t) => $q->where('tingkat', $t))
                ->with('tujuanPembelajarans')
                ->orderBy('urutan')
                ->get();
        }

        return view('agenda.dari-jadwal', [
            'tanggal' => Carbon::parse($tanggal),
            'hari' => $hari,
            'jadwals' => $jadwals,
            'sesiList' => $sesiList,
            'sesiAktif' => $sesiAktif,
            'agendaSebelumnya' => $agendaSebelumnya,
            'pertemuanKe' => $pertemuanKe,
            'babs' => $babsAktif,
            'babList' => $this->babUntukSesi($sesiList),
            'statusList' => StatusAgenda::pilihan(),
        ]);
    }

    public function simpanMassal(Request $request): RedirectResponse
    {
        // ── 1. Alur Baru: Input Per Kelas Saja ────────────────────────
        if ($request->filled('jadwal_id') && ! $request->has('pilih')) {
            $data = $request->validate([
                'tanggal' => ['required', 'date'],
                'jadwal_id' => ['required', 'integer', 'exists:jadwals,id'],
                'judul_materi' => ['required', 'string', 'max:255'],
                'status' => ['required', 'string'],
                'bab_id' => ['nullable', 'exists:babs,id'],
                'uraian_kegiatan' => ['nullable', 'string', 'max:2000'],
                'metode' => ['nullable', 'string', 'max:255'],
                'lanjut_presensi' => ['nullable'],
            ], [
                'judul_materi.required' => 'Judul materi wajib diisi.',
                'status.required' => 'Status pertemuan wajib dipilih.',
            ]);

            $tanggal = Carbon::parse($data['tanggal']);
            $jadwal = Jadwal::findOrFail($data['jadwal_id']);
            $this->authorize('isiAgenda', $jadwal);

            $sesi = SesiJadwal::cariSesiUntukJadwal($jadwal);
            $scheduleIds = $sesi ? $sesi->schedule_ids : [$jadwal->id];
            $primaryId = $sesi ? $sesi->primary_schedule_id : $jadwal->id;
            $totalJp = $sesi ? $sesi->total_jp : 1;

            $pertemuanKe = Agenda::whereIn('jadwal_id', $scheduleIds)
                ->whereDate('tanggal', '<=', $tanggal)
                ->where('tanggal', '!=', $tanggal->toDateString())
                ->count() + 1;

            $agenda = Agenda::whereIn('jadwal_id', $scheduleIds)
                ->whereDate('tanggal', $tanggal->toDateString())
                ->first();

            $payload = [
                'jadwal_id' => $primaryId,
                'tanggal' => $tanggal->toDateString(),
                'pertemuan_ke' => $pertemuanKe,
                'judul_materi' => $data['judul_materi'],
                'uraian_kegiatan' => $data['uraian_kegiatan'] ?? null,
                'metode' => $data['metode'] ?? null,
                'bab_id' => $data['bab_id'] ?? null,
                'status' => $data['status'],
            ];

            if ($agenda) {
                $agenda->update($payload);

                $duplikatLain = Agenda::whereIn('jadwal_id', $scheduleIds)
                    ->whereDate('tanggal', $tanggal->toDateString())
                    ->where('id', '!=', $agenda->id)
                    ->get();

                foreach ($duplikatLain as $dup) {
                    $dup->presensis()->update(['agenda_id' => $agenda->id]);
                    $dup->delete();
                }
            } else {
                $agenda = Agenda::create($payload);
            }

            cache()->forget("peringatan.{$request->user()->id}");

            $namaKelas = $jadwal->kelas_tampilan;

            if ($request->boolean('lanjut_presensi') && $agenda->status->perluPresensi()) {
                return redirect()->route('presensi.isi', $agenda)
                    ->with('sukses', "Agenda kelas {$namaKelas} ({$totalJp} JP) berhasil disimpan. Silakan lanjutkan mengisi presensi siswa.");
            }

            return redirect()->route('agenda.dari-jadwal', [
                'tanggal' => $tanggal->toDateString(),
                'jadwal_id' => $primaryId,
            ])->with('sukses', "Agenda kelas {$namaKelas} ({$totalJp} JP) berhasil disimpan.");
        }

        // ── 2. Alur Lama: Kompatibilitas Penyimpanan Massal ───────────
        $data = $request->validate([
            'tanggal' => ['required', 'date'],
            'pilih' => ['required', 'array', 'min:1'],
            'pilih.*' => ['integer', 'exists:jadwals,id'],
            'agenda' => ['required', 'array'],
            'agenda.*.judul_materi' => ['required', 'string', 'max:255'],
            'agenda.*.status' => ['required', 'string'],
            'agenda.*.bab_id' => ['nullable', 'exists:babs,id'],
            'agenda.*.uraian_kegiatan' => ['nullable', 'string', 'max:2000'],
            'agenda.*.metode' => ['nullable', 'string', 'max:255'],
        ], [
            'pilih.required' => 'Pilih minimal satu sesi jadwal yang akan dibuatkan agenda.',
            'agenda.*.judul_materi.required' => 'Judul materi wajib diisi untuk jadwal yang dicentang.',
        ]);

        $tanggal = Carbon::parse($data['tanggal']);
        $terakhirDibuat = null;
        $jumlah = 0;
        $totalJpTersimpan = 0;

        DB::transaction(function () use ($data, $tanggal, $request, &$jumlah, &$totalJpTersimpan, &$terakhirDibuat) {
            foreach ($data['pilih'] as $jadwalId) {
                $jadwal = Jadwal::findOrFail($jadwalId);
                $this->authorize('isiAgenda', $jadwal);

                $isian = $data['agenda'][$jadwalId] ?? null;

                if (! $isian) {
                    continue;
                }

                // Cari sesi jadwal yang memuat jadwal ini
                $sesi = SesiJadwal::cariSesiUntukJadwal($jadwal);
                $scheduleIds = $sesi ? $sesi->schedule_ids : [$jadwal->id];
                $primaryId = $sesi ? $sesi->primary_schedule_id : $jadwal->id;
                $totalJpTersimpan += $sesi ? $sesi->total_jp : 1;

                // pertemuan_ke = urutan agenda pada jadwal/sesi tersebut
                $pertemuanKe = Agenda::whereIn('jadwal_id', $scheduleIds)
                    ->whereDate('tanggal', '<=', $tanggal)
                    ->where('tanggal', '!=', $tanggal->toDateString())
                    ->count() + 1;

                // Cari apakah agenda sudah ada pada salah satu jadwal di sesi ini
                $agenda = Agenda::whereIn('jadwal_id', $scheduleIds)
                    ->whereDate('tanggal', $tanggal->toDateString())
                    ->first();

                $payload = [
                    'jadwal_id' => $primaryId,
                    'tanggal' => $tanggal->toDateString(),
                    'pertemuan_ke' => $pertemuanKe,
                    'judul_materi' => $isian['judul_materi'],
                    'uraian_kegiatan' => $isian['uraian_kegiatan'] ?? null,
                    'metode' => $isian['metode'] ?? null,
                    'bab_id' => $isian['bab_id'] ?? null,
                    'status' => $isian['status'],
                ];

                if ($agenda) {
                    $agenda->update($payload);

                    // Bersihkan agenda duplikat lain dalam sesi yang sama jika ada sisa input per-JP lama
                    $duplikatLain = Agenda::whereIn('jadwal_id', $scheduleIds)
                        ->whereDate('tanggal', $tanggal->toDateString())
                        ->where('id', '!=', $agenda->id)
                        ->get();

                    foreach ($duplikatLain as $dup) {
                        $dup->presensis()->update(['agenda_id' => $agenda->id]);
                        $dup->delete();
                    }
                } else {
                    $agenda = Agenda::create($payload);
                }

                $terakhirDibuat = $agenda;
                $jumlah++;
            }
        });

        cache()->forget("peringatan.{$request->user()->id}");

        // Setelah agenda tersimpan → langsung ke pengisian presensi (§7.3).
        if ($jumlah === 1 && $terakhirDibuat && $terakhirDibuat->status->perluPresensi()) {
            return redirect()->route('presensi.isi', $terakhirDibuat)
                ->with('sukses', "Agenda ({$totalJpTersimpan} JP) tersimpan. Lanjutkan mengisi presensi.");
        }

        return redirect()->route('agenda.index')
            ->with('sukses', "{$jumlah} agenda ({$totalJpTersimpan} JP) berhasil disimpan.");
    }

    public function edit(Agenda $agenda): View
    {
        $this->authorize('update', $agenda);
        $agenda->load(['jadwal.kelas', 'jadwal.mataPelajaran']);

        return view('agenda.form', [
            'agenda' => $agenda,
            'babList' => Bab::where('mata_pelajaran_id', $agenda->jadwal?->mata_pelajaran_id)
                ->when($agenda->jadwal?->kelas?->tingkat, fn ($q, $tingkat) => $q->where('tingkat', $tingkat))
                ->orderBy('urutan')
                ->get(),
            'statusList' => StatusAgenda::pilihan(),
        ]);
    }

    public function update(AgendaRequest $request, Agenda $agenda): RedirectResponse
    {
        $agenda->update($request->validated());

        return redirect()->route('agenda.index')->with('sukses', 'Agenda berhasil diperbarui.');
    }

    public function destroy(Agenda $agenda): RedirectResponse
    {
        $this->authorize('delete', $agenda);
        $agenda->delete();

        return back()->with('sukses', 'Agenda dihapus beserta presensinya.');
    }

    public function bukaKunci(Agenda $agenda): RedirectResponse
    {
        $this->authorize('bukaKunci', $agenda);
        $agenda->update(['is_terkunci' => false]);

        activity('agenda')->performedOn($agenda)->log('Kunci agenda dibuka oleh admin');

        return back()->with('sukses', 'Kunci agenda dibuka.');
    }

    public function ekspor(Request $request): BinaryFileResponse
    {
        return Excel::download(
            new AgendaExport($this->kueri($request)->get()),
            'agenda-mengajar.xlsx'
        );
    }

    public function cetak(Request $request): Response
    {
        $pdf = Pdf::loadView('cetak.agenda', [
            'daftar' => $this->kueri($request)->get(),
            'guru' => $request->user(),
            'filter' => $this->filter($request),
            'pengaturan' => Pengaturan::semua(),
        ])->setPaper('a4', 'portrait');

        return $pdf->stream('agenda-mengajar.pdf');
    }

    protected function kueri(Request $request)
    {
        $filter = $this->filter($request);

        return Agenda::query()
            ->milikGuru($request->user()->id)
            ->tahunAktif()
            ->when($filter['dari'], fn ($q, $v) => $q->whereDate('tanggal', '>=', $v))
            ->when($filter['sampai'], fn ($q, $v) => $q->whereDate('tanggal', '<=', $v))
            ->when($filter['kelas_id'], fn ($q, $v) => $q->whereHas('jadwal', fn ($s) => $s->where('kelas_id', $v)))
            ->when($filter['cari'], fn ($q, $v) => $q->where(fn ($s) => $s
                ->where('judul_materi', 'like', "%{$v}%")
                ->orWhere('uraian_kegiatan', 'like', "%{$v}%")))
            ->with(['jadwal.kelas', 'jadwal.mataPelajaran', 'bab'])
            ->withCount('presensis')
            ->orderByDesc('tanggal')
            ->orderBy('jadwal_id');
    }

    protected function filter(Request $request): array
    {
        return [
            'dari' => $request->filled('dari') ? $request->date('dari') : null,
            'sampai' => $request->filled('sampai') ? $request->date('sampai') : null,
            'kelas_id' => $request->integer('kelas_id') ?: null,
            'cari' => $request->filled('cari') ? $request->string('cari')->toString() : null,
        ];
    }

    /** Saran bab per sesi untuk autocomplete judul materi. */
    protected function babUntukSesi($sesiList): array
    {
        $saran = [];

        foreach ($sesiList as $sesi) {
            $primaryId = $sesi->primary_schedule_id ?? ($sesi->id ?? null);
            if (! $primaryId) {
                continue;
            }

            $saran[$primaryId] = Bab::query()
                ->where('mata_pelajaran_id', $sesi->mata_pelajaran_id)
                ->when($sesi->kelas?->tingkat, fn ($q, $tingkat) => $q->where('tingkat', $tingkat))
                ->with('tujuanPembelajarans')
                ->orderBy('urutan')
                ->get();
        }

        return $saran;
    }

    /** Saran bab per jadwal untuk autocomplete judul materi. */
    protected function babUntukJadwal($jadwals): array
    {
        $saran = [];

        foreach ($jadwals as $jadwal) {
            $saran[$jadwal->id] = Bab::query()
                ->where('mata_pelajaran_id', $jadwal->mata_pelajaran_id)
                ->when($jadwal->kelas?->tingkat, fn ($q, $tingkat) => $q->where('tingkat', $tingkat))
                ->with('tujuanPembelajarans')
                ->orderBy('urutan')
                ->get();
        }

        return $saran;
    }
}

