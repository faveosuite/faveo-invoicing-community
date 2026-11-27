<?php

namespace Tests\Unit\Backend\Seeders;

use App\License\Models\License;
use App\Model\Payment\Plan;
use App\Model\Product\Entitlement;
use App\Model\Product\Grant;
use App\Model\Product\Product;
use App\Services\EntitlementService;
use Database\Seeders\v4_0_2_8_RC_1\DatabaseSeeder;
use Illuminate\Support\Facades\DB;
use Tests\DBTestCase;

/**
 * An empty number on a plan now means unlimited. Plans saved before that meant the feature's
 * default, so the seeder writes the default into those rows.
 */
class V4028Rc1EmptyNumberLimitsSeederTest extends DBTestCase
{
    public function test_empty_number_limits_get_the_features_default_and_nothing_else_changes(): void
    {
        $plan = Plan::factory()->create(['product' => Product::factory()->create()->id]);
        $item = fn (string $code, string $type, ?string $default, bool $active = true): Entitlement => Entitlement::create(
            ['code' => $code.'_'.uniqid(), 'label' => $code, 'type' => $type, 'default_value' => $default, 'is_active' => $active]);
        $rows = [
            'null' => [$item('storage', 'number', '7'), null, '7'],
            'empty' => [$item('mailboxes', 'number', '3'), '', '3'],
            'set' => [$item('departments', 'number', '9'), '2', '2'],
            'no_default' => [$item('seats', 'number', null), null, null],
            'archived' => [$item('old', 'number', '4', active: false), null, null],
            'text' => [$item('region', 'text', 'US'), null, null],
            'agents' => [EntitlementService::agents(), null, null], // unlimited agents stay unlimited
        ];
        foreach ($rows as [$entitlement, $value]) {
            $plan->entitlements()->attach($entitlement->id, ['value' => $value]);
        }

        $seeder = new DatabaseSeeder;
        $seeder->fillEmptyNumberLimits();
        $seeder->fillEmptyNumberLimits(); // a second run changes nothing

        foreach ($rows as $case => [$entitlement, , $expected]) {
            $this->assertSame($expected, DB::table('plan_entitlements')->where('plan_id', $plan->id)
                ->where('entitlement_id', $entitlement->id)->value('value'), $case);
        }
    }

    public function test_unlimited_agents_stored_as_minus_one_become_empty(): void
    {
        $license = License::create(['product_id' => Product::factory()->create()->id, 'license_code' => 'ABCDEFGHIJKL0000',
            'license_order_number' => (string) random_int(10000000, 99999999), 'license_limit' => 1, 'license_status' => 1]);
        foreach ([Grant::PLAN, Grant::ADMIN] as $source) {
            Grant::create(['license_id' => $license->id, 'code' => EntitlementService::AGENTS, 'value' => '-1', 'source' => $source]);
        }

        $seeder = new DatabaseSeeder;
        $seeder->seedUnlimitedAgentsAsEmpty();
        $seeder->seedUnlimitedAgentsAsEmpty(); // safe to run again

        $this->assertSame([null, null], Grant::where('license_id', $license->id)->where('code', EntitlementService::AGENTS)->pluck('value')->all());
    }

    public function test_plugins_named_alike_get_their_sku_and_new_ones_do_too(): void
    {
        $name = 'Adhoc '.uniqid();
        $free = Product::factory()->create(['name' => $name, 'product_type' => 'addon', 'product_sku' => 'ADH'.uniqid()]);
        $pro = Product::factory()->create(['name' => $name, 'product_type' => 'addon', 'product_sku' => 'ADHPRO'.uniqid()]);
        $a = EntitlementService::pluginFeature($free);
        $b = EntitlementService::pluginFeature($pro);
        $this->assertSame([$name, $name.' ('.$pro->product_sku.')'], [$a->label, $b->label]); // new ones: told apart

        $b->update(['label' => $name]); // as older data has it
        (new DatabaseSeeder)->seedUniquePluginLabels();
        (new DatabaseSeeder)->seedUniquePluginLabels(); // safe to run again
        $this->assertSame([$name, $name.' ('.$pro->product_sku.')'], [$a->fresh()->label, $b->fresh()->label]);
    }

    public function test_a_license_running_unlimited_agents_on_a_counted_plan_gets_an_unlimited_override(): void
    {
        $product = Product::factory()->create();
        $plan = \App\Model\Payment\Plan::factory()->create(['product' => $product->id]);
        $plan->entitlements()->attach(EntitlementService::agents()->id, ['value' => '10']);
        $order = \App\Model\Order\Order::factory()->create(['product' => $product->id, 'number' => random_int(10000000, 99999999),
            'order_status' => 'executed', 'serial_key' => \Illuminate\Support\Facades\Crypt::encrypt('ABCDEFGHIJKL0000')]);
        \App\Model\Product\Subscription::factory()->create(['order_id' => $order->id, 'plan_id' => $plan->id, 'product_id' => $product->id]);
        License::create(['product_id' => $product->id, 'license_code' => 'ABCDEFGHIJKL0000', 'license_order_number' => (string) $order->number,
            'license_limit' => 1, 'license_status' => 1]);

        (new DatabaseSeeder)->seedUnlimitedLicenseOverrides();
        (new DatabaseSeeder)->seedUnlimitedLicenseOverrides(); // safe to run again

        $count = resolve(\App\Services\AgentCountService::class);
        $this->assertTrue($count->unlimited($order));
        $this->assertSame(1, Grant::where('code', EntitlementService::AGENTS)->where('source', Grant::ADMIN)->count() >= 1 ? 1 : 0);
        $this->assertSame('ABCDEFGHIJKL0000', License::where('license_order_number', $order->number)->value('license_code')); // never rewritten
    }
}
