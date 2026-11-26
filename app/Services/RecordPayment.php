<?php

namespace App\Services;

use App\Enums\TransactionStatus;
use App\Models\Payment;
use App\Models\Transaction;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class RecordPayment
{
    /**
     * Catat pembayaran transaksi secara atomik dan tentukan tipe pembayaran otomatis jika tidak diberikan.
     *
     * Untuk Transfer/QRIS, $isConfirmed = true berarti kasir sudah memastikan dana masuk sehingga status
     * langsung "Terverifikasi"; $isConfirmed = false menaruh bukti di antrean "Menunggu Verifikasi"
     * agar dicek mutasinya oleh Owner/kasir lewat panel verifikasi.
     *
     * @throws InvalidArgumentException
     */
    public static function execute(
        Transaction $transaction,
        float $amount,
        string $method,
        int $userId,
        ?string $proofFile = null,
        ?float $cashReceived = null,
        bool $isConfirmed = true
    ): Payment {
        if ($amount <= 0) {
            throw new InvalidArgumentException('Nominal pembayaran harus lebih besar dari 0.');
        }

        if (! in_array($method, ['Tunai', 'Transfer', 'QRIS'], true)) {
            throw new InvalidArgumentException("Metode pembayaran '{$method}' tidak valid.");
        }

        $cashReceived = $method === 'Tunai' ? $cashReceived : null;
        $changeAmount = null;

        if ($cashReceived !== null) {
            if ($cashReceived < $amount) {
                throw new InvalidArgumentException('Uang diterima kurang dari nominal pembayaran.');
            }

            $changeAmount = round($cashReceived - $amount, 2);
        }

        return DB::transaction(function () use ($transaction, $amount, $method, $userId, $proofFile, $cashReceived, $changeAmount, $isConfirmed): Payment {
            $lockedTransaction = Transaction::query()
                ->whereKey($transaction->id)
                ->lockForUpdate()
                ->firstOrFail();

            $status = $lockedTransaction->status instanceof TransactionStatus
                ? $lockedTransaction->status
                : TransactionStatus::from((string) $lockedTransaction->status);

            if ($status->isTerminal()) {
                throw new InvalidArgumentException("Transaksi berstatus {$status->value} tidak dapat menerima pembayaran.");
            }

            $remaining = $lockedTransaction->remaining_amount;

            if ($remaining <= 0) {
                throw new InvalidArgumentException('Transaksi ini sudah lunas, tidak dapat menerima pembayaran lagi.');
            }

            if ($amount > $remaining) {
                $formattedRemaining = number_format($remaining, 0, ',', '.');
                throw new InvalidArgumentException('Nominal pembayaran (Rp '.number_format($amount, 0, ',', '.').") melebihi sisa tagihan (Rp {$formattedRemaining}).");
            }

            $paymentType = self::determinePaymentType($lockedTransaction, $amount);

            // Tunai tidak perlu verifikasi. Transfer/QRIS hanya langsung "Terverifikasi" jika kasir
            // secara eksplisit mengonfirmasi dana masuk; selain itu masuk antrean verifikasi.
            $verificationStatus = match (true) {
                $method === 'Tunai' => 'Tidak Diperlukan',
                $isConfirmed => 'Terverifikasi',
                default => Payment::PENDING_STATUS,
            };

            $payment = Payment::create([
                'transaction_id' => $lockedTransaction->id,
                'amount' => $amount,
                'payment_type' => $paymentType,
                'method' => $method,
                'cash_received' => $cashReceived,
                'change_amount' => $changeAmount,
                'proof_file' => $proofFile,
                'verification_status' => $verificationStatus,
                'paid_at' => now(),
                'created_by' => $userId,
            ]);

            return $payment;
        });
    }

    /**
     * Tentukan jenis pembayaran berdasarkan pembayaran valid yang sudah tercatat.
     */
    public static function determinePaymentType(Transaction $transaction, float $amount): string
    {
        $totalPaidBefore = $transaction->total_paid;

        if ($totalPaidBefore == 0 && $amount >= (float) $transaction->total_amount) {
            return 'Pembayaran Penuh';
        }

        if ($amount >= $transaction->remaining_amount) {
            return 'Pelunasan';
        }

        return 'DP';
    }
}