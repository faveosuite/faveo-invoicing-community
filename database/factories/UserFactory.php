<?php

namespace Database\Factories;

use App\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class UserFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    protected $model = User::class;

    public function definition()
    {
        return [
            'user_name' => $this->faker->userName(),
            'first_name' => $this->faker->firstName(),
            'last_name' => $this->faker->lastName(),
            'email' => $this->faker->unique()->safeEmail(),
            'company' => $this->faker->company(),
            'bussiness' => 'abcd',
            'company_type' => 'public_company',
            'company_size' => '2-50',
            'country' => 'IN',
            'mobile' => $this->faker->e164PhoneNumber(),
            'currency' => 'INR',
            'address' => $this->faker->address(),
            'town' => $this->faker->city(),
            'state' => 'IN-TN',
            'zip' => $this->faker->postcode(),
            'password' => 'Rivara@12',
            'timezone_id' => 79,
            'remember_token' => str_random(10),
            'mobile_verified' => 1,
            'active' => 1,
            'role' => 'user',
        ];
    }
}
