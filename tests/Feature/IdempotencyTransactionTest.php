<?php

namespace Tests\Feature;

use App\Enums\TransactionStatus;
use App\Models\Customer;
use App\Models\IdempotencyKey;
use App\Models\Payment;
use App\Models\Service;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class IdempotencyTransactionTest extends TestCase
{
    use RefreshDatabase;

    private User $staff;

    private User $admin;

    private Customer $customer;

    private Service $service;

    private Service $service2;

    protected function setUp(): void
    {
        parent::setUp();

        $this->staff = User::factory()->staff()->create();
        $this->admin = User::factory()->admin()->create();
        $this->customer = Customer::factory()->create();

        $this->service = Service::factory()->create([
            'name' => 'Cuci Kering',
            'price' => 10000.00,
            'is_active' => true,
        ]);

        $this->service2 = Service::factory()->create([
            'name' => 'Setrika Saja',
            'price' => 8000.00,
            'is_active' => true,
        ]);
    }

    /**
     * Test 1: First POS transaction creates record in db and idempotency_keys
     */
    public function test_first_pos_transaction_creates_record_in_db_and_idempotency_keys(): void
    {
        $key = (string) Str::uuid();
        $payload = [
            'customer_id' => $this->customer->id,
            'items' => [
                ['service_id' => $this->service->id, 'weight' => 2.0],
            ],
        ];

        $response = $this->actingAs($this->staff)->post(
            route('staff.transactions.store'),
            $payload,
            ['Idempotency-Key' => $key]
        );

        $response->assertStatus(302);
        $this->assertDatabaseCount('transactions', 1);
        $this->assertDatabaseCount('transaction_details', 1);

        $transaction = Transaction::first();
        $this->assertNotNull($transaction);
        $response->assertRedirect(route('staff.transactions.show', $transaction));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('idempotency_keys', [
            'key' => $key,
            'user_id' => $this->staff->id,
            'status' => 'completed',
            'response_code' => 302,
        ]);
    }

    /**
     * Test 2: Duplicate identical request replayed with Idempotent-Replayed: true without creating second transaction
     */
    public function test_duplicate_pos_transaction_request_is_replayed_without_creating_second_transaction(): void
    {
        $key = (string) Str::uuid();
        $payload = [
            'customer_id' => $this->customer->id,
            'items' => [
                ['service_id' => $this->service->id, 'weight' => 2.0],
            ],
        ];

        // Request 1: Original creation
        $res1 = $this->actingAs($this->staff)->post(
            route('staff.transactions.store'),
            $payload,
            ['Idempotency-Key' => $key]
        );
        $res1->assertStatus(302);
        $this->assertDatabaseCount('transactions', 1);

        $transaction = Transaction::first();

        // Request 2: Duplicate identical replay
        $res2 = $this->actingAs($this->staff)->post(
            route('staff.transactions.store'),
            $payload,
            ['Idempotency-Key' => $key]
        );

        $res2->assertStatus(302);
        $res2->assertHeader('Idempotent-Replayed', 'true');
        $res2->assertRedirect(route('staff.transactions.show', $transaction));

        // Verifikasi tidak ada baris transaksi atau detail baru yang dibuat
        $this->assertDatabaseCount('transactions', 1);
        $this->assertDatabaseCount('transaction_details', 1);
        $this->assertDatabaseCount('idempotency_keys', 1);
    }

    /**
     * Test 3: Duplicate JSON request replayed with 201 and Idempotent-Replayed: true
     */
    public function test_duplicate_json_request_is_replayed_with_201_and_idempotent_replayed_header(): void
    {
        $key = (string) Str::uuid();
        $payload = [
            'customer_id' => $this->customer->id,
            'items' => [
                ['service_id' => $this->service->id, 'weight' => 3.0],
            ],
        ];

        // Request 1: Original JSON request
        $res1 = $this->actingAs($this->staff)->postJson(
            route('staff.transactions.store'),
            $payload,
            ['Idempotency-Key' => $key]
        );

        $res1->assertStatus(201);
        $data1 = $res1->json();
        $this->assertArrayHasKey('id', $data1);
        $this->assertArrayHasKey('transaction_code', $data1);

        // Request 2: Duplicate JSON request
        $res2 = $this->actingAs($this->staff)->postJson(
            route('staff.transactions.store'),
            $payload,
            ['Idempotency-Key' => $key]
        );

        $res2->assertStatus(201);
        $res2->assertHeader('Idempotent-Replayed', 'true');
        $data2 = $res2->json();

        // Data respons harus identik dengan request pertama
        $this->assertSame($data1['id'], $data2['id']);
        $this->assertSame($data1['transaction_code'], $data2['transaction_code']);
        $this->assertDatabaseCount('transactions', 1);
    }

    /**
     * Test 4: In-progress concurrent request returns 409 Conflict
     */
    public function test_in_progress_concurrent_request_returns_409_conflict(): void
    {
        $key = (string) Str::uuid();

        // Simulasi record idempotency yang sedang diproses di worker lain
        IdempotencyKey::create([
            'key' => $key,
            'user_id' => $this->staff->id,
            'route' => 'staff.transactions.store',
            'request_hash' => hash('sha256', 'dummy-hash'),
            'status' => 'processing',
            'created_at' => now(),
        ]);

        $payload = [
            'customer_id' => $this->customer->id,
            'items' => [
                ['service_id' => $this->service->id, 'weight' => 2.0],
            ],
        ];

        $response = $this->actingAs($this->staff)->postJson(
            route('staff.transactions.store'),
            $payload,
            ['Idempotency-Key' => $key]
        );

        $response->assertStatus(409);
        $response->assertJson(['message' => 'Permintaan sedang diproses. Mohon tunggu sesaat.']);
        $this->assertDatabaseCount('transactions', 0);
    }

    /**
     * Test 5: Same key with different payload returns 422 Unprocessable Content
     */
    public function test_same_key_with_different_payload_returns_422_unprocessable_content(): void
    {
        $key = (string) Str::uuid();
        $payload1 = [
            'customer_id' => $this->customer->id,
            'items' => [
                ['service_id' => $this->service->id, 'weight' => 2.0],
            ],
        ];

        // Request 1 berhasil
        $res1 = $this->actingAs($this->staff)->postJson(
            route('staff.transactions.store'),
            $payload1,
            ['Idempotency-Key' => $key]
        );
        $res1->assertStatus(201);

        // Request 2 dengan payload berbeda (berat 5.0 kg) tapi key sama
        $payload2 = [
            'customer_id' => $this->customer->id,
            'items' => [
                ['service_id' => $this->service->id, 'weight' => 5.0],
            ],
        ];

        $res2 = $this->actingAs($this->staff)->postJson(
            route('staff.transactions.store'),
            $payload2,
            ['Idempotency-Key' => $key]
        );

        $res2->assertStatus(422);
        $res2->assertJson(['message' => 'Idempotency key payload mismatch.']);
        $this->assertDatabaseCount('transactions', 1);
    }

    /**
     * Test 6: Request without key is pass-through (backward compatible, 0 idempotency rows)
     */
    public function test_request_without_idempotency_key_is_pass_through_and_creates_no_idempotency_record(): void
    {
        $payload = [
            'customer_id' => $this->customer->id,
            'items' => [
                ['service_id' => $this->service->id, 'weight' => 2.5],
            ],
        ];

        // Tanpa header Idempotency-Key dan tanpa form input
        $response = $this->actingAs($this->staff)->post(
            route('staff.transactions.store'),
            $payload
        );

        $response->assertStatus(302);
        $this->assertDatabaseCount('transactions', 1);
        $this->assertDatabaseCount('idempotency_keys', 0);
    }

    /**
     * Test 7: Request with validation error or >= 400 deletes idempotency key allowing retry
     */
    public function test_request_with_validation_error_deletes_idempotency_key_allowing_retry(): void
    {
        $key = (string) Str::uuid();

        // Payload salah (items kosong)
        $invalidPayload = [
            'customer_id' => $this->customer->id,
            'items' => [],
        ];

        $res1 = $this->actingAs($this->staff)->post(
            route('staff.transactions.store'),
            $invalidPayload,
            ['Idempotency-Key' => $key]
        );

        $res1->assertSessionHasErrors('items');
        // Kunci harus dihapus agar pengguna tidak terkunci
        $this->assertDatabaseCount('idempotency_keys', 0);
        $this->assertDatabaseCount('transactions', 0);

        // Retry dengan data valid menggunakan KEY YANG SAMA
        $validPayload = [
            'customer_id' => $this->customer->id,
            'items' => [
                ['service_id' => $this->service->id, 'weight' => 2.0],
            ],
        ];

        $res2 = $this->actingAs($this->staff)->post(
            route('staff.transactions.store'),
            $validPayload,
            ['Idempotency-Key' => $key]
        );

        $res2->assertStatus(302);
        $this->assertDatabaseCount('transactions', 1);
        $this->assertDatabaseCount('idempotency_keys', 1);
        $this->assertDatabaseHas('idempotency_keys', [
            'key' => $key,
            'status' => 'completed',
        ]);
    }

    /**
     * Test 8: Blade form hidden input idempotency_key supported
     */
    public function test_idempotency_key_supplied_via_form_body_input_works_for_blade_form(): void
    {
        $key = (string) Str::uuid();

        // Dikirim sebagai parameter request body (simulasi input hidden Blade)
        $payload = [
            'idempotency_key' => $key,
            'customer_id' => $this->customer->id,
            'items' => [
                ['service_id' => $this->service->id, 'weight' => 1.5],
            ],
        ];

        // Request 1: Form submit
        $res1 = $this->actingAs($this->staff)->post(
            route('staff.transactions.store'),
            $payload
        );
        $res1->assertStatus(302);
        $this->assertDatabaseCount('transactions', 1);

        // Request 2: Duplicate form submit
        $res2 = $this->actingAs($this->staff)->post(
            route('staff.transactions.store'),
            $payload
        );

        $res2->assertStatus(302);
        $res2->assertHeader('Idempotent-Replayed', 'true');
        $this->assertDatabaseCount('transactions', 1);
        $this->assertDatabaseHas('idempotency_keys', [
            'key' => $key,
            'status' => 'completed',
        ]);
    }

    /**
     * Test 9: Payment recording prevents double charge / duplicate payment row
     */
    public function test_payment_recording_prevents_duplicate_payment_with_same_idempotency_key(): void
    {
        $key = (string) Str::uuid();

        $transaction = Transaction::factory()->create([
            'customer_id' => $this->customer->id,
            'created_by' => $this->staff->id,
            'total_amount' => 50000.00,
            'status' => TransactionStatus::Diterima,
        ]);

        $paymentPayload = [
            'amount' => 50000.00,
            'method' => 'Tunai',
        ];

        // Payment 1: Catat pembayaran pertama
        $res1 = $this->actingAs($this->staff)->post(
            route('staff.transactions.payments.store', $transaction),
            $paymentPayload,
            ['Idempotency-Key' => $key]
        );
        $res1->assertRedirect();
        $this->assertDatabaseCount('payments', 1);

        // Payment 2: Duplikasi submit pembayaran
        $res2 = $this->actingAs($this->staff)->post(
            route('staff.transactions.payments.store', $transaction),
            $paymentPayload,
            ['Idempotency-Key' => $key]
        );

        $res2->assertRedirect();
        $res2->assertHeader('Idempotent-Replayed', 'true');

        // Sangat krusial: Tidak ada baris pembayaran kedua di database
        $this->assertDatabaseCount('payments', 1);

        $payment = Payment::first();
        $this->assertEquals(50000.00, (float) $payment->amount);

        $transaction->refresh();
        $this->assertEquals(0, (float) $transaction->remaining_amount);
        $this->assertEquals('Lunas', (string) $transaction->payment_status);
    }

    /**
     * Test 10: Admin transaction creation route is also guarded
     */
    public function test_admin_transaction_creation_route_is_guarded_by_idempotency(): void
    {
        $key = (string) Str::uuid();
        $payload = [
            'customer_id' => $this->customer->id,
            'items' => [
                ['service_id' => $this->service->id, 'weight' => 4.0],
            ],
        ];

        // Request 1 via admin route
        $res1 = $this->actingAs($this->admin)->post(
            route('admin.transactions.store'),
            $payload,
            ['Idempotency-Key' => $key]
        );
        $res1->assertStatus(302);
        $this->assertDatabaseCount('transactions', 1);

        $transaction = Transaction::first();

        // Request 2 duplicate via admin route
        $res2 = $this->actingAs($this->admin)->post(
            route('admin.transactions.store'),
            $payload,
            ['Idempotency-Key' => $key]
        );

        $res2->assertStatus(302);
        $res2->assertHeader('Idempotent-Replayed', 'true');
        $res2->assertRedirect(route('admin.transactions.show', $transaction));

        $this->assertDatabaseCount('transactions', 1);
        $this->assertDatabaseHas('idempotency_keys', [
            'key' => $key,
            'user_id' => $this->admin->id,
            'status' => 'completed',
        ]);
    }
}
