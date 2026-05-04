<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class UserFactory extends Factory
{
    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'ref' => fake()->regexify('[A-Za-z0-9]{255}'),
            'id_father' => User::factory()->create()->id_father,
            'name' => fake()->name(),
            'last_name' => fake()->lastName(),
            'email' => fake()->safeEmail(),
            'password' => fake()->password(),
            'remember_token' => fake()->uuid(),
            'role' => fake()->randomElement(["user","agent","admin"]),
            'valid' => fake()->numberBetween(-8, 8),
            'confirmed' => fake()->numberBetween(-8, 8),
            'confirmation_code' => fake()->regexify('[A-Za-z0-9]{191}'),
            'default_currency' => fake()->numberBetween(-10000, 10000),
            'referral_code' => fake()->regexify('[A-Za-z0-9]{255}'),
            'status' => fake()->randomElement(["pending","Success","failed","waiting"]),
            'country' => fake()->country(),
            'phone' => fake()->phoneNumber(),
            'ville' => fake()->regexify('[A-Za-z0-9]{100}'),
            'last_connect' => fake()->dateTime(),
            'cover_img' => fake()->regexify('[A-Za-z0-9]{200}'),
            'last_active' => fake()->dateTime(),
            'photo' => fake()->text(),
            'description' => fake()->text(),
            'birth' => fake()->date(),
            'country_code' => fake()->numberBetween(-10000, 10000),
            'recoveryPass_code' => fake()->regexify('[A-Za-z0-9]{255}'),
            'sexe' => fake()->randomElement(["H","F"]),
            'id_country' => fake()->numberBetween(-100000, 100000),
        ];
    }
}
