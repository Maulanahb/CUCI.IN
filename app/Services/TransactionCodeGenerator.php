<?php

namespace App\Services;

use App\Models\Transaction;
use Illuminate\Support\Facades\DB;

class TransactionCodeGenerator
{
    /**
     * Generate the next sequential transaction code (e.g. TRX-0001).
     *
     * Protected against race conditions in PostgreSQL using advisory transaction locks
     * and pessimistic row locking, avoiding naive count()+1 calculations.
     */
    public static function generate(): string
    {
        return DB::transaction(function (): string {
            // Protect against concurrent generations in PostgreSQL
            if (DB::getDriverName() === 'pgsql') {
                DB::statement("SELECT pg_advisory_xact_lock(hashtext('transaction_code_generator'))");
            }

            $lastTransaction = Transaction::query()
                ->where('transaction_code', 'LIKE', 'TRX-%')
                ->orderByDesc('id')
                ->lockForUpdate()
                ->first();

            $nextNumber = 1;

            if ($lastTransaction !== null && preg_match('/^TRX-(\d+)$/', $lastTransaction->transaction_code, $matches) === 1) {
                $nextNumber = ((int) $matches[1]) + 1;
            }

            $code = sprintf('TRX-%04d', $nextNumber);

            while (Transaction::where('transaction_code', $code)->exists()) {
                $nextNumber++;
                $code = sprintf('TRX-%04d', $nextNumber);
            }

            return $code;
        });
    }

    /**
     * Non-static wrapper to allow calling via dependency injection or instance.
     */
    public function getNextCode(): string
    {
        return self::generate();
    }
}
