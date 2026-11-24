<?php

namespace App\Filament\Widgets;

use App\Support\BmkgForecast;
use App\Traits\AuthorizesExternalInfoWidget;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class InfoCuacaWidget extends StatsOverviewWidget
{
    use AuthorizesExternalInfoWidget;

    protected static ?int $sort = 11;

    protected static bool $isLazy = false;

    protected static ?string $pollingInterval = null;

    protected ?string $heading = 'Info Cuaca — BMKG';

    protected function getStats(): array
    {
        abort_unless(static::canView(), 403);
        $forecast = app(BmkgForecast::class)->get(BmkgForecast::DEFAULT_PORT);
        $data = $forecast['data'];
        if (! $forecast['success']) {
            return [Stat::make($data['location']['name'], $forecast['message'])];
        }
        $items = collect($data['items']);
        $now = now('UTC')->toIso8601String();
        $item = $items->last(fn ($item) => $item['valid_at'] <= $now) ?? $items->first();

        return [
            Stat::make($item['wilayah'], $item['kondisi'] ?? '—')->description($item['valid_at']),
            Stat::make('Angin (knot)', $item['kecepatan_angin_knot'] ?? '—'),
            Stat::make('Gelombang (m)', $item['tinggi_gelombang_m'] ?? '—'),
        ];
    }
}