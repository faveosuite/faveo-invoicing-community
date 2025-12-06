<?php

namespace Database\Factories\Model\Product;

use App\Model\Order\Order;
use App\Model\Payment\Plan;
use App\Model\Product\Product;
use App\Model\Product\Subscription;
use App\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class SubscriptionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    protected $model = Subscription::class;

    public function definition()
    {
        $ends = now()->addDays($this->faker->numberBetween(30, 365));

        return [
            'user_id' => User::factory(),
            'plan_id' => Plan::factory(),
            'order_id' => Order::factory(),
            'product_id' => Product::factory(),
            'ends_at' => $ends,
            'update_ends_at' => $ends->copy()->addDays(5),
            'support_ends_at' => $ends->copy()->addDays(5),
            'version' => $this->faker->numerify('v#.##'),
            'version_updated_at' => now()->subDays($this->faker->numberBetween(1, 60)),
        ];
    }
}
