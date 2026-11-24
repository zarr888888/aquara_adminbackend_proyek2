<?php

namespace App\Support;

use DateTimeImmutable;
use DateTimeZone;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use InvalidArgumentException;

class BmkgForecast
{
    public const DEFAULT_PORT = 'XJ022';
    public const PORTS = ['XJ022' => 'Karangsong', 'XJ021' => 'Eretan'];
    public const BASE = 'https://maritim.bmkg.go.id/marine2026-data/pelabuhan/';

    public function get(string $code): array
    {
        $name = self::PORTS[$code] ?? throw new InvalidArgumentException('Kode pelabuhan tidak didukung.');
        $url = self::BASE.$code.'.json';
        $data = ['location' => ['code' => $code, 'name' => $name], 'source' => 'BMKG', 'source_url' => $url,
            'is_mock' => false, 'generated_at' => null, 'issued_at' => null, 'valid_from' => null, 'valid_to' => null, 'items' => []];

        // Cache only validated forecasts. Recheck validity even on a cache hit.
        $key = 'samudera:bmkg:v1:'.$code;
        $cached = Cache::get($key);
        if ($cached !== null) {
            $data = $cached;
        } else {
            try {
                $response = Http::acceptJson()->withoutRedirecting()->connectTimeout(2)->timeout(5)->get($url);
            } catch (ConnectionException) {
                return $this->result($data, 'unavailable');
            }
            if (! $response->successful()) {
                return $this->result($data, 'unavailable');
            }
            $raw = $response->json();
            if ($raw === [] || $response->body() === '') {
                return $this->result($data, 'empty');
            }
            if (($raw['code'] ?? null) !== $code) {
                return $this->result($data, 'invalid_data');
            }
            $data['generated_at'] = now('UTC')->toIso8601String();
            $data['issued_at'] = $this->utc($raw['issued'] ?? null);
            $data['valid_from'] = $this->utc($raw['valid_from'] ?? null);
            $data['valid_to'] = $this->utc($raw['valid_to'] ?? null);
            if (! $data['valid_from'] || ! $data['valid_to'] || $data['valid_to'] <= $data['valid_from']) {
                return $this->result($data, 'invalid_data');
            }
            $items = [];
            foreach (['forecast_day1', 'forecast_day2-4'] as $group) {
                foreach (is_array($raw[$group] ?? null) ? $raw[$group] : [] as $row) {
                    $time = $this->utc($row['time'] ?? null);
                    if (! $time || $time < $data['valid_from'] || $time > $data['valid_to']) {
                        continue;
                    }
                    $number = static fn ($key) => is_numeric($row[$key] ?? null) && is_finite((float) $row[$key]) ? (float) $row[$key] : null;
                    $text = static fn ($key) => is_string($row[$key] ?? null) && trim($row[$key]) !== '' ? trim($row[$key]) : null;
                    $wind = $number('wind_speed');
                    $values = ['kondisi' => $text('weather'), 'suhu_c' => $number('temp_avg'), 'angin_dari' => $text('wind_from'),
                        'kecepatan_angin_knot' => $wind, 'kecepatan_angin_kmh' => $wind === null ? null : round($wind * 1.852, 3),
                        'hembusan_angin_knot' => $number('wind_gust'), 'kategori_gelombang' => $text('wave_cat'),
                        'tinggi_gelombang_m' => $number('wave_height'), 'pasang_surut_m' => $number('tides')];
                    if (array_filter($values, static fn ($v) => $v !== null)) {
                        $items[$time] = ['id' => $code.'@'.$time, 'wilayah' => $name, 'valid_at' => $time] + $values;
                    }
                }
            }
            ksort($items);
            $data['items'] = array_values($items);
        }
        $now = now('UTC')->toIso8601String();
        if ($now >= $data['valid_to']) {
            $data['items'] = [];
            return $this->result($data, 'expired');
        }
        if (! $data['items']) {
            return $this->result($data, 'empty');
        }
        if ($cached === null) {
            Cache::put($key, $data, min(300, max(1, strtotime($data['valid_to']) - now()->timestamp)));
        }
        return $this->result($data, $now < $data['valid_from'] ? 'upcoming' : 'available');
    }

    private function utc(mixed $value): ?string
    {
        if (! is_string($value) || ! preg_match('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2} UTC$/D', $value)) {
            return null;
        }
        $format = 'Y-m-d H:i \\U\\T\\C';
        $date = DateTimeImmutable::createFromFormat('!'.$format, $value, new DateTimeZone('UTC'));
        return $date && $date->format($format) === $value ? $date->format(DATE_ATOM) : null;
    }

    private function result(array $data, string $status): array
    {
        $message = match ($status) {
            'available' => 'Prakiraan BMKG tersedia.', 'upcoming' => 'Prakiraan BMKG belum mulai berlaku.',
            'expired' => 'Prakiraan BMKG sudah kedaluwarsa.', 'empty' => 'Tidak ada data prakiraan BMKG.',
            'invalid_data' => 'Data prakiraan BMKG tidak valid.', 'unavailable' => 'Layanan BMKG tidak dapat diakses.',
        };
        return ['success' => in_array($status, ['available', 'upcoming'], true), 'message' => $message, 'data' => ['status' => $status] + $data];
    }
}