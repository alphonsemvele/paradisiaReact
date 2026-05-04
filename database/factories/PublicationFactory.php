<?php

namespace Database\Factories;

use App\Models\IdCountry;
use App\Models\Page;
use App\Models\Project;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class PublicationFactory extends Factory
{
    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'ref' => fake()->regexify('[A-Za-z0-9]{255}'),
            'title' => fake()->sentence(4),
            'text' => fake()->text(),
            'id_user' => User::factory()->create()->id_user,
            'id_project' => Project::factory()->create()->id_project,
            'id_page' => Page::factory()->create()->id_page,
            'status' => fake()->randomElement(["Success","pending","failed","waiting","deleted"]),
            'nbr_vews' => fake()->numberBetween(-10000, 10000),
            'image' => fake()->regexify('[A-Za-z0-9]{500}'),
            'video' => fake()->regexify('[A-Za-z0-9]{255}'),
            'audio' => fake()->regexify('[A-Za-z0-9]{500}'),
            'type' => fake()->randomElement(["publicity","publication"]),
            'typeServicePublicity' => fake()->randomElement(["sms","email"]),
            'country' => fake()->country(),
            'id_country' => IdCountry::factory(),
            'ip_address' => fake()->regexify('[A-Za-z0-9]{255}'),
            'attachment' => fake()->regexify('[A-Za-z0-9]{1000}'),
            'amount' => fake()->numberBetween(-10000, 10000),
            'user_id' => User::factory(),
            'project_id' => Project::factory(),
            'page_id' => Page::factory(),
        ];
    }
}
