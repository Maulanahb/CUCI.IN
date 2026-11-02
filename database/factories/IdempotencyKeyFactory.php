<?php

namespace Database\Factories;

use App\Models\IdempotencyKey;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<IdempotencyKey>
 */
class IdempotencyKeyFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<IdempotencyKey>
     */
    protected $model = IdempotencyKey::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'key' => (string) Str::uuid(),
            'user_id' => User::factory(),
            'route' => 'staff.transactions.store',
            'request_hash' => hash('sha256', fake()->sentence()),
            'status' => IdempotencyKey::STATUS_PROCESSING,
            'response_code' => null,
            'response_body' => null,
            'response_headers' => null,
            'expires_at' => now()->addHours(24),
        ];
    }

    /**
     * State untuk request yang telah selesai (completed) dengan response tersimpan.
     */
    public function completed(int $code = 201, string|array|null $body = null, ?array $headers = null): static
    {
        return $this->state(fn (): array => [
            'status' => IdempotencyKey::STATUS_COMPLETED,
            'response_code' => $code,
            'response_body' => is_array($body) ? json_encode($body) : ($body ?? json_encode(['id' => 1, 'transaction_code' => 'TRX-TEST-001'])),
            'response_headers' => $headers ?? ['content-type' => ['application/json']],
        ]);
    }

    /**
     * State untuk request yang sedang berjalan (processing).
     */
    public function processing(): static
    {
        return $this->state(fn (): array => [
            'status' => IdempotencyKey::STATUS_PROCESSING,
            'response_code' => null,
            'response_body' => null,
            'response_headers' => null,
        ]);
    }

    /**
     * State untuk key yang sudah kedaluwarsa (expired).
     */
    public function expired(): static
    {
        return $this->state(fn (): array => [
            'expires_at' => now()->subHour(),
        ]);
    }
}
