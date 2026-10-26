<?php

namespace App\Jobs;

use App\Models\Complaint;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Throwable;

class CompressComplaintPhoto implements ShouldQueue
{
    use Queueable;

    /**
     * Jumlah percobaan maksimal jika job mengalami kegagalan.
     */
    public int $tries = 3;

    /**
     * Jeda waktu antar percobaan ulang (dalam detik).
     *
     * @var array<int, int>
     */
    public array $backoff = [10, 60];

    /**
     * Batas waktu eksekusi maksimal sebelum timeout (dalam detik).
     */
    public int $timeout = 60;

    /**
     * Create a new job instance. Hanya membawa ID model untuk meminimalkan beban serialisasi antrean.
     */
    public function __construct(
        public int $complaintId
    ) {}

    /**
     * Execute the job to compress/optimize the complaint photo.
     */
    public function handle(): void
    {
        $complaint = Complaint::find($this->complaintId);
        if (! $complaint) {
            return;
        }

        $photoPath = $complaint->photo_path;

        if (! $photoPath || ! Storage::disk('public')->exists($photoPath)) {
            return;
        }

        $fullPath = Storage::disk('public')->path($photoPath);

        // Pastikan ekstensi GD tersedia untuk manipulasi berkas
        if (! extension_loaded('gd')) {
            Log::info('Ekstensi PHP-GD tidak aktif, kompresi berkas foto komplain dilewati.', [
                'complaint_id' => $complaint->id,
                'path' => $photoPath,
            ]);

            return;
        }

        try {
            $imageInfo = @getimagesize($fullPath);
            if (! $imageInfo) {
                return;
            }

            [$width, $height, $type] = $imageInfo;

            // Jika ukuran lebar lebih besar dari 1280px, kecilkan secara proporsional
            $maxWidth = 1280;
            $maxHeight = 1280;

            $newWidth = $width;
            $newHeight = $height;

            if ($width > $maxWidth || $height > $maxHeight) {
                $ratio = min($maxWidth / $width, $maxHeight / $height);
                $newWidth = (int) round($width * $ratio);
                $newHeight = (int) round($height * $ratio);
            }

            $sourceImage = match ($type) {
                IMAGETYPE_JPEG => @imagecreatefromjpeg($fullPath),
                IMAGETYPE_PNG => @imagecreatefrompng($fullPath),
                IMAGETYPE_WEBP => function_exists('imagecreatefromwebp') ? @imagecreatefromwebp($fullPath) : null,
                default => null,
            };

            if (! $sourceImage) {
                return;
            }

            $targetImage = imagecreatetruecolor($newWidth, $newHeight);

            // Pertahankan transparansi PNG / WEBP
            if (in_array($type, [IMAGETYPE_PNG, IMAGETYPE_WEBP], true)) {
                imagealphablending($targetImage, false);
                imagesavealpha($targetImage, true);
                $transparent = imagecolorallocatealpha($targetImage, 255, 255, 255, 127);
                imagefilledrectangle($targetImage, 0, 0, $newWidth, $newHeight, $transparent);
            }

            imagecopyresampled(
                $targetImage,
                $sourceImage,
                0,
                0,
                0,
                0,
                $newWidth,
                $newHeight,
                $width,
                $height
            );

            // Simpan gambar terkompresi dengan kualitas optimal (80%)
            match ($type) {
                IMAGETYPE_JPEG => imagejpeg($targetImage, $fullPath, 80),
                IMAGETYPE_PNG => imagepng($targetImage, $fullPath, 6),
                IMAGETYPE_WEBP => function_exists('imagewebp') ? imagewebp($targetImage, $fullPath, 80) : null,
                default => null,
            };

            imagedestroy($sourceImage);
            imagedestroy($targetImage);

            Log::info('Berhasil mengompresi berkas foto komplain.', [
                'complaint_id' => $complaint->id,
                'path' => $photoPath,
                'dimensions' => "{$newWidth}x{$newHeight}",
            ]);
        } catch (Throwable $e) {
            Log::warning('Gagal mengompresi foto komplain: '.$e->getMessage(), [
                'complaint_id' => $complaint->id,
            ]);
        }
    }
}
