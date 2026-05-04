<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class CategoryProjectFactory extends Factory
{
    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'icone' => fake()->regexify('[A-Za-z0-9]{255}'),
            'status' => fake()->randomElement(["pending","Success","failed","waiting"]),
            'color' => fake()->regexify('[A-Za-z0-9]{255}'),
        ];
    }
}
