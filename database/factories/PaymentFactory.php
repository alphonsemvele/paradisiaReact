<?php

namespace Database\Factories;

use App\Models\Fee;
use App\Models\PaymentOperatorCountry;
use App\Models\Project;
use App\Models\Publication;
use App\Models\Round;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class PaymentFactory extends Factory
{
    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'ref' => fake()->regexify('[A-Za-z0-9]{255}'),
            'id_round' => Round::factory()->create()->id_round,
            'id_project' => Project::factory()->create()->id_project,
            'id_publication' => Publication::factory()->create()->id_publication,
            'id_user' => User::factory()->create()->id_user,
            'id_agent' => User::factory()->create()->id_agent,
            'amount' => fake()->randomFloat(0, 0, 9999999999.),
            'total_amount' => fake()->randomFloat(0, 0, 9999999999.),
            'fees' => fake()->randomFloat(0, 0, 9999999999.),
            'id_fees' => Fee::factory()->create()->id_fees,
            'currency' => fake()->regexify('[A-Za-z0-9]{10}'),
            'services' => fake()->randomElement(["invest","don","studyFees","pub"]),
            'share' => fake()->randomFloat(0, 0, 9999999999.),
            'status' => fake()->randomElement(["pending","Failed","Success","Refunded","Cancelled"]),
            'type_paiement' => fake()->randomElement(["Contact","Mobile","Bank","Bonus"]),
            'error_code' => fake()->regexify('[A-Za-z0-9]{255}'),
            'customer_number' => fake()->regexify('[A-Za-z0-9]{255}'),
            'payment_number' => fake()->regexify('[A-Za-z0-9]{255}'),
            'customer_email' => fake()->regexify('[A-Za-z0-9]{255}'),
            'customer_name' => fake()->regexify('[A-Za-z0-9]{255}'),
            'customer_postal_code' => fake()->regexify('[A-Za-z0-9]{255}'),
            'description_payment' => fake()->text(),
            'http_request' => fake()->regexify('[A-Za-z0-9]{500}'),
            'ip_adress' => fake()->regexify('[A-Za-z0-9]{100}'),
            'network_adress' => fake()->regexify('[A-Za-z0-9]{255}'),
            'payment_country' => fake()->regexify('[A-Za-z0-9]{255}'),
            'url_payment' => fake()->regexify('[A-Za-z0-9]{255}'),
            'payment_country_code' => fake()->numberBetween(-10000, 10000),
            'fees_operator' => fake()->randomFloat(2, 0, 999.99),
            'operator_after_notify_payment' => fake()->regexify('[A-Za-z0-9]{255}'),
            'ref_paiement_api' => fake()->text(),
            'sign_operator' => fake()->text(),
            'id_operator' => PaymentOperatorCountry::factory()->create()->id_operator,
            'code_agent_temporaire' => fake()->text(),
        ];
    }
}
