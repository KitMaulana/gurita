<?php

namespace App\Policies;

use App\Models\Agenda;
use App\Models\User;

class AgendaPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Agenda $agenda): bool
    {
        return $agenda->jadwal->guru_id === $user->id
            || $user->isAdmin()
            || $agenda->jadwal->kelas?->wali_kelas_id === $user->id;
    }

    public function create(User $user): bool
    {
        return true;   // dibatasi lagi lewat JadwalPolicy::isiAgenda
    }

    public function update(User $user, Agenda $agenda): bool
    {
        return $agenda->jadwal->guru_id === $user->id && ! $agenda->is_terkunci;
    }

    public function delete(User $user, Agenda $agenda): bool
    {
        return $this->update($user, $agenda);
    }

    /** Hanya admin yang boleh membuka kunci; aksinya dicatat di activity log (§8.5). */
    public function bukaKunci(User $user, Agenda $agenda): bool
    {
        return $user->isAdmin();
    }
}
