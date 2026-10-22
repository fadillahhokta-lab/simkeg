# SIMKEG

Aplikasi internal untuk mengelola kegiatan, penugasan tim, dan dinas luar kota, beserta rekap periodik dalam format Excel dan PDF.

**Panduan pemakaian:** [docs/panduan](docs/panduan/README.md)

## Fitur

- **Kegiatan**: daftar dengan filter, detail per kode, update status/progres/hasil/rekomendasi oleh tim, riwayat perubahan.
- **Dinas luar kota**: dicatat Admin dan terhubung ke kegiatan, dengan lampiran surat tugas yang disimpan privat.
- **Dashboard**: ringkasan, grafik per jenis/status, dinas per bulan, dan kegiatan yang perlu perhatian.
- **Rekap & export**: rekap kegiatan, dinas, dan beban kerja staff ke Excel/PDF.
- **Master data** (Admin): jenis kegiatan dan akun user.

Hak akses: Admin melihat semua data; Staff hanya kegiatan timnya dan dinas terkait. Aturan ini diterapkan lewat policy dan query scope di server.

## Stack

Laravel 13 (PHP 8.3+), Livewire 4, Tailwind CSS 4, MariaDB/MySQL, `spatie/laravel-permission`, Chart.js, `maatwebsite/excel`, `barryvdh/laravel-dompdf`.

## Menjalankan untuk development

Prasyarat: PHP 8.3+ (ekstensi `pdo_mysql`, `zip`, `gd`, `dom`, `mbstring`), Composer, Node.js, dan Docker untuk database.

```bash
composer install
npm install
cp .env.example .env
php artisan key:generate
```

Isi di `.env`:

```dotenv
DB_PASSWORD=...          # sandi user database
DB_ROOT_PASSWORD=...     # sandi root MariaDB
ADMIN_PASSWORD=...       # sandi akun Admin pertama (username: ADMIN_USERNAME)
APP_TIMEZONE=Asia/Jakarta
```

Jalankan database, migrasi, lalu aplikasi:

```bash
docker compose up -d db
php artisan migrate --seed       # role, Admin pertama, jenis kegiatan PP/SA/PHP/PSLH
php artisan serve                # http://127.0.0.1:8000
npm run dev                      # terminal lain
```

Data contoh (opsional, ditolak di production):

```bash
php artisan db:seed --class=DemoSeeder   # akun hendra.w (Admin) dan rina.k (Staff), sandi demo1234
```

## Test

```bash
php artisan test
```

Test mencakup login, aturan akses per role (Admin/Staff), validasi, dan export.

## Catatan teknis

- **Lampiran surat tugas** disimpan di `storage/app/private` dan diunduh lewat `/dinas/{id}/lampiran`, yang mengecek hak akses. Lampiran tidak diletakkan di `storage/app/public` karena file di sana bisa diunduh tanpa login.
- **Status Terlambat** (kegiatan) dan **Terjadwal/Berjalan/Selesai** (dinas) dihitung dari tanggal, tidak disimpan.
- **Kode kegiatan** diisi manual, wajib unik, dan boleh memuat `/`.
