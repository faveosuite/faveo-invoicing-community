<?php

namespace Tests\Unit\Backend\Seeders;

use App\Http\Controllers\Order\RenewController;
use App\License\Models\License;
use App\Model\Order\Invoice;
use App\Model\Order\InvoiceItem;
use App\Model\Order\Order;
use App\Model\Order\OrderEntitlement;
use App\Model\Payment\Plan;
use App\Model\Payment\PlanPrice;
use App\Model\Product\CloudProducts;
use App\Model\Product\Entitlement;
use App\Model\Product\Grant;
use App\Model\Product\Product;
use App\Model\Product\Subscription;
use App\Services\EntitlementService;
use App\User;
use Database\Seeders\v4_0_2_8_RC_1\DatabaseSeeder;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\DBTestCase;

/**
 * The v4.0.2.8 seeder moves agent counts off the old columns (products.show_agent,
 * plan_prices.no_of_agents, cart_items.agents) onto the Agents feature. Needs a testing DB
 * that still has those columns; skipped once they are dropped.
 */
class V4028Rc1AgentsSeederTest extends DBTestCase
{
    private Entitlement $agents;

    private Product $product;

    private string $currency;

    private User $client;

    protected function setUp(): void
    {
        parent::setUp();
        $this->agents = EntitlementService::agents();
        $this->product = Product::factory()->create();
        if ($this->name() !== 'test_after_the_move_no_agent_prices_are_recreated') {
            foreach ([['products', 'show_agent'], ['plan_prices', 'no_of_agents'], ['cart_items', 'agents']] as [$table, $column]) {
                if (! Schema::hasColumn($table, $column)) {
                    $this->markTestSkipped("{$table}.{$column} is already dropped on this database.");
                }
            }
            DB::table('products')->where('id', $this->product->id)->update(['show_agent' => 1]);
        }
        $this->client = User::factory()->create(['country' => 'US']);
        $this->currency = (string) getCurrencyForClient('US');
    }

    /** An old-style plan: renew price for $noOfAgents agents in plan_prices. */
    private function plan(int $noOfAgents, float $renew = 100): Plan
    {
        $plan = Plan::factory()->create(['product' => $this->product->id, 'days' => 30]);
        DB::table('plan_prices')->insert(['plan_id' => $plan->id, 'currency' => $this->currency, 'add_price' => $renew,
            'renew_price' => $renew, 'offer_price' => 0, 'no_of_agents' => (string) $noOfAgents]);

        return $plan;
    }

    /** An order on $plan whose last invoice line had $invoiceAgents agents and whose license ends in $digits. */
    private function order(Plan $plan, int $invoiceAgents, string $digits): Order
    {
        $order = Order::factory()->create([
            'product' => $this->product->id, 'client' => $this->client->id, 'order_status' => 'executed',
            'serial_key' => Crypt::encrypt('ABCDEFGHIJKL'.$digits),
        ]);
        Subscription::factory()->create(['order_id' => $order->id, 'plan_id' => $plan->id, 'product_id' => $this->product->id, 'user_id' => $this->client->id]);
        License::create(['product_id' => $this->product->id, 'user_id' => $this->client->id, 'license_code' => 'ABCDEFGHIJKL'.$digits, 'license_order_number' => $order->number]);
        foreach ([$invoiceAgents + 3, $invoiceAgents] as $agents) { // an older invoice, then the newest
            $invoice = Invoice::factory()->create(['user_id' => $this->client->id, 'currency' => $this->currency]);
            InvoiceItem::factory()->create(['invoice_id' => $invoice->id, 'product_id' => $this->product->id, 'agents' => (string) $agents]);
            DB::table('order_invoice_relations')->insert(['order_id' => $order->id, 'invoice_id' => $invoice->id]);
        }

        return $order;
    }

    private function seeder(): DatabaseSeeder
    {
        return new DatabaseSeeder;
    }

