<?php

namespace Tests\Feature;

use App\Enums\TransactionStatus;
use App\Models\Customer;
use App\Models\Transaction;
use App\Models\User;
use App\Services\TransactionCodeGenerator;
use Carbon\CarbonInterface;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class TransactionTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Membuat transaksi dengan customer dan staff asli dari factory.
     * Kode transaksi dibuat otomatis kecuali diisi lewat $overrides.
     */
    private function makeTransaction(array $overrides = []): Transaction
    {
        return Transaction::create(array_merge([
            'customer_id' => Customer::factory()->create()->id,
            'created_by' => User::factory()->staff()->create()->id,
            'total_amount' => 50000,
        ], $overrides));
    }

    /**
     * Test kode transaksi pertama yang digenerate adalah TRX-0001.
     */
    public function test_first_transaction_code_is_trx_0001(): void
    {
        $code = TransactionCodeGenerator::generate();
        $this->assertEquals('TRX-0001', $code);

        $transaction = $this->makeTransaction();

        $this->assertEquals('TRX-0001', $transaction->transaction_code);
        $this->assertDatabaseHas('transactions', [
            'id' => $transaction->id,
            'transaction_code' => 'TRX-0001',
        ]);
    }

    /**
     * Test kode transaksi dihasilkan secara berurutan (TRX-0001, TRX-0002, TRX-0003).
     */
    public function test_transaction_codes_are_sequential(): void
    {
        $t1 = $this->makeTransaction(['total_amount' => 25000]);
        $t2 = $this->makeTransaction(['total_amount' => 30000]);
        $t3 = $this->makeTransaction(['total_amount' => 45000]);

        $this->assertEquals('TRX-0001', $t1->transaction_code);
        $this->assertEquals('TRX-0002', $t2->transaction_code);
        $this->assertEquals('TRX-0003', $t3->transaction_code);
    }

    /**
     * Test kode transaksi tetap unik dan tidak bentrok meskipun ada data yang dihapus (tidak memakai count() + 1).
     */
    public function test_transaction_codes_are_unique_and_not_relying_on_count(): void
    {
        $t1 = $this->makeTransaction(['total_amount' => 10000]);
        $t2 = $this->makeTransaction(['total_amount' => 20000]);
        $t3 = $this->makeTransaction(['total_amount' => 30000]);

        $this->assertEquals('TRX-0001', $t1->transaction_code);
        $this->assertEquals('TRX-0002', $t2->transaction_code);
        $this->assertEquals('TRX-0003', $t3->transaction_code);

        // Hapus salah satu transaksi (misal data TRX-0002)
        $t2->delete();

        // Jumlah transaksi sekarang 2. Jika menggunakan count() + 1, kodenya akan menjadi TRX-0003 (duplikat).
        $this->assertEquals(2, Transaction::count());

        $t4 = $this->makeTransaction(['total_amount' => 40000]);

        // Dengan generator yang aman, kode berikutnya adalah TRX-0004
        $this->assertEquals('TRX-0004', $t4->transaction_code);
        $this->assertDatabaseHas('transactions', [
            'id' => $t4->id,
            'transaction_code' => 'TRX-0004',
        ]);
    }

    /**
     * Test status default transaksi adalah 'Diterima' pada level model dan migrasi database.
     */
    public function test_transaction_default_status_is_diterima(): void
    {
        // 1. Verifikasi default status pada Eloquent model instance
        $transaction = $this->makeTransaction(['total_amount' => 75000]);

        $this->assertSame(TransactionStatus::Diterima, $transaction->status);
        $this->assertEquals('Diterima', $transaction->status->value);

        $this->assertDatabaseHas('transactions', [
            'id' => $transaction->id,
            'status' => 'Diterima',
        ]);

        // 2. Verifikasi default status pada tingkat skema database / migrasi (raw DB insert tanpa field status)
        DB::table('transactions')->insert([
            'transaction_code' => 'TRX-RAW-01',
            'customer_id' => Customer::factory()->create()->id,
            'created_by' => User::factory()->staff()->create()->id,
            'total_amount' => 20000,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->assertDatabaseHas('transactions', [
            'transaction_code' => 'TRX-RAW-01',
            'status' => 'Diterima',
        ]);
    }

    /**
     * Test created_by boleh kosong (nullable) dan estimated_completed_at tersimpan sebagai datetime.
     */
    public function test_transaction_can_be_created_without_created_by(): void
    {
        $transaction = $this->makeTransaction([
            'created_by' => null,
            'total_amount' => 15000,
            'estimated_completed_at' => now()->addDays(1),
        ]);

        $this->assertNotNull($transaction->id);
        $this->assertNull($transaction->created_by);
        $this->assertInstanceOf(CarbonInterface::class, $transaction->estimated_completed_at);
    }

    /**
     * Test customer_id yang tidak ada di tabel customers ditolak oleh foreign key.
     */
    public function test_transaction_with_nonexistent_customer_is_rejected(): void
    {
        $this->expectException(QueryException::class);

        $this->makeTransaction(['customer_id' => 999999]);
    }

    /**
     * Test format padding angka bertambah dengan benar melampaui 4 digit (misal TRX-9999 -> TRX-10000).
     */
    public function test_transaction_code_expands_beyond_four_digits(): void
    {
        $this->makeTransaction(['transaction_code' => 'TRX-9999']);

        $this->assertEquals('TRX-10000', TransactionCodeGenerator::generate());
    }

    /**
     * Test generator dapat dipanggil melalui instance class (bukan hanya static).
     */
    public function test_generator_can_be_called_via_instance(): void
    {
        $generator = new TransactionCodeGenerator;
        $this->assertEquals('TRX-0001', $generator->getNextCode());
    }
}
