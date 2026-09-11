<?php

namespace App\Policies;

use App\Models\Penilaian;
use App\Models\User;

class PenilaianPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Penilaian $penilaian): bool
    {
        return $penilaian->guru_id === $user->id
            || $user->isAdmin()
            || $penilaian->kelas?->wali_kelas_id === $user->id;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, Penilaian $penilaian): bool
    {
        return $penilaian->guru_id === $user->id && ! $penilaian->is_terkunci;
    }

    public function delete(User $user, Penilaian $penilaian): bool
    {
        return $this->update($user, $penilaian);
    }

    public function bukaKunci(User $user, Penilaian $penilaian): bool
    {
        return $user->isAdmin();
    }
}
