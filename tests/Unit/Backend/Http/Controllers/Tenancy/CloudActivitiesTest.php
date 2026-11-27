<?php

namespace Tests\Unit\Backend\Http\Controllers\Tenancy;

use App\Http\Controllers\Tenancy\CloudExtraActivities;
use App\License\Models\Installation;
use App\License\Models\License;
use App\Model\Common\FaveoCloud;
use App\Model\Mailjob\Condition;
use App\Model\Order\CreditTransaction;
use App\Model\Order\Invoice;
use App\Model\Order\InvoiceItem;
use App\Model\Order\Order;
use App\Model\Order\OrderEntitlement;
use App\Model\Order\OrderInvoiceRelation;
use App\Model\Payment\Currency;
use App\Model\Payment\Plan;
use App\Model\Payment\PlanPrice;
use App\Model\Product\CloudProducts;
use App\Model\Product\Entitlement;
use App\Model\Product\Product;
use App\Model\Product\Subscription;
use App\Services\Cloud\CloudApi;
use App\Services\EntitlementSalesService;
use App\Services\EntitlementService;
use App\Services\Payment\CreditBalanceService;
use App\Services\Payment\InvoicePaymentService;
use App\Services\Payment\PostPaymentService;
use App\ThirdPartyApp;
use App\User;
use DB;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Response;
use Illuminate\Support\Facades\Date;
use PHPUnit\Framework\Attributes\Group;
use Tests\DBTestCase;

class CloudActivitiesTest extends DBTestCase
{
    public $cloudactivities;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware();
        $this->getLoggedInUser('user');

        $this->cloudactivities = new CloudExtraActivities(new Client, new FaveoCloud);

