<?php

namespace Tests\Unit;

use App\Models\Customer;
use App\Models\Payment;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TransactionPaymentTest extends TestCase
{
    use RefreshDatabase;

    public function test_calculates_total_paid_remaining_and_payment_status(): void
    {
        $user = User::factory()->create();
        $customer = Customer::factory()->create();
        $transaction = Transaction::factory()->create([
            'customer_id' => $customer->id,
            'created_by' => $user->id,
            'total_amount' => 50000,
        ]);

        $this->assertEquals(0, $transaction->total_paid);
        $this->assertEquals(50000, $transaction->remaining_amount);
        $this->assertEquals('Belum Bayar', $transaction->payment_status);
        $this->assertFalse($transaction->isPaidOff());

        // Bayar DP 20.000
        Payment::create([
            'transaction_id' => $transaction->id,
            'amount' => 20000,
            'payment_type' => 'DP',
            'method' => 'Tunai',
            'verification_status' => 'Tidak Diperlukan',
            'paid_at' => now(),
            'created_by' => $user->id,
        ]);

        $transaction->refresh();
        $this->assertEquals(20000, $transaction->total_paid);
        $this->assertEquals(30000, $transaction->remaining_amount);
        $this->assertEquals('DP', $transaction->payment_status);
        $this->assertFalse($transaction->isPaidOff());

        // Bayar Pelunasan 30.000
        Payment::create([
            'transaction_id' => $transaction->id,
            'amount' => 30000,
            'payment_type' => 'Pelunasan',
            'method' => 'Tunai',
            'verification_status' => 'Tidak Diperlukan',
            'paid_at' => now(),
            'created_by' => $user->id,
        ]);

        $transaction->refresh();
        $this->assertEquals(50000, $transaction->total_paid);
        $this->assertEquals(0, $transaction->remaining_amount);
        $this->assertEquals('Lunas', $transaction->payment_status);
        $this->assertTrue($transaction->isPaidOff());
    }

    public function test_ignores_rejected_payments_in_calculation(): void
    {
        $user = User::factory()->create();
        $customer = Customer::factory()->create();
        $transaction = Transaction::factory()->create([
            'customer_id' => $customer->id,
            'created_by' => $user->id,
            'total_amount' => 50000,
        ]);

        // Pembayaran ditolak
        Payment::create([
            'transaction_id' => $transaction->id,
            'amount' => 50000,
            'payment_type' => 'Pembayaran Penuh',
            'method' => 'Transfer',
            'verification_status' => 'Ditolak',
            'paid_at' => now(),
            'created_by' => $user->id,
        ]);

        $transaction->refresh();
        $this->assertEquals(0, $transaction->total_paid);
        $this->assertEquals(50000, $transaction->remaining_amount);
        $this->assertEquals('Belum Bayar', $transaction->payment_status);
        $this->assertFalse($transaction->isPaidOff());
    }
}
