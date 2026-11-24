<?php

namespace Tests\Feature;

use App\Support\BmkgForecast;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class BmkgForecastTest extends TestCase
{
    private const PATH = '/api/info/cuaca';

    protected function setUp(): void
    {
        parent::setUp();
        config(['cache.default' => 'array']);
        Http::preventStrayRequests();
        $this->travelTo(now('UTC')->setDate(2026, 11, 21)->setTime(0, 0));
    }

    private function fixture(string $code): array
    {
        return ['code' => $code, 'valid_from' => '2026-11-21 00:01 UTC', 'valid_to' => '2026-11-21 00:03 UTC',
            'forecast_day1' => [['time' => '2026-11-21 00:01 UTC', 'wind_speed' => 0, 'tides' => -0.2]],
            'forecast_day2-4' => [['time' => '2026-11-21 00:02 UTC', 'wave_height' => 1]]];
    }

    public function test_both_ports_are_public_and_cached_independently_without_forwarding_credentials(): void
    {
        Http::fake([
            BmkgForecast::BASE.'XJ022.json' => Http::response($this->fixture('XJ022')),
            BmkgForecast::BASE.'XJ021.json' => Http::response($this->fixture('XJ021')),
        ]);
        foreach (BmkgForecast::PORTS as $code => $name) {
            $this->withToken('dummy')->getJson(self::PATH.'?kode='.$code)->assertOk()->assertJsonPath('data.location.name', $name)
                ->assertJsonPath('data.status', 'upcoming')->assertJsonPath('data.items.0.valid_at', '2026-11-21T00:01:00+00:00')
                ->assertJsonPath('data.items.0.pasang_surut_m', -0.2)->assertJsonPath('data.items.0.kecepatan_angin_kmh', 0)
                ->assertJsonPath('data.items.0.tinggi_gelombang_m', null)->assertJsonPath('data.items.1.tinggi_gelombang_m', 1);
        }
        $this->travel(1)->minutes();
        $this->getJson(self::PATH)->assertOk()->assertJsonPath('data.location.code', 'XJ022')->assertJsonPath('data.status', 'available');
        Http::assertSentCount(2);
        Http::assertNotSent(fn (Request $r) => $r->hasHeader('Authorization') || $r->hasHeader('Cookie'));
        $this->travel(2)->minutes();
        $this->getJson(self::PATH)->assertServiceUnavailable()->assertJsonPath('data.status', 'expired')->assertJsonCount(0, 'data.items');
    }

    public function test_invalid_codes_do_not_call_bmkg(): void
    {
        Http::fake();
        foreach (['XXX', '', '%5B%5D', 'xj022'] as $code) {
            $this->getJson(self::PATH.'?kode='.$code)->assertUnprocessable()->assertJsonValidationErrors('kode');
        }
        Http::assertNothingSent();
    }

    public function test_empty_invalid_and_failed_requests_never_supply_mock_forecasts(): void
    {
        $empty = $this->fixture('XJ022');
        unset($empty['forecast_day1'], $empty['forecast_day2-4']);
        Http::fake([BmkgForecast::BASE.'XJ022.json' => Http::sequence()->push($empty)->push('<html>')->push($this->fixture('XJ021'))->pushStatus(503)->pushFailedConnection()]);
        foreach (['empty', 'invalid_data', 'invalid_data', 'unavailable', 'unavailable'] as $status) {
            $this->getJson(self::PATH)->assertServiceUnavailable()->assertJsonPath('success', false)->assertJsonPath('data.status', $status)
                ->assertJsonPath('data.is_mock', false)->assertJsonCount(0, 'data.items');
        }
    }
}