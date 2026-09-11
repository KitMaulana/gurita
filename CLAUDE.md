# CLAUDE.md — Portal Admin Guru

> File pengetahuan proyek untuk Claude Code (VS Code). Baca file ini **sebelum** menulis kode apa pun.
> Semua penamaan UI, label, pesan, dan komentar penting ditulis dalam **Bahasa Indonesia**.

---

## 1. Tentang Proyek

**Portal Admin Guru** adalah aplikasi web administrasi mengajar untuk guru di **SMA Negeri 1 Ciruas**. Aplikasi ini menggantikan tumpukan administrasi manual (jadwal cetak, agenda mengajar buku tulis, daftar hadir kertas, daftar nilai Excel) menjadi satu portal terintegrasi yang bisa diisi harian dan direkap otomatis menjadi laporan siap cetak.

Prototipe awal dibuat sebagai halaman statis di Canva (HTML + Tailwind + Canva Data SDK) dengan 7 menu: Beranda, Jadwal Mengajar, Agenda Mengajar, Presensi Siswa, Daftar Nilai, Bank Materi, Rekap Rapor. Prototipe itu **hanya referensi tampilan dan alur**, bukan referensi arsitektur data.

### Masalah pada prototipe yang WAJIB diperbaiki di versi Laravel

| Prototipe Canva | Versi Laravel |
|---|---|
| Data hilang saat refresh (array JavaScript) | Persisten di MySQL dengan migrasi |
| Presensi hanya angka agregat (hadir: 30, sakit: 2) | Presensi **per siswa per pertemuan**, agregat dihitung otomatis |
| Nilai bebas tanpa struktur penilaian | Nilai terikat ke **jenis penilaian** (formatif/sumatif) + bobot + KKTP |
| Kelas di-hardcode `XI-1` … `XI-6` | Kelas, mapel, siswa jadi **master data** |
| Agenda & jadwal tidak terhubung | Agenda **turunan dari jadwal**, tanggal & kelas terisi otomatis |
| Bank Materi hanya kartu kosong | Upload file & tautan, terhubung ke bab/tujuan pembelajaran |
| Rapor angka statis | Rekap rapor dihitung dari nilai + presensi + deskripsi capaian otomatis |
| Satu pengguna, tanpa login | Multi-pengguna dengan peran & hak akses |

### Pengguna & peran

- **Guru mapel** — pemakai utama. Mengisi agenda, presensi, nilai, materi untuk kelas yang diampunya saja.
- **Wali kelas** — semua hak guru + melihat rekap lengkap kelas perwaliannya (semua mapel).
- **Admin / Waka Kurikulum** — kelola master data (tahun ajaran, kelas, siswa, mapel, jadwal, pengguna), lihat rekap seluruh sekolah, kelola pengaturan.

### Konteks kurikulum

Sekolah menerapkan **Kurikulum Permendikdasmen 13/2025** di semua tingkat (X, XI, XII). Konsekuensi untuk aplikasi:

- Penilaian memakai istilah **formatif** dan **sumatif** (bukan UH/UTS/UAS), dengan **sumatif lingkup materi** dan **sumatif akhir semester**.
- Ketuntasan memakai **KKTP** (Kriteria Ketercapaian Tujuan Pembelajaran), bukan KKM tunggal.
- Materi disusun sebagai **Capaian Pembelajaran (CP) → Bab/Lingkup Materi → Tujuan Pembelajaran (TP)**.
- Deskripsi rapor dibuat naratif berbasis TP yang sudah/belum tercapai.

---

## 2. Aturan Kerja untuk AI Agent

