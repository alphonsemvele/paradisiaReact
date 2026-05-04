<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class CurrencyFactory extends Factory
{
    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'name_abr' => fake()->regexify('[A-Za-z0-9]{10}'),
            'name' => fake()->name(),
        ];
    }
}
