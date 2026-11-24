<?php

namespace App\Support;

/**
 * Helper statis untuk memproses URL Cloudinary.
 *
 * Dipakai bersama oleh Controller (via trait App\Traits\UploadsToCloudinary)
 * maupun Model (event boot/deleting) agar logika ekstraksi path Cloudinary
 * dari sebuah URL publik hanya didefinisikan sekali (DRY).
 */
class CloudinaryUrl
{
    /**
     * Cek apakah sebuah string URL berasal dari Cloudinary.
     */
    public static function isCloudinaryUrl(?string $url): bool
    {
        return ! empty($url) && parse_url($url, PHP_URL_SCHEME) === 'https'
            && parse_url($url, PHP_URL_HOST) === 'res.cloudinary.com';
    }

    /**
     * Ekstrak path (folder/public_id.ext) dari URL Cloudinary agar bisa dipakai
     * kembali oleh Storage::disk('cloudinary')->delete().
     *
     * Contoh URL: https://res.cloudinary.com/<cloud>/image/upload/v169.../samudera/pasar-fotos/abc123.jpg
     * Hasil     : samudera/pasar-fotos/abc123.jpg
     */
    public static function extractPath(string $url): ?string
    {
        $parsed = parse_url($url);

        if (! isset($parsed['path'])) {
            return null;
        }

        // Path Cloudinary selalu mengandung segmen "/upload/" sebelum versi & public_id.
        if (! preg_match('#/upload/(?:v\d+/)?(.+)$#', $parsed['path'], $matches)) {
            return null;
        }

        return $matches[1];
    }
}
