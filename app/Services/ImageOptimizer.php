<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;

class ImageOptimizer
{
    /**
     * Perkecil dan kompres ulang gambar JPEG/PNG/WEBP di disk secara proporsional.
     *
     * Kompresi ulang juga membuang metadata EXIF (lokasi, perangkat) dari berkas unggahan.
     * Mengembalikan dimensi hasil dalam format "lebarxtinggi", atau null jika berkas tidak dapat diproses.
     */
    public static function optimize(string $disk, string $path, int $maxSide = 1280, int $quality = 80): ?string
    {
        if (! extension_loaded('gd') || ! Storage::disk($disk)->exists($path)) {
            return null;
        }

        $fullPath = Storage::disk($disk)->path($path);
        $imageInfo = @getimagesize($fullPath);

        if (! $imageInfo) {
            return null;
        }

        [$width, $height, $type] = $imageInfo;

        $newWidth = $width;
        $newHeight = $height;

        if ($width > $maxSide || $height > $maxSide) {
            $ratio = min($maxSide / $width, $maxSide / $height);
            $newWidth = (int) round($width * $ratio);
            $newHeight = (int) round($height * $ratio);
        }

        $sourceImage = match ($type) {
            IMAGETYPE_JPEG => @imagecreatefromjpeg($fullPath),
            IMAGETYPE_PNG => @imagecreatefrompng($fullPath),
            IMAGETYPE_WEBP => function_exists('imagecreatefromwebp') ? @imagecreatefromwebp($fullPath) : false,
            default => false,
        };

        if (! $sourceImage) {
            return null;
        }

        $targetImage = imagecreatetruecolor($newWidth, $newHeight);

        if (in_array($type, [IMAGETYPE_PNG, IMAGETYPE_WEBP], true)) {
            imagealphablending($targetImage, false);
            imagesavealpha($targetImage, true);
            $transparent = imagecolorallocatealpha($targetImage, 255, 255, 255, 127);
            imagefilledrectangle($targetImage, 0, 0, $newWidth, $newHeight, $transparent);
        }

        imagecopyresampled($targetImage, $sourceImage, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);

        $saved = match ($type) {
            IMAGETYPE_JPEG => imagejpeg($targetImage, $fullPath, $quality),
            IMAGETYPE_PNG => imagepng($targetImage, $fullPath, 6),
            IMAGETYPE_WEBP => function_exists('imagewebp') && imagewebp($targetImage, $fullPath, $quality),
            default => false,
        };

        imagedestroy($sourceImage);
        imagedestroy($targetImage);

        return $saved ? "{$newWidth}x{$newHeight}" : null;
    }
}
