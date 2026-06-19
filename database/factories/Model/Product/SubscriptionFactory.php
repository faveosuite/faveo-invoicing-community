<?php

declare(strict_types=1);

namespace Database\Factories\Model\Product;

use App\Model\Order\Order;
use App\Model\Payment\Plan;
use App\Model\Product\Product;
use App\Model\Product\Subscription;
use App\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Model\Product\Subscription>
 */
class SubscriptionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<mixed>     */
    protected $model = Subscription::class;

    public function definition()
    {
        $ends = now()->addDays(fake()->numberBetween(30, 365));

        return [
            'user_id' => User::factory(),
            'plan_id' => Plan::factory(),
            'order_id' => Order::factory(),
            'product_id' => Product::factory(),
            'ends_at' => $ends,
            'update_ends_at' => $ends->copy()->addDays(5),
            'support_ends_at' => $ends->copy()->addDays(5),
            'version' => fake()->numerify('v#.##'),
            'version_updated_at' => now()->subDays(fake()->numberBetween(1, 60)),
        ];
    }
}
