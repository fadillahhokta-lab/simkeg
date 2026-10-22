<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Pemulihan bila sandi Admin terlupa dan tidak ada Admin lain yang bisa meresetnya.
Artisan::command('simkeg:reset-sandi {username} {sandi}', function (string $username, string $sandi) {
    $user = App\Models\User::where('username', $username)->first();

    if (! $user) {
        $this->error("User {$username} tidak ditemukan.");

        return 1;
    }
    if (mb_strlen($sandi) < 8) {
        $this->error('Sandi minimal 8 karakter.');

        return 1;
    }

    $user->update(['password' => $sandi, 'is_active' => true]);
    $this->info("Sandi {$username} sudah diganti dan akunnya aktif.");

    return 0;
})->purpose('Ganti sandi user dan aktifkan akunnya');
