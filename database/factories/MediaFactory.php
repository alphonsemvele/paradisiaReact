<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class MediaFactory extends Factory
{
    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'type' => fake()->regexify('[A-Za-z0-9]{100}'),
            'location' => fake()->regexify('[A-Za-z0-9]{100}'),
            'name' => fake()->name(),
            'id_users_pack_pub' => fake()->numberBetween(-10000, 10000),
            'id_publication' => fake()->numberBetween(-10000, 10000),
            'id_project' => fake()->numberBetween(-10000, 10000),
            'id_fundraising' => fake()->numberBetween(-10000, 10000),
        ];
    }
}
