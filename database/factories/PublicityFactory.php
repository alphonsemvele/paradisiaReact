<?php

namespace Database\Factories;

use App\Models\Project;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class PublicityFactory extends Factory
{
    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'id_user' => User::factory()->create()->id_user,
            'id_project' => Project::factory()->create()->id_project,
            'object' => fake()->text(),
            'text' => fake()->text(),
            'status' => fake()->randomElement(["pending","Success","failed"]),
        ];
    }
}
