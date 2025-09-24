<?php

namespace Database\Factories\Model\Order;

use App\Model\Order\Invoice;
use App\Model\Order\InvoiceItem;
use App\Model\Order\Order;
use App\Model\Order\OrderInvoiceRelation;
use App\Model\Payment\Plan;
use App\Model\Product\Product;
use App\Model\Product\Subscription;
use App\User;
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
            'client' => User::factory(),
            'order_status' => $this->faker->randomElement(['executed', 'Terminated']),
            'invoice_item_id' => InvoiceItem::factory(),
            'serial_key' => strtoupper($this->faker->bothify('????????????????????????')),
            'product' => Product::factory(),
            'qty' => $this->faker->numberBetween(1, 10),
            'invoice_id' => Invoice::factory(),
            'number' => $this->faker->unique()->numerify('########'),
            'license_mode' => $this->faker->randomElement(['Database']),
        ];
    }

    public function withRelations(array $overrides = [])
    {
        return $this->state(fn () => $overrides)
            ->afterCreating(function (Order $order) {
                $user = User::find($order->client) ?? User::factory()->create();
                $product = Product::find($order->product) ?? Product::factory()->create();

                // Create Plan
                $plan = Plan::factory()->create(['product' => $product->id]);

                // Create Invoice
                $invoice = Invoice::factory()->create([
                    'user_id' => $user->id,
                ]);

                // Create Invoice Item
                InvoiceItem::factory()->create([
                    'invoice_id' => $invoice->id,
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                ]);

                // Order ↔ Invoice link
                OrderInvoiceRelation::create([
                    'order_id' => $order->id,
                    'invoice_id' => $invoice->id,
                ]);

                // Subscription
                Subscription::factory()->create([
                    'product_id' => $product->id,
                    'plan_id' => $plan->id,
                    'order_id' => $order->id,
                ]);

                // Attach for easy access in tests
                $order->setRelation('user', $user);
                $order->setRelation('productModel', $product);
                $order->setRelation('plan', $plan);
                $order->setRelation('invoice', $invoice);
            });
    }
}
