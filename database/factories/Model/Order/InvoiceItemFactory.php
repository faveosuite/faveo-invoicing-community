<?php

declare(strict_types=1);

namespace Database\Factories\Model\Order;

use App\Model\Order\Invoice;
use App\Model\Order\InvoiceItem;
use App\Model\Product\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Model\Order\InvoiceItem>
 */
class InvoiceItemFactory extends Factory
{
    protected $model = InvoiceItem::class;

    public function definition()
    {
        $price = fake()->randomFloat(2, 1, 1000);
        $qty = fake()->numberBetween(1, 10);
        $discount = fake()->randomFloat(2, 0, 50);
        $subtotal = ($price * $qty) - $discount;

        $product = Product::factory()->create();

        return [
            'invoice_id' => Invoice::factory(),
            'product_id' => $product->id,
            'product_name' => $product->name,
            'regular_price' => $price,
            'quantity' => $qty,
            'subtotal' => $subtotal,
            'agents' => fake()->numberBetween(1, 10),
        ];
    }
}
