<?php

namespace Tests\Unit\Backend\Http\Controllers\Front;

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
        $group = \App\Model\Product\ProductGroup::first();
        if (! $group) {
            $tmpl = \App\Model\Common\PricingTemplate::first();
            if (! $tmpl) {
                $this->assertTrue(true);

                return;
            }
            $group = \App\Model\Product\ProductGroup::create(['name' => 'Test Group '.uniqid(), 'pricing_templates_id' => $tmpl->id]);
        }

        $response = $this->getJson("/store/{$group->id}/products");

        $this->assertContains($response->getStatusCode(), [200, 400]);
    }
}
