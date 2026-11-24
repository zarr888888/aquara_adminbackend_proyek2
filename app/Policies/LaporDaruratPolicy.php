<?php

namespace App\Policies;

use App\Models\LaporDarurat;
use App\Models\User;

class LaporDaruratPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(LaporDarurat::ADMIN_ROLES);
    }

    public function view(User $user, LaporDarurat $laporDarurat): bool
    {
        return $this->viewAny($user);
    }

    public function update(User $user, LaporDarurat $laporDarurat): bool
    {
        return $this->viewAny($user);
    }

    public function create(User $user): bool
    {
        return false;
    }

    public function delete(User $user, LaporDarurat $laporDarurat): bool
    {
        return false;
    }

    public function deleteAny(User $user): bool
    {
        return false;
    }
}