<?php

namespace App\Filament\Resources;

use App\Models\TpiVerification;
use Filament\Forms\Components\{Select, Textarea, TextInput, Toggle};
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class TpiVerificationResource extends Resource
{
    protected static ?string $model = TpiVerification::class;
    protected static ?string $navigationLabel = 'Verifikasi koordinat TPI';
    public static function canViewAny(): bool { return auth()->user()?->hasAnyRole(['Admin Dinas','super_admin']) ?? false; }
    public static function canCreate(): bool { return static::canViewAny(); }
    public static function form(Form $form): Form {
        return $form->schema([
            Select::make('facility')->label('Fasilitas')->options(TpiVerification::FACILITIES)->required(),
            Toggle::make('published')->label('Identitas dan titik fasilitas sudah diverifikasi; terbitkan pin')->live(),
            TextInput::make('lat')->numeric()->minValue(-90)->maxValue(90)->visible(fn ($get) => $get('published'))->required(),
            TextInput::make('lng')->numeric()->minValue(-180)->maxValue(180)->visible(fn ($get) => $get('published'))->required(),
            TextInput::make('source_url')->label('Halaman bukti resmi KKP atau Pemkab Indramayu')->url()->visible(fn ($get) => $get('published'))->required(),
            Textarea::make('evidence')->label('Bukti kecocokan identitas dan titik, atau alasan pencabutan')->minLength(12)->maxLength(2000)->required(),
        ]);
    }
    public static function table(Table $table): Table {
        return $table->defaultSort('id','desc')->columns([TextColumn::make('facility'),TextColumn::make('published')->formatStateUsing(fn ($state) => $state ? 'Pin terbit' : 'Pin dicabut'), TextColumn::make('verified_by'), TextColumn::make('evidence')->wrap(), TextColumn::make('created_at')->dateTime()]);
    }
    public static function getPages(): array { return ['index'=>TpiVerificationResource\Pages\ManageVerifications::route('/')]; }
}