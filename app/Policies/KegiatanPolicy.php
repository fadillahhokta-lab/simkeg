<?php

namespace App\Policies;

use App\Models\Kegiatan;
use App\Models\User;

class KegiatanPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    /** Admin, atau anggota tim kegiatan. */
    public function view(User $user, Kegiatan $kegiatan): bool
    {
        return $user->isAdmin() || $this->anggotaTim($user, $kegiatan);
    }

    public function create(User $user): bool
    {
        return $user->isAdmin();
    }

    /** Edit data utama dan tim: Admin saja. */
    public function update(User $user, Kegiatan $kegiatan): bool
    {
        return $user->isAdmin();
    }

    /** Update status, progres, dan catatan: Admin atau anggota tim. */
    public function updateProgres(User $user, Kegiatan $kegiatan): bool
    {
        return $user->isAdmin() || $this->anggotaTim($user, $kegiatan);
    }

    public function delete(User $user, Kegiatan $kegiatan): bool
    {
        return $user->isAdmin();
    }

    private function anggotaTim(User $user, Kegiatan $kegiatan): bool
    {
        return $kegiatan->tim()->whereKey($user->id)->exists();
    }
}