    /** What renewing costs today, read through the renewal code. */
    private function renewalCost(Order $order, Plan $plan): float
    {
        $price = PlanPrice::where('plan_id', $plan->id)->where('currency', $this->currency)->firstOrFail();

        return (new RenewController)->renewalCost($order->id, $price);
    }

    private function agentsExtra(Order $order): ?OrderEntitlement
    {
        return OrderEntitlement::where('order_id', $order->id)->where('entitlement_id', $this->agents->id)->first();
    }

    public function test_plans_get_their_agents_row(): void
    {
        $five = $this->plan(5);
        $none = $this->plan(0);
        $everyAgent = $this->plan(3);
        $everyAgent->entitlements()->attach($this->agents->id, ['value' => '0']);
        $interim = $this->plan(4);
        $interim->entitlements()->attach($this->agents->id, ['value' => null]); // count was in no_of_agents

        $this->seeder()->seedPlanAgentRows();

        $this->assertSame(5, EntitlementService::includedAgents($five));
        $this->assertTrue(EntitlementService::unlimitedAgents($none));
        $this->assertSame(0, EntitlementService::includedAgents($everyAgent));
        $this->assertSame(4, EntitlementService::includedAgents($interim));
    }

    public function test_orders_above_included_get_a_positive_extra(): void
    {
        $plan = $this->plan(5);
        $this->agents->prices()->create(['plan_id' => $plan->id, 'currency' => $this->currency, 'unit_price' => 18, 'renew_price' => 15]);
        $order = $this->order($plan, 7, '0007');
        $oldPrice = 100 + (7 - 5) * 15.0; // plan renewal + agents above the plan at the agent price

        $this->seeder()->seedPlanAgentRows();
        $this->seeder()->seedOrderAgentExtras();
        $this->seeder()->seedOrderAgentExtras(); // a second run changes nothing

        $extra = $this->agentsExtra($order);
        $this->assertSame(2, $extra?->quantity);
        $this->assertSame(1, OrderEntitlement::where('order_id', $order->id)->count());
        $this->assertSame($oldPrice, $this->renewalCost($order, $plan));
    }

    public function test_orders_below_included_get_a_negative_extra(): void
    {
        $plan = $this->plan(5);
        $order = $this->order($plan, 3, '0003');
        $oldPrice = 100 + (3 - 5) * (100 / 5); // no agent price: the plan's share per agent

        $this->seeder()->seedPlanAgentRows();
        $this->seeder()->seedOrderAgentExtras();

        $this->assertSame(-2, $this->agentsExtra($order)?->quantity);
        $this->assertSame((float) $oldPrice, $this->renewalCost($order, $plan));
    }

    public function test_fewer_agents_are_never_priced_above_the_plans_share(): void
    {
        $plan = $this->plan(5);
        $this->agents->prices()->create(['plan_id' => $plan->id, 'currency' => $this->currency, 'unit_price' => 30, 'renew_price' => 30]);
        $order = $this->order($plan, 3, '0003');

        $this->seeder()->seedPlanAgentRows();
        $this->seeder()->seedOrderAgentExtras();

        $this->assertSame(-2, $this->agentsExtra($order)?->quantity);
        $this->assertSame(60.0, $this->renewalCost($order, $plan)); // 100 − 2 × 20, not 100 − 2 × 30
    }

    public function test_unlimited_license_charged_for_more_agents_keeps_its_renewal_price(): void
    {
        $plan = $this->plan(5);
        $order = $this->order($plan, 7, '0000');
        $license = License::where('license_order_number', $order->number)->firstOrFail();

        $this->seeder()->seedPlanAgentRows();
        $this->seeder()->seedOrderAgentExtras();

        $this->assertSame(2, $this->agentsExtra($order)?->quantity);
        $this->assertTrue(Grant::where('license_id', $license->id)->where('source', Grant::ADMIN)->where('code', EntitlementService::AGENTS)->exists());
        $this->assertSame(100 + 2 * 20.0, $this->renewalCost($order, $plan));
        $this->assertSame('ABCDEFGHIJKL0000', $license->fresh()?->license_code);
    }

