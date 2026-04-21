<?php

namespace App\Filament\Resources;

use App\Filament\Resources\FishTypeResource\Pages;
use App\Filament\Resources\FishTypeResource\RelationManagers;
use App\Models\FishType;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class FishTypeResource extends Resource
{
    protected static ?string $model = FishType::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
{
    return $form
        ->schema([
            Forms\Components\Section::make('Informasi Ikan')
                ->description('Data dasar jenis ikan untuk katalog.')
                ->schema([
                    Forms\Components\TextInput::make('name')
                        ->label('Nama Jenis Ikan')
                        ->required(),
                    Forms\Components\FileUpload::make('image')
                        ->label('Foto Ikan')
                        ->directory('fish-images'),
                ]),

            Forms\Components\Section::make('Parameter Analisa Usaha')
                ->description('Data ini PENTING untuk rumus kalkulator otomatis.')
                ->schema([
                    Forms\Components\TextInput::make('fcr_ratio')
                        ->label('FCR (Feed Conversion Ratio)')
                        ->helperText('Contoh: 1.0 artinya 1kg pakan jadi 1kg daging.')
                        ->numeric()
                        ->default(1.0),
                    Forms\Components\TextInput::make('harvest_duration_days')
                        ->label('Lama Panen (Hari)')
                        ->numeric(),
                    Forms\Components\TextInput::make('standard_density_per_m2')
                        ->label('Kepadatan Tebar (ekor/m2)')
                        ->numeric(),
                    Forms\Components\TextInput::make('survival_rate')
                        ->label('Survival Rate (%)')
                        ->numeric()
                        ->default(90),
                    Forms\Components\TextInput::make('market_price_per_kg')
                        ->label('Harga Jual Pasar (Rp/kg)')
                        ->prefix('Rp')
                        ->numeric(),
                    Forms\Components\TextInput::make('feed_price_per_kg')
                        ->label('Harga Pakan Rata-rata (Rp/kg)')
                        ->required()
                        ->prefix('Rp')
                        ->numeric(),
                ])->columns(2), // Biar kolomnya jadi 2 baris (rapi)

            Forms\Components\Section::make('Edukasi')
                ->schema([
                    Forms\Components\RichEditor::make('cultivation_guide')
                        ->label('Panduan Cara Budidaya')
                        ->columnSpanFull(),
                ]),
        ]);
}

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),
                Tables\Columns\ImageColumn::make('image'),
                Tables\Columns\TextColumn::make('fcr_ratio')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('harvest_duration_days')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('standard_density_per_m2')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('survival_rate')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('market_price_per_kg')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('feed_price_per_kg')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
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
            'index' => Pages\ListFishTypes::route('/'),
            'create' => Pages\CreateFishType::route('/create'),
            'edit' => Pages\EditFishType::route('/{record}/edit'),
        ];
    }
}
