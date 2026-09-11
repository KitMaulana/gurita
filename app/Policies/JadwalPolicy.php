<?php

namespace App\Policies;

use App\Models\Jadwal;
use App\Models\User;

class JadwalPolicy
{
    public function viewAny(User $user): bool
    {
        return true;   // daftar sudah difilter ke jadwal miliknya
    }

    public function view(User $user, Jadwal $jadwal): bool
    {
        return $jadwal->guru_id === $user->id
            || $user->isAdmin()
            || $jadwal->kelas?->wali_kelas_id === $user->id;
    }

    public function create(User $user): bool
    {
        return $user->isAdmin();
    }

    public function update(User $user, Jadwal $jadwal): bool
    {
        return $user->isAdmin();
    }

    public function delete(User $user, Jadwal $jadwal): bool
    {
        return $user->isAdmin();
    }

    /** Guru boleh membuat agenda dari jadwalnya sendiri. */
    public function isiAgenda(User $user, Jadwal $jadwal): bool
    {
        return $jadwal->guru_id === $user->id;
    }
}
