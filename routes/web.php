<?php

use App\Http\Controllers\Auth\LoginController;
use App\Livewire\Dashboard;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'create'])->name('login');
    Route::post('/login', [LoginController::class, 'store']);
});

Route::middleware('auth')->group(function () {
    Route::redirect('/', '/dashboard');
    Route::get('/dashboard', Dashboard::class)->name('dashboard');
    Route::post('/logout', [LoginController::class, 'destroy'])->name('logout');

    // Sementara, diganti saat halamannya dikerjakan.
    Route::view('/kegiatan', 'placeholder', ['title' => 'Kegiatan'])->name('kegiatan.index');
    Route::view('/dinas', 'placeholder', ['title' => 'Dinas Luar Kota'])->name('dinas.index');
    Route::view('/rekap', 'placeholder', ['title' => 'Rekap & Export'])->name('rekap.index');

    Route::middleware('role:admin')->group(function () {
        Route::view('/jenis-kegiatan', 'placeholder', ['title' => 'Jenis Kegiatan'])->name('jenis-kegiatan.index');
        Route::view('/pengguna', 'placeholder', ['title' => 'Manajemen User'])->name('pengguna.index');
    });
});
