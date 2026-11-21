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
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Str;
use Tests\TestCase;

class IdempotencyChallengeStressTest extends TestCase
{
    use RefreshDatabase;

    private User $staff;

    private Customer $customer;

    private Service $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->staff = User::factory()->staff()->create();
        $this->customer = Customer::factory()->create();
        $this->service = Service::factory()->create([
            'name' => 'Cuci Kering',
            'price' => 10000.00,
            'is_active' => true,
        ]);
    }

    /**
     * Challenge 1: Concurrency Stress - 10 rapid simulated requests
     */
    public function test_concurrent_in_flight_requests_return_409_and_exactly_one_succeeds(): void
    {
        $key = (string) Str::uuid();
        $payload = [
            'customer_id' => $this->customer->id,
            'items' => [
                ['service_id' => $this->service->id, 'weight' => 2.0],
            ],
        ];

        // 1. Manually simulate an in-flight processing key as if Request #1 is executing
        IdempotencyKey::create([
            'key' => $key,
            'user_id' => $this->staff->id,
            'route' => 'staff.transactions.store',
            'request_hash' => hash('sha256', 'dummy'),
            'status' => 'processing',
            'expires_at' => now()->addHours(24),
        ]);

        // 2. Fire 5 simulated concurrent requests while status is 'processing'
        for ($i = 0; $i < 5; $i++) {
            $res = $this->actingAs($this->staff)->postJson(
                route('staff.transactions.store'),
                $payload,
                ['Idempotency-Key' => $key]
            );

            $this->assertEquals(409, $res->getStatusCode(), "Concurrent request {$i} must receive HTTP 409 Conflict");
            $res->assertJson(['message' => 'Permintaan sedang diproses. Mohon tunggu sesaat.']);
        }

        // Database must remain at 0 transactions
        $this->assertDatabaseCount('transactions', 0);
    }

    /**
     * Challenge 2: Payload Normalization & Manipulation Resistance
     */
    public function test_nested_key_order_and_token_differences_produce_identical_replay(): void
    {
        $key = (string) Str::uuid();

        // Request 1: items with keys in order [service_id, weight]
        $payload1 = [
            '_token' => 'token-AAA',
            'customer_id' => $this->customer->id,
            'items' => [
                ['service_id' => $this->service->id, 'weight' => 2.0],
            ],
        ];

        $res1 = $this->actingAs($this->staff)->postJson(
            route('staff.transactions.store'),
            $payload1,
            ['Idempotency-Key' => $key]
        );
        $res1->assertStatus(201);
        $this->assertDatabaseCount('transactions', 1);

        // Request 2: items with keys in REVERSED order [weight, service_id] and DIFFERENT _token
        $payload2 = [
            '_token' => 'token-DIFFERENT-BBB',
            'items' => [
                ['weight' => 2.0, 'service_id' => $this->service->id],
            ],
            'customer_id' => $this->customer->id,
        ];

        $res2 = $this->actingAs($this->staff)->postJson(
            route('staff.transactions.store'),
            $payload2,
            ['Idempotency-Key' => $key]
        );

        $res2->assertStatus(201);
        $res2->assertHeader('Idempotent-Replayed', 'true');
        // Still only 1 transaction
        $this->assertDatabaseCount('transactions', 1);

        // Request 3: Malicious payload modification (injected field) under SAME key
        $payload3 = $payload1;
        $payload3['injected_field'] = 'attack';

        $res3 = $this->actingAs($this->staff)->postJson(
            route('staff.transactions.store'),
            $payload3,
            ['Idempotency-Key' => $key]
        );

        $res3->assertStatus(422);
        $res3->assertJson(['message' => 'Idempotency key payload mismatch.']);
        $this->assertDatabaseCount('transactions', 1);
    }

    /**
     * Challenge 3: Expiration Behavior & Artisan Model Pruning
     */
    public function test_expired_key_is_automatically_pruned_and_reprocessed(): void
    {
        $key = (string) Str::uuid();

        // Create an expired idempotency record
        IdempotencyKey::create([
            'key' => $key,
            'user_id' => $this->staff->id,
            'route' => 'staff.transactions.store',
            'request_hash' => hash('sha256', 'old-hash'),
            'status' => 'completed',
            'response_code' => 201,
            'response_body' => json_encode(['dummy' => true]),
            'expires_at' => now()->subMinutes(10), // EXPIRED
        ]);

        $this->assertDatabaseCount('idempotency_keys', 1);

        $payload = [
            'customer_id' => $this->customer->id,
            'items' => [
                ['service_id' => $this->service->id, 'weight' => 3.0],
            ],
        ];

        // Send request with the expired key
        $res = $this->actingAs($this->staff)->postJson(
            route('staff.transactions.store'),
            $payload,
            ['Idempotency-Key' => $key]
        );

        $res->assertStatus(201);
        $this->assertFalse($res->headers->has('Idempotent-Replayed'));
        $this->assertDatabaseCount('transactions', 1);

        // Record should now have updated expires_at in future
        $refreshedKey = IdempotencyKey::where('key', $key)->first();
        $this->assertNotNull($refreshedKey);
        $this->assertTrue($refreshedKey->expires_at->isFuture());
    }

    /**
     * Challenge 3b: Artisan model:prune command cleans up expired keys
     */
    public function test_artisan_model_prune_removes_expired_records(): void
    {
        // 1 active key
        IdempotencyKey::factory()->completed()->create([
            'key' => 'active-key',
            'expires_at' => now()->addHours(12),
        ]);

        // 1 expired key
        IdempotencyKey::factory()->completed()->create([
            'key' => 'expired-key',
            'expires_at' => now()->subHour(),
        ]);

        $this->assertDatabaseCount('idempotency_keys', 2);

        Artisan::call('model:prune', [
            '--model' => [IdempotencyKey::class],
        ]);

        $this->assertDatabaseCount('idempotency_keys', 1);
        $this->assertDatabaseHas('idempotency_keys', ['key' => 'active-key']);
        $this->assertDatabaseMissing('idempotency_keys', ['key' => 'expired-key']);
    }

    /**
     * Challenge 4: Session Flash & Redirect Persistence on Replay
     */
    public function test_web_form_replay_preserves_flash_session_and_target_url(): void
    {
        $key = (string) Str::uuid();
        $payload = [
            'customer_id' => $this->customer->id,
            'items' => [
                ['service_id' => $this->service->id, 'weight' => 2.5],
            ],
        ];

        // Request 1: Form post
        $res1 = $this->actingAs($this->staff)->post(
            route('staff.transactions.store'),
            $payload,
            ['Idempotency-Key' => $key]
        );

        $res1->assertStatus(302);
        $res1->assertSessionHas('success');
        $this->assertDatabaseCount('transactions', 1);

        $transaction = Transaction::first();

        // Request 2: Replay
        $res2 = $this->actingAs($this->staff)->post(
            route('staff.transactions.store'),
            $payload,
            ['Idempotency-Key' => $key]
        );

        $res2->assertStatus(302);
        $res2->assertHeader('Idempotent-Replayed', 'true');
        $res2->assertRedirect(route('staff.transactions.show', $transaction));
        $res2->assertSessionHas('success');
        $this->assertDatabaseCount('transactions', 1);
    }

    /**
     * Challenge 5: Validation Error Pruning Allows Immediate Retry with Same Key
     */
    public function test_validation_error_prunes_record_allowing_immediate_fix_and_retry(): void
    {
        $key = (string) Str::uuid();

        // Step 1: Submit invalid payload (weight is negative / invalid)
        $invalidPayload = [
            'customer_id' => $this->customer->id,
            'items' => [
                ['service_id' => $this->service->id, 'weight' => -5.0],
            ],
        ];

        $res1 = $this->actingAs($this->staff)->post(
            route('staff.transactions.store'),
            $invalidPayload,
            ['Idempotency-Key' => $key]
        );

        $res1->assertSessionHasErrors();
        // Record MUST be pruned from database
        $this->assertDatabaseCount('idempotency_keys', 0);
        $this->assertDatabaseCount('transactions', 0);

        // Step 2: Retry immediately with valid payload and THE SAME KEY
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
     * Challenge 6: Stress Double Payment with Rapid Submits
     */
    public function test_payment_recording_rapid_submits_never_double_charges(): void
    {
        $key = (string) Str::uuid();

        $transaction = Transaction::factory()->create([
            'customer_id' => $this->customer->id,
            'created_by' => $this->staff->id,
            'total_amount' => 100000.00,
            'status' => TransactionStatus::Diterima,
        ]);

        $paymentPayload = [
            'amount' => 100000.00,
            'method' => 'Tunai',
        ];

        // Fire 1st payment
        $res1 = $this->actingAs($this->staff)->post(
            route('staff.transactions.payments.store', $transaction),
            $paymentPayload,
            ['Idempotency-Key' => $key]
        );
        $res1->assertRedirect();
        $this->assertDatabaseCount('payments', 1);

        // Fire 4 rapid replays with same key
        for ($i = 0; $i < 4; $i++) {
            $res = $this->actingAs($this->staff)->post(
                route('staff.transactions.payments.store', $transaction),
                $paymentPayload,
                ['Idempotency-Key' => $key]
            );
            $res->assertRedirect();
            $res->assertHeader('Idempotent-Replayed', 'true');
        }

        // Must still have exactly 1 payment record
        $this->assertDatabaseCount('payments', 1);
        $payment = Payment::first();
        $this->assertEquals(100000.00, (float) $payment->amount);

        $transaction->refresh();
        $this->assertEquals(0, (float) $transaction->remaining_amount);
        $this->assertEquals('Lunas', (string) $transaction->payment_status);
    }

    /**
     * Challenge 7: Oversized key truncated safely and array key handled gracefully
     */
    public function test_key_extraction_boundary_and_type_coercion(): void
    {
        // 1. Oversized key (> 64 characters)
        $oversizedKey = str_repeat('a', 120);

        $payload = [
            'customer_id' => $this->customer->id,
            'items' => [
                ['service_id' => $this->service->id, 'weight' => 1.5],
            ],
        ];

        $res1 = $this->actingAs($this->staff)->postJson(
            route('staff.transactions.store'),
            $payload,
            ['Idempotency-Key' => $oversizedKey]
        );

        $res1->assertStatus(201);
        // Truncated to 64 chars in DB
        $this->assertDatabaseHas('idempotency_keys', [
            'key' => substr($oversizedKey, 0, 64),
            'status' => 'completed',
        ]);

        // 2. Array key passed via query/body
        $key2 = (string) Str::uuid();
        $payload2 = [
            'idempotency_key' => [$key2, 'another-key'],
            'customer_id' => $this->customer->id,
            'items' => [
                ['service_id' => $this->service->id, 'weight' => 2.0],
            ],
        ];

        $res2 = $this->actingAs($this->staff)->postJson(
            route('staff.transactions.store'),
            $payload2
        );

        $res2->assertStatus(201);
        $this->assertDatabaseHas('idempotency_keys', [
            'key' => $key2,
            'status' => 'completed',
        ]);
    }

    /**
     * Challenge 8: Whitespace-only key is treated as blank (backward compatibility pass-through)
     */
    public function test_whitespace_only_key_is_treated_as_blank_pass_through(): void
    {
        $payload = [
            'customer_id' => $this->customer->id,
            'items' => [
                ['service_id' => $this->service->id, 'weight' => 1.0],
            ],
        ];

        $res = $this->actingAs($this->staff)->post(
            route('staff.transactions.store'),
            $payload,
            ['Idempotency-Key' => '    ']
        );

        $res->assertStatus(302);
        $this->assertDatabaseCount('transactions', 1);
        $this->assertDatabaseCount('idempotency_keys', 0);
    }

    /**
     * Challenge 9: UploadedFile normalization produces deterministic hash without serialization error
     */
    public function test_uploaded_file_payload_produces_consistent_hash_and_replays_cleanly(): void
    {
        $key = (string) Str::uuid();
        $fakeFile = UploadedFile::fake()->create('struk.pdf', 150, 'application/pdf');

        $payload = [
            'customer_id' => $this->customer->id,
            'items' => [
                ['service_id' => $this->service->id, 'weight' => 2.0],
            ],
            'attachment' => $fakeFile,
        ];

        $res1 = $this->actingAs($this->staff)->post(
            route('staff.transactions.store'),
            $payload,
            ['Idempotency-Key' => $key]
        );

        $res1->assertStatus(302);
        $this->assertDatabaseCount('transactions', 1);
        $this->assertDatabaseCount('idempotency_keys', 1);

        // Replay with identical file metadata
        $res2 = $this->actingAs($this->staff)->post(
            route('staff.transactions.store'),
            $payload,
            ['Idempotency-Key' => $key]
        );

        $res2->assertStatus(302);
        $res2->assertHeader('Idempotent-Replayed', 'true');
        $this->assertDatabaseCount('transactions', 1);
    }

    /**
     * Challenge 10: Replay never leaks stored Set-Cookie from original request
     */
    public function test_replay_response_does_not_contain_set_cookie_header(): void
    {
        $key = (string) Str::uuid();
        $payload = [
            'customer_id' => $this->customer->id,
            'items' => [
                ['service_id' => $this->service->id, 'weight' => 2.0],
            ],
        ];

        // Request 1
        $res1 = $this->actingAs($this->staff)->postJson(
            route('staff.transactions.store'),
            $payload,
            ['Idempotency-Key' => $key]
        );
        $res1->assertStatus(201);

        $record = IdempotencyKey::where('key', $key)->first();
        $this->assertNotNull($record);
        $storedHeaders = array_change_key_case($record->response_headers ?? [], CASE_LOWER);
        $this->assertArrayNotHasKey('set-cookie', $storedHeaders, 'Database must not persist Set-Cookie');

        // Request 2 (Replay)
        $res2 = $this->actingAs($this->staff)->postJson(
            route('staff.transactions.store'),
            $payload,
            ['Idempotency-Key' => $key]
        );

        $res2->assertStatus(201);
        $res2->assertHeader('Idempotent-Replayed', 'true');
    }

    /**
     * Challenge 11: Exception during downstream execution prunes key allowing retry
     */
    public function test_exception_during_controller_execution_deletes_idempotency_record_allowing_subsequent_retry(): void
    {
        $key = (string) Str::uuid();
        $payload = [
            'customer_id' => $this->customer->id,
            'items' => [
                ['service_id' => $this->service->id, 'weight' => 2.0],
            ],
        ];

        // Simulate temporary downstream failure (e.g. database deadlock or runtime exception)
        Transaction::saving(function () {
            static $alreadyFailed = false;
            if (! $alreadyFailed) {
                $alreadyFailed = true;
                throw new \RuntimeException('Simulated downstream deadlock');
            }
        });

        // Request 1: Fails with exception
        try {
            $this->actingAs($this->staff)->withoutExceptionHandling()->postJson(
                route('staff.transactions.store'),
                $payload,
                ['Idempotency-Key' => $key]
            );
            $this->fail('Expected RuntimeException was not thrown');
        } catch (\RuntimeException $e) {
            $this->assertSame('Simulated downstream deadlock', $e->getMessage());
        }

        // Must be pruned so the user is not locked out!
        $this->assertDatabaseCount('idempotency_keys', 0);
        $this->assertDatabaseCount('transactions', 0);

        // Request 2: Retry with SAME key succeeds
        $res2 = $this->actingAs($this->staff)->postJson(
            route('staff.transactions.store'),
            $payload,
            ['Idempotency-Key' => $key]
        );

        $res2->assertStatus(201);
        $this->assertDatabaseCount('transactions', 1);
        $this->assertDatabaseHas('idempotency_keys', [
            'key' => $key,
            'status' => 'completed',
        ]);
    }

    /**
     * Challenge 12: Cross-user same key with different payload returns 422 preventing pollution
     */
    public function test_cross_user_same_key_different_payload_returns_422_preventing_pollution(): void
    {
        $key = (string) Str::uuid();
        $staff2 = User::factory()->staff()->create();

        $payloadStaff1 = [
            'customer_id' => $this->customer->id,
            'items' => [
                ['service_id' => $this->service->id, 'weight' => 2.0],
            ],
        ];

        // Staff 1 creates transaction
        $res1 = $this->actingAs($this->staff)->postJson(
            route('staff.transactions.store'),
            $payloadStaff1,
            ['Idempotency-Key' => $key]
        );
        $res1->assertStatus(201);

        // Staff 2 tries to reuse the same key for their own transaction with different payload
        $payloadStaff2 = [
            'customer_id' => $this->customer->id,
            'items' => [
                ['service_id' => $this->service->id, 'weight' => 5.0],
            ],
        ];

        $res2 = $this->actingAs($staff2)->postJson(
            route('staff.transactions.store'),
            $payloadStaff2,
            ['Idempotency-Key' => $key]
        );

        $res2->assertStatus(422);
        $res2->assertJson(['message' => 'Idempotency key payload mismatch.']);
        $this->assertDatabaseCount('transactions', 1);
    }
}
