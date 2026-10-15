<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Service;
use App\Models\Transaction;
use App\Models\TransactionDetail;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ServiceManagementTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Guest diarahkan ke halaman login saat mengakses halaman layanan.
     */
    public function test_guest_cannot_access_services_pages_and_redirects_to_login(): void
    {
        $responseAdmin = $this->get(route('admin.services.index'));
        $responseAdmin->assertRedirect(route('login'));

        $responseStaff = $this->get(route('staff.services.index'));
        $responseStaff->assertRedirect(route('login'));
    }

    /**
     * Customer tidak memiliki izin (403 Forbidden) mengakses pengelolaan layanan.
     */
    public function test_customer_cannot_access_services_pages(): void
    {
        $customerUser = User::factory()->customer()->create();

        $responseAdmin = $this->actingAs($customerUser)->get(route('admin.services.index'));
        $responseAdmin->assertStatus(403);

        $responseStaff = $this->actingAs($customerUser)->get(route('staff.services.index'));
        $responseStaff->assertStatus(403);
    }

    /**
     * Staff dapat melihat katalog layanan (UC06) secara read-only.
     */
    public function test_staff_can_view_services_catalog_as_read_only(): void
    {
        $this->withoutVite();

        $staff = User::factory()->staff()->create();

        $service = Service::create([
            'name' => 'Cuci Kering Standar',
            'price' => 7000.00,
            'estimated_duration' => 24,
            'description' => 'Paket cuci kiloan hemat.',
            'is_active' => true,
        ]);

        $response = $this->actingAs($staff)->get(route('staff.services.index'));
        $response->assertOk();
        $response->assertSee('Katalog Layanan Laundry');
        $response->assertSee('Cuci Kering Standar');
        $response->assertSee('7.000');
        $response->assertSee('24 Jam');
        $response->assertSee('Katalog Kasir / Staff');
        // Staff tidak melihat tombol aksi mutasi tambah layanan
        $response->assertDontSee('btn-open-create-service');
    }

    /**
     * Staff ditolak (403 Forbidden) jika mencoba melakukan mutasi data layanan.
     */
    public function test_staff_cannot_mutate_services(): void
    {
        $staff = User::factory()->staff()->create();
        $service = Service::factory()->create(['name' => 'Paket Asli', 'is_active' => true]);

        // 1. Coba tambah layanan
        $responseStore = $this->actingAs($staff)->post(route('admin.services.store'), [
            'name' => 'Layanan Ilegal',
            'price' => 10000,
            'estimated_duration' => 24,
        ]);
        $responseStore->assertStatus(403);

        // 2. Coba update layanan
        $responseUpdate = $this->actingAs($staff)->put(route('admin.services.update', $service), [
            'name' => 'Paket Diubah',
            'price' => 12000,
            'estimated_duration' => 48,
        ]);
        $responseUpdate->assertStatus(403);

        // 3. Coba toggle status
        $responseToggle = $this->actingAs($staff)->patch(route('admin.services.toggle', $service));
        $responseToggle->assertStatus(403);

        // 4. Coba hapus layanan
        $responseDelete = $this->actingAs($staff)->delete(route('admin.services.destroy', $service));
        $responseDelete->assertStatus(403);
    }

    /**
     * Admin dapat melihat halaman master data layanan dengan tombol tambah dan statistik.
     */
    public function test_admin_can_view_services_management_page(): void
    {
        $this->withoutVite();

        $admin = User::factory()->admin()->create();

        Service::create([
            'name' => 'Cuci Kilat Express',
            'price' => 15000.00,
            'estimated_duration' => 6,
            'description' => 'Selesai dalam 6 jam.',
            'is_active' => true,
        ]);

        $response = $this->actingAs($admin)->get(route('admin.services.index'));
        $response->assertOk();
        $response->assertSee('Katalog Layanan Laundry');
        $response->assertSee('Cuci Kilat Express');
        $response->assertSee('Mode Administrator');
        $response->assertSee('Tambah Layanan');
        $response->assertSee('btn-open-create-service');
    }

    /**
     * Admin dapat membuat data layanan laundry baru.
     */
    public function test_admin_can_create_new_service(): void
    {
        $admin = User::factory()->admin()->create();

        $payload = [
            'name' => 'Cuci Boneka Lembut',
            'price' => '25.000',
            'estimated_duration' => 48,
            'description' => 'Perawatan khusus boneka dengan deterjen ramah anak.',
            'is_active' => true,
        ];

        $response = $this->actingAs($admin)->post(route('admin.services.store'), $payload);

        $response->assertRedirect(route('admin.services.index'));
        $response->assertSessionHas('status');

        $this->assertDatabaseHas('services', [
            'name' => 'Cuci Boneka Lembut',
            'price' => '25000.00',
            'estimated_duration' => 48,
            'description' => 'Perawatan khusus boneka dengan deterjen ramah anak.',
            'is_active' => true,
        ]);
    }

    /**
     * Validasi pembuatan layanan: nama unik, harga > 0, durasi > 0.
     */
    public function test_service_creation_validation_errors(): void
    {
        $admin = User::factory()->admin()->create();

        Service::create([
            'name' => 'Cuci Setrika Rapih',
            'price' => 10000,
            'estimated_duration' => 24,
            'is_active' => true,
        ]);

        // Input duplikat nama dan nilai tidak valid
        $response = $this->actingAs($admin)->post(route('admin.services.store'), [
            'name' => 'Cuci Setrika Rapih', // Duplikat
            'price' => -5000,              // Negatif
            'estimated_duration' => 0,     // Nol
        ]);

        $response->assertSessionHasErrors(['name', 'price', 'estimated_duration']);
        $this->assertEquals(1, Service::count());
    }

    /**
     * Admin dapat memperbarui data layanan yang sudah ada.
     */
    public function test_admin_can_update_service(): void
    {
        $admin = User::factory()->admin()->create();

        $service = Service::create([
            'name' => 'Cuci Karpet Mini',
            'price' => 30000.00,
            'estimated_duration' => 72,
            'description' => 'Deskripsi lama.',
            'is_active' => true,
        ]);

        $response = $this->actingAs($admin)->put(route('admin.services.update', $service), [
            'name' => 'Cuci Karpet Jumbo Premium',
            'price' => '45000',
            'estimated_duration' => 96,
            'description' => 'Deskripsi baru lebih lengkap.',
            'is_active' => '1',
        ]);

        $response->assertRedirect(route('admin.services.index'));
        $response->assertSessionHas('status');

        $service->refresh();
        $this->assertEquals('Cuci Karpet Jumbo Premium', $service->name);
        $this->assertEquals('45000.00', $service->price);
        $this->assertEquals(96, $service->estimated_duration);
        $this->assertEquals('Deskripsi baru lebih lengkap.', $service->description);
        $this->assertTrue($service->is_active);
    }

    /**
     * Admin dapat melakukan toggle status ketersediaan layanan (is_active).
     */
    public function test_admin_can_toggle_service_active_status(): void
    {
        $admin = User::factory()->admin()->create();

        $service = Service::create([
            'name' => 'Cuci Sepatu Kanvas',
            'price' => 20000.00,
            'estimated_duration' => 48,
            'is_active' => true,
        ]);

        // 1. Nonaktifkan layanan
        $responseToggleOff = $this->actingAs($admin)->patch(route('admin.services.toggle', $service));
        $responseToggleOff->assertRedirect(route('admin.services.index'));
        $responseToggleOff->assertSessionHas('status');

        $service->refresh();
        $this->assertFalse($service->is_active);

        // 2. Aktifkan kembali
        $responseToggleOn = $this->actingAs($admin)->patch(route('admin.services.toggle', $service));
        $responseToggleOn->assertRedirect(route('admin.services.index'));

        $service->refresh();
        $this->assertTrue($service->is_active);
    }

    /**
     * Layanan yang belum pernah digunakan dalam transaksi DAPAT dihapus.
     */
    public function test_admin_can_delete_service_without_transactions(): void
    {
        $admin = User::factory()->admin()->create();

        $service = Service::create([
            'name' => 'Layanan Percobaan',
            'price' => 5000.00,
            'estimated_duration' => 12,
            'is_active' => true,
        ]);

        $response = $this->actingAs($admin)->delete(route('admin.services.destroy', $service));
        $response->assertRedirect(route('admin.services.index'));
        $response->assertSessionHas('status');

        $this->assertDatabaseMissing('services', ['id' => $service->id]);
    }

    /**
     * Aturan Integritas Data Historis: Layanan yang SUDAH digunakan dalam transaksi
     * TIDAK BOLEH dihapus melalui fungsi operasional.
     */
    public function test_admin_cannot_delete_service_with_existing_transactions(): void
    {
        $admin = User::factory()->admin()->create();
        $staff = User::factory()->staff()->create();
        $customer = Customer::factory()->create();

        $service = Service::create([
            'name' => 'Cuci Kering Dipakai Transaksi',
            'price' => 10000.00,
            'estimated_duration' => 24,
            'is_active' => true,
        ]);

        $transaction = Transaction::create([
            'customer_id' => $customer->id,
            'created_by' => $staff->id,
            'transaction_code' => 'TRK-SVC-TEST-001',
            'total_amount' => 20000.00,
        ]);

        TransactionDetail::create([
            'transaction_id' => $transaction->id,
            'service_id' => $service->id,
            'weight' => 2.00,
            'unit_price' => 10000.00,
            'subtotal' => 20000.00,
        ]);

        // Coba hapus layanan yang terikat transaksi
        $response = $this->actingAs($admin)->delete(route('admin.services.destroy', $service));

        $response->assertRedirect(route('admin.services.index'));
        $response->assertSessionHasErrors(['error']);

        // Layanan harus tetap tersimpan di database
        $this->assertDatabaseHas('services', ['id' => $service->id]);
    }

    /**
     * Fitur pencarian dan penyaringan (filter) status layanan.
     */
    public function test_service_search_and_status_filtering(): void
    {
        $this->withoutVite();

        $admin = User::factory()->admin()->create();

        Service::create(['name' => 'Paket Kilat 1', 'price' => 10000, 'estimated_duration' => 6, 'is_active' => true]);
        Service::create(['name' => 'Paket Santai 2', 'price' => 6000, 'estimated_duration' => 48, 'is_active' => true]);
        Service::create(['name' => 'Paket Musim Hujan', 'price' => 8000, 'estimated_duration' => 72, 'is_active' => false]);

        // 1. Filter aktif
        $responseActive = $this->actingAs($admin)->get(route('admin.services.index', ['status' => 'active']));
        $responseActive->assertOk();
        $responseActive->assertSee('Paket Kilat 1');
        $responseActive->assertSee('Paket Santai 2');
        $responseActive->assertDontSee('Paket Musim Hujan');

        // 2. Filter nonaktif
        $responseInactive = $this->actingAs($admin)->get(route('admin.services.index', ['status' => 'inactive']));
        $responseInactive->assertOk();
        $responseInactive->assertDontSee('Paket Kilat 1');
        $responseInactive->assertSee('Paket Musim Hujan');

        // 3. Pencarian kata kunci
        $responseSearch = $this->actingAs($admin)->get(route('admin.services.index', ['q' => 'Santai']));
        $responseSearch->assertOk();
        $responseSearch->assertSee('Paket Santai 2');
        $responseSearch->assertDontSee('Paket Kilat 1');
    }

    /**
     * Menu sidebar "Layanan" tampil dan aktif untuk Admin dan Staff.
     */
    public function test_services_sidebar_link_is_visible_for_admin_and_staff(): void
    {
        $this->withoutVite();

        $admin = User::factory()->admin()->create();
        $staff = User::factory()->staff()->create();

        // 1. Admin melihat menu Layanan menuju admin.services.index
        $responseAdmin = $this->actingAs($admin)->get(route('admin.dashboard'));
        $responseAdmin->assertOk();
        $responseAdmin->assertSee('Layanan');
        $responseAdmin->assertSee(route('admin.services.index'));

        // 2. Staff melihat menu Layanan menuju staff.services.index
        $responseStaff = $this->actingAs($staff)->get(route('staff.dashboard'));
        $responseStaff->assertOk();
        $responseStaff->assertSee('Layanan');
        $responseStaff->assertSee(route('staff.services.index'));
    }
}
