<?php

namespace App\Jobs;

use App\Models\Complaint;
use App\Services\ImageOptimizer;
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

        // Pastikan ekstensi GD tersedia untuk manipulasi berkas
        if (! extension_loaded('gd')) {
            Log::info('Ekstensi PHP-GD tidak aktif, kompresi berkas foto komplain dilewati.', [
                'complaint_id' => $complaint->id,
                'path' => $photoPath,
            ]);

            return;
        }

        try {
            $dimensions = ImageOptimizer::optimize('public', $photoPath);

            if ($dimensions === null) {
                Log::info('Foto komplain dilewati saat optimasi (dimensi melebihi batas piksel atau format tidak didukung).', [
                    'complaint_id' => $complaint->id,
                    'path' => $photoPath,
                ]);

                return;
            }

            Log::info('Berhasil mengompresi berkas foto komplain.', [
                'complaint_id' => $complaint->id,
                'path' => $photoPath,
                'dimensions' => $dimensions,
            ]);
        } catch (Throwable $e) {
            Log::warning('Gagal mengompresi foto komplain: '.$e->getMessage(), [
                'complaint_id' => $complaint->id,
            ]);
        }
    }
}
