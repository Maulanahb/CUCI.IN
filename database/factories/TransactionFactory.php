<?php

namespace Database\Factories;

use App\Enums\TransactionStatus;
use App\Models\Transaction;
use App\Services\TransactionCodeGenerator;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Transaction>
 */
class TransactionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'customer_id' => 1,
            'created_by' => 1,
            'transaction_code' => TransactionCodeGenerator::generate(),
            'total_amount' => fake()->randomFloat(2, 10000, 200000),
            'status' => TransactionStatus::Diterima,
            'estimated_completed_at' => now()->addDays(2),
        ];
    }
}
