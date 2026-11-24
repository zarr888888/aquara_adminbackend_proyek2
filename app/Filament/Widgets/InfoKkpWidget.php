<?php

namespace App\Filament\Widgets;

use App\Support\ExternalInfo;
use App\Traits\AuthorizesExternalInfoWidget;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class InfoKkpWidget extends StatsOverviewWidget
{
    use AuthorizesExternalInfoWidget;

    protected static ?int $sort = 10;

    protected static bool $isLazy = false;

    protected static ?string $pollingInterval = null;

    protected ?string $heading = 'Info KKP';

    protected ?string $description = ExternalInfo::NOTICE;

    protected function getStats(): array
    {
        abort_unless(static::canView(), 403);

        return array_map(fn (array $item) => Stat::make('Informasi KKP', $item['judul'])
            ->description($item['ringkasan']), app(ExternalInfo::class)->get('kkp')['data']['items']);
    }
}