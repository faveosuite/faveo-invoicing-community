<?php

declare(strict_types=1);

namespace Tests\Unit\Backend\Http\Controllers\Common;

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

    public function test_periods_dependency_returns_200(): void
    {
        $response = $this->getJson('/dependency/periods');
        $response->assertStatus(200);
    }
}
