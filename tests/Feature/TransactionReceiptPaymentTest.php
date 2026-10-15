<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Payment;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TransactionReceiptPaymentTest extends TestCase
{
    use RefreshDatabase;

    private User $staff;

    private Customer $customer;

    protected function setUp(): void
    {
        parent::setUp();

        $this->staff = User::factory()->create(['role' => 'staff']);
        $this->customer = Customer::factory()->create();
    }

    public function test_receipt_displays_payment_breakdown(): void
    {
        $transaction = Transaction::factory()->create([
            'customer_id' => $this->customer->id,
            'created_by' => $this->staff->id,
            'total_amount' => 75000,
        ]);

        Payment::create([
            'transaction_id' => $transaction->id,
            'amount' => 50000,
            'payment_type' => 'DP',
            'method' => 'QRIS',
            'verification_status' => 'Tidak Diperlukan',
            'paid_at' => now(),
            'created_by' => $this->staff->id,
        ]);

        $response = $this->actingAs($this->staff)
            ->get(route('staff.transactions.print', $transaction));

        $response->assertOk();
        $response->assertSee('Total Tagihan');
        $response->assertSee('75.000');
        $response->assertSee('Sudah Dibayar');
        $response->assertSee('50.000');
        $response->assertSee('Sisa Tagihan');
        $response->assertSee('25.000');
        $response->assertSee('DP');
    }

    public function test_transactions_index_displays_payment_status(): void
    {
        $transaction = Transaction::factory()->create([
            'customer_id' => $this->customer->id,
            'created_by' => $this->staff->id,
            'total_amount' => 30000,
        ]);

        Payment::create([
            'transaction_id' => $transaction->id,
            'amount' => 30000,
            'payment_type' => 'Pembayaran Penuh',
            'method' => 'Tunai',
            'verification_status' => 'Tidak Diperlukan',
            'paid_at' => now(),
            'created_by' => $this->staff->id,
        ]);

        $response = $this->actingAs($this->staff)
            ->get(route('staff.transactions.index'));

        $response->assertOk();
        $response->assertSee('Lunas');
    }
}
