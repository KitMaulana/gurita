<?php

namespace App\Policies;

use App\Models\Kelas;
use App\Models\User;

class KelasPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    /** Guru boleh melihat kelas yang diampu; wali kelas juga kelas perwaliannya. */
    public function view(User $user, Kelas $kelas): bool
    {
        return $user->isAdmin()
            || $kelas->wali_kelas_id === $user->id
            || in_array($kelas->id, $user->idKelasDiampu(), true);
    }

    public function create(User $user): bool
    {
        return $user->isAdmin();
    }

    public function update(User $user, Kelas $kelas): bool
    {
        return $user->isAdmin();
    }

    public function delete(User $user, Kelas $kelas): bool
    {
        return $user->isAdmin();
    }

    /** Rekap lengkap seluruh mapel — hanya wali kelas & admin (§10). */
    public function lihatRekap(User $user, Kelas $kelas): bool
    {
        return $user->isAdmin() || $kelas->wali_kelas_id === $user->id;
    }
}
