<?php

namespace Database\Factories;

use App\Models\Project;
use Illuminate\Database\Eloquent\Factories\Factory;

class RoundFactory extends Factory
{
    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'begin' => fake()->dateTime(),
            'end' => fake()->dateTime(),
            'id_project' => Project::factory()->create()->id_project,
            'amount' => fake()->numberBetween(-10000, 10000),
            'status' => fake()->randomElement(["pending","Success","failed","waiting"]),
        ];
    }
}
