<?php

namespace Database\Factories;

use App\Models\Fee;
use App\Models\Project;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class PaymentDonationFactory extends Factory
{
    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'ref' => fake()->regexify('[A-Za-z0-9]{255}'),
            'customer_name' => fake()->regexify('[A-Za-z0-9]{255}'),
            'customer_email' => fake()->regexify('[A-Za-z0-9]{255}'),
            'id_user' => User::factory()->create()->id_user,
            'amount' => fake()->numberBetween(-10000, 10000),
            'country' => fake()->country(),
            'payment_country' => fake()->regexify('[A-Za-z0-9]{255}'),
            'payment_country_code' => fake()->regexify('[A-Za-z0-9]{255}'),
            'currency' => fake()->regexify('[A-Za-z0-9]{255}'),
            'id_project' => Project::factory()->create()->id_project,
            'status' => fake()->randomElement(["pending","Success","failed","waiting"]),
            'type_paiement' => fake()->randomElement(["Contact","Mobile","Bank"]),
            'payment_number' => fake()->regexify('[A-Za-z0-9]{255}'),
            'id_fees' => Fee::factory()->create()->id_fees,
            'fees' => fake()->numberBetween(-10000, 10000),
            'total_amount' => fake()->numberBetween(-10000, 10000),
            'customer_number' => fake()->regexify('[A-Za-z0-9]{255}'),
            'description' => fake()->text(),
        ];
    }
}
