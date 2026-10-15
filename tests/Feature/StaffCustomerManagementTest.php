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

        $responseLowercase = $this->actingAs($staff)->get(route('staff.customers.index', ['q' => 'rina']));
        $responseLowercase->assertOk();
        $responseLowercase->assertSee('Rina Kusuma');
        $responseLowercase->assertDontSee('Budi Santoso');

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

    public function test_admin_can_access_customers_page(): void
    {
        $this->withoutVite();

        $admin = User::factory()->admin()->create();
        Customer::create(['name' => 'Pelanggan Toko', 'phone' => '08123456789']);

        // Admin mengakses via rute admin.customers.index
        $responseAdmin = $this->actingAs($admin)->get(route('admin.customers.index'));
        $responseAdmin->assertOk();
        $responseAdmin->assertSee('Data Pelanggan');
        $responseAdmin->assertSee('Pelanggan Toko');

        // Admin juga dapat mengakses via rute staff.customers.index
        $responseStaff = $this->actingAs($admin)->get(route('staff.customers.index'));
        $responseStaff->assertOk();
    }

    public function test_staff_and_admin_can_create_new_customer(): void
    {
        $staff = User::factory()->staff()->create();
        $admin = User::factory()->admin()->create();

        // Staff membuat pelanggan baru
        $responseStaff = $this->actingAs($staff)->post(route('staff.customers.store'), [
            'name' => 'Ahmad Dani',
            'phone' => '081399887766',
            'address' => 'Jl. Kemang Raya No. 15',
        ]);

        $this->assertDatabaseHas('customers', [
            'name' => 'Ahmad Dani',
            'phone' => '081399887766',
            'address' => 'Jl. Kemang Raya No. 15',
        ]);
        $responseStaff->assertSessionHas('status', 'Data pelanggan berhasil ditambahkan.');

        // Admin membuat pelanggan baru
        $responseAdmin = $this->actingAs($admin)->post(route('admin.customers.store'), [
            'name' => 'Maya Estianty',
            'phone' => '081322334455',
            'address' => 'Jl. Kebayoran No. 8',
        ]);

        $this->assertDatabaseHas('customers', [
            'name' => 'Maya Estianty',
            'phone' => '081322334455',
        ]);
        $responseAdmin->assertSessionHas('status', 'Data pelanggan berhasil ditambahkan.');
    }

    public function test_customer_creation_validation_requires_name_and_phone(): void
    {
        $staff = User::factory()->staff()->create();

        $response = $this->actingAs($staff)->post(route('staff.customers.store'), [
            'name' => '',
            'phone' => '',
            'address' => 'Jl. Tanpa Nama',
        ]);

        $response->assertSessionHasErrors(['name', 'phone']);
    }

    public function test_staff_and_admin_can_update_customer(): void
    {
        $staff = User::factory()->staff()->create();
        $customer = Customer::create([
            'name' => 'Nama Lama',
            'phone' => '0811111111',
            'address' => 'Alamat Lama',
        ]);

        $response = $this->actingAs($staff)->put(route('staff.customers.update', $customer), [
            'name' => 'Nama Baru',
            'phone' => '0822222222',
            'address' => 'Alamat Baru',
        ]);

        $this->assertDatabaseHas('customers', [
            'id' => $customer->id,
            'name' => 'Nama Baru',
            'phone' => '0822222222',
            'address' => 'Alamat Baru',
        ]);
        $response->assertSessionHas('status', 'Data pelanggan berhasil diperbarui.');
    }

    public function test_customer_with_transactions_cannot_be_deleted(): void
    {
        $staff = User::factory()->staff()->create();
        $customer = Customer::create([
            'name' => 'Pelanggan Bertransaksi',
            'phone' => '081234567890',
        ]);

        Transaction::create([
            'customer_id' => $customer->id,
            'transaction_code' => 'TRX-TEST-DEL-1',
            'total_amount' => 30000,
        ]);

        $response = $this->actingAs($staff)->delete(route('staff.customers.destroy', $customer));

        // Data tidak terhapus dari database
        $this->assertDatabaseHas('customers', ['id' => $customer->id]);
        $response->assertSessionHasErrors('error');
    }

    public function test_customer_without_transactions_can_be_deleted(): void
    {
        $staff = User::factory()->staff()->create();
        $customer = Customer::create([
            'name' => 'Pelanggan Baru Batal',
            'phone' => '089999999999',
        ]);

        $response = $this->actingAs($staff)->delete(route('staff.customers.destroy', $customer));

        $this->assertDatabaseMissing('customers', ['id' => $customer->id]);
        $response->assertSessionHas('status', 'Data pelanggan berhasil dihapus.');
    }

    public function test_customer_role_cannot_perform_crud_operations(): void
    {
        $customerUser = User::factory()->customer()->create();
        $targetCustomer = Customer::create([
            'name' => 'Target Customer',
            'phone' => '0877777777',
        ]);

        $this->actingAs($customerUser)
            ->post(route('staff.customers.store'), ['name' => 'Hacker', 'phone' => '081'])
            ->assertStatus(403);

        $this->actingAs($customerUser)
            ->put(route('staff.customers.update', $targetCustomer), ['name' => 'Hacker', 'phone' => '081'])
            ->assertStatus(403);

        $this->actingAs($customerUser)
            ->delete(route('staff.customers.destroy', $targetCustomer))
            ->assertStatus(403);
    }

    public function test_customer_phone_is_automatically_normalized_to_local_format(): void
    {
        $staff = User::factory()->staff()->create();

        // 1. Input dengan format +62
        $this->actingAs($staff)->post(route('staff.customers.store'), [
            'name' => 'Budi Plus62',
            'phone' => '+6281234567890',
            'address' => 'Jl. Mawar',
        ])->assertSessionHasNoErrors();

        $this->assertDatabaseHas('customers', [
            'name' => 'Budi Plus62',
            'phone' => '081234567890',
        ]);

        // 2. Input dengan format 62 tanpa plus dan ada spasi/strip
        $this->actingAs($staff)->post(route('staff.customers.store'), [
            'name' => 'Siti Strip',
            'phone' => '62 813-9988-7766',
            'address' => 'Jl. Melati',
        ])->assertSessionHasNoErrors();

        $this->assertDatabaseHas('customers', [
            'name' => 'Siti Strip',
            'phone' => '081399887766',
        ]);

        // 3. Update customer dengan +62
        $customer = Customer::create([
            'name' => 'Andi Update',
            'phone' => '0811111111',
        ]);

        $this->actingAs($staff)->put(route('staff.customers.update', $customer), [
            'name' => 'Andi Update',
            'phone' => '+628999888777',
        ])->assertSessionHasNoErrors();

        $this->assertDatabaseHas('customers', [
            'id' => $customer->id,
            'phone' => '08999888777',
        ]);
    }

    public function test_customer_whatsapp_url_generates_valid_wa_me_link(): void
    {
        $customer = Customer::create([
            'name' => 'Pelanggan WA',
            'phone' => '081234567890',
        ]);

        $this->assertSame('https://wa.me/6281234567890', $customer->whatsapp_url);
    }
}
