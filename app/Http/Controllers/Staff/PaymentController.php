<?php

namespace App\Http\Controllers\Staff;

use App\Enums\PaymentStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\StorePaymentRequest;
use App\Models\Transaction;
use App\Services\RecordPayment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Validation\ValidationException;
use InvalidArgumentException;

class PaymentController extends Controller
{
    /**
     * Catat pembayaran baru untuk suatu transaksi.
     */
    public function store(StorePaymentRequest $request, Transaction $transaction): JsonResponse|RedirectResponse
    {
        $validated = $request->validated();

        try {
            $payment = RecordPayment::execute(
                transaction: $transaction,
                amount: (float) $validated['amount'],
                method: (string) $validated['method'],
                userId: (int) auth()->id(),
                cashReceived: isset($validated['cash_received']) ? (float) $validated['cash_received'] : null
            );
        } catch (InvalidArgumentException $e) {
            throw ValidationException::withMessages([
                'amount' => [$e->getMessage()],
            ]);
        }

        $formattedAmount = number_format($payment->amount, 0, ',', '.');
        $successMessage = "Pembayaran sebesar Rp {$formattedAmount} ({$payment->method}) berhasil dicatat.";

        if ($request->expectsJson()) {
            $transaction->refresh();
            $transaction->load('payments');

            $paymentStatusValue = $transaction->payment_status instanceof PaymentStatus
                ? $transaction->payment_status->value
                : (string) $transaction->payment_status;

            return response()->json([
                'success' => true,
                'message' => $successMessage,
                'transaction' => [
                    'id' => $transaction->id,
                    'paid_amount' => (float) $transaction->total_paid,
                    'paid_formatted' => 'Rp '.number_format($transaction->total_paid, 0, ',', '.'),
                    'remaining_amount' => (float) $transaction->remaining_amount,
                    'remaining_formatted' => 'Rp '.number_format($transaction->remaining_amount, 0, ',', '.'),
                    'payment_status' => $paymentStatusValue,
                    'payments' => $transaction->payments->map(fn ($p) => [
                        'id' => $p->id,
                        'amount_formatted' => 'Rp '.number_format($p->amount, 0, ',', '.'),
                        'method' => $p->method ?? 'Tunai',
                        'created_at' => $p->paid_at?->format('d M Y, H:i') ?? $p->created_at?->format('d M Y, H:i'),
                        'status' => $p->verification_status ?? 'Terverifikasi',
                    ])->values()->all(),
                ],
            ]);
        }

        return redirect()->back()->with('success', $successMessage);
    }
}
