<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Throwable;

/** Dipakai mulai.bat untuk memastikan MySQL sudah menyala sebelum membuka aplikasi. */
class CekDatabase extends Command
{
    protected $signature = 'simkeg:cek-database';

    protected $description = 'Cek koneksi ke database';

    public function handle(): int
    {
        try {
            DB::connection()->getPdo();
        } catch (Throwable $e) {
            $this->error('Database belum bisa dihubungi: '.$e->getMessage());

            return self::FAILURE;
        }

        $this->info('Database tersambung.');

        return self::SUCCESS;
    }
}
