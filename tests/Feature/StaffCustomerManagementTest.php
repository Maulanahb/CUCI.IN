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

class StaffCustomerManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_to_login(): void
    {
        $response = $this->get(route('staff.customers.index'));
        $response->assertRedirect(route('login'));
    }

    public function test_regular_customer_cannot_access_staff_customer_page(): void
    {
        $customerUser = User::factory()->customer()->create();

        $response = $this->actingAs($customerUser)->get(route('staff.customers.index'));
        $response->assertStatus(403);
    }

    public function test_staff_can_view_customers_page_with_data_and_summary(): void
    {
        $this->withoutVite();

        $staff = User::factory()->staff()->create(['name' => 'Siti Rahmawati']);
        $customer = Customer::create([
            'name' => 'Hendra Wijaya',
            'phone' => '0812-9842-1102',
            'address' => 'Jl. Tebet Raya No. 10',
        ]);

        $service = Service::create([
            'name' => 'Cuci Komplit Reguler',
            'price' => 8000,
            'estimated_duration' => 3,
            'description' => 'Cuci + Kering + Setrika Rapi',
            'is_active' => true,
        ]);

        $transaction = Transaction::create([
            'customer_id' => $customer->id,
            'transaction_code' => 'TRK-20261025-046',
            'total_amount' => 52000,
            'status' => 'Diproses',
            'estimated_completed_at' => now()->addDays(3),
        ]);

        TransactionDetail::create([
            'transaction_id' => $transaction->id,
            'service_id' => $service->id,
            'weight' => 6.5,
            'unit_price' => 8000,
            'subtotal' => 52000,
        ]);

        Payment::create([
            'transaction_id' => $transaction->id,
            'amount' => 52000,
            'payment_type' => 'Pembayaran Penuh',
            'method' => 'Transfer',
            'verification_status' => 'Terverifikasi',
            'paid_at' => now(),
            'created_by' => $staff->id,
        ]);

        // Saat pertama dibuka tanpa memilih customer, detail belum muncul
        $response = $this->actingAs($staff)->get(route('staff.customers.index'));
        $response->assertOk();
        $response->assertSee('Data Pelanggan');
        $response->assertSee('Hendra Wijaya');
        $response->assertSee('0812-9842-1102');
        $response->assertDontSee('TRK-20261025-046');

        // Saat customer dipilih, kartu profil dan tabel riwayat muncul tapi kartu detail transaksi belum muncul sebelum baris diklik
        $responseSelected = $this->actingAs($staff)->get(route('staff.customers.index', ['customer_id' => $customer->id]));
        $responseSelected->assertOk();
        $responseSelected->assertSee('Hendra Wijaya');
        $responseSelected->assertSee('Riwayat Transaksi Cucian Sebelumnya');
        $responseSelected->assertSee('TRK-20261025-046');
        $responseSelected->assertDontSee('Estimasi Pengambilan');

        // Saat baris riwayat transaksi diklik (dengan transaction_id), kartu detail riwayat order baru muncul
        $responseTransaction = $this->actingAs($staff)->get(route('staff.customers.index', [
            'customer_id' => $customer->id,
            'transaction_id' => $transaction->id,
        ]));
        $responseTransaction->assertOk();
        $responseTransaction->assertSee('Detail Transaksi');
        $responseTransaction->assertSee('Estimasi Pengambilan');
        $responseTransaction->assertSee('Cuci Komplit Reguler');
        $responseTransaction->assertSee('52.000');
        $responseTransaction->assertSee('Lunas');
    }

    public function test_staff_can_search_customer_by_name_or_phone(): void
    {
        $this->withoutVite();

        $staff = User::factory()->staff()->create();
        Customer::create(['name' => 'Budi Santoso', 'phone' => '0811111111']);
        Customer::create(['name' => 'Rina Kusuma', 'phone' => '0822222222']);

        $response = $this->actingAs($staff)->get(route('staff.customers.index', ['q' => 'Rina']));
        $response->assertOk();
        $response->assertSee('Rina Kusuma');
        $response->assertDontSee('Budi Santoso');

        $responsePhone = $this->actingAs($staff)->get(route('staff.customers.index', ['q' => '0811111111']));
        $responsePhone->assertOk();
        $responsePhone->assertSee('Budi Santoso');
        $responsePhone->assertDontSee('Rina Kusuma');
    }

    public function test_staff_can_select_specific_customer_to_view_detail(): void
    {
        $this->withoutVite();

        $staff = User::factory()->staff()->create();
        $customerA = Customer::create(['name' => 'Customer A', 'phone' => '0811111111']);
        $customerB = Customer::create(['name' => 'Customer B', 'phone' => '0822222222']);

        $response = $this->actingAs($staff)->get(route('staff.customers.index', ['customer_id' => $customerB->id]));

        $response->assertOk();
        $response->assertSee('Customer B');
        $response->assertSee('0822222222');
    }
}
