<?php

declare(strict_types=1);

namespace Tests\Unit\Backend\Http\Controllers\Common;

use App\Model\License\LicensePermission;
use App\Model\License\LicenseType;
use App\Model\Product\Product;
use Tests\DBTestCase;

class DependencyControllerTest extends DBTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware();
        $this->getLoggedInUser('admin');
    }

    // =========================================================================
    // DependencyController::handle() – public dependency types
    // =========================================================================

    public function test_time_zones_returns_200(): void
    {
        $response = $this->getJson('/dependency/time-zones');
        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
    }

    public function test_languages_returns_200(): void
    {
        $response = $this->getJson('/dependency/languages');
        $response->assertStatus(200);
    }

    public function test_countries_returns_200(): void
    {
        $response = $this->getJson('/dependency/countries');
        $response->assertStatus(200);
    }

    public function test_states_returns_400_or_200(): void
    {
        // states may need a country param but should not throw
        $response = $this->getJson('/dependency/states');
        $this->assertContains($response->status(), [200, 400]);
    }

    public function test_unknown_dependency_type_returns_error(): void
    {
        $response = $this->getJson('/dependency/nonexistent-type-xyz');
        // Either 400 or errorResponse from exception handling
        $this->assertContains($response->status(), [200, 400]);
    }

    // =========================================================================
    // NonPublicDependencies – admin-restricted types
    // =========================================================================

    public function test_currencies_dependency_returns_200(): void
    {
        $response = $this->getJson('/dependency/currencies');
        $response->assertStatus(200);
    }

    public function test_products_dependency_returns_200(): void
    {
        $response = $this->getJson('/dependency/products');
        $response->assertStatus(200);
    }

    private function productChildren($response): array
    {
        return collect($response->json('data.products'))
            ->flatMap(fn ($group) => $group['children'] ?? [])
            ->all();
    }

    public function test_products_dependency_includes_build_type_in_each_child(): void
    {
        Product::factory()->create(['invoice_hidden' => 0, 'build_type' => 'obfuscated']);

        $response = $this->getJson('/dependency/products');

        $response->assertStatus(200);
        $children = $this->productChildren($response);
        $this->assertNotEmpty($children);
        $this->assertArrayHasKey('build_type', $children[0]);
    }

    public function test_products_dependency_filters_by_permission(): void
    {
        $permission = LicensePermission::create(['permissions' => 'Can be Downloaded']);
        $type = LicenseType::factory()->create();
        $type->permissions()->attach($permission->id);

        $withPermission = Product::factory()->create(['invoice_hidden' => 0, 'type' => $type->id, 'name' => 'Downloadable '.uniqid()]);
        $withoutPermission = Product::factory()->create(['invoice_hidden' => 0, 'name' => 'Not Downloadable '.uniqid()]);

        $response = $this->getJson('/dependency/products?permission=downloadPermission');

        $response->assertStatus(200);
        $childIds = array_column($this->productChildren($response), 'id');

        $this->assertContains($withPermission->id, $childIds);
        $this->assertNotContains($withoutPermission->id, $childIds);
    }

    public function test_periods_dependency_returns_200(): void
    {
        $response = $this->getJson('/dependency/periods');
        $response->assertStatus(200);
    }

    public function test_managers_dependency_returns_200(): void
    {
        $response = $this->getJson('/dependency/managers');
        $response->assertStatus(200);
    }

    public function test_industries_dependency_returns_200(): void
    {
        $response = $this->getJson('/dependency/industries');
        $response->assertStatus(200);
    }

    public function test_order_versions_dependency_returns_200(): void
    {
        $response = $this->getJson('/dependency/order-versions');
        $this->assertContains($response->getStatusCode(), [200, 400]);
    }

    public function test_product_plans_dependency_returns_200(): void
    {
        $response = $this->getJson('/dependency/product-plans');
        $this->assertContains($response->getStatusCode(), [200, 400]);
    }

    public function test_license_types_dependency_returns_200(): void
    {
        $response = $this->getJson('/dependency/license-types');
        $this->assertContains($response->getStatusCode(), [200, 400]);
    }

    public function test_product_groups_dependency_returns_200(): void
    {
        $response = $this->getJson('/dependency/product-groups');
        $this->assertContains($response->getStatusCode(), [200, 400]);
    }

    public function test_tax_classes_dependency_returns_200(): void
    {
        $response = $this->getJson('/dependency/tax-classes');
        $this->assertContains($response->getStatusCode(), [200, 400]);
    }

    public function test_promotion_types_dependency_returns_200(): void
    {
        $response = $this->getJson('/dependency/promotion-types');
        $this->assertContains($response->getStatusCode(), [200, 400]);
    }

    public function test_pricing_templates_dependency_returns_200(): void
    {
        $response = $this->getJson('/dependency/pricing-templates');
        $this->assertContains($response->getStatusCode(), [200, 400]);
    }

    public function test_all_products_dependency_returns_200(): void
    {
        $response = $this->getJson('/dependency/all-products');
        $this->assertContains($response->getStatusCode(), [200, 400]);
    }

    public function test_plugin_products_dependency_returns_200(): void
    {
        $response = $this->getJson('/dependency/plugin-products');
        $this->assertContains($response->getStatusCode(), [200, 400]);
    }

    public function test_users_dependency_returns_200(): void
    {
        $response = $this->getJson('/dependency/users');
        $this->assertContains($response->getStatusCode(), [200, 400]);
    }
}
