<?php

namespace Tests\Unit\Backend\Http\Controllers\Front;

use App\Http\Controllers\Front\StoreController;
use App\Model\Common\CommonSettings;
use App\Model\Common\PricingTemplate;
use App\Model\Common\Setting;
use App\Model\Payment\Period;
use App\Model\Payment\Plan;
use App\Model\Payment\PlanPrice;
use App\Model\Product\Product;
use App\Model\Product\ProductGroup;
use App\Services\Seo\SeoTemplateFormatter;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\DBTestCase;

class StoreControllerTest extends DBTestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware();
        $this->getLoggedInUser('user');
    }

    public function test_get_groups_returns_200(): void
    {
        $response = $this->getJson('/store/groups');

        $response->assertStatus(200)
            ->assertJson(['success' => true]);
    }

    public function test_get_products_returns_response_for_unknown_group(): void
    {
        $response = $this->getJson('/store/999999/products');

        $this->assertContains($response->getStatusCode(), [200, 400, 404]);
    }

    public function test_get_products_returns_response_for_valid_group(): void
    {
        $group = ProductGroup::first();
        if (! $group) {
            $tmpl = PricingTemplate::first();
            if (! $tmpl) {
                $this->assertTrue(true);

                return;
            }

            $group = ProductGroup::create(['name' => 'Test Group '.uniqid(), 'pricing_templates_id' => $tmpl->id]);
        }

        $response = $this->getJson("/store/{$group->id}/products");

        $this->assertContains($response->getStatusCode(), [200, 400]);
    }

    public function test_get_products_falls_back_to_the_bare_name_when_group_has_no_own_meta(): void
    {
        Setting::find(1)->update(['company' => 'Acme Inc', 'favicon_title_client' => '']);
        CommonSettings::where('option_name', 'seo')->where('optional_field', 'general_description')->delete();
        // SeoTemplateFormatter is bound as a singleton (AppServiceProvider)
        // and caches Setting/CommonSettings at construction — forget it so
        // the values just set above are actually picked up when the
        // controller resolves it fresh for this request.
        $this->app->forgetInstance(SeoTemplateFormatter::class);

        $tmpl = PricingTemplate::first();
        if (! $tmpl) {
            $this->assertTrue(true);

            return;
        }

        $group = ProductGroup::create([
            'name' => 'Widgets '.uniqid(),
            'pricing_templates_id' => $tmpl->id,
            'hidden' => 0,
            'meta_title' => null,
            'meta_description' => null,
        ]);

        $response = $this->getJson("/store/{$group->id}/products");

        $response->assertStatus(200);
        $this->assertSame($group->name, $response->json('data.group.meta_title'));
    }

    /** A plan on $product priced $price with $offer % off, billed every $days days (null = no period). */
    private function plan(Product $product, float $price, float $offer = 0, ?int $days = 365, ?string $description = null): Plan
    {
        $plan = Plan::create(['name' => 'P'.uniqid(), 'product' => $product->id, 'days' => $days ?? 0]);
        PlanPrice::create(['plan_id' => $plan->id, 'currency' => 'USD', 'add_price' => $price, 'renew_price' => $price, 'offer_price' => $offer, 'price_description' => $description]);
        if ($days !== null) {
            $plan->periods()->attach(Period::create(['name' => $days.' days', 'days' => $days])->id);
        }

        return $plan;
    }

    /** @return list<array<string, mixed>> */
    private function plansFor(Product $product): array
    {
        $controller = resolve(StoreController::class);

        return new \ReflectionMethod($controller, 'getProductPlans')->invoke($controller, $product->fresh(), 'USD');
    }

    public function test_plans_show_offer_price_per_month_and_original_price(): void
    {
        $product = Product::create(['name' => 'Plans '.uniqid()]);
        $plan = $this->plan($product, 100, offer: 25, days: 360, description: 'per year');

        $this->assertSame([[
            'id' => $plan->id,
            'option_label' => '$75.00 per year',
            'price_raw' => 75.0,
            'price_display' => '75.00',
            'price_per_month' => '6.25',
            'original_price_raw' => 100.0,
            'original_display' => '100.00',
            'original_price_per_month' => '8.33',
            'description' => 'per year',
            'period' => '360 days',
            'is_default' => true,
        ]], $this->plansFor($product));
    }

    public function test_plans_newest_first_cheapest_is_default_free_has_no_prices(): void
    {
        $product = Product::create(['name' => 'Plans '.uniqid()]);
        $paid = $this->plan($product, 50, days: 30);
        $free = $this->plan($product, 0, days: null);
        $dear = $this->plan($product, 90, days: 15);

        $plans = $this->plansFor($product);

        $this->assertSame([$dear->id, $free->id, $paid->id], array_column($plans, 'id'));
        $this->assertSame([false, true, false], array_column($plans, 'is_default'));
        $this->assertSame(['Free', null, null, null, ''], [$plans[1]['option_label'], $plans[1]['price_display'], $plans[1]['price_per_month'], $plans[1]['original_display'], $plans[1]['period']]);
        $this->assertSame(['$90.00 15 days', '90.00'], [$plans[0]['option_label'], $plans[0]['price_per_month']]); // under a month counts as one month
    }

    public function test_products_sort_by_cheapest_paid_plan_ignoring_free_trial_plans(): void
    {
        $tmpl = PricingTemplate::first();
        if (! $tmpl) {
            $this->markTestSkipped('No pricing template to attach a group to.');
        }

        $group = ProductGroup::create(['name' => 'Cloud '.uniqid(), 'pricing_templates_id' => $tmpl->id, 'hidden' => 0]);
        $currency = getCurrencyForClient((string) auth()->user()->country);

        // Created dearest first, and each carries a free trial plan like the cloud products do.
        $names = [];
        foreach (['Enterprise' => 300, 'SME' => 200, 'Startup' => 100] as $name => $price) {
            $product = Product::create(['name' => $name.' '.uniqid(), 'group' => $group->id, 'hidden' => 0]);
            foreach ([0, $price] as $amount) {
                $plan = Plan::create(['name' => 'P'.uniqid(), 'product' => $product->id, 'days' => 30, 'status' => 1]);
                PlanPrice::create(['plan_id' => $plan->id, 'currency' => $currency, 'add_price' => $amount, 'renew_price' => $amount, 'offer_price' => 0]);
            }

            $names[$name] = $product->name;
        }

        $listed = array_column($this->getJson("/store/{$group->id}/products")->assertOk()->json('data.products'), 'name');

        $this->assertSame([$names['Startup'], $names['SME'], $names['Enterprise']], $listed);
    }

    public function test_a_free_product_stays_first_when_a_paid_plan_is_added_to_it(): void
    {
        $tmpl = PricingTemplate::first();
        if (! $tmpl) {
            $this->markTestSkipped('No pricing template to attach a group to.');
        }

        $group = ProductGroup::create(['name' => 'Perpetual '.uniqid(), 'pricing_templates_id' => $tmpl->id, 'hidden' => 0]);
        $currency = getCurrencyForClient((string) auth()->user()->country);

        // Freelancer is free, plus a paid plan dearer than Startup's; Startup has no free plan.
        $names = [];
        foreach (['Startup' => [100], 'Freelancer' => [0, 500]] as $name => $prices) {
            $product = Product::create(['name' => $name.' '.uniqid(), 'group' => $group->id, 'hidden' => 0]);
            foreach ($prices as $amount) {
                $plan = Plan::create(['name' => 'P'.uniqid(), 'product' => $product->id, 'days' => 365, 'status' => 1]);
                PlanPrice::create(['plan_id' => $plan->id, 'currency' => $currency, 'add_price' => $amount, 'renew_price' => $amount, 'offer_price' => 0]);
            }

            $names[$name] = $product->name;
        }

        $listed = array_column($this->getJson("/store/{$group->id}/products")->assertOk()->json('data.products'), 'name');

        $this->assertSame([$names['Freelancer'], $names['Startup']], $listed);
    }

    public function test_contact_sales_product_is_listed_last(): void
    {
        $tmpl = PricingTemplate::first();
        if (! $tmpl) {
            $this->markTestSkipped('No pricing template to attach a group to.');
        }

        $group = ProductGroup::create(['name' => 'Perpetual '.uniqid(), 'pricing_templates_id' => $tmpl->id, 'hidden' => 0]);
        $currency = getCurrencyForClient((string) auth()->user()->country);

        // Enterprise is contact-sales and cheapest, but must still come after Pro.
        $names = [];
        foreach (['Enterprise' => [10, 1], 'Pro' => [900, 0]] as $name => [$amount, $contact]) {
            $product = Product::create(['name' => $name.' '.uniqid(), 'group' => $group->id, 'hidden' => 0, 'add_to_contact' => $contact]);
            $plan = Plan::create(['name' => 'P'.uniqid(), 'product' => $product->id, 'days' => 365, 'status' => 1]);
            PlanPrice::create(['plan_id' => $plan->id, 'currency' => $currency, 'add_price' => $amount, 'renew_price' => $amount, 'offer_price' => 0]);
            $names[$name] = $product->name;
        }

        $listed = array_column($this->getJson("/store/{$group->id}/products")->assertOk()->json('data.products'), 'name');

        $this->assertSame([$names['Pro'], $names['Enterprise']], $listed);
    }

    public function test_plans_skip_a_plan_without_a_price(): void
    {
        $product = Product::create(['name' => 'Plans '.uniqid()]);
        Plan::create(['name' => 'Unpriced', 'product' => $product->id, 'days' => 30]);

        $this->assertSame([], $this->plansFor($product));
    }
}
