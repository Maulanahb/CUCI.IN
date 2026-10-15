<?php

namespace Tests\Feature;

use App\Enums\TransactionStatus;
use App\Models\Customer;
use App\Models\Service;
use App\Models\Transaction;
use App\Models\TransactionDetail;
use App\Models\User;
use App\Services\CreateTransaction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use InvalidArgumentException;
use Tests\TestCase;

class CreateTransactionTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test transaksi dan detail tersimpan dengan benar dan total yang akurat.
     */
    public function test_creates_transaction_and_details_with_correct_totals(): void
    {
        $customer = Customer::factory()->create();
        $staff = User::factory()->staff()->create();

        $service1 = Service::create([
            'name' => 'Cuci Kering',
            'price' => 7000.00,
            'estimated_duration' => 24,
            'is_active' => true,
        ]);

        $service2 = Service::create([
            'name' => 'Setrika',
            'price' => 10000.00,
            'estimated_duration' => 48,
            'is_active' => true,
        ]);

        $items = [
            ['service_id' => $service1->id, 'weight' => 2],
            ['service_id' => $service2->id, 'weight' => 3],
        ];

        $transaction = CreateTransaction::execute($customer->id, $staff->id, $items);

        $this->assertInstanceOf(Transaction::class, $transaction);
        $this->assertEquals($customer->id, $transaction->customer_id);
        $this->assertEquals($staff->id, $transaction->created_by);
        $this->assertEquals('44000.00', $transaction->total_amount);
        $this->assertSame(TransactionStatus::Diterima, $transaction->status);

        // Pastikan relasi details dimuat dan sesuai
        $this->assertTrue($transaction->relationLoaded('details'));
        $this->assertCount(2, $transaction->details);

        $detail1 = $transaction->details->firstWhere('service_id', $service1->id);
        $this->assertNotNull($detail1);
        $this->assertEquals('2.00', $detail1->weight);
        $this->assertEquals('7000.00', $detail1->unit_price);
        $this->assertEquals('14000.00', $detail1->subtotal);

        $detail2 = $transaction->details->firstWhere('service_id', $service2->id);
        $this->assertNotNull($detail2);
        $this->assertEquals('3.00', $detail2->weight);
        $this->assertEquals('10000.00', $detail2->unit_price);
        $this->assertEquals('30000.00', $detail2->subtotal);

        // Verifikasi di database
        $this->assertDatabaseHas('transactions', [
            'id' => $transaction->id,
            'customer_id' => $customer->id,
            'created_by' => $staff->id,
            'total_amount' => '44000.00',
            'status' => 'Diterima',
        ]);

        $this->assertDatabaseHas('transaction_details', [
            'transaction_id' => $transaction->id,
            'service_id' => $service1->id,
            'subtotal' => '14000.00',
        ]);

        $this->assertDatabaseHas('transaction_details', [
            'transaction_id' => $transaction->id,
            'service_id' => $service2->id,
            'subtotal' => '30000.00',
        ]);
    }

    /**
     * Test status awal transaksi selalu 'Diterima'.
     */
    public function test_initial_status_is_diterima(): void
    {
        $customer = Customer::factory()->create();
        $staff = User::factory()->staff()->create();
        $service = Service::create([
            'name' => 'Cuci Kering',
            'price' => 7000.00,
            'estimated_duration' => 24,
            'is_active' => true,
        ]);

        $transaction = CreateTransaction::execute($customer->id, $staff->id, [
            ['service_id' => $service->id, 'weight' => 1],
        ]);

        $this->assertSame(TransactionStatus::Diterima, $transaction->status);
        $this->assertEquals('Diterima', $transaction->status->value);
    }

    /**
     * Test kode transaksi terbentuk dan unik.
     */
    public function test_transaction_code_is_generated_and_unique(): void
    {
        $customer = Customer::factory()->create();
        $staff = User::factory()->staff()->create();
        $service = Service::create([
            'name' => 'Cuci Kering',
            'price' => 7000.00,
            'estimated_duration' => 24,
            'is_active' => true,
        ]);

        $tx1 = CreateTransaction::execute($customer->id, $staff->id, [
            ['service_id' => $service->id, 'weight' => 1],
        ]);

        $tx2 = CreateTransaction::execute($customer->id, $staff->id, [
            ['service_id' => $service->id, 'weight' => 2],
        ]);

        $this->assertNotNull($tx1->transaction_code);
        $this->assertNotNull($tx2->transaction_code);
        $this->assertStringStartsWith('TRX-', $tx1->transaction_code);
        $this->assertStringStartsWith('TRX-', $tx2->transaction_code);
        $this->assertNotEquals($tx1->transaction_code, $tx2->transaction_code);
    }

    /**
     * Test unit_price tetap tersimpan meski harga layanan diubah di kemudian hari.
     */
    public function test_unit_price_remains_unchanged_even_if_service_price_is_updated_later(): void
    {
        $customer = Customer::factory()->create();
        $staff = User::factory()->staff()->create();

        $service = Service::create([
            'name' => 'Cuci Kering',
            'price' => 10000.00,
            'estimated_duration' => 24,
            'is_active' => true,
        ]);

        $transaction = CreateTransaction::execute($customer->id, $staff->id, [
            ['service_id' => $service->id, 'weight' => 2],
        ]);

        $detail = $transaction->details->first();
        $this->assertEquals('10000.00', $detail->unit_price);
        $this->assertEquals('20000.00', $detail->subtotal);

        // Ubah harga service di master data
        $service->update(['price' => 15000.00]);

        // Detail transaksi lama harus tetap menyimpan harga saat transaksi dibuat (10000.00)
        $reloadedDetail = TransactionDetail::find($detail->id);
        $this->assertEquals('10000.00', $reloadedDetail->unit_price);
        $this->assertEquals('20000.00', $reloadedDetail->subtotal);

        $reloadedTx = Transaction::find($transaction->id);
        $this->assertEquals('20000.00', $reloadedTx->total_amount);
    }

    /**
     * Test input harga, subtotal, dan total dari luar diabaikan.
     */
    public function test_external_totals_and_prices_are_ignored(): void
    {
        $customer = Customer::factory()->create();
        $staff = User::factory()->staff()->create();

        $service = Service::create([
            'name' => 'Cuci Kering',
            'price' => 8000.00,
            'estimated_duration' => 24,
            'is_active' => true,
        ]);

        $payload = [
            'customer_id' => $customer->id,
            'created_by' => $staff->id,
            'total_amount' => 100, // Harus diabaikan
            'status' => 'Selesai', // Harus diabaikan
            'items' => [
                [
                    'service_id' => $service->id,
                    'weight' => 2,
                    'unit_price' => 50, // Harus diabaikan
                    'subtotal' => 100,  // Harus diabaikan
                ],
            ],
        ];

        $transaction = CreateTransaction::execute($payload);

        $this->assertEquals('16000.00', $transaction->total_amount);
        $this->assertSame(TransactionStatus::Diterima, $transaction->status);

        $detail = $transaction->details->first();
        $this->assertEquals('8000.00', $detail->unit_price);
        $this->assertEquals('16000.00', $detail->subtotal);
    }

    /**
     * Test rollback penuh jika salah satu item tidak valid (misal service tidak aktif).
     */
    public function test_full_rollback_when_one_item_is_invalid(): void
    {
        $customer = Customer::factory()->create();
        $staff = User::factory()->staff()->create();

        $activeService = Service::create([
            'name' => 'Cuci Kering',
            'price' => 7000.00,
            'estimated_duration' => 24,
            'is_active' => true,
        ]);

        $inactiveService = Service::create([
            'name' => 'Layanan Lama',
            'price' => 10000.00,
            'estimated_duration' => 24,
            'is_active' => false,
        ]);

        $txCountBefore = Transaction::count();
        $detailCountBefore = TransactionDetail::count();

        try {
            CreateTransaction::execute($customer->id, $staff->id, [
                ['service_id' => $activeService->id, 'weight' => 2],
                ['service_id' => $inactiveService->id, 'weight' => 1],
            ]);
            $this->fail('Seharusnya melempar InvalidArgumentException karena salah satu layanan tidak aktif.');
        } catch (InvalidArgumentException $e) {
            $this->assertStringContainsString('tidak aktif', $e->getMessage());
        }

        // Pastikan tidak ada data yang tersimpan sama sekali (transaksi di-rollback)
        $this->assertEquals($txCountBefore, Transaction::count());
        $this->assertEquals($detailCountBefore, TransactionDetail::count());
    }

    /**
     * Test rollback penuh jika kegagalan terjadi SETELAH Transaction dibuat (membuktikan atomicity transaksi database).
     */
    public function test_full_rollback_when_failure_occurs_after_transaction_is_created(): void
    {
        $customer = Customer::factory()->create();
        $staff = User::factory()->staff()->create();
        $service = Service::create([
            'name' => 'Cuci Kering',
            'price' => 10000.00,
            'estimated_duration' => 24,
            'is_active' => true,
        ]);

        $txCountBefore = Transaction::count();
        $detailCountBefore = TransactionDetail::count();

        // Pasang listener pada TransactionDetail::creating untuk menyimulasikan kegagalan
        // tepat SETELAH Transaction::create() berhasil dijalankan
        TransactionDetail::creating(function (): void {
            throw new \RuntimeException('Simulasi kegagalan saat insert transaction_details.');
        });

        try {
            CreateTransaction::execute($customer->id, $staff->id, [
                ['service_id' => $service->id, 'weight' => 2],
            ]);
            $this->fail('Seharusnya melempar RuntimeException.');
        } catch (\RuntimeException $e) {
            $this->assertSame('Simulasi kegagalan saat insert transaction_details.', $e->getMessage());
        } finally {
            // Bersihkan listener agar tidak memengaruhi test lain
            TransactionDetail::flushEventListeners();
        }

        // Buktikan bahwa tabel transactions dan transaction_details tetap kosong (di-rollback penuh)
        $this->assertEquals($txCountBefore, Transaction::count());
        $this->assertEquals($detailCountBefore, TransactionDetail::count());
    }

    /**
     * Test jika created_by kosong (null), proses ditolak dengan InvalidArgumentException.
     */
    public function test_rejects_empty_created_by(): void
    {
        $customer = Customer::factory()->create();
        $service = Service::create([
            'name' => 'Cuci Kering',
            'price' => 7000.00,
            'estimated_duration' => 24,
            'is_active' => true,
        ]);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessageMatches('/created by/i');

        CreateTransaction::execute($customer->id, null, [
            ['service_id' => $service->id, 'weight' => 2],
        ]);
    }

    /**
     * Test jika created_by tidak disertakan di array payload, proses ditolak.
     */
    public function test_rejects_missing_created_by_in_payload(): void
    {
        $customer = Customer::factory()->create();
        $service = Service::create([
            'name' => 'Cuci Kering',
            'price' => 7000.00,
            'estimated_duration' => 24,
            'is_active' => true,
        ]);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessageMatches('/created by/i');

        CreateTransaction::execute([
            'customer_id' => $customer->id,
            'items' => [
                ['service_id' => $service->id, 'weight' => 2],
            ],
        ]);
    }

    /**
     * Test jika customer tidak ditemukan, proses ditolak dan tidak ada data dibuat.
     */
    public function test_rejects_nonexistent_customer(): void
    {
        $staff = User::factory()->staff()->create();
        $service = Service::create([
            'name' => 'Cuci Kering',
            'price' => 7000.00,
            'estimated_duration' => 24,
            'is_active' => true,
        ]);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessageMatches('/customer dengan id/i');

        CreateTransaction::execute(999999, $staff->id, [
            ['service_id' => $service->id, 'weight' => 2],
        ]);
    }

    /**
     * Test jika daftar item kosong, proses ditolak.
     */
    public function test_rejects_empty_items_list(): void
    {
        $customer = Customer::factory()->create();
        $staff = User::factory()->staff()->create();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessageMatches('/tidak boleh kosong/i');

        CreateTransaction::execute($customer->id, $staff->id, []);
    }

    /**
     * Test pemanggilan melalui instance dan array payload.
     */
    public function test_can_be_executed_with_array_payload_and_instance(): void
    {
        $customer = Customer::factory()->create();
        $staff = User::factory()->staff()->create();
        $service = Service::create([
            'name' => 'Cuci Kering',
            'price' => 10000.00,
            'estimated_duration' => 24,
            'is_active' => true,
        ]);

        $action = new CreateTransaction;

        $transaction = $action->handle([
            'customer_id' => $customer->id,
            'created_by' => $staff->id,
            'items' => [
                ['service_id' => $service->id, 'weight' => 1.5],
            ],
        ]);

        $this->assertInstanceOf(Transaction::class, $transaction);
        $this->assertEquals('15000.00', $transaction->total_amount);
    }
}
