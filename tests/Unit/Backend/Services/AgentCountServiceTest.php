<?php

namespace Tests\Unit\Backend\Services;

use App\License\Models\License;
use App\License\Services\LicenseService;
use App\Model\Common\FaveoCloud;
use App\Model\Order\Order;
use App\Model\Order\OrderEntitlement;
use App\Model\Payment\Plan;
use App\Model\Product\CloudProducts;
use App\Model\Product\Entitlement;
use App\Model\Product\Product;
use App\Model\Product\Subscription;
use App\Services\AgentCountService;
use App\Services\EntitlementSalesService;
use App\Services\EntitlementService;
use App\ThirdPartyApp;
use App\User;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Mockery;
use Tests\DBTestCase;

class AgentCountServiceTest extends DBTestCase
{
    private Product $product;

    private Entitlement $agents;

    private Order $order;

    private string $currency = 'USD';

    protected function setUp(): void
    {
        parent::setUp();
        $this->agents = EntitlementService::agents();
        $this->product = Product::factory()->create();
        $plan = $this->plan('5', withPrice: true);
        $user = User::factory()->create();
        $this->order = Order::factory()->create([
            'product' => $this->product->id, 'client' => $user->id, 'order_status' => 'executed',
            'serial_key' => Crypt::encrypt('ABCDEFGHIJKL0005'),
        ]);
        Subscription::factory()->create([
            'order_id' => $this->order->id, 'plan_id' => $plan->id, 'product_id' => $this->product->id,
            'user_id' => $user->id, 'ends_at' => now()->addDays(15)->addHour(),
        ]);
    }

    /** A plan whose Agents row is $included ('N', '0', null = unlimited); false = flat, no row. */
    private function plan(string|null|false $included, bool $withPrice = false): Plan
    {
        $plan = Plan::factory()->create(['product' => $this->product->id, 'days' => 30]);
        if ($included !== false) {
            $plan->entitlements()->attach($this->agents->id, ['value' => $included]);
        }
        if ($withPrice) {
            $this->agents->prices()->create(['plan_id' => $plan->id, 'currency' => $this->currency, 'unit_price' => 3]);
        }

        return $plan;
    }

    private function onPlan(Plan $plan): void
    {
        Subscription::where('order_id', $this->order->id)->update(['plan_id' => $plan->id]);
    }

    private function extra(int $quantity): void
    {
        OrderEntitlement::updateOrCreate(
            ['order_id' => $this->order->id, 'entitlement_id' => $this->agents->id],
            ['quantity' => $quantity, 'unit_price' => '3', 'currency' => $this->currency],
        );
    }

    /** @param list<Response> $cloudReplies */
    private function service(LicenseService $licenses, array $cloudReplies = []): AgentCountService
    {
        return new AgentCountService($licenses, new Client(['handler' => HandlerStack::create(new MockHandler($cloudReplies))]));
    }

    public function test_total_is_included_plus_bought_agents(): void
    {
        $service = $this->service(Mockery::mock(LicenseService::class));

        $this->extra(2);
        $this->assertSame(7, $service->total($this->order));
        $this->extra(-2); // a seeded "fewer agents" extra
        $this->assertSame(3, $service->total($this->order));

        $this->onPlan($this->plan(false));
        $this->assertSame(0, $service->total($this->order));
        $this->onPlan($this->plan(null));
        $this->assertSame(0, $service->total($this->order));
    }

    public function test_write_rewrites_only_the_last_four_digits(): void
    {
        $licenses = Mockery::mock(LicenseService::class);
        $licenses->shouldReceive('updateLicenseCode')->once()->with('ABCDEFGHIJKL0005', 'ABCDEFGHIJKL0007')->andReturn(1);

        $this->service($licenses)->write($this->order, 7);

        $this->assertSame('ABCDEFGHIJKL0007', $this->order->fresh()->serial_key);
    }

