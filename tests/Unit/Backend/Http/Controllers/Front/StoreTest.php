<?php

namespace Tests\Unit\Backend\Http\Controllers\Front;

use App\Http\Controllers\Common\TemplateController;
use App\Http\Controllers\Front\PageController;
use App\Model\Payment\Plan;
use App\Model\Payment\PlanPrice;
use App\Model\Product\Product;
use App\Model\Product\ProductGroup;
use App\User;
use Illuminate\Http\Request;
use Mockery;
use PHPUnit\Framework\Attributes\Group;
use Spatie\Html\Html;
use Tests\DBTestCase;

class StoreTest extends DBTestCase
{
    private TemplateController $con;

    private PageController $con1;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware();
        $this->con = new TemplateController;
        $this->con1 = new PageController;
        $this->request = resolve(Request::class);
        $this->html = Mockery::mock(Html::class, [$this->request])->makePartial();
        $this->html->shouldReceive('token')->andReturn('mocked-token');
        $this->app->instance(Html::class, $this->html);
    }

    #[Group('store')]
    public function test_store_has_groups(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        $group = ProductGroup::create(['name' => 'consumer-products', 'hidden' => 0, 'pricing_templates_id' => 1]);
        Product::factory()->create(['group' => $group->id]);
        $response = $this->call('GET', 'store/groups');
        $response->assertStatus(200);
        $response->assertJsonStructure(['success', 'data']);
    }

    #[Group('store')]
    public function test_store_get_price_description(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        $group = ProductGroup::create(['name' => 'consumer-products', 'hidden' => 0, 'pricing_templates_id' => 1]);
        $product = Product::factory()->create(['group' => $group->id]);
        $plan = Plan::factory()->create(['product' => $product->id]);
        $planPrice = PlanPrice::factory()->create(['plan_id' => $plan->id, 'add_price' => '500', 'price_description' => 'GoodProduct']);
        $response = $this->getPrivateMethod($this->con1, 'getPriceDescription', [$product->id]);
        $this->assertEquals($response, $planPrice->price_description);
    }

    public function test_store_products_for_guest_uses_ip_based_currency(): void
    {
        // Covers lines 88-91: guest user → IP-based currency resolution
        $group = ProductGroup::create(['name' => 'guest-group', 'hidden' => 0, 'pricing_templates_id' => 1]);

        $response = $this->getJson('store/'.$group->id.'/products');
        $response->assertStatus(200);
    }

    public function test_store_product_with_contact_sales_flag(): void
    {
        // Covers lines 191-196: product->add_to_contact == 1 → contact button
        $user = User::factory()->create(['country' => 'US']);
        $this->actingAs($user);
        $group = ProductGroup::create(['name' => 'contact-group-'.uniqid(), 'hidden' => 0, 'pricing_templates_id' => 1]);
        $product = Product::factory()->create(['group' => $group->id, 'hidden' => 0]);
        // add_to_contact not in fillable — use direct DB update
        \DB::table('products')->where('id', $product->id)->update(['add_to_contact' => 1]);
        Plan::factory()->create(['product' => $product->id, 'days' => 365, 'status' => 1]);
        PlanPrice::factory()->create(['plan_id' => Plan::where('product', $product->id)->value('id'), 'add_price' => 100, 'currency' => 'USD']);

        $response = $this->getJson('store/'.$group->id.'/products');
        $response->assertStatus(200);
    }

    public function test_store_plan_without_plan_price_is_skipped(): void
    {
        // Covers line 141: plan without planPrice → continue
        // The product must pass the whereHas('planRelation') check, so it needs at least
        // ONE plan with a PlanPrice. A second plan WITHOUT PlanPrice triggers the continue.
        $user = User::factory()->create(['country' => 'US', 'email' => 'store-skip-test-'.uniqid().'@test.local']);
        $this->actingAs($user);
        $currency = \App\Model\Payment\Currency::where('code', 'USD')->value('code') ?? 'USD';

        $group = ProductGroup::create(['name' => 'no-price-group-'.uniqid(), 'hidden' => 0, 'pricing_templates_id' => 1]);
        $product = Product::factory()->create(['group' => $group->id, 'hidden' => 0]);

        // Plan A: has PlanPrice → product passes whereHas check
        $planA = Plan::create(['name' => 'plan-with-price-'.uniqid(), 'product' => $product->id, 'days' => 30, 'status' => 1, 'allow_tax' => 0]);
        PlanPrice::create(['plan_id' => $planA->id, 'currency' => $currency, 'add_price' => '99', 'renew_price' => '99']);

        // Plan B: NO PlanPrice → triggers the `continue` on line 141
        Plan::create(['name' => 'plan-no-price-'.uniqid(), 'product' => $product->id, 'days' => 365, 'status' => 1, 'allow_tax' => 0]);

        $response = $this->getJson('store/'.$group->id.'/products');
        $response->assertStatus(200);
    }

    public function test_wordpress_plugin_url(): void
    {
        $user = User::factory()->create(['country' => 'US']);
        $this->actingAs($user);
        $group = ProductGroup::create(['name' => 'consumer-products', 'hidden' => 0, 'pricing_templates_id' => 1]);
        $product = Product::factory()->create(['group' => $group->id, 'hidden' => 0]);
        $plan = Plan::factory()->create(['product' => $product->id, 'days' => 365, 'status' => 1]);
        PlanPrice::factory()->create(['plan_id' => $plan->id, 'add_price' => '500', 'price_description' => 'GoodProduct', 'no_of_agents' => 7, 'offer_price' => '100', 'currency' => 'USD']);

        $response = $this->call('GET', 'store/'.$group->id.'/products');
        $response->assertStatus(200);
        $json = $response->json();
        $this->assertNotEmpty($json['data']['products']);
        $this->assertEquals($product->name, $json['data']['products'][0]['name']);
    }
}
