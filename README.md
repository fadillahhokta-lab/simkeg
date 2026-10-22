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

## Deploy dengan Docker

Satu image (FrankenPHP) berisi aplikasi dan aset yang sudah di-build, ditambah MariaDB dari `compose.yaml`.

1. Siapkan `.env` di server dari `.env.example`, minimal:

   ```dotenv
   APP_ENV=production
   APP_DEBUG=false
   APP_URL=http://alamat-internal:8080
   APP_KEY=base64:...        # php artisan key:generate --show
   DB_PASSWORD=...
   DB_ROOT_PASSWORD=...
   ADMIN_USERNAME=admin
   ADMIN_PASSWORD=...        # sandi Admin pertama
   APP_VERSION=1.0.0         # tag image
   APP_PORT=8080             # port di host
   ```

   `.env` tidak ikut masuk image; semua rahasia dibaca saat container berjalan.

2. Build dan jalankan:

   ```bash
   docker compose build
   docker compose up -d
   docker compose logs -f app
   ```

   Saat start, container menjalankan migrasi, seeder dasar (role, Admin pertama, jenis kegiatan), lalu cache konfigurasi.

3. Buka `http://alamat-internal:8080`.

Catatan:

- Data tersimpan di volume `dbdata` (database) dan `storage` (lampiran, sesi). **Jangan jalankan `docker compose down -v`**, karena perintah itu menghapus keduanya.
- Container melayani HTTP biasa di port 80. Pasang TLS di reverse proxy atau VPN.
- Migrasi otomatis aman untuk satu instance. Kalau nanti memakai beberapa replika, pindahkan migrasi ke langkah deploy terpisah.
- Perintah artisan di container: `docker compose exec app php artisan ...`

## Test

```bash
php artisan test
```

Test mencakup login, aturan akses per role (Admin/Staff), validasi, dan export.

## Catatan teknis

- **Lampiran surat tugas** disimpan di `storage/app/private` dan diunduh lewat `/dinas/{id}/lampiran`, yang mengecek hak akses. Lampiran tidak diletakkan di `storage/app/public` karena file di sana bisa diunduh tanpa login.
- **Status Terlambat** (kegiatan) dan **Terjadwal/Berjalan/Selesai** (dinas) dihitung dari tanggal, tidak disimpan.
- **Kode kegiatan** diisi manual, wajib unik, dan boleh memuat `/`.
