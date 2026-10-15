<?php

namespace Tests\Feature;

use App\Enums\TransactionStatus;
use App\Models\Customer;
use App\Models\Service;
use App\Models\Transaction;
use App\Models\TransactionDetail;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class StoreTransactionTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Guest ditolak dan diarahkan ke halaman login.
     */
    public function test_guest_is_redirected_to_login(): void
    {
        $response = $this->post(route('staff.transactions.store'), [
            'customer_id' => 1,
            'items' => [
                ['service_id' => 1, 'weight' => 2],
            ],
        ]);

        $response->assertRedirect(route('login'));
        $this->assertEquals(0, Transaction::count());
        $this->assertEquals(0, TransactionDetail::count());
    }

    /**
     * Customer ditolak dengan status 403 Forbidden.
     */
    public function test_customer_cannot_access_store_transaction_endpoint(): void
    {
        $customerUser = User::factory()->customer()->create();
        $customer = Customer::factory()->create();
        $service = Service::factory()->create(['is_active' => true]);

        $response = $this->actingAs($customerUser)->post(route('staff.transactions.store'), [
            'customer_id' => $customer->id,
            'items' => [
                ['service_id' => $service->id, 'weight' => 2.5],
            ],
        ]);

        $response->assertStatus(403);
        $this->assertEquals(0, Transaction::count());
        $this->assertEquals(0, TransactionDetail::count());
    }

    /**
     * Staff dengan input benar via web request: transaksi dan detail tersimpan,
     * total benar, status Diterima, created_by = id staff login, redirect back dengan flash success.
     */
    public function test_staff_can_store_transaction_successfully_via_web_request(): void
    {
        $staff = User::factory()->staff()->create();
        $customer = Customer::factory()->create();

        $service1 = Service::factory()->create([
            'name' => 'Cuci Kering',
            'price' => 7000.00,
            'is_active' => true,
        ]);

        $service2 = Service::factory()->create([
            'name' => 'Setrika Rapi',
            'price' => 5000.00,
            'is_active' => true,
        ]);

        $response = $this->actingAs($staff)
            ->post(route('staff.transactions.store'), [
                'customer_id' => $customer->id,
                'items' => [
                    ['service_id' => $service1->id, 'weight' => 2],
                    ['service_id' => $service2->id, 'weight' => 3],
                ],
            ]);

        $transaction = Transaction::first();
        $this->assertNotNull($transaction);
        $response->assertRedirect(route('staff.transactions.show', $transaction));
        $response->assertSessionHas('success');
        $this->assertEquals($customer->id, $transaction->customer_id);
        $this->assertEquals($staff->id, $transaction->created_by);
        $this->assertSame(TransactionStatus::Diterima, $transaction->status);
        $this->assertEquals('29000.00', $transaction->total_amount);

        // Flash session mengandung kode transaksi
        $this->assertStringContainsString($transaction->transaction_code, session('success'));

        // Cek detail transaksi di database
        $this->assertCount(2, $transaction->details);
        $this->assertDatabaseHas('transaction_details', [
            'transaction_id' => $transaction->id,
            'service_id' => $service1->id,
            'weight' => '2.00',
            'unit_price' => '7000.00',
            'subtotal' => '14000.00',
        ]);
        $this->assertDatabaseHas('transaction_details', [
            'transaction_id' => $transaction->id,
            'service_id' => $service2->id,
            'weight' => '3.00',
            'unit_price' => '5000.00',
            'subtotal' => '15000.00',
        ]);
    }

    /**
     * Request JSON mendapatkan respon 201 berisi id dan transaction_code.
     */
    public function test_staff_can_store_transaction_via_json_request_returns_201_with_code(): void
    {
        $staff = User::factory()->staff()->create();
        $customer = Customer::factory()->create();
        $service = Service::factory()->create([
            'price' => 10000.00,
            'is_active' => true,
        ]);

        $response = $this->actingAs($staff)->postJson(route('staff.transactions.store'), [
            'customer_id' => $customer->id,
            'items' => [
                ['service_id' => $service->id, 'weight' => 1.5],
            ],
        ]);

        $response->assertStatus(201);
        $response->assertJsonStructure([
            'id',
            'transaction_code',
        ]);

        $transaction = Transaction::first();
        $this->assertNotNull($transaction);
        $response->assertJson([
            'id' => $transaction->id,
            'transaction_code' => $transaction->transaction_code,
        ]);
    }

    /**
     * Input total_amount, unit_price, subtotal, status, transaction_code,
     * dan created_by palsu dari browser diabaikan dan ditimpa nilai resmi server.
     */
    public function test_manipulated_inputs_from_client_are_strictly_ignored(): void
    {
        $staff = User::factory()->staff()->create();
        $fakeStaff = User::factory()->staff()->create();
        $customer = Customer::factory()->create();
        $service = Service::factory()->create([
            'price' => 12000.00,
            'is_active' => true,
        ]);

        $response = $this->actingAs($staff)->postJson(route('staff.transactions.store'), [
            'customer_id' => $customer->id,
            'created_by' => $fakeStaff->id,
            'status' => 'Selesai',
            'total_amount' => '100.00',
            'transaction_code' => 'TRK-HACKED-999',
            'items' => [
                [
                    'service_id' => $service->id,
                    'weight' => 2,
                    'unit_price' => '50.00',
                    'subtotal' => '100.00',
                ],
            ],
        ]);

        $response->assertStatus(201);

        $transaction = Transaction::first();
        $this->assertNotNull($transaction);
        $this->assertEquals($staff->id, $transaction->created_by);
        $this->assertNotEquals($fakeStaff->id, $transaction->created_by);
        $this->assertSame(TransactionStatus::Diterima, $transaction->status);
        $this->assertEquals('24000.00', $transaction->total_amount);
        $this->assertNotEquals('TRK-HACKED-999', $transaction->transaction_code);

        $detail = $transaction->details->first();
        $this->assertEquals('12000.00', $detail->unit_price);
        $this->assertEquals('24000.00', $detail->subtotal);
    }

    /**
     * customer_id tidak ada atau tidak dikirim ditolak validasi.
     */
    public function test_validation_fails_when_customer_id_is_missing_or_not_found(): void
    {
        $staff = User::factory()->staff()->create();
        $service = Service::factory()->create(['is_active' => true]);

        // 1. customer_id tidak dikirim
        $responseMissing = $this->actingAs($staff)->postJson(route('staff.transactions.store'), [
            'items' => [
                ['service_id' => $service->id, 'weight' => 2],
            ],
        ]);
        $responseMissing->assertStatus(422);
        $responseMissing->assertJsonValidationErrors(['customer_id']);

        // 2. customer_id tidak ada di tabel customers
        $responseNotFound = $this->actingAs($staff)->postJson(route('staff.transactions.store'), [
            'customer_id' => 999999,
            'items' => [
                ['service_id' => $service->id, 'weight' => 2],
            ],
        ]);
        $responseNotFound->assertStatus(422);
        $responseNotFound->assertJsonValidationErrors(['customer_id']);

        $this->assertEquals(0, Transaction::count());
    }

    /**
     * items kosong atau tidak dikirim ditolak validasi.
     */
    public function test_validation_fails_when_items_is_empty_or_not_sent(): void
    {
        $staff = User::factory()->staff()->create();
        $customer = Customer::factory()->create();

        // 1. items tidak dikirim
        $responseMissing = $this->actingAs($staff)->postJson(route('staff.transactions.store'), [
            'customer_id' => $customer->id,
        ]);
        $responseMissing->assertStatus(422);
        $responseMissing->assertJsonValidationErrors(['items']);

        // 2. items array kosong
        $responseEmpty = $this->actingAs($staff)->postJson(route('staff.transactions.store'), [
            'customer_id' => $customer->id,
            'items' => [],
        ]);
        $responseEmpty->assertStatus(422);
        $responseEmpty->assertJsonValidationErrors(['items']);

        $this->assertEquals(0, Transaction::count());
    }

    /**
     * Service nonaktif ditolak dan tidak ada data tersisa di transactions maupun transaction_details.
     */
    public function test_validation_fails_when_service_is_inactive_and_no_data_is_persisted(): void
    {
        $staff = User::factory()->staff()->create();
        $customer = Customer::factory()->create();
        $inactiveService = Service::factory()->inactive()->create([
            'name' => 'Cuci Karpet Spesial',
        ]);

        $response = $this->actingAs($staff)->postJson(route('staff.transactions.store'), [
            'customer_id' => $customer->id,
            'items' => [
                ['service_id' => $inactiveService->id, 'weight' => 5],
            ],
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['items.0.service_id']);

        // Pastikan tidak ada data yang tersimpan sama sekali
        $this->assertEquals(0, Transaction::count());
        $this->assertEquals(0, TransactionDetail::count());
        $this->assertDatabaseMissing('transactions', ['customer_id' => $customer->id]);
        $this->assertDatabaseMissing('transaction_details', ['service_id' => $inactiveService->id]);
    }

    /**
     * service_id tidak ada di tabel services ditolak validasi.
     */
    public function test_validation_fails_when_service_id_does_not_exist(): void
    {
        $staff = User::factory()->staff()->create();
        $customer = Customer::factory()->create();

        $response = $this->actingAs($staff)->postJson(route('staff.transactions.store'), [
            'customer_id' => $customer->id,
            'items' => [
                ['service_id' => 999999, 'weight' => 2],
            ],
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['items.0.service_id']);
        $this->assertEquals(0, Transaction::count());
        $this->assertEquals(0, TransactionDetail::count());
    }

    /**
     * Weight bernilai nol, negatif, atau non-numerik ditolak validasi.
     */
    public function test_validation_fails_when_weight_is_zero_negative_or_non_numeric(): void
    {
        $staff = User::factory()->staff()->create();
        $customer = Customer::factory()->create();
        $service = Service::factory()->create(['is_active' => true]);

        // 1. Weight nol
        $responseZero = $this->actingAs($staff)->postJson(route('staff.transactions.store'), [
            'customer_id' => $customer->id,
            'items' => [
                ['service_id' => $service->id, 'weight' => 0],
            ],
        ]);
        $responseZero->assertStatus(422);
        $responseZero->assertJsonValidationErrors(['items.0.weight']);

        // 2. Weight negatif
        $responseNegative = $this->actingAs($staff)->postJson(route('staff.transactions.store'), [
            'customer_id' => $customer->id,
            'items' => [
                ['service_id' => $service->id, 'weight' => -2.5],
            ],
        ]);
        $responseNegative->assertStatus(422);
        $responseNegative->assertJsonValidationErrors(['items.0.weight']);

        // 3. Weight non-numerik
        $responseNonNumeric = $this->actingAs($staff)->postJson(route('staff.transactions.store'), [
            'customer_id' => $customer->id,
            'items' => [
                ['service_id' => $service->id, 'weight' => 'lima_kilo'],
            ],
        ]);
        $responseNonNumeric->assertStatus(422);
        $responseNonNumeric->assertJsonValidationErrors(['items.0.weight']);

        $this->assertEquals(0, Transaction::count());
        $this->assertEquals(0, TransactionDetail::count());
    }

    /**
     * Admin juga dapat mengakses dan menyimpan transaksi (mengikuti pola Customer Management).
     */
    public function test_admin_can_also_store_transaction_via_staff_and_admin_routes(): void
    {
        $admin = User::factory()->admin()->create();
        $customer = Customer::factory()->create();
        $service = Service::factory()->create(['is_active' => true, 'price' => 8000.00]);

        // 1. Admin menyimpan melalui rute staff.transactions.store
        $responseStaff = $this->actingAs($admin)->postJson(route('staff.transactions.store'), [
            'customer_id' => $customer->id,
            'items' => [
                ['service_id' => $service->id, 'weight' => 2],
            ],
        ]);
        $responseStaff->assertStatus(201);
        $this->assertDatabaseHas('transactions', [
            'customer_id' => $customer->id,
            'created_by' => $admin->id,
            'total_amount' => '16000.00',
        ]);

        // 2. Admin menyimpan melalui rute admin.transactions.store
        $responseAdmin = $this->actingAs($admin)->postJson(route('admin.transactions.store'), [
            'customer_id' => $customer->id,
            'items' => [
                ['service_id' => $service->id, 'weight' => 3],
            ],
        ]);
        $responseAdmin->assertStatus(201);
        $this->assertDatabaseHas('transactions', [
            'customer_id' => $customer->id,
            'created_by' => $admin->id,
            'total_amount' => '24000.00',
        ]);
    }

    /**
     * Saat CreateTransaction melempar InvalidArgumentException, dibalas validation error (422),
     * bukan error 500.
     */
    public function test_create_transaction_invalid_argument_exception_returns_422_not_500(): void
    {
        $staff = User::factory()->staff()->create();
        $customer = Customer::factory()->create();
        $service = Service::factory()->create(['is_active' => true]);

        // Simulasikan race condition: setelah FormRequest memvalidasi keberadaan customer,
        // data customer terhapus sebelum CreateTransaction dieksekusi.
        $deleted = false;
        DB::listen(function ($query) use ($customer, &$deleted) {
            if (! $deleted && str_contains($query->sql, 'services')) {
                $deleted = true;
                DB::table('customers')->where('id', $customer->id)->delete();
            }
        });

        $response = $this->actingAs($staff)->postJson(route('staff.transactions.store'), [
            'customer_id' => $customer->id,
            'items' => [
                ['service_id' => $service->id, 'weight' => 2],
            ],
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['items']);
        $this->assertEquals(0, Transaction::count());
    }
}
