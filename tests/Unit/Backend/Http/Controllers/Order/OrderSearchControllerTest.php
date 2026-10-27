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
            'to_date' => '2020-12-31',
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

    public function test_active_subscription_filter_includes_lifetime_subscriptions(): void
    {
        $this->getLoggedInUser('admin');
        $lifetime = \App\Model\Order\Order::factory()->create();
        \App\Model\Product\Subscription::factory()->create(['order_id' => $lifetime->id, 'ends_at' => null]);
        $expired = \App\Model\Order\Order::factory()->create();
        \App\Model\Product\Subscription::factory()->create(['order_id' => $expired->id, 'ends_at' => now()->subDay()]);

        $ids = fn (string $f) => $this->controller->advanceOrderSearch(new \Illuminate\Http\Request(['renewal' => $f]))
            ->whereIn('orders.id', [$lifetime->id, $expired->id])->pluck('orders.id')->all();

        $this->assertSame([$lifetime->id], $ids('active_subscription')); // no end date = never expires
        $this->assertSame([$expired->id], $ids('expired_subscription'));
    }

    public function test_an_install_without_a_version_still_shows_in_the_order_list(): void
    {
        $this->getLoggedInUser('admin');
        $order = \App\Model\Order\Order::factory()->create();
        $code = 'NV'.uniqid();
        \App\License\Models\License::create(['product_id' => $order->product, 'license_code' => $code, 'license_order_number' => $order->number]);
        \App\License\Models\InstallationLog::create(['license_code' => $code, 'installation_domain' => 'cloud.test', 'version_number' => null, 'installation_last_active_date' => now()]);

        $row = collect($this->getJson('/orders?order_no='.$order->number)->assertOk()->json('data.data'))->firstWhere('id', $order->id);

        // The "Active installation" filter counts it, so the list must show it too.
        $this->assertSame([['version' => '—', 'active' => true]], $row['versions']);
    }

    public function test_installation_and_version_filters_split_orders_without_a_product(): void
    {
        $this->getLoggedInUser('admin');
        $count = fn (array $filters): int => $this->controller->advanceOrderSearch(new \Illuminate\Http\Request($filters))->count();

        // Installed / not installed partition every order (both read the installation records).
        $this->assertSame($count([]), $count(['act_ins' => 'installed']) + $count(['act_ins' => 'not_installed']));

        // Used to throw "Illegal operator and value combination" when no product was picked.
        $this->assertGreaterThanOrEqual(0, $count(['version' => 'Outdated']));
        $this->assertGreaterThanOrEqual(0, $count(['version' => 'Latest']));
    }
}
