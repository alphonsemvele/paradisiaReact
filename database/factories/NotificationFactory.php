<?php

namespace Database\Factories;

use App\Models\Project;
use App\Models\Publication;
use Illuminate\Database\Eloquent\Factories\Factory;

class NotificationFactory extends Factory
{
    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'type' => fake()->randomElement(["project","publication","payment"]),
            'body' => fake()->text(),
            'status' => fake()->randomElement(["Success","pending","failed","waiting"]),
            'id_project' => Project::factory()->create()->id_project,
            'id_publication' => Publication::factory()->create()->id_publication,
            'id_user' => fake()->numberBetween(-100000, 100000),
        ];
    }
}
