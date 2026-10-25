<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePaymentRequest;
use App\Models\Payment;
use App\Models\Transaction;
use App\Services\RecordPayment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Storage;
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

        /*
        |--------------------------------------------------------------------------
        | Pencarian
        |--------------------------------------------------------------------------
        */

        if ($request->filled('search')) {
            $search = $request->search;

            $query->where(function ($q) use ($search) {
                $q->whereHas('transaction', function ($transactionQuery) use ($search) {
                    $transactionQuery
                        ->where('transaction_code', 'like', "%{$search}%")
                        ->orWhereHas('customer', function ($customerQuery) use ($search) {
                            $customerQuery->where(
                                'name',
                                'like',
                                "%{$search}%"
                            );
                        });
                });
            });
        }

        /*
        |--------------------------------------------------------------------------
        | Filter Status Verifikasi
        |--------------------------------------------------------------------------
        */

        if ($request->filled('verification_status')) {
            $query->where(
                'verification_status',
                $request->verification_status
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Filter Metode Pembayaran
        |--------------------------------------------------------------------------
        */

        if ($request->filled('method')) {
            $query->where('method', $request->input('method'));
        }

        $payments = $query
            ->paginate(10)
            ->withQueryString();

        return view(
            'staff.payments.index',
            compact('payments')
        );
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

        return view(
            'staff.payments.show',
            compact('payment')
        );
    }

    /**
     * Catat pembayaran baru.
     *
     * Pembayaran:
     * - Tunai
     * - Transfer
     * - QRIS
     *
     * Transfer dan QRIS dapat menyertakan
     * bukti pembayaran digital.
     */
    public function store(
        StorePaymentRequest $request,
        Transaction $transaction
    ): RedirectResponse {
        $validated = $request->validated();

        try {

            /*
            |--------------------------------------------------------------------------
            | Catat pembayaran menggunakan service yang sudah ada
            |--------------------------------------------------------------------------
            */

            $payment = RecordPayment::execute(
                transaction: $transaction,
                amount: (float) $validated['amount'],
                method: (string) $validated['method'],
                userId: (int) auth()->id(),
                paymentType: $validated['payment_type'] ?? null
            );

            /*
            |--------------------------------------------------------------------------
            | Upload bukti pembayaran
            |--------------------------------------------------------------------------
            */

            if (
                $request->hasFile('proof_file')
                && in_array(
                    $validated['method'],
                    ['Transfer', 'QRIS'],
                    true
                )
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

        $amount = number_format(
        (float) $payment->amount,
        0,
        ',',
        '.'
    );

    $method = $payment->method;

    return redirect()
        ->back()
        ->with(
            'success',
            "Pembayaran sebesar Rp {$amount} ({$method}) berhasil dicatat."
        );
    }

    /**
     * Verifikasi pembayaran non-tunai.
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

        return redirect()
            ->back()
            ->with(
                'success',
                "Pembayaran berhasil diproses dengan status {$status}."
            );
    }
}