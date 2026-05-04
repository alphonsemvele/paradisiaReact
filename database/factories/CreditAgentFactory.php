<?php

namespace Database\Factories;

use App\Models\Admin;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class CreditAgentFactory extends Factory
{
    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'ref' => fake()->regexify('[A-Za-z0-9]{255}'),
            'id_user' => User::factory()->create()->id_user,
            'id_admin' => Admin::factory()->create()->id_admin,
            'status' => fake()->randomElement(["pending","Success","failed"]),
            'amount' => fake()->numberBetween(-10000, 10000),
            'currency' => fake()->regexify('[A-Za-z0-9]{255}'),
            'commentaire' => fake()->text(),
        ];
    }
}
