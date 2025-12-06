<?php

namespace Database\Factories\Model\Payment;

use App\Model\Payment\Plan;
use App\Model\Payment\PlanPrice;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\PlanPrice>
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
            'plan_id'           => Plan::factory(),
            'currency'          => $this->faker->randomElement(['INR', 'USD', 'EUR']),
            'add_price'         => $this->faker->randomFloat(2, 10, 5000),
            'renew_price'       => $this->faker->randomFloat(2, 10, 5000),
            'price_description' => $this->faker->sentence(6),
            'product_quantity'  => $this->faker->numberBetween(1, 10),
            'no_of_agents'      => $this->faker->numberBetween(1, 10),
        ];
    }
}
