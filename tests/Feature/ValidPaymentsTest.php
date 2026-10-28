<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Payment;
use App\Models\Transaction;
use App\Models\User;
use App\Services\Reports\DateRange;
use App\Services\Reports\ValidPayments;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ValidPaymentsTest extends TestCase
{
    use RefreshDatabase;

    private User $staff;

    private Customer $customer;

    private Transaction $transaction;

    protected function setUp(): void
    {
        parent::setUp();

        $this->staff = User::factory()->staff()->create();
        $this->customer = Customer::factory()->create();
        $this->transaction = Transaction::factory()->create([
            'customer_id' => $this->customer->id,
            'created_by' => $this->staff->id,
            'total_amount' => 100000,
        ]);
    }

    public function test_cash_payment_is_valid(): void
    {
        Payment::factory()->create([
            'transaction_id' => $this->transaction->id,
            'amount' => 50000,
            'method' => 'Tunai',
            'verification_status' => 'Tidak Diperlukan',
            'paid_at' => '2026-10-15 10:00:00',
            'created_by' => $this->staff->id,
        ]);

        $range = DateRange::make('2026-10-15', '2026-10-15');
        $result = ValidPayments::query($range)->get();

        $this->assertCount(1, $result);
        $this->assertEquals('50000.00', $result->first()->amount);
    }

    public function test_verified_transfer_is_valid(): void
    {
        Payment::factory()->verified('Transfer')->create([
            'transaction_id' => $this->transaction->id,
            'amount' => 100000,
            'paid_at' => '2026-10-15 12:00:00',
            'created_by' => $this->staff->id,
        ]);

        $range = DateRange::make('2026-10-15', '2026-10-15');

        $this->assertCount(1, ValidPayments::query($range)->get());
    }

    public function test_pending_payment_is_excluded(): void
    {
        Payment::factory()->pendingProof('Transfer')->create([
            'transaction_id' => $this->transaction->id,
            'amount' => 100000,
            'paid_at' => '2026-10-15 12:00:00',
            'created_by' => $this->staff->id,
        ]);

        $range = DateRange::make('2026-10-15', '2026-10-15');

        $this->assertCount(0, ValidPayments::query($range)->get());
    }

    public function test_rejected_payment_is_excluded(): void
    {
        Payment::factory()->rejected('QRIS')->create([
            'transaction_id' => $this->transaction->id,
            'amount' => 100000,
            'paid_at' => '2026-10-15 12:00:00',
            'created_by' => $this->staff->id,
        ]);

        $range = DateRange::make('2026-10-15', '2026-10-15');

        $this->assertCount(0, ValidPayments::query($range)->get());
    }

    public function test_mixed_statuses_only_counts_valid(): void
    {
        // Valid: tunai
        Payment::factory()->create([
            'transaction_id' => $this->transaction->id,
            'amount' => 20000,
            'method' => 'Tunai',
            'verification_status' => 'Tidak Diperlukan',
            'paid_at' => '2026-10-15 08:00:00',
            'created_by' => $this->staff->id,
        ]);

        // Valid: terverifikasi
        Payment::factory()->verified('Transfer')->create([
            'transaction_id' => $this->transaction->id,
            'amount' => 30000,
            'paid_at' => '2026-10-15 09:00:00',
            'created_by' => $this->staff->id,
        ]);

        // Invalid: menunggu
        Payment::factory()->pendingProof('QRIS')->create([
            'transaction_id' => $this->transaction->id,
            'amount' => 25000,
            'paid_at' => '2026-10-15 10:00:00',
            'created_by' => $this->staff->id,
        ]);

        // Invalid: ditolak
        Payment::factory()->rejected('Transfer')->create([
            'transaction_id' => $this->transaction->id,
            'amount' => 25000,
            'paid_at' => '2026-10-15 11:00:00',
            'created_by' => $this->staff->id,
        ]);

        $range = DateRange::make('2026-10-15', '2026-10-15');
        $result = ValidPayments::query($range)->get();

        $this->assertCount(2, $result);
        $this->assertEquals('50000.00', $result->sum('amount'));
    }

    public function test_payment_at_23_59_59_is_included(): void
    {
        Payment::factory()->create([
            'transaction_id' => $this->transaction->id,
            'amount' => 75000,
            'method' => 'Tunai',
            'verification_status' => 'Tidak Diperlukan',
            'paid_at' => '2026-10-15 23:59:59',
            'created_by' => $this->staff->id,
        ]);

        $range = DateRange::make('2026-10-15', '2026-10-15');

        $this->assertCount(1, ValidPayments::query($range)->get());
    }

    public function test_empty_range_returns_zero(): void
    {
        // Pembayaran di luar rentang
        Payment::factory()->create([
            'transaction_id' => $this->transaction->id,
            'amount' => 50000,
            'method' => 'Tunai',
            'verification_status' => 'Tidak Diperlukan',
            'paid_at' => '2026-10-10 10:00:00',
            'created_by' => $this->staff->id,
        ]);

        $range = DateRange::make('2026-10-15', '2026-10-20');

        $this->assertCount(0, ValidPayments::query($range)->get());
    }

    public function test_query_count_is_small(): void
    {
        Payment::factory()->create([
            'transaction_id' => $this->transaction->id,
            'amount' => 50000,
            'method' => 'Tunai',
            'verification_status' => 'Tidak Diperlukan',
            'paid_at' => '2026-10-15 10:00:00',
            'created_by' => $this->staff->id,
        ]);

        $range = DateRange::make('2026-10-15', '2026-10-15');

        $queryCount = 0;
        \DB::listen(function () use (&$queryCount) {
            $queryCount++;
        });

        ValidPayments::query($range)->get();

        $this->assertLessThanOrEqual(1, $queryCount, 'ValidPayments::query harus menggunakan 1 kueri saja.');
    }
}
