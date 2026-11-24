<?php

namespace App\Filament\Widgets;

use App\Support\ExternalInfo;
use App\Traits\AuthorizesExternalInfoWidget;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class PetaTpiWidget extends StatsOverviewWidget
{
    use AuthorizesExternalInfoWidget;

    protected static ?int $sort = 12;

    protected static bool $isLazy = false;

    protected static ?string $pollingInterval = null;

    protected ?string $heading = 'Daftar TPI';

    protected ?string $description = ExternalInfo::NOTICE;

    protected function getStats(): array
    {
        abort_unless(static::canView(), 403);

        return array_map(fn (array $item) => Stat::make($item['nama'], $item['alamat'])
            ->description($item['coordinate_source_url'] ? 'Titik terverifikasi' : 'Titik belum terverifikasi'),
            app(ExternalInfo::class)->get('tpi')['data']['items']);
    }
}