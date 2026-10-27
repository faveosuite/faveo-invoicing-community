<?php

declare(strict_types=1);

namespace Database\Factories\Model\Payment;

use App\Model\Payment\Plan;
use App\Model\Product\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Plan>
 */
class PlanFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<mixed>     */
    protected $model = Plan::class;

    public function definition()
    {
        $durations = [14, 30, 90, 180, 365, 730];

        return [
            'name' => fake()->sentence(3),
            'product' => Product::factory(),
            'days' => fake()->randomElement($durations),
        ];
    }
}