    public function test_write_does_nothing_when_the_digits_already_match(): void
    {
        $licenses = Mockery::mock(LicenseService::class);
        $licenses->shouldNotReceive('updateLicenseCode');

        $this->service($licenses)->write($this->order, 5);

        $this->assertSame('ABCDEFGHIJKL0005', $this->order->fresh()->serial_key);
    }

    public function test_a_failed_cloud_push_reverts_the_license(): void
    {
        ThirdPartyApp::firstOrCreate(['app_name' => 'faveo_app_key'], ['app_key' => 'test-key', 'app_secret' => 'test-secret']);
        FaveoCloud::firstOrCreate([], ['cloud_central_domain' => 'https://cloud.test.local', 'cloud_cname' => 'test']);
        CloudProducts::create(['cloud_product' => $this->product->id, 'cloud_free_plan' => $this->plan(false)->id, 'cloud_product_key' => 'x']);
        DB::table('licenses')->insert(['product_id' => $this->product->id, 'license_code' => 'ABCDEFGHIJKL0005', 'license_order_number' => $this->order->number, 'created_at' => now(), 'updated_at' => now()]);
        DB::table('installations')->insert(['product_id' => $this->product->id, 'license_code' => 'ABCDEFGHIJKL0005', 'installation_domain' => 'tenant.example.test', 'created_at' => now(), 'updated_at' => now()]);
        $this->extra(2);

        $licenses = Mockery::mock(LicenseService::class);
        $licenses->shouldReceive('updateLicenseCode')->once()->with('ABCDEFGHIJKL0005', 'ABCDEFGHIJKL0007')->andReturn(1);
        $licenses->shouldReceive('updateLicenseCode')->once()->with('ABCDEFGHIJKL0007', 'ABCDEFGHIJKL0005')->andReturn(1);
        $service = $this->service($licenses, [
            new Response(200, [], json_encode(['status' => 'failed'])),
            new Response(200, [], json_encode(['status' => 'success'])), // the old license put back on the tenant
        ]);

        try {
            $service->write($this->order, 7);
            $this->fail('A failed cloud push must throw.');
        } catch (\RuntimeException) {
        }

        $this->assertSame('ABCDEFGHIJKL0005', $this->order->fresh()->serial_key);
        $this->assertSame(2, OrderEntitlement::where('order_id', $this->order->id)->value('quantity'));
    }

    public function test_a_cloud_order_without_an_installed_domain_still_gets_its_license_and_a_warning(): void
    {
        CloudProducts::create(['cloud_product' => $this->product->id, 'cloud_free_plan' => $this->plan(false)->id, 'cloud_product_key' => 'x']);
        \Logger::shouldReceive('exception')->once()->with(Mockery::on(fn (\Throwable $e): bool => str_contains($e->getMessage(), 'has no installed domain')));
        $licenses = Mockery::mock(LicenseService::class);
        $licenses->shouldReceive('updateLicenseCode')->once()->with('ABCDEFGHIJKL0005', 'ABCDEFGHIJKL0007')->andReturn(1);

        $this->service($licenses)->write($this->order, 7); // no cloud reply queued: a push would throw

        $this->assertSame('ABCDEFGHIJKL0007', $this->order->fresh()->serial_key);
    }

    /** An Agents override of unlimited on the order's subscription (the seeder writes one for 0000 licenses). */
    private function unlimitedOverride(License|Subscription $owner): void
    {
        resolve(EntitlementService::class)->setOverride($owner, $this->agents, null, null);
    }

    public function test_an_unlimited_override_makes_the_order_unlimited(): void
    {
        $service = $this->service(Mockery::mock(LicenseService::class));
        $this->extra(2);

        $this->unlimitedOverride(Subscription::where('order_id', $this->order->id)->firstOrFail());

        $this->assertSame(0, $service->total($this->order));
        $this->assertTrue($service->unlimited($this->order));
    }

    public function test_an_admin_agents_override_replaces_the_total(): void
    {
        $service = $this->service(Mockery::mock(LicenseService::class));
        $this->extra(2); // 5 + 2 bought
        $subscription = Subscription::where('order_id', $this->order->id)->firstOrFail();

        resolve(EntitlementService::class)->setOverride($subscription, $this->agents, '3', null);

        $this->assertSame(3, $service->total($this->order));
        $this->assertFalse($service->unlimited($this->order));
    }

