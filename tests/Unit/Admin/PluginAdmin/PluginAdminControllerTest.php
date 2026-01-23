<?php

namespace Tests\Unit\Admin\PluginAdmin;

use App\Model\Configure\ConfigGroup;
use App\Model\Configure\ConfigOption;
use App\Model\Configure\PluginCompatibleWithProducts;
use App\Model\Configure\ProductPluginGroup;
use App\Model\Payment\Plan;
use App\Model\Product\Product;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\DBTestCase;

class PluginAdminControllerTest extends DBTestCase
{
    use DatabaseTransactions;

    public function setup(): void
    {
        parent::setup();
        $this->withoutMiddleware();
        $this->getLoggedInUser('admin');
    }

    public function testGetPluginProducts()
    {
        $products = Product::factory(10)->create();
        $allProd = $products->pluck('id', 'name')->toArray();
        $response = $this->call('get', 'get-plugin-products');
        $response->assertStatus(200);
        $content = json_decode($response->getContent());
        $data = (array) $content->data;
        $this->assertEqualsCanonicalizing($allProd, $data);
    }

    public function testGetCompatiblePlugins()
    {
        $product = Product::factory()->create();
        $plugin = Product::factory()->create();
        $allProd = $plugin->where('id', $plugin->id)->pluck('id', 'name')->toArray();
        PluginCompatibleWithProducts::create(['product_id' => $product->id, 'plugin_id' => $plugin->id]);
        $response = $this->call('get', 'get-compatible-plugins', ['id' => $product->id]);
        $content = json_decode($response->getContent());
        $data = (array) $content->data;
        $this->assertEqualsCanonicalizing($allProd, $data);
    }

    public function testAddingPlugins()
    {
        $product = Product::factory()->create();
        $plugin1 = Product::factory()->create();
        $plugin2 = Product::factory()->create();
        $response = $this->call('post', 'add-plugins', ['product_id' => $product->id, 'plugin_ids' => [$plugin1->id, $plugin2->id]]);
        $response->assertStatus(200);
        $content = json_decode($response->getContent());
        $this->assertEquals('true', $content->success);
    }

    public function testDeletePlugins()
    {
        $product = Product::factory()->create();
        $plugin = Product::factory()->create();
        PluginCompatibleWithProducts::create(['product_id' => $product->id, 'plugin_id' => $plugin->id]);
        $response = $this->call('delete', 'delete-plugins', ['plugin_ids' => [$plugin->id], 'product_id' => $product->id]);
        $response->assertStatus(200);
        $content = json_decode($response->getContent());
        $this->assertEquals('true', $content->success);
    }

    public function testProductPluginGrouping()
    {
        $product = Product::factory()->create();
        $plugin = Product::factory()->create();
        $response = $this->call('post', 'addTo-group', ['product_id' => $product->id, 'plugin_ids' => [$plugin->id]]);
        $response->assertStatus(200);
        $content = json_decode($response->getContent());
        $this->assertEquals('true', $content->success);
    }

    public function testRemoveFromGroup()
    {
        $product = Product::factory()->create();
        $plugin = Product::factory()->create();
        $group = ProductPluginGroup::create(['product_id' => $product->id, 'plugin_id' => $plugin->id]);
        $response = $this->call('post', 'removeFrom-group', ['group_ids' => [$group->id]]);
        $response->assertStatus(200);
        $content = json_decode($response->getContent());
        $this->assertEquals('true', $content->success);
    }

    public function testGetGroup()
    {
        $product = Product::factory()->create();
        $plugin = Product::factory()->create();
        $group = ProductPluginGroup::create(['product_id' => $product->id, 'plugin_id' => $plugin->id]);
        $response = $this->call('get', 'get-group', ['product_id' => $product->id]);
        $response->assertStatus(200);
        $content = json_decode($response->getContent());
        $content = (array) $content;
        $data = (array) $content['data'];
        $this->assertEquals($plugin->name, $data[$group->id]);
    }

    public function testCreateGroup()
    {
        $product = Product::factory()->create();
        $response = $this->call('post', 'create-group', ['group_name' => 'Helpdesk', 'group_description' => 'This is a good group.', 'product_id' => $product->id,
            'add_price' => [200], 'offer_price' => [200], 'renew_price' => [200], 'name' => 'new plan']);
        $response->assertStatus(200);
        $content = json_decode($response->getContent());
        $this->assertEquals('true', $content->success);
    }

    public function testGroupDeletionWithPlan()
    {
        $plan = Plan::create(['name' => 'Helpdesk Plan']);
        $group = ConfigGroup::create(['config_group_name' => 'helpdesk Advanced', 'description' => 'It is a good product', 'plan_id' => $plan->id]);
        $response = $this->call('delete', 'delete-group', ['group_id' => $group->id]);
        $response->assertStatus(200);
        $content = json_decode($response->getContent());
        $this->assertEquals('true', $content->success);
        $this->assertDatabaseMissing('config_group', ['id' => $group->id]);
    }

    public function testCreateOption()
    {
        $plan = Plan::factory()->create();
        $product = Product::factory()->create();
        $group = ConfigGroup::create(['config_group_name' => 'helpdesk Advanced', 'description' => 'It is a good product', 'plan_id' => $plan->id, 'product_id' => $product->id]);
        $response = $this->call('post', 'create-option', ['group_id' => $group->id, 'option_name' => 'new option', 'option_description' => 'option description',
            'config_option_key_value' => ['name' => 'testing']]);
        $response->assertStatus(200);
        $this->assertDatabaseHas('config_option', ['group_id' => $group->id]);
        $this->assertDatabaseHas('config_option_values', ['key' => 'name']);
    }

    public function testOptionDeletion()
    {
        $plan = Plan::factory()->create();
        $product = Product::factory()->create();
        $group = ConfigGroup::create(['config_group_name' => 'helpdesk Advanced', 'description' => 'It is a good product', 'plan_id' => $plan->id, 'product_id' => $product->id]);

        $configOption = ConfigOption::create(['config_option_name' => 'first', 'config_option_description' => 'first', 'group_id' => $group->id, 'product_id' => $product->id, 'plan_id' => $plan->id]);
        $response = $this->call('delete', 'delete-option', ['option_ids' => [$configOption->id]]);
        $response->assertStatus(200);
        $content = json_decode($response->getContent());
        $this->assertEquals('true',$content->success);
    }
}
