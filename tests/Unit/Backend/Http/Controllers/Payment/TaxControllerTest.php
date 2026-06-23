<?php

declare(strict_types=1);

namespace Tests\Unit\Backend\Http\Controllers\Payment;

use Tests\DBTestCase;

class TaxControllerTest extends DBTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware(\App\Http\Middleware\Install::class);
    }

    // --- GET /tax-tables ---

    public function test_admin_gets_200(): void
    {
        $this->getLoggedInUser('admin');
        $this->getJson('/tax-tables')->assertStatus(200);
    }

    public function test_client_is_redirected_302(): void
    {
        $this->getLoggedInUser('user');
        $this->getJson('/tax-tables')->assertStatus(302);
    }

    public function test_guest_gets_401(): void
    {
        $this->getJson('/tax-tables')->assertStatus(401);
    }

    public function test_list_has_paginated_structure(): void
    {
        $this->getLoggedInUser('admin');
        $response = $this->getJson('/tax-tables');

        $response->assertStatus(200);
        $this->assertTrue($response->json('success'));
        $this->assertIsArray($response->json('data.data'));
    }

    // --- GET /tax/edit/{id} ---

    public function test_nonexistent_tax_rate_returns_404(): void
    {
        $this->getLoggedInUser('admin');
        $this->getJson('/tax/edit/999999999')->assertStatus(404);
    }

    // --- PUT /tax/{id} — validation via route model binding ---

    public function test_update_nonexistent_rate_id_returns_404(): void
    {
        // Route model binding resolves TaxRate; 999999999 doesn't exist → 404 before controller
        $this->getLoggedInUser('admin');
        $this->putJson('/tax/999999999', [])->assertStatus(404);
    }

    // --- GET /tax-options — shape verification ---

    public function test_tax_options_returns_200_with_options_object(): void
    {
        $this->getLoggedInUser('admin');
        $response = $this->getJson('/tax-options');

        $response->assertStatus(200);
        $this->assertTrue($response->json('success'));

        $options = $response->json('data.options');
        $this->assertNotNull($options, 'data.options must be present');
        // Verify the option fields used by frontend
        foreach (['tax_enable', 'inclusive', 'tax_based_on', 'rounding'] as $field) {
            $this->assertArrayHasKey($field, $options, "tax-options missing field: $field");
        }
    }

    // --- DELETE /tax/delete ---

    public function test_bulk_delete_without_ids_returns_400_with_failure_flag(): void
    {
        $this->getLoggedInUser('admin');
        $response = $this->deleteJson('/tax/delete', []);

        $response->assertStatus(400);
        $this->assertFalse($response->json('success'));
        $this->assertNotEmpty($response->json('message'));
    }

    public function test_bulk_delete_unauthenticated_returns_401(): void
    {
        $this->deleteJson('/tax/delete', ['ids' => [1]])->assertStatus(401);
    }
}
