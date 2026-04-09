<?php

namespace App\Filament\Widgets;

use App\Models\User;
use App\Models\Pasar;
use App\Models\Stock;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class OverviewStats extends BaseWidget
{
    protected static ?int $sort = 1; 

    protected function getStats(): array
    {
        return [
            Stat::make('Total Pembudidaya', User::count())
                ->description('Seluruh pengguna terdaftar di AQUARA')
                ->descriptionIcon('heroicon-m-users')
                ->color('primary') 
                ->chart([7, 2, 10, 3, 15, 4, 17]),

            Stat::make('Total Komoditas di Pasar', Pasar::count())
                ->description('Ikan yang sedang dijual saat ini')
                ->descriptionIcon('heroicon-m-shopping-bag')
                ->color('success') 
                ->chart([2, 5, 3, 8, 4, 10, 12]),

            Stat::make('Total Laporan Stok', Stock::count())
                ->description('Jumlah laporan stok dari pembudidaya')
                ->descriptionIcon('heroicon-m-cube')
                ->color('warning')
                ->chart([10, 8, 12, 5, 14, 10, 16]),
        ];
    }
}