<?php

declare(strict_types=1);

namespace Database\Factories;

use App\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\User>
 */
class UserFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<mixed>     */
    protected $model = User::class;

    public function definition()
    {
        return [
            'user_name' => fake()->userName(),
            'first_name' => fake()->firstName(),
            'last_name' => fake()->lastName(),
            'email' => fake()->unique()->safeEmail(),
            'company' => fake()->company(),
            'bussiness' => 'abcd',
            'company_type' => 'public_company',
            'company_size' => '2-50',
            'country' => 'IN',
            'mobile' => fake()->e164PhoneNumber(),
            'currency' => 'INR',
            'address' => fake()->address(),
            'town' => fake()->city(),
            'state' => 'TN',
            'zip' => fake()->postcode(),
            'password' => 'Rivara@12',
            'timezone_id' => 79,
            'remember_token' => Str::random(10),
            'mobile_verified' => 1,
            'role' => 'user',
            'active' => 1,
        ];
    }
}
