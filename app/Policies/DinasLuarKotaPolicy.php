<?php

namespace App\Policies;

use App\Models\DinasLuarKota;
use App\Models\User;

class DinasLuarKotaPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    /** Admin, anggota tim kegiatan terkait, atau staff yang ikut berangkat. Berlaku juga untuk unduh lampiran. */
    public function view(User $user, DinasLuarKota $dinas): bool
    {
        return $user->isAdmin()
            || $dinas->kegiatan->tim()->whereKey($user->id)->exists()
            || $dinas->staff()->whereKey($user->id)->exists();
    }

    public function create(User $user): bool
    {
        return $user->isAdmin();
    }

    public function update(User $user, DinasLuarKota $dinas): bool
    {
        return $user->isAdmin();
    }

    public function delete(User $user, DinasLuarKota $dinas): bool
    {
        return $user->isAdmin();
    }
}
