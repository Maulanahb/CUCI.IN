<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Payment;
use App\Models\Service;
use App\Models\Transaction;
use App\Models\TransactionDetail;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomerTransactionTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_to_login_when_accessing_transactions(): void
    {
        $response = $this->get(route('customer.transactions.index'));

        $response->assertRedirect(route('login'));
    }

    public function test_customer_can_access_riwayat_transaksi_page(): void
    {
        $this->withoutVite();

        $customer = Customer::factory()->withAccount()->create(['name' => 'Rina Wulandari']);
        /** @var User $user */
        $user = $customer->user;

        $response = $this->actingAs($user)->get(route('customer.transactions.index'));

        $response->assertOk();
        $response->assertSee('Riwayat Transaksi');
        $response->assertSee('Belum Ada Transaksi');
    }

    public function test_sidebar_displays_riwayat_transaksi_for_customer(): void
    {
        $this->withoutVite();

        $customer = Customer::factory()->withAccount()->create();
        /** @var User $user */
        $user = $customer->user;

        $response = $this->actingAs($user)->get(route('customer.dashboard'));

        $response->assertOk();
        $response->assertSee('Riwayat Transaksi');
        $response->assertSee(route('customer.transactions.index'));
    }

    public function test_customer_sees_their_transactions_and_details(): void
    {
        $this->withoutVite();

        $staff = User::factory()->staff()->create();
        $customer = Customer::factory()->withAccount()->create(['name' => 'Rina Wulandari']);
        /** @var User $user */
        $user = $customer->user;

        $service = Service::create([
            'name' => 'Cuci Setrika Reguler',
            'price' => 10000,
            'estimated_duration' => 48,
            'description' => 'Layanan cuci dan setrika reguler',
            'is_active' => true,
        ]);

        $trx = Transaction::create([
            'customer_id' => $customer->id,
            'transaction_code' => 'TRX-2026-001',
            'total_amount' => 50000,
            'status' => 'Diproses',
            'estimated_completed_at' => now()->addDays(2),
        ]);

        TransactionDetail::create([
            'transaction_id' => $trx->id,
            'service_id' => $service->id,
            'weight' => 5.0,
            'unit_price' => 10000,
            'subtotal' => 50000,
        ]);

        Payment::create([
            'transaction_id' => $trx->id,
            'amount' => 25000,
            'payment_type' => 'DP',
            'method' => 'QRIS',
            'verification_status' => 'Terverifikasi',
            'paid_at' => now(),
            'created_by' => $staff->id,
        ]);

        $response = $this->actingAs($user)->get(route('customer.transactions.index'));

        $response->assertOk();
        $response->assertSee('TRX-2026-001');
        $response->assertSee('Cuci Setrika Reguler');
        $response->assertSee('Diproses');
        $response->assertSee('Rp 50.000');
        $response->assertSee('Rp 25.000');
        $response->assertSee('DP Kasir');
        $response->assertSee('Belum Lunas');
    }
}
