<?php

namespace App\Services;

use App\Enums\TransactionStatus;
use App\Jobs\OptimizePaymentProof;
use App\Models\Payment;
use App\Models\Transaction;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use InvalidArgumentException;
use Throwable;

class SubmitPaymentProof
{
    /**
     * Simpan bukti transfer/QRIS unggahan pelanggan sebagai pembayaran berstatus "Menunggu Verifikasi".
     *
     * Nominal tidak dihitung sebagai uang masuk sampai kasir menyetujuinya.
     *
     * @throws InvalidArgumentException
     */
    public static function execute(
        Transaction $transaction,
        float $amount,
        string $method,
        UploadedFile $proofFile,
        int $userId
    ): Payment {
        if ($amount <= 0) {
            throw new InvalidArgumentException('Nominal pembayaran harus lebih besar dari 0.');
        }

        if (! in_array($method, Payment::PROOF_METHODS, true)) {
            throw new InvalidArgumentException('Bukti pembayaran hanya dapat diunggah untuk metode Transfer atau QRIS.');
        }

        $proofPath = $proofFile->store(Payment::PROOF_DIRECTORY, Payment::PROOF_DISK);

        if ($proofPath === false) {
            throw new InvalidArgumentException('Bukti pembayaran gagal disimpan. Silakan coba lagi.');
        }

        try {
            return DB::transaction(function () use ($transaction, $amount, $method, $proofPath, $userId): Payment {
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

                if ($lockedTransaction->remaining_amount <= 0) {
                    throw new InvalidArgumentException('Transaksi ini sudah lunas, tidak perlu mengunggah bukti pembayaran.');
                }

                // Bukti lain yang belum diverifikasi ikut mengurangi batas nominal agar total pengajuan tidak melebihi tagihan.
                $submittable = $lockedTransaction->submittable_amount;

                if ($submittable <= 0) {
                    throw new InvalidArgumentException('Masih ada bukti pembayaran yang menunggu verifikasi kasir untuk sisa tagihan ini.');
                }

                if ($amount > $submittable) {
                    throw new InvalidArgumentException('Nominal pembayaran (Rp '.number_format($amount, 0, ',', '.').') melebihi sisa tagihan yang dapat diajukan (Rp '.number_format($submittable, 0, ',', '.').').');
                }

                $payment = Payment::create([
                    'transaction_id' => $lockedTransaction->id,
                    'amount' => $amount,
                    'payment_type' => RecordPayment::determinePaymentType($lockedTransaction, $amount),
                    'method' => $method,
                    'proof_file' => $proofPath,
                    'verification_status' => Payment::PENDING_STATUS,
                    'paid_at' => now(),
                    'created_by' => $userId,
                ]);

                OptimizePaymentProof::dispatch($payment->id)->afterCommit();

                return $payment;
            });
        } catch (Throwable $e) {
            // Berkas yang sudah tersimpan dibersihkan agar tidak menjadi sampah ketika pengajuan gagal.
            Storage::disk(Payment::PROOF_DISK)->delete($proofPath);

            throw $e;
        }
    }
}
