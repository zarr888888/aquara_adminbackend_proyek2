<?php

namespace App\Filament\Widgets;

use App\Models\Pasar;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class LatestPasars extends BaseWidget
{
    protected static ?int $sort = 4;

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Pasar::query()->latest()->limit(5)
            )
            ->heading(' 5 Daftar Jualan Terbaru (Pasar)')
            ->columns([
                Tables\Columns\TextColumn::make('nama_ikan')
                    ->label('Komoditas Ikan')
                    ->searchable(),
                
                Tables\Columns\TextColumn::make('harga')
                    ->label('Harga')
                    ->money('IDR')
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Waktu Posting')
                    ->dateTime('d M Y, H:i')
                    ->badge()
                    ->color('success'),
            ])
            ->paginated(false); 
    }
}