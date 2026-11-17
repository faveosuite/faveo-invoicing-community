<?php

namespace Tests\Unit\Backend\Services;

use App\License\Models\License;
use App\Model\Order\Order;
use App\Model\Payment\Plan;
use App\Model\Product\Entitlement;
use App\Model\Product\Product;
use App\Model\Product\Subscription;
use App\Services\EntitlementService;
use Tests\DBTestCase;

class EntitlementServiceTest extends DBTestCase
{
    private EntitlementService $service;

    private Product $product;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new EntitlementService;
        $this->product = Product::factory()->create(['show_agent' => 1]);
    }

    private function item(string $feature, string $key, string $type, ?string $default, array $extra = []): Entitlement
    {
        return Entitlement::create([
            'feature' => $feature, 'key' => $key,
            'label' => ucfirst($key), 'type' => $type, 'default_value' => $default, ...$extra,
        ]);
    }

    private function license(int $orderNumber, ?string $code = null): License
    {
        return License::create([
            'product_id' => $this->product->id,
            'license_code' => $code ?? 'ENT'.uniqid().'X', // ends in a letter: no agent count in the code
            'license_order_number' => (string) $orderNumber,
            'license_limit' => 1,
            'license_status' => 1,
        ]);
    }

    public function test_a_plan_gives_only_the_features_it_includes_typed(): void
    {
        $sla = $this->item('sla', 'enabled', 'boolean', '0');
        $region = $this->item('region', 'value', 'select', 'US', ['options' => ['US', 'EU']]);
        $storage = $this->item('storage', 'gb', 'number', '10');
        $old = $this->item('old', 'thing', 'boolean', '1', ['is_active' => false]);
        $this->item('chat', 'enabled', 'boolean', '1'); // not on the plan: not sent

        $plan = Plan::factory()->create(['product' => $this->product->id]);
        \App\Model\Payment\PlanPrice::factory()->create(['plan_id' => $plan->id, 'no_of_agents' => 5]); // agents come from the plan's Pricing
        $plan->entitlements()->attach([$sla->id => ['value' => '1'], $region->id => ['value' => 'EU'], $storage->id => ['value' => null], $old->id => ['value' => '1']]);

        $this->assertSame(
            ['sla' => ['enabled' => true], 'region' => ['value' => 'EU'], 'storage' => ['gb' => 10], 'agents' => ['max' => 5]],
            $this->service->forPlan($this->product->id, $plan->id),
        );
    }

    public function test_license_keeps_its_copy_when_the_plan_changes_later(): void
    {
        $storage = $this->item('storage', 'gb', 'number', '5');
        $plan = Plan::factory()->create(['product' => $this->product->id]);
        $plan->entitlements()->attach($storage->id, ['value' => '25']);

        $license = $this->license(111111);
        $this->service->grant($license, $plan->id);

        $plan->entitlements()->updateExistingPivot($storage->id, ['value' => '50']);
        $chat = $this->item('chat', 'enabled', 'boolean', '1');
        $plan->entitlements()->attach($chat->id); // added to the plan after the sale

        $this->assertSame(
            ['storage' => ['gb' => 25]],
            $this->service->resolve($license->fresh()),
        );
    }

    public function test_apply_plan_updates_live_licenses_only(): void
    {
        $storage = $this->item('storage', 'gb', 'number', '5');
        $plan = Plan::factory()->create(['product' => $this->product->id]);

        $live = Order::factory()->create(['product' => $this->product->id, 'number' => 222222, 'order_status' => 'executed']);
        $dead = Order::factory()->create(['product' => $this->product->id, 'number' => 333333, 'order_status' => 'Terminated']);
        foreach ([$live, $dead] as $order) {
            Subscription::factory()->create(['plan_id' => $plan->id, 'order_id' => $order->id, 'product_id' => $this->product->id]);
        }
        $liveLicense = $this->license(222222);
        $deadLicense = $this->license(333333);

        $plan->entitlements()->attach($storage->id, ['value' => '40']);

        $this->assertSame(1, $this->service->applyPlanToLicenses($plan));
        $this->assertEquals(['storage' => ['gb' => 40], 'agents' => ['max' => EntitlementService::UNLIMITED]], $liveLicense->fresh()->entitlement_base);
        $this->assertNull($deadLicense->fresh()->entitlement_base);
    }

    public function test_override_wins_until_its_end_date_and_agents_come_from_the_code(): void
    {
        $chat = $this->item('chat', 'enabled', 'boolean', '0');
        $storage = $this->item('storage', 'gb', 'number', '10');
        EntitlementService::agents();

        $license = $this->license(555555, 'ABCDEFGHIJKL0040');
        $this->service->grant($license, null);
        $this->service->setOverride($license, $chat, '1', null);
        $this->service->setOverride($license, $storage, '50', now()->subDay()->toDateString()); // already ended

        $this->assertSame(
            ['chat' => ['enabled' => true], 'agents' => ['max' => 40]],
            $this->service->resolve($license->fresh()),
        );

        $this->service->removeOverride($license, $chat);
        $this->assertArrayNotHasKey('chat', $this->service->resolve($license->fresh())); // not on the plan
        $this->assertArrayNotHasKey('storage', $this->service->resolve($license->fresh()));
    }

    public function test_overrides_carry_to_the_replacing_license(): void
    {
        $chat = $this->item('chat', 'enabled', 'boolean', '0');
        $old = $this->license(666666);
        $new = $this->license(777777);
        $this->service->setOverride($old, $chat, '1', null);

        $this->service->carryOverrides($old->fresh(), $new);

        $this->assertEquals(['chat.enabled' => ['value' => '1', 'until' => null]], $new->fresh()->entitlement_overrides);
    }

    public function test_bought_extras_add_to_the_plan_and_overrides_still_win(): void
    {
        $storage = $this->item('storage', 'gb', 'number', '10');
        $chat = $this->item('chat', 'enabled', 'boolean', '0');
        $sla = $this->item('sla', 'enabled', 'boolean', '0');

        $order = Order::factory()->create(['product' => $this->product->id, 'number' => 999001]);
        $license = $this->license(999001);
        $license->update(['entitlement_base' => ['storage' => ['gb' => 10]]]); // the plan includes 10 GB
        foreach ([[$storage, 15], [$chat, 1], [$sla, 1]] as [$item, $qty]) {
            \App\Model\Order\OrderEntitlement::create([
                'order_id' => $order->id, 'entitlement_id' => $item->id, 'quantity' => $qty, 'unit_price' => 2, 'currency' => 'USD',
            ]);
        }
        $this->service->setOverride($license, $sla, '0', null);

        $this->assertSame(
            ['storage' => ['gb' => 25], 'chat' => ['enabled' => true], 'sla' => ['enabled' => false]],
            $this->service->resolve($license->fresh()),
        );
    }

    public function test_plan_with_no_agent_count_gives_unlimited_agents(): void
    {
        $plan = Plan::factory()->create(['product' => $this->product->id]);
        \App\Model\Payment\PlanPrice::factory()->create(['plan_id' => $plan->id, 'no_of_agents' => 0]);

        $this->assertSame(['agents' => ['max' => EntitlementService::UNLIMITED]], $this->service->forPlan($this->product->id, $plan->id));
        EntitlementService::agents();
        $license = $this->license(123456, 'ABCDEFGHIJKL0000');
        $this->assertSame(EntitlementService::UNLIMITED, $this->service->resolve($license)['agents']['max']);
    }

    public function test_normalize_rejects_values_that_do_not_fit_the_type(): void
    {
        $number = $this->item('agents', 'max', 'number', null);
        $select = $this->item('region', 'value', 'select', null, ['options' => ['US', 'EU']]);
        $bool = $this->item('sla', 'enabled', 'boolean', null);

        $this->assertSame('7', $number->normalize('7'));
        $this->assertFalse($number->normalize('-1'));
        $this->assertFalse($select->normalize('APAC'));
        $this->assertSame('0', $bool->normalize(false));
        $this->assertNull($bool->normalize(''));
    }

    public function test_a_number_feature_not_on_the_plan_counts_only_what_was_bought(): void
    {
        $storage = $this->item('storage', 'gb', 'number', '10');
        $order = Order::factory()->create(['product' => $this->product->id, 'number' => 999002]);
        $license = $this->license(999002);
        \App\Model\Order\OrderEntitlement::create(['order_id' => $order->id, 'entitlement_id' => $storage->id, 'quantity' => 5, 'unit_price' => 2, 'currency' => 'USD']);

        $this->assertSame(['storage' => ['gb' => 5]], $this->service->resolve($license));
    }
}
