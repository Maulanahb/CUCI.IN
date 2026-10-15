<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePaymentRequest;
use App\Models\Transaction;
use App\Services\RecordPayment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Validation\ValidationException;
use InvalidArgumentException;

class PaymentController extends Controller
{
    /**
     * Catat pembayaran baru untuk suatu transaksi.
     */
    public function store(StorePaymentRequest $request, Transaction $transaction): RedirectResponse
    {
        $validated = $request->validated();

        try {
            $payment = RecordPayment::execute(
                transaction: $transaction,
                amount: (float) $validated['amount'],
                method: (string) $validated['method'],
                userId: (int) auth()->id(),
                paymentType: $validated['payment_type'] ?? null
            );
        } catch (InvalidArgumentException $e) {
            throw ValidationException::withMessages([
                'amount' => [$e->getMessage()],
            ]);
        }

        $formattedAmount = number_format($payment->amount, 0, ',', '.');

        return redirect()->back()->with(
            'success',
            "Pembayaran sebesar Rp {$formattedAmount} ({$payment->method}) berhasil dicatat."
        );
    }
}
