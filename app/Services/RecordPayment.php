<?php

namespace App\Services;

use App\Models\Payment;
use App\Models\Transaction;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class RecordPayment
{
    /**
     * Catat pembayaran transaksi secara atomik dan tentukan tipe pembayaran otomatis jika tidak diberikan.
     *
     * @throws InvalidArgumentException
     */
    public static function execute(
        Transaction $transaction,
        float $amount,
        string $method,
        int $userId,
        ?string $paymentType = null,
        ?string $proofFile = null
    ): Payment {
        if ($amount <= 0) {
            throw new InvalidArgumentException('Nominal pembayaran harus lebih besar dari 0.');
        }

        $remaining = $transaction->remaining_amount;

        if ($remaining <= 0) {
            throw new InvalidArgumentException('Transaksi ini sudah lunas, tidak dapat menerima pembayaran lagi.');
        }

        if ($amount > $remaining) {
            $formattedRemaining = number_format($remaining, 0, ',', '.');
            throw new InvalidArgumentException('Nominal pembayaran (Rp '.number_format($amount, 0, ',', '.').") melebihi sisa tagihan (Rp {$formattedRemaining}).");
        }

        if (! in_array($method, ['Tunai', 'Transfer', 'QRIS'], true)) {
            throw new InvalidArgumentException("Metode pembayaran '{$method}' tidak valid.");
        }

        // Tentukan tipe pembayaran otomatis jika belum diset
        if (! $paymentType) {
            $totalPaidBefore = $transaction->total_paid;
            if ($totalPaidBefore == 0 && $amount >= (float) $transaction->total_amount) {
                $paymentType = 'Pembayaran Penuh';
            } elseif ($amount >= $remaining) {
                $paymentType = 'Pelunasan';
            } else {
                $paymentType = 'DP';
            }
        }

        // Sesuai FR-13: Tunai tidak memerlukan verifikasi. Untuk transaksi kasir langsung, default Tidak Diperlukan
        $verificationStatus = 'Tidak Diperlukan';

        return DB::transaction(function () use ($transaction, $amount, $paymentType, $method, $proofFile, $verificationStatus, $userId): Payment {
            $payment = Payment::create([
                'transaction_id' => $transaction->id,
                'amount' => $amount,
                'payment_type' => $paymentType,
                'method' => $method,
                'proof_file' => $proofFile,
                'verification_status' => $verificationStatus,
                'paid_at' => now(),
                'created_by' => $userId,
            ]);

            $transaction->refresh();

            return $payment;
        });
    }
}
