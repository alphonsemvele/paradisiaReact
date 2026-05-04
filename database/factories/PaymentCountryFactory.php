<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class PaymentCountryFactory extends Factory
{
    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'api_end_point' => fake()->text(),
            'status' => fake()->randomElement(["pending","Success","failes"]),
        ];
    }
}
