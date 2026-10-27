<?php

namespace App\Services;

use App\Enums\TransactionStatus;
use App\Models\Payment;
use App\Models\Transaction;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class VerifyPayment
{
    public const APPROVE = 'Terverifikasi';

    public const REJECT = 'Ditolak';

    /**
     * Setujui atau tolak bukti pembayaran Transfer/QRIS secara atomik.
     *
     * @throws InvalidArgumentException
     */
    public static function execute(Payment $payment, string $decision, int $verifierId, ?string $notes = null): Payment
    {
        if (! in_array($decision, [self::APPROVE, self::REJECT], true)) {
            throw new InvalidArgumentException("Keputusan verifikasi '{$decision}' tidak valid.");
        }

        $notes = $notes !== null && trim($notes) !== '' ? trim($notes) : null;

        if ($decision === self::REJECT && $notes === null) {
            throw new InvalidArgumentException('Alasan penolakan wajib diisi agar pelanggan tahu apa yang perlu diperbaiki.');
        }

        return DB::transaction(function () use ($payment, $decision, $verifierId, $notes): Payment {
            // Urutan kunci sama dengan RecordPayment (transaksi dahulu) untuk menghindari deadlock.
            $lockedTransaction = Transaction::query()
                ->whereKey($payment->transaction_id)
                ->lockForUpdate()
                ->firstOrFail();

            $lockedPayment = Payment::query()
                ->whereKey($payment->id)
                ->lockForUpdate()
                ->firstOrFail();

            if (! $lockedPayment->isPendingVerification()) {
                throw new InvalidArgumentException("Pembayaran ini sudah diproses sebelumnya dengan status {$lockedPayment->verification_status}.");
            }

            if ($decision === self::APPROVE) {
                $status = $lockedTransaction->status instanceof TransactionStatus
                    ? $lockedTransaction->status
                    : TransactionStatus::from((string) $lockedTransaction->status);

                if ($status === TransactionStatus::Dibatalkan) {
                    throw new InvalidArgumentException('Transaksi sudah dibatalkan. Tolak bukti ini dan proses pengembalian dana secara manual.');
                }

                $amount = (float) $lockedPayment->amount;
                $remaining = $lockedTransaction->remaining_amount;

                if ($amount > $remaining) {
                    throw new InvalidArgumentException('Nominal bukti (Rp '.number_format($amount, 0, ',', '.').') melebihi sisa tagihan saat ini (Rp '.number_format($remaining, 0, ',', '.').'). Tolak bukti ini atau hubungi pelanggan.');
                }

                // Jenis pembayaran dihitung ulang karena pembayaran lain bisa saja masuk setelah bukti diunggah.
                $lockedPayment->payment_type = RecordPayment::determinePaymentType($lockedTransaction, $amount);
            }

            $lockedPayment->fill([
                'verification_status' => $decision,
                'verification_notes' => $notes,
                'verified_by' => $verifierId,
                'verified_at' => now(),
            ])->save();

            return $lockedPayment;
        });
    }
}