1. **Kerjakan bertahap sesuai roadmap di §12.** Jangan melompat fase. Selesaikan satu fase, laporkan, minta konfirmasi, baru lanjut.
2. **Jangan mengubah skema database** yang sudah dipakai tanpa membuat migrasi baru. Dilarang mengedit file migrasi yang sudah dijalankan di production/lokal — buat migrasi tambahan.
3. **Semua kolom uang/nilai numerik** pakai tipe eksplisit (`decimal`, `unsignedTinyInteger`), jangan `string`.
4. **Setiap fitur baru wajib punya**: migrasi → model + relasi → seeder (jika master data) → policy/gate → form request (validasi) → controller/Livewire → view → route → uji manual singkat.
5. **Jangan pakai raw query** kalau Eloquent cukup. Hindari N+1 — selalu `with()` relasi yang dipakai di view.
6. **Otorisasi wajib.** Guru hanya boleh menyentuh data kelas/jadwal miliknya. Semua controller memanggil `authorize()` atau lewat Policy.
7. **Bahasa Indonesia** untuk label UI, pesan validasi, pesan flash, dan nama menu. Nama tabel/kolom/kelas PHP tetap konvensi Laravel (bahasa Indonesia boleh untuk domain, mis. `siswa`, `kelas`, `nilai`).
8. **Jangan menambah dependensi baru** di luar daftar §3 tanpa menjelaskan alasannya lebih dulu.
9. **Commit kecil dan deskriptif** (`feat: modul presensi per siswa`, `fix: perhitungan nilai akhir`).
10. Kalau ada ambiguitas aturan bisnis, **tanyakan** — jangan menebak lalu membangun setengah jalan.

---

## 3. Tech Stack

| Komponen | Pilihan | Catatan |
|---|---|---|
| Framework | **Laravel 12** | struktur direktori Laravel 11+ (tanpa `app/Http/Kernel.php`) |
| PHP | **8.2+** | bawaan XAMPP terbaru |
| Database | **MySQL / MariaDB** (XAMPP) | charset `utf8mb4`, collation `utf8mb4_unicode_ci` |
| Auth | **Laravel Breeze (Blade)** | login pakai email atau NIP |
| Frontend | **Blade + Tailwind CSS 3 + Alpine.js**, bundling **Vite** | ikuti design token §11 |
| Interaktif | **Livewire 3** | khusus tabel nilai (edit inline), filter presensi/agenda, pencarian |
| Ikon | **Heroicons** (`blade-ui-kit/blade-heroicons`) | prototipe sudah pakai Heroicons outline |
| Hak akses | **spatie/laravel-permission** | peran: `admin`, `guru`, `wali_kelas` |
| Excel | **maatwebsite/excel** | import & export |
| PDF | **barryvdh/laravel-dompdf** | cetak A4 & F4 |
| Log aktivitas | **spatie/laravel-activitylog** | audit perubahan nilai & presensi |

### Lingkungan lokal

Proyek berjalan di **Windows + XAMPP**. Folder proyek: `C:\xampp\htdocs\portal-guru`.

```bash
# instalasi awal
composer create-project laravel/laravel portal-guru
cd portal-guru
composer require livewire/livewire spatie/laravel-permission spatie/laravel-activitylog maatwebsite/excel barryvdh/laravel-dompdf blade-ui-kit/blade-heroicons
composer require laravel/breeze --dev && php artisan breeze:install blade
npm install && npm run dev

# harian
php artisan serve
npm run dev
php artisan migrate:fresh --seed   # HANYA di lokal, hapus semua data
php artisan migrate                # produksi
php artisan optimize:clear
```

`.env` penting:

```
APP_NAME="Portal Admin Guru"
APP_LOCALE=id
APP_TIMEZONE=Asia/Jakarta
DB_DATABASE=portal_guru
FILESYSTEM_DISK=public   # jalankan: php artisan storage:link
```

---

## 4. Struktur Folder Aplikasi

```
app/
├── Console/Commands/          # mis. GenerateAgendaHarian
├── Enums/                     # StatusPresensi, JenisPenilaian, HariEnum, StatusAgenda
├── Exports/                   # NilaiExport, PresensiExport, AgendaExport, RaporExport
├── Http/
│   ├── Controllers/
│   │   ├── Admin/             # MasterData: Kelas, Siswa, Mapel, Jadwal, Pengguna, TahunAjaran
│   │   ├── Guru/              # Jadwal, Agenda, Presensi, Nilai, Materi, Rapor
│   │   └── DashboardController.php
│   ├── Requests/              # satu FormRequest per aksi store/update
│   └── Middleware/            # PastikanTahunAjaranAktif
├── Imports/                   # SiswaImport, JadwalImport, NilaiImport
├── Livewire/                  # TabelNilai, FilterPresensi, FilterAgenda, PresensiHarian
├── Models/
├── Policies/
├── Services/                  # PerhitunganNilaiService, RekapPresensiService, DeskripsiRaporService
└── Support/                   # helper kecil (format tanggal Indonesia, terbilang)

resources/views/
├── layouts/app.blade.php      # sidebar + topbar (lihat §11)
├── components/                # kartu-statistik, badge-status, tabel-kosong, modal
├── dashboard/
├── jadwal/  agenda/  presensi/  nilai/  materi/  rapor/
├── admin/
└── cetak/                     # template khusus PDF (A4 & F4)
```

