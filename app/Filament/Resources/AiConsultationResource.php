<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AiConsultationResource\Pages;
use App\Filament\Resources\AiConsultationResource\RelationManagers;
use App\Models\AiConsultation;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class AiConsultationResource extends Resource
{
    protected static ?string $model = AiConsultation::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\FileUpload::make('image_path')
                    ->label('Foto Tambak/Ikan')
                    ->image()
                    ->directory('consultations')
                    ->columnSpanFull(),
                Forms\Components\Textarea::make('disease_detected')
                    ->label('Hasil Diagnosa Penyakit')
                    ->rows(3),
                Forms\Components\Textarea::make('water_quality_status')
                    ->label('Kondisi Kualitas Air')
                    ->rows(3),
                Forms\Components\Textarea::make('solution')
                    ->label('Solusi yang Diberikan AI')
                    ->rows(5)
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('image_path')
                    ->label('Foto')
                    ->circular(),
                Tables\Columns\TextColumn::make('disease_detected')
                    ->label('Penyakit')
                    ->searchable()
                    ->limit(30), // Dibatasi 30 huruf biar tabel gak kepanjangan
                Tables\Columns\TextColumn::make('water_quality_status')
                    ->label('Kualitas Air')
                    ->limit(30),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Tanggal Laporan')
                    ->dateTime('d M Y H:i')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc') 
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make(), 
                Tables\Actions\DeleteAction::make(), 
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAiConsultations::route('/'),
            'create' => Pages\CreateAiConsultation::route('/create'),
            'edit' => Pages\EditAiConsultation::route('/{record}/edit'),
        ];
    }
}
