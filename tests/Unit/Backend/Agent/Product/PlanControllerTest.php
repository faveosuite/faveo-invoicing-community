<?php

namespace Tests\Unit\Backend\Agent\Product;

use App\Model\Payment\Period;
use App\Model\Payment\Plan;
use App\Model\Payment\PlanPrice;
use App\Model\Product\CloudProducts;
use App\Model\Product\Product;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\DBTestCase;

class PlanControllerTest extends DBTestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        $this->getLoggedInUser('admin');
    }

    public function test_get_all_plans_successfully(): void
    {
        $product = Product::factory()->create();
        $plan = Plan::factory()->create(['product' => $product->id]);

        PlanPrice::factory()->create([
            'plan_id' => $plan->id,
            'currency' => 'INR',
        ]);

        $response = $this->getJson('/plans');

        $response
            ->assertStatus(200)
            ->assertJsonFragment(['id' => $plan->id]);
    }

    public function test_get_all_plans_with_search_filter(): void
    {
        $product = Product::factory()->create(['name' => 'Billing Pro']);
        $plan = Plan::factory()->create(['name' => 'Yearly Plan', 'product' => $product->id]);

        PlanPrice::factory()->create([
            'plan_id' => $plan->id,
            'currency' => 'USD',
        ]);

        $response = $this->getJson('/plans?search-query=Year');

        $response->assertStatus(200);
        $response->assertJsonFragment(['name' => 'Yearly Plan']);
    }

    public function test_get_all_plans_pagination(): void
    {
        Plan::factory()->count(25)->create();

        $response = $this->getJson('/plans?limit=20');

        $response->assertStatus(200)
            ->assertJsonCount(20, 'data.data');
    }

    public function test_create_plan_successfully(): void
    {
        $product = Product::factory()->create();

        $payload = [
            'name' => 'Test Plan',
            'product' => $product->id,
            'days' => 30,
            'add_price' => [100],
            'renew_price' => [120],
            'offer_price' => [90],
            'currency' => ['INR'],
            'price_description' => 'Test description',
            'product_quantity' => 1,
            'no_of_agents' => 3,
        ];

        $response = $this->putJson('/plans', $payload);

        $response->assertStatus(200)
            ->assertJsonFragment(['message' => __('message.saved-successfully')]);

        $this->assertDatabaseHas('plans', ['name' => 'Test Plan']);
        $this->assertDatabaseHas('plan_prices', [
            'currency' => 'INR',
            'add_price' => 100,
        ]);
    }

    public function test_create_plan_duplicate_rejected(): void
    {
        $product = Product::factory()->create();
        Period::create(['days' => 30]);

        $plan = Plan::factory()->create([
            'product' => $product->id,
            'days' => 30,
        ]);

        CloudProducts::create([
            'cloud_product' => $product->id,
            'cloud_free_plan' => $plan->id,
        ]);

        $payload = [
            'name' => 'Duplicate Plan',
            'product' => $product->id,
            'days' => 30,
            'add_price' => [100],
            'renew_price' => [120],
            'offer_price' => [null],
            'currency' => ['INR'],
            'country_id' => [1],
            'price_description' => 'x',
            'product_quantity' => 1,
            'no_of_agents' => 3,
        ];

        $response = $this->putJson('/plans', $payload);

        $response->assertStatus(400)
            ->assertJsonFragment(['message' => 'Plan already exists']);
    }

    public function test_get_single_plan_success(): void
    {
        $product = Product::factory()->create();
        $plan = Plan::factory()->create(['product' => $product->id]);

        PlanPrice::factory()->create([
            'plan_id' => $plan->id,
            'currency' => 'USD',
            'product_quantity' => 2,
            'no_of_agents' => 5,
        ]);

        $response = $this->getJson('/plan/'.$plan->id);

        $response->assertStatus(200)
            ->assertJsonFragment(['id' => $plan->id])
            ->assertJsonFragment(['currency' => 'USD']);
    }

    public function test_get_plan_not_found(): void
    {
        $response = $this->getJson('/plan/999999');

        $response->assertStatus(400);
    }

    public function test_update_plan_successfully(): void
    {
        $product = Product::factory()->create();
        $original = Plan::factory()->create(['product' => $product->id]);

        Period::create(['days' => 30]);

        $payload = [
            'name' => 'Updated Plan',
            'product' => $product->id,
            'days' => 30,
            'add_price' => [200],
            'renew_price' => [250],
            'offer_price' => [null],
            'currency' => ['USD'],
            'country_id' => [1],
            'price_description' => 'Updated desc',
            'product_quantity' => 5,
            'no_of_agents' => 10,
        ];

        $response = $this->patchJson('/plan/'.$original->id, $payload);

        $response->assertStatus(200)
            ->assertJsonFragment(['message' => __('message.saved-successfully')]);

        $this->assertDatabaseHas('plans', ['id' => $original->id, 'name' => 'Updated Plan']);
        $this->assertDatabaseHas('plan_prices', ['plan_id' => $original->id, 'add_price' => 200]);
    }

    public function test_update_plan_not_found(): void
    {
        $response = $this->patchJson('/plan/999999', [
            'name' => 'Invalid',
            'product' => 1,
            'days' => 30,
            'add_price' => [100],
            'renew_price' => [120],
            'currency' => ['INR'],
            'country_id' => [1],
        ]);

        $response->assertStatus(422);
    }

    public function test_bulk_delete_plans_successfully(): void
    {
        $plans = Plan::factory()->count(3)->create();

        $ids = $plans->pluck('id')->toArray();

        $response = $this->deleteJson('/plans', ['select' => $ids]);

        $response->assertStatus(200)
            ->assertJsonFragment(['message' => __('message.deleted-successfully')]);

        foreach ($ids as $id) {
            $this->assertDatabaseMissing('plans', ['id' => $id]);
        }
    }

    public function test_bulk_delete_without_ids(): void
    {
        $response = $this->deleteJson('/plans', ['select' => []]);

        $response->assertStatus(400)
            ->assertJsonFragment(['message' => __('message.select-a-row')]);
    }
}
