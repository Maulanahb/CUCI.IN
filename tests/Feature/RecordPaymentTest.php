<?php

namespace Tests\Feature;

use App\Enums\TransactionStatus;
use App\Models\Customer;
use App\Models\Payment;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RecordPaymentTest extends TestCase
{
    use RefreshDatabase;

    private User $staff;

    private User $admin;

    private Customer $customer;

    private Transaction $transaction;

    protected function setUp(): void
    {
        parent::setUp();

        $this->staff = User::factory()->create(['role' => 'staff']);
        $this->admin = User::factory()->create(['role' => 'admin']);
        $this->customer = Customer::factory()->create();

        $this->transaction = Transaction::factory()->create([
            'customer_id' => $this->customer->id,
            'created_by' => $this->staff->id,
            'total_amount' => 50000,
        ]);
    }

    public function test_staff_can_record_full_cash_payment(): void
    {
        $response = $this->actingAs($this->staff)
            ->post(route('staff.transactions.payments.store', $this->transaction), [
                'amount' => 50000,
                'method' => 'Tunai',
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('payments', [
            'transaction_id' => $this->transaction->id,
            'amount' => 50000,
            'method' => 'Tunai',
            'payment_type' => 'Pembayaran Penuh',
            'verification_status' => 'Tidak Diperlukan',
            'created_by' => $this->staff->id,
        ]);

        $this->transaction->refresh();
        $this->assertEquals(0, $this->transaction->remaining_amount);
        $this->assertEquals('Lunas', $this->transaction->payment_status);
    }

    public function test_staff_can_record_dp_and_subsequent_settlement(): void
    {
        // 1. Bayar DP 20.000
        $response1 = $this->actingAs($this->staff)
            ->post(route('staff.transactions.payments.store', $this->transaction), [
                'amount' => 20000,
                'method' => 'Tunai',
            ]);

        $response1->assertRedirect();
        $this->assertDatabaseHas('payments', [
            'transaction_id' => $this->transaction->id,
            'amount' => 20000,
            'payment_type' => 'DP',
        ]);

        $this->transaction->refresh();
        $this->assertEquals('DP', $this->transaction->payment_status);
        $this->assertEquals(30000, $this->transaction->remaining_amount);

        // 2. Bayar Pelunasan 30.000
        $response2 = $this->actingAs($this->staff)
            ->post(route('staff.transactions.payments.store', $this->transaction), [
                'amount' => 30000,
                'method' => 'Tunai',
            ]);

        $response2->assertRedirect();
        $this->assertDatabaseHas('payments', [
            'transaction_id' => $this->transaction->id,
            'amount' => 30000,
            'payment_type' => 'Pelunasan',
        ]);

        $this->transaction->refresh();
        $this->assertEquals('Lunas', $this->transaction->payment_status);
        $this->assertEquals(0, $this->transaction->remaining_amount);
    }

    public function test_cannot_record_payment_exceeding_remaining_amount(): void
    {
        $response = $this->actingAs($this->staff)
            ->post(route('staff.transactions.payments.store', $this->transaction), [
                'amount' => 60000,
                'method' => 'Tunai',
            ]);

        $response->assertSessionHasErrors(['amount']);
        $this->assertDatabaseCount('payments', 0);
    }

    public function test_cannot_record_payment_if_already_paid_off(): void
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
            ->post(route('staff.transactions.payments.store', $this->transaction), [
                'amount' => 10000,
                'method' => 'Tunai',
            ]);

        $response->assertSessionHasErrors();
        $this->assertDatabaseCount('payments', 1);
    }

    public function test_admin_can_also_record_payment(): void
    {
        $response = $this->actingAs($this->admin)
            ->post(route('admin.transactions.payments.store', $this->transaction), [
                'amount' => 50000,
                'method' => 'QRIS',
            ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('payments', [
            'transaction_id' => $this->transaction->id,
            'amount' => 50000,
            'method' => 'QRIS',
            'created_by' => $this->admin->id,
        ]);
    }

    public function test_cash_payment_persists_received_cash_and_change(): void
    {
        $response = $this->actingAs($this->staff)
            ->post(route('staff.transactions.payments.store', $this->transaction), [
                'amount' => 50000,
                'method' => 'Tunai',
                'cash_received' => 100000,
            ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('payments', [
            'transaction_id' => $this->transaction->id,
            'amount' => 50000,
            'cash_received' => 100000,
            'change_amount' => 50000,
        ]);
    }

    public function test_cash_payment_rejects_tendered_cash_below_amount(): void
    {
        $response = $this->actingAs($this->staff)
            ->post(route('staff.transactions.payments.store', $this->transaction), [
                'amount' => 50000,
                'method' => 'Tunai',
                'cash_received' => 40000,
            ]);

        $response->assertSessionHasErrors(['cash_received']);
        $this->assertDatabaseCount('payments', 0);
    }

    public function test_cannot_record_payment_on_cancelled_transaction(): void
    {
        $this->transaction->update(['status' => TransactionStatus::Dibatalkan]);

        $response = $this->actingAs($this->staff)
            ->post(route('staff.transactions.payments.store', $this->transaction), [
                'amount' => 50000,
                'method' => 'Tunai',
            ]);

        $response->assertSessionHasErrors(['amount']);
        $this->assertDatabaseCount('payments', 0);
    }

    public function test_payment_type_is_derived_server_side(): void
    {
        $response = $this->actingAs($this->staff)
            ->post(route('staff.transactions.payments.store', $this->transaction), [
                'amount' => 10000,
                'method' => 'Tunai',
                'payment_type' => 'Pembayaran Penuh',
            ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('payments', [
            'transaction_id' => $this->transaction->id,
            'amount' => 10000,
            'payment_type' => 'DP',
        ]);
    }

    public function test_non_cash_payment_is_recorded_as_verified(): void
    {
        $response = $this->actingAs($this->staff)
            ->post(route('staff.transactions.payments.store', $this->transaction), [
                'amount' => 50000,
                'method' => 'QRIS',
            ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('payments', [
            'transaction_id' => $this->transaction->id,
            'method' => 'QRIS',
            'verification_status' => 'Terverifikasi',
        ]);
    }
}
