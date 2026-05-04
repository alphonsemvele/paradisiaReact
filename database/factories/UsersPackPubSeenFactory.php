<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class UsersPackPubSeenFactory extends Factory
{
    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'id_user' => fake()->numberBetween(-10000, 10000),
            'id_pack_pub' => fake()->numberBetween(-10000, 10000),
        ];
    }
}
