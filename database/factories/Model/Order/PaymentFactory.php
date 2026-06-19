<?php

declare(strict_types=1);

namespace Database\Factories\Model\Order;

use App\Model\Order\Invoice;
use App\Model\Order\Payment;
use App\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Payment>
 */
class PaymentFactory extends Factory
{
    protected $model = Payment::class;

    public function definition(): array
    {
        return [
            'parent_id' => 0,
            'invoice_id' => Invoice::factory(),
            'amount' => fake()->randomFloat(2, 50, 5000),
            'payment_method' => fake()->randomElement(['razorpay', 'stripe', 'bank_transfer', 'manual']),
            'user_id' => User::factory(),
            'payment_status' => fake()->randomElement(['success', 'failed', 'pending']),
            'created_at' => now(),
            'amt_to_credit' => fake()->randomFloat(2, 10, 5000),
        ];
    }
}
