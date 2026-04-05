<?php

namespace App\Filament\Widgets;

use App\Models\AiConsultation;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class LatestAiConsultations extends BaseWidget
{
    protected static ?int $sort = 5;

    public function table(Table $table): Table
    {
        return $table
            ->query(
                AiConsultation::query()->latest()->limit(5)
            )
            ->heading(' 5 Konsultasi AI Terakhir (Radar Penyakit)')
            ->columns([
                Tables\Columns\TextColumn::make('disease_detected')
                    ->label('Penyakit Terdeteksi')
                    ->limit(50)
                    ->searchable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Waktu Konsultasi')
                    ->dateTime('d M Y, H:i')
                    ->badge()
                    ->color('warning'),
            ])
            ->paginated(false);
    }
}