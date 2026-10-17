<?php

namespace Tests\Feature;

use App\Enums\TransactionStatus;
use App\Models\Customer;
use App\Models\LaundryStatusHistory;
use App\Models\Service;
use App\Models\Transaction;
use App\Models\TransactionDetail;
use App\Models\User;
use App\Services\CreateTransaction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TransactionStatusHistoryAndPrintTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Pembuatan transaksi baru otomatis mencatat log awal ke laundry_status_histories.
     */
    public function test_create_transaction_automatically_logs_initial_status_history(): void
    {
        $staff = User::factory()->staff()->create();
        $customer = Customer::factory()->create();
        $service = Service::factory()->create([
            'name' => 'Cuci Kering Standar',
            'price' => 6000.00,
            'is_active' => true,
        ]);

        $transaction = CreateTransaction::execute(
            $customer->id,
            $staff->id,
            [['service_id' => $service->id, 'weight' => 2.5]]
        );

        $this->assertDatabaseHas('laundry_status_histories', [
            'transaction_id' => $transaction->id,
            'status' => 'Diterima',
            'changed_by' => $staff->id,
        ]);

        $this->assertCount(1, $transaction->statusHistories);
        $this->assertEquals('Diterima', $transaction->statusHistories->first()->status->value ?? $transaction->statusHistories->first()->status);
        $this->assertEquals($staff->id, $transaction->statusHistories->first()->changed_by);
    }

    /**
     * Perubahan status cucian oleh Staff mencatat baris riwayat baru ke laundry_status_histories.
     */
    public function test_staff_update_status_records_status_history(): void
    {
        $staff1 = User::factory()->staff()->create(['name' => 'Kasir Maya']);
        $staff2 = User::factory()->staff()->create(['name' => 'Operator Doni']);
        $customer = Customer::factory()->create();

        $transaction = Transaction::create([
            'customer_id' => $customer->id,
            'created_by' => $staff1->id,
            'transaction_code' => 'TRK-20261015-001',
            'total_amount' => 20000.00,
            'status' => TransactionStatus::Diterima,
        ]);

        // Catat initial history jika ada
        LaundryStatusHistory::create([
            'transaction_id' => $transaction->id,
            'status' => TransactionStatus::Diterima,
            'changed_by' => $staff1->id,
            'changed_at' => now()->subHours(2),
        ]);

        // Operator Doni mengubah status ke 'Diproses'
        $response = $this->actingAs($staff2)->patch(route('staff.transactions.status', $transaction), [
            'status' => 'Diproses',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        // Pastikan status transaksi terupdate
        $transaction->refresh();
        $this->assertSame(TransactionStatus::Diproses, $transaction->status);

        // Pastikan baris log riwayat bertambah
        $this->assertDatabaseHas('laundry_status_histories', [
            'transaction_id' => $transaction->id,
            'status' => 'Diproses',
            'changed_by' => $staff2->id,
        ]);

        $this->assertCount(2, $transaction->statusHistories);

        // Doni mengubah lagi status ke 'Selesai'
        $this->actingAs($staff2)->patch(route('staff.transactions.status', $transaction), [
            'status' => 'Selesai',
        ]);

        $this->assertCount(3, $transaction->fresh()->statusHistories);
        $this->assertDatabaseHas('laundry_status_histories', [
            'transaction_id' => $transaction->id,
            'status' => 'Selesai',
            'changed_by' => $staff2->id,
        ]);
    }

    /**
     * Halaman detail transaksi menampilkan tombol 'Cetak Nota' dan tabel Riwayat Status cucian.
     */
    public function test_show_page_displays_print_button_and_status_history_table(): void
    {
        $this->withoutVite();

        $staff = User::factory()->staff()->create(['name' => 'Rina Staff']);
        $customer = Customer::factory()->create(['name' => 'Ahmad Dahlan']);
        $transaction = Transaction::create([
            'customer_id' => $customer->id,
            'created_by' => $staff->id,
            'transaction_code' => 'TRK-20261015-789',
            'total_amount' => 15000.00,
            'status' => TransactionStatus::Diproses,
        ]);

        LaundryStatusHistory::create([
            'transaction_id' => $transaction->id,
            'status' => TransactionStatus::Diterima,
            'changed_by' => $staff->id,
            'changed_at' => now()->subHours(3),
        ]);

        LaundryStatusHistory::create([
            'transaction_id' => $transaction->id,
            'status' => TransactionStatus::Diproses,
            'changed_by' => $staff->id,
            'changed_at' => now()->subHour(),
        ]);

        $response = $this->actingAs($staff)->get(route('staff.transactions.show', $transaction));

        $response->assertOk();
        $response->assertSee('Cetak Nota');
        $response->assertSee(route('staff.transactions.print', $transaction));
        $response->assertSee('Riwayat Pembaruan Status');
        $response->assertSee('Rina Staff');
        $response->assertSee('Diproses');
    }

    /**
     * Halaman cetak nota menampilkan rincian struk kasir thermal (print-friendly).
     */
    public function test_print_page_displays_thermal_receipt_details(): void
    {
        $this->withoutVite();

        $staff = User::factory()->staff()->create(['name' => 'Budi Kasir']);
        $customer = Customer::factory()->create([
            'name' => 'Citra Kirana',
            'phone' => '081299998888',
        ]);
        $service = Service::factory()->create([
            'name' => 'Cuci Kering Kilat',
            'price' => 12000.00,
            'is_active' => true,
        ]);

        $transaction = Transaction::create([
            'customer_id' => $customer->id,
            'created_by' => $staff->id,
            'transaction_code' => 'TRK-20261015-PRINT',
            'total_amount' => 24000.00,
            'status' => TransactionStatus::Diterima,
            'estimated_completed_at' => now()->addDays(2),
        ]);

        TransactionDetail::create([
            'transaction_id' => $transaction->id,
            'service_id' => $service->id,
            'weight' => 2.00,
            'unit_price' => 12000.00,
            'subtotal' => 24000.00,
        ]);

        $response = $this->actingAs($staff)->get(route('staff.transactions.print', $transaction));

        $response->assertOk();
        $response->assertSee('TRK-20261015-PRINT');
        $response->assertSee('Citra Kirana');
        $response->assertSee('081299998888');
        $response->assertSee('Cuci Kering Kilat');
        $response->assertSee('Rp 24.000');
        $response->assertSee('Budi Kasir');
        $response->assertSee('window.print()');
        $response->assertSee('58 mm (Mini)');
        $response->assertSee('80 mm (Standar)');
        $response->assertSee('setPaperSize');
    }

    /**
     * Guest dan Customer ditolak mengakses halaman cetak nota kasir.
     */
    public function test_guest_and_customer_cannot_access_print_page(): void
    {
        $customerUser = User::factory()->customer()->create();
        $staff = User::factory()->staff()->create();
        $customer = Customer::factory()->create();

        $transaction = Transaction::create([
            'customer_id' => $customer->id,
            'created_by' => $staff->id,
            'transaction_code' => 'TRK-20261015-GUEST',
            'total_amount' => 10000.00,
        ]);

        // Guest ditolak redirect ke login
        $responseGuest = $this->get(route('staff.transactions.print', $transaction));
        $responseGuest->assertRedirect(route('login'));

        // Customer ditolak 403 Forbidden
        $responseCustomer = $this->actingAs($customerUser)->get(route('staff.transactions.print', $transaction));
        $responseCustomer->assertStatus(403);
    }

    /**
     * Administrator dapat mengakses halaman cetak nota via rute admin dan staff.
     */
    public function test_admin_can_access_print_page(): void
    {
        $this->withoutVite();

        $admin = User::factory()->admin()->create();
        $customer = Customer::factory()->create();

        $transaction = Transaction::create([
            'customer_id' => $customer->id,
            'created_by' => $admin->id,
            'transaction_code' => 'TRK-20261015-ADMIN',
            'total_amount' => 18000.00,
        ]);

        $responseAdmin = $this->actingAs($admin)->get(route('admin.transactions.print', $transaction));
        $responseAdmin->assertOk();

        $responseStaff = $this->actingAs($admin)->get(route('staff.transactions.print', $transaction));
        $responseStaff->assertOk();
    }
}
