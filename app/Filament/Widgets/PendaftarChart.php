<?php

namespace App\Filament\Widgets;

use App\Models\User;
use Filament\Widgets\ChartWidget;
use Carbon\Carbon;

class PendaftarChart extends ChartWidget
{
    protected static ?string $heading = 'Grafik Pertumbuhan Pendaftar Baru';
    
    protected static ?int $sort = 2; 

    protected function getData(): array
    {
        $data = [];
        $months = [];

        for ($i = 5; $i >= 0; $i--) {
            $months[] = Carbon::now()->subMonths($i)->format('M');
            
            $count = User::whereMonth('created_at', Carbon::now()->subMonths($i)->month)
                         ->whereYear('created_at', Carbon::now()->subMonths($i)->year)
                         ->count();
            
            $data[] = $count;
        }

        return [
            'datasets' => [
                [
                    'label' => 'Pendaftar Baru',
                    'data' => $data,
                    'borderColor' => '#009FE3',
                    'fill' => 'start',
                    'backgroundColor' => 'rgba(0, 159, 227, 0.2)',
                ],
            ],
            'labels' => $months,
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}