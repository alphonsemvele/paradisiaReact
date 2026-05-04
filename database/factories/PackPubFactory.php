<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class PackPubFactory extends Factory
{
    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'id_user' => fake()->numberBetween(-10000, 10000),
            'id_currency' => fake()->numberBetween(-10000, 10000),
            'payment_method' => fake()->regexify('[A-Za-z0-9]{255}'),
            'ref' => fake()->regexify('[A-Za-z0-9]{255}'),
            'title' => fake()->sentence(4),
            'description' => fake()->text(),
            'amount' => fake()->randomFloat(0, 0, 9999999999.),
            'url_payment' => fake()->regexify('[A-Za-z0-9]{255}'),
            'fees' => fake()->randomFloat(0, 0, 9999999999.),
            'video' => fake()->regexify('[A-Za-z0-9]{255}'),
            'image' => fake()->regexify('[A-Za-z0-9]{255}'),
            'forfait' => fake()->regexify('[A-Za-z0-9]{255}'),
            'website' => fake()->regexify('[A-Za-z0-9]{255}'),
            'payment_status' => fake()->numberBetween(-10000, 10000),
            'visibility_status' => fake()->numberBetween(-10000, 10000),
        ];
    }
}
