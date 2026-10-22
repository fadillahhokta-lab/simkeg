<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\LampiranDinasController;
use App\Livewire\Dashboard;
use App\Livewire\Dinas;
use App\Livewire\JenisKegiatan;
use App\Livewire\Pengguna;
use App\Livewire\Kegiatan;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'create'])->name('login');
    Route::post('/login', [LoginController::class, 'store']);
});

Route::middleware('auth')->group(function () {
    Route::redirect('/', '/dashboard');
    Route::get('/dashboard', Dashboard::class)->name('dashboard');
    Route::post('/logout', [LoginController::class, 'destroy'])->name('logout');

    Route::get('/kegiatan', Kegiatan\Index::class)->name('kegiatan.index');
    Route::get('/kegiatan/baru', Kegiatan\Form::class)->name('kegiatan.create');
    // Kode bisa memuat "/" (format nomor surat tugas), jadi parameter menerima "/". Route edit didaftarkan dulu.
    Route::get('/kegiatan/{kegiatan}/edit', Kegiatan\Form::class)->where('kegiatan', '.+')->name('kegiatan.edit');
    Route::get('/kegiatan/{kegiatan}', Kegiatan\Show::class)->where('kegiatan', '.+')->name('kegiatan.show');

    Route::get('/dinas', Dinas\Index::class)->name('dinas.index');
    Route::get('/dinas/baru', Dinas\Form::class)->name('dinas.create');
    Route::get('/dinas/{dinas}', Dinas\Show::class)->whereNumber('dinas')->name('dinas.show');
    Route::get('/dinas/{dinas}/edit', Dinas\Form::class)->whereNumber('dinas')->name('dinas.edit');
    Route::get('/dinas/{dinas}/lampiran', LampiranDinasController::class)->whereNumber('dinas')->name('dinas.lampiran');

    // Sementara, diganti saat halamannya dikerjakan.
    Route::view('/rekap', 'placeholder', ['title' => 'Rekap & Export'])->name('rekap.index');

    Route::middleware('role:admin')->group(function () {
        Route::get('/jenis-kegiatan', JenisKegiatan\Index::class)->name('jenis-kegiatan.index');
        Route::get('/pengguna', Pengguna\Index::class)->name('pengguna.index');
    });
});
