<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class AskQuestionFactory extends Factory
{
    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'id_user' => User::factory()->create()->id_user,
            'response' => fake()->randomElement(["facebook","instagram","twitter","whatsapp","friend","other"]),
        ];
    }
}
