<?php

namespace Tests\Unit\Backend\Agent\Payment;

use App\Model\Payment\PromoProductRelation;
use App\Model\Payment\Promotion;
use App\Model\Payment\PromotionType;
use App\Model\Product\Product;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Date;
use Tests\DBTestCase;

class PromotionControllerTest extends DBTestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        $this->getLoggedInUser('admin');
    }

    /**
     * Helper: create type + product + base payload.
     */
    private function makePromotionPayload(array $overrides = []): array
    {
        $product = $overrides['product'] ?? Product::factory()->create();
        $type = $overrides['type'] ?? random_int(1, 2);
        $rawValue = $overrides['value'] ?? random_int(5, 80);

        return array_merge([
            'code' => $overrides['code'] ?? 'TEST50',
            'type' => $type,
            'value' => $rawValue,
            'uses' => $overrides['uses'] ?? 10,
            'start' => Date::now()->toDateTimeString(),
            'expiry' => Date::now()->addDays(10)->toDateTimeString(),
            'applied' => $product->id,
        ], $overrides);
    }

    public function test_it_returns_paginated_promotions_with_default_params(): void
    {
        Promotion::factory()->count(15)->create();

        $response = $this->getJson('/promotions');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'data',
                    'per_page',
                    'current_page',
                ],
            ])
            ->assertJsonFragment(['success' => true]);
    }

    public function test_it_respects_limit_parameter_in_get_all_promotions(): void
    {
        Promotion::factory()->count(5)->create();

        $response = $this->getJson('/promotions?limit=2');

        $response->assertStatus(200)
            ->assertJsonCount(2, 'data.data');
    }

    public function test_it_filters_promotions_by_code_search_query(): void
    {
        Promotion::factory()->create(['code' => 'SPECIAL2025']);
        Promotion::factory()->create(['code' => 'OTHER']);

        $response = $this->getJson('/promotions?search-query=SPECIAL');

        $response->assertStatus(200)
            ->assertJsonFragment(['success' => true])
            ->assertJsonFragment(['code' => 'SPECIAL2025'])
            ->assertJsonMissing(['code' => 'OTHER']);
    }

    public function test_it_filters_promotions_by_product_name(): void
    {
        $productMatch = Product::factory()->create(['name' => 'Pro Helpdesk']);
        $productNoMatch = Product::factory()->create(['name' => 'Something Else']);

        $promoMatch = Promotion::factory()->create(['code' => 'P_MATCH']);
        $promoNoMatch = Promotion::factory()->create(['code' => 'P_NO_MATCH']);

        PromoProductRelation::create([
            'promotion_id' => $promoMatch->id,
            'product_id' => $productMatch->id,
        ]);

        PromoProductRelation::create([
            'promotion_id' => $promoNoMatch->id,
            'product_id' => $productNoMatch->id,
        ]);

        $response = $this->getJson('/promotions?search-query=Helpdesk');

        $response->assertStatus(200)
            ->assertJsonFragment(['code' => 'P_MATCH'])
            ->assertJsonMissing(['code' => 'P_NO_MATCH']);
    }

    public function test_it_filters_promotions_by_promotion_type_name(): void
    {
        $typePercentage = PromotionType::where('name', 'Percentage')->first();
        $typeFixedAmount = PromotionType::where('name', 'Fixed Amount')->first();

        Promotion::factory()->create([
            'code' => 'DISC01',
            'type' => $typePercentage->id,
        ]);

        Promotion::factory()->create([
            'code' => 'GIFT01',
            'type' => $typeFixedAmount->id,
        ]);

        $response = $this->getJson('/promotions?search-query=Percentage');

        $response->assertStatus(200)
            ->assertJsonFragment(['code' => 'DISC01'])
            ->assertJsonMissing(['code' => 'GIFT01']);
    }

    public function test_it_sorts_promotions_by_code_desc(): void
    {
        Promotion::factory()->create(['code' => 'AAAA']);
        Promotion::factory()->create(['code' => 'ZZZZ']);

        $response = $this->getJson('/promotions?sort-field=code&sort-order=desc&limit=10');

        $response->assertStatus(200)
            ->assertJsonFragment(['success' => true]);

        $codes = array_column($response->json('data.data'), 'code');

        // first should be 'ZZZZ' if sorted desc
        $this->assertEquals('ZZZZ', $codes[0]);
    }

    public function test_it_returns_single_promotion_with_relations(): void
    {
        $typePercentage = PromotionType::where('name', 'Percentage')->first();
        $product = Product::factory()->create(['name' => 'Cloud Helpdesk']);

        $promotion = Promotion::factory()->create([
            'code' => 'SINGLE',
            'type' => $typePercentage->id,
        ]);

        PromoProductRelation::create([
            'promotion_id' => $promotion->id,
            'product_id' => $product->id,
        ]);

        $response = $this->getJson('/promotion/'.$promotion->id);

        $response->assertStatus(200)
            ->assertJsonFragment(['id' => $promotion->id])
            ->assertJsonFragment(['code' => 'SINGLE'])
            ->assertJsonFragment(['name' => 'Percentage'])
            ->assertJsonFragment(['name' => 'Cloud Helpdesk']);
    }

    public function test_it_returns_error_response_when_promotion_not_found(): void
    {
        $response = $this->getJson('/promotion/999999');

        $response->assertStatus(400)
            ->assertJsonFragment(['success' => false]);
    }

    public function test_it_creates_percentage_promotion_code_successfully(): void
    {
        $payload = $this->makePromotionPayload([
            'type' => 1, // 1 means percentage
            'value' => 50,
        ]);

        $response = $this->putJson('/promotionCreate', $payload);

        $response->assertStatus(200)
            ->assertJsonFragment([
                'success' => true,
                'message' => __('message.created-successfully'),
            ]);

        $this->assertDatabaseHas('promotions', [
            'code' => $payload['code'],
            'value' => '50%',
        ]);

        $this->assertDatabaseHas('promo_product_relations', [
            'product_id' => $payload['applied'],
        ]);
    }

    public function test_it_creates_flat_promotion_code_for_type_two(): void
    {
        $product = Product::factory()->create();
        $type = PromotionType::create(['name' => 'Flat']);

        $payload = $this->makePromotionPayload([
            'code' => 'FLAT100',
            'type' => $type->id,
            'value' => 100,
            'applied' => $product->id,
        ]);

        $response = $this->putJson('/promotionCreate', $payload);

        $response->assertStatus(200)
            ->assertJsonFragment([
                'success' => true,
                'message' => __('message.created-successfully'),
            ]);

        $this->assertDatabaseHas('promotions', [
            'code' => 'FLAT100',
            'value' => 100,
        ]);
    }

    public function test_it_fails_to_create_promotion_with_invalid_payload(): void
    {
        $response = $this->putJson('/promotionCreate', [
            'code' => '',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['code', 'type', 'value']);
    }

    public function test_it_updates_promotion_code_with_percentage_value(): void
    {
        $product = Product::factory()->create();
        $type1 = PromotionType::create(['name' => 'Percentage']);
        $type2 = PromotionType::create(['name' => 'Flat']);

        $promotion = Promotion::factory()->create([
            'code' => 'OLD',
            'type' => $type2->id,
            'value' => 99,
            'uses' => 1,
        ]);

        PromoProductRelation::create([
            'promotion_id' => $promotion->id,
            'product_id' => $product->id,
        ]);

        $payload = [
            'code' => 'NEWPERCENT',
            'type' => $type1->id,
            'value' => 30,
            'uses' => 5,
            'start' => Date::now()->toDateTimeString(),
            'expiry' => Date::now()->addDays(7)->toDateTimeString(),
            'applied' => $product->id,
        ];

        $response = $this->patchJson('/updatePromotion/'.$promotion->id, $payload);

        $response->assertStatus(200)
            ->assertJsonFragment([
                'success' => true,
                'message' => __('message.updated-successfully'),
            ]);

        $this->assertDatabaseHas('promotions', [
            'id' => $promotion->id,
            'code' => 'NEWPERCENT',
            'value' => '30%',
        ]);

        $this->assertDatabaseHas('promo_product_relations', [
            'promotion_id' => $promotion->id,
            'product_id' => $product->id,
        ]);
    }

    public function test_it_updates_promotion_code_with_flat_value_for_type_two(): void
    {
        $product = Product::factory()->create();
        $type2 = PromotionType::create(['name' => 'Flat']);

        $promotion = Promotion::factory()->create([
            'code' => 'OLD_FLAT',
            'type' => $type2->id,
            'value' => 10,
            'uses' => 1,
        ]);

        PromoProductRelation::create([
            'promotion_id' => $promotion->id,
            'product_id' => $product->id,
        ]);

        $payload = [
            'code' => 'NEW_FLAT',
            'type' => $type2->id,   // 2 => store value as int
            'value' => 200,
            'uses' => 2,
            'start' => Date::now()->toDateTimeString(),
            'expiry' => Date::now()->addDays(3)->toDateTimeString(),
            'applied' => $product->id,
        ];

        $response = $this->patchJson('/updatePromotion/'.$promotion->id, $payload);

        $response->assertStatus(200)
            ->assertJsonFragment([
                'success' => true,
                'message' => __('message.updated-successfully'),
            ]);

        $this->assertDatabaseHas('promotions', [
            'id' => $promotion->id,
            'code' => 'NEW_FLAT',
            'value' => 200,
        ]);
    }

    public function test_it_returns_error_when_updating_non_existing_promotion(): void
    {
        $type = PromotionType::create();

        $payload = [
            'code' => 'NOPE',
            'type' => $type->id,
            'value' => 10,
            'uses' => 1,
            'start' => Date::now()->toDateTimeString(),
            'expiry' => Date::now()->addDays(1)->toDateTimeString(),
            'applied' => Product::factory()->create()->id,
        ];

        $response = $this->patchJson('/updatePromotion/999999', $payload);

        $response->assertStatus(400)
            ->assertJsonFragment(['success' => false]);
    }

    public function test_it_fails_to_update_promotion_with_invalid_payload(): void
    {
        $promotion = Promotion::factory()->create();

        $response = $this->patchJson('/updatePromotion/'.$promotion->id, [
            'code' => '',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['code', 'type', 'value']);
    }

    public function test_it_deletes_bulk_promotions_successfully(): void
    {
        $p1 = Promotion::factory()->create();
        $p2 = Promotion::factory()->create();

        $response = $this->deleteJson('/promotions', [
            'select' => [$p1->id, $p2->id],
        ]);

        $response->assertStatus(200)
            ->assertJsonFragment([
                'success' => true,
                'message' => __('message.deleted-successfully'),
            ]);

        $this->assertDatabaseMissing('promotions', ['id' => $p1->id]);
        $this->assertDatabaseMissing('promotions', ['id' => $p2->id]);
    }

    public function test_it_returns_error_if_delete_bulk_called_with_empty_selection(): void
    {
        $response = $this->deleteJson('/promotions', [
            'select' => [],
        ]);

        $response->assertStatus(400)
            ->assertJsonFragment([
                'success' => false,
                'message' => __('message.select-a-row'),
            ]);
    }

    public function test_it_ignores_invalid_ids_in_bulk_delete_but_deletes_valid_ones(): void
    {
        $p1 = Promotion::factory()->create();

        $response = $this->deleteJson('/promotions', [
            'select' => [$p1->id, 999999],
        ]);

        $response->assertStatus(200)
            ->assertJsonFragment([
                'success' => true,
                'message' => __('message.deleted-successfully'),
            ]);

        $this->assertDatabaseMissing('promotions', ['id' => $p1->id]);
    }
}
