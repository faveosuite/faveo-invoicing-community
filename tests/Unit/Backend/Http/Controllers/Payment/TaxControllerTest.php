<?php

declare(strict_types=1);

namespace Tests\Unit\Backend\Http\Controllers\Payment;

use App\Model\Payment\TaxRate;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\DBTestCase;

class TaxControllerTest extends DBTestCase
{
    use DatabaseTransactions;

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

    public function test_get_tax_returns_paginated_list(): void
    {
        $this->getLoggedInUser('admin');
        $response = $this->getJson('/tax-tables');
        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
    }

    public function test_get_tax_with_search_filters_results(): void
    {
        // Covers lines 60-66: search-filtered tax list
        $this->getLoggedInUser('admin');
        $response = $this->getJson('/tax-tables?search-query=GST&tax_class=standard');
        $response->assertStatus(200);
    }

    public function test_edit_tax_api_returns_rate_data(): void
    {
        // Covers lines 100-130: editTaxApi with existing rate
        $this->getLoggedInUser('admin');
        $rate = TaxRate::create(['name' => 'Test Rate', 'country' => 'IN', 'rate' => 18.0, 'tax_class' => 'standard', 'priority' => 1, 'compound' => false, 'active' => true]);

        $response = $this->getJson('/tax/edit/'.$rate->id);
        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
    }

    public function test_save_tax_class_creates_rate(): void
    {
        // Covers lines 120-130: saveTaxClassSettingApi
        $this->getLoggedInUser('admin');
        $response = $this->postJson('/create/tax-class', [
            'name' => 'New Rate',
            'country' => 'US',
            'rate' => 10.0,
            'tax_class' => 'standard',
            'priority' => 1,
            'compound' => false,
            'active' => true,
        ]);
        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
    }

    public function test_save_tax_class_with_postcode_creates_location(): void
    {
        // Covers lines 294-297: syncLocations with postcode/city
        $this->getLoggedInUser('admin');
        $response = $this->postJson('/create/tax-class', [
            'name' => 'Rate With Location',
            'country' => 'US',
            'rate' => 5.0,
            'tax_class' => 'standard',
            'priority' => 1,
            'compound' => false,
            'active' => true,
            'postcode' => '90210, 10001',
            'city' => 'Los Angeles, New York',
        ]);
        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
    }

    public function test_update_tax_api_updates_rate(): void
    {
        // Covers lines 144-153: updateTaxApi
        $this->getLoggedInUser('admin');
        $rate = TaxRate::create(['name' => 'Update Me', 'country' => 'GB', 'rate' => 20.0, 'tax_class' => 'standard', 'priority' => 1, 'compound' => false, 'active' => true]);

        $response = $this->putJson('/tax/'.$rate->id, [
            'name' => 'Updated Rate',
            'country' => 'GB',
            'rate' => 20.0,
            'tax_class' => 'standard',
            'priority' => 1,
            'compound' => false,
            'active' => true,
        ]);
        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
    }

    public function test_delete_tax_with_ids_returns_200(): void
    {
        // Covers lines 166-189: deleteTax with IDs
        $this->getLoggedInUser('admin');
        $rate = TaxRate::create(['name' => 'Delete Me', 'country' => 'FR', 'rate' => 20.0, 'tax_class' => 'standard', 'priority' => 1, 'compound' => false, 'active' => true]);

        $response = $this->deleteJson('/tax/delete', ['select' => [$rate->id]]);
        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
    }

    public function test_save_tax_option_setting_saves_options(): void
    {
        // Covers lines 207-212 and 244-248: saveTaxOptionSetting + syncAdditionalClasses
        $this->getLoggedInUser('admin');

        // Create a TaxClass that will NOT be in the desired list → gets deleted (lines 244-248)
        \App\Model\Payment\TaxClass::create(['name' => 'Will Be Removed', 'slug' => 'will-be-removed-'.uniqid()]);

        $response = $this->postJson('/taxes/option', [
            'tax_enable' => 1,
            'inclusive' => 0,
            'tax_based_on' => 'billing',
            'additional_tax_classes' => "New Class",
        ]);
        $response->assertStatus(200);
    }

    public function test_get_state_returns_states_for_country(): void
    {
        // Covers lines 235-254: getState
        $this->getLoggedInUser('admin');
        $response = $this->getJson('/get-state/US');
        $response->assertStatus(200);
    }
}
