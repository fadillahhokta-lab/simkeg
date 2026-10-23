<?php

namespace App\Console\Commands;

use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Database\Seeders\DemoSeeder;
use Illuminate\Console\Command;
use PDO;
use PDOException;

/**
 * Instalasi tanpa Docker (mis. Windows + Laragon), dipanggil oleh install.bat.
 * Aman dijalankan berulang: langkah yang sudah selesai dilewati.
 */
class Pasang extends Command
{
    protected $signature = 'simkeg:pasang
        {--sandi-admin= : Sandi akun admin (tanpa ini akan ditanyakan)}
        {--data-contoh : Muat data contoh tanpa bertanya}
        {--tanpa-data-contoh : Jangan muat data contoh}';

    protected $description = 'Siapkan database, akun admin, dan data awal SIMKEG';

    /** Ekstensi PHP yang dibutuhkan aplikasi dan paket export. */
    public const EKSTENSI = ['pdo_mysql', 'mbstring', 'openssl', 'fileinfo', 'zip', 'gd', 'dom', 'xml'];

    public function handle(): int
    {
        $this->info('[1/5] Memeriksa sistem...');
        if (! $this->cekSistem()) {
            return self::FAILURE;
        }

        $this->info('[2/5] Menyiapkan kunci aplikasi...');
        if (blank(config('app.key'))) {
            $this->callSilently('key:generate', ['--force' => true]);
        }

        $this->info('[3/5] Menyiapkan database...');
        if (! $this->buatDatabase()) {
            return self::FAILURE;
        }
        $this->call('migrate', ['--force' => true]);
        $this->callSilently('db:seed', ['--force' => true]);

        $this->info('[4/5] Menyiapkan akun admin...');
        $this->siapkanAdmin();

        $this->info('[5/5] Data contoh...');
        $this->dataContoh();

        $this->callSilently('optimize:clear');

        $this->newLine();
        $this->info('Instalasi selesai. Jalankan mulai.bat untuk membuka SIMKEG.');

        return self::SUCCESS;
    }

    private function cekSistem(): bool
    {
        $kurang = array_values(array_filter(self::EKSTENSI, fn (string $e) => ! extension_loaded($e)));
        if ($kurang) {
            $this->error('Ekstensi PHP belum aktif: '.implode(', ', $kurang));
            $this->line('Di Laragon: klik kanan ikon Laragon > PHP > Extensions, centang ekstensi di atas, lalu jalankan ulang.');

            return false;
        }

        if (! is_writable(storage_path()) || ! is_writable(base_path('bootstrap/cache'))) {
            $this->error('Folder storage atau bootstrap/cache tidak bisa ditulisi. Pindahkan folder SIMKEG ke C:\\laragon\\www.');

            return false;
        }

        if ($this->keByte((string) ini_get('upload_max_filesize')) < 6 * 1024 * 1024) {
            $this->warn('Batas upload PHP '.ini_get('upload_max_filesize').': lampiran surat tugas di atas batas ini akan gagal diunggah.');
            $this->line('Di Laragon: menu PHP > php.ini, ubah upload_max_filesize = 10M dan post_max_size = 12M.');
        }

        return true;
    }

    /** Buat database bila belum ada (hanya untuk MySQL/MariaDB). */
    private function buatDatabase(): bool
    {
        $nama = config('database.default');
        $koneksi = config("database.connections.{$nama}");

        if (! in_array($koneksi['driver'], ['mysql', 'mariadb'], true)) {
            return true;
        }

        try {
            $pdo = new PDO(
                "mysql:host={$koneksi['host']};port={$koneksi['port']}",
                $koneksi['username'],
                $koneksi['password'],
                [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION],
            );
            $database = str_replace('`', '', $koneksi['database']);
            $pdo->exec("CREATE DATABASE IF NOT EXISTS `{$database}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        } catch (PDOException $e) {
            $this->error('Tidak bisa terhubung ke MySQL: '.$e->getMessage());
            $this->line('Pastikan Laragon sudah dibuka dan tombol "Start All" sudah diklik.');

            return false;
        }

        return true;
    }

    private function siapkanAdmin(): void
    {
        $username = env('ADMIN_USERNAME', 'admin');
        if (User::where('username', $username)->exists()) {
            $this->line("Akun {$username} sudah ada, dilewati.");

            return;
        }

        $sandi = $this->option('sandi-admin');
        while (blank($sandi) || mb_strlen($sandi) < 8) {
            if (filled($sandi)) {
                $this->error('Sandi minimal 8 karakter.');
            }
            $sandi = (string) $this->secret("Buat sandi untuk akun {$username} (minimal 8 karakter, tidak tampil saat diketik)");
            if (mb_strlen($sandi) >= 8 && $sandi !== $this->secret('Ketik ulang sandi')) {
                $this->error('Sandi tidak sama, ulangi.');
                $sandi = '';
            }
        }

        DatabaseSeeder::buatAdmin($username, $sandi);
        $this->line("Akun {$username} dibuat.");
    }

    private function dataContoh(): void
    {
        $muat = match (true) {
            (bool) $this->option('data-contoh') => true,
            (bool) $this->option('tanpa-data-contoh') => false,
            default => $this->confirm('Muat data contoh (12 kegiatan, 11 dinas, akun hendra.w dan rina.k)?', true),
        };

        if (! $muat) {
            return;
        }
        if (app()->isProduction()) {
            $this->warn('APP_ENV=production: data contoh tidak dimuat.');

            return;
        }

        $this->callSilently('db:seed', ['--class' => DemoSeeder::class, '--force' => true]);
        $this->line('Data contoh dimuat. Akun contoh: hendra.w (Admin) dan rina.k (Staff), sandi demo1234.');
    }

    private function keByte(string $nilai): int
    {
        $angka = (int) $nilai;

        return match (strtoupper(substr(trim($nilai), -1))) {
            'G' => $angka * 1024 ** 3,
            'M' => $angka * 1024 ** 2,
            'K' => $angka * 1024,
            default => $angka,
        };
    }
}
