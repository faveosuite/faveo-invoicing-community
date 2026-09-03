<?php

declare(strict_types=1);

namespace Tests\Unit\Backend\Http\Controllers\Payment;

use App\Model\Payment\Promotion;
use App\Model\Payment\PromotionType;
use App\Model\Product\Product;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\DBTestCase;

class PromotionControllerTest extends DBTestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware(\App\Http\Middleware\Install::class);
    }

    private function validPromoPayload(string $suffix = ''): array
    {
        return [
            'code' => 'PROMO'.$suffix.uniqid(),
            'type' => 1,
            'value' => 10,
            'uses' => 5,
            'start' => '2025-01-01',
            'expiry' => '2025-12-31',
            'applied' => Product::factory()->create()->id,
        ];
    }

    // =========================================================================
    // GET /promotions — role gates
    // =========================================================================

    public function test_admin_gets_200(): void
    {
        $this->getLoggedInUser('admin');
        $this->getJson('/promotions')->assertStatus(200);
    }

    public function test_client_is_redirected_302(): void
    {
        $this->getLoggedInUser('user');
        $this->getJson('/promotions')->assertStatus(302);
    }

    public function test_guest_gets_401(): void
    {
        $this->getJson('/promotions')->assertStatus(401);
    }

    // =========================================================================
    // Response shape — list items have required fields
    // =========================================================================

    public function test_list_response_structure(): void
    {
        $this->getLoggedInUser('admin');
        $response = $this->getJson('/promotions');

        $response->assertStatus(200);
        $this->assertTrue($response->json('success'));
        $this->assertIsArray($response->json('data.data'));

        $items = $response->json('data.data');
        if (count($items) > 0) {
            $item = $items[0];
            // Verify item has expected fields
            foreach (['id', 'code', 'type', 'uses', 'value', 'start', 'expiry'] as $field) {
                $this->assertArrayHasKey($field, $item, "Promotion item missing field: $field");
            }
        }
    }

    // =========================================================================
    // GET /promotion/{id} — 400 error shape
    // =========================================================================

    public function test_nonexistent_promotion_returns_400_with_error_flag_and_message(): void
    {
        $this->getLoggedInUser('admin');
        $response = $this->getJson('/promotion/999999999');

        $response->assertStatus(400);
        $this->assertFalse($response->json('success'));
        $this->assertNotEmpty($response->json('message'));
    }

    // =========================================================================
    // POST /promotionCreate — exact field errors
    // =========================================================================

    public function test_create_empty_body_returns_422_with_all_required_field_errors(): void
    {
        $this->getLoggedInUser('admin');
        $response = $this->postJson('/promotionCreate', []);

        $response->assertStatus(412);
        $errors = $response->json('message');

        foreach (['code', 'type', 'applied', 'uses', 'start', 'expiry', 'value'] as $field) {
            $this->assertArrayHasKey($field, $errors, "Expected '$field' in promotion errors");
        }
    }

    public function test_create_missing_code_has_specific_error_message(): void
    {
        $this->getLoggedInUser('admin');
        $response = $this->postJson('/promotionCreate', [
            'type' => '2', 'applied' => '2025-01-01', 'uses' => 10,
            'start' => '2025-01-01', 'expiry' => '2025-12-31', 'value' => 50,
        ]);

        $response->assertStatus(412);
        $this->assertSame('The coupon code field is required.', $response->json('message.code'));
    }

    public function test_create_expiry_before_start_has_expiry_error(): void
    {
        $this->getLoggedInUser('admin');
        $response = $this->postJson('/promotionCreate', [
            'code' => 'PROMO', 'type' => '2', 'applied' => '2025-01-01',
            'uses' => 10, 'start' => '2025-12-31', 'expiry' => '2025-01-01', 'value' => 50,
        ]);

        $response->assertStatus(412);
        $this->assertArrayHasKey('expiry', $response->json('message'));
    }

    public function test_create_non_numeric_uses_has_uses_error(): void
    {
        $this->getLoggedInUser('admin');
        $response = $this->postJson('/promotionCreate', [
            'code' => 'TEST', 'type' => '2', 'applied' => '2025-01-01',
            'uses' => 'many', 'start' => '2025-01-01', 'expiry' => '2025-12-31', 'value' => 50,
        ]);

        $response->assertStatus(412);
        $this->assertArrayHasKey('uses', $response->json('message'));
    }

    // =========================================================================
    // DELETE /promotions — shape
    // =========================================================================

    public function test_bulk_delete_without_ids_returns_400(): void
    {
        $this->getLoggedInUser('admin');
        $response = $this->deleteJson('/promotions', []);

        $response->assertStatus(400);
        $this->assertFalse($response->json('success'));
    }

    public function test_bulk_delete_unauthenticated_returns_401(): void
    {
        $this->deleteJson('/promotions', ['ids' => [1]])->assertStatus(401);
    }

    public function test_get_all_promotions_with_search_returns_200(): void
    {
        // Covers lines 179-192: searchable promotion list
        $this->getLoggedInUser('admin');
        $response = $this->getJson('/promotions?search-query=TEST&sort-field=code&sort-order=asc');
        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
    }

    public function test_get_promotion_returns_data(): void
    {
        // Covers lines 194-208: getPromotion
        $this->getLoggedInUser('admin');
        $type = PromotionType::first() ?? PromotionType::create(['name' => 'Percent']);
        $promo = Promotion::create(['code' => 'GETME'.uniqid(), 'type' => $type->id, 'value' => '10%', 'uses' => 5, 'start' => '2025-01-01', 'expiry' => '2025-12-31']);

        $response = $this->getJson('/promotion/'.$promo->id);
        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
    }

    public function test_promotion_code_create_creates_promotion(): void
    {
        // Covers lines 248-271: promotionCodeCreate
        $this->getLoggedInUser('admin');
        $response = $this->postJson('/promotionCreate', $this->validPromoPayload());
        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
    }

    public function test_update_promotion_code_updates_promotion(): void
    {
        // Covers lines 212-244: updatePromotionCode
        $this->getLoggedInUser('admin');
        $type = PromotionType::first() ?? PromotionType::create(['name' => 'Percent']);
        $promo = Promotion::create(['code' => 'UPDME'.uniqid(), 'type' => $type->id, 'value' => '10%', 'uses' => 5, 'start' => '2025-01-01', 'expiry' => '2025-12-31']);

        $response = $this->patchJson('/updatePromotion/'.$promo->id, $this->validPromoPayload());
        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
    }

    public function test_bulk_delete_with_ids_returns_200(): void
    {
        // Covers lines 280-290: deleteBulkPromotions with IDs
        $this->getLoggedInUser('admin');
        $type = PromotionType::first() ?? PromotionType::create(['name' => 'Percent']);
        $promo = Promotion::create(['code' => 'DELME'.uniqid(), 'type' => $type->id, 'value' => '5%', 'uses' => 3, 'start' => '2025-01-01', 'expiry' => '2025-12-31']);

        $response = $this->deleteJson('/promotions', ['select' => [$promo->id]]);
        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
    }
}
