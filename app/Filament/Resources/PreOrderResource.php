<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PreOrderResource\Pages;
use App\Models\PreOrder;
use Filament\Forms\Components\Select;
use Filament\Forms\Form;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class PreOrderResource extends Resource
{
    protected static ?string $model = PreOrder::class;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-cart';

    protected static ?string $navigationLabel = 'Bursa Grosir';

    protected static ?string $modelLabel = 'Pre-Order';

    protected static ?string $pluralModelLabel = 'Pre-Order';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Select::make('status_bayar')->label('Status Pembayaran')
                ->options(PreOrder::STATUS_OPTIONS)->required()
                ->rules([Rule::in(array_keys(PreOrder::STATUS_OPTIONS))]),
        ]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist->schema([
            TextEntry::make('user.name')->label('Pembeli'),
            TextEntry::make('pasar.nama_ikan')->label('Komoditas'),
            TextEntry::make('pasar.user.name')->label('Penjual'),
            TextEntry::make('total_tonase')->label('Tonase')->suffix(' ton'),
            TextEntry::make('tgl_pengambilan')->label('Tanggal Pengambilan')->date('d M Y'),
            TextEntry::make('status_bayar')->label('Status Pembayaran')->badge(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->defaultSort('created_at', 'desc')->columns([
            Tables\Columns\TextColumn::make('user.name')->label('Pembeli')->searchable(),
            Tables\Columns\TextColumn::make('pasar.nama_ikan')->label('Komoditas')->searchable(),
            Tables\Columns\TextColumn::make('pasar.user.name')->label('Penjual'),
            Tables\Columns\TextColumn::make('total_tonase')->label('Tonase')->suffix(' ton')->sortable(),
            Tables\Columns\TextColumn::make('tgl_pengambilan')->label('Pengambilan')->date('d M Y')->sortable(),
            Tables\Columns\TextColumn::make('status_bayar')->label('Pembayaran')->badge()
                ->color(fn (string $state): string => match ($state) {
                    'Lunas' => 'success', 'DP' => 'info', default => 'warning',
                }),
        ])->filters([
            Tables\Filters\SelectFilter::make('status_bayar')->label('Pembayaran')->options(PreOrder::STATUS_OPTIONS),
        ])->actions([
            Tables\Actions\ViewAction::make(),
            Tables\Actions\EditAction::make()->label('Update Pembayaran')
                ->using(function (PreOrder $record, array $data): PreOrder {
                    Gate::authorize('update', $record);
                    $validated = Validator::make($data, [
                        'status_bayar' => ['required', Rule::in(array_keys(PreOrder::STATUS_OPTIONS))],
                    ])->validate();

                    // Explicit assignment only after authorization; the model remains guarded.
                    $record->status_bayar = $validated['status_bayar'];
                    $record->save();

                    return $record;
                }),
        ]);
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function getPages(): array
    {
        return ['index' => Pages\ListPreOrders::route('/')];
    }
}