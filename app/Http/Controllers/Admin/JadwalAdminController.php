<?php

namespace App\Http\Controllers\Admin;

use App\Enums\HariEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\JadwalBulkRequest;
use App\Http\Requests\JadwalRequest;
use App\Imports\JadwalImport;
use App\Models\Agenda;
use App\Models\Bab;
use App\Models\FolderMateri;
use App\Models\Jadwal;
use App\Models\Kelas;
use App\Models\Kktp;
use App\Models\MataPelajaran;
use App\Models\Materi;
use App\Models\Nilai;
use App\Models\Penilaian;
use App\Models\Presensi;
use App\Models\RaporMapel;
use App\Models\TahunAjaran;
use App\Models\TujuanPembelajaran;
use App\Models\User;
use App\Support\JamPelajaran;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\StreamedResponse;

class JadwalAdminController extends Controller
{
    public function index(Request $request): View
    {
        $tahunAjaranId = TahunAjaran::aktif()?->id;

        $perPage = $request->get('per_page', 30);
        $perPageCount = in_array($perPage, ['50', '100', 'semua'])
            ? ($perPage === 'semua' ? 999999 : (int) $perPage)
            : 30;

        $daftar = Jadwal::query()
            ->where('tahun_ajaran_id', $tahunAjaranId)
            ->when($request->integer('kelas_id'), fn ($q, $id) => $q->where('kelas_id', $id))
            ->when($request->integer('guru_id'), fn ($q, $id) => $q->where('guru_id', $id))
            ->when($request->filled('hari'), fn ($q) => $q->where('hari', $request->string('hari')))
            ->with(['kelas', 'mataPelajaran', 'guru'])
            ->orderByRaw("FIELD(hari, 'senin', 'selasa', 'rabu', 'kamis', 'jumat', 'sabtu')")
            ->orderBy('jam_ke')
            ->paginate($perPageCount)
            ->withQueryString();

        return view('admin.jadwal.index', [
            'daftar' => $daftar,
            'kelasList' => Kelas::where('tahun_ajaran_id', $tahunAjaranId)->orderBy('nama')->get(),
            'guruList' => User::aktif()->orderBy('name')->get(),
            'hariList' => HariEnum::pilihan(),
            'modeAktif' => JamPelajaran::modeAktif(),
            'daftarMode' => JamPelajaran::daftarMode(),
            'slotWaktuMode' => JamPelajaran::getAllSlotsJson(),
        ]);
    }

    public function create(): View
    {
        return view('admin.jadwal.form', $this->dataForm(new Jadwal));
    }

    public function store(JadwalRequest $request): RedirectResponse
    {
        Jadwal::create($request->validated());

        return redirect()->route('admin.jadwal.index')->with('sukses', 'Jadwal berhasil ditambahkan.');
    }

    /**
     * Form Input Massal (Bulk Input) untuk jadwal pelajaran.
     */
    public function createBulk(): View
    {
        return view('admin.jadwal.bulk', $this->dataForm(new Jadwal));
    }

    /**
     * Simpan jadwal pelajaran secara massal (Multi-JP).
     */
    public function storeBulk(JadwalBulkRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $isGlobal = ! empty($validated['title']);
        $hari = $validated['hari'];
        $tersimpan = 0;

        DB::transaction(function () use ($validated, $isGlobal, $hari, &$tersimpan) {
            foreach ($validated['jam_ke'] as $jp) {
                $slot = JamPelajaran::getSlotTime((int) $jp, $hari);

                Jadwal::create([
                    'tahun_ajaran_id' => $validated['tahun_ajaran_id'],
                    'kelas_id' => $isGlobal ? null : ($validated['kelas_id'] ?? null),
                    'mata_pelajaran_id' => $isGlobal ? null : $validated['mata_pelajaran_id'],
                    'guru_id' => $isGlobal ? null : $validated['guru_id'],
                    'title' => $isGlobal ? $validated['title'] : null,
                    'hari' => $hari,
                    'jam_ke' => (int) $jp,
                    'jam_mulai' => $slot['start'] ?? '07:00',
                    'jam_selesai' => $slot['end'] ?? '07:45',
                    'ruang' => $validated['ruang'] ?? null,
                ]);

                $tersimpan++;
            }
        });

        return redirect()->route('admin.jadwal.index')
            ->with('sukses', "{$tersimpan} jadwal pelajaran berhasil ditambahkan secara massal.");
    }

    public function edit(Jadwal $jadwal): View
    {
        return view('admin.jadwal.form', $this->dataForm($jadwal));
    }

    public function update(JadwalRequest $request, Jadwal $jadwal): RedirectResponse
    {
        $jadwal->update($request->validated());

        return redirect()->route('admin.jadwal.index')->with('sukses', 'Jadwal berhasil diperbarui.');
    }

