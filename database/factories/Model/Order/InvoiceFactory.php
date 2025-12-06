<?php

namespace Database\Factories\Model\Order;

use App\Model\Order\Invoice;
use App\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class InvoiceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    protected $model = Invoice::class;

    public function definition()
    {
        return [
            'user_id'        => User::factory(),
            'number'         => $this->faker->unique()->numerify('########'),
            'date'           => $this->faker->dateTimeBetween('-1 year', 'now')->format('Y-m-d'),
            'grand_total'    => $this->faker->randomFloat(2, 500, 5000),
            'currency'       => $this->faker->randomElement(['INR', 'USD', 'EUR']),
            'status'         => $this->faker->randomElement(['success', 'partially paid', 'pending']),
            'description'    => $this->faker->sentence(6),
        ];
    }
}
