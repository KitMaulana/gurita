<?php

namespace App\Policies;

use App\Models\FolderMateri;
use App\Models\User;

class FolderMateriPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, FolderMateri $folder): bool
    {
        return $folder->guru_id === $user->id || $user->isAdmin();
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, FolderMateri $folder): bool
    {
        return $folder->guru_id === $user->id || $user->isAdmin();
    }

    public function delete(User $user, FolderMateri $folder): bool
    {
        return $this->update($user, $folder);
    }
}
