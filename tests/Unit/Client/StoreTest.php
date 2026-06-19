<?php

namespace Tests\Unit\Client;

use App\Http\Controllers\Common\TemplateController;
use App\Http\Controllers\Front\PageController;
use App\Model\Payment\Plan;
use App\Model\Payment\PlanPrice;
use App\Model\Product\CloudProducts;
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
        $this->withoutMiddleware();
        $group = ProductGroup::create(['name' => 'consumer-products', 'hidden' => 0, 'pricing_templates_id' => 1]);
        $product = Product::factory()->create(['group' => $group->id]);
        $plan = Plan::factory()->create(['product' => $product->id]);
        PlanPrice::factory()->create(['plan_id' => $plan->id, 'add_price' => '0', 'currency' => 'USD']);
        $response = $this->call('GET', 'group/'.$group->pricing_templates_id.'/'.$group->id.'/');
        $response->json();
        $response->assertStatus(200);
    }

    #[Group('store')]
    public function test_store_get_monthly_price(): void
    {
        $user = User::factory()->create(['country' => 'US']);
        $this->actingAs($user);
        $this->withoutMiddleware();
        $group = ProductGroup::create(['name' => 'consumer-products', 'hidden' => 0, 'pricing_templates_id' => 1]);
        $product = Product::factory()->create(['group' => $group->id]);
        $plan = Plan::factory()->create(['product' => $product->id, 'days' => 30]);
        PlanPrice::factory()->create(['plan_id' => $plan->id, 'add_price' => 200, 'currency' => 'USD']);
        $response = $this->con->leastAmount($product->id);
        //        $response = $this->getPrivateMethod($this->con, 'leastAmount', [$product->id]);
        $this->assertEquals($response, '<span class="price-unit">$</span>200.00');
    }

    #[Group('store')]
    public function test_store_monthly_price_more_days(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        $this->withoutMiddleware();
        $group = ProductGroup::create(['name' => 'consumer-products', 'hidden' => 0, 'pricing_templates_id' => 1]);
        $product = Product::factory()->create(['group' => $group->id]);
        $plan = Plan::factory()->create(['product' => $product->id]);
        PlanPrice::factory()->create(['plan_id' => $plan->id]);
        $response = $this->getPrivateMethod($this->con, 'leastAmount', [$product->id]);
        $this->assertEquals($response, 'Free');
    }

    #[Group('store')]
    public function test_store_yearly_price(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        $this->withoutMiddleware();
        $group = ProductGroup::create(['name' => 'consumer-products', 'hidden' => 0, 'pricing_templates_id' => 1]);
        $product = Product::factory()->create(['group' => $group->id]);
        $plan = Plan::factory()->create(['product' => $product->id, 'days' => 30]);
        PlanPrice::factory()->create(['plan_id' => $plan->id, 'add_price' => '500', 'currency' => 'USD']);
        $response = $this->getPrivateMethod($this->con1, 'YearlyAmount', [$product->id]);
        $this->assertEquals($response, '<span class="price-unit" id="'.$plan->id.'">$</span>500.00');
    }

    #[Group('store')]
    public function test_store_get_price_description(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        $this->withoutMiddleware();
        $group = ProductGroup::create(['name' => 'consumer-products', 'hidden' => 0, 'pricing_templates_id' => 1]);
        $product = Product::factory()->create(['group' => $group->id]);
        $plan = Plan::factory()->create(['product' => $product->id]);
        $planPrice = PlanPrice::factory()->create(['plan_id' => $plan->id, 'add_price' => '500', 'price_description' => 'GoodProduct']);
        $response = $this->getPrivateMethod($this->con1, 'getPriceDescription', [$product->id]);
        $this->assertEquals($response, $planPrice->price_description);
    }

    #[Group('store')]
    public function test_store_get_number_of_agents_monthly(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        $this->withoutMiddleware();
        $group = ProductGroup::create(['name' => 'consumer-products', 'hidden' => 0, 'pricing_templates_id' => 1]);
        $product = Product::factory()->create(['group' => $group->id]);
        $plan = Plan::factory()->create(['product' => $product->id, 'days' => 30]);
        $planPrice = PlanPrice::factory()->create(['plan_id' => $plan->id, 'add_price' => '500', 'price_description' => 'GoodProduct', 'no_of_agents' => 7]);
        $response = $this->getPrivateMethod($this->con1, 'getmonthPriceDescription', [$product->id]);
        $this->assertEquals($response, sprintf('per month for <strong> %s agent</strong>', $planPrice->no_of_agents));
    }

    #[Group('store')]
    public function test_store_get_url(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        $this->withoutMiddleware();
        $group = ProductGroup::create(['name' => 'consumer-products', 'hidden' => 0, 'pricing_templates_id' => 1]);
        $product = Product::factory()->create(['group' => $group->id]);
        $plan = Plan::factory()->create(['product' => $product->id, 'days' => 30]);
        PlanPrice::factory()->create(['plan_id' => $plan->id, 'add_price' => '500', 'price_description' => 'GoodProduct', 'no_of_agents' => 7]);
        $orderButton = 'btn-dark';
        $highlight = false;
        $response = $this->getPrivateMethod($this->con1, 'generateProductUrl', [$product, $orderButton, $highlight]);
        $this->assertEquals('Order Now', $response['button']);
    }

    #[Group('store')]
    public function test_store_when_product_registered_in_cloud(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        $this->withoutMiddleware();
        $group = ProductGroup::create(['name' => 'consumer-products', 'hidden' => 0, 'pricing_templates_id' => 1]);
        $product = Product::factory()->create(['group' => $group->id]);
        $plan = Plan::factory()->create(['product' => $product->id, 'days' => 30]);
        PlanPrice::factory()->create(['plan_id' => $plan->id, 'add_price' => '500', 'price_description' => 'GoodProduct', 'no_of_agents' => 7]);
        CloudProducts::create(['cloud_product' => $product->id, 'cloud_free_plan' => $plan->id]);
        $orderButton = 'btn-dark';
        $highlight = false;
        $response = $this->getPrivateMethod($this->con1, 'generateProductUrl', [$product, $orderButton, $highlight]);
        $this->assertEquals('Order Now', $response['button']);
        $this->assertEquals('cloud', $response['type']);
    }

    #[Group('store')]
    public function test_store_get_offer_price_monthly(): void
    {
        $user = User::factory()->create(['country' => 'US']);
        $this->actingAs($user);
        $this->withoutMiddleware();
        $group = ProductGroup::create(['name' => 'consumer-products', 'hidden' => 0, 'pricing_templates_id' => 1]);
        $product = Product::factory()->create(['group' => $group->id]);
        $plan = Plan::factory()->create(['product' => $product->id, 'days' => 30]);
        $planPrice = PlanPrice::factory()->create(['plan_id' => $plan->id, 'add_price' => '500', 'price_description' => 'GoodProduct', 'no_of_agents' => 7, 'offer_price' => '100', 'currency' => 'USD']);
        CloudProducts::create(['cloud_product' => $product->id, 'cloud_free_plan' => $plan->id]);
        $response = $this->getPrivateMethod($this->con1, 'getOfferprice', [$product->id]);

        $this->assertEquals($response['30_days'], $planPrice->offer_price);
    }

    #[Group('store')]
    public function test_store_get_offer_price_yearly(): void
    {
        $user = User::factory()->create(['country' => 'US']);
        $this->actingAs($user);
        $this->withoutMiddleware();
        $group = ProductGroup::create(['name' => 'consumer-products', 'hidden' => 0, 'pricing_templates_id' => 1]);
        $product = Product::factory()->create(['group' => $group->id]);
        $plan = Plan::factory()->create(['product' => $product->id, 'days' => 365]);
        $planPrice = PlanPrice::factory()->create(['plan_id' => $plan->id, 'add_price' => '500', 'price_description' => 'GoodProduct', 'no_of_agents' => 7, 'offer_price' => '100', 'currency' => 'USD']);
        CloudProducts::create(['cloud_product' => $product->id, 'cloud_free_plan' => $plan->id]);
        $response = $this->getPrivateMethod($this->con1, 'getOfferprice', [$product->id]);
        $this->assertEquals($response['365_days'], $planPrice->offer_price);
    }

    public function test_wordpress_plugin_url(): void
    {
        $user = User::factory()->create(['country' => 'US']);
        $this->actingAs($user);
        $this->withoutMiddleware();
        $group = ProductGroup::create(['name' => 'consumer-products', 'hidden' => 0, 'pricing_templates_id' => 1]);
        $product = Product::factory()->create(['group' => $group->id]);
        $plan = Plan::factory()->create(['product' => $product->id, 'days' => 365]);
        $planPrice = PlanPrice::factory()->create(['plan_id' => $plan->id, 'add_price' => '500', 'price_description' => 'GoodProduct', 'no_of_agents' => 7, 'offer_price' => '100']);
        $response = $this->call('GET', 'pricing/data', ['ipAddress' => '121.0.0.1', 'group' => $group->id]);

        $json = $response->json();
        $response->assertStatus(200);
        $this->assertEquals($planPrice->add_price, $json['products'][0]['add_price']);
        $this->assertEquals($product->name, $json['products'][0]['name']);
    }
}
