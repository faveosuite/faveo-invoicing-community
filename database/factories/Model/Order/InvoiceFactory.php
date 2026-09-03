<?php

declare(strict_types=1);

namespace Database\Factories\Model\Order;

use App\Model\Order\Invoice;
use App\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Invoice>
 */
class InvoiceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<mixed>     */
    protected $model = Invoice::class;

    public function definition()
    {
        return [
            'user_id' => User::factory(),
            'number' => fake()->unique()->numerify('########'),
            'date' => fake()->dateTimeBetween('-1 year', 'now')->format('Y-m-d'),
            'grand_total' => fake()->randomFloat(2, 500, 5000),
            'currency' => fake()->randomElement(['INR', 'USD', 'EUR']),
            'status' => fake()->randomElement(['success', 'partially paid', 'pending']),
            'description' => fake()->sentence(6),
        ];
    }
}
