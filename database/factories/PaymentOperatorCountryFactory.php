<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class PaymentOperatorCountryFactory extends Factory
{
    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'ref' => fake()->regexify('[A-Za-z0-9]{255}'),
            'name' => fake()->name(),
            'api_end_point' => fake()->text(),
            'currency' => fake()->regexify('[A-Za-z0-9]{255}'),
            'fees' => fake()->randomFloat(2, 0, 999.99),
            'status' => fake()->randomElement(["pending","Success","failed"]),
            'country_code' => fake()->regexify('[A-Za-z0-9]{11}'),
            'country_cdial' => fake()->numberBetween(-10000, 10000),
            'message' => fake()->text(),
            'otpOption' => fake()->numberBetween(-8, 8),
        ];
    }
}
