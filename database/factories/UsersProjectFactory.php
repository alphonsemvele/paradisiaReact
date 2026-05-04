<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class UsersProjectFactory extends Factory
{
    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'id_user' => fake()->numberBetween(-10000, 10000),
            'id_project' => fake()->numberBetween(-10000, 10000),
            'role' => fake()->regexify('[A-Za-z0-9]{100}'),
        ];
    }
}