    public function test_an_unlimited_override_carries_to_the_order_a_plan_change_makes(): void
    {
        $service = $this->service(Mockery::mock(LicenseService::class));
        $from = License::create(['product_id' => $this->product->id, 'license_code' => 'ABCDEFGHIJKL0000', 'license_order_number' => $this->order->number]);
        $this->unlimitedOverride($from);
        $newOrder = Order::factory()->create(['product' => $this->product->id, 'client' => $this->order->client, 'serial_key' => Crypt::encrypt('MNOPQRSTUVWX0000')]);
        Subscription::factory()->create(['order_id' => $newOrder->id, 'plan_id' => $this->plan('2', withPrice: true)->id, 'product_id' => $this->product->id, 'user_id' => $this->order->client]);
        $to = License::create(['product_id' => $this->product->id, 'license_code' => 'MNOPQRSTUVWX0000', 'license_order_number' => $newOrder->number]);

        resolve(EntitlementService::class)->carryOverrides($from, $to);

        $this->assertSame(0, $service->total($newOrder));
    }

    public function test_paying_for_more_agents_updates_the_license(): void
    {
        $agentCount = Mockery::mock(AgentCountService::class)->makePartial();
        $agentCount->shouldReceive('write')->once()->with(Mockery::on(fn (Order $o): bool => $o->id === $this->order->id), 7);
        $this->app->instance(AgentCountService::class, $agentCount);

        $sales = new EntitlementSalesService;
        $invoice = $sales->createPurchaseInvoice($this->order, $this->agents->fresh(), 2);
        $sales->fulfil($invoice->fresh());

        $this->assertSame(2, OrderEntitlement::where('order_id', $this->order->id)->where('entitlement_id', $this->agents->id)->value('quantity'));
    }

    public function test_a_failed_license_write_after_payment_keeps_the_paid_agents(): void
    {
        $agentCount = Mockery::mock(AgentCountService::class)->makePartial();
        $agentCount->shouldReceive('write')->once()->andThrow(new \RuntimeException('cloud down'));
        $this->app->instance(AgentCountService::class, $agentCount);

        $sales = new EntitlementSalesService;
        $sales->fulfil($sales->createPurchaseInvoice($this->order, $this->agents->fresh(), 2)->fresh());

        $this->assertSame(2, OrderEntitlement::where('order_id', $this->order->id)->where('entitlement_id', $this->agents->id)->value('quantity'));
    }

    public function test_a_reduction_at_renewal_rewrites_the_license(): void
    {
        $this->extra(3);
        OrderEntitlement::where('order_id', $this->order->id)->update(['renew_quantity' => 1]);
        $agentCount = Mockery::mock(AgentCountService::class)->makePartial();
        $agentCount->shouldReceive('write')->once()->with(Mockery::type(Order::class), 6);
        $this->app->instance(AgentCountService::class, $agentCount);

        (new EntitlementSalesService)->applyPendingReductions($this->order->id);
    }

    public function test_a_plan_change_carries_the_agents_bought(): void
    {
        $this->extra(3); // 5 included + 3 bought
        $carry = function (Plan $plan): ?int {
            $to = Order::factory()->create(['product' => $this->product->id, 'client' => $this->order->client]);
            Subscription::factory()->create(['order_id' => $to->id, 'plan_id' => $plan->id, 'product_id' => $this->product->id, 'user_id' => $this->order->client]);
            (new EntitlementSalesService)->carryExtras($this->order, $to, resolve(EntitlementService::class));

            return OrderEntitlement::where('order_id', $to->id)->where('entitlement_id', $this->agents->id)->value('quantity');
        };

        $this->assertSame(3, $carry($this->plan('4', withPrice: true)));
        $this->assertSame(3, $carry($this->plan('10', withPrice: true)));
        $this->assertNull($carry($this->plan(null, withPrice: true)));
    }
}
