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
        PlanPrice::factory()->create(['plan_id' => $plan->id, 'currency' => $currency, 'add_price' => 100, 'offer_price' => 0]);
        $this->postJson('my-cart/items', ['product_id' => $product->id, 'plan_id' => $plan->id])->assertOk();
        $cart = Cart::where('user_id', auth()->id())->firstOrFail();
        $this->assertSame($currency, (string) $cart->currency);

        $storage = Entitlement::create(['code' => 'storage', 'label' => 'Storage', 'type' => 'number']);
        $storage->prices()->create(['plan_id' => $plan->id, 'currency' => $currency, 'unit_price' => 2]);
        $chat = Entitlement::create(['code' => 'chat', 'label' => 'Chat', 'type' => 'boolean']);
        $chat->prices()->create(['plan_id' => $plan->id, 'currency' => $currency, 'unit_price' => 10]);
        $notForSale = Entitlement::create(['code' => 'sla', 'label' => 'SLA', 'type' => 'boolean']); // no price on this plan

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
        // The name stays the product's own; the add-ons live in extras (listed under it on the invoice).
        $this->assertStringNotContainsString('(+', (string) $invoiceLine->product_name);
        $this->assertSame(10, (int) collect($invoiceLine->extras)->firstWhere('entitlement_id', $storage->id)['quantity']);

        $order = (new OrderController)->executeOrder($invoice->id)->first();

        $this->assertEqualsCanonicalizing(
            [[$storage->id, 10, '2.0000'], [$chat->id, 1, '10.0000']],
            OrderEntitlement::where('order_id', $order->id)->get()->map(fn ($e) => [$e->entitlement_id, $e->quantity, $e->unit_price])->all(),
        );
    }

    public function test_a_limit_the_plan_includes_reports_its_included_amount_so_the_cart_can_start_there(): void
    {
        $this->withoutMiddleware();
        $this->actingAs(User::factory()->create(['country' => 'US']));
        $currency = (string) getCurrencyForClient('US');
        $product = Product::factory()->create();
        $plan = Plan::factory()->create(['product' => $product->id, 'status' => 1, 'days' => 30]);
        PlanPrice::factory()->create(['plan_id' => $plan->id, 'currency' => $currency, 'add_price' => 100, 'offer_price' => 0]);
        $this->postJson('my-cart/items', ['product_id' => $product->id, 'plan_id' => $plan->id])->assertOk();

        // Storage: the plan gives 10 and sells more. Chat: not in the plan, a pure add-on.
        $storage = Entitlement::create(['code' => 'storage', 'label' => 'Storage', 'type' => 'number']);
        $storage->prices()->create(['plan_id' => $plan->id, 'currency' => $currency, 'unit_price' => 2]);
        $plan->entitlements()->attach($storage->id, ['value' => '10']);
        $chat = Entitlement::create(['code' => 'chat', 'label' => 'Chat', 'type' => 'boolean']);
        $chat->prices()->create(['plan_id' => $plan->id, 'currency' => $currency, 'unit_price' => 10]);

        $line = Cart::where('user_id', auth()->id())->firstOrFail()->items()->firstOrFail();
        $this->putJson("my-cart/items/{$line->id}", ['extras' => [$storage->id => 5]])->assertOk();

        $extras = collect($line->refresh()->availableExtras($currency))->keyBy('entitlement_id');
        $this->assertSame(10, $extras[$storage->id]['included']);
        $this->assertSame(5, $extras[$storage->id]['quantity']); // the 5 on top: 15 in all, 5 charged
        $this->assertNull($extras[$chat->id]['included']);
        $this->assertSame(110.0, $line->unitPrice($currency)); // 100 + 5 × 2
        $this->assertArrayNotHasKey('included', $line->chosenExtras($currency)[0]); // the invoice keeps only what's charged
    }
}
