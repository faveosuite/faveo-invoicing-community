<?php

declare(strict_types=1);

namespace Tests\Unit\Backend\Http\Controllers\Front\Cart;

use App\Http\Controllers\Order\OrderController;
use App\Model\Cart\Cart;
use App\Model\Order\Invoice;
use App\Model\Order\OrderEntitlement;
use App\Model\Payment\Plan;
use App\Model\Payment\PlanPrice;
use App\Model\Product\Entitlement;
use App\Model\Product\Product;
use App\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;
use Tests\DBTestCase;

class CartExtrasTest extends DBTestCase
{
    public function test_extras_chosen_in_the_cart_are_charged_and_given_to_the_order(): void
    {
        $this->withoutMiddleware();
        Queue::fake();
        Mail::fake();
        $this->actingAs(User::factory()->create(['country' => 'US']));
        $currency = (string) getCurrencyForClient('US');

        $product = Product::factory()->create();
        $plan = Plan::factory()->create(['product' => $product->id, 'status' => 1, 'days' => 30]);
        $periodId = (int) DB::table('periods')->insertGetId(['name' => 'Test 30 days', 'days' => '30']);
        $plan->periods()->attach($periodId);
        PlanPrice::factory()->create(['plan_id' => $plan->id, 'currency' => $currency, 'add_price' => 100, 'offer_price' => 0, 'no_of_agents' => 0]);
        $this->postJson('my-cart/items', ['product_id' => $product->id, 'plan_id' => $plan->id])->assertOk();
        $cart = Cart::where('user_id', auth()->id())->firstOrFail();
        $this->assertSame($currency, (string) $cart->currency);

        $storage = Entitlement::create(['feature' => 'storage', 'key' => 'gb', 'label' => 'Storage', 'type' => 'number']);
        $storage->prices()->create(['plan_id' => $plan->id, 'currency' => $currency, 'unit_price' => 2]);
        $chat = Entitlement::create(['feature' => 'chat', 'key' => 'enabled', 'label' => 'Chat', 'type' => 'boolean']);
        $chat->prices()->create(['plan_id' => $plan->id, 'currency' => $currency, 'unit_price' => 10]);
        $notForSale = Entitlement::create(['feature' => 'sla', 'key' => 'enabled', 'label' => 'SLA', 'type' => 'boolean']); // no price on this plan

        $line = $cart->items()->firstOrFail();
        $this->putJson("my-cart/items/{$line->id}", ['extras' => [$storage->id => 10, $chat->id => 5, $notForSale->id => 1]])->assertOk();

        // Chat is on/off (capped at 1) and SLA isn't for sale: 100 + 10×2 + 10.
        $line->refresh();
        $this->assertSame([$storage->id => 10, $chat->id => 1], array_map('intval', $line->extras));
        $this->assertSame(130.0, $line->unitPrice($currency));

        $this->putJson("my-cart/items/{$line->id}", ['domain' => 'helpdesk.example.com'])->assertOk();
        $this->postJson('my-cart/place-order', ['gateway' => 'Stripe'])->assertOk();
        $invoice = Invoice::where('user_id', auth()->id())->latest('id')->firstOrFail();
        $invoiceLine = $invoice->invoiceItem()->firstOrFail();
        $this->assertCount(2, $invoiceLine->extras);
        $this->assertStringContainsString('Storage × 10', (string) $invoiceLine->product_name);

        $order = (new OrderController)->executeOrder($invoice->id)->first();

        $this->assertEqualsCanonicalizing(
            [[$storage->id, 10, '2.0000'], [$chat->id, 1, '10.0000']],
            OrderEntitlement::where('order_id', $order->id)->get()->map(fn ($e) => [$e->entitlement_id, $e->quantity, $e->unit_price])->all(),
        );
    }
}
