<?php

namespace Tests\Feature;

use App\Enums\TransactionStatus;
use App\Models\Customer;
use App\Models\Payment;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TransactionBulkStatusTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Staff dapat memperbarui status banyak transaksi sekaligus secara massal.
     */
    public function test_staff_can_bulk_update_transaction_status(): void
    {
        $staff = User::factory()->staff()->create();
        $customer = Customer::factory()->create();

        $t1 = Transaction::create([
            'customer_id' => $customer->id,
            'created_by' => $staff->id,
            'transaction_code' => 'TRK-BULK-001',
            'total_amount' => 15000,
            'status' => TransactionStatus::Diterima,
        ]);

        $t2 = Transaction::create([
            'customer_id' => $customer->id,
            'created_by' => $staff->id,
            'transaction_code' => 'TRK-BULK-002',
            'total_amount' => 25000,
            'status' => TransactionStatus::Diterima,
        ]);

        $response = $this->actingAs($staff)->patch(route('staff.transactions.bulk-status'), [
            'transaction_ids' => [$t1->id, $t2->id],
            'status' => 'Diproses',
            'notes' => 'Mulai cuci bersamaan',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertEquals(TransactionStatus::Diproses, $t1->fresh()->status);
        $this->assertEquals(TransactionStatus::Diproses, $t2->fresh()->status);

        $this->assertDatabaseHas('laundry_status_histories', [
            'transaction_id' => $t1->id,
            'status' => 'Diproses',
            'notes' => 'Mulai cuci bersamaan',
            'changed_by' => $staff->id,
        ]);
    }

    /**
     * Transaksi yang belum lunas tidak dapat diubah massal ke status Diambil.
     */
    public function test_unpaid_transactions_are_skipped_when_bulk_updating_to_diambil(): void
    {
        $staff = User::factory()->staff()->create();
        $customer = Customer::factory()->create();

        $unpaidTransaction = Transaction::create([
            'customer_id' => $customer->id,
            'created_by' => $staff->id,
            'transaction_code' => 'TRK-BULK-UNPAID',
            'total_amount' => 50000,
            'status' => TransactionStatus::Selesai,
        ]);

        $paidTransaction = Transaction::create([
            'customer_id' => $customer->id,
            'created_by' => $staff->id,
            'transaction_code' => 'TRK-BULK-PAID',
            'total_amount' => 30000,
            'status' => TransactionStatus::Selesai,
        ]);

        Payment::create([
            'transaction_id' => $paidTransaction->id,
            'amount' => 30000,
            'payment_type' => 'Pembayaran Penuh',
            'method' => 'Tunai',
            'verification_status' => 'Tidak Diperlukan',
            'paid_at' => now(),
            'created_by' => $staff->id,
        ]);

        $response = $this->actingAs($staff)->patch(route('staff.transactions.bulk-status'), [
            'transaction_ids' => [$unpaidTransaction->id, $paidTransaction->id],
            'status' => 'Diambil',
        ]);

        $response->assertRedirect();

        // Yang belum lunas tetap Selesai (dilewati)
        $this->assertEquals(TransactionStatus::Selesai, $unpaidTransaction->fresh()->status);
        // Yang sudah lunas berhasil jadi Diambil
        $this->assertEquals(TransactionStatus::Diambil, $paidTransaction->fresh()->status);
    }
}
