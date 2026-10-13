<?php

namespace Database\Factories;

use App\Models\Complaint;
use App\Models\Transaction;
use App\Services\ComplaintCodeGenerator;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Complaint>
 */
class ComplaintFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'complaint_code' => ComplaintCodeGenerator::generate(),
            'transaction_id' => Transaction::factory(),
            'subject' => fake()->sentence(4),
            'description' => fake()->paragraph(2),
            'status' => 'Menunggu',
            'response' => null,
            'deadline_at' => now()->addDays(2),
            'handled_by' => null,
            'handled_at' => null,
        ];
    }
}
