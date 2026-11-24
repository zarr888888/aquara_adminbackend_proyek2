<?php

namespace App\Support;

use Illuminate\Support\Facades\Cache;
use InvalidArgumentException;

class ExternalInfo
{
    public const CACHE_SECONDS = 300;

    public const REQUESTS_PER_MINUTE = 30;

    public const NOTICE = 'Pilihan sumber pemerintah yang dikurasi SAMUDERA.';

    public function get(string $type): array
    {
        if (! in_array($type, ['kkp', 'tpi'], true)) {
            throw new InvalidArgumentException('Jenis informasi tidak didukung.');
        }

        return Cache::remember('samudera:external-info:curated:v1:'.$type, self::CACHE_SECONDS, fn () => [
            'success' => true,
            'message' => self::NOTICE,
            'data' => [
                'is_mock' => false,
                'source' => 'Kurasi SAMUDERA',
                'generated_at' => now()->toIso8601String(),
                'items' => $this->items($type),
            ],
        ]);
    }

    protected function items(string $type): array
    {
        $pins = $type === 'tpi' ? \App\Models\TpiVerification::query()->whereIn('id',
            \App\Models\TpiVerification::query()->selectRaw('MAX(id)')->groupBy('facility')
        )->where('published', true)->get()->keyBy('facility') : collect();
        return match ($type) {
            'kkp' => [[
                'id' => 'diskanla-indramayu',
                'judul' => 'Layanan perikanan Indramayu',
                'ringkasan' => 'Baca berita daerah, layanan dinas, dan informasi publik untuk nelayan serta pembudidaya.',
                'penerbit' => 'Diskanla Indramayu',
                'source_url' => 'https://diskanla.indramayukab.go.id/',
                'published_at' => null,
            ], [
                'id' => 'kkp-pdpi',
                'judul' => 'Peta prakiraan daerah penangkapan ikan',
                'ringkasan' => 'Lihat peta prakiraan sebaran daerah penangkapan ikan pelagis di perairan Indonesia.',
                'penerbit' => 'Kementerian Kelautan dan Perikanan',
                'source_url' => 'https://portal-informasi.kkp.go.id/pdpi/',
                'published_at' => null,
            ]],
            'tpi' => array_map(static fn ($row) => [
                'id' => $row[0], 'nama' => $row[1], 'desa' => $row[2], 'kecamatan' => $row[3],
                'alamat' => implode(', ', array_filter([$row[2], $row[3], 'Kabupaten Indramayu'])),
                'lat' => $pins->get($row[0])?->lat, 'lng' => $pins->get($row[0])?->lng, 'coordinate_source_url' => $pins->get($row[0])?->source_url,
            ], [
                // SINONIAYU /investasi_locations/275 identifies a facility polygon, not a surveyed point.
                ['dadap', 'TPI Dadap', 'Dadap', 'Juntinyuat'],
                ['glayem', 'TPI Glayem', null, 'Juntinyuat'],
                ['karangsong', 'TPI Karangsong', 'Karangsong', 'Indramayu'],
                ['eretan-wetan', 'TPI Eretan Wetan (Misaya Mina)', 'Eretan Wetan', 'Kandanghaur'],
                ['mina-bahari', 'TPI Mina Bahari', 'Eretan Kulon', 'Kandanghaur'],
            ]),
        };
    }
}