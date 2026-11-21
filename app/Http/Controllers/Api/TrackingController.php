<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\TransactionResource;
use App\Models\Transaction;
use Illuminate\Http\JsonResponse;

class TrackingController extends Controller
{
    /**
     * Melacak status cucian berdasarkan kode nota.
     */
    public function show(string $transaction_code): JsonResponse
    {
        $transaction = Transaction::query()
            ->with([
                'customer',
                'details.service',
                'payments' => fn ($query) => $query->latest('id'),
            ])
            ->whereRaw(
                'UPPER(transaction_code) = ?',
                [mb_strtoupper($transaction_code)]
            )
            ->first();

        // Jika kode nota tidak ditemukan
        if (! $transaction) {
            return response()->json([
                'success' => false,
                'message' => 'Transaksi tidak ditemukan.',
            ], 404);
        }

        // Jika kode nota ditemukan, bentuk response via TransactionResource
        return response()->json([
            'success' => true,
            'message' => 'Data transaksi berhasil ditemukan.',
            'data' => new TransactionResource($transaction),
        ], 200);
    }
}
