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

    public function test_customer_can_open_and_close_transaction_detail_modal(): void
    {
        $this->withoutVite();

        $staff = User::factory()->staff()->create();
        $customer = Customer::factory()->withAccount()->create(['name' => 'Budi Santoso']);
        /** @var User $user */
        $user = $customer->user;

        $service = Service::create([
            'name' => 'Cuci Komplit Reguler',
            'price' => 8000,
            'estimated_duration' => 3,
            'description' => 'Cuci + Kering + Setrika Rapi',
            'is_active' => true,
        ]);

        $trx = Transaction::create([
            'customer_id' => $customer->id,
            'transaction_code' => 'TRK-20261025-046',
            'total_amount' => 52000,
            'status' => 'Diproses',
            'estimated_completed_at' => now()->addDays(3),
        ]);

        TransactionDetail::create([
            'transaction_id' => $trx->id,
            'service_id' => $service->id,
            'weight' => 6.5,
            'unit_price' => 8000,
            'subtotal' => 52000,
        ]);

        Payment::create([
            'transaction_id' => $trx->id,
            'amount' => 52000,
            'payment_type' => 'Pelunasan',
            'method' => 'Transfer',
            'verification_status' => 'Terverifikasi',
            'paid_at' => now(),
            'created_by' => $staff->id,
        ]);

        // 1. Tanpa query param selected: Modal tidak terbuka, tabel tampil bersih
        $responseTable = $this->actingAs($user)->get(route('customer.transactions.index'));
        $responseTable->assertOk();
        $responseTable->assertSee('TRK-20261025-046');
        $responseTable->assertDontSee('Tutup detail (Esc)');

        // 2. Dengan query param selected: Modal terbuka di tengah layar sesuai database
        $responseModal = $this->actingAs($user)->get(route('customer.transactions.index', ['selected' => 'TRK-20261025-046']));
        $responseModal->assertOk();
        $responseModal->assertSee('TRK-20261025-046');
        $responseModal->assertSee('Detail Transaksi');
        $responseModal->assertSee('Tanggal Masuk');
        $responseModal->assertSee('Status Cucian');
        $responseModal->assertSee('Estimasi Selesai');
        $responseModal->assertSee('Status Pembayaran');
        $responseModal->assertSee('Lunas');
        $responseModal->assertSee('Rincian Layanan');
        $responseModal->assertSee('Cuci Komplit Reguler');
        $responseModal->assertSee('Cuci + Kering + Setrika Rapi');
        $responseModal->assertSee('6.5 kg');
        $responseModal->assertSee('Total Tagihan:');
        $responseModal->assertSee('Rp 52.000');
        $responseModal->assertSee('Tutup detail (Esc)');
    }

    public function test_customer_dashboard_displays_active_cucian_tracker_and_stats(): void
    {
        $this->withoutVite();

        $staff = User::factory()->staff()->create();
        $customer = Customer::factory()->withAccount()->create(['name' => 'Dewi Sartika']);
        /** @var User $user */
        $user = $customer->user;

        $service = Service::create([
            'name' => 'Cuci Kilat 1 Hari',
            'price' => 15000,
            'estimated_duration' => 24,
            'description' => 'Cuci kilat express',
            'is_active' => true,
        ]);

        $activeTrx = Transaction::create([
            'customer_id' => $customer->id,
            'transaction_code' => 'TRK-20261025-099',
            'total_amount' => 45000,
            'status' => 'Diproses',
            'estimated_completed_at' => now()->addDay(),
        ]);

        TransactionDetail::create([
            'transaction_id' => $activeTrx->id,
            'service_id' => $service->id,
            'weight' => 3.0,
            'unit_price' => 15000,
            'subtotal' => 45000,
        ]);

        Payment::create([
            'transaction_id' => $activeTrx->id,
            'amount' => 20000,
            'payment_type' => 'DP',
            'method' => 'Tunai',
            'verification_status' => 'Tidak Diperlukan',
            'paid_at' => now(),
            'created_by' => $staff->id,
        ]);

        $response = $this->actingAs($user)->get(route('customer.dashboard'));

        $response->assertOk();
        $response->assertSee('Cucian Sedang Berjalan');
        $response->assertSee('TRK-20261025-099');
        $response->assertSee('Sedang Dicuci / Disetrika');
        $response->assertSee('Cuci Kilat 1 Hari (3 kg)');
        $response->assertSee('Diterima');
        $response->assertSee('Diproses');
        $response->assertSee('Selesai');
        $response->assertSee('Sisa: Rp 25.000');
        $response->assertSee('Riwayat Pesanan Terbaru');
    }

    public function test_customer_dashboard_displays_empty_state_when_no_active_cucian(): void
    {
        $this->withoutVite();

        $customer = Customer::factory()->withAccount()->create(['name' => 'Ahmad Yani']);
        /** @var User $user */
        $user = $customer->user;

        $response = $this->actingAs($user)->get(route('customer.dashboard'));

        $response->assertOk();
        $response->assertSee('Tidak Ada Cucian Aktif');
        $response->assertSee('0');
        $response->assertSee('Nota Aktif');
    }
}
