<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\View\View;

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
                    'payments' => fn ($query) => $query->whereIn('verification_status', ['Terverifikasi', 'Tidak Diperlukan']),
                    'statusHistories.changedBy',
                ])
                ->whereRaw('UPPER(transaction_code) = ?', [mb_strtoupper($code)])
                ->first();

            if ($transaction) {
                $totalPaid = (float) $transaction->payments->sum('amount');
                $totalAmount = (float) $transaction->total_amount;
                $remainingAmount = max(0.0, $totalAmount - $totalPaid);

                $paymentStatus = match (true) {
                    $totalPaid >= $totalAmount && $totalAmount > 0 => 'Lunas',
                    $totalPaid > 0 => 'DP',
                    default => 'Belum Bayar',
                };

                $paymentSummary = [
                    'total_paid' => $totalPaid,
                    'remaining_amount' => $remainingAmount,
                    'status' => $paymentStatus,
                    'latest_payment' => $transaction->payments->last(),
                ];
            }
        }

        return view('lookup.index', [
            'code' => $code,
            'searched' => $searched,
            'transaction' => $transaction,
            'paymentSummary' => $paymentSummary,
        ]);
    }
}
