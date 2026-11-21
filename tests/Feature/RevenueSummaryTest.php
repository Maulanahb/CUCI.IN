<?php

namespace Tests\Feature;

use App\Enums\TransactionStatus;
use App\Models\Customer;
use App\Models\Payment;
use App\Models\Transaction;
use App\Models\User;
use App\Services\Reports\DateRange;
use App\Services\Reports\RevenueSummary;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class RevenueSummaryTest extends TestCase
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

    public function test_calculates_gross_real_and_receivables_correctly(): void
    {
        $range = DateRange::make('2026-10-01', '2026-10-10');

        // Transaksi 1: 100.000 (Selesai), bayar Tunai 100.000 (Lunas, valid)
        $tx1 = Transaction::factory()->create([
            'customer_id' => $this->customer->id,
            'created_by' => $this->user->id,
            'transaction_code' => 'TRX-REV-1',
            'total_amount' => 100000,
            'status' => TransactionStatus::Selesai,
            'created_at' => '2026-10-02 10:00:00',
        ]);
        Payment::factory()->create([
            'transaction_id' => $tx1->id,
            'amount' => 100000,
            'method' => 'Tunai',
            'verification_status' => 'Tidak Diperlukan',
            'paid_at' => '2026-10-02 10:05:00',
            'created_by' => $this->user->id,
        ]);

        // Transaksi 2: 60.000 (Diproses), DP QRIS Terverifikasi 20.000 (valid)
        $tx2 = Transaction::factory()->create([
            'customer_id' => $this->customer->id,
            'created_by' => $this->user->id,
            'transaction_code' => 'TRX-REV-2',
            'total_amount' => 60000,
            'status' => TransactionStatus::Diproses,
            'created_at' => '2026-10-03 11:00:00',
        ]);
        Payment::factory()->verified('QRIS')->create([
            'transaction_id' => $tx2->id,
            'amount' => 20000,
            'paid_at' => '2026-10-03 11:10:00',
            'created_by' => $this->user->id,
        ]);

        // Transaksi 3: 40.000 (Diterima), bayar Transfer Menunggu Verifikasi (BELUM valid)
        $tx3 = Transaction::factory()->create([
            'customer_id' => $this->customer->id,
            'created_by' => $this->user->id,
            'transaction_code' => 'TRX-REV-3',
            'total_amount' => 40000,
            'status' => TransactionStatus::Diterima,
            'created_at' => '2026-10-04 14:00:00',
        ]);
        Payment::factory()->pendingProof('Transfer')->create([
            'transaction_id' => $tx3->id,
            'amount' => 40000,
            'paid_at' => '2026-10-04 14:05:00',
            'created_by' => $this->user->id,
        ]);

        // Transaksi 4: 50.000 (Dibatalkan) — TIDAK boleh dihitung ke omset kotor
        Transaction::factory()->create([
            'customer_id' => $this->customer->id,
            'created_by' => $this->user->id,
            'transaction_code' => 'TRX-REV-4',
            'total_amount' => 50000,
            'status' => TransactionStatus::Dibatalkan,
            'created_at' => '2026-10-05 09:00:00',
        ]);

        $summary = RevenueSummary::forRange($range);

        // Omset Kotor = Tx1(100k) + Tx2(60k) + Tx3(40k) = 200.000 (Tx4 Dibatalkan dikecualikan)
        $this->assertSame(200000.0, $summary->omsetKotor());
        $this->assertSame(200000.0, $summary->grossRevenue());

        // Penerimaan Riil = Tunai(100k) + QRIS Terverifikasi(20k) = 120.000 (Transfer pending tidak masuk)
        $this->assertSame(120000.0, $summary->penerimaanRiil());
        $this->assertSame(120000.0, $summary->realRevenue());

        // Piutang = 200.000 - 120.000 = 80.000
        $this->assertSame(80000.0, $summary->piutang());
        $this->assertSame(80000.0, $summary->receivables());

        // Breakdown Metode Bayar
        $methods = $summary->paymentMethods();
        $this->assertSame(100000.0, $methods['Tunai']);
        $this->assertSame(20000.0, $methods['QRIS']);
        $this->assertSame(0.0, $methods['Transfer']); // Zero-filling untuk Transfer yang belum diverifikasi
    }

    public function test_payment_methods_zero_fills_unrepresented_methods(): void
    {
        $range = DateRange::make('2026-10-01', '2026-10-05');

        $tx = Transaction::factory()->create([
            'customer_id' => $this->customer->id,
            'created_by' => $this->user->id,
            'transaction_code' => 'TRX-MTHD-1',
            'total_amount' => 50000,
            'created_at' => '2026-10-02 10:00:00',
        ]);

        // Hanya ada pembayaran Tunai
        Payment::factory()->create([
            'transaction_id' => $tx->id,
            'amount' => 50000,
            'method' => 'Tunai',
            'verification_status' => 'Tidak Diperlukan',
            'paid_at' => '2026-10-02 10:05:00',
            'created_by' => $this->user->id,
        ]);

        $summary = RevenueSummary::forRange($range);
        $methods = $summary->paymentMethods();

        $this->assertArrayHasKey('Tunai', $methods);
        $this->assertArrayHasKey('Transfer', $methods);
        $this->assertArrayHasKey('QRIS', $methods);

        $this->assertSame(50000.0, $methods['Tunai']);
        $this->assertSame(0.0, $methods['Transfer']);
        $this->assertSame(0.0, $methods['QRIS']);

        $detail = $summary->paymentMethodsDetail();
        $this->assertSame(1, $detail['Tunai']['count']);
        $this->assertSame(0, $detail['Transfer']['count']);
        $this->assertSame(0, $detail['QRIS']['count']);
    }

    public function test_receivables_never_goes_below_zero(): void
    {
        $range = DateRange::make('2026-10-01', '2026-10-05');

        // Misal transaksi 50.000 tapi ada pelunasan 60.000
        $tx = Transaction::factory()->create([
            'customer_id' => $this->customer->id,
            'created_by' => $this->user->id,
            'transaction_code' => 'TRX-OVER-1',
            'total_amount' => 50000,
            'created_at' => '2026-10-02 10:00:00',
        ]);

        Payment::factory()->create([
            'transaction_id' => $tx->id,
            'amount' => 60000,
            'method' => 'Tunai',
            'verification_status' => 'Tidak Diperlukan',
            'paid_at' => '2026-10-02 10:05:00',
            'created_by' => $this->user->id,
        ]);

        $summary = RevenueSummary::forRange($range);

        $this->assertSame(50000.0, $summary->omsetKotor());
        $this->assertSame(60000.0, $summary->penerimaanRiil());
        $this->assertSame(0.0, $summary->piutang());
    }

    public function test_empty_range_returns_zeroes(): void
    {
        $range = DateRange::make('2026-01-01', '2026-01-02');
        $summary = RevenueSummary::forRange($range);

        $this->assertSame(0.0, $summary->omsetKotor());
        $this->assertSame(0.0, $summary->penerimaanRiil());
        $this->assertSame(0.0, $summary->piutang());

        foreach (['Tunai', 'Transfer', 'QRIS'] as $method) {
            $this->assertSame(0.0, $summary->paymentMethods()[$method]);
        }
    }

    public function test_executes_minimal_queries_at_database_level(): void
    {
        $range = DateRange::make('2026-10-01', '2026-10-05');

        $tx = Transaction::factory()->create([
            'customer_id' => $this->customer->id,
            'created_by' => $this->user->id,
            'transaction_code' => 'TRX-PERF-1',
            'total_amount' => 50000,
            'created_at' => '2026-10-02 10:00:00',
        ]);

        Payment::factory()->create([
            'transaction_id' => $tx->id,
            'amount' => 50000,
            'method' => 'Tunai',
            'verification_status' => 'Tidak Diperlukan',
            'paid_at' => '2026-10-02 10:05:00',
            'created_by' => $this->user->id,
        ]);

        DB::flushQueryLog();
        DB::enableQueryLog();

        $summary = RevenueSummary::forRange($range);

        // Panggil seluruh accessor
        $summary->omsetKotor();
        $summary->penerimaanRiil();
        $summary->piutang();
        $summary->paymentMethods();
        $summary->paymentMethodsDetail();
        $summary->toArray();

        $queries = DB::getQueryLog();
        DB::disableQueryLog();

        // Tepat 2 query agregasi SQL: 1 query transaksi dan 1 query pembayaran
        $this->assertCount(2, $queries);
    }

    public function test_to_array_structure(): void
    {
        $range = DateRange::make('2026-10-01', '2026-10-05');
        $summary = RevenueSummary::forRange($range);
        $array = $summary->toArray();

        $this->assertArrayHasKey('omset_kotor', $array);
        $this->assertArrayHasKey('penerimaan_riil', $array);
        $this->assertArrayHasKey('piutang', $array);
        $this->assertArrayHasKey('payment_methods', $array);
        $this->assertArrayHasKey('payment_methods_detail', $array);
        $this->assertArrayHasKey('total_transactions', $array);
        $this->assertArrayHasKey('active_transactions', $array);
        $this->assertArrayHasKey('cancelled_transactions', $array);
        $this->assertArrayHasKey('status_counts', $array);
    }
}
