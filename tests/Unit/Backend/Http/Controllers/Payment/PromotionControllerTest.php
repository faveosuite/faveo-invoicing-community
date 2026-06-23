<?php

declare(strict_types=1);

namespace Tests\Unit\Backend\Http\Controllers\Payment;

use Tests\DBTestCase;

class PromotionControllerTest extends DBTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware(\App\Http\Middleware\Install::class);
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
    // PUT /promotionCreate — exact field errors
    // =========================================================================

    public function test_create_empty_body_returns_422_with_all_required_field_errors(): void
    {
        $this->getLoggedInUser('admin');
        $response = $this->putJson('/promotionCreate', []);

        $response->assertStatus(422);
        $errors = $response->json('errors');

        foreach (['code', 'type', 'applied', 'uses', 'start', 'expiry', 'value'] as $field) {
            $this->assertArrayHasKey($field, $errors, "Expected '$field' in promotion errors");
        }
    }

    public function test_create_missing_code_has_specific_error_message(): void
    {
        $this->getLoggedInUser('admin');
        $response = $this->putJson('/promotionCreate', [
            'type' => '2', 'applied' => '2025-01-01', 'uses' => 10,
            'start' => '2025-01-01', 'expiry' => '2025-12-31', 'value' => 50,
        ]);

        $response->assertStatus(422);
        $this->assertSame('The coupon code field is required.', $response->json('errors.code.0'));
    }

    public function test_create_expiry_before_start_has_expiry_error(): void
    {
        $this->getLoggedInUser('admin');
        $response = $this->putJson('/promotionCreate', [
            'code' => 'PROMO', 'type' => '2', 'applied' => '2025-01-01',
            'uses' => 10, 'start' => '2025-12-31', 'expiry' => '2025-01-01', 'value' => 50,
        ]);

        $response->assertStatus(422);
        $this->assertArrayHasKey('expiry', $response->json('errors'));
    }

    public function test_create_non_numeric_uses_has_uses_error(): void
    {
        $this->getLoggedInUser('admin');
        $response = $this->putJson('/promotionCreate', [
            'code' => 'TEST', 'type' => '2', 'applied' => '2025-01-01',
            'uses' => 'many', 'start' => '2025-01-01', 'expiry' => '2025-12-31', 'value' => 50,
        ]);

        $response->assertStatus(422);
        $this->assertArrayHasKey('uses', $response->json('errors'));
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
}
