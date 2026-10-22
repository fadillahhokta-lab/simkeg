<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Master data dan user. Dipakai juga di aksi Livewire, karena middleware route
        // tidak ikut dijalankan ulang pada request update Livewire.
        Gate::define('kelola-master', fn (User $user) => $user->isAdmin());
    }
}