---

## 5. Skema Database

Semua tabel memakai `id` bigint auto increment dan `timestamps`. Tambahkan `softDeletes` pada `siswas`, `kelas`, `jadwals`, `penilaians`.

### 5.1 Pengguna & referensi akademik

**users**
`id`, `name`, `nip` (nullable, unique), `email` (unique), `password`, `jabatan` (string, mis. "Guru Bahasa Indonesia & Kepala Perpustakaan"), `quote` (text, nullable — kutipan motivasi di beranda), `foto` (nullable), `is_aktif` (bool, default true), `remember_token`

**tahun_ajarans**
`id`, `nama` (mis. `2026/2027`), `semester` (enum: `ganjil`,`genap`), `tanggal_mulai`, `tanggal_selesai`, `is_aktif` (bool)
→ Hanya **satu** baris boleh `is_aktif = true`. Enforce di model observer.

**mata_pelajarans**
`id`, `nama`, `singkatan`, `kelompok` (enum: `umum`,`pilihan`,`muatan_lokal`)

**kelas**
`id`, `tahun_ajaran_id`, `nama` (mis. `XII IPA 1`), `tingkat` (enum: `X`,`XI`,`XII`), `jurusan` (enum nullable: `IPA`,`IPS`), `wali_kelas_id` (FK users, nullable)

**siswas**
`id`, `nisn` (unique), `nis` (nullable), `nama`, `jenis_kelamin` (enum `L`,`P`), `is_aktif` (bool)

**kelas_siswa** (pivot)
`id`, `kelas_id`, `siswa_id`, `no_absen` (unsigned tinyint)
→ unique(`kelas_id`,`siswa_id`) dan unique(`kelas_id`,`no_absen`)

### 5.2 Jadwal & agenda

**jadwals**
`id`, `tahun_ajaran_id`, `kelas_id`, `mata_pelajaran_id`, `guru_id` (FK users), `hari` (enum `senin`..`sabtu`), `jam_ke` (unsigned tinyint), `jam_mulai` (time), `jam_selesai` (time), `ruang` (nullable)
→ Aturan bentrok: satu guru tidak boleh punya dua jadwal pada `hari`+`jam_ke` yang sama, **kecuali** mapel PJOK (boleh mengajar >1 kelas dalam 1 JP). Satu kelas tidak boleh punya dua jadwal pada `hari`+`jam_ke` yang sama. Validasi di `JadwalRequest`.

**agendas** (satu baris = satu pertemuan nyata)
`id`, `jadwal_id`, `tanggal` (date), `pertemuan_ke` (unsigned smallint), `judul_materi`, `uraian_kegiatan` (text, nullable), `metode` (nullable), `bab_id` (nullable), `catatan` (text, nullable), `status` (enum: `terlaksana`,`tugas_mandiri`,`kosong`,`libur`,`kegiatan_sekolah`), `is_terkunci` (bool, default false)
→ unique(`jadwal_id`,`tanggal`). `is_terkunci` mencegah edit setelah direkap.

### 5.3 Presensi

**presensis**
`id`, `agenda_id`, `siswa_id`, `status` (enum: `hadir`,`sakit`,`izin`,`alfa`,`bolos`,`dispensasi`), `keterangan` (nullable)
→ unique(`agenda_id`,`siswa_id`). Agregat **tidak disimpan** — dihitung dengan query agregasi + cache.

### 5.4 Materi & penilaian

**babs** (lingkup materi)
`id`, `mata_pelajaran_id`, `tingkat`, `kode` (mis. `BAB 1`), `judul`, `capaian_pembelajaran` (text, nullable), `urutan`

**tujuan_pembelajarans** (menggantikan "Sub BAB" di prototipe)
`id`, `bab_id`, `kode` (mis. `TP 1.1`), `deskripsi` (text), `urutan`

