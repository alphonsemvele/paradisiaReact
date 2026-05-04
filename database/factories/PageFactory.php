<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class PageFactory extends Factory
{
    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'id_user' => User::factory()->create()->id_user,
            'name' => fake()->name(),
            'description' => fake()->text(),
            'ref' => fake()->regexify('[A-Za-z0-9]{150}'),
            'website_url' => fake()->regexify('[A-Za-z0-9]{500}'),
            'status' => fake()->randomElement(["pending","Success","failed","waiting"]),
            'logo_125_125' => fake()->regexify('[A-Za-z0-9]{255}'),
            'img_banniere_1202_425' => fake()->regexify('[A-Za-z0-9]{100}'),
            'facebook' => fake()->regexify('[A-Za-z0-9]{255}'),
            'twitter' => fake()->regexify('[A-Za-z0-9]{255}'),
            'instagram' => fake()->regexify('[A-Za-z0-9]{255}'),
            'youtube' => fake()->regexify('[A-Za-z0-9]{255}'),
            'sigle' => fake()->regexify('[A-Za-z0-9]{20}'),
            'bio' => fake()->regexify('[A-Za-z0-9]{255}'),
            'video' => fake()->text(),
            'whatsapp' => fake()->text(),
            'country' => fake()->country(),
            'country_code' => fake()->numberBetween(-10000, 10000),
        ];
    }
}
