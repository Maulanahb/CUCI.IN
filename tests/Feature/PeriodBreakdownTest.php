<?php

namespace Tests\Feature;

use App\Enums\TransactionStatus;
use App\Models\Customer;
use App\Models\Transaction;
use App\Models\User;
use App\Services\Reports\DateRange;
use App\Services\Reports\PeriodBreakdown;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use Tests\TestCase;

class PeriodBreakdownTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private Customer $customer;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->customer = Customer::factory()->create();
    }

    public function test_daily_zero_fills_days_without_transactions(): void
    {
        $range = DateRange::make('2026-10-01', '2026-10-05');

        // Transaksi hanya di hari ke-1 dan hari ke-4
        Transaction::factory()->create([
            'customer_id' => $this->customer->id,
            'created_by' => $this->user->id,
            'transaction_code' => 'TRX-DAILY-1',
            'total_amount' => 25000,
            'status' => TransactionStatus::Selesai,
            'created_at' => '2026-10-01 10:00:00',
        ]);

        Transaction::factory()->create([
            'customer_id' => $this->customer->id,
            'created_by' => $this->user->id,
            'transaction_code' => 'TRX-DAILY-4',
            'total_amount' => 50000,
            'status' => TransactionStatus::Diproses,
            'created_at' => '2026-10-04 14:00:00',
        ]);

        $breakdown = PeriodBreakdown::forRange($range)->daily();

        // Rentang 5 hari harus menghasilkan tepat 5 item
        $this->assertCount(5, $breakdown);

        // 2026-10-01 (ada transaksi)
        $this->assertSame('2026-10-01', $breakdown[0]['period']);
        $this->assertSame(1, $breakdown[0]['count']);
        $this->assertSame(25000.0, $breakdown[0]['omset']);

        // 2026-10-02 (kosong -> zero-filled)
        $this->assertSame('2026-10-02', $breakdown[1]['period']);
        $this->assertSame(0, $breakdown[1]['count']);
        $this->assertSame(0.0, $breakdown[1]['omset']);

        // 2026-10-03 (kosong -> zero-filled)
        $this->assertSame('2026-10-03', $breakdown[2]['period']);
        $this->assertSame(0, $breakdown[2]['count']);
        $this->assertSame(0.0, $breakdown[2]['omset']);

        // 2026-10-04 (ada transaksi)
        $this->assertSame('2026-10-04', $breakdown[3]['period']);
        $this->assertSame(1, $breakdown[3]['count']);
        $this->assertSame(50000.0, $breakdown[3]['omset']);

        // 2026-10-05 (kosong -> zero-filled)
        $this->assertSame('2026-10-05', $breakdown[4]['period']);
        $this->assertSame(0, $breakdown[4]['count']);
        $this->assertSame(0.0, $breakdown[4]['omset']);
    }

    public function test_daily_excludes_cancelled_transactions_from_omset(): void
    {
        $range = DateRange::make('2026-10-02', '2026-10-02');

        // Transaksi aktif
        Transaction::factory()->create([
            'customer_id' => $this->customer->id,
            'created_by' => $this->user->id,
            'transaction_code' => 'TRX-ACT-1',
            'total_amount' => 30000,
            'status' => TransactionStatus::Diterima,
            'created_at' => '2026-10-02 09:00:00',
        ]);

        // Transaksi dibatalkan (nominal 100.000 tidak boleh masuk omset)
        Transaction::factory()->create([
            'customer_id' => $this->customer->id,
            'created_by' => $this->user->id,
            'transaction_code' => 'TRX-CNCL-1',
            'total_amount' => 100000,
            'status' => TransactionStatus::Dibatalkan,
            'created_at' => '2026-10-02 11:00:00',
        ]);

        $breakdown = PeriodBreakdown::forRange($range)->daily();

        $this->assertCount(1, $breakdown);
        $this->assertSame(1, $breakdown[0]['count']);
        $this->assertSame(2, $breakdown[0]['total_count']);
        $this->assertSame(1, $breakdown[0]['cancelled_count']);
        $this->assertSame(30000.0, $breakdown[0]['omset']);
    }

    public function test_weekly_groups_and_zero_fills_empty_weeks(): void
    {
        // 2026-10-05 (Senin M1) s.d. 2026-10-25 (Minggu M3) -> 3 minggu penuh
        $range = DateRange::make('2026-10-05', '2026-10-25');

        // Transaksi di Minggu 1 (07 Okt)
        Transaction::factory()->create([
            'customer_id' => $this->customer->id,
            'created_by' => $this->user->id,
            'transaction_code' => 'TRX-W1',
            'total_amount' => 40000,
            'status' => TransactionStatus::Selesai,
            'created_at' => '2026-10-07 10:00:00',
        ]);

        // Minggu 2 (12-18 Okt) sengaja dikosongkan

        // Transaksi di Minggu 3 (20 Okt)
        Transaction::factory()->create([
            'customer_id' => $this->customer->id,
            'created_by' => $this->user->id,
            'transaction_code' => 'TRX-W3',
            'total_amount' => 60000,
            'status' => TransactionStatus::Selesai,
            'created_at' => '2026-10-20 15:00:00',
        ]);

        $breakdown = PeriodBreakdown::forRange($range)->weekly();

        $this->assertCount(3, $breakdown);

        // Minggu 1
        $this->assertSame(1, $breakdown[0]['count']);
        $this->assertSame(40000.0, $breakdown[0]['omset']);

        // Minggu 2 (zero-filled)
        $this->assertSame(0, $breakdown[1]['count']);
        $this->assertSame(0.0, $breakdown[1]['omset']);

        // Minggu 3
        $this->assertSame(1, $breakdown[2]['count']);
        $this->assertSame(60000.0, $breakdown[2]['omset']);
    }

    public function test_monthly_groups_and_zero_fills_empty_months(): void
    {
        // Rentang Agustus s.d. Oktober 2026 (3 bulan)
        $range = DateRange::make('2026-08-01', '2026-10-31');

        // Transaksi di Agustus
        Transaction::factory()->create([
            'customer_id' => $this->customer->id,
            'created_by' => $this->user->id,
            'transaction_code' => 'TRX-AUG',
            'total_amount' => 75000,
            'status' => TransactionStatus::Selesai,
            'created_at' => '2026-08-15 10:00:00',
        ]);

        // September sengaja kosong

        // Transaksi di Oktober
        Transaction::factory()->create([
            'customer_id' => $this->customer->id,
            'created_by' => $this->user->id,
            'transaction_code' => 'TRX-OCT',
            'total_amount' => 125000,
            'status' => TransactionStatus::Selesai,
            'created_at' => '2026-10-10 10:00:00',
        ]);

        $breakdown = PeriodBreakdown::forRange($range)->monthly();

        $this->assertCount(3, $breakdown);

        // Agustus
        $this->assertSame('2026-08', $breakdown[0]['period']);
        $this->assertSame(1, $breakdown[0]['count']);
        $this->assertSame(75000.0, $breakdown[0]['omset']);

        // September (zero-filled)
        $this->assertSame('2026-09', $breakdown[1]['period']);
        $this->assertSame(0, $breakdown[1]['count']);
        $this->assertSame(0.0, $breakdown[1]['omset']);

        // Oktober
        $this->assertSame('2026-10', $breakdown[2]['period']);
        $this->assertSame(1, $breakdown[2]['count']);
        $this->assertSame(125000.0, $breakdown[2]['omset']);
    }

    public function test_get_dispatches_correctly_or_throws_on_invalid_interval(): void
    {
        $range = DateRange::make('2026-10-01', '2026-10-02');
        $service = PeriodBreakdown::forRange($range);

        $this->assertCount(2, $service->get('daily'));
        $this->assertNotEmpty($service->get('weekly'));
        $this->assertNotEmpty($service->get('monthly'));

        $this->expectException(InvalidArgumentException::class);
        $service->get('yearly');
    }

    public function test_executes_single_sql_query_for_aggregation(): void
    {
        $range = DateRange::make('2026-10-01', '2026-10-05');

        Transaction::factory()->create([
            'customer_id' => $this->customer->id,
            'created_by' => $this->user->id,
            'transaction_code' => 'TRX-SQL-1',
            'created_at' => '2026-10-02 10:00:00',
        ]);

        DB::flushQueryLog();
        DB::enableQueryLog();

        $service = PeriodBreakdown::forRange($range);
        $service->daily();
        $service->weekly();
        $service->monthly();

        $queries = DB::getQueryLog();
        DB::disableQueryLog();

        // Hanya 1 query SQL dieksekusi berkat caching hasil query harian
        $this->assertCount(1, $queries);
    }
}
