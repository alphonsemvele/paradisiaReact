<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class CountryFactory extends Factory
{
    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'sortname' => fake()->regexify('[A-Za-z0-9]{255}'),
            'phoneCode' => fake()->numberBetween(-10000, 10000),
        ];
    }
}
