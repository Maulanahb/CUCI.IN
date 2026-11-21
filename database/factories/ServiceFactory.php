<?php

namespace Database\Factories;

use App\Models\Service;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Service>
 */
class ServiceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->unique()->words(2, true),
            'price' => fake()->randomElement([5000, 7000, 10000, 15000]),
            'unit' => 'kg',
            'service_type' => 'reguler',
            'is_express' => false,
            'minimum_order' => 1.00,
            'estimated_duration' => fake()->randomElement([24, 48, 72]),
            'description' => fake()->sentence(),
            'is_active' => true,
        ];
    }

    /**
     * Indicate that the service is express / kilat.
     */
    public function express(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_express' => true,
            'service_type' => 'express',
            'estimated_duration' => 6,
        ]);
    }

    /**
     * Indicate that the service is per satuan (pcs).
     */
    public function satuan(): static
    {
        return $this->state(fn (array $attributes) => [
            'unit' => 'pcs',
            'service_type' => 'satuan',
        ]);
    }

    /**
     * Indicate that the service is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }
}