    public function destroy(Jadwal $jadwal): RedirectResponse
    {
        if ($jadwal->agendas()->exists()) {
            return back()->with('galat', 'Jadwal sudah memiliki agenda mengajar dan tidak dapat dihapus.');
        }

        $jadwal->delete();

        return back()->with('sukses', 'Jadwal dihapus.');
    }

    /**
     * Hapus banyak jadwal terpilih sekaligus (Bulk Delete).
     */
    public function bulkDestroy(Request $request): RedirectResponse
    {
        $ids = $request->input('ids', []);

        if (empty($ids) || ! is_array($ids)) {
            return back()->with('galat', 'Tidak ada jadwal yang dipilih untuk dihapus.');
        }

        $jadwals = Jadwal::whereIn('id', $ids)->get();
        $dihapus = 0;
        $dilewati = 0;

        foreach ($jadwals as $jadwal) {
            if ($jadwal->agendas()->exists()) {
                $dilewati++;
                continue;
            }
            $jadwal->delete();
            $dihapus++;
        }

        $pesan = "{$dihapus} jadwal berhasil dihapus.";
        if ($dilewati > 0) {
            $pesan .= " ({$dilewati} jadwal dilewati karena sudah memiliki riwayat agenda).";
        }

        return redirect()->route('admin.jadwal.index')->with('sukses', $pesan);
    }

    /**
     * Reset total seluruh jadwal pelajaran dan seluruh data yang terintegrasi
     * (Kelas, Mata Pelajaran, KKTP, Agenda, Presensi, Penilaian, Nilai, Rapor, dan akun Guru non-admin)
     * untuk memberikan clean slate sebelum admin mengunggah ulang data keseluruhan.
     */
    public function reset(Request $request): RedirectResponse
    {
        @set_time_limit(180);
        @ini_set('max_execution_time', '180');

        $tahunAjaranId = TahunAjaran::aktif()?->id;

        DB::transaction(function () use ($tahunAjaranId, $request) {
            // 1. Bersihkan nilai & penilaian
            Nilai::whereHas('penilaian', function ($q) use ($tahunAjaranId) {
                if ($tahunAjaranId) {
                    $q->where('tahun_ajaran_id', $tahunAjaranId);
                }
            })->delete();

            $queryPenilaian = Penilaian::query();
            if ($tahunAjaranId) {
                $queryPenilaian->where('tahun_ajaran_id', $tahunAjaranId);
            }
            $queryPenilaian->forceDelete();

            // 2. Bersihkan rapor mapel
            $queryRapor = RaporMapel::query();
            if ($tahunAjaranId) {
                $queryRapor->where('tahun_ajaran_id', $tahunAjaranId);
            }
            $queryRapor->delete();

            // 3. Bersihkan presensi & agenda mengajar yang terikat pada jadwal
            Presensi::whereHas('agenda.jadwal', function ($q) use ($tahunAjaranId) {
                if ($tahunAjaranId) {
                    $q->where('tahun_ajaran_id', $tahunAjaranId);
                }
            })->delete();

            Agenda::whereHas('jadwal', function ($q) use ($tahunAjaranId) {
                if ($tahunAjaranId) {
                    $q->where('tahun_ajaran_id', $tahunAjaranId);
                }
            })->delete();

            // 4. Hapus seluruh jadwal secara permanen pada tahun ajaran aktif
            $queryJadwal = Jadwal::query();
            if ($tahunAjaranId) {
                $queryJadwal->where('tahun_ajaran_id', $tahunAjaranId);
            }
            $queryJadwal->forceDelete();

            if (function_exists('activity')) {
                activity()
                    ->causedBy($request->user())
                    ->useLog('jadwal')
                    ->log('Mereset seluruh jadwal pelajaran pada tahun ajaran aktif.');
            }
        });

        return redirect()->route('admin.jadwal.index')
            ->with('sukses', 'Seluruh jadwal pelajaran berhasil direset. Data kelas dan siswa tetap aman.');
    }

