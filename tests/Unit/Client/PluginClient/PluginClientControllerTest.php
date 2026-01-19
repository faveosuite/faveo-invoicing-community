<?php
namespace Tests\Unit\Client\PluginClient;

use App\Model\Configure\ConfigGroup;
use App\Model\Configure\ConfigOption;
use App\Model\Configure\ConfigOptionValue;
use App\Model\Configure\PluginCompatibleWithProducts;
use App\Model\Configure\ProductPluginGroup;
use App\Model\Payment\Plan;
use App\Model\Payment\PlanPrice;
use App\Model\Product\Product;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use App\Facades\Cart;

use Tests\DBTestCase;

class PluginClientControllerTest extends DBTestCase{

    public $cart;
    public function setup():void
    {
        parent::setup();
        $this->withoutMiddleware();
        $this->getLoggedInUser('user');
        $this->cart=new Cart();
    }

    public function testAddGroupToCart(){
        $product = Product::factory()->create();
        $plan = Plan::create(['name' => 'HD Plan 1 year', 'product' => $product->id, 'days' => 366]);
        $planPrice = PlanPrice::create(['plan_id' => $plan->id, 'currency' => 'USD', 'add_price' => '1000', 'renew_price' => '500', 'price_description' => 'Random description', 'product_quantity' => 1, 'no_of_agents' => 0]);
        $currency = 'USD';
        $group=ConfigGroup::create(['config_group_name'=>'helpdesk Advanced','description'=>'It is a good product','plan_id'=>$plan->id,'product_id'=>$product->id]);

        $this->cart->add(
            $plan->id,
            $product->name,
            1000,
            1,
            ['currency' => $currency, 'symbol' => $currency, 'agents' => 10],
        );

        $response=$this->call('post','add-groupTo-cart',['group_id'=>$group->id]);
        $content=$this->cart->getContent();
        $this->assertEquals($group->id,$content[$plan->id]['group']['groupId']);
        $response->assertStatus(200);
        $content=json_decode($response->getContent());
        $this->assertEquals('true',$content->success);
    }


    public function testRemoveGroupFromCart(){
        $product = Product::factory()->create();
        $plan = Plan::create(['name' => 'HD Plan 1 year', 'product' => $product->id, 'days' => 366]);
        $planPrice = PlanPrice::create(['plan_id' => $plan->id, 'currency' => 'USD', 'add_price' => '1000', 'renew_price' => '500', 'price_description' => 'Random description', 'product_quantity' => 1, 'no_of_agents' => 0]);
        $currency = 'USD';
        $group=ConfigGroup::create(['config_group_name'=>'helpdesk Advanced','description'=>'It is a good product','plan_id'=>$plan->id,'product_id'=>$product->id]);

        $this->cart->add(
            $plan->id,
            $product->name,
            1000,
            1,
            ['currency' => $currency, 'symbol' => $currency, 'agents' => 10],
            null,$product,['groupId'=>'23']
        );

        $response=$this->call('post','remove-groupFrom-cart',['group_id'=>$group->id]);
        $content=$this->cart->getContent();
        $this->assertEquals([],$content[$plan->id]['group']);
        $response->assertStatus(200);
        $content=json_decode($response->getContent());
        $this->assertEquals('true',$content->success);
    }


    public function testGetPlugins(){
        $product = Product::factory()->create();
        $plugin = Product::factory()->create(['shoping_cart_link'=>'https//:example.com','name'=>'test_prod']);
        PluginCompatibleWithProducts::create(['plugin_id'=>$plugin->id,'product_id'=>$product->id]);
        $response=$this->call('get','get-plugins',['id'=>$product->id]);
        $response->assertStatus(200);
        $content= json_decode($response->getContent());
        $data=(array) $content->data;
        $this->assertEquals($plugin->id,$data[0]->id);
    }


    public function testCheckProduct(){
        $product = Product::factory()->create();
        $plugin = Product::factory()->create(['shoping_cart_link'=>'https//:example.com','name'=>'test_prod']);
        PluginCompatibleWithProducts::create(['plugin_id'=>$plugin->id,'product_id'=>$product->id]);
        $response=$this->call('post','check-product',['product_id'=>$product->id,'plugin_id'=>$plugin->id]);
        $response->assertStatus(200);
        $content=json_decode($response->getContent());
        $this->assertEquals('true',$content->message);
    }


    public function testCheckProductFailure(){
        $product = Product::factory()->create();
        $product1 = Product::factory()->create();

        $plugin = Product::factory()->create(['shoping_cart_link'=>'https//:example.com','name'=>'test_prod']);
        PluginCompatibleWithProducts::create(['plugin_id'=>$plugin->id,'product_id'=>$product->id]);
        $response=$this->call('post','check-product',['product_id'=>$product1->id,'plugin_id'=>$plugin->id]);
        $response->assertStatus(200);
        $content=json_decode($response->getContent());
        $this->assertEquals('false',$content->message);
    }

    public function testGetGroupsWithProduct(){
        $plan=Plan::factory()->create();
        $product=Product::factory()->create();
        $group=ConfigGroup::create(['config_group_name'=>'helpdesk Advanced','description'=>'It is a good product','plan_id'=>$plan->id,'product_id'=>$product->id]);

        $configOption=ConfigOption::create(['config_option_name'=>'first','config_option_description'=>'first','group_id'=>$group->id,'product_id'=>$product->id,'plan_id'=>$plan->id]);
        $response=$this->call('get','get-groupsWith-options',['product_id'=>$product->id]);
        $response->assertStatus(200);
        $content=json_decode($response->getContent());
        $data=(array) $content->data;
        $this->assertEquals($group->id,$data[$group->id][0]->group_id);
    }

    public function testGetOptionKeyValue(){
        $plan=Plan::factory()->create();
        $product=Product::factory()->create();
        $group=ConfigGroup::create(['config_group_name'=>'helpdesk Advanced','description'=>'It is a good product','plan_id'=>$plan->id,'product_id'=>$product->id]);

        $configOption=ConfigOption::create(['config_option_name'=>'first','config_option_description'=>'first','group_id'=>$group->id,'product_id'=>$product->id,'plan_id'=>$plan->id]);
        $value=ConfigOptionValue::create(['option_id'=>$configOption->id,'key'=>'name','value'=>'tester']);
        $response=$this->call('get','getOptions-key-value',['option_id'=>$configOption->id]);
        $response->assertStatus(200);
        $content=json_decode($response->getContent());
        $data=(array) $content->data;
        $this->assertEquals($value->value,$data[0]->name);
    }

    public function testRemoveWithGroupedProductId(){
        $product = Product::factory()->create();
        $plan = Plan::create(['name' => 'HD Plan 1 year', 'product' => $product->id, 'days' => 366]);
        $planPrice = PlanPrice::create(['plan_id' => $plan->id, 'currency' => 'USD', 'add_price' => '1000', 'renew_price' => '500', 'price_description' => 'Random description', 'product_quantity' => 1, 'no_of_agents' => 0]);
        $currency = 'USD';
        $group=ConfigGroup::create(['config_group_name'=>'helpdesk Advanced','description'=>'It is a good product','plan_id'=>$plan->id,'product_id'=>$product->id]);

        $this->cart->add(
            $plan->id,
            $product->name,
            1000,
            1,
            ['currency' => $currency, 'symbol' => $currency, 'agents' => 10],
            null,$product,['groupId'=>'23'],1234,
        );

        $response=$this->call('post','remove-whole-group',['groupedProductId'=>1234]);
        $response->assertStatus(200);
        $content=json_decode($response->getContent());
        $this->assertEquals("Removed Successfully",$content->message);
    }


}