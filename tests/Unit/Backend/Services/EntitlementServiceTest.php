<?php

namespace Tests\Unit\Backend\Services;

use App\License\Models\License;
use App\Model\Order\Order;
use App\Model\Payment\Plan;
use App\Model\Product\Entitlement;
use App\Model\Product\Grant;
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

    private function item(string $code, string $type, ?string $default, array $extra = []): Entitlement
    {
        return Entitlement::create([
            'code' => $code,
            'label' => ucfirst($code), 'type' => $type, 'default_value' => $default, ...$extra,
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
        $sla = $this->item('sla', 'boolean', '0');
        $region = $this->item('region', 'select', 'US', ['options' => ['US', 'EU']]);
        $storage = $this->item('storage', 'number', '10');
        $old = $this->item('old', 'boolean', '1', ['is_active' => false]);
        $this->item('chat', 'boolean', '1'); // not on the plan: not sent

        $plan = Plan::factory()->create(['product' => $this->product->id]);
        \App\Model\Payment\PlanPrice::factory()->create(['plan_id' => $plan->id, 'no_of_agents' => 5]); // agents come from the plan's Pricing
        $plan->entitlements()->attach([$sla->id => ['value' => '1'], $region->id => ['value' => 'EU'], $storage->id => ['value' => null], $old->id => ['value' => '1']]);

        $this->assertSame(
            ['sla' => true, 'region' => 'EU', 'storage' => 10, 'agents' => 5],
            $this->service->forPlan($this->product->id, $plan->id),
        );
    }

    public function test_license_keeps_its_copy_when_the_plan_changes_later(): void
    {
        $storage = $this->item('storage', 'number', '5');
        $plan = Plan::factory()->create(['product' => $this->product->id]);
        $plan->entitlements()->attach($storage->id, ['value' => '25']);

        $license = $this->license(111111);
        $this->service->grant($license, $plan->id);

        $plan->entitlements()->updateExistingPivot($storage->id, ['value' => '50']);
        $chat = $this->item('chat', 'boolean', '1');
        $plan->entitlements()->attach($chat->id); // added to the plan after the sale

        $this->assertSame(
            ['storage' => 25],
            $this->service->resolve($license->fresh()),
        );
    }

    public function test_apply_plan_updates_live_licenses_only(): void
    {
        $storage = $this->item('storage', 'number', '5');
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
        $this->assertEquals(['storage' => '40', 'agents' => '-1'], $this->grantsOf($liveLicense, 'plan'));
        $this->assertSame([], $this->grantsOf($deadLicense, 'plan'));
    }

    public function test_override_wins_until_its_end_date_and_agents_come_from_the_code(): void
    {
        $chat = $this->item('chat', 'boolean', '0');
        $storage = $this->item('storage', 'number', '10');
        EntitlementService::agents();

        $license = $this->license(555555, 'ABCDEFGHIJKL0040');
        $this->service->grant($license, null);
        $this->service->setOverride($license, $chat, '1', null);
        $this->service->setOverride($license, $storage, '50', now()->subDay()->toDateString()); // already ended

        $this->assertSame(
            ['chat' => true, 'agents' => 40],
            $this->service->resolve($license->fresh()),
        );

        $this->service->removeOverride($license, $chat);
        $this->assertArrayNotHasKey('chat', $this->service->resolve($license->fresh())); // not on the plan
        $this->assertArrayNotHasKey('storage', $this->service->resolve($license->fresh()));
    }

    public function test_overrides_carry_to_the_replacing_license(): void
    {
        $chat = $this->item('chat', 'boolean', '0');
        $old = $this->license(666666);
        $new = $this->license(777777);
        $this->service->setOverride($old, $chat, '1', null);

        $this->service->carryOverrides($old->fresh(), $new);

        $this->assertEquals(['chat' => ['value' => '1', 'until' => null]], $this->grantsOf($new, 'admin'));
    }

    public function test_bought_extras_add_to_the_plan_and_overrides_still_win(): void
    {
        $storage = $this->item('storage', 'number', '10');
        $chat = $this->item('chat', 'boolean', '0');
        $sla = $this->item('sla', 'boolean', '0');

        $order = Order::factory()->create(['product' => $this->product->id, 'number' => 999001]);
        $license = $this->license(999001);
        $this->planGrants($license, ['storage' => '10']); // the plan includes 10 GB
        foreach ([[$storage, 15], [$chat, 1], [$sla, 1]] as [$item, $qty]) {
            \App\Model\Order\OrderEntitlement::create([
                'order_id' => $order->id, 'entitlement_id' => $item->id, 'quantity' => $qty, 'unit_price' => 2, 'currency' => 'USD',
            ]);
        }
        $this->service->setOverride($license, $sla, '0', null);

        $this->assertSame(
            ['storage' => 25, 'chat' => true, 'sla' => false],
            $this->service->resolve($license->fresh()),
        );
    }

    public function test_plan_with_no_agent_count_gives_unlimited_agents(): void
    {
        $plan = Plan::factory()->create(['product' => $this->product->id]);
        \App\Model\Payment\PlanPrice::factory()->create(['plan_id' => $plan->id, 'no_of_agents' => 0]);

        $this->assertSame(['agents' => EntitlementService::UNLIMITED], $this->service->forPlan($this->product->id, $plan->id));
        EntitlementService::agents();
        $license = $this->license(123456, 'ABCDEFGHIJKL0000');
        $this->assertSame(EntitlementService::UNLIMITED, $this->service->resolve($license)['agents']);
    }

    public function test_normalize_rejects_values_that_do_not_fit_the_type(): void
    {
        $number = $this->item('agents', 'number', null);
        $select = $this->item('region', 'select', null, ['options' => ['US', 'EU']]);
        $bool = $this->item('sla', 'boolean', null);

        $this->assertSame('7', $number->normalize('7'));
        $this->assertFalse($number->normalize('-1'));
        $this->assertFalse($select->normalize('APAC'));
        $this->assertSame('0', $bool->normalize(false));
        $this->assertNull($bool->normalize(''));
    }

    public function test_a_number_feature_not_on_the_plan_counts_only_what_was_bought(): void
    {
        $storage = $this->item('storage', 'number', '10');
        $order = Order::factory()->create(['product' => $this->product->id, 'number' => 999002]);
        $license = $this->license(999002);
        \App\Model\Order\OrderEntitlement::create(['order_id' => $order->id, 'entitlement_id' => $storage->id, 'quantity' => 5, 'unit_price' => 2, 'currency' => 'USD']);

        $this->assertSame(['storage' => 5], $this->service->resolve($license));
    }

    public function test_payload_sorts_each_type_into_its_own_list_and_dates_a_temporary_feature(): void
    {
        $sla = $this->item('sla', 'boolean', '0');
        $chat = $this->item('chat', 'boolean', '0');
        $reports = $this->item('reports', 'boolean', '0');
        $storage = $this->item('storage', 'number', null);
        $region = $this->item('region', 'select', null, ['options' => ['us', 'eu']]);
        EntitlementService::agents();

        $license = $this->license(999003, 'ABCDEFGHIJKL0025');
        $this->planGrants($license, ['sla' => '1', 'reports' => '0', 'storage' => null, 'region' => 'eu']);
        $license->update([
            'license_expire_date' => '2027-01-31 00:00:00',
            'license_updates_date' => '0000-00-00 00:00:00',
        ]);
        $until = now()->addDays(30)->toDateString();
        $this->service->setOverride($license, $chat, '1', $until); // a 30-day trial

        $payload = $this->service->payload($license->fresh());

        $this->assertSame(1, $payload['v']);
        $this->assertEqualsCanonicalizing(['sla', 'chat'], $payload['features']); // reports is off: not listed
        $this->assertSame(['storage' => null, 'agents' => 25], $payload['limits']); // empty number = unlimited
        $this->assertSame(['region' => 'eu'], $payload['values']);
        $this->assertSame(['chat' => $until], $payload['ends']); // sla comes from the plan: no end
        $this->assertSame(['license' => '2027-01-31', 'updates' => null, 'support' => null], $payload['expires']);
        $this->assertNotEmpty($payload['issued_at']);
    }

    public function test_license_dates_are_grants_and_each_change_counts_the_version_up(): void
    {
        $chat = $this->item('chat', 'boolean', '0');
        $license = $this->license(999004);
        $license->update(['license_expire_date' => '2027-05-01 00:00:00', 'license_support_date' => '0000-00-00 00:00:00']);

        $dates = Grant::where('license_id', $license->id)->where('source', Grant::LICENSE)->get()->mapWithKeys(fn (Grant $g) => [$g->code => $g->ends_at?->toDateString()]);
        $this->assertEquals(['run' => '2027-05-01', 'updates' => null, 'support' => null], $dates->all()); // 0000-00-00 = never
        $version = (int) $license->fresh()->version;

        $license->update(['license_comments' => 'no date change']);
        $this->service->setOverride($license, $chat, '1', null);
        $this->service->setOverride($license, $chat, '1', null); // same again: nothing changed
        $this->assertSame($version + 1, (int) $license->fresh()->version);

        $license->update(['license_expire_date' => '2028-05-01 00:00:00']);
        $this->assertSame($version + 2, (int) $license->fresh()->version);
        $this->assertSame('2028-05-01', $this->service->payload($license->fresh())['expires']['license']);
    }

    public function test_extras_follow_order_entitlements(): void
    {
        $storage = $this->item('storage', 'number', '10');
        $order = Order::factory()->create(['product' => $this->product->id, 'number' => 999005]);
        $license = $this->license(999005);

        $extra = \App\Model\Order\OrderEntitlement::create([
            'order_id' => $order->id, 'entitlement_id' => $storage->id, 'quantity' => 5, 'unit_price' => 2, 'currency' => 'USD',
        ]);
        $this->assertSame(['storage' => '5'], $this->grantsOf($license, Grant::EXTRA));

        $extra->update(['quantity' => 8]);
        $this->assertSame(['storage' => '8'], $this->grantsOf($license, Grant::EXTRA));

        $extra->delete();
        $this->assertSame([], $this->grantsOf($license, Grant::EXTRA));
    }
}