    /** Runs only once the old columns are dropped. */
    public function test_after_the_move_no_agent_prices_are_recreated(): void
    {
        if (Schema::hasColumn('products', 'show_agent') || Schema::hasColumn('plan_prices', 'no_of_agents')) {
            $this->markTestSkipped('The old agent columns are still on this database.');
        }
        $plan = Plan::factory()->create(['product' => $this->product->id, 'days' => 30]);
        CloudProducts::create(['cloud_product' => $this->product->id, 'cloud_free_plan' => $plan->id]);
        PlanPrice::factory()->create(['plan_id' => $plan->id, 'currency' => $this->currency, 'add_price' => 100, 'renew_price' => 100]);
        $plan->entitlements()->attach($this->agents->id, ['value' => '5']); // an admin removed its agent price

        $this->seeder()->seedAgentsEntitlements();

        $this->assertSame(0, $this->agents->prices()->where('plan_id', $plan->id)->count());
    }

    public function test_orders_at_included_get_no_extra(): void
    {
        $plan = $this->plan(5);
        $order = $this->order($plan, 5, '0005');

        $this->seeder()->seedPlanAgentRows();
        $this->seeder()->seedOrderAgentExtras();

        $this->assertNull($this->agentsExtra($order));
    }

    public function test_unlimited_license_on_a_counted_plan_gets_an_unlimited_override(): void
    {
        $plan = $this->plan(5);
        $order = $this->order($plan, 0, '0000');
        $license = License::where('license_order_number', $order->number)->firstOrFail();

        $this->seeder()->seedPlanAgentRows();
        $this->seeder()->seedOrderAgentExtras();

        $override = Grant::where('license_id', $license->id)->where('source', Grant::ADMIN)->where('code', EntitlementService::AGENTS)->first();
        $this->assertNotNull($override);
        $this->assertNull($override->value); // null: unlimited
        $this->assertNull($this->agentsExtra($order));
        $this->assertSame('ABCDEFGHIJKL0000', $license->fresh()?->license_code);
        $this->assertSame('ABCDEFGHIJKL0000', $order->fresh()?->serial_key);
    }

    public function test_license_codes_are_never_rewritten(): void
    {
        $plan = $this->plan(5);
        $this->order($plan, 7, '0007');
        $this->order($plan, 3, '0003');
        $this->order($plan, 9, '0006'); // invoice and license disagree: the license is kept
        $before = DB::table('licenses')->orderBy('id')->pluck('license_code', 'id')->all();
        $keys = DB::table('orders')->orderBy('id')->pluck('serial_key', 'id')->all();

        $this->seeder()->seedPlanAgentRows();
        $this->seeder()->seedOrderAgentExtras();

        $this->assertSame($before, DB::table('licenses')->orderBy('id')->pluck('license_code', 'id')->all());
        $this->assertSame($keys, DB::table('orders')->orderBy('id')->pluck('serial_key', 'id')->all());
    }

    public function test_cart_agents_above_included_become_an_agents_extra(): void
    {
        $plan = $this->plan(5);
        $cartId = DB::table('carts')->insertGetId(['user_id' => $this->client->id, 'currency' => $this->currency]);
        $above = DB::table('cart_items')->insertGetId(['cart_id' => $cartId, 'product_id' => $this->product->id, 'plan_id' => $plan->id, 'quantity' => 1, 'agents' => 8]);
        $below = DB::table('cart_items')->insertGetId(['cart_id' => $cartId, 'product_id' => $this->product->id, 'plan_id' => $plan->id, 'quantity' => 1, 'agents' => 2]);

        $this->seeder()->seedPlanAgentRows();
        $this->seeder()->seedCartAgentExtras();
        $this->seeder()->seedCartAgentExtras();

        $this->assertSame([(string) $this->agents->id => 3], json_decode((string) DB::table('cart_items')->where('id', $above)->value('extras'), true));
        $this->assertNull(DB::table('cart_items')->where('id', $below)->value('extras'));
    }
}
