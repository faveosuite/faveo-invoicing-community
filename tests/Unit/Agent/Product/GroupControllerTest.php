<?php

namespace Tests\Unit\Agent\Product;

use App\Model\Common\PricingTemplate;
use App\Model\Payment\Plan;
use App\Model\Product\Product;
use App\Model\Product\ProductGroup;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\DBTestCase;

class GroupControllerTest extends DBTestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        $this->getLoggedInUser('admin');
    }

    public function test_can_fetch_product_groups(): void
    {
        ProductGroup::factory()->count(5)->create();

        $response = $this->getJson('/groups?search-query=&limit=10');

        $response->assertStatus(200);
        $response->assertJsonStructure(['data']);
        $this->assertCount(5, $response->json('data.data'));
    }

    public function test_product_group_search_works(): void
    {
        ProductGroup::factory()->create(['name' => 'Alpha']);
        ProductGroup::factory()->create(['name' => 'Beta']);

        $response = $this->getJson('/groups?search-query=Alpha');

        $response->assertStatus(200);
        $this->assertEquals('Alpha', $response->json('data.data')[0]['name']);
    }

    public function test_can_get_single_group(): void
    {
        $template = PricingTemplate::query()->first();
        $group = ProductGroup::factory()->create(['pricing_templates_id' => $template->id]);

        Product::factory()->count(2)->create(['group' => $group->id]);

        $response = $this->getJson('/group/'.$group->id);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'id',
                'name',
                'pricing_template',
                'product',
            ]);
    }

    public function test_get_group_returns_error_if_not_found(): void
    {
        $response = $this->getJson('/group/999');

        $response->assertStatus(400);
    }

    public function test_group_can_be_created(): void
    {
        $template = PricingTemplate::query()->first();

        $payload = [
            'name' => 'New Group',
            'pricing_templates_id' => $template->id,
        ];

        $response = $this->putJson('/group', $payload);

        $response->assertStatus(200)
            ->assertJsonFragment(['message' => __('message.saved-successfully')]);

        $this->assertDatabaseHas('product_groups', ['name' => 'New Group']);
    }

    public function test_group_create_validation_fails(): void
    {
        $response = $this->putJson('/group', [
            'name' => '',
        ]);

        $response->assertStatus(422);
    }

    public function test_group_can_be_updated_when_all_products_have_monthly_and_yearly_plans(): void
    {
        $template = PricingTemplate::query()->first();

        $group = ProductGroup::factory()->create(['status' => 0]);

        $productA = Product::factory()->create(['group' => $group->id]);
        $productB = Product::factory()->create(['group' => $group->id]);

        // Monthly + yearly plans
        Plan::factory()->create(['product' => $productA->id, 'days' => 30]);
        Plan::factory()->create(['product' => $productA->id, 'days' => 365]);

        Plan::factory()->create(['product' => $productB->id, 'days' => 31]);
        Plan::factory()->create(['product' => $productB->id, 'days' => 366]);

        $response = $this->patchJson('/group/' . $group->id, [
            'pricing_templates_id' => $template->id,
            'name' => 'Updated Group',
            'status' => 1,
        ]);

        $response->assertStatus(200)
            ->assertJsonFragment(['message' => __('message.updated-successfully')]);

        $this->assertDatabaseHas('product_groups', ['name' => 'Updated Group']);
        $this->assertDatabaseHas('products', ['group' => $group->id, 'status' => 1]);
    }

    public function test_group_update_fails_if_products_missing_monthly_or_yearly_plans(): void
    {
        $template = PricingTemplate::query()->first();

        $group = ProductGroup::factory()->create(['status' => 0]);

        $product = Product::factory()->create(['group' => $group->id]);

        // Only monthly plan exists
        Plan::factory()->create(['product' => $product->id, 'days' => 30]);

        $response = $this->patchJson('/group/' . $group->id, [
            'pricing_templates_id' => $template->id,
            'name' => 'Bad Update',
            'status' => 1,
        ]);

        $response->assertStatus(400);
        $response->assertJson(['message' => __('message.all_products_monthly_yearly_plan')]);
    }

    public function test_group_can_be_disabled_even_if_plans_missing(): void
    {
        $template = PricingTemplate::query()->first();

        $group = ProductGroup::factory()->create(['status' => 1]);

        Product::factory()->create(['group' => $group->id]);

        // No plans at all

        $response = $this->patchJson('/group/' . $group->id, [
            'pricing_templates_id' => $template->id,
            'name' => 'Disabled Group',
            'status' => 0,
        ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('product_groups', ['status' => 0]);
    }

    public function test_bulk_group_delete_works(): void
    {
        $groups = ProductGroup::factory()->count(3)->create();

        $ids = $groups->pluck('id')->toArray();

        $response = $this->deleteJson('/group', ['select' => $ids]);

        $response->assertStatus(200);

        foreach ($ids as $id) {
            $this->assertDatabaseMissing('product_groups', ['id' => $id]);
        }
    }

    public function test_bulk_delete_requires_ids(): void
    {
        $response = $this->deleteJson('/group', ['select' => []]);

        $response->assertStatus(400)
            ->assertJsonFragment([
                'message' => __('message.select-a-row'),
            ]);
    }
}
