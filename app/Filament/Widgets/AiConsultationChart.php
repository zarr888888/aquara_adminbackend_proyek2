<?php

namespace App\Filament\Widgets;

use App\Models\AiConsultation;
use Filament\Widgets\ChartWidget;
use Carbon\Carbon;

class AiConsultationChart extends ChartWidget
{
    protected static ?string $heading = 'Grafik Penggunaan Konsultasi AI (Radar Penyakit)';
    
    protected static ?int $sort = 3; 

    protected function getData(): array
    {
        $data = [];
        $months = [];

        for ($i = 5; $i >= 0; $i--) {
            $months[] = Carbon::now()->subMonths($i)->format('M');
            
            $count = AiConsultation::whereMonth('created_at', Carbon::now()->subMonths($i)->month)
                                  ->whereYear('created_at', Carbon::now()->subMonths($i)->year)
                                  ->count();
            
            $data[] = $count;
        }

        return [
            'datasets' => [
                [
                    'label' => 'Total Konsultasi',
                    'data' => $data,
                    'backgroundColor' => '#F7941D',
                    'borderColor' => '#F7941D',
                    'borderWidth' => 1,
                ],
            ],
            'labels' => $months,
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}