**penilaians** (definisi satu asesmen)
`id`, `tahun_ajaran_id`, `kelas_id`, `mata_pelajaran_id`, `guru_id`, `bab_id` (nullable), `tujuan_pembelajaran_id` (nullable), `jenis` (enum: `formatif`,`sumatif_lingkup`,`sumatif_akhir`), `nama` (mis. "Menulis Teks Editorial"), `tanggal`, `bobot` (decimal 5,2 — default mengikuti pengaturan), `nilai_maksimal` (default 100), `is_terkunci` (bool)

**nilais**
`id`, `penilaian_id`, `siswa_id`, `nilai` (decimal 5,2, nullable — null = belum dinilai), `catatan` (nullable)
→ unique(`penilaian_id`,`siswa_id`)

**kktps**
`id`, `tahun_ajaran_id`, `mata_pelajaran_id`, `tingkat`, `nilai` (unsigned tinyint, default 75)

**folder_materis**
`id`, `mata_pelajaran_id`, `parent_id` (nullable, self-reference), `nama`, `deskripsi` (nullable), `warna` (string, kelas gradient Tailwind), `guru_id`

**materis**
`id`, `folder_materi_id`, `bab_id` (nullable), `judul`, `deskripsi` (nullable), `tipe` (enum: `file`,`tautan`,`video`), `path` (nullable), `url` (nullable), `ukuran` (nullable), `jumlah_unduh` (default 0)

**folder_materi_kelas** (pivot): `folder_materi_id`, `kelas_id`

### 5.5 Rapor & pengaturan

**rapor_mapels** (hasil rekap yang dibekukan per akhir semester)
`id`, `tahun_ajaran_id`, `kelas_id`, `mata_pelajaran_id`, `siswa_id`, `nilai_formatif`, `nilai_sumatif_lingkup`, `nilai_sumatif_akhir`, `nilai_akhir` (decimal 5,2), `predikat` (char 1), `is_tuntas` (bool), `deskripsi_capaian` (text), `jumlah_hadir`, `jumlah_sakit`, `jumlah_izin`, `jumlah_alfa`, `dibekukan_pada` (timestamp nullable)

**pengaturans**
`id`, `key` (unique), `value` (text)
→ Isi awal: `nama_sekolah`, `npsn`, `alamat_sekolah`, `kepala_sekolah`, `nip_kepala_sekolah`, `logo`, `bobot_formatif` (20), `bobot_sumatif_lingkup` (40), `bobot_sumatif_akhir` (40), `ambang_predikat_a` (90), `ambang_predikat_b` (80), `ambang_predikat_c` (70).

---

## 6. Relasi Eloquent (ringkas)

```php
User        → hasMany(Jadwal, 'guru_id'), hasMany(Penilaian), hasMany(Kelas,'wali_kelas_id')
TahunAjaran → hasMany(Kelas), hasMany(Jadwal), hasMany(Penilaian)
Kelas       → belongsTo(TahunAjaran), belongsTo(User,'wali_kelas_id'),
              belongsToMany(Siswa)->withPivot('no_absen'), hasMany(Jadwal)
Siswa       → belongsToMany(Kelas), hasMany(Nilai), hasMany(Presensi)
Jadwal      → belongsTo(Kelas), belongsTo(MataPelajaran), belongsTo(User,'guru_id'), hasMany(Agenda)
Agenda      → belongsTo(Jadwal), belongsTo(Bab), hasMany(Presensi)
Presensi    → belongsTo(Agenda), belongsTo(Siswa)
Bab         → belongsTo(MataPelajaran), hasMany(TujuanPembelajaran), hasMany(Penilaian)
Penilaian   → belongsTo(Kelas), belongsTo(MataPelajaran), belongsTo(Bab), hasMany(Nilai)
Nilai       → belongsTo(Penilaian), belongsTo(Siswa)
FolderMateri→ belongsTo(MataPelajaran), hasMany(Materi), belongsToMany(Kelas), hasMany(FolderMateri,'parent_id')
```

Scope wajib pada model yang punya `tahun_ajaran_id`:

```php
public function scopeTahunAktif($query) {
    return $query->where('tahun_ajaran_id', TahunAjaran::aktif()->id);
}
```

---

## 7. Modul & Halaman

