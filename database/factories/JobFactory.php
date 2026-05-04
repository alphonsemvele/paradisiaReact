<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class JobFactory extends Factory
{
    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'queue' => fake()->regexify('[A-Za-z0-9]{500}'),
            'payload' => fake()->text(),
            'attempts' => fake()->numberBetween(-8, 8),
            'reserved_at' => fake()->numberBetween(-10000, 10000),
            'available_at' => fake()->numberBetween(-10000, 10000),
            'created_at' => fake()->numberBetween(-10000, 10000),
        ];
    }
}
