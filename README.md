# SIMKEG

Aplikasi internal untuk mengelola kegiatan, penugasan tim, dan dinas luar kota, beserta rekap periodik dalam format Excel dan PDF.

- **Ingin mencoba aplikasinya?** Ikuti [Cara mencoba aplikasi](#cara-mencoba-aplikasi).
- **Cara memakai aplikasinya:** lihat [Panduan pemakaian](docs/panduan/README.md).

## Fitur

- **Kegiatan**: daftar dengan filter, detail per kode, update status/progres/hasil/rekomendasi oleh tim, riwayat perubahan.
- **Dinas luar kota**: dicatat Admin dan terhubung ke kegiatan, dengan lampiran surat tugas yang disimpan privat.
- **Dashboard**: ringkasan, grafik per jenis/status, dinas per bulan, dan kegiatan yang perlu perhatian.
- **Rekap & export**: rekap kegiatan, dinas, dan beban kerja staff ke Excel/PDF.
- **Master data** (Admin): jenis kegiatan dan akun user.

Admin melihat semua data; Staff hanya kegiatan timnya dan dinas terkait.

---

## Cara mencoba aplikasi

Panduan ini untuk menjalankan SIMKEG di komputer Anda sendiri (Windows atau Mac). Anda hanya perlu memasang satu program, **Docker Desktop**. Pertama kali, prosesnya sekitar 15–20 menit, sebagian besar menunggu unduhan. Pastikan komputer terhubung ke internet.

### Langkah 1. Pasang Docker Desktop

1. Unduh Docker Desktop dari <https://www.docker.com/products/docker-desktop/> sesuai sistem operasi Anda.
2. Pasang seperti aplikasi biasa, lalu buka **Docker Desktop**.
3. Tunggu sampai di pojok kiri bawah jendela Docker Desktop tertulis **Engine running** (berwarna hijau).

Docker Desktop harus tetap terbuka selama Anda memakai SIMKEG.

### Langkah 2. Unduh SIMKEG

- Kalau Anda punya akses ke repositori GitHub: buka halaman repositori, klik tombol hijau **Code**, lalu **Download ZIP**.
- Atau pakai file ZIP yang diberikan pengembang.

Ekstrak ZIP tersebut ke folder yang mudah ditemukan, misalnya `Documents\simkeg` (Windows) atau `Documents/simkeg` (Mac).

### Langkah 3. Buka terminal di folder SIMKEG

Semua perintah di langkah berikutnya diketik di jendela ini.

- **Windows**: buka folder `simkeg` di File Explorer, klik kolom alamat di bagian atas, ketik `cmd`, lalu tekan Enter.
- **Mac**: buka aplikasi **Terminal**, ketik `cd ` (dengan spasi), seret folder `simkeg` ke jendela Terminal, lalu tekan Enter.

### Langkah 4. Buat file pengaturan

Salin file contoh pengaturan dengan perintah berikut:

- **Windows**: `copy .env.example .env`
- **Mac**: `cp .env.example .env`

Buka file `.env` yang baru dibuat dengan Notepad (Windows) atau TextEdit (Mac). Cari tiga baris di bawah ini dan isi dengan sandi buatan Anda sendiri, tanpa spasi:

```dotenv
DB_PASSWORD=sandiDatabase123
DB_ROOT_PASSWORD=sandiRootDatabase123
ADMIN_PASSWORD=sandiAdmin123
```

`ADMIN_PASSWORD` adalah sandi untuk masuk ke SIMKEG nanti, dengan username `admin`. Simpan file, tapi jangan tutup dulu.

> Di Mac, file yang namanya diawali titik tersembunyi. Tekan **Cmd + Shift + .** di Finder agar `.env` terlihat.

### Langkah 5. Buat kunci aplikasi

Ketik perintah berikut di terminal:

```bash
docker compose run --rm --build --no-deps --entrypoint php app artisan key:generate --show
```

Pertama kali, perintah ini menyiapkan aplikasi dan bisa memakan waktu **5–10 menit**. Tunggu sampai muncul satu baris yang diawali `base64:`, misalnya:

```
base64:ozuPhjnlUF+Q0V235JHZHQEQjz5SNn56EpbrdhIJ3jE=
```

Salin seluruh baris itu ke file `.env` pada baris `APP_KEY=`, sehingga menjadi `APP_KEY=base64:...`. Simpan file `.env`.

### Langkah 6. Jalankan SIMKEG

```bash
docker compose up -d --build
```

Tunggu sekitar 1 menit, lalu buka browser ke alamat **<http://localhost:8080>**.

Masuk dengan username **`admin`** dan sandi yang Anda isi di `ADMIN_PASSWORD`.

### Langkah 7 (opsional). Muat data contoh

Agar langsung bisa mencoba fitur dengan data yang sudah terisi (12 kegiatan, 11 dinas luar kota, dan 10 akun), jalankan:

```bash
docker compose exec app php artisan db:seed --class=DemoSeeder
```

Setelah itu Anda bisa masuk dengan akun contoh berikut. Sandi keduanya `demo1234`:

| Username | Peran |
|---|---|
| `hendra.w` | Admin |
| `rina.k` | Staff |

Masuk sebagai `rina.k` untuk melihat bagaimana Staff hanya melihat kegiatan timnya.

### Mematikan dan menyalakan lagi

Jalankan perintah di folder `simkeg`:

| Tujuan | Perintah |
|---|---|
| Mematikan SIMKEG | `docker compose stop` |
| Menyalakan lagi | `docker compose start`, lalu buka <http://localhost:8080> |
| Setelah memperbarui file SIMKEG | `docker compose up -d --build` |

Data Anda tetap tersimpan walaupun SIMKEG dimatikan atau komputer di-restart.

> **Jangan jalankan `docker compose down -v`.** Perintah itu menghapus seluruh data, termasuk lampiran.

### Jika ada masalah

| Gejala | Penyelesaian |
|---|---|
| `Cannot connect to the Docker daemon` atau `docker` tidak dikenal | Buka Docker Desktop dan tunggu sampai **Engine running**. Tutup lalu buka lagi terminal. |
| `DB_PASSWORD wajib diisi di .env` | Isi `DB_PASSWORD`, `DB_ROOT_PASSWORD`, dan `ADMIN_PASSWORD` di `.env` (langkah 4), simpan, lalu ulangi perintahnya. |
| Halaman tidak terbuka dan log berisi `APP_KEY belum diisi` | Ulangi langkah 5 dan pastikan baris `APP_KEY=base64:...` sudah tersimpan. Lalu jalankan `docker compose up -d`. |
| `port is already allocated` untuk 8080 | Ganti `APP_PORT=8080` di `.env` menjadi misalnya `8081`, jalankan `docker compose up -d`, lalu buka <http://localhost:8081>. |
| `port is already allocated` untuk 3306 (sering terjadi bila XAMPP atau MySQL terpasang) | Ganti `DB_FORWARD_PORT=3306` di `.env` menjadi misalnya `3307`, lalu jalankan `docker compose up -d`. |
| Lupa sandi `admin` | Mengubah `ADMIN_PASSWORD` di `.env` tidak berpengaruh setelah akun dibuat. Jalankan `docker compose exec app php artisan simkeg:reset-sandi admin sandiBaru123` (ganti `sandiBaru123`, minimal 8 karakter). |
| Masalah lain | Jalankan `docker compose logs app` dan kirim hasilnya ke pengembang. |

---

## Deploy ke server

Untuk pemakaian sungguhan di server kantor, langkahnya sama dengan [Cara mencoba aplikasi](#cara-mencoba-aplikasi), dengan perbedaan berikut di `.env`:

```dotenv
APP_ENV=production
APP_DEBUG=false
APP_URL=http://alamat-internal:8080
APP_VERSION=1.0.0         # tag image; naikkan setiap rilis
```

Jangan muat data contoh (langkah 7); `DemoSeeder` memang menolak berjalan saat `APP_ENV=production`.

Catatan:

- `.env` tidak ikut masuk image; semua rahasia dibaca saat container berjalan.
- Saat start, container menjalankan migrasi, seeder dasar (role, Admin pertama, jenis kegiatan), lalu cache konfigurasi.
- Data tersimpan di volume `dbdata` (database) dan `storage` (lampiran, sesi).
- Container melayani HTTP biasa di port 80. Pasang TLS di reverse proxy atau VPN.
- Migrasi otomatis aman untuk satu instance. Kalau nanti memakai beberapa replika, pindahkan migrasi ke langkah deploy terpisah.
- Perintah artisan di container: `docker compose exec app php artisan ...`

## Development

Prasyarat: PHP 8.3+ (ekstensi `pdo_mysql`, `zip`, `gd`, `dom`, `mbstring`), Composer, Node.js, dan Docker untuk database.

```bash
composer install
npm install
cp .env.example .env
php artisan key:generate
```

Isi `DB_PASSWORD`, `DB_ROOT_PASSWORD`, dan `ADMIN_PASSWORD` di `.env`, lalu:

```bash
docker compose up -d db
php artisan migrate --seed                 # role, Admin pertama, jenis kegiatan PP/SA/PHP/PSLH
php artisan db:seed --class=DemoSeeder     # opsional: data contoh
php artisan serve                          # http://127.0.0.1:8000
npm run dev                                # terminal lain
```

Untuk mengunggah lampiran sampai 5 MB secara lokal, naikkan `upload_max_filesize` (≥ 6M) dan `post_max_size` di `php.ini` lokal. Image Docker sudah mengaturnya.

### Test

```bash
php artisan test
```

Test mencakup login, aturan akses per role (Admin/Staff), validasi, dan export.

### Stack

Laravel 13 (PHP 8.3+), Livewire 4, Tailwind CSS 4, MariaDB, `spatie/laravel-permission`, Chart.js, `maatwebsite/excel`, `barryvdh/laravel-dompdf`, FrankenPHP (Docker).

### Catatan teknis

- **Lampiran surat tugas** disimpan di `storage/app/private` dan diunduh lewat `/dinas/{id}/lampiran`, yang mengecek hak akses. Lampiran tidak diletakkan di `storage/app/public` karena file di sana bisa diunduh tanpa login.
- **Status Terlambat** (kegiatan) dan **Terjadwal/Berjalan/Selesai** (dinas) dihitung dari tanggal, tidak disimpan.
- **Kode kegiatan** diisi manual, wajib unik, dan boleh memuat `/`.
