<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class AdminFactory extends Factory
{
    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'email' => fake()->safeEmail(),
            'status' => fake()->randomElement(["Success","pending","failed"]),
        ];
    }
}