### 7.1 Beranda / Dashboard
Kartu sambutan (nama, jabatan, kutipan motivasi dari profil pengguna) + 4 kartu statistik:
- Kelas Diampu (distinct kelas di jadwal guru, tahun aktif)
- Materi Tersedia (jumlah materi milik guru)
- Total Siswa (siswa unik di kelas yang diampu)
- Jadwal Hari Ini (jumlah jadwal `hari` = hari ini)

Di bawahnya:
- **Jadwal hari ini** dengan penanda "sedang berlangsung" berdasarkan jam server, dan tombol cepat **"Isi Agenda & Presensi"**.
- **Rekap presensi hari ini** per kelas (hadir/sakit/izin/alfa) — dihitung dari tabel `presensis`, bukan hardcode.
- **Peringatan**: agenda yang belum diisi >2 hari, penilaian yang nilainya belum lengkap.

### 7.2 Jadwal Mengajar
- Tampilan dikelompokkan per hari (Senin–Sabtu), seperti prototipe.
- Guru: hanya **melihat** jadwalnya + filter tahun ajaran + tombol cetak PDF (F4 landscape).
- Admin: CRUD jadwal + **import Excel/CSV** + cek bentrok otomatis dengan pesan jelas ("Bentrok: Bu Ani sudah mengajar XII IPA 3 pada Senin jam ke-3").
- Hapus tombol "Reset Semua" dari prototipe — ganti dengan hapus per baris + konfirmasi.

### 7.3 Agenda Mengajar
- Tombol **"Buat Agenda dari Jadwal"**: pilih tanggal → sistem menampilkan seluruh jadwal guru pada hari tersebut → guru centang yang terlaksana → agenda dibuat massal, `pertemuan_ke` diisi otomatis (urutan agenda pada `jadwal_id` tersebut).
- Form agenda: judul materi (autocomplete dari `babs`/`tujuan_pembelajarans`), uraian kegiatan, metode, status, catatan.
- Setelah agenda disimpan → langsung diarahkan ke **pengisian presensi** pertemuan itu.
- Filter: rentang tanggal, kelas, kata kunci materi. Export Excel & PDF.

### 7.4 Presensi Siswa
- Halaman input: daftar siswa satu kelas (urut `no_absen`), tombol status per siswa (H/S/I/A/B/D).
- Tombol **"Tandai Semua Hadir"** lalu tinggal ubah yang tidak hadir — ini alur tercepat.
- Wajib: siswa yang statusnya bukan `hadir` diminta `keterangan`.
- Rekap: per pertemuan, per kelas, per siswa, per rentang tanggal. Persentase kehadiran ditampilkan; siswa dengan alfa ≥ ambang tertentu ditandai merah.
- Export Excel & PDF (format daftar hadir bulanan: baris = siswa, kolom = tanggal pertemuan).

### 7.5 Daftar Nilai
- Alur: **buat Penilaian** (jenis, nama, bab/TP, tanggal, bobot) → **isi nilai** dalam grid Livewire (baris = siswa, kolom = penilaian) → simpan otomatis per sel dengan indikator tersimpan.
- Nilai di bawah KKTP diberi latar merah (kelas `nilai-merah` dari prototipe).
- Kolom kalkulasi: rata formatif, rata sumatif lingkup, sumatif akhir, **Nilai Akhir**, predikat, status tuntas.
- Fitur **remedial**: nilai perbaikan disimpan sebagai penilaian terpisah bertanda `is_remedial`; nilai akhir memakai nilai tertinggi, dibatasi maksimal KKTP (aturan bisa diubah di pengaturan).
- Import nilai dari Excel per penilaian; export daftar nilai per kelas.

### 7.6 Bank Materi
- Folder per mapel (bisa bersarang), kartu berwarna seperti prototipe, terhubung ke satu/beberapa kelas.
- Isi folder: unggah file (pdf/docx/pptx/gambar, maks 20 MB) atau tautan (Google Drive/YouTube).
- Pratinjau ikon per tipe file, tombol unduh, penghitung unduhan.
- Materi bisa ditautkan ke `bab` sehingga muncul sebagai saran saat mengisi agenda.

### 7.7 Rekap Rapor
- Pilih kelas + mapel + tahun ajaran → tabel: nama siswa, nilai formatif, sumatif lingkup, sumatif akhir, nilai akhir, predikat, status, rekap absensi.
- Tombol **"Bekukan Rapor"** → menyimpan hasil ke `rapor_mapels` + generate `deskripsi_capaian` otomatis (§8.4), guru boleh menyunting deskripsi.
- Export: Excel (untuk diunggah ke e-rapor) dan PDF (leger nilai A4/F4 dengan kop sekolah + tanda tangan guru & kepala sekolah).

