<?php

namespace App\Support;

use App\Traits\UploadsToCloudinary;
use Filament\Forms\Components\FileUpload;
use Illuminate\Http\UploadedFile;
use Illuminate\Validation\ValidationException;

class CloudinaryFileUpload
{
    use UploadsToCloudinary;

    public static function make(string $name): FileUpload
    {
        return FileUpload::make($name)->disk('cloudinary')->image()
            ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])->maxSize(5120)
            ->fetchFileInformation(false)
            ->saveUploadedFileUsing(function (UploadedFile $file, FileUpload $component): string {
                $url = (new self)->uploadFotoToCloudinary($file, 'samudera/'.($component->getDirectory() ?: 'admin-images'));
                if (! $url) {
                    throw ValidationException::withMessages([$component->getStatePath() => 'Upload Cloudinary gagal.']);
                }

                return $url;
            })
            ->getUploadedFileUsing(function (string $file): ?array {
                if (! CloudinaryUrl::isCloudinaryUrl($file)) {
                    return null; // Do not fetch arbitrary URLs or rewrite legacy files.
                }

                return ['name' => basename(parse_url($file, PHP_URL_PATH)), 'size' => 0, 'type' => 'image/jpeg', 'url' => $file];
            });
    }
}