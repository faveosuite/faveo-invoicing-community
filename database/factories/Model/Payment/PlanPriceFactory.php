<?php

declare(strict_types=1);

namespace Database\Factories\Model\Payment;

use App\Model\Payment\Plan;
use App\Model\Payment\PlanPrice;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\PlanPrice>
 */
class PlanPriceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    protected $model = PlanPrice::class;

    public function definition()
    {
        return [
            'plan_id' => Plan::factory(),
            'currency' => fake()->randomElement(['INR', 'USD', 'EUR']),
            'add_price' => fake()->randomFloat(2, 10, 5000),
            'renew_price' => fake()->randomFloat(2, 10, 5000),
            'price_description' => fake()->sentence(6),
            'product_quantity' => fake()->numberBetween(1, 10),
            'no_of_agents' => fake()->numberBetween(1, 10),
        ];
    }
}
