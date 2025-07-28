<?php

namespace Database\Factories\Model\Order;

use App\Model\Order\Invoice;
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
            'id' => mt_rand(1, 999),
            'number' => '2344353',
            'grand_total' => 10000,
            'currency' => 'INR',
            'status' => 'success',
        ];
    }
}
