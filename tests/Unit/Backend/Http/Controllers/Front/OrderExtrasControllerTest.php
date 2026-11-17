<?php

declare(strict_types=1);

namespace Tests\Unit\Backend\Http\Controllers\Front;

use App\Model\Order\Invoice;
use App\Model\Order\Order;
use App\Model\Order\OrderEntitlement;
use App\Model\Order\OrderInvoiceRelation;
use App\Model\Payment\Plan;
use App\Model\Payment\PlanPrice;
use App\Model\Product\Entitlement;
use App\Model\Product\Product;
use App\Model\Product\Subscription;
use App\User;
use Illuminate\Support\Facades\DB;
use Tests\DBTestCase;

class OrderExtrasControllerTest extends DBTestCase
{
    public function test_customer_can_buy_extras_only_for_their_own_order(): void
    {
        $this->withoutMiddleware();
        $customer = User::factory()->create(['country' => 'IN']);
        $currency = (string) getCurrencyForClient('IN');
        $product = Product::factory()->create();
        $plan = Plan::factory()->create(['product' => $product->id, 'days' => 30]);
        $periodId = (int) DB::table('periods')->insertGetId(['name' => 'Test 30 days', 'days' => '30']);
        $plan->periods()->attach($periodId);
        PlanPrice::factory()->create(['plan_id' => $plan->id, 'currency' => $currency]);
        $order = Order::factory()->create(['product' => $product->id, 'client' => $customer->id, 'order_status' => 'executed']);
        Subscription::factory()->create(['order_id' => $order->id, 'plan_id' => $plan->id, 'product_id' => $product->id, 'ends_at' => now()->addDays(20)]);
        $item = Entitlement::create(['feature' => 'storage', 'key' => 'gb', 'label' => 'Storage', 'type' => 'number']);
        $item->prices()->create(['plan_id' => $plan->id, 'currency' => $currency, 'unit_price' => 3]);

        $this->be(User::factory()->create());
        $this->getJson("/my-order/{$order->id}/extras")->assertForbidden();

        $this->be($customer);
        $this->getJson("/my-order/{$order->id}/extras")->assertOk()->assertJsonPath('data.extras.0.can_buy', true);
        $invoiceId = $this->postJson("/my-order/{$order->id}/extras", ['entitlement_id' => $item->id, 'quantity' => 5])
            ->assertOk()->json('data.invoice_id');
        $this->assertNotNull($invoiceId);
    }

    public function test_what_renews_cannot_change_while_an_invoice_is_unpaid(): void
    {
        // Otherwise: switch extras off, raise the (cheaper) renewal, switch them back, pay.
        $this->withoutMiddleware();
        $customer = User::factory()->create();
        $order = Order::factory()->create(['client' => $customer->id, 'order_status' => 'executed']);
        $item = Entitlement::create(['feature' => 'storage', 'key' => 'gb', 'label' => 'Storage', 'type' => 'number']);
        $extra = OrderEntitlement::create(['order_id' => $order->id, 'entitlement_id' => $item->id, 'quantity' => 10, 'unit_price' => 3, 'currency' => 'USD']);
        $paid = Invoice::factory()->create(['user_id' => $customer->id, 'status' => 'success']);
        OrderInvoiceRelation::create(['order_id' => $order->id, 'invoice_id' => $paid->id]);
        $this->be($customer);

        $this->postJson("/my-order/{$order->id}/extras/{$item->id}/renewal", ['quantity' => 0])->assertOk();
        $this->assertSame(0, $extra->fresh()->renew_quantity);

        $renewal = Invoice::factory()->create(['user_id' => $customer->id, 'status' => 'pending']);
        OrderInvoiceRelation::create(['order_id' => $order->id, 'invoice_id' => $renewal->id]);

        $this->postJson("/my-order/{$order->id}/extras/{$item->id}/renewal", ['quantity' => 10])->assertStatus(409);
        $this->assertSame(0, $extra->fresh()->renew_quantity); // still off: the cheap invoice stays honest
    }
}
