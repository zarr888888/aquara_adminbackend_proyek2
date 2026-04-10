<?php

namespace App\Policies;

use App\Models\User;
use BezhanSalleh\FilamentExceptions\Models\Exception;

class ExceptionPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasRole('super_admin');
    }

    public function view(User $user, Exception $exception): bool
    {
        return $user->hasRole('super_admin');
    }
}