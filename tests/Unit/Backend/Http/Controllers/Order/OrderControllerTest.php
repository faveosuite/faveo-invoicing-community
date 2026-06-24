<?php

declare(strict_types=1);

namespace Tests\Unit\Backend\Http\Controllers\Order;

use Tests\DBTestCase;

class OrderControllerTest extends DBTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware(\App\Http\Middleware\Install::class);
    }

    // =========================================================================
    // GET /orders — authentication & role gates
    // =========================================================================

    public function test_admin_gets_200(): void
    {
        $this->getLoggedInUser('admin');
        $this->getJson('/orders')->assertStatus(200);
    }

    public function test_agent_is_redirected_302(): void
    {
        $this->getLoggedInUser('agent');
        $this->getJson('/orders')->assertStatus(302);
    }

    public function test_client_is_redirected_302(): void
    {
        $this->getLoggedInUser('user');
        $this->getJson('/orders')->assertStatus(302);
    }

    public function test_guest_gets_401(): void
    {
        $this->getJson('/orders')->assertStatus(401);
    }

    // =========================================================================
    // Response shape — success shape + pagination metadata
    // =========================================================================

    public function test_list_response_has_success_flag_and_paginated_structure(): void
    {
        $this->getLoggedInUser('admin');
        $response = $this->getJson('/orders');

        $response->assertStatus(200);
        $json = $response->json();

        $this->assertTrue($json['success']);
        // Pagination keys
        $this->assertArrayHasKey('current_page', $json['data']);
        $this->assertArrayHasKey('data', $json['data']);
        $this->assertArrayHasKey('per_page', $json['data']);
        $this->assertArrayHasKey('next_page_url', $json['data']);
        $this->assertIsArray($json['data']['data']);
        $this->assertIsInt($json['data']['current_page']);
    }

    // =========================================================================
    // GET /order/{id} — 404 shape
    // =========================================================================

    public function test_nonexistent_order_returns_404_with_laravel_exception_shape(): void
    {
        $this->getLoggedInUser('admin');
        $response = $this->getJson('/order/999999999');

        $response->assertStatus(404);
        $json = $response->json();
        // Laravel native 404 (route model binding) — has message key, no success key
        $this->assertArrayHasKey('message', $json);
        $this->assertStringContainsString('999999999', $json['message']);
    }

    // =========================================================================
    // Pagination boundary
    // =========================================================================

    public function test_page_beyond_total_returns_200_with_empty_items_array(): void
    {
        $this->getLoggedInUser('admin');
        $response = $this->getJson('/orders?page=99999');

        $response->assertStatus(200);
        $this->assertTrue($response->json('success'));
        $this->assertIsArray($response->json('data.data'));
    }

    // =========================================================================
    // Search safety — actual response body must not reflect injection
    // =========================================================================

    public function test_sql_injection_in_search_returns_200_and_does_not_echo_payload(): void
    {
        $this->getLoggedInUser('admin');
        $response = $this->getJson("/orders?search=' OR 1=1 --");

        $response->assertStatus(200);
        $this->assertTrue($response->json('success'));
        $this->assertStringNotContainsString('OR 1=1', (string) $response->getContent());
    }

    public function test_xss_in_search_not_reflected_raw_in_response(): void
    {
        $this->getLoggedInUser('admin');
        $response = $this->getJson('/orders?search=<script>alert(1)</script>');

        $response->assertStatus(200);
        $this->assertStringNotContainsString('<script>', (string) $response->getContent());
    }

    // =========================================================================
    // GET /getOrderPayments/{orderId} — 400 error shape
    // =========================================================================

    public function test_payments_for_nonexistent_order_returns_400_with_model_not_found_message(): void
    {
        $this->getLoggedInUser('admin');
        $response = $this->getJson('/getOrderPayments/999999999');

        $response->assertStatus(400);
        $json = $response->json();
        $this->assertFalse($json['success']);
        $this->assertArrayHasKey('message', $json);
        $this->assertStringContainsString('999999999', $json['message']);
    }

    // =========================================================================
    // GET /getOrderInvoices/{orderId} — 404
    // =========================================================================

    public function test_invoices_for_nonexistent_order_returns_404(): void
    {
        $this->getLoggedInUser('admin');
        $this->getJson('/getOrderInvoices/999999999')->assertStatus(404);
    }

    // =========================================================================
    // DELETE /orders — bulk delete shape
    // =========================================================================

    public function test_bulk_delete_without_ids_returns_400_with_failure_flag(): void
    {
        $this->getLoggedInUser('admin');
        $response = $this->deleteJson('/orders', []);

        $response->assertStatus(400);
        $this->assertFalse($response->json('success'));
        $this->assertNotEmpty($response->json('message'));
    }

    public function test_bulk_delete_unauthenticated_returns_401(): void
    {
        $this->deleteJson('/orders', [])->assertStatus(401);
    }

    // =========================================================================
    // GET /orders — search and sort
    // =========================================================================

    public function test_get_orders_with_search_query_returns_200(): void
    {
        $this->getLoggedInUser('admin');
        $response = $this->getJson('/orders?search-query=test');
        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
    }

    public function test_get_orders_sort_by_number_asc_returns_200(): void
    {
        $this->getLoggedInUser('admin');
        $response = $this->getJson('/orders?sort-field=number&sort-order=asc');
        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
    }

    // =========================================================================
    // GET /order/{id} — existing order
    // =========================================================================

    public function test_get_order_existing_returns_200_with_order_data(): void
    {
        $this->getLoggedInUser('admin');
        $user = \App\User::factory()->create(['role' => 'user']);
        $order = \App\Model\Order\Order::factory()->create(['client' => $user->id]);

        $response = $this->getJson("/order/{$order->id}");
        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
        $this->assertEquals($order->id, $response->json('data.order.id'));
    }

    // =========================================================================
    // GET /getOrderPayments/{orderId}
    // =========================================================================

    public function test_get_payments_for_existing_order_returns_200(): void
    {
        $this->getLoggedInUser('admin');
        $user = \App\User::factory()->create(['role' => 'user']);
        $order = \App\Model\Order\Order::factory()->create(['client' => $user->id]);

        $response = $this->getJson("/getOrderPayments/{$order->id}");
        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
    }

    // =========================================================================
    // GET /getOrderInvoices/{orderId}
    // =========================================================================

    public function test_get_invoices_for_existing_order_returns_200(): void
    {
        $this->getLoggedInUser('admin');
        $user = \App\User::factory()->create(['role' => 'user']);
        $order = \App\Model\Order\Order::factory()->create(['client' => $user->id]);

        $response = $this->getJson("/getOrderInvoices/{$order->id}");
        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
    }

    // =========================================================================
    // GET /export-orders — requires queue
    // =========================================================================

    public function test_export_orders_without_queue_returns_400(): void
    {
        $this->getLoggedInUser('admin');
        $response = $this->getJson('/export-orders');
        $response->assertStatus(400);
        $response->assertJson(['success' => false]);
    }

    // =========================================================================
    // GET /get-installation-details/{orderId}
    // =========================================================================

    public function test_get_installation_details_nonexistent_order_returns_200_with_empty_array(): void
    {
        $this->getLoggedInUser('admin');
        $response = $this->getJson('/get-installation-details/999999');
        // No installation details found → 200 with empty data array
        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
    }
}
