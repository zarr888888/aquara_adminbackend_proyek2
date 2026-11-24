<?php

namespace App\Policies;

use App\Models\PreOrder;
use App\Models\User;

class PreOrderPolicy
{
    // Filament lists all orders; mobile history uses its own participant scope.
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(PreOrder::ADMIN_ROLES);
    }

    public function viewHistory(User $user): bool
    {
        return true;
    }

    public function view(User $user, PreOrder $preOrder): bool
    {
        return $this->viewAny($user)
            || (int) $preOrder->user_id === (int) $user->id
            || (int) $preOrder->pasar?->user_id === (int) $user->id;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, PreOrder $preOrder): bool
    {
        return $this->viewAny($user);
    }

    public function delete(User $user, PreOrder $preOrder): bool
    {
        return false;
    }

    public function deleteAny(User $user): bool
    {
        return false;
    }
}