<?php

namespace Database\Factories;

use App\Models\Page;
use App\Models\Project;
use App\Models\Publication;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ShareFactory extends Factory
{
    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'id_project' => Project::factory()->create()->id_project,
            'id_publication' => Publication::factory()->create()->id_publication,
            'id_user' => User::factory()->create()->id_user,
            'id_page' => Page::factory()->create()->id_page,
            'ip_address' => fake()->regexify('[A-Za-z0-9]{255}'),
            'status' => fake()->randomElement(["Success","pending","failed"]),
        ];
    }
}
