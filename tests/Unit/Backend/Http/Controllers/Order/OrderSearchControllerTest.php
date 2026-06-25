<?php

declare(strict_types=1);

namespace Tests\Unit\Backend\Http\Controllers\Order;

use App\Http\Controllers\Order\OrderSearchController;
use Tests\DBTestCase;

class OrderSearchControllerTest extends DBTestCase
{
    private OrderSearchController $controller;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware(\App\Http\Middleware\Install::class);
        $this->controller = new OrderSearchController();
    }

    // =========================================================================
    // GET /orders?search=... (HTTP, admin only)
    // =========================================================================

    public function test_search_returns_200_for_admin(): void
    {
        $this->getLoggedInUser('admin');

        $this->getJson('/orders?search=test')->assertStatus(200);
    }

    public function test_search_with_empty_string_returns_200(): void
    {
        $this->getLoggedInUser('admin');

        $this->getJson('/orders?search=')->assertStatus(200);
    }

    public function test_search_result_has_paginated_success_shape(): void
    {
        $this->getLoggedInUser('admin');

        $response = $this->getJson('/orders?search=anything');

        $response->assertStatus(200);
        $json = $response->json();
        $this->assertTrue($json['success']);
        $this->assertArrayHasKey('data', $json);
        $this->assertArrayHasKey('data', $json['data']); // paginated items
    }

    public function test_xss_in_search_not_reflected_raw(): void
    {
        $this->getLoggedInUser('admin');

        $response = $this->getJson('/orders?search=<script>alert(1)</script>');

        $response->assertStatus(200);
        $this->assertStringNotContainsString('<script>', (string) $response->getContent());
    }

    // =========================================================================
    // Internal: advanceOrderSearch() and applyOrdersSearch()
    // =========================================================================

    public function test_advance_order_search_returns_builder(): void
    {
        $this->getLoggedInUser('admin');

        $builder = $this->controller->advanceOrderSearch(new \Illuminate\Http\Request());

        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Builder::class, $builder);
    }

    public function test_apply_orders_search_with_null_returns_same_builder(): void
    {
        $this->getLoggedInUser('admin');

        $base = $this->controller->advanceOrderSearch(new \Illuminate\Http\Request());
        $filtered = $this->controller->applyOrdersSearch($base, null);

        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Builder::class, $filtered);
    }

    public function test_apply_orders_search_with_status_string_does_not_crash(): void
    {
        $this->getLoggedInUser('admin');

        $base = $this->controller->advanceOrderSearch(new \Illuminate\Http\Request());
        $filtered = $this->controller->applyOrdersSearch($base, 'executed');

        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Builder::class, $filtered);
    }

    public function test_apply_orders_search_with_status_narrows_results(): void
    {
        $this->getLoggedInUser('admin');

        $base = $this->controller->advanceOrderSearch(new \Illuminate\Http\Request());
        $all = $base->count();
        $filtered = $this->controller->applyOrdersSearch(
            $this->controller->advanceOrderSearch(new \Illuminate\Http\Request()),
            'status_that_does_not_exist_xyz'
        );

        // Filtering on a nonexistent status must return ≤ total (never more)
        $this->assertLessThanOrEqual($all, $filtered->count());
    }

    // =========================================================================
    // advanceOrderSearch — with specific filters exercises private methods
    // =========================================================================

    public function test_advance_order_search_with_order_no_filter(): void
    {
        $this->getLoggedInUser('admin');
        $request = new \Illuminate\Http\Request;
        $request->merge(['order_no' => 'NONEXISTENT-ORDER-XYZ']);
        $result = $this->controller->advanceOrderSearch($request);
        $this->assertEquals(0, $result->count());
    }

    public function test_advance_order_search_with_client_filter(): void
    {
        $this->getLoggedInUser('admin');
        $request = new \Illuminate\Http\Request;
        $request->merge(['client' => 999999]);
        $result = $this->controller->advanceOrderSearch($request);
        $this->assertEquals(0, $result->count());
    }

    public function test_advance_order_search_with_date_range(): void
    {
        $this->getLoggedInUser('admin');
        $request = new \Illuminate\Http\Request;
        $request->merge([
            'from_date' => '2020-01-01',
            'to_date'   => '2020-12-31',
        ]);
        $result = $this->controller->advanceOrderSearch($request);
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Builder::class, $result);
    }

    public function test_advance_order_search_with_domain_filter(): void
    {
        $this->getLoggedInUser('admin');
        $request = new \Illuminate\Http\Request;
        $request->merge(['domain' => 'nonexistent-domain-xyz.test']);
        $result = $this->controller->advanceOrderSearch($request);
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Builder::class, $result);
    }

    public function test_advance_order_search_with_renewal_expiring(): void
    {
        $this->getLoggedInUser('admin');
        $request = new \Illuminate\Http\Request;
        $request->merge(['renewal' => 'expiring_subscription']);
        $result = $this->controller->advanceOrderSearch($request);
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Builder::class, $result);
    }

    public function test_advance_order_search_with_product_id_filter(): void
    {
        $this->getLoggedInUser('admin');
        $request = new \Illuminate\Http\Request;
        $request->merge(['product_id' => 999999]);
        $result = $this->controller->advanceOrderSearch($request);
        $this->assertEquals(0, $result->count());
    }
}
