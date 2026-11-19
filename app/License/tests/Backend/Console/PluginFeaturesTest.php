<?php

namespace App\License\tests\Backend\Console;

use App\License\Helpers\LicenseValidator;
use App\License\Models\LicensePlugin;
use App\License\tests\Backend\LicenseTestCase;
use App\Model\Order\Order;
use App\Model\Payment\Plan;
use App\Model\Product\Grant;
use App\Model\Product\Subscription;
use App\Services\EntitlementService;
use Database\Seeders\v4_0_2_7_RC_1\DatabaseSeeder;
use Illuminate\Support\Facades\DB;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;

#[Group('plugin-features')]
class PluginFeaturesTest extends LicenseTestCase
{
    #[Test]
    public function plugin_links_become_features_without_anyone_losing_a_plugin(): void
    {
        $product = $this->createProduct();
        $bundled = $this->createProduct(['name' => 'Bundled Pro '.uniqid()]);
        $byHand = $this->createProduct(['name' => 'By Hand '.uniqid()]);
        $bundledLater = $this->createProduct(['name' => 'Bundled Later '.uniqid()]); // bundled after this license was sold
        DB::table('product_plugin_group')->insert([
            ['product_id' => $product->id, 'plugin_id' => $bundled->id],
            ['product_id' => $product->id, 'plugin_id' => $bundledLater->id],
        ]);

        $plan = Plan::factory()->create(['product' => $product->id]);
        $order = Order::factory()->create(['product' => $product->id, 'number' => random_int(10000000, 99999999)]);
        Subscription::factory()->create(['order_id' => $order->id, 'plan_id' => $plan->id, 'product_id' => $product->id]);
        $license = $this->createLicense(['product_id' => $product->id, 'license_order_number' => $order->number]);
        foreach ([$bundled, $byHand] as $plugin) {
            LicensePlugin::create(['license_id' => $license->id, 'product_id' => $plugin->id]);
        }

        (new DatabaseSeeder)->seedPluginFeatures();
        (new DatabaseSeeder)->seedPluginFeatures(); // safe to run again

        $bundledCode = $bundled->fresh()->unlocksEntitlement->code;
        $byHandCode = $byHand->fresh()->unlocksEntitlement->code;
        $base = $this->grantsOf($license, 'plan');
        $admin = $this->grantsOf($license, 'admin');

        $this->assertTrue($plan->entitlements()->where('code', $bundledCode)->exists()); // the bundle is now a plan feature
        $this->assertSame('1', $base[$bundledCode]); // the license got its plan's features
        $this->assertEquals(['value' => '1', 'until' => null], $admin[$byHandCode]); // a hand-added link is kept
        $this->assertArrayNotHasKey($bundledCode, $admin);
        $laterCode = $bundledLater->fresh()->unlocksEntitlement->code;
        $this->assertTrue($plan->entitlements()->where('code', $laterCode)->exists()); // new sales get it
        $this->assertArrayNotHasKey($laterCode, $base); // this customer didn't have it: still doesn't

        $this->artisan('license:check-grants')->expectsOutputToContain('Lost (must be 0): 0')->assertSuccessful();

        Grant::where('license_id', $license->id)->where('source', Grant::ADMIN)->delete(); // the hand-added plugin is no longer granted
        $this->artisan('license:check-grants')->expectsOutputToContain('Lost (must be 0): 1')->assertFailed();
    }

    #[Test]
    public function a_plugin_runs_on_its_main_license_only_with_its_feature(): void
    {
        $product = $this->createProduct();
        $plugin = $this->createProduct(['product_type' => 'addon', 'product_sku' => 'WHT-APP-PRO-'.uniqid()]);
        $feature = $plugin->fresh()->unlocksEntitlement; // every add-on gets its feature on save

        $this->assertStringStartsWith('wht_app_pro_', $feature->code);
        $this->assertSame('Plugins', $feature->category);

        $license = $this->createLicense(['product_id' => $product->id]);
        $validator = new LicenseValidator;
        $this->assertNull($validator->findLicenseWithPlugins($license->license_code, $plugin->id));

        $this->grantPlugin($license, $plugin);
        $this->assertSame($license->id, $validator->findLicenseWithPlugins($license->license_code, $plugin->id)?->id);

        // A plan that sells the plugin makes it one of the product's plugins (download list).
        $plan = Plan::factory()->create(['product' => $product->id]);
        $feature->prices()->create(['plan_id' => $plan->id, 'currency' => 'USD', 'unit_price' => 5]);
        $this->assertContains($plugin->id, EntitlementService::productPluginIds($product->id));
    }
}
