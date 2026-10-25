<?php

namespace App\Http\Controllers\Staff;

use App\Enums\PaymentStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\StorePaymentRequest;
use App\Models\Payment;
use App\Models\Transaction;
use App\Services\RecordPayment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use InvalidArgumentException;

class PaymentController extends Controller
{
    /**
     * Menampilkan daftar pembayaran.
     */
    public function index(Request $request)
    {
        $query = Payment::with([
            'transaction.customer',
            'creator',
            'verifier',
        ])->latest();

        // Pencarian kode transaksi atau nama pelanggan.
        if ($request->filled('search')) {
            $search = $request->input('search');

            $query->where(function ($q) use ($search) {
                $q->whereHas('transaction', function ($transactionQuery) use ($search) {
                    $transactionQuery
                        ->where('transaction_code', 'like', "%{$search}%")
                        ->orWhereHas('customer', function ($customerQuery) use ($search) {
                            $customerQuery->where('name', 'like', "%{$search}%");
                        });
                });
            });
        }

        // Filter status verifikasi.
        if ($request->filled('verification_status')) {
            $query->where(
                'verification_status',
                $request->input('verification_status')
            );
        }

        // Filter metode pembayaran.
        if ($request->filled('method')) {
            $query->where('method', $request->input('method'));
        }

        $payments = $query->paginate(10)->withQueryString();

        return view('staff.payments.index', compact('payments'));
    }

    /**
     * Menampilkan detail pembayaran.
     */
    public function show(Payment $payment)
    {
        $payment->load([
            'transaction.customer',
            'transaction.details.service',
            'creator',
            'verifier',
        ]);

        return view('staff.payments.show', compact('payment'));
    }

    /**
     * Mencatat pembayaran baru dan mengunggah bukti jika diperlukan.
     */
    public function store(
        StorePaymentRequest $request,
        Transaction $transaction
    ): JsonResponse|RedirectResponse {
        $validated = $request->validated();

        try {
            $payment = RecordPayment::execute(
                transaction: $transaction,
                amount: (float) $validated['amount'],
                method: (string) $validated['method'],
                userId: (int) auth()->id(),
                cashReceived: isset($validated['cash_received'])
                    ? (float) $validated['cash_received']
                    : null
            );

            // Bukti Transfer/QRIS disimpan dan menunggu verifikasi.
            if (
                $request->hasFile('proof_file')
                && in_array($validated['method'], ['Transfer', 'QRIS'], true)
            ) {
                $path = $request->file('proof_file')->store(
                    'payments/proofs',
                    'public'
                );

                $payment->update([
                    'proof_file' => $path,
                    'verification_status' => 'Menunggu Verifikasi',
                ]);
            }
        } catch (InvalidArgumentException $e) {
            throw ValidationException::withMessages([
                'amount' => [$e->getMessage()],
            ]);
        }

        $formattedAmount = number_format(
            (float) $payment->amount,
            0,
            ',',
            '.'
        );

        $successMessage = "Pembayaran sebesar Rp {$formattedAmount} ({$payment->method}) berhasil dicatat.";

        // Jika dipanggil melalui AJAX/API, kembalikan respons JSON.
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
                    'paid_formatted' => 'Rp '.number_format(
                        (float) $transaction->total_paid,
                        0,
                        ',',
                        '.'
                    ),
                    'remaining_amount' => (float) $transaction->remaining_amount,
                    'remaining_formatted' => 'Rp '.number_format(
                        (float) $transaction->remaining_amount,
                        0,
                        ',',
                        '.'
                    ),
                    'payment_status' => $paymentStatusValue,
                    'payments' => $transaction->payments->map(fn ($p) => [
                        'id' => $p->id,
                        'amount_formatted' => 'Rp '.number_format(
                            (float) $p->amount,
                            0,
                            ',',
                            '.'
                        ),
                        'method' => $p->method ?? 'Tunai',
                        'created_at' => $p->paid_at?->format('d M Y, H:i')
                            ?? $p->created_at?->format('d M Y, H:i'),
                        'status' => $p->verification_status ?? 'Terverifikasi',
                    ])->values()->all(),
                ],
            ]);
        }

        return redirect()->back()->with('success', $successMessage);
    }

    /**
     * Memverifikasi atau menolak pembayaran non-tunai.
     */
    public function verify(
        Request $request,
        Payment $payment
    ): RedirectResponse {
        $validated = $request->validate([
            'verification_status' => [
                'required',
                'in:Terverifikasi,Ditolak',
            ],
        ]);

        $payment->update([
            'verification_status' => $validated['verification_status'],
            'verified_by' => auth()->id(),
            'verified_at' => now(),
        ]);

        $status = $validated['verification_status'];

        return redirect()->back()->with(
            'success',
            "Pembayaran berhasil diproses dengan status {$status}."
        );
    }
}