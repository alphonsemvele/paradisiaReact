<?php

namespace Database\Factories;

use App\Models\Page;
use App\Models\Project;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class AbonnementFactory extends Factory
{
    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'id_user' => User::factory()->create()->id_user,
            'id_project' => Project::factory()->create()->id_project,
            'id_page' => Page::factory()->create()->id_page,
            'status' => fake()->randomElement(["Success","pending","failed"]),
            'ip_address' => fake()->regexify('[A-Za-z0-9]{255}'),
        ];
    }
}
