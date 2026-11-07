<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
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
            ->whereRaw(
                'UPPER(transaction_code) = ?',
                [mb_strtoupper($transaction_code)]
            )
            ->first();

        // Jika kode nota tidak ditemukan
        if (!$transaction) {
            return response()->json([
                'success' => false,
                'message' => 'Transaksi tidak ditemukan.',
            ], 404);
        }

        // Jika kode nota ditemukan
        return response()->json([
            'success' => true,
            'message' => 'Data transaksi berhasil ditemukan.',
            'data' => [
                'transaction_code' => $transaction->transaction_code,
                'status' => $transaction->status->value,
                'estimated_completed_at' =>
                    $transaction->estimated_completed_at?->toIso8601String(),
                'last_updated_at' =>
                    $transaction->updated_at?->toIso8601String(),
            ],
        ], 200);
    }
}
