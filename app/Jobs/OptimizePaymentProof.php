<?php

namespace App\Jobs;

use App\Models\Payment;
use App\Services\ImageOptimizer;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class OptimizePaymentProof implements ShouldQueue
{
    use Queueable;

    /**
     * Jumlah maksimal percobaan job.
     */
    public int $tries = 3;

    /**
     * Jeda (detik) sebelum percobaan ulang.
     *
     * @var list<int>
     */
    public array $backoff = [10, 60];

    /**
     * Batas waktu eksekusi job (detik).
     */
    public int $timeout = 60;

    /**
     * Job hanya membawa ID pembayaran agar payload antrean tetap ringan.
     */
    public function __construct(
        public int $paymentId
    ) {}

    /**
     * Kompres dan perkecil gambar bukti transfer/QRIS agar pratinjau kasir ringan dimuat.
     */
    public function handle(): void
    {
        $payment = Payment::find($this->paymentId);

        if (! $payment || ! $payment->proof_file) {
            return;
        }

        $dimensions = ImageOptimizer::optimize(Payment::PROOF_DISK, $payment->proof_file);

        if ($dimensions === null) {
            Log::info('Bukti pembayaran tidak dapat dioptimasi, berkas asli dipertahankan.', [
                'payment_id' => $payment->id,
                'path' => $payment->proof_file,
            ]);

            return;
        }

        Log::info('Berhasil mengoptimasi bukti pembayaran.', [
            'payment_id' => $payment->id,
            'path' => $payment->proof_file,
            'dimensions' => $dimensions,
        ]);
    }
}
