<?php

namespace Database\Factories;

use App\Models\Customer;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Customer>
 */
class CustomerFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * Default: customer guest (tanpa akun, user_id = null).
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => null,
            'name' => fake()->name(),
            'phone' => fake()->numerify('08##########'),
            'address' => fake()->address(),
        ];
    }

    /**
     * Indicate that the customer has a registered user account.
     */
    public function withAccount(): static
    {
        return $this->state(fn (array $attributes) => [
            'user_id' => User::factory()->customer(),
        ]);
    }
}
