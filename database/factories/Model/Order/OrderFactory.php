<?php

namespace Database\Factories\Model\Order;

use App\Model\Order\Order;
use Illuminate\Database\Eloquent\Factories\Factory;

class OrderFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    protected $model = Order::class;

    public function definition()
    {
        return [
            'number' => 1123244,
            'order_status' => 'executed',
            'serial_key' => 'QWWERRR32434544',
            'domain' => 'faveo.com',
            'price_override' => 12234443,
            'qty' => 1,
        ];
    }
}
