<?php

namespace Database\Factories\Model\Payment;

use App\Model\Payment\Plan;
use App\Model\Product\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

class PlanFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    protected $model = Plan::class;

    public function definition()
    {
        $durations = [14, 30, 90, 180, 365, 730];

        return [
            'name' => $this->faker->sentence(3),
            'product' => Product::factory(),
            'days' => $this->faker->randomElement($durations),
        ];
    }
}
