<?php

namespace Database\Factories\Model\Order;

use App\Model\Order\Invoice;
use App\Model\Order\InvoiceItem;
use App\Model\Product\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

class InvoiceItemFactory extends Factory
{
    protected $model = InvoiceItem::class;

    public function definition()
    {
        $price = $this->faker->randomFloat(2, 1, 1000);
        $qty = $this->faker->numberBetween(1, 10);
        $discount = $this->faker->randomFloat(2, 0, 50);
        $subtotal = ($price * $qty) - $discount;

        $product = Product::factory()->create();

        return [
            'invoice_id' => Invoice::factory(),
            'product_id' => $product->id,
            'product_name' => $product->name,
            'regular_price' => $price,
            'quantity' => $qty,
            'subtotal' => $subtotal,
            'agents' => $this->faker->numberBetween(1, 10),
        ];
    }
}
