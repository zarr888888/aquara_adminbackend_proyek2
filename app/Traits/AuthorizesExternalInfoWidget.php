<?php

namespace App\Traits;

use App\Models\User;
use Filament\Facades\Filament;

trait AuthorizesExternalInfoWidget
{
    public static function canView(): bool
    {
        $user = Filament::auth()->user();

        return $user instanceof User && $user->hasAnyRole(['Admin Dinas', 'super_admin']);
    }

    // Visibility alone is not authorization: enforce on initial AND subsequent requests.
    public function boot(): void
    {
        abort_unless(static::canView(), 403);
    }
}