    /**
     * Unduh template CSV impor jadwal format resmi (kompatibel dengan SIDACHEERS).
     */
    public function template(): StreamedResponse
    {
        $baris = [
            ['hari', 'jam_ke', 'nama_kelas', 'nama_guru', 'mata_pelajaran'],
            ['Senin', '1', 'XII IPA 1', 'Siti Rahmawati', 'Bahasa Indonesia'],
            ['Senin', '2', 'XII IPA 1', 'Siti Rahmawati', 'Bahasa Indonesia'],
            ['Selasa', '1', 'XII IPA 2', 'Siti Rahmawati', 'Bahasa Indonesia'],
            ['Rabu', '3', 'XII IPA 1', 'Administrator Portal', 'Matematika'],
        ];

        return response()->streamDownload(function () use ($baris) {
            $keluaran = fopen('php://output', 'w');
            fwrite($keluaran, "\xEF\xBB\xBF");
            foreach ($baris as $item) {
                fputcsv($keluaran, $item);
            }
            fclose($keluaran);
        }, 'Template_Jadwal.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    public function impor(Request $request): RedirectResponse
    {
        @set_time_limit(300);
        @ini_set('max_execution_time', '300');

        $request->validate([
            'berkas' => ['required', 'file', 'mimes:csv,txt,xlsx,xls', 'max:5120'],
        ], [], ['berkas' => 'berkas impor']);

        $import = new JadwalImport;
        Excel::import($import, $request->file('berkas'));

        $redirect = back();

        if (! empty($import->guruBaru)) {
            $redirect->with('guruBaruCreated', $import->guruBaru);
        }
        if (! empty($import->kelasBaru)) {
            $redirect->with('kelasBaruCreated', $import->kelasBaru);
        }
        if (! empty($import->mapelBaru)) {
            $redirect->with('mapelBaruCreated', $import->mapelBaru);
        }
        if (! empty($import->peringatan)) {
            $redirect->with('peringatanImpor', $import->peringatan);
        }

        if ($import->galat) {
            return $redirect
                ->with('peringatan', 'Impor selesai sebagian: '.$import->ringkasan())
                ->with('galatImpor', $import->galat);
        }

        return $redirect->with('sukses', 'Impor berhasil: '.$import->ringkasan());
    }

    protected function dataForm(Jadwal $jadwal): array
    {
        $tahunAjaranId = TahunAjaran::aktif()?->id;

        return [
            'jadwal' => $jadwal,
            'kelasList' => Kelas::where('tahun_ajaran_id', $tahunAjaranId)->orderBy('nama')->pluck('nama', 'id'),
            'mapelList' => MataPelajaran::orderBy('nama')->pluck('nama', 'id'),
            'guruList' => User::aktif()->orderBy('name')->pluck('name', 'id'),
            'hariList' => HariEnum::pilihan(),
            'dailySlots' => JamPelajaran::getAllSlotsJson(),
            'rekomendasiAgenda' => JamPelajaran::rekomendasiAgendaBersama(),
        ];
    }

    /**
     * Mengubah mode jadwal aktif (mis. Reguler, Bulan Ramadhan, Ujian/Khusus).
     */
    public function ubahMode(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'mode' => ['required', 'string', 'in:reguler,ramadhan,khusus'],
        ]);

        $mode = $validated['mode'];
        \App\Models\Pengaturan::simpan('mode_jadwal', $mode);

        $daftarMode = JamPelajaran::daftarMode();
        $label = $daftarMode[$mode]['label'] ?? $mode;

        return back()->with(
            'sukses',
            "Mode jadwal berhasil diubah ke '{$label}'. Seluruh jadwal di akun guru, beranda sekolah, dan dokumen otomatis mengikuti jam mode ini tanpa mengubah agenda & presensi historis."
        );
    }

    /**
     * Menyimpan kustomisasi slot waktu untuk mode tertentu.
     */
    public function simpanSlotMode(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'mode' => ['required', 'string', 'in:reguler,ramadhan,khusus'],
            'hari' => ['required', 'string', 'in:senin,selasa,rabu,kamis,jumat,sabtu'],
            'slots' => ['required', 'array'],
        ]);

        $mode = $validated['mode'];
        $hari = $validated['hari'];
        $slots = [];

        foreach ($validated['slots'] as $num => $s) {
            $jp = (int) $num;
            $start = trim($s['start'] ?? '');
            $end = trim($s['end'] ?? '');
            if ($start && $end) {
                $slots[$jp] = [
                    'number' => $jp,
                    'start' => $start,
                    'end' => $end,
                    'label' => "JP {$jp} ({$start} – {$end})",
                ];
            }
        }

        \App\Models\Pengaturan::simpan("slot_jam_{$mode}_{$hari}", json_encode($slots));

        return back()->with('sukses', "Kustomisasi jam pelajaran untuk hari {$hari} (Mode {$mode}) berhasil disimpan.");
    }

    /**
     * Mengembalikan slot waktu mode ke pengaturan bawaan sistem.
     */
    public function resetSlotMode(Request $request): RedirectResponse
    {
        $mode = $request->string('mode')->toString();
        $daftarHari = ['senin', 'selasa', 'rabu', 'kamis', 'jumat', 'sabtu'];

        foreach ($daftarHari as $hari) {
            \App\Models\Pengaturan::where('key', "slot_jam_{$mode}_{$hari}")->delete();
        }
        \App\Models\Pengaturan::lupakanCache();

        return back()->with('sukses', "Slot waktu jam pelajaran mode {$mode} berhasil dikembalikan ke standar bawaan.");
    }
}
