<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;

class SystemInfoWidget extends BaseWidget
{
    protected static ?int $sort = -1;

    public static function canView(): bool
    {
        /** @var \App\Models\User|null $user */
        $user = Auth::user();

        return $user && $user->hasRole('super_admin');
    }

    protected function getStats(): array
    
    {
        return [
            Stat::make('Versi PHP', phpversion())
                ->description('Mesin Penggerak Server Laragon')
                ->descriptionIcon('heroicon-m-server')
                ->color('success'),
                
            Stat::make('Versi Laravel', App::version())
                ->description('Framework Backend')
                ->descriptionIcon('heroicon-m-code-bracket')
                ->color('info'),
                
            Stat::make('Environment', App::environment())
                ->description('Status Aplikasi Saat Ini')
                ->descriptionIcon('heroicon-m-cpu-chip')
                ->color('warning'),
        ];
    }
}