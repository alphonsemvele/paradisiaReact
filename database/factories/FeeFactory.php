<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class FeeFactory extends Factory
{
    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'type' => fake()->randomElement(["invest","don","studyFees","pub"]),
            'value' => fake()->numberBetween(-10000, 10000),
            'status' => fake()->randomElement(["pending","Success","failed","waiting"]),
        ];
    }
}
