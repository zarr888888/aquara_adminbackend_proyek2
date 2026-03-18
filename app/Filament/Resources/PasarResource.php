<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PasarResource\Pages;
use App\Filament\Resources\PasarResource\RelationManagers;
use App\Models\Pasar;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class PasarResource extends Resource
{
    protected static ?string $model = Pasar::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                \Filament\Forms\Components\Select::make('user_id')
                    ->relationship('user', 'name')
                    ->label('Nama Penjual (User)')
                    ->searchable()
                    ->required(),
                
                // Kolom Nama Ikan
                \Filament\Forms\Components\TextInput::make('nama_ikan')
                    ->label('Nama Ikan / Barang')
                    ->required()
                    ->maxLength(255),
                
                // Kolom Harga
                \Filament\Forms\Components\TextInput::make('harga')
                    ->required()
                    ->numeric()
                    ->prefix('Rp')
                    ->suffix('/ Kg'),
                
                // Kolom WA
                \Filament\Forms\Components\TextInput::make('nomor_wa')
                    ->label('Nomor WhatsApp Penjual')
                    ->required()
                    ->tel()
                    ->placeholder('Contoh: 081234567890'),
                
                // Kolom Lokasi
                \Filament\Forms\Components\TextInput::make('lokasi')
                    ->label('Lokasi Tambak')
                    ->required()
                    ->maxLength(255)
                    ->placeholder('Contoh: Desa Singajaya'),
                
                // Kolom Deskripsi
                \Filament\Forms\Components\Textarea::make('deskripsi')
                    ->required()
                    ->columnSpanFull(),
                
                // Kolom Foto
                \Filament\Forms\Components\FileUpload::make('foto')
                    ->image()
                    ->directory('pasar-fotos')
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                \Filament\Tables\Columns\TextColumn::make('user.name')
                    ->label('Penjual')
                    ->searchable()
                    ->sortable(),
                \Filament\Tables\Columns\ImageColumn::make('foto')
                    ->circular(),
                \Filament\Tables\Columns\TextColumn::make('nama_ikan')
                    ->searchable(),
                \Filament\Tables\Columns\TextColumn::make('harga')
                    ->money('idr') 
                    ->sortable(),
                \Filament\Tables\Columns\TextColumn::make('lokasi')
                    ->searchable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                \Filament\Tables\Actions\EditAction::make(),
                \Filament\Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                \Filament\Tables\Actions\BulkActionGroup::make([
                    \Filament\Tables\Actions\DeleteBulkAction::make(),
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
            'index' => Pages\ListPasars::route('/'),
            'create' => Pages\CreatePasar::route('/create'),
            'edit' => Pages\EditPasar::route('/{record}/edit'),
        ];
    }
}
