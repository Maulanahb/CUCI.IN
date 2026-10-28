<?php

namespace App\Http\Controllers;

use App\Http\Requests\GuestPaymentProofRequest;
use App\Models\Transaction;
use App\Services\SubmitPaymentProof;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use InvalidArgumentException;

class TransactionLookupController extends Controller
{
    /**
     * Tampilkan halaman pelacakan transaksi publik untuk customer atau guest.
     */
    public function index(Request $request): View
    {
        $code = trim((string) $request->input('code', ''));
        $transaction = null;
        $searched = false;
        $maskedCustomerName = 'Pelanggan Umum';

        $paymentSummary = [
            'total_paid' => 0.0,
            'remaining_amount' => 0.0,
            'status' => 'Belum Bayar',
            'latest_payment' => null,
        ];

        if ($code !== '') {
            $searched = true;

            $transaction = Transaction::query()
                ->with([
                    'customer',
                    'details.service',
                    'payments',
                    'statusHistories.changedBy',
                ])
                ->whereRaw('UPPER(transaction_code) = ?', [mb_strtoupper($code)])
                ->first();

            if ($transaction) {
                $totalPaid = $transaction->total_paid;
                $totalAmount = (float) $transaction->total_amount;
                $remainingAmount = max(0.0, $totalAmount - $totalPaid);
                $pendingAmount = $transaction->pending_verification_amount;
                $submittableAmount = $transaction->submittable_amount;

                $paymentStatus = match (true) {
                    $totalPaid >= $totalAmount && $totalAmount > 0 => 'Lunas',
                    $totalPaid > 0 => 'DP',
                    default => 'Belum Bayar',
                };

                $paymentSummary = [
                    'total_paid' => $totalPaid,
                    'remaining_amount' => $remainingAmount,
                    'pending_amount' => $pendingAmount,
                    'submittable_amount' => $submittableAmount,
                    'status' => $paymentStatus,
                    'latest_payment' => $transaction->payments->filter(fn ($p) => $p->verification_status === 'Terverifikasi' || $p->method === 'Tunai')->last(),
                ];

                if ($transaction->customer?->name) {
                    $maskedCustomerName = collect(preg_split('/\s+/', trim($transaction->customer->name)))
                        ->filter()
                        ->map(fn (string $part): string => mb_substr($part, 0, 1).'***')
                        ->implode(' ');
                }
            }
        }

        return view('lookup.index', [
            'code' => $code,
            'searched' => $searched,
            'transaction' => $transaction,
            'paymentSummary' => $paymentSummary,
            'maskedCustomerName' => $maskedCustomerName,
        ]);
    }

    /**
     * Menerima unggahan bukti transfer/QRIS dari pelanggan umum (guest) via Cek Resi.
     *
     * @throws ValidationException
     */
    public function storePaymentProof(GuestPaymentProofRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $transaction = Transaction::query()
            ->with(['customer', 'payments'])
            ->where('transaction_code', $validated['transaction_code'])
            ->firstOrFail();

        // 1. Verifikasi 4 digit terakhir nomor telepon customer untuk keamanan
        $customerPhone = preg_replace('/\D/', '', (string) ($transaction->customer?->phone ?? ''));
        $last4 = substr($customerPhone, -4);

        if ($customerPhone === '' || $last4 !== $validated['phone_last_4']) {
            throw ValidationException::withMessages([
                'phone_last_4' => ['4 digit terakhir nomor telepon tidak cocok dengan nomor yang terdaftar pada nota.'],
            ]);
        }

        // 2. Delegasikan ke service SubmitPaymentProof
        try {
            $effectiveUserId = (int) ($transaction->customer?->user_id ?? $transaction->created_by);

            $payment = SubmitPaymentProof::execute(
                transaction: $transaction,
                amount: (float) $validated['amount'],
                method: (string) $validated['method'],
                proofFile: $request->file('proof_file'),
                userId: $effectiveUserId,
            );
        } catch (InvalidArgumentException $e) {
            throw ValidationException::withMessages([
                'amount' => [$e->getMessage()],
            ]);
        }

        $formattedAmount = number_format((float) $payment->amount, 0, ',', '.');

        return redirect()
            ->route('transactions.lookup', ['code' => $transaction->transaction_code])
            ->with('payment_proof_success', "Bukti transfer sebesar Rp {$formattedAmount} berhasil dikirim! Kasir kami akan segera memverifikasinya.");
    }
}
