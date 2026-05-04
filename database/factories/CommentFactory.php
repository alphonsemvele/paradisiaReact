<?php

namespace Database\Factories;

use App\Models\Page;
use App\Models\Project;
use App\Models\Publication;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class CommentFactory extends Factory
{
    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'id_user' => User::factory()->create()->id_user,
            'id_publication' => Publication::factory()->create()->id_publication,
            'id_project' => Project::factory()->create()->id_project,
            'id_page' => Page::factory()->create()->id_page,
            'body' => fake()->text(),
            'status' => fake()->randomElement(["Success","pending","failed","waiting"]),
        ];
    }
}
