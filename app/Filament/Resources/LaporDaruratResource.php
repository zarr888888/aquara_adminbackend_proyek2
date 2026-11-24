<?php

namespace App\Filament\Resources;

use App\Filament\Resources\LaporDaruratResource\Pages;
use App\Models\LaporDarurat;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class LaporDaruratResource extends Resource
{
    protected static ?string $model = LaporDarurat::class;

    protected static ?string $navigationIcon = 'heroicon-o-exclamation-triangle';

    protected static ?string $navigationLabel = 'SOS Bahari';

    protected static ?string $modelLabel = 'Laporan Darurat';

    protected static ?string $pluralModelLabel = 'Laporan Darurat';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('status_laporan')
                ->label('Status Laporan')->options(LaporDarurat::STATUS_OPTIONS)->required(),
            Forms\Components\Select::make('admin_id')
                ->label('Admin Penanggung Jawab')
                ->relationship('admin', 'name', fn (Builder $query) => $query->whereHas(
                    'roles', fn (Builder $roles) => $roles->whereIn('name', LaporDarurat::ADMIN_ROLES)
                ))
                ->searchable()->preload()
                ->helperText('Jika dikosongkan, admin yang menyimpan akan menjadi penanggung jawab.'),
        ]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist->schema([
            Infolists\Components\TextEntry::make('user.name')->label('Pelapor'),
            Infolists\Components\TextEntry::make('jenis_sos')->label('Jenis SOS'),
            Infolists\Components\TextEntry::make('deskripsi')->columnSpanFull(),
            Infolists\Components\ImageEntry::make('foto')->label('Foto Bukti')
                ->height(320)->columnSpanFull(),
            Infolists\Components\TextEntry::make('status_laporan')->label('Status Laporan')
                ->badge()->color(fn (string $state): string => self::statusColor($state)),
            Infolists\Components\TextEntry::make('admin.name')->label('Penanggung Jawab')->placeholder('Belum ditugaskan'),
            Infolists\Components\TextEntry::make('created_at')->label('Dilaporkan')->dateTime('d M Y H:i'),
            Infolists\Components\TextEntry::make('updated_at')->label('Diperbarui')->dateTime('d M Y H:i'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->defaultSort('created_at', 'desc')->columns([
            Tables\Columns\TextColumn::make('user.name')->label('Pelapor')->searchable(),
            Tables\Columns\TextColumn::make('jenis_sos')->label('Jenis SOS')->searchable(),
            Tables\Columns\ImageColumn::make('foto')->label('Foto Bukti'),
            Tables\Columns\TextColumn::make('status_laporan')->label('Status')->badge()
                ->color(fn (string $state): string => self::statusColor($state))->sortable(),
            Tables\Columns\TextColumn::make('admin.name')->label('Penanggung Jawab')->placeholder('Belum ditugaskan'),
            Tables\Columns\TextColumn::make('created_at')->label('Dilaporkan')->dateTime('d M Y H:i')->sortable(),
        ])->filters([
            Tables\Filters\SelectFilter::make('status_laporan')->label('Status')->options(LaporDarurat::STATUS_OPTIONS),
            Tables\Filters\SelectFilter::make('jenis_sos')->label('Jenis SOS')->options(LaporDarurat::JENIS_SOS_OPTIONS),
        ])->actions([
            Tables\Actions\ViewAction::make(),
            Tables\Actions\EditAction::make()->label('Update Status')
                ->mutateFormDataUsing(function (array $data): array {
                    // Only handling metadata may change; never the original report.
                    return [
                        'status_laporan' => $data['status_laporan'],
                        'admin_id' => $data['admin_id'] ?? Auth::id(),
                    ];
                }),
        ]);
    }

    public static function statusColor(string $status): string
    {
        return match ($status) {
            'Pending' => 'warning',
            'Diproses' => 'info',
            'Selesai' => 'success',
            default => 'gray',
        };
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function getPages(): array
    {
        return ['index' => Pages\ListLaporDarurats::route('/')];
    }
}