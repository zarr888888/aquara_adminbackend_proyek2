<?php

namespace App\Traits;

use App\Support\CloudinaryUrl;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

/**
 * Trait UploadsToCloudinary
 *
 * Menyediakan helper terpusat untuk upload & hapus foto ke disk `cloudinary`.
 * Dipakai oleh seluruh Controller API SAMUDERA agar file media (foto SOS Bahari,
 * Pasar/Katalog, Forum, dsb) tidak hilang akibat ephemeral filesystem Heroku.
 *
 * Fase 1 (PRD SAMUDERA): hanya alur upload BARU yang wajib memakai Cloudinary.
 * File lama yang masih tersimpan di disk `public` TIDAK dimigrasi otomatis.
 */
trait UploadsToCloudinary
{
    /**
     * Upload file ke disk `cloudinary` dan kembalikan URL publik (secure_url)
     * yang siap disimpan ke kolom database & dikonsumsi langsung oleh Flutter.
     *
     * @param  UploadedFile  $file
     * @param  string  $directory  Folder tujuan di Cloudinary, contoh: 'samudera/pasar-fotos'
     * @return string|null  URL publik Cloudinary, atau null jika upload gagal
     */
    protected function uploadFotoToCloudinary(UploadedFile $file, string $directory): ?string
    {
        try {
            /** @var FilesystemAdapter $disk */
            $disk = Storage::disk('cloudinary');
            $path = $disk->putFile($directory, $file);

            if (! $path) {
                return null;
            }

            /** @var FilesystemAdapter $urlDisk */
            $urlDisk = Storage::disk('cloudinary');

            return $urlDisk->url($path);
        } catch (\Throwable $e) {
            Log::error('Cloudinary upload failed.', ['exception_type' => $e::class]);

            return null;
        }
    }

    /**
     * Hapus foto dari Cloudinary berdasarkan URL publik yang tersimpan di database.
     * Aman dipanggil meski URL bukan berasal dari Cloudinary (akan di-skip & di-log,
     * bukan melempar exception) — berguna untuk kompatibilitas data lama di disk `public`.
     */
    protected function deleteFotoFromCloudinary(?string $url): void
    {
        if ($url === null || ! CloudinaryUrl::isCloudinaryUrl($url)) {
            // Data lama (Fase sebelum Cloudinary) masih berupa path relatif disk `public`,
            // bukan URL Cloudinary. Jangan diproses di sini sesuai keputusan Fase 1
            // (migrasi data lama masuk ke backlog v2).
            return;
        }

        try {
            $publicIdWithFolder = CloudinaryUrl::extractPath($url);

            if ($publicIdWithFolder) {
                Storage::disk('cloudinary')->delete($publicIdWithFolder);
            }
        } catch (\Throwable $e) {
            Log::error('Cloudinary deletion failed.', ['exception_type' => $e::class]);
        }
    }
}
