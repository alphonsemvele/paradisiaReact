<?php

namespace Database\Factories;

use App\Models\Page;
use App\Models\Project;
use App\Models\Publication;
use Illuminate\Database\Eloquent\Factories\Factory;

class LikeFactory extends Factory
{
    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'id_user' => fake()->numberBetween(-100000, 100000),
            'ip_address' => fake()->regexify('[A-Za-z0-9]{15}'),
            'id_publication' => Publication::factory()->create()->id_publication,
            'id_project' => Project::factory()->create()->id_project,
            'id_page' => Page::factory()->create()->id_page,
            'status' => fake()->randomElement(["Success","pending","failed","waiting"]),
        ];
    }
}
