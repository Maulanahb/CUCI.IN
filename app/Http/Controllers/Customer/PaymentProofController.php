<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Http\Requests\Customer\StorePaymentProofRequest;
use App\Models\Payment;
use App\Models\Transaction;
use App\Models\User;
use App\Services\SubmitPaymentProof;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use InvalidArgumentException;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PaymentProofController extends Controller
{
    /**
     * Simpan bukti transfer/QRIS yang diunggah pelanggan untuk diverifikasi kasir.
     *
     * @throws ValidationException
     */
    public function store(StorePaymentProofRequest $request, Transaction $transaction): RedirectResponse
    {
        $validated = $request->validated();

        try {
            $payment = SubmitPaymentProof::execute(
                transaction: $transaction,
                amount: (float) $validated['amount'],
                method: (string) $validated['method'],
                proofFile: $request->file('proof_file'),
                userId: (int) $request->user()->id,
            );
        } catch (InvalidArgumentException $e) {
            throw ValidationException::withMessages([
                'amount' => [$e->getMessage()],
            ]);
        }

        $formattedAmount = number_format((float) $payment->amount, 0, ',', '.');

        return redirect()
            ->route('customer.transactions.index', ['selected' => $transaction->transaction_code])
            ->with('status', "Bukti pembayaran Rp {$formattedAmount} sudah terkirim. Kasir akan mengeceknya secepatnya, ya!");
    }

    /**
     * Tampilkan berkas bukti pembayaran milik pelanggan yang sedang login.
     */
    public function show(Payment $payment): StreamedResponse
    {
        /** @var User $user */
        $user = auth()->user();
        $customerId = $user->customer?->id;

        if ($customerId === null || $payment->transaction?->customer_id !== $customerId) {
            abort(403, 'Anda tidak memiliki hak akses untuk melihat bukti pembayaran ini.');
        }

        if (! $payment->proof_file || ! Storage::disk(Payment::PROOF_DISK)->exists($payment->proof_file)) {
            abort(404, 'Berkas bukti pembayaran tidak ditemukan.');
        }

        return response()->stream(function () use ($payment): void {
            $stream = Storage::disk(Payment::PROOF_DISK)->readStream($payment->proof_file);

            if ($stream !== false) {
                fpassthru($stream);
                fclose($stream);
            }
        }, 200, ['Content-Type' => 'application/octet-stream']);
    }
}