### 7.8 Master Data (Admin)
Tahun ajaran, kelas, siswa (+import), mata pelajaran, jadwal (+import), pengguna & peran, KKTP, pengaturan sekolah.

---

## 8. Aturan Bisnis & Perhitungan

Semua logika di bawah **ditempatkan di Service**, bukan di controller atau view.

### 8.1 Nilai Akhir (`PerhitunganNilaiService`)

```
rata_formatif        = AVG(nilai penilaian jenis=formatif)
rata_sumatif_lingkup = AVG(nilai penilaian jenis=sumatif_lingkup)
nilai_sumatif_akhir  = nilai penilaian jenis=sumatif_akhir (ambil yang terakhir)

nilai_akhir = (rata_formatif        × bobot_formatif
             + rata_sumatif_lingkup × bobot_sumatif_lingkup
             + nilai_sumatif_akhir  × bobot_sumatif_akhir) / 100
```

- Bobot default 20 / 40 / 40, dapat diubah di `pengaturans`. Validasi: total harus 100.
- Komponen yang belum ada nilainya **dikeluarkan** dari perhitungan dan bobotnya dinormalisasi ulang, agar nilai tidak anjlok di tengah semester. Tampilkan label "sementara" bila belum lengkap.
- Pembulatan: 2 desimal saat menghitung, tampil 1 desimal, dibulatkan ke bilangan bulat saat dibekukan ke rapor.

### 8.2 Predikat & ketuntasan

| Predikat | Syarat (default, dapat diubah) |
|---|---|
| A | nilai_akhir ≥ 90 |
| B | 80 ≤ nilai_akhir < 90 |
| C | 70 ≤ nilai_akhir < 80 |
| D | nilai_akhir < 70 |

`is_tuntas = nilai_akhir >= KKTP` (KKTP per mapel per tingkat, default 75).

### 8.3 Rekap presensi (`RekapPresensiService`)

```
persentase_kehadiran = jumlah_hadir / jumlah_pertemuan_terlaksana × 100
```
Agenda berstatus `libur` / `kegiatan_sekolah` **tidak dihitung** sebagai pertemuan. `dispensasi` dihitung hadir. `bolos` dihitung terpisah dari `alfa` untuk keperluan BK.

### 8.4 Deskripsi rapor otomatis (`DeskripsiRaporService`)

Ambil TP dengan nilai tertinggi dan terendah untuk siswa tersebut, lalu susun kalimat:

> "Ananda **{nama}** menunjukkan penguasaan sangat baik pada {TP tertinggi}. Perlu peningkatan pada {TP terendah}."

Variasikan frasa berdasarkan predikat (A: "sangat baik", B: "baik", C: "cukup", D: "perlu bimbingan"). Hasilnya **selalu bisa disunting manual** oleh guru sebelum dibekukan.

### 8.5 Penguncian data
Setelah rapor dibekukan, `penilaians` dan `agendas` semester tersebut otomatis `is_terkunci = true`. Hanya admin yang bisa membuka kunci, dan aksi itu dicatat di activity log.

---

## 9. Import & Export

Semua template disediakan sebagai tombol **"Unduh Template"** di halaman terkait.

### Template import

**Siswa** — `template_siswa.csv`
`nisn,nis,nama,jenis_kelamin,kelas,no_absen`
Contoh: `0071234567,12345,Ahmad Rizki,L,XII IPA 1,1`
Aturan: NISN duplikat → data diperbarui, bukan digandakan. Kelas harus sudah ada, kalau tidak → baris ditolak dengan pesan baris ke-N.

**Jadwal** — `template_jadwal.csv`
`hari,jam_ke,jam_mulai,jam_selesai,kelas,mata_pelajaran,guru_nip`
Aturan: cek bentrok sebelum menyimpan; laporkan seluruh bentrok sekaligus, jangan berhenti di error pertama.

**Nilai** — `template_nilai.csv` (per penilaian)
`nisn,nama,nilai`

