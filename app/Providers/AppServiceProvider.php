<?php

namespace App\Providers;

use Spatie\Activitylog\Models\Activity;
use App\Policies\ActivityPolicy;
use BezhanSalleh\FilamentExceptions\Models\Exception;
use App\Policies\ExceptionPolicy;
use Illuminate\Support\Facades\Gate;
use Filament\Support\Facades\FilamentView;
use Filament\View\PanelsRenderHook;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        /**
         * Super Admin bypass all permissions
         */
        Gate::before(function ($user, $ability) {
            if ($user->hasRole('super_admin')) {
                return true;
            }
        });

        /**
         * Register policies
         */
        Gate::policy(Activity::class, ActivityPolicy::class);
        Gate::policy(Exception::class, ExceptionPolicy::class);

        /**
         * Hide some menu for non super admin
         */
        FilamentView::registerRenderHook(
            PanelsRenderHook::HEAD_END,
            function () {

                /** @var \App\Models\User|null $user */
                $user = Auth::user();

                if ($user && !$user->hasRole('super_admin')) {
                    return new HtmlString('
                        <style>
                            .fi-sidebar-group:has(a[href*="exceptions"]),
                            .fi-sidebar-group:has(a[href*="backups"]) {
                                display: none !important;
                            }
                        </style>
                    ');
                }

                return '';
            }
        );
    }
}