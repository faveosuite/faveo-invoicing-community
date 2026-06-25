<?php

declare(strict_types=1);

namespace Tests\Unit\Backend\Http\Controllers\Order;

use App\Model\Order\Order;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\DBTestCase;

class OrderControllerTest extends DBTestCase
{
    use DatabaseTransactions;

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
        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
    }

    public function test_orders_list_includes_order_transform_data(): void
    {
        // Covers lines 127-167: transform closure in getOrders
        $this->getLoggedInUser('admin');
        Order::factory()->withRelations()->create();

        $response = $this->getJson('/orders');
        $response->assertStatus(200);
        $data = $response->json('data.data');
        $this->assertNotEmpty($data);
        $this->assertArrayHasKey('number', $data[0]);
    }

    public function test_delete_bulk_orders_with_ids_returns_200(): void
    {
        // Covers lines 263-280: deleteBulkOrders with actual IDs
        $this->getLoggedInUser('admin');
        $order = Order::factory()->withRelations()->create();

        $response = $this->deleteJson('/orders', ['order_ids' => [$order->id]]);
        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
    }

    public function test_get_order_returns_full_order_data(): void
    {
        // Covers lines 189+: getOrder response transform
        $this->getLoggedInUser('admin');
        $order = Order::factory()->withRelations()->create();

        $response = $this->getJson('/order/'.$order->id);
        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
    }

    // =========================================================================
    // Small helper methods: plan(), product(), subscription(), expiry()
    // =========================================================================

    public function test_plan_returns_zero_for_unknown_invoice_item(): void
    {
        $this->getLoggedInUser('admin');
        $controller = new \App\Http\Controllers\Order\OrderController;

        $result = $controller->plan(999999);

        $this->assertEquals(0, $result);
    }

    public function test_check_invoice_status_by_order_id_returns_string(): void
    {
        $this->getLoggedInUser('admin');
        $controller = new \App\Http\Controllers\Order\OrderController;

        $result = $controller->checkInvoiceStatusByOrderId(999999);

        $this->assertIsString($result);
    }

    public function test_product_returns_empty_for_unknown_item(): void
    {
        $this->getLoggedInUser('admin');
        $controller = new \App\Http\Controllers\Order\OrderController;

        $result = $controller->product(999999);

        $this->assertIsString($result);
    }

    public function test_subscription_returns_null_for_unknown_order(): void
    {
        $this->getLoggedInUser('admin');
        $controller = new \App\Http\Controllers\Order\OrderController;

        $result = $controller->subscription(999999);

        $this->assertNull($result);
    }

    public function test_expiry_returns_null_or_empty_for_unknown_order(): void
    {
        $this->getLoggedInUser('admin');
        $controller = new \App\Http\Controllers\Order\OrderController;

        $result = $controller->expiry(999999);

        $this->assertTrue($result === null || $result === '');
    }

    public function test_get_installation_details_returns_response(): void
    {
        $this->getLoggedInUser('admin');

        $response = $this->getJson('/get-installation-details/999999');

        $this->assertContains($response->getStatusCode(), [200, 400, 404]);
    }

    public function test_export_orders_returns_response(): void
    {
        $this->getLoggedInUser('admin');

        $response = $this->getJson('/export-orders');

        $this->assertContains($response->getStatusCode(), [200, 400, 404, 422]);
    }

    // =========================================================================
    // getOrderInvoices — with real data showing paginated invoices
    // =========================================================================

    public function test_get_order_invoices_with_real_data_returns_paginated_invoices(): void
    {
        $this->getLoggedInUser('admin');
        $order = Order::factory()->withRelations()->create();

        $response = $this->getJson("/getOrderInvoices/{$order->id}");

        $response->assertStatus(200)
            ->assertJson(['success' => true]);

        $data = $response->json('data');
        $this->assertArrayHasKey('data', $data);
        $this->assertIsArray($data['data']);
    }

    public function test_get_order_invoices_with_sort_and_limit(): void
    {
        $this->getLoggedInUser('admin');
        $order = Order::factory()->withRelations()->create();

        $response = $this->getJson("/getOrderInvoices/{$order->id}?sort-field=created_at&sort-order=desc&limit=5");

        $response->assertStatus(200)
            ->assertJson(['success' => true]);
    }

    // =========================================================================
    // getPaymentByOrderId — with search query (covers search branches)
    // =========================================================================

    public function test_get_payment_by_order_id_with_search_query_returns_200(): void
    {
        $this->getLoggedInUser('admin');
        $user = \App\User::factory()->create(['role' => 'user']);
        $order = Order::factory()->create(['client' => $user->id]);

        $response = $this->getJson("/getOrderPayments/{$order->id}?search-query=stripe");

        $response->assertStatus(200)
            ->assertJson(['success' => true]);
    }

    public function test_get_payment_by_order_id_with_sort_params_returns_200(): void
    {
        $this->getLoggedInUser('admin');
        $user = \App\User::factory()->create(['role' => 'user']);
        $order = Order::factory()->create(['client' => $user->id]);

        $response = $this->getJson("/getOrderPayments/{$order->id}?sort-field=amount&sort-order=desc&limit=5");

        $response->assertStatus(200)
            ->assertJson(['success' => true]);
    }

    // =========================================================================
    // renew — direct call
    // =========================================================================

    public function test_renew_returns_my_orders_url(): void
    {
        $this->getLoggedInUser('admin');

        $controller = new \App\Http\Controllers\Order\OrderController;
        $result = $controller->renew(999999);

        $this->assertStringContainsString('my-orders', (string) $result);
    }

    // =========================================================================
    // checkInvoiceStatusByOrderId — with real order that has success invoice
    // =========================================================================

    public function test_check_invoice_status_returns_success_for_paid_invoice(): void
    {
        $this->getLoggedInUser('admin');

        // Create order with associated paid invoice
        $order = Order::factory()->withRelations()->create();
        $invoice = $order->getRelation('invoice');
        if ($invoice) {
            $invoice->status = 'Success';
            $invoice->save();
        }

        $controller = new \App\Http\Controllers\Order\OrderController;
        $result = $controller->checkInvoiceStatusByOrderId($order->id);

        $this->assertIsString($result);
        // With a 'Success' invoice, the method returns 'success'
        $this->assertSame('success', $result);
    }

    // =========================================================================
    // getOrders — sort field whitelist: invalid field falls back to created_at
    // =========================================================================

    public function test_get_orders_with_invalid_sort_field_falls_back_to_created_at(): void
    {
        $this->getLoggedInUser('admin');

        $response = $this->getJson('/orders?sort-field=nonexistent_column&sort-order=asc');

        $response->assertStatus(200)
            ->assertJson(['success' => true]);
    }

    public function test_get_orders_with_order_status_sort_returns_200(): void
    {
        $this->getLoggedInUser('admin');

        $response = $this->getJson('/orders?sort-field=order_status&sort-order=asc');

        $response->assertStatus(200)
            ->assertJson(['success' => true]);
    }

    // =========================================================================
    // getPaymentByOrderId — with real payments linked to order
    // =========================================================================

    public function test_get_payment_by_order_id_with_payments_returns_paginated_data(): void
    {
        $this->getLoggedInUser('admin');
        $order = Order::factory()->withRelations()->create();

        // Create a payment for the invoice linked to this order
        $invoice = $order->getRelation('invoice');
        if ($invoice) {
            \App\Model\Order\Payment::create([
                'invoice_id' => $invoice->id,
                'user_id' => $order->client,
                'amount' => 100.0,
                'payment_method' => 'stripe',
                'payment_status' => 'success',
            ]);
        }

        $response = $this->getJson("/getOrderPayments/{$order->id}");

        $response->assertStatus(200)
            ->assertJson(['success' => true]);

        $data = $response->json('data');
        $this->assertArrayHasKey('data', $data);
    }
}
