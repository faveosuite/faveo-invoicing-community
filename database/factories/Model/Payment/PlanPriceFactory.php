<?php

namespace Database\Factories\Model\Payment;

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
            'country_id'=>0,
            'currency'=>'USD',
            'renew_price'=>$this->faker->numberBetween($min = 1500, $max = 6000),
        ];
    }
}
