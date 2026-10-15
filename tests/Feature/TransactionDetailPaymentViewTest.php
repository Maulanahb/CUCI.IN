<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Payment;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TransactionDetailPaymentViewTest extends TestCase
{
    use RefreshDatabase;

    private User $staff;

    private Customer $customer;

    private Transaction $transaction;

    protected function setUp(): void
    {
        parent::setUp();

        $this->staff = User::factory()->create(['role' => 'staff']);
        $this->customer = Customer::factory()->create();

        $this->transaction = Transaction::factory()->create([
            'customer_id' => $this->customer->id,
            'created_by' => $this->staff->id,
            'total_amount' => 50000,
        ]);
    }

    public function test_displays_unpaid_payment_summary_and_record_button(): void
    {
        $response = $this->actingAs($this->staff)
            ->get(route('staff.transactions.show', $this->transaction));

        $response->assertOk();
        $response->assertSee('Informasi & Riwayat Pembayaran', false);
        $response->assertSee('Belum Bayar');
        $response->assertSee('Catat Pembayaran / Pelunasan');
        $response->assertSee('Rp 50.000');
    }

    public function test_displays_paid_off_status_and_payment_history_table(): void
    {
        Payment::create([
            'transaction_id' => $this->transaction->id,
            'amount' => 50000,
            'payment_type' => 'Pembayaran Penuh',
            'method' => 'Tunai',
            'verification_status' => 'Tidak Diperlukan',
            'paid_at' => now(),
            'created_by' => $this->staff->id,
        ]);

        $response = $this->actingAs($this->staff)
            ->get(route('staff.transactions.show', $this->transaction));

        $response->assertOk();
        $response->assertSee('Lunas');
        $response->assertSee('Pembayaran Penuh');
        $response->assertSee('Tunai');
        $response->assertDontSee('Catat Pembayaran / Pelunasan');
    }
}
