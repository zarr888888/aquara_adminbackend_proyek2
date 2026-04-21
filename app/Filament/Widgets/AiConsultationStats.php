<?php

namespace App\Filament\Widgets;

use App\Models\AiConsultation;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Carbon\Carbon;

class AiConsultationStats extends BaseWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('Total Konsultasi AI', AiConsultation::count())
                ->description('Seluruh riwayat penggunaan AI')
                ->descriptionIcon('heroicon-m-camera')
                ->color('success'),

            Stat::make('Konsultasi Hari Ini', AiConsultation::whereDate('created_at', Carbon::today())->count())
                ->description('Petani yang memakai AI hari ini')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('primary'),
        ];
    }
}