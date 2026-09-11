# Portal Admin Guru — SMA Negeri 1 Ciruas

Aplikasi administrasi mengajar berbasis Laravel 12. Spesifikasi lengkap ada di [CLAUDE.md](CLAUDE.md).

## Menjalankan di lokal (XAMPP)

```bash
# 1. Pastikan MySQL/MariaDB XAMPP menyala
php artisan migrate:fresh --seed      # struktur + data inti (aman untuk produksi)
php artisan db:seed --class=DemoSeeder   # data contoh, HANYA untuk pengembangan
php artisan serve
```

Buka <http://127.0.0.1:8000>.

### Akun bawaan (ganti kata sandinya sebelum dipakai sungguhan)

| Peran | Masuk dengan | Kata sandi |
|---|---|---|
| Admin / Waka Kurikulum | `admin@sman1ciruas.sch.id` atau NIP `000000000000000001` | `password` |
| Guru + Wali Kelas | `guru@sman1ciruas.sch.id` atau NIP `198001012005011001` | `password` |

Masuk bisa memakai **email atau NIP**. Pendaftaran mandiri dinonaktifkan — akun dibuat admin
lewat menu **Pengguna & Peran**.

## Aset frontend

Node.js belum terpasang di mesin ini, jadi aplikasi memakai **Tailwind Play**
(`public/vendor/tailwind-play.js`, dilayani lokal sehingga tetap jalan tanpa internet) dan Alpine
yang sudah dibundel Livewire 3.

Begitu Node.js tersedia, jalankan:

```bash
npm install
npm run build      # atau: npm run dev
```

Layout otomatis beralih ke `@vite` begitu `public/build/manifest.json` ada — tidak ada perubahan
kode yang perlu dilakukan. `tailwind.config.js` dan `resources/css/app.css` sudah berisi design
token yang sama persis dengan konfigurasi Play.

> Catatan: `resources/js/app.js` sengaja **tidak** mengimpor Alpine. Livewire 3 sudah
> membundelnya; dua instans Alpine menyebabkan galat "Detected multiple instances of Alpine".

## Pengujian

Uji berjalan terhadap basis data terpisah `portal_guru_test` (lihat `.env.testing`).

```bash
# siapkan sekali
php artisan migrate:fresh --seed --env=testing
php artisan db:seed --class=DemoSeeder --env=testing

php artisan test
```

Cakupan: seluruh halaman utama (guru & admin), dokumen cetak PDF, ekspor Excel, alur
agenda → presensi → nilai → pembekuan rapor, validasi bentrok jadwal, impor CSV, dan otorisasi.

## Sebelum dipakai sungguhan

1. Hapus data contoh: `php artisan migrate:fresh --seed` (tanpa `DemoSeeder`).
2. Ganti kata sandi kedua akun bawaan.
3. Isi **Pengaturan Sekolah** (kepala sekolah, NIP, logo) — dipakai pada kop dokumen cetak.
4. Isi **KKTP** per mata pelajaran & tingkat.
5. Set `APP_DEBUG=false` dan `APP_ENV=production` di `.env`.
