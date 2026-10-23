# Instalasi di Windows tanpa Docker (Laragon)

Panduan ini untuk komputer Windows yang **tidak bisa menjalankan Docker**, misalnya Windows Home tanpa WSL, virtualisasi dimatikan, atau dibatasi kebijakan IT. SIMKEG dijalankan langsung memakai **Laragon**, paket gratis berisi PHP dan MySQL untuk Windows.

Yang Anda butuhkan:

- File **`simkeg-<versi>-windows.zip`** dari pengembang. Jangan pakai "Download ZIP" dari GitHub, karena file itu tidak berisi komponen yang dibutuhkan.
- Koneksi internet untuk mengunduh Laragon dan PHP.
- Sekitar 20–30 menit.

## Langkah 1. Pasang Laragon

1. Buka <https://laragon.org/download/> dan unduh **Laragon Full**.
   - **Laragon 6** gratis. **Laragon 7 ke atas** memerlukan lisensi berbayar, tapi langkah 2 bisa dilewati karena PHP-nya sudah 8.3.
2. Jalankan installer dan biarkan semua pilihan bawaan, termasuk lokasi `C:\laragon`.
3. Buka **Laragon**.

## Langkah 2. Tambahkan PHP 8.3 (khusus Laragon 6)

SIMKEG membutuhkan PHP 8.3 atau lebih baru, sedangkan Laragon 6 membawa versi yang lebih lama.

1. Buka <https://windows.php.net/download/>, cari bagian **PHP 8.3**, lalu unduh **Zip** pada baris **VS16 x64 Thread Safe**.
2. Ekstrak isinya ke folder baru di dalam `C:\laragon\bin\php\`, misalnya `C:\laragon\bin\php\php-8.3-x64\`. Di dalam folder itu harus langsung ada file `php.exe`.
3. Di Laragon, **klik kanan** di jendela Laragon, pilih **PHP > Version**, lalu pilih folder PHP 8.3 tadi.

Kalau muncul error tentang `VCRUNTIME140.dll`, pasang [Microsoft Visual C++ Redistributable x64](https://aka.ms/vs/17/release/vc_redist.x64.exe), lalu ulangi.

## Langkah 3. Aktifkan ekstensi PHP

Klik kanan Laragon, pilih **PHP > Extensions**, lalu pastikan semua ini **tercentang**:

`pdo_mysql`, `mbstring`, `openssl`, `fileinfo`, `zip`, `gd`

Kalau ada yang terlewat, `install.bat` akan menyebutkan namanya.

## Langkah 4. Naikkan batas upload (disarankan)

Supaya lampiran surat tugas sampai 5 MB bisa diunggah:

1. Klik kanan Laragon, pilih **PHP > php.ini**. File akan terbuka di Notepad.
2. Cari (Ctrl+F) baris berikut dan ubah nilainya:
   ```ini
   upload_max_filesize = 10M
   post_max_size = 12M
   ```
3. Simpan, lalu di Laragon klik **Stop** dan **Start All** lagi.

## Langkah 5. Nyalakan Laragon

Klik **Start All** di Laragon. Tunggu sampai **MySQL** berstatus berjalan.

## Langkah 6. Ekstrak SIMKEG

Ekstrak `simkeg-<versi>-windows.zip` ke `C:\laragon\www\`, sehingga ada folder `C:\laragon\www\simkeg` yang berisi `install.bat` dan `mulai.bat`.

## Langkah 7. Jalankan instalasi

Buka folder `C:\laragon\www\simkeg`, lalu **klik dua kali `install.bat`**.

- Windows mungkin menampilkan peringatan "Windows protected your PC". Klik **More info**, lalu **Run anyway**.
- Ketik **sandi untuk akun `admin`** (minimal 8 karakter). Sandi tidak tampil saat diketik, itu normal. Ketik sekali lagi untuk konfirmasi.
- Pilih **yes** untuk memuat data contoh bila ingin langsung mencoba dengan data terisi, atau **no** untuk mulai dari kosong.

Instalasi selesai bila muncul **"Instalasi selesai"**. Tekan tombol apa saja untuk menutup jendela.

`install.bat` aman dijalankan ulang. Langkah yang sudah selesai akan dilewati.

## Langkah 8. Buka SIMKEG

**Klik dua kali `mulai.bat`**. Browser akan terbuka ke **<http://127.0.0.1:8000>**.

- Masuk dengan username **`admin`** dan sandi dari langkah 7.
- Bila memuat data contoh: `hendra.w` (Admin) atau `rina.k` (Staff), sandi `demo1234`.

**Biarkan jendela hitam `mulai.bat` tetap terbuka** selama memakai SIMKEG. Menutupnya berarti mematikan SIMKEG.

## Pemakaian sehari-hari

1. Buka Laragon, klik **Start All**.
2. Klik dua kali `mulai.bat`.

## Jika ada masalah

| Pesan | Penyelesaian |
|---|---|
| `PHP 8.3 atau lebih baru tidak ditemukan` | Ulangi langkah 2 dan pastikan `php.exe` ada langsung di folder PHP baru. |
| `Ekstensi PHP belum aktif: ...` | Centang ekstensi yang disebut di Laragon > PHP > Extensions (langkah 3), lalu jalankan `install.bat` lagi. |
| `Tidak bisa terhubung ke MySQL` atau `Database belum menyala` | Buka Laragon dan klik **Start All**, lalu ulangi. |
| `Folder vendor tidak ada` | Anda memakai ZIP dari GitHub. Minta file `simkeg-<versi>-windows.zip` ke pengembang. |
| `Batas upload PHP 2M ...` | Peringatan saja. Lakukan langkah 4 bila ingin mengunggah lampiran besar. |
| Browser menampilkan "This site can't be reached" | Tunggu beberapa detik lalu muat ulang. Pastikan jendela `mulai.bat` masih terbuka. |
| Lupa sandi `admin` | Di Laragon klik **Terminal**, lalu ketik `cd C:\laragon\www\simkeg` dan `php artisan simkeg:reset-sandi admin sandiBaru123` (ganti `sandiBaru123`, minimal 8 karakter). |

---
[Daftar isi panduan](README.md)
