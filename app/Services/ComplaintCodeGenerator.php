<?php

namespace App\Services;

use App\Models\Complaint;
use Illuminate\Support\Facades\DB;

class ComplaintCodeGenerator
{
    /**
     * Generate the next sequential complaint code (e.g. CMP-0001).
     *
     * Protected against race conditions in PostgreSQL using advisory transaction locks
     * and pessimistic row locking, avoiding naive count()+1 calculations.
     */
    public static function generate(): string
    {
        return DB::transaction(function (): string {
            // Protect against concurrent generations in PostgreSQL
            if (DB::getDriverName() === 'pgsql') {
                DB::statement("SELECT pg_advisory_xact_lock(hashtext('complaint_code_generator'))");
            }

            $lastComplaint = Complaint::query()
                ->where('complaint_code', 'LIKE', 'CMP-%')
                ->orderByDesc('id')
                ->lockForUpdate()
                ->first();

            $nextNumber = 1;

            if ($lastComplaint !== null && preg_match('/^CMP-(\d+)$/', $lastComplaint->complaint_code, $matches) === 1) {
                $nextNumber = ((int) $matches[1]) + 1;
            }

            $code = sprintf('CMP-%04d', $nextNumber);

            while (Complaint::where('complaint_code', $code)->exists()) {
                $nextNumber++;
                $code = sprintf('CMP-%04d', $nextNumber);
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
