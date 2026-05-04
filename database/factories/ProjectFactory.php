<?php

namespace Database\Factories;

use App\Models\CategoryProject;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProjectFactory extends Factory
{
    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'id_user' => User::factory()->create()->id_user,
            'category_id' => CategoryProject::factory(),
            'type' => fake()->randomElement(["invest","don"]),
            'name' => fake()->name(),
            'description' => fake()->text(),
            'public_key' => fake()->regexify('[A-Za-z0-9]{150}'),
            'secret_key' => fake()->regexify('[A-Za-z0-9]{150}'),
            'website_url' => fake()->regexify('[A-Za-z0-9]{500}'),
            'status' => fake()->randomElement(["pending","Success","failed","waiting"]),
            'status_invest' => fake()->randomElement(["pending","invest","vote","trade","deleted","end"]),
            'objective' => fake()->randomFloat(0, 0, 9999999999.),
            'currency' => fake()->regexify('[A-Za-z0-9]{5}'),
            'project_book' => fake()->regexify('[A-Za-z0-9]{255}'),
            'private_policy' => fake()->regexify('[A-Za-z0-9]{255}'),
            'business_plan' => fake()->regexify('[A-Za-z0-9]{255}'),
            'logo_125_125' => fake()->regexify('[A-Za-z0-9]{255}'),
            'img_banniere_1202_425' => fake()->regexify('[A-Za-z0-9]{100}'),
            'organizer_name' => fake()->regexify('[A-Za-z0-9]{255}'),
            'organizer_address' => fake()->text(),
            'organizer_city' => fake()->regexify('[A-Za-z0-9]{255}'),
            'organizer_email' => fake()->regexify('[A-Za-z0-9]{255}'),
            'organizer_cdial' => fake()->regexify('[A-Za-z0-9]{10}'),
            'organizer_phone' => fake()->regexify('[A-Za-z0-9]{255}'),
            'organizer_cname' => fake()->regexify('[A-Za-z0-9]{255}'),
            'organizer_country_code' => fake()->regexify('[A-Za-z0-9]{10}'),
            'organizer_website_url' => fake()->regexify('[A-Za-z0-9]{300}'),
            'organizer_country' => fake()->regexify('[A-Za-z0-9]{255}'),
            'cachet' => fake()->regexify('[A-Za-z0-9]{255}'),
            'registre_comm' => fake()->regexify('[A-Za-z0-9]{255}'),
            'numero_cont' => fake()->regexify('[A-Za-z0-9]{255}'),
            'video' => fake()->regexify('[A-Za-z0-9]{255}'),
            'pack_vue' => fake()->numberBetween(-10000, 10000),
            'duration' => fake()->regexify('[A-Za-z0-9]{3}'),
            'facebook' => fake()->regexify('[A-Za-z0-9]{255}'),
            'twitter' => fake()->regexify('[A-Za-z0-9]{255}'),
            'instagram' => fake()->regexify('[A-Za-z0-9]{255}'),
            'youtube' => fake()->regexify('[A-Za-z0-9]{255}'),
            'cni' => fake()->regexify('[A-Za-z0-9]{255}'),
            'logo_105_200' => fake()->regexify('[A-Za-z0-9]{255}'),
            'img_banniere_263_240' => fake()->regexify('[A-Za-z0-9]{255}'),
            'sigle' => fake()->regexify('[A-Za-z0-9]{20}'),
            'contract_color' => fake()->regexify('[A-Za-z0-9]{255}'),
            'feesStudy' => fake()->randomElement(["0","1"]),
            'feesStudyValue' => fake()->numberBetween(-10000, 10000),
            'category_project_id' => CategoryProject::factory(),
            'user_id' => User::factory(),
        ];
    }
}
