<?php

declare(strict_types=1);

namespace Tests\Unit\Backend\Http\Controllers\Product;

use App\Model\Product\Product;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\DBTestCase;

class ProductPluginControllerTest extends DBTestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware();
        $this->getLoggedInUser('admin');
    }

    public function test_index_returns_plugins_for_product(): void
    {
        $product = Product::factory()->create();

        $response = $this->getJson('/product/'.$product->id.'/plugins');

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
        $response->assertJsonStructure(['data' => ['plugins']]);
    }

    public function test_index_returns_400_for_nonexistent_product(): void
    {
        $response = $this->getJson('/product/999999999/plugins');

        $response->assertStatus(400);
    }

    public function test_sync_updates_plugin_associations(): void
    {
        $product = Product::factory()->create();

        $response = $this->postJson('/product/'.$product->id.'/plugins', [
            'bundled' => [],
            'compatible' => [],
        ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
    }
}
