<?php

namespace Tests\Feature;

use App\Enums\TransactionStatus;
use App\Models\Customer;
use App\Models\Transaction;
use App\Models\User;
use App\Services\Reports\DateRange;
use App\Services\Reports\TransactionSummary;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class TransactionSummaryTest extends TestCase
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

    public function test_calculates_total_count_and_omset_excluding_cancelled(): void
    {
        $range = DateRange::make('2026-10-01', '2026-10-10');

        // Transaksi aktif berbagai status
        Transaction::factory()->create([
            'customer_id' => $this->customer->id,
            'created_by' => $this->user->id,
            'total_amount' => 50000,
            'status' => TransactionStatus::Diterima,
            'created_at' => '2026-10-02 10:00:00',
        ]);

        Transaction::factory()->create([
            'customer_id' => $this->customer->id,
            'created_by' => $this->user->id,
            'total_amount' => 35000,
            'status' => TransactionStatus::Diproses,
            'created_at' => '2026-10-03 12:00:00',
        ]);

        Transaction::factory()->create([
            'customer_id' => $this->customer->id,
            'created_by' => $this->user->id,
            'total_amount' => 25000,
            'status' => TransactionStatus::Selesai,
            'created_at' => '2026-10-04 14:00:00',
        ]);

        // Transaksi Dibatalkan — harus dikecualikan dari omset, tapi dihitung pada total_count
        Transaction::factory()->create([
            'customer_id' => $this->customer->id,
            'created_by' => $this->user->id,
            'total_amount' => 100000,
            'status' => TransactionStatus::Dibatalkan,
            'created_at' => '2026-10-05 09:00:00',
        ]);

        $summary = TransactionSummary::forRange($range);

        $this->assertSame(4, $summary->totalCount());
        $this->assertSame(3, $summary->activeCount());
        $this->assertSame(1, $summary->cancelledCount());
        $this->assertSame(110000.0, $summary->totalOmset());

        $statusCounts = $summary->statusCounts();
        $this->assertSame(1, $statusCounts[TransactionStatus::Diterima->value]);
        $this->assertSame(1, $statusCounts[TransactionStatus::Diproses->value]);
        $this->assertSame(1, $statusCounts[TransactionStatus::Selesai->value]);
        $this->assertSame(1, $statusCounts[TransactionStatus::Dibatalkan->value]);
        $this->assertSame(0, $statusCounts[TransactionStatus::Diambil->value]);
    }

    public function test_status_counts_zero_fills_all_defined_statuses(): void
    {
        $range = DateRange::make('2026-10-01', '2026-10-05');

        // Hanya buat 1 transaksi dengan status 'Diproses'
        Transaction::factory()->create([
            'customer_id' => $this->customer->id,
            'created_by' => $this->user->id,
            'total_amount' => 15000,
            'status' => TransactionStatus::Diproses,
            'created_at' => '2026-10-02 11:00:00',
        ]);

        $summary = TransactionSummary::forRange($range);
        $statusCounts = $summary->statusCounts();

        // Seluruh nilai TransactionStatus harus ada sebagai key di array
        foreach (TransactionStatus::cases() as $case) {
            $this->assertArrayHasKey($case->value, $statusCounts);
        }

        $this->assertSame(1, $statusCounts['Diproses']);
        $this->assertSame(0, $statusCounts['Diterima']);
        $this->assertSame(0, $statusCounts['Selesai']);
        $this->assertSame(0, $statusCounts['Diambil']);
        $this->assertSame(0, $statusCounts['Dibatalkan']);
    }

    public function test_filters_strictly_by_date_range_including_235959(): void
    {
        $range = DateRange::make('2026-10-05', '2026-10-10');

        // Tepat di batas awal hari (00:00:00) -> masuk
        Transaction::factory()->create([
            'customer_id' => $this->customer->id,
            'created_by' => $this->user->id,
            'total_amount' => 10000,
            'status' => TransactionStatus::Selesai,
            'created_at' => '2026-10-05 00:00:00',
        ]);

        // Tepat di batas akhir hari (23:59:59) -> masuk
        Transaction::factory()->create([
            'customer_id' => $this->customer->id,
            'created_by' => $this->user->id,
            'total_amount' => 20000,
            'status' => TransactionStatus::Selesai,
            'created_at' => '2026-10-10 23:59:59',
        ]);

        // 1 detik sebelum batas awal -> TIDAK masuk
        Transaction::factory()->create([
            'customer_id' => $this->customer->id,
            'created_by' => $this->user->id,
            'total_amount' => 99000,
            'status' => TransactionStatus::Selesai,
            'created_at' => '2026-10-04 23:59:59',
        ]);

        // 1 detik setelah batas akhir -> TIDAK masuk
        Transaction::factory()->create([
            'customer_id' => $this->customer->id,
            'created_by' => $this->user->id,
            'total_amount' => 88000,
            'status' => TransactionStatus::Selesai,
            'created_at' => '2026-10-11 00:00:00',
        ]);

        $summary = TransactionSummary::forRange($range);

        $this->assertSame(2, $summary->totalCount());
        $this->assertSame(30000.0, $summary->totalOmset());
    }

    public function test_empty_range_returns_zeroes(): void
    {
        $range = DateRange::make('2026-01-01', '2026-01-02');
        $summary = TransactionSummary::forRange($range);

        $this->assertSame(0, $summary->totalCount());
        $this->assertSame(0, $summary->activeCount());
        $this->assertSame(0, $summary->cancelledCount());
        $this->assertSame(0.0, $summary->totalOmset());

        foreach (TransactionStatus::cases() as $case) {
            $this->assertSame(0, $summary->statusCounts()[$case->value]);
        }
    }

    public function test_executes_single_aggregation_query(): void
    {
        $range = DateRange::make('2026-10-01', '2026-10-05');

        Transaction::factory()
            ->count(3)
            ->sequence(fn ($seq) => ['transaction_code' => 'TRX-TEST-'.$seq->index])
            ->create([
                'customer_id' => $this->customer->id,
                'created_by' => $this->user->id,
                'created_at' => '2026-10-02 12:00:00',
            ]);

        DB::flushQueryLog();
        DB::enableQueryLog();

        $summary = TransactionSummary::forRange($range);

        // Panggil beberapa accessor sekaligus
        $summary->totalCount();
        $summary->totalOmset();
        $summary->statusCounts();
        $summary->activeCount();
        $summary->cancelledCount();

        $queries = DB::getQueryLog();
        DB::disableQueryLog();

        // Memastikan hanya 1 query database yang dijalankan (agregasi SQL tingkat database)
        $this->assertCount(1, $queries);
        $this->assertStringContainsString('group by', strtolower($queries[0]['query']));
    }

    public function test_to_array_structure(): void
    {
        $range = DateRange::make('2026-10-01', '2026-10-05');

        Transaction::factory()->create([
            'customer_id' => $this->customer->id,
            'created_by' => $this->user->id,
            'total_amount' => 50000,
            'status' => TransactionStatus::Diterima,
            'created_at' => '2026-10-02 10:00:00',
        ]);

        $summary = TransactionSummary::forRange($range);
        $array = $summary->toArray();

        $this->assertArrayHasKey('total_count', $array);
        $this->assertArrayHasKey('total_omset', $array);
        $this->assertArrayHasKey('active_count', $array);
        $this->assertArrayHasKey('cancelled_count', $array);
        $this->assertArrayHasKey('status_counts', $array);
        $this->assertSame(1, $array['total_count']);
        $this->assertSame(50000.0, $array['total_omset']);
    }
}
