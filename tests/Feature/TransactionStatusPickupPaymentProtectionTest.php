<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Payment;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TransactionStatusPickupPaymentProtectionTest extends TestCase
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
            'status' => 'Selesai',
        ]);
    }

    public function test_cannot_set_status_to_diambil_if_unpaid(): void
    {
        $response = $this->actingAs($this->staff)
            ->patch(route('staff.transactions.status', $this->transaction), [
                'status' => 'Diambil',
            ]);

        $response->assertSessionHasErrors('status');
        $freshStatus = $this->transaction->fresh()->status;
        $this->assertEquals('Selesai', $freshStatus instanceof \BackedEnum ? $freshStatus->value : (string) $freshStatus);
    }

    public function test_cannot_set_status_to_diambil_if_only_dp_paid(): void
    {
        Payment::create([
            'transaction_id' => $this->transaction->id,
            'amount' => 20000,
            'payment_type' => 'DP',
            'method' => 'Tunai',
            'verification_status' => 'Tidak Diperlukan',
            'paid_at' => now(),
            'created_by' => $this->staff->id,
        ]);

        $response = $this->actingAs($this->staff)
            ->patch(route('staff.transactions.status', $this->transaction), [
                'status' => 'Diambil',
            ]);

        $response->assertSessionHasErrors('status');
        $freshStatus = $this->transaction->fresh()->status;
        $this->assertEquals('Selesai', $freshStatus instanceof \BackedEnum ? $freshStatus->value : (string) $freshStatus);
    }

    public function test_can_set_status_to_other_stages_even_if_unpaid(): void
    {
        $unpaid = Transaction::factory()->create([
            'customer_id' => $this->customer->id,
            'created_by' => $this->staff->id,
            'total_amount' => 50000,
            'status' => 'Diterima',
        ]);

        $response = $this->actingAs($this->staff)
            ->patch(route('staff.transactions.status', $unpaid), [
                'status' => 'Diproses',
            ]);

        $response->assertSessionHasNoErrors();
        $freshStatus = $unpaid->fresh()->status;
        $this->assertEquals('Diproses', $freshStatus instanceof \BackedEnum ? $freshStatus->value : (string) $freshStatus);
    }

    public function test_can_set_status_to_diambil_when_fully_paid(): void
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
            ->patch(route('staff.transactions.status', $this->transaction), [
                'status' => 'Diambil',
            ]);

        $response->assertSessionHasNoErrors();
        $freshStatus = $this->transaction->fresh()->status;
        $this->assertEquals('Diambil', $freshStatus instanceof \BackedEnum ? $freshStatus->value : (string) $freshStatus);
    }
}