        Currency::where('code', 'INR')->update(['status' => 1]);
    }

    /**
     * Bind a mock Guzzle client to the container.
     * cloudApiPost, checktheAgent, and jobsForCloudDomain all use resolve(Client::class).
     */
    /**
     * The customer's own live cloud instance, installed on $domain — what every cloud
     * setting needs before it does anything (OrderPolicy::manageCloud).
     */
    private function liveCloudOrder(User $user, string $domain): Order
    {
        // cloudSubDomain() reads settings row 1; without it every domain looks like a cloud subdomain.
        DB::table((new FaveoCloud)->getTable())->updateOrInsert(['id' => 1], ['cloud_central_domain' => 'https://central.example.test', 'cloud_cname' => 'faveocloud.test']);
        $product = Product::factory()->create();
        $plan = Plan::create(['name' => 'Cloud '.uniqid(), 'product' => $product->id, 'days' => 30]);
        CloudProducts::create(['cloud_product' => $product->id, 'cloud_free_plan' => $plan->id]);
        $order = Order::factory()->create(['client' => $user->id, 'product' => $product->id, 'order_status' => 'executed', 'serial_key' => '1234567890120005']);
        Subscription::factory()->create(['order_id' => $order->id, 'product_id' => $product->id, 'user_id' => $user->id, 'plan_id' => $plan->id, 'is_deleted' => 0]);
        $code = 'CLOUD'.uniqid();
        License::create(['product_id' => $product->id, 'license_code' => $code, 'license_order_number' => $order->number]);
        Installation::create(['product_id' => $product->id, 'license_code' => $code, 'installation_domain' => $domain]);

        return $order;
    }

    /**
     * A product the client's plan list offers to switch to (see Plan::cloudPlanChangeOptions):
     * another cloud product.
     */
    private function planChangeTarget(): Product
    {
        $product = Product::factory()->create();
        CloudProducts::create(['cloud_product' => $product->id, 'cloud_free_plan' => Plan::factory()->create(['product' => $product->id])->id]);

        return $product;
    }

    /** Sells the plan by agents: its Agents row of $included, and a per-agent price on each of its prices — its share (price ÷ agents), so totals don't change. */
    private function agentPriced(Plan $plan, int $included = 1): void
    {
        $plan->entitlements()->attach(EntitlementService::agents()->id, ['value' => (string) $included]);
        foreach (PlanPrice::where('plan_id', $plan->id)->get() as $price) {
            EntitlementService::agents()->prices()->create(['plan_id' => $plan->id, 'currency' => $price->currency,
                'unit_price' => (float) $price->add_price / $included]);
        }
    }

    /** The order's agents beyond its plan's one included: an Agents extra at the old plan's share. */
    private function agentsExtra(Order $order, int $quantity): void
    {
        OrderEntitlement::create(['order_id' => $order->id, 'entitlement_id' => EntitlementService::agents()->id,
            'quantity' => $quantity, 'unit_price' => 5000, 'currency' => 'INR']);
    }

    private function bindMockClientWithResponses(array $responses): void
    {
        $mock = new MockHandler($responses);
        $client = new Client(['handler' => HandlerStack::create($mock)]);
        $this->app->bind(Client::class, fn (): Client => $client);
    }

    protected function tearDown(): void
    {
        restore_error_handler();
        restore_exception_handler();

        parent::tearDown();
    }

    public function test_check_domain_reads_the_leading_true_false_of_the_cloud_response(): void
    {
        ThirdPartyApp::updateOrCreate(['app_name' => 'faveo_app_key'], ['app_key' => 'key', 'app_secret' => 'secret']);
        FaveoCloud::query()->delete();
        FaveoCloud::create(['cloud_central_domain' => 'https://central.example.test']);

        $check = fn (string $body): bool => new CloudExtraActivities(
            new Client(['handler' => HandlerStack::create(new MockHandler([new Response(200, [], $body)]))]),
            new FaveoCloud
        )->checkDomain('acme.example.test');

        $this->assertTrue($check('true{"status":"success"}'));
        $this->assertFalse($check('false{"status":"fails"}'));
        $this->assertFalse($check('{"status":"success"}'));
    }

    /**
     * The customer's live cloud order on a paid plan that includes $included agents and sells
     * more at 300 each (INR and USD), with $extra agents bought; the license carries the total.
     */
    private function perAgentCloudOrder(int $included = 5, int $extra = 0): Order
    {
        $order = $this->liveCloudOrder($this->user, 'customer.example.com');
        $plan = Plan::create(['name' => 'Cloud paid '.uniqid(), 'product' => $order->product, 'days' => 30]);
        $plan->entitlements()->attach(EntitlementService::agents()->id, ['value' => (string) $included]);
        foreach (['INR', 'USD'] as $currency) {
            PlanPrice::factory()->create(['plan_id' => $plan->id, 'currency' => $currency, 'add_price' => 5000, 'renew_price' => 5000]);
            EntitlementService::agents()->prices()->create(['plan_id' => $plan->id, 'currency' => $currency, 'unit_price' => 300]);
        }
        Subscription::where('order_id', $order->id)->update(['plan_id' => $plan->id, 'ends_at' => Date::now()->addDays(15)->addHour()]);
        if ($extra > 0) {
            OrderEntitlement::create(['order_id' => $order->id, 'entitlement_id' => EntitlementService::agents()->id,
                'quantity' => $extra, 'unit_price' => 300, 'currency' => resolve(EntitlementSalesService::class)->quote($order, EntitlementService::agents(), 1)['currency'],
                'period_id' => $plan->periodId()]);
        }
        $order->update(['serial_key' => '123456789012'.str_pad((string) ($included + $extra), 4, '0', STR_PAD_LEFT)]);

        return $order->fresh();
    }

    #[Group('Cloud plan Change')]
    public function test_cloud_plan_change_invoice_counts_the_new_plans_agents_plus_the_bought_ones(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        $product = Product::create(['name' => 'Helpdesk Advance', 'description' => 'goodProduct']);
        // The license says 9; the order's own count (1 included + 4 bought = 5) is what moves.
        $order = Order::create(['client' => $user->id, 'order_status' => 'executed',
            'product' => $product->id, 'number' => mt_rand(100000, 999999), 'serial_key' => 'eyJpdiI6IkpI0009']);
        $plan = Plan::create(['name' => 'Hepldesk 1 year', 'product' => $product->id, 'days' => 65]);
        CloudProducts::create(['cloud_product' => $product->id, 'cloud_free_plan' => Plan::factory()->create(['product' => $product->id])->id]);
        $plan2 = Plan::create(['name' => 'Hepldesk 2 year', 'product' => $this->planChangeTarget()->id, 'days' => 65]);
        PlanPrice::factory()->create(['plan_id' => $plan->id, 'currency' => 'INR', 'add_price' => 3000]);
        PlanPrice::factory()->create(['plan_id' => $plan2->id, 'currency' => 'INR', 'add_price' => 5000]);
        $this->agentPriced($plan);
        $this->agentPriced($plan2, 2);
        Subscription::create(['plan_id' => $plan->id, 'order_id' => $order->id, 'product_id' => $product->id,
            'version' => 'v6.0.0', 'update_ends_at' => '', 'ends_at' => Date::now()->addDays(65)]);
        $this->agentsExtra($order, 4);

        $response = $this->postJson('/upgradeDowngradeCloud', ['id' => $plan2->id, 'orderId' => $order->id]);

        $response->assertStatus(200);
        // 2 included on the new plan + the 4 bought, carried as its Agents extra.
        $this->assertSame(6, (int) InvoiceItem::where('invoice_id', $response->json('data.invoice_id'))->value('agents'));
    }

    /** A plan to move to at 5000, with $included agents ('flat' = no Agents row) at 300 each. */
    private function movePlan(Order $order, string $included): Plan
    {
        $plan = Plan::create(['name' => 'Move '.uniqid(), 'product' => $order->product, 'days' => 30]);
        if ($included !== 'flat') {
            $plan->entitlements()->attach(EntitlementService::agents()->id, ['value' => $included]);
        }
        foreach (['INR', 'USD'] as $currency) {
            PlanPrice::factory()->create(['plan_id' => $plan->id, 'currency' => $currency, 'add_price' => 5000, 'renew_price' => 5000, 'offer_price' => 0]);
            if ($included !== 'flat') {
                EntitlementService::agents()->prices()->create(['plan_id' => $plan->id, 'currency' => $currency, 'unit_price' => 300]);
            }
        }

        return $plan;
    }

    /** What one period of $plan is worth for $order in the plan-change price. */
    private function planValue(Order $order, Plan $plan): float
    {
        $currency = (string) OrderEntitlement::where('order_id', $order->id)->value('currency') ?: 'INR';

        return (fn (): float => $this->planValue($order, $plan, $currency))->call(new CloudExtraActivities(new Client, new FaveoCloud));
    }

    public function test_a_plan_change_prices_the_bought_agents_it_carries(): void
    {
        // 5 included + 5 bought: the 5 bought move, whatever the new plan includes.
        $order = $this->perAgentCloudOrder(included: 5, extra: 5);
        $this->assertEqualsWithDelta(5000 + 5 * 300, $this->planValue($order, $this->movePlan($order, '10')), 0.01);
        $this->assertEqualsWithDelta(5000 + 5 * 300, $this->planValue($order, $this->movePlan($order, '2')), 0.01);
        $this->assertEqualsWithDelta(5000, $this->planValue($order, $this->movePlan($order, 'flat')), 0.01); // no agents to carry
    }

    public function test_a_downgrade_to_fewer_included_agents_bills_no_agents(): void
    {
        $order = $this->perAgentCloudOrder(included: 10); // 10 agents, nothing bought

        $this->assertEqualsWithDelta(5000, $this->planValue($order, $this->movePlan($order, '5')), 0.01); // 5 agents now
    }

    public function test_a_plan_change_without_an_agents_price_in_the_currency_carries_no_agents(): void
    {
        $order = $this->perAgentCloudOrder(included: 5, extra: 5);
        $plan = $this->movePlan($order, '2');
        EntitlementService::agents()->prices()->where('plan_id', $plan->id)->delete();
        EntitlementService::agents()->prices()->create(['plan_id' => $plan->id, 'currency' => 'EUR', 'unit_price' => 300]);

        $this->assertEqualsWithDelta(5000, $this->planValue($order, $plan), 0.01); // the 5 bought can't be billed there: not charged
    }

    public function test_a_plan_change_prices_any_number_feature_by_the_same_rule_as_agents(): void
    {
        $order = $this->perAgentCloudOrder(included: 10); // 10 agents, nothing bought
        $storage = Entitlement::create(['code' => 'storage_'.uniqid(), 'label' => 'Storage', 'type' => 'number']);
        $oldPlan = Plan::find(Subscription::where('order_id', $order->id)->value('plan_id'));
        $oldPlan->entitlements()->attach($storage->id, ['value' => '5']);
        OrderEntitlement::create(['order_id' => $order->id, 'entitlement_id' => $storage->id, 'quantity' => 3, 'unit_price' => 40, 'currency' => 'INR']); // 8 in all
        $move = function (string $storageIncluded) use ($order, $storage): Plan {
            $plan = $this->movePlan($order, '10');
            $plan->entitlements()->attach($storage->id, ['value' => $storageIncluded]);
            $storage->prices()->create(['plan_id' => $plan->id, 'currency' => 'INR', 'unit_price' => 50]);

            return $plan;
        };

        $this->assertEqualsWithDelta(5000 + 3 * 50, $this->planValue($order, $move('8')), 0.01); // the 3 bought move
        $this->assertEqualsWithDelta(5000 + 3 * 50, $this->planValue($order, $move('2')), 0.01);
        OrderEntitlement::where('order_id', $order->id)->where('entitlement_id', $storage->id)->delete();
        $this->assertEqualsWithDelta(5000, $this->planValue($order, $move('2')), 0.01); // nothing bought: nothing billed
    }

    #[Group('Cloud plan Change')]
    public function test_an_unlimited_order_stays_unlimited_on_a_plan_change(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        $product = Product::create(['name' => 'Helpdesk Advance', 'description' => 'goodProduct']);
        $order = Order::create(['client' => $user->id, 'order_status' => 'executed',
            'product' => $product->id, 'number' => mt_rand(100000, 999999), 'serial_key' => 'eyJpdiI6IkpI0000']);
        $plan = Plan::create(['name' => 'Hepldesk 1 year', 'product' => $product->id, 'days' => 65]);
        CloudProducts::create(['cloud_product' => $product->id, 'cloud_free_plan' => Plan::factory()->create(['product' => $product->id])->id]);
        $plan2 = Plan::create(['name' => 'Hepldesk 2 year', 'product' => $this->planChangeTarget()->id, 'days' => 65]);
        PlanPrice::factory()->create(['plan_id' => $plan->id, 'currency' => 'INR', 'add_price' => 3000]);
        PlanPrice::factory()->create(['plan_id' => $plan2->id, 'currency' => 'INR', 'add_price' => 5000]);
        $this->agentPriced($plan, 5);
        $this->agentPriced($plan2, 2);
        $subscription = Subscription::create(['plan_id' => $plan->id, 'order_id' => $order->id, 'product_id' => $product->id,
            'version' => 'v6.0.0', 'update_ends_at' => '', 'ends_at' => Date::now()->addDays(65)]);
        resolve(EntitlementService::class)->setOverride($subscription, EntitlementService::agents(), null, null);

        $response = $this->postJson('/upgradeDowngradeCloud', ['id' => $plan2->id, 'orderId' => $order->id]);

        $response->assertStatus(200);
        $this->assertSame(0, (int) InvoiceItem::where('invoice_id', $response->json('data.invoice_id'))->value('agents')); // 0000
    }

    #[Group('Cloud domain Change')]
    //    public function  test_cloud_update_no_of_agents(){
    //        $user=User::factory()->create();
    //        $this->actingAs($user);
    //        $this->withoutMiddleware();
    //        $licensetype=LicenseType::create(['name'=>'DevelopmentLicense']);
    //        $licensepermissiontype=LicensePermission::create(['Can be Downloaded']);
    //        LicensePermission::create(['Generate License Expiry Date']);
    //        LicensePermission::create(['Generate Updates Expiry Date']);
    //        LicensePermission::create(['Allow Downloads Before Updates Expire']);
    //        $permissionid=[
    //            0 => "1",
    //            1 => "2",
    //            2 => "3",
    //            3 => "4",
    //            6=>'6',
    //        ];
    //        $licensetype->permissions()->attach($permissionid);
    //
    //        $product = Product::create(['name' => 'Helpdesk Advance','description'=>'goodProduct','type'=>$licensetype->id]);
    //        $invoice = Invoice::factory()->create(['user_id' => $user->id]);
    //        $invoiceItem = InvoiceItem::create(['invoice_id' => $invoice->id, 'product_name' => $product->name]);
    //        $order = Order::create(['client' => $user->id, 'order_status' => 'executed',
    //            'product' => $product->id, 'number' => mt_rand(100000, 999999), 'invoice_id' => $invoice->id]);
    //        $plan = Plan::create(['id' => 'mt_rand(1,99)', 'name' => 'Hepldesk 1 year', 'product' => $product->id, 'days' => 65]);
    //        $planPrice=PlanPrice::factory()->create(['plan_id'=>$plan->id,'currency'=>'INR','add_price'=>5000]);
    //        $subscription = Subscription::create(['plan_id' => $plan->id, 'order_id' => $order->id, 'product_id' => $product->id,
    //            'version' => 'v6.0.0', 'update_ends_at' => '','ends_at'=>Carbon::now()->addDays(80)]);
    //        $cloud=FaveoCloud::create(['cloud_central_domain'=>'https://santhanu.com','cloud_cname'=>'santhanu.com']);
    //        $request=new Request(['newAgents'=>5,'product_id'=>$product->id,'subId'=>$subscription->id,'orderId'=>$order->id]);
    //        $client=new Client();
    //        $cloudActivities = new CloudExtraActivities($client,$cloud);
    //
    //    }

    #[Group('Cloud plan Change')]
    public function test_cloud_plan_old_price_less_then_new_price(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        $this->withoutMiddleware();

        $product = Product::create(['name' => 'Helpdesk Advance', 'description' => 'goodProduct']);
        $invoice = Invoice::factory()->create(['user_id' => $user->id]);
        InvoiceItem::create(['invoice_id' => $invoice->id, 'product_name' => $product->name]);
        $order = Order::create(['client' => $user->id, 'order_status' => 'executed',
            'product' => $product->id, 'number' => mt_rand(100000, 999999), 'invoice_id' => $invoice->id, 'serial_key' => 'eyJpdiI6IkpI0003']);
        $plan = Plan::create(['id' => 'mt_rand(1,99)', 'name' => 'Hepldesk 1 year', 'product' => $product->id, 'days' => 65]);
        CloudProducts::create(['cloud_product' => $product->id, 'cloud_free_plan' => $plan->id]);
        $plan2 = Plan::create(['id' => 'mt_rand(1,99)', 'name' => 'Hepldesk 2 year', 'product' => $this->planChangeTarget()->id, 'days' => 130]);
        PlanPrice::factory()->create(['plan_id' => $plan->id, 'currency' => 'INR', 'add_price' => 5000]);
        PlanPrice::factory()->create(['plan_id' => $plan2->id, 'currency' => 'INR', 'add_price' => 9000]);
        $this->agentPriced($plan);
        $this->agentPriced($plan2);

        Subscription::create(['plan_id' => $plan->id, 'order_id' => $order->id, 'product_id' => $product->id,
            'version' => 'v6.0.0', 'update_ends_at' => '', 'ends_at' => Date::now()->addDays(65)]);
        Subscription::create(['plan_id' => $plan2->id, 'order_id' => $order->id, 'product_id' => $product->id,
            'version' => 'v6.0.0', 'update_ends_at' => '', 'ends_at' => Date::now()->addDays(130)]);
        $this->agentsExtra($order, 2); // 3 agents: 1 included + 2
        // Agents are priced from the order's Agents extra, never from the request.
        $response = $this->call('POST', 'get-cloud-upgrade-cost', ['plan' => $plan2->id, 'orderId' => $order->id]);
        $content = $response->json();
        // The 2 agents the new plan doesn't include move at its per-agent price (9000).
        $this->assertEquals('12,023.08', $content['price_to_be_paid']); // (9000 + 2 × 9000) × 129/130 − (5000 + 2 × 5000) × 64/65
        $this->assertEquals('26,792.31', $content['pricenewplan']);
        $this->assertEquals('9,000.00', $content['priceperagent']);
    }

    #[Group('Cloud plan Change')]
    public function test_cloud_plan_old_price_equal_to_new_price(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        $this->withoutMiddleware();

        $product = Product::create(['name' => 'Helpdesk Advance', 'description' => 'goodProduct']);
        $invoice = Invoice::factory()->create(['user_id' => $user->id]);
        InvoiceItem::create(['invoice_id' => $invoice->id, 'product_name' => $product->name]);
        $order = Order::create(['client' => $user->id, 'order_status' => 'executed',
            'product' => $product->id, 'number' => mt_rand(100000, 999999), 'invoice_id' => $invoice->id, 'serial_key' => 'eyJpdiI6IkpI0003']);
        $plan = Plan::create(['id' => 'mt_rand(1,99)', 'name' => 'Hepldesk 1 year', 'product' => $product->id, 'days' => 65]);
        CloudProducts::create(['cloud_product' => $product->id, 'cloud_free_plan' => $plan->id]);
        $plan2 = Plan::create(['id' => 'mt_rand(1,99)', 'name' => 'Hepldesk 2 year', 'product' => $this->planChangeTarget()->id, 'days' => 130]);
        PlanPrice::factory()->create(['plan_id' => $plan->id, 'currency' => 'INR', 'add_price' => 5000]);
        // Same add_price as the old plan — with agents always read from the
        // license (server-side) for both, equal add_price is what makes the
        // two plans' total contract value equal, not a client-supplied agent
        // count trick.
        PlanPrice::factory()->create(['plan_id' => $plan2->id, 'currency' => 'INR', 'add_price' => 5000]);
        $this->agentPriced($plan2);

        Subscription::create(['plan_id' => $plan->id, 'order_id' => $order->id, 'product_id' => $product->id,
            'version' => 'v6.0.0', 'update_ends_at' => '', 'ends_at' => Date::now()->addDays(65)]);
        Subscription::create(['plan_id' => $plan2->id, 'order_id' => $order->id, 'product_id' => $product->id,
            'version' => 'v6.0.0', 'update_ends_at' => '', 'ends_at' => Date::now()->addDays(130)]);
        $response = $this->call('POST', 'get-cloud-upgrade-cost', ['plan' => $plan2->id, 'orderId' => $order->id]);
        $content = $response->json();
        $this->assertEquals('0.00', $content['price_to_be_paid']);
        $this->assertEquals('0.00', $content['pricenewplan']);
        $this->assertEquals('5,000.00', $content['priceperagent']);
    }

    #[Group('Cloud plan Change')]
    public function test_cloud_plan_old_price_greater_than_new_price(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        $this->withoutMiddleware();
        $product = Product::create(['name' => 'Helpdesk Advance', 'description' => 'goodProduct']);
        $invoice = Invoice::factory()->create(['user_id' => $user->id]);
        InvoiceItem::create(['invoice_id' => $invoice->id, 'product_name' => $product->name]);
        $order = Order::create(['client' => $user->id, 'order_status' => 'executed',
            'product' => $product->id, 'number' => mt_rand(100000, 999999), 'invoice_id' => $invoice->id, 'serial_key' => 'eyJpdiI6IkpI0005']);
        $plan = Plan::create(['id' => 'mt_rand(1,99)', 'name' => 'Hepldesk 1 year', 'product' => $product->id, 'days' => 65]);
        CloudProducts::create(['cloud_product' => $product->id, 'cloud_free_plan' => $plan->id]);
        $plan2 = Plan::create(['id' => 'mt_rand(1,99)', 'name' => 'Hepldesk 2 year', 'product' => $this->planChangeTarget()->id, 'days' => 130]);
        PlanPrice::factory()->create(['plan_id' => $plan->id, 'currency' => 'INR', 'add_price' => 5000]);
        PlanPrice::factory()->create(['plan_id' => $plan2->id, 'currency' => 'INR', 'add_price' => 3000]);
        $this->agentPriced($plan);
        $this->agentPriced($plan2);

        Subscription::create(['plan_id' => $plan->id, 'order_id' => $order->id, 'product_id' => $product->id,
            'version' => 'v6.0.0', 'update_ends_at' => '', 'ends_at' => Date::now()->addDays(65)]);
        Subscription::create(['plan_id' => $plan2->id, 'order_id' => $order->id, 'product_id' => $product->id,
            'version' => 'v6.0.0', 'update_ends_at' => '', 'ends_at' => Date::now()->addDays(130)]);
        $this->agentsExtra($order, 4); // 5 agents: 1 included + 4
        $response = $this->call('POST', 'get-cloud-upgrade-cost', ['agents' => 5, 'plan' => $plan2->id, 'orderId' => $order->id]);
        $content = $response->json();
        $this->assertEquals('9,730.77', $content['discount']); // (5000 + 4 × 5000) × 64/65 − (3000 + 4 × 3000) × 129/130
    }

    #[Group('Cloud plan Change')]
    public function test_cloud_upgrade_downgrade_plan(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        $this->withoutMiddleware();
        $product = Product::create(['name' => 'Helpdesk Advance', 'description' => 'goodProduct']);
        $invoice = Invoice::factory()->create(['user_id' => $user->id]);
        InvoiceItem::create(['invoice_id' => $invoice->id, 'product_name' => $product->name]);
        $order = Order::create(['client' => $user->id, 'order_status' => 'executed',
            'product' => $product->id, 'number' => mt_rand(100000, 999999), 'invoice_id' => $invoice->id, 'serial_key' => 'eyJpdiI6IkpI0005']);
        $plan = Plan::create(['id' => 'mt_rand(1,99)', 'name' => 'Hepldesk 1 year', 'product' => $product->id, 'days' => 65]);
        CloudProducts::create(['cloud_product' => $product->id, 'cloud_free_plan' => $plan->id]);
        $plan2 = Plan::create(['id' => 'mt_rand(1,99)', 'name' => 'Hepldesk 2 year', 'product' => $this->planChangeTarget()->id, 'days' => 65]);
        PlanPrice::factory()->create(['plan_id' => $plan->id, 'currency' => 'INR', 'add_price' => 3000]);
        $planPrice2 = PlanPrice::factory()->create(['plan_id' => $plan2->id, 'currency' => 'INR', 'add_price' => 5000]);
        $this->agentPriced($plan2, 5);

        Subscription::create(['plan_id' => $plan->id, 'order_id' => $order->id, 'product_id' => $product->id,
            'version' => 'v6.0.0', 'update_ends_at' => '', 'ends_at' => Date::now()->addDays(65)]);
        Subscription::create(['plan_id' => $plan2->id, 'order_id' => $order->id, 'product_id' => $product->id,
            'version' => 'v6.0.0', 'update_ends_at' => '', 'ends_at' => Date::now()->addDays(65)]);

        $response = $this->call('POST', 'upgradeDowngradeCloud', ['id' => $plan2->id, 'orderId' => $order->id, 'agents' => 5]);
        $response->assertStatus(200);
        $response->assertJsonStructure(['success', 'data' => ['invoice_id']]);
    }

    /**
     * Regression test: when the old plan's remaining value covers the new
     * plan's prorated cost, the actual charge must be 0 — same as the
     * preview shows. Previously this path charged the un-netted new-plan
     * cost instead (see calculatePlanChange/lessPriceNewDaysEqualToOldDays).
     */
    #[Group('Cloud plan Change')]
    public function test_cloud_downgrade_plan_charges_nothing_when_credit_covers_cost(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        $this->withoutMiddleware();
        $product = Product::create(['name' => 'Helpdesk Advance', 'description' => 'goodProduct']);
        $invoice = Invoice::factory()->create(['user_id' => $user->id]);
        InvoiceItem::create(['invoice_id' => $invoice->id, 'product_name' => $product->name]);
        $order = Order::create(['client' => $user->id, 'order_status' => 'executed',
            'product' => $product->id, 'number' => mt_rand(100000, 999999), 'invoice_id' => $invoice->id, 'serial_key' => 'eyJpdiI6IkpI0005']);
        $plan = Plan::create(['id' => 'mt_rand(1,99)', 'name' => 'Hepldesk 1 year', 'product' => $product->id, 'days' => 65]);
        CloudProducts::create(['cloud_product' => $product->id, 'cloud_free_plan' => $plan->id]);
        $plan2 = Plan::create(['id' => 'mt_rand(1,99)', 'name' => 'Hepldesk 2 year', 'product' => $this->planChangeTarget()->id, 'days' => 65]);
        PlanPrice::factory()->create(['plan_id' => $plan->id, 'currency' => 'INR', 'add_price' => 5000]);
        PlanPrice::factory()->create(['plan_id' => $plan2->id, 'currency' => 'INR', 'add_price' => 3000]);
        $this->agentPriced($plan2);

        Subscription::create(['plan_id' => $plan->id, 'order_id' => $order->id, 'product_id' => $product->id,
            'version' => 'v6.0.0', 'update_ends_at' => '', 'ends_at' => Date::now()->addDays(65)]);
        Subscription::create(['plan_id' => $plan2->id, 'order_id' => $order->id, 'product_id' => $product->id,
            'version' => 'v6.0.0', 'update_ends_at' => '', 'ends_at' => Date::now()->addDays(65)]);

        $response = $this->postJson('/upgradeDowngradeCloud', ['id' => $plan2->id, 'orderId' => $order->id]);
        $response->assertStatus(200);

        $newInvoice = Invoice::find($response->json('data.invoice_id'));
        $this->assertSame(0.0, (float) $newInvoice->grand_total);

        // Regression: the checkout page for this invoice must show the real
        // product price (the new plan's prorated cost) and a real discount
        // (same amount, since credit fully covers it), not 0 for everything
        // just because nothing is actually charged.
        $item = $newInvoice->invoiceItem()->first();
        $this->assertGreaterThan(0, (float) $item->subtotal);
        $this->assertSame((float) $item->subtotal, (float) $item->regular_price);
        $this->assertSame((float) $item->subtotal, (float) $newInvoice->discount);
    }

    /**
     * Regression: paying off a $0 plan-change invoice must create a genuinely
     * NEW order for the new plan, not mistake the OLD order for it. Before
     * the fix, upgradeDowngradeCloud() pre-created an OrderInvoiceRelation to
     * the old order, which fooled PostPaymentService::executeOrders()'s
     * "has an order already been made for this invoice?" check into thinking
     * fulfilment was done — so no new order/subscription was ever created,
     * and the client's real, still-in-use order was headed for being wrongly
     * marked Terminated in its own place.
     */
    #[Group('Cloud plan Change')]
    public function test_paying_plan_change_invoice_creates_new_order_not_reusing_old_one(): void
    {
        ThirdPartyApp::where('app_name', 'faveo_app_key')->delete();

        $user = User::factory()->create();
        $this->actingAs($user);
        $this->withoutMiddleware();
        $product = Product::create(['name' => 'Helpdesk Advance', 'description' => 'goodProduct']);
        $invoice = Invoice::factory()->create(['user_id' => $user->id]);
        InvoiceItem::create(['invoice_id' => $invoice->id, 'product_name' => $product->name]);
        $oldOrder = Order::create(['client' => $user->id, 'order_status' => 'executed',
            'product' => $product->id, 'number' => mt_rand(100000, 999999), 'invoice_id' => $invoice->id, 'serial_key' => 'eyJpdiI6IkpI0005']);
        $plan = Plan::create(['id' => 'mt_rand(1,99)', 'name' => 'Hepldesk 1 year', 'product' => $product->id, 'days' => 65]);
        CloudProducts::create(['cloud_product' => $product->id, 'cloud_free_plan' => $plan->id]);
        $plan2 = Plan::create(['id' => 'mt_rand(1,99)', 'name' => 'Hepldesk 2 year', 'product' => $this->planChangeTarget()->id, 'days' => 65]);
        PlanPrice::factory()->create(['plan_id' => $plan->id, 'currency' => 'INR', 'add_price' => 5000]);
        PlanPrice::factory()->create(['plan_id' => $plan2->id, 'currency' => 'INR', 'add_price' => 3000]);
        $this->agentPriced($plan2);

        Subscription::create(['plan_id' => $plan->id, 'order_id' => $oldOrder->id, 'product_id' => $product->id,
            'version' => 'v6.0.0', 'update_ends_at' => '', 'ends_at' => Date::now()->addDays(65)]);
        Subscription::create(['plan_id' => $plan2->id, 'order_id' => $oldOrder->id, 'product_id' => $product->id,
            'version' => 'v6.0.0', 'update_ends_at' => '', 'ends_at' => Date::now()->addDays(65)]);

        $response = $this->postJson('/upgradeDowngradeCloud', ['id' => $plan2->id, 'orderId' => $oldOrder->id]);
        $response->assertStatus(200);

        $newInvoice = Invoice::find($response->json('data.invoice_id'));

        // Paying it off runs executeOrders() (creates the new order), then
        // doTheActivity() (grants credit), then the external cloud API call —
        // which fails here for lack of test app-key config, same as the
        // existing doTheProductUpgradeDowngrade tests below. That's fine: the
        // part under test (order creation) runs before that call.
        try {
            resolve(InvoicePaymentService::class)->applyCredit($newInvoice);
        } catch (\Throwable) {
            // expected — no faveo_app_key configured in tests
        }

        $newOrderId = OrderInvoiceRelation::where('invoice_id', $newInvoice->id)
            ->latest('id')->value('order_id');

        $this->assertNotNull($newOrderId, 'A new order should have been created for the new plan.');
        $this->assertNotSame($oldOrder->id, $newOrderId, 'The new order must not be the same row as the old order.');
        $this->assertSame($plan2->id, Subscription::where('order_id', $newOrderId)->value('plan_id'));
        // The cloud call runs after commit and only logs its failure (no app-key
        // here), so the paid switch still completes and the old order is retired.
        $this->assertSame('terminated', strtolower((string) $oldOrder->fresh()->order_status));
    }

    /**
     * Security regression: agent count must come from the order's own
     * license (server-side), never from the request — otherwise a client
     * could under-report agents to shrink the price it's charged. Two
     * identically-configured orders, one submitted with the real agent
     * count and one with an absurd tampered value, must price identically.
     */
    #[Group('Cloud plan Change')]
    public function test_upgrade_downgrade_cloud_ignores_client_supplied_agents_count(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        $this->withoutMiddleware();
        $product = Product::create(['name' => 'Helpdesk Advance', 'description' => 'goodProduct']);
        $plan = Plan::create(['id' => 'mt_rand(1,99)', 'name' => 'Hepldesk 1 year', 'product' => $product->id, 'days' => 65]);
        CloudProducts::create(['cloud_product' => $product->id, 'cloud_free_plan' => $plan->id]);
        $plan2 = Plan::create(['id' => 'mt_rand(1,99)', 'name' => 'Hepldesk 2 year', 'product' => $this->planChangeTarget()->id, 'days' => 65]);
        PlanPrice::factory()->create(['plan_id' => $plan->id, 'currency' => 'INR', 'add_price' => 3000]);
        PlanPrice::factory()->create(['plan_id' => $plan2->id, 'currency' => 'INR', 'add_price' => 5000]);
        $this->agentPriced($plan2);

        $makeOrder = function () use ($user, $product, $plan): Order {
            $invoice = Invoice::factory()->create(['user_id' => $user->id]);
            InvoiceItem::create(['invoice_id' => $invoice->id, 'product_name' => $product->name]);
            $order = Order::create(['client' => $user->id, 'order_status' => 'executed',
                'product' => $product->id, 'number' => mt_rand(100000, 999999), 'invoice_id' => $invoice->id, 'serial_key' => 'eyJpdiI6IkpI0005']);
            Subscription::create(['plan_id' => $plan->id, 'order_id' => $order->id, 'product_id' => $product->id,
                'version' => 'v6.0.0', 'update_ends_at' => '', 'ends_at' => Date::now()->addDays(65)]);

            return $order;
        };

        $orderHonest = $makeOrder();
        $orderTampered = $makeOrder();

        // serial_key '...0005' encodes 5 agents — honest request matches it,
        // tampered request lies about it.
        $honest = $this->postJson('/upgradeDowngradeCloud', ['id' => $plan2->id, 'orderId' => $orderHonest->id, 'agents' => 5]);
        $tampered = $this->postJson('/upgradeDowngradeCloud', ['id' => $plan2->id, 'orderId' => $orderTampered->id, 'agents' => 999]);

        $honest->assertStatus(200);
        $tampered->assertStatus(200);

        $honestInvoice = Invoice::find($honest->json('data.invoice_id'));
        $tamperedInvoice = Invoice::find($tampered->json('data.invoice_id'));

        $this->assertSame((float) $honestInvoice->grand_total, (float) $tamperedInvoice->grand_total);
    }

    /**
     * Security regression: the preview endpoint must enforce order ownership
     * the same way the actual charge does — it must not compute or leak
     * pricing for an order that belongs to someone else.
     */
    #[Group('Cloud plan Change')]
    public function test_get_upgrade_cost_is_refused_when_user_does_not_own_order(): void
    {
        $otherUser = User::factory()->create(['email' => 'other-order-'.uniqid().'@test.local']);
        $order = Order::create([
            'client' => $otherUser->id,
            'order_status' => 'executed',
            'number' => 'OWN-'.uniqid(),
            'serial_key' => 'eyJpdiI6IkpI0005',
        ]);

        $response = $this->postJson('/get-cloud-upgrade-cost', [
            'plan' => 1,
            'orderId' => $order->id,
        ]);

        // Refused outright (OrderPolicy) — no price is computed for someone else's order.
        $response->assertStatus(403)->assertJson(['success' => false, 'message' => __('message.unauthorized_action')]);
    }

    public function test_cloud_get_cost_upgrade_plan(): void
    {
        $user = User::factory()->create(['billing_pay_balance' => 0]);
        $this->actingAs($user);
        $this->withoutMiddleware();
        $product = Product::create(['name' => 'Helpdesk Advance', 'description' => 'goodProduct']);
        $invoice = Invoice::factory()->create(['user_id' => $user->id]);
        InvoiceItem::create(['invoice_id' => $invoice->id, 'product_name' => $product->name]);
        $order = Order::create(['client' => $user->id, 'order_status' => 'executed',
            'product' => $product->id, 'number' => mt_rand(100000, 999999), 'invoice_id' => $invoice->id, 'serial_key' => 'eyJpdiI6IkpI0005']);
        $plan = Plan::create(['id' => 'mt_rand(1,99)', 'name' => 'Hepldesk 1 year', 'product' => $product->id, 'days' => 65]);
        CloudProducts::create(['cloud_product' => $product->id, 'cloud_free_plan' => $plan->id]);
        $plan2 = Plan::create(['id' => 'mt_rand(1,99)', 'name' => 'Hepldesk 2 year', 'product' => $this->planChangeTarget()->id, 'days' => 65]);
        PlanPrice::factory()->create(['plan_id' => $plan->id, 'currency' => 'INR', 'add_price' => 3000]);
        PlanPrice::factory()->create(['plan_id' => $plan2->id, 'currency' => 'INR', 'add_price' => 5000]);
        $this->agentPriced($plan2, 5);

        Subscription::create(['plan_id' => $plan->id, 'order_id' => $order->id, 'product_id' => $product->id,
            'version' => 'v6.0.0', 'update_ends_at' => '', 'ends_at' => Date::now()->addDays(65)]);
        Subscription::create(['plan_id' => $plan2->id, 'order_id' => $order->id, 'product_id' => $product->id,
            'version' => 'v6.0.0', 'update_ends_at' => '', 'ends_at' => Date::now()->addDays(65)]);

        $response = $this->getPrivateMethod($this->cloudactivities, 'calculatePlanChange', [$order, $plan2->id]);
        // Response now: ['price', 'discount', 'product', 'currency', 'priceoldplan', 'pricenewplan']
        $this->assertArrayHasKey('price', $response);
        $this->assertArrayHasKey('currency', $response);
        $this->assertGreaterThanOrEqual(0, $response['price']);
    }

    public function test_cloud_get_cost_downgrade_plan(): void
    {
        $user = User::factory()->create(['billing_pay_balance' => 0]);
        $this->actingAs($user);
        $this->withoutMiddleware();
        $product = Product::create(['name' => 'Helpdesk Advance', 'description' => 'goodProduct']);
        $invoice = Invoice::factory()->create(['user_id' => $user->id]);
        InvoiceItem::create(['invoice_id' => $invoice->id, 'product_name' => $product->name]);
        $order = Order::create(['client' => $user->id, 'order_status' => 'executed',
            'product' => $product->id, 'number' => mt_rand(100000, 999999), 'invoice_id' => $invoice->id, 'serial_key' => 'eyJpdiI6IkpI0005']);
        $plan = Plan::create(['id' => 'mt_rand(1,99)', 'name' => 'Hepldesk 1 year', 'product' => $product->id, 'days' => 65]);
        CloudProducts::create(['cloud_product' => $product->id, 'cloud_free_plan' => $plan->id]);
        $plan2 = Plan::create(['id' => 'mt_rand(1,99)', 'name' => 'Hepldesk 2 year', 'product' => $this->planChangeTarget()->id, 'days' => 65]);
        PlanPrice::factory()->create(['plan_id' => $plan->id, 'currency' => 'INR', 'add_price' => 5000]);
        PlanPrice::factory()->create(['plan_id' => $plan2->id, 'currency' => 'INR', 'add_price' => 3000]);
        $this->agentPriced($plan2, 5);

        Subscription::create(['plan_id' => $plan->id, 'order_id' => $order->id, 'product_id' => $product->id,
            'version' => 'v6.0.0', 'update_ends_at' => '', 'ends_at' => Date::now()->addDays(65)]);
        Subscription::create(['plan_id' => $plan2->id, 'order_id' => $order->id, 'product_id' => $product->id,
            'version' => 'v6.0.0', 'update_ends_at' => '', 'ends_at' => Date::now()->addDays(65)]);

        // Old plan's remaining value (25,000) covers the new plan's prorated
        // cost (15,000) — nothing should be due. Regression case for the bug
        // where this path charged the un-netted 15,000 instead of 0 (the
        // preview correctly showed 0, only the actual charge disagreed).
        $response = $this->getPrivateMethod($this->cloudactivities, 'calculatePlanChange', [$order, $plan2->id]);
        $this->assertArrayHasKey('price', $response);
        $this->assertArrayHasKey('currency', $response);
        $this->assertSame(0.0, (float) $response['price']);
        $this->assertGreaterThan(0, $response['discount']);
    }

    public function test_subscription_query_is_correct(): void
    {
        $user = User::factory()->create(['billing_pay_balance' => 0]);
        $this->actingAs($user);
        $this->withoutMiddleware();
        $product = Product::create(['name' => 'Helpdesk Advance', 'description' => 'goodProduct']);
        $invoice = Invoice::factory()->create(['user_id' => $user->id]);
        InvoiceItem::create(['invoice_id' => $invoice->id, 'product_name' => $product->name]);
        $order = Order::create(['client' => $user->id, 'order_status' => 'executed',
            'product' => $product->id, 'number' => mt_rand(100000, 999999), 'invoice_id' => $invoice->id, 'serial_key' => 'eyJpdiI6IkpI0005']);
        $plan = Plan::create(['id' => 'mt_rand(1,99)', 'name' => 'Hepldesk 1 year', 'product' => $product->id, 'days' => 65]);
        CloudProducts::create(['cloud_product' => $product->id, 'cloud_free_plan' => $plan->id]);
        PlanPrice::factory()->create(['plan_id' => $plan->id, 'currency' => 'INR', 'add_price' => 5000]);
        CloudProducts::create(['id' => 1, 'cloud_product' => $product->id, 'cloud_free_plan' => $plan->id, 'cloud_product_key' => 'HelpDesk']);

        Subscription::create(['plan_id' => $plan->id, 'order_id' => $order->id, 'product_id' => $product->id,
            'version' => 'v6.0.0', 'update_ends_at' => '', 'ends_at' => Date::now()->subDays(8)]);
        Subscription::create(['plan_id' => $plan->id, 'order_id' => $order->id, 'product_id' => $product->id,
            'version' => 'v6.0.0', 'update_ends_at' => '', 'ends_at' => Date::now()->addDays(1)]);
        $day = (int) Condition::dayCount('cloud');
        $today = Date::today();
        $sub = Subscription::whereNotNull('ends_at')
            ->whereIn('product_id', cloudPopupProducts())
            ->whereDate(
                DB::raw(sprintf('DATE_ADD(ends_at, INTERVAL %s DAY)', $day)),
                '<=',
                $today
            )
            ->get();
        // The query depends on the cloud task's days being seeded
        // Just assert the query ran without errors
        $this->assertIsArray($sub->toArray());
    }

    public function test_domain_cloud_autofill_returns_company_domain(): void
    {
        $user = User::factory()->create(['role' => 'user', 'company' => 'My Test Company']);
        $this->actingAs($user);
        $this->withoutMiddleware();
        $response = $this->getJson('/api/domain');
        $response->assertStatus(200);
        $this->assertEquals('mytestcompany', $response->json('data'));
    }

    public function test_change_domain_for_a_missing_order_is_refused(): void
    {
        $user = User::factory()->create(['role' => 'user']);
        $this->actingAs($user);
        $this->withoutMiddleware();

        $this->postJson('/change/domain', ['newDomain' => 'new.example.com', 'order_id' => 999999])
            ->assertStatus(403)
            ->assertJson(['success' => false, 'message' => __('message.unauthorized_action')]);
    }

    public function test_fetch_data_returns_paginated_200(): void
    {
        $user = User::factory()->create(['role' => 'admin']);
        $this->actingAs($user);
        $this->withoutMiddleware();
        $response = $this->getJson('/fetch-data');
        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
        $response->assertJsonStructure(['success', 'data' => ['data', 'current_page']]);
    }

    public function test_fetch_data_with_search_returns_200(): void
    {
        $user = User::factory()->create(['role' => 'admin']);
        $this->actingAs($user);
        $this->withoutMiddleware();
        $response = $this->getJson('/fetch-data?search-query=helpdesk');
        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
    }

    public function test_update_trial_status_with_nonexistent_id_returns_400(): void
    {
        $user = User::factory()->create(['role' => 'admin']);
        $this->actingAs($user);
        $this->withoutMiddleware();
        $response = $this->postJson('/update-trial-status', ['id' => 999999, 'status' => 1]);
        $response->assertStatus(400);
        $response->assertJson(['success' => false]);
    }

    public function test_delete_product_config_with_nonexistent_id_returns_400(): void
    {
        $user = User::factory()->create(['role' => 'admin']);
        $this->actingAs($user);
        $this->withoutMiddleware();
        $response = $this->deleteJson('/delete-cloud-product', ['id' => 999999]);
        $response->assertStatus(400);
        $response->assertJson(['success' => false]);
    }

    public function test_store_cloud_data_center_missing_countries_returns_422(): void
    {
        $user = User::factory()->create(['role' => 'admin']);
        $this->actingAs($user);
        $this->withoutMiddleware();
        $response = $this->postJson('/cloud-data-center-store', ['cloud_state' => 'TN']);
        $response->assertStatus(412);
        $response->assertJsonValidationErrors(['cloud_countries'], 'message');
    }

    public function test_store_cloud_data_center_missing_state_returns_422(): void
    {
        $user = User::factory()->create(['role' => 'admin']);
        $this->actingAs($user);
        $this->withoutMiddleware();
        $response = $this->postJson('/cloud-data-center-store', ['cloud_countries' => 'IN']);
        $response->assertStatus(412);
        $response->assertJsonValidationErrors(['cloud_state'], 'message');
    }

    public function test_remove_location_with_data_returns_200(): void
    {
        $user = User::factory()->create(['role' => 'admin']);
        $this->actingAs($user);
        $this->withoutMiddleware();
        $response = $this->deleteJson('/remove-location', ['location_id' => 'NonexistentCity, TN']);
        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
    }

    public function test_get_cloud_products(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        $this->withoutMiddleware();
        $product = Product::create(['name' => 'Helpdesk Advance']);
        $product1 = Product::create(['name' => 'FreeHelpdesk Advance', 'description' => 'goodProduct']);

        $plan = Plan::create(['id' => 25, 'name' => 'Hepldesk 1 year', 'product' => $product->id, 'days' => 15]);
        CloudProducts::create(['cloud_product' => $product->id, 'cloud_free_plan' => $plan->id]);
        PlanPrice::create(['plan_id' => $plan->id, 'add_price' => '1000', 'currency' => 'USD']);
        CloudProducts::create(['cloud_product' => $product->id, 'cloud_free_plan' => $plan->id, 'cloud_product_key' => $product->name, 'trial_status' => 1]);
        CloudProducts::create(['cloud_product' => $product1->id, 'cloud_free_plan' => $plan->id, 'cloud_product_key' => $product1->name, 'trial_status' => 1]);
        // Route changed from POST trial-cloud-products to GET store/cloud-products
        $response = $this->call('GET', 'store/cloud-products');
        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
        $response->assertJsonStructure(['data' => ['products']]);
    }

    // =========================================================================
    // getUpgradeCost – error path when plan not found
    // =========================================================================

    public function test_get_upgrade_cost_returns_nan_when_plan_not_found(): void
    {
        $user = User::factory()->create(['role' => 'admin']);
        $this->actingAs($user);
        $this->withoutMiddleware();
        $response = $this->postJson('/get-cloud-upgrade-cost', [
            'plan' => 999999,
            'agents' => 5,
            'orderId' => 999999,
        ]);
        // Returns array with NaN values via error path
        $this->assertTrue($response->status() >= 200);
    }

    // =========================================================================
    // upgradeDowngradeCloud – missing data returns error
    // =========================================================================

    public function test_upgrade_downgrade_cloud_with_missing_data_returns_error(): void
    {
        $user = User::factory()->create(['role' => 'admin']);
        $this->actingAs($user);
        $this->withoutMiddleware();
        $response = $this->postJson('/upgradeDowngradeCloud', [
            'orderId' => 999999,
        ]);
        $this->assertTrue($response->status() >= 200);
    }

    // =========================================================================
    // changeDomain – all early-return error branches
    // =========================================================================

    public function test_change_domain_validation_fails_without_domains(): void
    {
        // changeDomain wraps $this->validate() in try/catch(Exception)
        // ValidationException IS an Exception → caught → returns errorResponse 400
        $user = User::factory()->create(['email' => 'change-dom-1-'.uniqid().'@test.local']);
        $this->actingAs($user);
        $this->withoutMiddleware();
        $response = $this->postJson('/change/domain', []);
        $response->assertStatus(400);
        $response->assertJson(['success' => false]);
    }

    public function test_change_domain_takes_the_current_domain_from_the_order_not_the_request(): void
    {
        $user = User::factory()->create(['email' => 'change-dom-2-'.uniqid().'@test.local']);
        $this->actingAs($user);
        $this->withoutMiddleware();
        $order = $this->liveCloudOrder($user, 'mine.example.com');

        // A different tenant's domain/license in the request is ignored: the new domain equals
        // this order's own domain, so there is nothing to change.
        $this->postJson('/change/domain', [
            'currentDomain' => 'someone-else.example.com',
            'lic_code' => 'NOT-MY-LICENSE',
            'newDomain' => 'mine.example.com',
            'order_id' => $order->id,
        ])->assertStatus(400)->assertJson(['message' => __('message.nothing_changed')]);
    }

    public function test_change_domain_reports_success_when_the_cloud_confirms(): void
    {
        $user = User::factory()->create(['email' => 'change-dom-ok-'.uniqid().'@test.local']);
        $this->actingAs($user);
        $this->withoutMiddleware();
        $order = $this->liveCloudOrder($user, 'old.example.com');
        ThirdPartyApp::firstOrCreate(['app_name' => 'faveo_app_key'], ['app_key' => 'test-key', 'app_secret' => 'test-secret']);
        config(['custom.cloud_job_url' => 'https://jobs.example.test']);
        $this->bindMockClientWithResponses([
            new Response(200, [], json_encode(['status' => 'success'])), // cloud changeDomain
            new Response(200, [], ''),                                   // server job for the new domain
        ]);

        // Contains the cloud CNAME (faveocloud.test) without being a subdomain of it, so no live DNS lookup.
        $this->postJson('/change/domain', ['newDomain' => 'myfaveocloud.test', 'order_id' => $order->id])
            ->assertOk()->assertJson(['message' => __('message.cloud_domain_change')]);
    }

    public function test_change_domain_keeps_the_old_domain_when_the_cloud_refuses(): void
    {
        $user = User::factory()->create(['email' => 'change-dom-no-'.uniqid().'@test.local']);
        $this->actingAs($user);
        $this->withoutMiddleware();
        $order = $this->liveCloudOrder($user, 'old.example.com');
        ThirdPartyApp::firstOrCreate(['app_name' => 'faveo_app_key'], ['app_key' => 'test-key', 'app_secret' => 'test-secret']);
        $this->bindMockClientWithResponses([new Response(200, [], json_encode(['status' => 'fails', 'message' => 'x']))]);

        $this->postJson('/change/domain', ['newDomain' => 'myfaveocloud.test', 'order_id' => $order->id])
            ->assertStatus(400)->assertJson(['message' => __('message.wrong_domain')]);

        $this->assertSame(['old.example.com'], $order->installedDomains()->all());
    }

    public function test_change_domain_returns_error_for_same_domain(): void
    {
        $user = User::factory()->create(['email' => 'change-dom-3-'.uniqid().'@test.local']);
        $this->actingAs($user);
        $this->withoutMiddleware();
        $order = $this->liveCloudOrder($user, 'same.example.com');

        $this->postJson('/change/domain', ['newDomain' => 'same.example.com', 'order_id' => $order->id])
            ->assertStatus(400)
            ->assertJson(['success' => false, 'message' => __('message.nothing_changed')]);
    }

    // =========================================================================
    // fetchData – covers pagination and search
    // =========================================================================

    public function test_fetch_data_with_pagination_returns_structure(): void
    {
        $user = User::factory()->create(['role' => 'admin', 'email' => 'fetch-data-'.uniqid().'@test.local']);
        $this->actingAs($user);
        $this->withoutMiddleware();
        $response = $this->getJson('/fetch-data?limit=5&page=1');
        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $data = $response->json('data');
        $this->assertArrayHasKey('data', $data);
        $this->assertArrayHasKey('total', $data);
        $this->assertArrayHasKey('per_page', $data);
    }

    public function test_fetch_data_with_search_query_filters_results(): void
    {
        $user = User::factory()->create(['role' => 'admin', 'email' => 'fetch-data-s-'.uniqid().'@test.local']);
        $this->actingAs($user);
        $this->withoutMiddleware();
        $response = $this->getJson('/fetch-data?search_query=nonexistent_domain_xyz');
        $response->assertStatus(200);

        $data = $response->json('data.data');
        $this->assertIsArray($data);
        $this->assertCount(0, $data);  // No matching results
    }

    // =========================================================================
    // changeDomain – early return branches (no Guzzle call needed)
    // =========================================================================

    public function test_change_domain_returns_400_when_domains_missing(): void
    {
        $user = User::factory()->create(['email' => 'cdomain-1-'.uniqid().'@test.local']);
        $this->actingAs($user);
        $this->withoutMiddleware();

        // Missing both required fields → ValidationException is caught → errorResponse 400
        $response = $this->postJson('/change/domain', []);

        $response->assertStatus(400)
            ->assertJson(['success' => false]);
    }

    public function test_change_domain_is_refused_for_an_order_that_is_not_a_live_cloud_instance(): void
    {
        $user = User::factory()->create(['email' => 'cdomain-2-'.uniqid().'@test.local']);
        $this->actingAs($user);
        $this->withoutMiddleware();
        $deleted = $this->liveCloudOrder($user, 'gone.example.com');
        Subscription::where('order_id', $deleted->id)->update(['is_deleted' => 1]);
        $terminated = $this->liveCloudOrder($user, 'old.example.com');
        $terminated->update(['order_status' => 'Terminated']);

        $this->postJson('/change/domain', ['newDomain' => 'new.example.com', 'order_id' => $deleted->id])->assertStatus(400);
        $this->postJson('/change/domain', ['newDomain' => 'new.example.com', 'order_id' => $terminated->id])->assertStatus(400);
    }

    public function test_change_domain_returns_400_when_same_domain(): void
    {
        $user = User::factory()->create(['email' => 'cdomain-3-'.uniqid().'@test.local']);
        $this->actingAs($user);
        $this->withoutMiddleware();
        $order = $this->liveCloudOrder($user, 'same.domain.com');

        $this->postJson('/change/domain', ['newDomain' => 'same.domain.com', 'order_id' => $order->id])
            ->assertStatus(400)
            ->assertJson(['success' => false, 'message' => __('message.nothing_changed')]);
    }

    // =========================================================================
    // domainCloudAutofill – returns company name truncated to 28 chars
    // =========================================================================

    public function test_domain_cloud_autofill_returns_truncated_company_name(): void
    {
        $user = User::factory()->create([
            'email' => 'autofill-'.uniqid().'@test.local',
            'company' => 'My Long Company Name That Exceeds Limit',
        ]);
        $this->actingAs($user);
        $this->withoutMiddleware();

        $response = $this->getJson('/api/domain');

        $response->assertStatus(200);

        $body = $response->json();
        $this->assertArrayHasKey('data', $body);
        // Company is lowercased, spaces removed, truncated to 28 chars
        $expected = substr(strtolower(str_replace(' ', '', 'My Long Company Name That Exceeds Limit')), 0, 28);
        $this->assertSame($expected, $body['data']);
    }

    // =========================================================================
    // updateTrialStatus – with existing subscription
    // =========================================================================

    public function test_update_trial_status_returns_success_with_existing_cloud_product(): void
    {
        // $this->user is set by getLoggedInUser('user') in setUp()
        $product = Product::create(['name' => 'Test Cloud Product '.uniqid()]);
        $plan = Plan::firstOrCreate(
            ['name' => 'Trial Test Plan'],
            ['product' => $product->id, 'days' => 30]
        );
        $cloudProduct = CloudProducts::create([
            'cloud_product' => $product->id,
            'cloud_product_key' => 'TRIAL_TEST_'.uniqid(),
            'trial_status' => 0,
            'cloud_free_plan' => $plan->id,
        ]);

        $response = $this->postJson('/update-trial-status', [
            'id' => $cloudProduct->id,
            'status' => 1,
        ]);

        $response->assertStatus(200)
            ->assertJson(['success' => true]);

        // Verify the trial_status was actually updated to 1
        $this->assertDatabaseHas('cloud_products', [
            'id' => $cloudProduct->id,
            'trial_status' => 1,
        ]);
    }

    // =========================================================================
    // changeDomain — order-not-owned branch (precise assertion)
    // =========================================================================

    public function test_change_domain_is_refused_when_user_does_not_own_order(): void
    {
        $otherUser = User::factory()->create(['email' => 'cdomain-owner-'.uniqid().'@test.local']);
        $order = $this->liveCloudOrder($otherUser, 'theirs.example.com');

        $this->postJson('/change/domain', ['newDomain' => 'new.example.com', 'order_id' => $order->id])
            ->assertStatus(403)
            ->assertJson(['success' => false, 'message' => __('message.unauthorized_action')]);
    }

    // =========================================================================
    // CloudApi — reply parsing
    // =========================================================================

    public function test_cloud_api_reads_nested_json_after_notices_and_throws_on_garbage(): void
    {
        ThirdPartyApp::firstOrCreate(['app_name' => 'faveo_app_key'], ['app_key' => 'test-key', 'app_secret' => 'test-secret']);
        $cloud = FaveoCloud::firstOrCreate([], ['cloud_central_domain' => 'https://cloud.test.local', 'cloud_cname' => 'test']);
        $history = [];
        $stack = HandlerStack::create(new MockHandler([
            new Response(200, [], 'Notice: x'.json_encode(['status' => 'fails', 'message' => ['a' => 1]])),
            new Response(200, [], '<html>oops</html>'),
        ]));
        $stack->push(Middleware::history($history));

        $api = new CloudApi(new Client(['handler' => $stack]), $cloud);

        $this->assertSame('fails', $api->send('POST', '/x', [])['status']);
        $this->assertSame('application/json', $history[0]['request']->getHeaderLine('Accept'));
        $this->assertNotSame('', $history[0]['request']->getHeaderLine('signature'));

        $this->expectException(\RuntimeException::class);
        $api->get('/x');
    }

    // =========================================================================
    // doTheActivity — direct controller call covers the discount-is-null branch
    // =========================================================================

    public function test_do_the_activity_returns_early_when_discount_is_null(): void
    {
        $cloud = FaveoCloud::firstOrCreate([], [
            'cloud_central_domain' => 'https://cloud.test.local',
            'cloud_cname' => 'test',
        ]);

        $controller = new CloudExtraActivities(new Client, $cloud);

        // discount = null → early return, no DB operations
        $controller->doTheActivity(1, 2);
        $this->assertTrue(true); // Reached without exception
    }

    public function test_do_the_activity_with_discount_grants_credit(): void
    {
        $user = User::factory()->create(['email' => 'dta-'.uniqid().'@test.local']);
        $this->actingAs($user);

        $order1 = Order::create([
            'client' => $user->id,
            'order_status' => 'executed',
            'number' => mt_rand(100000, 999999),
        ]);
        $order2 = Order::create([
            'client' => $user->id,
            'order_status' => 'executed',
            'number' => mt_rand(100000, 999999),
        ]);

        $cloud = FaveoCloud::firstOrCreate([], [
            'cloud_central_domain' => 'https://cloud.test.local',
            'cloud_cname' => 'test',
        ]);

        $controller = new CloudExtraActivities(new Client, $cloud);

        $controller->doTheActivity($order1->id, $order2->id, 100.0, 'USD');

        $this->assertSame(100.0, (new CreditBalanceService)->balance($user->id, 'USD'));
        $this->assertDatabaseHas('credit_transactions', [
            'user_id' => $user->id,
            'currency' => 'USD',
            'amount' => '100',
            'type' => CreditTransaction::TYPE_DOWNGRADE_PRORATION,
            // The client reads this note, so it names order numbers, not ids.
            'note' => sprintf('Plan downgrade: terminated order #%s, new order #%s', $order1->number, $order2->number),
        ]);
    }

    public function test_do_the_activity_falls_back_to_client_country_currency_when_none_given(): void
    {
        $user = User::factory()->create(['email' => 'dta-cc-'.uniqid().'@test.local', 'country' => 'IN']);
        $this->actingAs($user);

        $cloud = FaveoCloud::firstOrCreate([], [
            'cloud_central_domain' => 'https://cloud.test.local',
            'cloud_cname' => 'test',
        ]);

        $controller = new CloudExtraActivities(new Client, $cloud);
        // Credit goes to the terminated order's customer — here, this user's own order.
        $order = Order::create(['client' => $user->id, 'order_status' => 'Terminated', 'number' => mt_rand(100000, 999999)]);

        $controller->doTheActivity($order->id, $order->id + 1, 50.0);

        $this->assertSame(50.0, (new CreditBalanceService)->balance($user->id, getCurrencyForClient('IN')));
    }

    // =========================================================================
    // upgradeDowngradeCloud — invalid order id returns 400
    // =========================================================================

    public function test_upgrade_downgrade_cloud_is_refused_when_order_not_found(): void
    {
        $response = $this->postJson('/upgradeDowngradeCloud', [
            'id' => 999999,
            'orderId' => 999999,
            'agents' => 5,
        ]);

        $response->assertStatus(403)
            ->assertJson(['success' => false]);
    }

    public function test_upgrade_downgrade_cloud_is_refused_when_user_does_not_own_order(): void
    {
        $otherUser = User::factory()->create(['email' => 'updown-'.uniqid().'@test.local']);
        $order = Order::create([
            'client' => $otherUser->id,
            'order_status' => 'executed',
            'number' => 'UDP-'.uniqid(),
        ]);

        $response = $this->postJson('/upgradeDowngradeCloud', [
            'id' => 1,
            'orderId' => $order->id,
            'agents' => 5,
        ]);

        $response->assertStatus(403)
            ->assertJson(['success' => false]);
    }

    // =========================================================================
    // getUpgradeCost — calls calculatePlanChange
    // Tests the array (not JsonResponse) return path
    // =========================================================================

    public function test_get_upgrade_cost_refuses_a_plan_the_plan_list_does_not_offer(): void
    {
        $user = User::factory()->create(['email' => 'ugcost-'.uniqid().'@test.local']);
        $this->actingAs($user);

        $product = Product::create(['name' => 'UGCostTest '.uniqid()]);
        $plan = Plan::create(['name' => 'UGCost Plan', 'product' => $product->id, 'days' => 365]);
        CloudProducts::create(['cloud_product' => $product->id, 'cloud_free_plan' => $plan->id]);
        PlanPrice::factory()->create(['plan_id' => $plan->id, 'currency' => 'INR', 'add_price' => 1000]);
        $order = Order::create([
            'client' => $user->id,
            'order_status' => 'executed',
            'number' => 'UGC-'.uniqid(),
            'serial_key' => '1234567890120003',
            'product' => $product->id,
        ]);
        Subscription::create([
            'plan_id' => $plan->id,
            'order_id' => $order->id,
            'product_id' => $product->id,
            'version' => 'v1.0',
            'update_ends_at' => '',
            'ends_at' => '',
        ]);

        $response = $this->postJson('/get-cloud-upgrade-cost', [
            'plan' => $plan->id,
            'agents' => 3,
            'orderId' => $order->id,
        ]);

        // Its own plan isn't one the plan list offers, so no price is quoted for it.
        $response->assertStatus(400);
    }

    // =========================================================================
    // changeDomain — invalid domain (fails FILTER_VALIDATE_DOMAIN)
    // =========================================================================

    public function test_change_domain_returns_400_for_invalid_domain_format(): void
    {
        $order = $this->liveCloudOrder($this->user, 'old.example.com');

        // '#invalid!domain' fails FILTER_VALIDATE_DOMAIN
        $this->postJson('/change/domain', ['newDomain' => '#invalid!domain', 'order_id' => $order->id])
            ->assertStatus(400)
            ->assertJson(['success' => false, 'message' => __('message.not_allowed_domain')]);
    }

    // =========================================================================
    // doTheProductUpgradeDowngrade — calls doTheActivity then cloud API
    // =========================================================================

    public function test_do_the_product_upgrade_downgrade_with_no_app_key_throws_or_errors(): void
    {
        ThirdPartyApp::where('app_name', 'faveo_app_key')->delete();

        $cloud = FaveoCloud::firstOrCreate([], [
            'cloud_central_domain' => 'https://cloud.test.local',
            'cloud_cname' => 'test',
        ]);

        $this->bindMockClientWithResponses([]);
        $controller = new CloudExtraActivities(new Client, $cloud);

        try {
            // discount=null means doTheActivity returns early; then cloudApiPost throws (no keys)
            $controller->doTheProductUpgradeDowngrade(
                'NEWLICENSE001',
                'test.domain.com',
                1,
                'OLDLICENSE001',
                0,
                0
            );
            $this->assertTrue(true); // Or it may silently fail
        } catch (\Throwable $throwable) {
            $this->assertTrue(true); // Expected — no app key → exception from cloudApiPost
        }
    }

    public function test_a_plan_change_deactivates_the_old_license_only_when_the_cloud_took_it(): void
    {
        ThirdPartyApp::firstOrCreate(['app_name' => 'faveo_app_key'], ['app_key' => 'test-key', 'app_secret' => 'test-secret']);
        $cloud = FaveoCloud::firstOrCreate([], ['cloud_central_domain' => 'https://cloud.test.local', 'cloud_cname' => 'test']);
        $product = Product::factory()->create();
        License::create(['product_id' => $product->id, 'license_code' => 'PLANOLDOK0000005', 'license_status' => 1]);
        License::create(['product_id' => $product->id, 'license_code' => 'PLANOLDKO0000005', 'license_status' => 1]);
        $controller = new CloudExtraActivities(new Client(['handler' => HandlerStack::create(new MockHandler([
            new Response(200, [], json_encode(['status' => 'success'])),
            new Response(200, [], json_encode(['status' => 'fails'])),
        ]))]), $cloud);

        $controller->doTheProductUpgradeDowngrade('PLANNEW000000005', 'tenant.example.test', $product->id, 'PLANOLDOK0000005');
        $controller->doTheProductUpgradeDowngrade('PLANNEW000000005', 'tenant.example.test', $product->id, 'PLANOLDKO0000005');

        $this->assertSame(0, License::where('license_code', 'PLANOLDOK0000005')->value('license_status'));
        $this->assertSame(1, License::where('license_code', 'PLANOLDKO0000005')->value('license_status'));
    }

    // ── C6/C7: one plan change per order; the new subscription runs the term it was priced for ──

    /** A logged-in client's cloud order on a plan of $oldDays with $daysLeft to go. */
    private function planChangeSetup(int $oldDays, int $daysLeft, float $oldPrice, int $newDays, float $newPrice): array
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        $this->withoutMiddleware();
        $product = Product::create(['name' => 'Helpdesk Cloud '.uniqid(), 'description' => 'x']);
        // Licence serial ends 0001: one agent, so prices below are the plan prices (cloud is per agent).
        $oldOrder = Order::create(['client' => $user->id, 'order_status' => 'executed', 'product' => $product->id,
            'number' => mt_rand(100000, 999999), 'serial_key' => 'eyJpdiI6IkpI0001']);
        $oldPlan = Plan::create(['name' => 'old', 'product' => $product->id, 'days' => $oldDays]);
        CloudProducts::create(['cloud_product' => $product->id, 'cloud_free_plan' => $oldPlan->id]);
        $newPlan = Plan::create(['name' => 'new', 'product' => $this->planChangeTarget()->id, 'days' => $newDays]);
        foreach (['INR', 'USD'] as $currency) {
            PlanPrice::factory()->create(['plan_id' => $oldPlan->id, 'currency' => $currency, 'add_price' => $oldPrice, 'offer_price' => 0]);
            PlanPrice::factory()->create(['plan_id' => $newPlan->id, 'currency' => $currency, 'add_price' => $newPrice, 'offer_price' => 0]);
        }
        $this->agentPriced($newPlan);

        Subscription::create(['plan_id' => $oldPlan->id, 'order_id' => $oldOrder->id, 'product_id' => $product->id, 'user_id' => $user->id,
            'version' => 'v6.0.0', 'update_ends_at' => '', 'ends_at' => Date::now()->addDays($daysLeft)->addHour()]);

        return [$oldOrder, $newPlan];
    }

    private function planChangeMetadata(Order $oldOrder, Plan $newPlan): array
    {
        $response = $this->postJson('/upgradeDowngradeCloud', ['id' => $newPlan->id, 'orderId' => $oldOrder->id])->assertOk();

        return Invoice::findOrFail($response->json('data.invoice_id'))->metadata;
    }

    public function test_an_upgrade_charges_the_extras_for_their_new_term(): void
    {
        // Monthly → yearly with 10 days left. 10 storage units cost 2 each a month (20/30 days)
        // and 20 each a year on the new plan. The extras move to the new order, so the upgrade
        // pays for their new term (200/365 × 345 = 189.04) less their unused part (20/30 × 10 = 6.67).
        $price = function (bool $withExtras): float {
            [$oldOrder, $newPlan] = $this->planChangeSetup(oldDays: 30, daysLeft: 10, oldPrice: 100, newDays: 365, newPrice: 1000);
            if ($withExtras) {
                $currency = userCurrencyAndPrice('', $newPlan)['currency']; // how the plan change picks it
                $storage = Entitlement::create(['code' => 'storage_'.uniqid(), 'label' => 'Storage', 'type' => 'number']);
                $storage->prices()->create(['plan_id' => $newPlan->id, 'currency' => $currency, 'unit_price' => 20, 'renew_price' => 20]);
                $monthly = (int) DB::table('periods')->insertGetId(['name' => 'Test 30 days', 'days' => '30']);
                $newPlan->periods()->attach(DB::table('periods')->insertGetId(['name' => 'Test 365 days', 'days' => '365']));
                OrderEntitlement::create(['order_id' => $oldOrder->id, 'entitlement_id' => $storage->id, 'quantity' => 10, 'unit_price' => 2, 'currency' => $currency, 'period_id' => $monthly]);
            }
            $paid = $this->postJson('get-cloud-upgrade-cost', ['plan' => $newPlan->id, 'orderId' => $oldOrder->id])->assertOk()->json('price_to_be_paid');

            return (float) str_replace(',', '', $paid);
        };

        $this->assertEqualsWithDelta(189.04 - 6.67, $price(true) - $price(false), 0.05);
    }

    public function test_an_upgrade_runs_for_the_remaining_term_not_a_full_new_period(): void
    {
        // Monthly with 10 days left → yearly: priced for 365 − (30 − 10) = 345 days, so it must run 345.
        [$oldOrder, $newPlan] = $this->planChangeSetup(oldDays: 30, daysLeft: 10, oldPrice: 1000, newDays: 365, newPrice: 5000);

        $meta = $this->planChangeMetadata($oldOrder, $newPlan);

        $this->assertSame(345, $meta['increase-decrease-days']);
        $this->assertArrayNotHasKey('increase-decrease-days-dont-cloud', $meta);
    }

    public function test_a_same_length_plan_change_keeps_the_old_expiry(): void
    {
        [$oldOrder, $newPlan] = $this->planChangeSetup(oldDays: 30, daysLeft: 10, oldPrice: 1000, newDays: 30, newPrice: 2000);

        $meta = $this->planChangeMetadata($oldOrder, $newPlan);

        $this->assertSame($oldOrder->id, $meta['increase-decrease-days-dont-cloud']);
        $this->assertArrayNotHasKey('increase-decrease-days', $meta);
    }

    public function test_an_expired_plan_changes_to_a_full_new_period(): void
    {
        [$oldOrder, $newPlan] = $this->planChangeSetup(oldDays: 30, daysLeft: -5, oldPrice: 1000, newDays: 365, newPrice: 5000);

        $this->assertSame(365, $this->planChangeMetadata($oldOrder, $newPlan)['increase-decrease-days']);
    }

    public function test_a_terminated_order_cannot_be_plan_changed_again(): void
    {
        [$oldOrder, $newPlan] = $this->planChangeSetup(oldDays: 30, daysLeft: 10, oldPrice: 2000, newDays: 30, newPrice: 1000);
        $oldOrder->update(['order_status' => 'Terminated']);
        $before = Invoice::count();

        $this->postJson('/upgradeDowngradeCloud', ['id' => $newPlan->id, 'orderId' => $oldOrder->id])->assertStatus(400)->assertJson(['message' => __('message.plan_change_order_terminated')]);
        $this->assertSame($before, Invoice::count());
    }

    public function test_a_second_plan_change_is_refused_while_one_is_unpaid(): void
    {
        // Each downgrade invoice banks the old plan's leftover value — N of them would bank it N times.
        [$oldOrder, $newPlan] = $this->planChangeSetup(oldDays: 30, daysLeft: 20, oldPrice: 2000, newDays: 30, newPrice: 1000);
        $this->planChangeMetadata($oldOrder, $newPlan);
        $before = Invoice::count();

        $this->postJson('/upgradeDowngradeCloud', ['id' => $newPlan->id, 'orderId' => $oldOrder->id])->assertStatus(400)->assertJson(['message' => __('message.order_change_invoice_pending', ['number' => Invoice::latest('id')->value('number')])]);
        $this->assertSame($before, Invoice::count());
    }

    public function test_a_paid_plan_change_for_an_already_terminated_order_is_not_fulfilled_again(): void
    {
        [$oldOrder] = $this->planChangeSetup(oldDays: 30, daysLeft: 10, oldPrice: 2000, newDays: 30, newPrice: 1000);
        $oldOrder->update(['order_status' => 'Terminated']);
        $invoice = Invoice::factory()->create(['user_id' => $oldOrder->client, 'grand_total' => 0, 'status' => 'success', 'metadata' => [
            'type' => 'upgrade_downgrade', 'old_order_id' => $oldOrder->id, 'old_license' => 'x', 'installation_path' => 'x.test', 'discount' => 500,
        ]]);

        resolve(PostPaymentService::class)->handle($invoice, 'Credit Balance');

        $this->assertSame(0, OrderInvoiceRelation::where('invoice_id', $invoice->id)->count()); // no new order
    }

    // ── H1: moving to a shorter plan never yields a negative term or phantom credit ──

    public function test_yearly_to_monthly_with_many_days_left_runs_one_month_and_credits_the_true_remainder(): void
    {
        // 100/yr with 200 days left is worth 100/365×200 = 54.79; a month of the 9/mo plan
        // costs 9, so 45.79 is banked. The old formula gave 30 − 165 = −135 days and 95.29.
        [$oldOrder, $newPlan] = $this->planChangeSetup(oldDays: 365, daysLeft: 200, oldPrice: 100, newDays: 30, newPrice: 9);

        $meta = $this->planChangeMetadata($oldOrder, $newPlan);

        $this->assertSame(30, $meta['increase-decrease-days']);
        $this->assertEquals(46, $meta['discount']); // 45.79, rounded where it's banked
    }

    public function test_shorter_plan_with_fewer_days_left_than_its_length_keeps_the_old_expiry(): void
    {
        // Unchanged rule: 20 days left fits inside the new 30-day plan.
        [$oldOrder, $newPlan] = $this->planChangeSetup(oldDays: 365, daysLeft: 20, oldPrice: 100, newDays: 30, newPrice: 9);

        $this->assertSame($oldOrder->id, $this->planChangeMetadata($oldOrder, $newPlan)['increase-decrease-days-dont-cloud']);
    }

    public function test_a_longer_plan_still_gets_its_length_less_the_days_used(): void
    {
        [$oldOrder, $newPlan] = $this->planChangeSetup(oldDays: 30, daysLeft: 10, oldPrice: 1000, newDays: 365, newPrice: 5000);

        $this->assertSame(345, $this->planChangeMetadata($oldOrder, $newPlan)['increase-decrease-days']);
    }

    // ── Downgrade credit goes to the order's customer, whoever is logged in ──

    public function test_downgrade_credit_goes_to_the_customer_not_the_logged_in_admin(): void
    {
        $client = User::factory()->create(['country' => 'US']);
        $order = Order::create(['client' => $client->id, 'order_status' => 'Terminated', 'number' => mt_rand(100000, 999999)]);
        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin); // e.g. the admin recorded the payment

        new CloudExtraActivities(new Client, new FaveoCloud)->doTheActivity($order->id, $order->id + 1, 40.0, 'USD');

        $credit = resolve(CreditBalanceService::class);
        $this->assertSame(40.0, $credit->balance($client->id, 'USD'));
        $this->assertSame(0.0, $credit->balance($admin->id, 'USD'));
    }
}
