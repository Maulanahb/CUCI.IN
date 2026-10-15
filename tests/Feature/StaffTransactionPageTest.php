<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Service;
use App\Models\Transaction;
use App\Models\TransactionDetail;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StaffTransactionPageTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Guest ditolak dan diarahkan ke login saat mengakses halaman form atau detail transaksi.
     */
    public function test_guest_is_redirected_to_login_from_create_and_show(): void
    {
        $customer = Customer::factory()->create();
        $staff = User::factory()->staff()->create();
        $transaction = Transaction::create([
            'customer_id' => $customer->id,
            'created_by' => $staff->id,
            'transaction_code' => 'TRK-20261014-001',
            'total_amount' => 15000,
        ]);

        // Akses halaman form
        $responseCreate = $this->get(route('staff.transactions.create'));
        $responseCreate->assertRedirect(route('login'));

        // Akses halaman detail
        $responseShow = $this->get(route('staff.transactions.show', $transaction));
        $responseShow->assertRedirect(route('login'));
    }

    /**
     * Customer ditolak (403 Forbidden) saat mengakses halaman form atau detail transaksi Staff.
     */
    public function test_customer_cannot_access_staff_transaction_pages(): void
    {
        $customerUser = User::factory()->customer()->create();
        $customer = Customer::factory()->create();
        $staff = User::factory()->staff()->create();
        $transaction = Transaction::create([
            'customer_id' => $customer->id,
            'created_by' => $staff->id,
            'transaction_code' => 'TRK-20261014-002',
            'total_amount' => 20000,
        ]);

        // Akses halaman form
        $responseCreate = $this->actingAs($customerUser)->get(route('staff.transactions.create'));
        $responseCreate->assertStatus(403);

        // Akses halaman detail
        $responseShow = $this->actingAs($customerUser)->get(route('staff.transactions.show', $transaction));
        $responseShow->assertStatus(403);
    }

    /**
     * Staff dan Admin dapat membuka halaman form transaksi (POS),
     * melihat daftar pelanggan serta layanan aktif. Layanan nonaktif tidak ditampilkan.
     */
    public function test_staff_and_admin_can_view_create_form_with_customers_and_active_services(): void
    {
        $this->withoutVite();

        $staff = User::factory()->staff()->create();
        $admin = User::factory()->admin()->create();

        $customer1 = Customer::factory()->create(['name' => 'Budi Santoso']);
        $customer2 = Customer::factory()->create(['name' => 'Ani Lestari']);

        $activeService = Service::factory()->create([
            'name' => 'Cuci Kering Kilat',
            'price' => 10000.00,
            'is_active' => true,
        ]);

        $inactiveService = Service::factory()->inactive()->create([
            'name' => 'Cuci Sepatu Premium Nonaktif',
            'price' => 25000.00,
        ]);

        // 1. Staff membuka form
        $responseStaff = $this->actingAs($staff)->get(route('staff.transactions.create'));
        $responseStaff->assertOk();
        $responseStaff->assertSee('Kasir POS — Transaksi Baru');
        $responseStaff->assertSee('Ani Lestari');
        $responseStaff->assertSee('Budi Santoso');
        $responseStaff->assertSee('Cuci Kering Kilat');
        $responseStaff->assertSee('10.000');
        $responseStaff->assertDontSee('Cuci Sepatu Premium Nonaktif');

        // 2. Admin membuka form via rute admin.transactions.create
        $responseAdmin = $this->actingAs($admin)->get(route('admin.transactions.create'));
        $responseAdmin->assertOk();
        $responseAdmin->assertSee('Kasir POS — Transaksi Baru');
        $responseAdmin->assertSee('Ani Lestari');
        $responseAdmin->assertSee('Cuci Kering Kilat');
        $responseAdmin->assertDontSee('Cuci Sepatu Premium Nonaktif');

        // 3. Admin juga dapat membuka form via rute staff.transactions.create
        $responseAdminStaff = $this->actingAs($admin)->get(route('staff.transactions.create'));
        $responseAdminStaff->assertOk();
    }

    /**
     * Halaman detail transaksi menampilkan kode nota, nama customer, item layanan,
     * harga satuan, subtotal, dan total transaksi dalam format Rupiah, serta tombol kembali.
     */
    public function test_transaction_detail_page_displays_all_transaction_information(): void
    {
        $this->withoutVite();

        $staff = User::factory()->staff()->create();
        $customer = Customer::factory()->create(['name' => 'Dewi Sartika', 'phone' => '081234567890']);
        $service = Service::factory()->create([
            'name' => 'Cuci Komplit Reguler',
            'price' => 8000.00,
            'is_active' => true,
        ]);

        $transaction = Transaction::create([
            'customer_id' => $customer->id,
            'created_by' => $staff->id,
            'transaction_code' => 'TRK-20261014-777',
            'total_amount' => 24000.00,
            'estimated_completed_at' => now()->addDays(2),
        ]);

        TransactionDetail::create([
            'transaction_id' => $transaction->id,
            'service_id' => $service->id,
            'weight' => 3.00,
            'unit_price' => 8000.00,
            'subtotal' => 24000.00,
        ]);

        $response = $this->actingAs($staff)->get(route('staff.transactions.show', $transaction));
        $response->assertOk();
        $response->assertSee('TRK-20261014-777');
        $response->assertSee('Dewi Sartika');
        $response->assertSee('081234567890');
        $response->assertSee('Cuci Komplit Reguler');
        $response->assertSee('3,00 kg');
        $response->assertSee('Rp 8.000');
        $response->assertSee('Rp 24.000');
        $response->assertSee('Diterima');
        $response->assertSee(route('staff.transactions.create'));
    }

    /**
     * Link "Transaksi Baru" tampil di sidebar navigasi untuk Staff dan Admin.
     */
    public function test_transaksi_baru_sidebar_link_is_visible_for_staff_and_admin(): void
    {
        $this->withoutVite();

        $staff = User::factory()->staff()->create();
        $admin = User::factory()->admin()->create();

        // Staff melihat link Transaksi Baru di sidebar mengarah ke staff.transactions.create
        $responseStaff = $this->actingAs($staff)->get(route('staff.dashboard'));
        $responseStaff->assertOk();
        $responseStaff->assertSee('Transaksi Baru');
        $responseStaff->assertSee(route('staff.transactions.create'));

        // Admin melihat link Transaksi Baru di sidebar mengarah ke admin.transactions.create
        $responseAdmin = $this->actingAs($admin)->get(route('admin.dashboard'));
        $responseAdmin->assertOk();
        $responseAdmin->assertSee('Transaksi Baru');
        $responseAdmin->assertSee(route('admin.transactions.create'));
    }

    /**
     * Setelah store berhasil, user diarahkan ke halaman detail transaksi dengan pesan flash success.
     */
    public function test_successful_store_redirects_to_transaction_show_page(): void
    {
        $this->withoutVite();

        $staff = User::factory()->staff()->create();
        $customer = Customer::factory()->create();
        $service = Service::factory()->create([
            'name' => 'Cuci Bedcover',
            'price' => 15000.00,
            'is_active' => true,
        ]);

        $response = $this->actingAs($staff)->post(route('staff.transactions.store'), [
            'customer_id' => $customer->id,
            'items' => [
                ['service_id' => $service->id, 'weight' => 2],
            ],
        ]);

        $transaction = Transaction::first();
        $this->assertNotNull($transaction);

        // Redirect tepat ke halaman transactions.show transaksi yang baru saja dibuat
        $response->assertRedirect(route('staff.transactions.show', $transaction));
        $response->assertSessionHas('success');

        // Buka halaman detail untuk memverifikasi pesan flash dan konten
        $responseShow = $this->actingAs($staff)->get(route('staff.transactions.show', $transaction));
        $responseShow->assertOk();
        $responseShow->assertSee($transaction->transaction_code);
        $responseShow->assertSee('Cuci Bedcover');
        $responseShow->assertSee('Rp 30.000');
    }

    /**
     * Halaman POS menampilkan tombol '+ Pelanggan Baru' dan modal quick-add.
     */
    public function test_pos_page_renders_quick_add_customer_button_and_modal(): void
    {
        $this->withoutVite();
        $staff = User::factory()->staff()->create();

        $response = $this->actingAs($staff)->get(route('staff.transactions.create'));

        $response->assertOk();
        $response->assertSee('Pelanggan Baru');
        $response->assertSee('modal-quick-customer');
        $response->assertSee('quick_customer_name');
        $response->assertSee('quick_customer_phone');
    }
}
