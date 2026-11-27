<?php

declare(strict_types=1);

namespace Tests\Unit\Backend\Http\Controllers\Front;

use App\License\Models\Installation;
use App\License\Models\License;
use App\Model\Order\Invoice;
use App\Model\Order\Order;
use App\Model\Order\OrderEntitlement;
use App\Model\Order\OrderInvoiceRelation;
use App\Model\Payment\Plan;
use App\Model\Payment\PlanPrice;
use App\Model\Product\CloudProducts;
use App\Model\Product\Entitlement;
use App\Model\Product\Product;
use App\Model\Product\Subscription;
use App\Services\EntitlementService;
use App\User;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
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
        $item = Entitlement::create(['code' => 'storage', 'label' => 'Storage', 'type' => 'number']);
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
        $item = Entitlement::create(['code' => 'storage', 'label' => 'Storage', 'type' => 'number']);
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

    public function test_cloud_agents_cannot_renew_below_the_agents_in_use(): void
    {
        $this->withoutMiddleware();
        $customer = User::factory()->create();
        $product = Product::factory()->create();
        $plan = Plan::factory()->create(['product' => $product->id, 'days' => 30]);
        CloudProducts::create(['cloud_product' => $product->id, 'cloud_free_plan' => Plan::factory()->create(['product' => $product->id])->id]);
        $agents = EntitlementService::agents();
        $plan->entitlements()->attach($agents->id, ['value' => '5']);
        $order = Order::factory()->create(['client' => $customer->id, 'product' => $product->id, 'order_status' => 'executed', 'serial_key' => '1234567890120008']);
        Subscription::factory()->create(['order_id' => $order->id, 'plan_id' => $plan->id, 'product_id' => $product->id, 'ends_at' => now()->addDays(20)]);
        License::create(['product_id' => $product->id, 'license_code' => '1234567890120008', 'license_order_number' => $order->number]);
        Installation::create(['product_id' => $product->id, 'license_code' => '1234567890120008', 'installation_domain' => 'tenant.example.test']);
        $extra = OrderEntitlement::create(['order_id' => $order->id, 'entitlement_id' => $agents->id, 'quantity' => 3, 'unit_price' => 3, 'currency' => 'USD']);
        // The tenant answers "true": more agents in use than the 6 asked for.
        $history = [];
        $stack = HandlerStack::create(new MockHandler([new Response(200, [], 'true{}')]));
        $stack->push(\GuzzleHttp\Middleware::history($history));
        $this->app->bind(Client::class, fn (): Client => new Client(['handler' => $stack]));
        $this->be($customer);

        $this->postJson("/my-order/{$order->id}/extras/{$agents->id}/renewal", ['quantity' => 1])
            ->assertStatus(400)->assertJsonPath('message', __('message.agent_reduce'));

        $this->assertNull($extra->fresh()->renew_quantity);
        $this->assertStringContainsString('number_of_agents=6', (string) $history[0]['request']->getBody());
    }

    /** A customer's live order on a plan including 5 agents, with an Agents price; cloud when asked. */
    private function agentsOrder(User $customer, bool $cloud = false): Order
    {
        $currency = (string) getCurrencyForClient((string) $customer->country);
        $product = Product::factory()->create();
        $plan = Plan::factory()->create(['product' => $product->id, 'days' => 30]);
        $plan->periods()->attach((int) DB::table('periods')->insertGetId(['name' => 'Test 30 days', 'days' => '30']));
        PlanPrice::factory()->create(['plan_id' => $plan->id, 'currency' => $currency]);
        $agents = EntitlementService::agents();
        $plan->entitlements()->attach($agents->id, ['value' => '5']);
        $agents->prices()->create(['plan_id' => $plan->id, 'currency' => $currency, 'unit_price' => 3]);
        if ($cloud) {
            CloudProducts::create(['cloud_product' => $product->id, 'cloud_free_plan' => Plan::factory()->create(['product' => $product->id])->id]);
        }
        $order = Order::factory()->create(['client' => $customer->id, 'product' => $product->id, 'order_status' => 'executed', 'serial_key' => '1234567890120005']);
        Subscription::factory()->create(['order_id' => $order->id, 'plan_id' => $plan->id, 'product_id' => $product->id, 'ends_at' => now()->addDays(20)]);

        return $order;
    }

    public function test_a_cloud_order_buys_agents_from_its_extras_and_the_open_invoice_blocks_plan_changes(): void
    {
        $this->withoutMiddleware();
        $customer = User::factory()->create(['country' => 'IN']);
        $order = $this->agentsOrder($customer, cloud: true);
        $agents = EntitlementService::agents();
        $this->be($customer);

        $row = collect($this->getJson("/my-order/{$order->id}/extras")->assertOk()->json('data.extras'))->firstWhere('id', $agents->id);
        $this->assertTrue($row['can_buy']);

        $invoiceId = $this->postJson("/my-order/{$order->id}/extras", ['entitlement_id' => $agents->id, 'quantity' => 2])->assertOk()->json('data.invoice_id');

        $this->assertSame((string) Invoice::findOrFail($invoiceId)->number, $order->pendingCloudChangeInvoice());
    }

    public function test_agents_set_by_an_admin_cannot_be_bought(): void
    {
        $this->withoutMiddleware();
        $customer = User::factory()->create(['country' => 'IN']);
        $order = $this->agentsOrder($customer);
        $agents = EntitlementService::agents();
        resolve(EntitlementService::class)->setOverride(Subscription::where('order_id', $order->id)->firstOrFail(), $agents, '12', null);
        $this->be($customer);

        $row = collect($this->getJson("/my-order/{$order->id}/extras")->assertOk()->json('data.extras'))->firstWhere('id', $agents->id);
        $this->assertFalse($row['can_buy']);
        $this->postJson("/my-order/{$order->id}/extras", ['entitlement_id' => $agents->id, 'quantity' => 2])
            ->assertStatus(412)->assertJsonPath('message.entitlement_id', __('message.extra_agents_overridden'));
    }

    public function test_cloud_agents_set_by_an_admin_reduce_without_asking_the_tenant(): void
    {
        // The license stays at the admin's count, so no agent in use is stranded.
        $this->withoutMiddleware();
        $customer = User::factory()->create(['country' => 'IN']);
        $order = $this->agentsOrder($customer, cloud: true);
        $agents = EntitlementService::agents();
        License::create(['product_id' => $order->product, 'license_code' => '1234567890120005', 'license_order_number' => $order->number]);
        Installation::create(['product_id' => $order->product, 'license_code' => '1234567890120005', 'installation_domain' => 'tenant.example.test']);
        $extra = OrderEntitlement::create(['order_id' => $order->id, 'entitlement_id' => $agents->id, 'quantity' => 3, 'unit_price' => 3, 'currency' => 'USD']);
        resolve(EntitlementService::class)->setOverride(Subscription::where('order_id', $order->id)->firstOrFail(), $agents, '20', null);
        // Any call to the tenant fails the test.
        $this->app->bind(Client::class, fn (): Client => new Client(['handler' => HandlerStack::create(new MockHandler([]))]));
        $this->be($customer);

        $this->postJson("/my-order/{$order->id}/extras/{$agents->id}/renewal", ['quantity' => 1])->assertOk();
        $this->assertSame(1, $extra->fresh()->renew_quantity);
    }
}