### Export
- Excel: daftar nilai, rekap presensi, agenda, leger rapor.
- PDF: jadwal mengajar (F4 landscape), daftar hadir bulanan (F4), agenda mengajar (A4), leger rapor (F4), kartu nilai per siswa (A4).
- Semua PDF memakai kop sekolah dari `pengaturans` + tanda tangan guru & kepala sekolah + tanggal cetak.

---

## 10. Otorisasi

```php
// Policy inti
JadwalPolicy::view    → $jadwal->guru_id === $user->id || $user->hasRole('admin')
AgendaPolicy::update  → $agenda->jadwal->guru_id === $user->id && ! $agenda->is_terkunci
PresensiPolicy        → mengikuti AgendaPolicy
NilaiPolicy::update   → $penilaian->guru_id === $user->id && ! $penilaian->is_terkunci
RaporPolicy::view     → guru pengampu, wali kelas terkait, atau admin
```

Wali kelas mendapat gate tambahan `lihat-rekap-kelas` untuk kelas perwaliannya (semua mapel, hanya baca).

---

## 11. Panduan Tampilan (dari draft Canva)

Pertahankan identitas visual prototipe.

```js
// tailwind.config.js
colors: {
  primary:   { DEFAULT: '#1A365D', 600: '#2D4A6F' },  // biru navy — header, sidebar, judul
  accent:    { DEFAULT: '#7C9885', 600: '#6B8874' },  // hijau sage — tombol aksi, aksen
}
fontFamily: { sans: ['Plus Jakarta Sans', 'sans-serif'] }
```

**Layout**: sidebar kiri 288px (`w-72`) putih dengan header profil bergradasi `from-[#1A365D] to-[#2D4A6F]`; di bawah 1024px sidebar menjadi drawer dengan overlay blur. Topbar putih berisi tanggal Indonesia lengkap ("Kamis, 6 Agustus 2026") dan lonceng notifikasi.

**Komponen wajib dibuat sebagai Blade component:**
- `<x-kartu-statistik>` — angka besar + label
- `<x-badge-status>` — hadir (hijau), sakit (kuning), izin (biru), alfa (merah muda), bolos (merah)
- `<x-badge-predikat>` — A hijau, B biru, C kuning, D merah
- `<x-tabel>` + `<x-tabel.kosong>` — keadaan kosong dengan ilustrasi & ajakan aksi
- `<x-modal>` — pengganti `modal-backdrop` manual di prototipe
- `<x-notifikasi>` — toast kanan bawah, 3 detik (ganti `showNotification()`)

**Gaya**: kartu `rounded-xl border border-slate-200`, hover naik 4px, animasi fade-in 0.4s, latar halaman `bg-slate-50`. Tabel selalu dibungkus `overflow-x-auto` agar aman di ponsel.

**Aksesibilitas & mobile**: guru banyak mengisi presensi lewat HP — target sentuh minimal 44px, tombol status presensi berupa tombol besar, bukan dropdown.

---

## 12. Roadmap Pembangunan

Kerjakan berurutan. Setiap fase harus bisa dijalankan dan diuji sebelum lanjut.

**Fase 0 — Fondasi**
Instalasi Laravel + paket, Breeze, Tailwind + design token, layout sidebar/topbar, komponen Blade dasar, halaman login, seeder pengguna admin & guru.

**Fase 1 — Master Data**
Migrasi & model: tahun_ajarans, mata_pelajarans, kelas, siswas, kelas_siswa. CRUD admin + import siswa. Seeder: tahun ajaran 2026/2027 Ganjil, kelas XII IPA 1–8, mapel Bahasa Indonesia.

**Fase 2 — Jadwal Mengajar**
Migrasi jadwals + validasi bentrok, CRUD admin, import jadwal, tampilan guru per hari, cetak PDF F4.

**Fase 3 — Agenda Mengajar**
Migrasi agendas, buat agenda dari jadwal, CRUD, filter, export Excel/PDF.

**Fase 4 — Presensi**
Migrasi presensis, halaman input cepat (tandai semua hadir), rekap per kelas/siswa/rentang, export daftar hadir.

**Fase 5 — Struktur Materi & Penilaian**
Migrasi babs, tujuan_pembelajarans, kktps, penilaians, nilais. CRUD penilaian + grid nilai Livewire + `PerhitunganNilaiService` + import/export nilai.

**Fase 6 — Bank Materi**
Folder & unggah materi, tautan ke bab, penghitung unduhan, integrasi saran materi di form agenda.

