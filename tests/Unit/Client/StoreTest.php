<?php

namespace Tests\Unit\Client;

use App\Http\Controllers\Common\TemplateController;
use App\Http\Controllers\Front\PageController;
use App\Http\Controllers\Product\ProductController;
use App\Model\Payment\Plan;
use App\Model\Payment\PlanPrice;
use App\Model\Product\CloudProducts;
use App\Model\Product\Product;
use App\Model\Product\ProductGroup;
use App\User;
use Tests\DBTestCase;

class StoreTest extends DBTestCase
{
    private $con;
    private $con1;
    protected function setUp(): void
    {
        parent::setUp();
        $this->con = new TemplateController();
        $this->con1 = new PageController();
    }
    public function test_store_has_groups(){
        $user=User::factory()->create();
        $this->actingAs($user);
        $this->withoutMiddleware();
        $group=ProductGroup::create(['name'=>'consumer-products','hidden'=>0,'pricing_templates_id'=>1]);
        $product=Product::factory()->create(['group'=>$group->id]);
        $plan=Plan::factory()->create(['product'=>$product->id]);
        $planPrice=PlanPrice::factory()->create(['plan_id'=>$plan->id]);
        $response = $this->call('GET','group/'.$group->pricing_templates_id.'/'.$group->id.'/');
        $response->assertStatus(200);
        $response->assertViewIs('themes.default1.common.template.shoppingcart');
        $response->assertViewHas('templates');
    }


    public function test_store_get_monthly_price(){
        $user=User::factory()->create();
        $user=User::factory()->create();
        $this->actingAs($user);
        $this->withoutMiddleware();
        $group=ProductGroup::create(['name'=>'consumer-products','hidden'=>0,'pricing_templates_id'=>1]);
        $product=Product::factory()->create(['group'=>$group->id]);
        $plan=Plan::factory()->create(['product'=>$product->id,'days'=>30]);
        $planPrice=PlanPrice::factory()->create(['plan_id'=>$plan->id,'add_price'=>200]);
        $response = $this->getPrivateMethod($this->con, 'leastAmount', [$product->id]);
        $this->assertEquals($response,'<span class="price-unit">$</span>200.00' );
    }

    public function test_store_monthly_price_more_days(){
        $user=User::factory()->create();
        $user=User::factory()->create();
        $this->actingAs($user);
        $this->withoutMiddleware();
        $group=ProductGroup::create(['name'=>'consumer-products','hidden'=>0,'pricing_templates_id'=>1]);
        $product=Product::factory()->create(['group'=>$group->id]);
        $plan=Plan::factory()->create(['product'=>$product->id]);
        $planPrice=PlanPrice::factory()->create(['plan_id'=>$plan->id]);
        $response = $this->getPrivateMethod($this->con, 'leastAmount', [$product->id]);
        $this->assertEquals($response,'Free' );
    }


    public function test_store_yearly_price(){
        $user=User::factory()->create();
        $user=User::factory()->create();
        $this->actingAs($user);
        $this->withoutMiddleware();
        $group=ProductGroup::create(['name'=>'consumer-products','hidden'=>0,'pricing_templates_id'=>1]);
        $product=Product::factory()->create(['group'=>$group->id]);
        $plan=Plan::factory()->create(['product'=>$product->id]);
        $planPrice=PlanPrice::factory()->create(['plan_id'=>$plan->id,'add_price'=>'500']);
        $response = $this->getPrivateMethod($this->con1, 'YearlyAmount', [$product->id]);
        $this->assertEquals($response,'<span class="price-unit" id="'.$plan->id.'">$</span>500.00' );
    }

    public function test_store_get_price_description(){
        $user=User::factory()->create();
        $user=User::factory()->create();
        $this->actingAs($user);
        $this->withoutMiddleware();
        $group=ProductGroup::create(['name'=>'consumer-products','hidden'=>0,'pricing_templates_id'=>1]);
        $product=Product::factory()->create(['group'=>$group->id]);
        $plan=Plan::factory()->create(['product'=>$product->id]);
        $planPrice=PlanPrice::factory()->create(['plan_id'=>$plan->id,'add_price'=>'500','price_description'=>'GoodProduct']);
        $response = $this->getPrivateMethod($this->con1, 'getPriceDescription', [$product->id]);
        $this->assertEquals($response,$planPrice->price_description);
    }

