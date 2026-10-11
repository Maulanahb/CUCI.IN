<?php

namespace Tests\Feature;

use App\Enums\TransactionStatus;
use App\Models\Customer;
use App\Models\Payment;
use App\Models\Service;
use App\Models\Transaction;
use App\Models\TransactionDetail;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TransactionLookupTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test tamu/guest dapat membuka halaman pelacakan transaksi tanpa login.
     */
    public function test_guest_can_access_lookup_page(): void
    {
        $this->withoutVite();

        $response = $this->get(route('transactions.lookup'));

        $response->assertOk();
        $response->assertSee('Cek Status Cucian Anda');
        $response->assertSee('Masukkan Kode Transaksi');
        $response->assertSee('Cek Transaksi');
    }

    /**
     * Test tamu dapat mencari dan melihat rincian transaksi berdasarkan kode nota.
     */
    public function test_guest_can_search_and_find_transaction_details(): void
    {
        $this->withoutVite();

        $customer = Customer::create([
            'name' => 'Budi Santoso',
            'phone' => '081234567890',
        ]);

        $service = Service::create([
            'name' => 'Cuci Kering',
            'price' => 7000,
            'estimated_duration' => 2,
            'is_active' => true,
        ]);

        $transaction = Transaction::create([
            'customer_id' => $customer->id,
            'transaction_code' => 'TRX-0001',
            'total_amount' => 21000,
            'status' => TransactionStatus::Diproses,
            'estimated_completed_at' => Carbon::create(2026, 10, 6, 15, 0),
        ]);

        TransactionDetail::create([
            'transaction_id' => $transaction->id,
            'service_id' => $service->id,
            'weight' => 3,
            'unit_price' => 7000,
            'subtotal' => 21000,
        ]);

        $staff = User::factory()->staff()->create();
        Payment::create([
            'transaction_id' => $transaction->id,
            'amount' => 10000,
            'payment_type' => 'DP',
            'method' => 'Transfer',
            'verification_status' => 'Terverifikasi',
            'paid_at' => now(),
            'created_by' => $staff->id,
        ]);

        $response = $this->get(route('transactions.lookup', ['code' => 'TRX-0001']));

        $response->assertOk();
        $response->assertSee('TRX-0001');
        $response->assertSee('Budi Santoso');
        $response->assertSee('Cuci Kering');
        $response->assertSee('3 kg');
        $response->assertSee('21.000');
        $response->assertSee('DP');
        $response->assertSee('Diproses');
        $response->assertSee('2026');
    }

    /**
     * Test pesan notifikasi ketika kode transaksi yang dicari tidak ditemukan.
     */
    public function test_guest_sees_not_found_message_when_transaction_code_is_invalid(): void
    {
        $this->withoutVite();

        $response = $this->get(route('transactions.lookup', ['code' => 'TRX-INVALID-999']));

        $response->assertOk();
        $response->assertSee('Transaksi Tidak Ditemukan');
        $response->assertSee('TRX-INVALID-999');
    }

    /**
     * Test status pembayaran Lunas ditampilkan ketika pembayaran sudah penuh.
     */
    public function test_guest_sees_paid_full_status_when_transaction_is_lunas(): void
    {
        $this->withoutVite();

        $customer = Customer::create([
            'name' => 'Siti Aminah',
            'phone' => '08987654321',
        ]);

        $service = Service::create([
            'name' => 'Cuci + Setrika',
            'price' => 10000,
            'estimated_duration' => 2,
            'is_active' => true,
        ]);

        $transaction = Transaction::create([
            'customer_id' => $customer->id,
            'transaction_code' => 'TRX-0002',
            'total_amount' => 30000,
            'status' => TransactionStatus::Selesai,
        ]);

        TransactionDetail::create([
            'transaction_id' => $transaction->id,
            'service_id' => $service->id,
            'weight' => 3,
            'unit_price' => 10000,
            'subtotal' => 30000,
        ]);

        $staff = User::factory()->staff()->create();
        Payment::create([
            'transaction_id' => $transaction->id,
            'amount' => 30000,
            'payment_type' => 'Pembayaran Penuh',
            'method' => 'QRIS',
            'verification_status' => 'Terverifikasi',
            'paid_at' => now(),
            'created_by' => $staff->id,
        ]);

        $response = $this->get(route('transactions.lookup', ['code' => 'TRX-0002']));

        $response->assertOk();
        $response->assertSee('TRX-0002');
        $response->assertSee('Siti Aminah');
        $response->assertSee('Lunas');
        $response->assertSee('Selesai');
    }

    /**
     * Test halaman login memiliki tautan cepat menuju cek transaksi publik.
     */
    public function test_login_page_contains_link_to_transaction_lookup(): void
    {
        $this->withoutVite();

        $response = $this->get(route('login'));

        $response->assertOk();
        $response->assertSee(route('transactions.lookup'));
        $response->assertSee('Cek Transaksi');
    }
}