**Fase 7 — Rekap Rapor**
`RekapPresensiService`, `DeskripsiRaporService`, tabel rapor, pembekuan, leger PDF, export Excel.

**Fase 8 — Dashboard & Penyempurnaan**
Statistik nyata, jadwal berlangsung, peringatan agenda belum diisi, activity log, pengaturan sekolah, uji beban data (8 kelas × 36 siswa × 1 semester).

### Definition of Done per fase
- [ ] Migrasi jalan bersih di `migrate:fresh --seed`
- [ ] Seeder menghasilkan data contoh yang cukup untuk mencoba fitur
- [ ] Validasi form lengkap dengan pesan Bahasa Indonesia
- [ ] Policy diterapkan dan diuji dengan akun guru non-pemilik
- [ ] Tidak ada query N+1 (cek dengan `DB::listen` atau Debugbar)
- [ ] Tampilan rapi di lebar 375px dan 1440px
- [ ] Keadaan kosong (belum ada data) ditangani, bukan tabel kosong tanpa penjelasan

---

## 13. Data Seeder Awal (konteks nyata)

- **Sekolah**: SMA Negeri 1 Ciruas, Kabupaten Serang, Banten
- **Tahun ajaran aktif**: 2026/2027 Semester Ganjil
- **Kelas contoh**: XII IPA 1 s.d. XII IPA 8
- **Mapel contoh**: Bahasa Indonesia (kelompok umum), KKTP 75
- **Bab contoh (Bahasa Indonesia XII)**: Teks Editorial, Teks Cerita Sejarah, Novel, Surat Lamaran Pekerjaan, Kritik & Esai
- **Siswa**: generate 36 siswa dummy per kelas dengan Faker locale `id_ID`
- **Jadwal contoh**: 2 JP per kelas per minggu

Data dummy hanya untuk pengembangan. Beri `--seed` terpisah (`DemoSeeder`) agar mudah dihapus sebelum dipakai sungguhan.

---

## 14. Yang TIDAK Boleh Dilakukan

- ❌ Menyimpan agregat presensi (hadir/sakit/izin/alfa) sebagai kolom di tabel — selalu hitung dari `presensis`.
- ❌ Menaruh logika perhitungan nilai di Blade atau controller.
- ❌ Membuat kelas/mapel sebagai nilai teks bebas di form — selalu relasi ke master data.
- ❌ Memakai `localStorage`/array JavaScript sebagai sumber data (kesalahan prototipe).
- ❌ Menghapus data secara permanen tanpa konfirmasi ganda; gunakan soft delete.
- ❌ Menampilkan data siswa/nilai kelas lain kepada guru yang tidak mengampu.
- ❌ Menulis migrasi ulang yang menghapus tabel berisi data nyata.

---

## 15. Glosarium

| Istilah | Arti |
|---|---|
| JP | Jam Pelajaran (satu slot jadwal) |
| CP | Capaian Pembelajaran |
| TP | Tujuan Pembelajaran (di prototipe disebut "Sub BAB") |
| KKTP | Kriteria Ketercapaian Tujuan Pembelajaran (pengganti KKM) |
| Formatif | Penilaian selama proses belajar, untuk perbaikan pembelajaran |
| Sumatif lingkup | Penilaian di akhir satu lingkup materi/bab |
| Sumatif akhir | Penilaian akhir semester |
| Rombel | Rombongan belajar (kelas) |
| Leger | Rekap nilai seluruh siswa satu kelas |
| Alfa | Tidak hadir tanpa keterangan |
| Bolos | Hadir di sekolah tetapi tidak mengikuti pelajaran |

---

## 16. Catatan Integrasi (opsional, di luar cakupan awal)

Sekolah juga memiliki sistem jadwal pelajaran tingkat sekolah berbasis Laravel (folder `C:\xampp\htdocs\KURIKULUM`). Bila kelak ingin dihubungkan, jalur paling aman adalah **import CSV jadwal** dari sistem itu ke Portal Admin Guru menggunakan `template_jadwal.csv` di §9 — jangan berbagi database secara langsung. Rancang `JadwalImport` supaya bisa menerima format tersebut sejak awal (kolom `hari, jam_ke, nama_kelas, nama_guru, mata_pelajaran` dipetakan ke skema di sini).