    public function test_store_get_number_of_agents_monthly(){
        $user=User::factory()->create();
        $user=User::factory()->create();
        $this->actingAs($user);
        $this->withoutMiddleware();
        $group=ProductGroup::create(['name'=>'consumer-products','hidden'=>0,'pricing_templates_id'=>1]);
        $product=Product::factory()->create(['group'=>$group->id]);
        $plan=Plan::factory()->create(['product'=>$product->id,'days'=>30]);
        $planPrice=PlanPrice::factory()->create(['plan_id'=>$plan->id,'add_price'=>'500','price_description'=>'GoodProduct','no_of_agents'=>7]);
        $response = $this->getPrivateMethod($this->con1, 'getmonthPriceDescription', [$product->id]);
        $this->assertEquals($response,"per month for <strong> $planPrice->no_of_agents agent</strong>");
    }


    public function test_store_get_url(){
        $user=User::factory()->create();
        $user=User::factory()->create();
        $this->actingAs($user);
        $this->withoutMiddleware();
        $group=ProductGroup::create(['name'=>'consumer-products','hidden'=>0,'pricing_templates_id'=>1]);
        $product=Product::factory()->create(['group'=>$group->id]);
        $plan=Plan::factory()->create(['product'=>$product->id,'days'=>30]);
        $planPrice=PlanPrice::factory()->create(['plan_id'=>$plan->id,'add_price'=>'500','price_description'=>'GoodProduct','no_of_agents'=>7]);
        $orderButton = 'btn-dark';
        $highlight=false;
        $response = $this->getPrivateMethod($this->con1, 'generateProductUrl', [$product,$orderButton,$highlight]);
        $this->assertEquals($response,'<input type="submit" value="Order Now" class="btn '.$orderButton.' btn-modern buttonsale"></form>' );
    }


    public function test_store_when_product_registered_in_cloud(){
        $user=User::factory()->create();
        $user=User::factory()->create();
        $this->actingAs($user);
        $this->withoutMiddleware();
        $group=ProductGroup::create(['name'=>'consumer-products','hidden'=>0,'pricing_templates_id'=>1]);
        $product=Product::factory()->create(['group'=>$group->id]);
        $plan=Plan::factory()->create(['product'=>$product->id,'days'=>30]);
        $planPrice=PlanPrice::factory()->create(['plan_id'=>$plan->id,'add_price'=>'500','price_description'=>'GoodProduct','no_of_agents'=>7]);
        $cloudproduct=CloudProducts::create(['cloud_product'=>$product->id,'cloud_free_plan'=>$plan->id]);
        $orderButton = 'btn-dark';
        $highlight=false;
        $response = $this->getPrivateMethod($this->con1, 'generateProductUrl', [$product,$orderButton,$highlight]);
        $this->assertEquals($response,'<button class="btn '.$orderButton.' btn-modern buttonsale" data-toggle="modal" data-target="#tenancy" data-mydata="'.$product->id.'">
                                <span style="white-space: nowrap;">Order Now</span>
                            </button>');
    }


    public function test_store_get_offer_price_monthly(){
        $user=User::factory()->create();
        $user=User::factory()->create();
        $this->actingAs($user);
        $this->withoutMiddleware();
        $group=ProductGroup::create(['name'=>'consumer-products','hidden'=>0,'pricing_templates_id'=>1]);
        $product=Product::factory()->create(['group'=>$group->id]);
        $plan=Plan::factory()->create(['product'=>$product->id,'days'=>30]);
        $planPrice=PlanPrice::factory()->create(['plan_id'=>$plan->id,'add_price'=>'500','price_description'=>'GoodProduct','no_of_agents'=>7,'offer_price'=>'100']);
        $cloudproduct=CloudProducts::create(['cloud_product'=>$product->id,'cloud_free_plan'=>$plan->id]);
        $orderButton = 'btn-dark';
        $highlight=false;
        $response = $this->getPrivateMethod($this->con1, 'getOfferprice', [$product->id]);
        $this->assertEquals($response['30_days'],$planPrice->offer_price);
    }

    public function test_store_get_offer_price_yearly(){
        $user=User::factory()->create();
        $user=User::factory()->create();
        $this->actingAs($user);
        $this->withoutMiddleware();
        $group=ProductGroup::create(['name'=>'consumer-products','hidden'=>0,'pricing_templates_id'=>1]);
        $product=Product::factory()->create(['group'=>$group->id]);
        $plan=Plan::factory()->create(['product'=>$product->id,'days'=>365]);
        $planPrice=PlanPrice::factory()->create(['plan_id'=>$plan->id,'add_price'=>'500','price_description'=>'GoodProduct','no_of_agents'=>7,'offer_price'=>'100']);
        $cloudproduct=CloudProducts::create(['cloud_product'=>$product->id,'cloud_free_plan'=>$plan->id]);
        $orderButton = 'btn-dark';
        $highlight=false;
        $response = $this->getPrivateMethod($this->con1, 'getOfferprice', [$product->id]);
        $this->assertEquals($response['365_days'],$planPrice->offer_price);
    }

}
