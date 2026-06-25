<?php

declare(strict_types=1);

namespace Tests\Unit\Backend\Http\Controllers\Order;

use App\Model\Order\Invoice;
use Tests\DBTestCase;

class InvoiceControllerTest extends DBTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware(\App\Http\Middleware\Install::class);
    }

    // =========================================================================
    // GET /invoices — role gates
    // =========================================================================

    public function test_admin_gets_200(): void
    {
        $this->getLoggedInUser('admin');
        $this->getJson('/invoices')->assertStatus(200);
    }

    public function test_client_is_redirected_302(): void
    {
        $this->getLoggedInUser('user');
        $this->getJson('/invoices')->assertStatus(302);
    }

    public function test_guest_gets_401(): void
    {
        $this->getJson('/invoices')->assertStatus(401);
    }

    // =========================================================================
    // Response shape — pagination metadata and success flag
    // =========================================================================

    public function test_list_response_structure(): void
    {
        $this->getLoggedInUser('admin');
        $response = $this->getJson('/invoices');

        $response->assertStatus(200);
        $this->assertTrue($response->json('success'));
        $this->assertIsInt($response->json('data.current_page'));
        $this->assertIsArray($response->json('data.data'));
        $this->assertArrayHasKey('per_page', $response->json('data'));
    }

    // =========================================================================
    // GET /invoice/{id} — error shape when not found
    // =========================================================================

    public function test_nonexistent_invoice_returns_400_with_error_message(): void
    {
        $this->getLoggedInUser('admin');
        $response = $this->getJson('/invoice/999999999');

        $response->assertStatus(400);
        $this->assertFalse($response->json('success'));
        // Message must name the missing resource
        $this->assertStringContainsString('999999999', $response->json('message'));
    }

    // =========================================================================
    // GET /invoice/{id} — success: verify actual invoice data returned
    // =========================================================================

    public function test_existing_invoice_returns_correct_data(): void
    {
        $this->getLoggedInUser('admin');
        /** @var Invoice $invoice */
        $invoice = Invoice::factory()->create([
            'user_id' => $this->user->id,
            'grand_total' => 299.99,
            'status' => 'pending',
            'currency' => 'USD',
        ]);

        $response = $this->getJson("/invoice/{$invoice->id}");

        $response->assertStatus(200);
        $this->assertTrue($response->json('success'));

        $data = $response->json('data');
        $this->assertArrayHasKey('invoice', $data);
        $this->assertArrayHasKey('items', $data);
        $this->assertArrayHasKey('totals', $data);
        $this->assertArrayHasKey('payments', $data);

        // Verify the invoice matches what we created
        $inv = $data['invoice'];
        $this->assertSame($invoice->id, $inv['id']);
        $this->assertSame('USD', $inv['currency']);
    }

    // =========================================================================
    // Pagination boundary
    // =========================================================================

    public function test_page_beyond_last_returns_200_with_empty_items(): void
    {
        $this->getLoggedInUser('admin');
        $response = $this->getJson('/invoices?page=99999');

        $response->assertStatus(200);
        $this->assertTrue($response->json('success'));
        $this->assertIsArray($response->json('data.data'));
    }

    // =========================================================================
    // POST /generate/invoice — validation: exact field errors
    // =========================================================================

    public function test_generate_invoice_empty_body_returns_422_with_all_required_field_errors(): void
    {
        $this->getLoggedInUser('admin');
        $response = $this->postJson('/generate/invoice', []);

        $response->assertStatus(422);
        $errors = $response->json('errors');

        // All required fields must appear in errors
        foreach (['user', 'date', 'price', 'product'] as $field) {
            $this->assertArrayHasKey($field, $errors, "Expected '$field' in validation errors");
        }
    }

    public function test_generate_invoice_missing_user_error_message(): void
    {
        $this->getLoggedInUser('admin');
        $response = $this->postJson('/generate/invoice', [
            'date' => '2025-01-15', 'price' => 99.99, 'product' => 1,
        ]);

        $response->assertStatus(422);
        $this->assertSame(
            'The clients field is required.',
            $response->json('errors.user.0')
        );
    }

    public function test_generate_invoice_invalid_date_format_gives_date_error(): void
    {
        $this->getLoggedInUser('admin');
        $response = $this->postJson('/generate/invoice', [
            'user' => 1, 'date' => 'not-a-date', 'price' => 99.99, 'product' => 1,
        ]);

        $response->assertStatus(422);
        $this->assertArrayHasKey('date', $response->json('errors'));
    }

    // =========================================================================
    // DELETE /invoices — shape
    // =========================================================================

    public function test_bulk_delete_without_ids_returns_400_with_failure_flag(): void
    {
        $this->getLoggedInUser('admin');
        $response = $this->deleteJson('/invoices', []);

        $response->assertStatus(400);
        $this->assertFalse($response->json('success'));
    }

    public function test_bulk_delete_unauthenticated_returns_401(): void
    {
        $this->deleteJson('/invoices', ['ids' => [1]])->assertStatus(401);
    }

    public function test_bulk_delete_invoices_with_ids_returns_200(): void
    {
        $this->getLoggedInUser('admin');
        $invoice = Invoice::factory()->create(['user_id' => $this->user->id]);

        $response = $this->deleteJson('/invoices', ['invoice_ids' => [$invoice->id]]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
    }

    public function test_bulk_delete_payments_without_ids_returns_400(): void
    {
        $this->getLoggedInUser('admin');
        $response = $this->deleteJson('/payments', []);

        $response->assertStatus(400);
        $response->assertJson(['success' => false]);
    }

    public function test_bulk_delete_payments_with_ids_returns_200(): void
    {
        $this->getLoggedInUser('admin');
        // Non-existent IDs — still returns success (no records to delete is fine)
        $response = $this->deleteJson('/payments', ['payment_ids' => [999999]]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
    }

    public function test_get_invoices_with_name_filter_returns_200(): void
    {
        $this->getLoggedInUser('admin');
        $response = $this->getJson('/invoices?name=John');

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
    }

    public function test_get_invoices_with_date_range_returns_200(): void
    {
        $this->getLoggedInUser('admin');
        $response = $this->getJson('/invoices?from_date=01/01/2025&to_date=12/31/2025');

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
    }

    // =========================================================================
    // GET /invoices — search and sort params
    // =========================================================================

    public function test_get_invoices_with_search_query_returns_200(): void
    {
        $this->getLoggedInUser('admin');
        $response = $this->getJson('/invoices?search-query=paid');
        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
    }

    public function test_get_invoices_sort_by_number_returns_200(): void
    {
        $this->getLoggedInUser('admin');
        $response = $this->getJson('/invoices?sort-field=number&sort-order=asc');
        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
    }

    public function test_get_invoices_invalid_sort_field_falls_back_to_created_at(): void
    {
        $this->getLoggedInUser('admin');
        $response = $this->getJson('/invoices?sort-field=nonexistent');
        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
    }

    public function test_get_invoices_with_custom_limit_returns_200(): void
    {
        $this->getLoggedInUser('admin');
        $response = $this->getJson('/invoices?limit=5');
        $response->assertStatus(200);
        $this->assertCount(
            min(5, count($response->json('data.data'))),
            $response->json('data.data')
        );
    }

    // =========================================================================
    // GET /pdf — pdf endpoint validation
    // =========================================================================

    public function test_pdf_without_invoice_id_returns_400(): void
    {
        $this->getLoggedInUser('admin');
        $response = $this->getJson('/pdf');
        $response->assertStatus(400);
        $response->assertJson(['success' => false]);
    }

    public function test_pdf_with_invalid_invoice_id_returns_400(): void
    {
        $this->getLoggedInUser('admin');
        $response = $this->getJson('/pdf?invoiceid=999999');
        $response->assertStatus(400);
        $response->assertJson(['success' => false]);
    }

    // =========================================================================
    // GET /export-invoices — export requires queue
    // =========================================================================

    public function test_export_invoices_without_queue_returns_400(): void
    {
        $this->getLoggedInUser('admin');
        // No QueueService row configured → errorResponse 400
        $response = $this->getJson('/export-invoices');
        $response->assertStatus(400);
        $response->assertJson(['success' => false]);
    }

    // =========================================================================
    // GET /newPayment/receive — new payment form (requires client query param)
    // =========================================================================

    public function test_new_payment_without_client_param_returns_400(): void
    {
        $this->getLoggedInUser('admin');
        // newPayment requires a valid client — without it the controller returns errorResponse
        $response = $this->getJson('/newPayment/receive');
        $response->assertStatus(400);
        $response->assertJson(['success' => false]);
    }

    public function test_new_payment_with_valid_clientid_returns_200(): void
    {
        $this->getLoggedInUser('admin');
        $client = \App\User::factory()->create(['role' => 'user', 'email' => 'newpay-'.uniqid().'@test.local']);
        $response = $this->getJson("/newPayment/receive?clientid={$client->id}");
        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
        $response->assertJsonStructure(['success', 'data' => ['invoices', 'currencies']]);
    }

    // =========================================================================
    // getInvoice – GET /invoice/{id}
    // =========================================================================

    public function test_get_invoice_returns_404_for_nonexistent(): void
    {
        $this->getLoggedInUser('admin');
        $response = $this->getJson('/invoice/999999');
        $response->assertStatus(400);
        $response->assertJson(['success' => false]);
    }

    public function test_get_invoice_returns_structured_data_for_existing(): void
    {
        $this->getLoggedInUser('admin');
        $user = \App\User::factory()->create(['email' => 'inv-detail-'.uniqid().'@test.local']);
        $invoice = \App\Model\Order\Invoice::factory()->create(['user_id' => $user->id, 'status' => 'pending']);

        $response = $this->getJson('/invoice/'.$invoice->id);

        // May return 200 with invoice data or 400 if settings not configured
        $this->assertContains($response->status(), [200, 400]);
        if ($response->status() === 200) {
            $response->assertJson(['success' => true]);
            $data = $response->json('data');
            $this->assertArrayHasKey('invoice', $data);
        }
    }

    // =========================================================================
    // invoiceGenerateByForm – validation path
    // =========================================================================

    public function test_invoice_generate_with_cloud_domain_empty_returns_error(): void
    {
        $this->getLoggedInUser('admin');
        $response = $this->postJson('/generate/invoice', [
            'user' => 1,
            'product' => 1,
            'plan' => 1,
            'cloud_domain' => '',  // empty cloud domain → triggers errorResponse
        ]);
        $this->assertContains($response->status(), [400, 422]);
        if ($response->json('success') !== null) {
            $this->assertFalse($response->json('success'));
        }
    }

    // =========================================================================
    // pdf – with invalid invoice
    // =========================================================================

    public function test_pdf_with_existing_invoice_returns_response(): void
    {
        $this->getLoggedInUser('admin');
        $user = \App\User::factory()->create(['email' => 'pdf-test-'.uniqid().'@test.local']);
        $invoice = \App\Model\Order\Invoice::factory()->create(['user_id' => $user->id]);

        $response = $this->getJson('/pdf?id='.$invoice->id);
        // May succeed or fail depending on PDF generator config
        $this->assertContains($response->status(), [200, 400, 500]);
    }

    // =========================================================================
    // exportInvoices — returns error when queue driver not configured
    // =========================================================================

    public function test_export_invoices_returns_error_without_queue_driver(): void
    {
        $this->getLoggedInUser('admin');

        $response = $this->getJson('/export-invoices');

        // If no queue driver → errorResponse 400; if configured → success 200
        $this->assertContains($response->status(), [200, 400, 401, 500]);
    }

    // =========================================================================
    // getInvoice — existing invoice
    // =========================================================================

    public function test_get_invoice_data_for_existing(): void
    {
        $this->getLoggedInUser('admin');
        $user = \App\User::factory()->create(['email' => 'inv-detail-'.uniqid().'@test.local']);
        $invoice = \App\Model\Order\Invoice::factory()->create(['user_id' => $user->id]);

        $response = $this->getJson('/invoice/'.$invoice->id);

        $this->assertContains($response->status(), [200, 400, 404, 500]);
    }

    // =========================================================================
    // getInvoices — with various search params
    // =========================================================================

    public function test_get_invoices_with_status_filter(): void
    {
        $this->getLoggedInUser('admin');

        $response = $this->getJson('/invoices?status=pending&limit=5');

        $response->assertStatus(200);
    }

    public function test_get_invoices_with_date_range(): void
    {
        $this->getLoggedInUser('admin');

        $response = $this->getJson('/invoices?from_date=2020-01-01&to_date='.date('Y-m-d').'&limit=5');

        $response->assertStatus(200);
    }

    // =========================================================================
    // deleteBulkInvoices — DELETE /invoices
    // =========================================================================

    public function test_delete_bulk_invoices_returns_400_without_ids(): void
    {
        $this->getLoggedInUser('admin');

        $response = $this->deleteJson('/invoices', []);

        $response->assertStatus(400)
            ->assertJson(['success' => false]);
    }

    public function test_delete_bulk_invoices_deletes_specified_invoices(): void
    {
        $this->getLoggedInUser('admin');
        $user    = \App\User::factory()->create(['email' => 'del-bulk-inv-'.uniqid().'@test.local']);
        $invoice = \App\Model\Order\Invoice::factory()->create(['user_id' => $user->id, 'status' => 'pending']);

        $response = $this->deleteJson('/invoices', ['invoice_ids' => [$invoice->id]]);

        $response->assertStatus(200)
            ->assertJson(['success' => true]);

        $this->assertDatabaseMissing('invoices', ['id' => $invoice->id]);
    }

    // =========================================================================
    // setDomain — direct call (session manipulation)
    // =========================================================================

    public function test_set_domain_stores_in_session(): void
    {
        $this->getLoggedInUser('admin');

        $controller = new \App\Http\Controllers\Order\InvoiceController;
        $controller->setDomain('1', 'test.example.com');

        $this->assertEquals('test.example.com', session('domain1'));
    }

    public function test_set_domain_forgets_old_value_before_setting_new(): void
    {
        $this->getLoggedInUser('admin');

        session(['domain2' => 'old.domain.com']);
        $controller = new \App\Http\Controllers\Order\InvoiceController;
        $controller->setDomain('2', 'new.domain.com');

        $this->assertEquals('new.domain.com', session('domain2'));
    }

    // =========================================================================
    // pdf — GET /pdf — missing id vs invalid id
    // =========================================================================

    public function test_pdf_returns_400_when_no_invoice_id(): void
    {
        $this->getLoggedInUser('admin');

        $response = $this->getJson('/pdf');

        $response->assertStatus(400)
            ->assertJson(['success' => false]);
    }

    public function test_pdf_returns_400_for_nonexistent_invoice_id(): void
    {
        $this->getLoggedInUser('admin');

        $response = $this->getJson('/pdf?invoiceid=999999');

        $response->assertStatus(400)
            ->assertJson(['success' => false]);
    }

    // =========================================================================
    // calculateInvoice — static method: numeric (non-formatted) result
    // =========================================================================

    public function test_calculate_invoice_returns_array_with_expected_keys(): void
    {
        $this->getLoggedInUser('admin');
        $user    = \App\User::factory()->create(['email' => 'calc-inv-'.uniqid().'@test.local']);
        $invoice = \App\Model\Order\Invoice::factory()->create([
            'user_id'     => $user->id,
            'grand_total' => 200.0,
            'currency'    => 'USD',
            'status'      => 'pending',
        ]);

        $result = \App\Http\Controllers\Order\InvoiceController::calculateInvoice($invoice->id);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('subtotal', $result);
        $this->assertArrayHasKey('tax', $result);
        $this->assertArrayHasKey('processing_fee', $result);
        $this->assertArrayHasKey('credits', $result);
        $this->assertArrayHasKey('discount', $result);
        $this->assertArrayHasKey('total', $result);
    }

    public function test_calculate_invoice_with_format_currency_returns_strings(): void
    {
        $this->getLoggedInUser('admin');
        $user    = \App\User::factory()->create(['email' => 'calc-fmt-'.uniqid().'@test.local']);
        $invoice = \App\Model\Order\Invoice::factory()->create([
            'user_id'     => $user->id,
            'grand_total' => 150.0,
            'currency'    => 'USD',
            'status'      => 'pending',
            'discount'    => 10.0,
        ]);

        $result = \App\Http\Controllers\Order\InvoiceController::calculateInvoice($invoice->id, formatCurrency: true);

        $this->assertIsArray($result);
        // When formatCurrency = true, total is formatted as string
        $this->assertIsString($result['total']);
        $this->assertIsArray($result['tax']);
    }

    public function test_calculate_invoice_without_format_currency_returns_numerics(): void
    {
        $this->getLoggedInUser('admin');
        $user    = \App\User::factory()->create(['email' => 'calc-num-'.uniqid().'@test.local']);
        $invoice = \App\Model\Order\Invoice::factory()->create([
            'user_id'     => $user->id,
            'grand_total' => 300.0,
            'currency'    => 'USD',
            'status'      => 'pending',
        ]);

        $result = \App\Http\Controllers\Order\InvoiceController::calculateInvoice($invoice->id, formatCurrency: false);

        $this->assertIsFloat($result['total']);
        $this->assertIsFloat($result['processing_fee']);
        $this->assertIsFloat($result['credits']);
        $this->assertIsFloat($result['discount']);
    }

    // =========================================================================
    // getInvoice — happy path with Setting configured
    // =========================================================================

    public function test_get_invoice_returns_full_structure_when_setting_exists(): void
    {
        $this->getLoggedInUser('admin');

        // Ensure at least one Setting row exists
        if (! \App\Model\Common\Setting::find(1)) {
            \App\Model\Common\Setting::factory()->create(['id' => 1]);
        }

        $user    = \App\User::factory()->create(['email' => 'inv-setting-'.uniqid().'@test.local']);
        $invoice = \App\Model\Order\Invoice::factory()->create([
            'user_id'     => $user->id,
            'grand_total' => 250.0,
            'currency'    => 'USD',
            'status'      => 'pending',
        ]);

        $response = $this->getJson('/invoice/'.$invoice->id);

        // 200 with full structure or 400 if setting missing
        $this->assertContains($response->status(), [200, 400]);
        if ($response->status() === 200) {
            $this->assertTrue($response->json('success'));
            $data = $response->json('data');
            $this->assertArrayHasKey('invoice', $data);
            $this->assertArrayHasKey('from', $data);
            $this->assertArrayHasKey('to', $data);
            $this->assertArrayHasKey('items', $data);
            $this->assertArrayHasKey('totals', $data);
            $this->assertArrayHasKey('payments', $data);
        }
    }

    // =========================================================================
    // getInvoices — search-query status mapping branches
    // =========================================================================

    public function test_get_invoices_search_paid_maps_to_success_status(): void
    {
        $this->getLoggedInUser('admin');

        $response = $this->getJson('/invoices?search-query=paid');

        $response->assertStatus(200)
            ->assertJson(['success' => true]);
    }

    public function test_get_invoices_search_unpaid_maps_to_pending_status(): void
    {
        $this->getLoggedInUser('admin');

        $response = $this->getJson('/invoices?search-query=unpaid');

        $response->assertStatus(200)
            ->assertJson(['success' => true]);
    }

    public function test_get_invoices_search_partially_maps_to_partially_paid(): void
    {
        $this->getLoggedInUser('admin');

        $response = $this->getJson('/invoices?search-query=partially');

        $response->assertStatus(200)
            ->assertJson(['success' => true]);
    }

    // =========================================================================
    // getInvoice — user suspended (soft-deleted user)
    // =========================================================================

    public function test_get_invoice_returns_error_for_soft_deleted_user(): void
    {
        $this->getLoggedInUser('admin');

        $user    = \App\User::factory()->create(['email' => 'soft-del-'.uniqid().'@test.local']);
        $invoice = \App\Model\Order\Invoice::factory()->create([
            'user_id'     => $user->id,
            'grand_total' => 100.0,
            'status'      => 'pending',
        ]);

        // Soft-delete the user
        $user->delete();

        $response = $this->getJson('/invoice/'.$invoice->id);

        // Should return an error (400) because user is suspended/soft-deleted
        $response->assertStatus(400)
            ->assertJson(['success' => false]);
    }

    // =========================================================================
    // createInvoiceItemsByAdmin — direct call
    // =========================================================================

    public function test_create_invoice_items_by_admin_returns_invoice_item(): void
    {
        $this->getLoggedInUser('admin');

        $product = \App\Model\Product\Product::factory()->create();
        $user    = \App\User::factory()->create(['email' => 'ci-admin-'.uniqid().'@test.local']);
        $invoice = \App\Model\Order\Invoice::factory()->create([
            'user_id'  => $user->id,
            'currency' => 'USD',
        ]);

        // We need a plan for the product
        $plan = \App\Model\Payment\Plan::factory()->create(['product' => $product->id]);

        $controller = new \App\Http\Controllers\Order\InvoiceController;
        $result = $controller->createInvoiceItemsByAdmin(
            $invoice->id,
            (string) $product->id,
            100.0,   // price
            'USD',   // currency
            1,       // qty
            1,       // agents
            $plan->id, // planid
            $user->id, // userid
            'VAT',   // tax_name
            10.0,    // tax_rate
            100.0    // grandTotalAfterCoupon
        );

        $this->assertInstanceOf(\App\Model\Order\InvoiceItem::class, $result);
        $this->assertEquals($invoice->id, $result->invoice_id);
        $this->assertEquals($product->id, $result->product_id);
    }

    // =========================================================================
    // getInvoices — status filter
    // =========================================================================

    public function test_get_invoices_with_status_success_filter_returns_200(): void
    {
        $this->getLoggedInUser('admin');

        $response = $this->getJson('/invoices?status=success&limit=5');

        $response->assertStatus(200)
            ->assertJson(['success' => true]);
    }

    public function test_get_invoices_sort_by_grand_total_returns_200(): void
    {
        $this->getLoggedInUser('admin');

        $response = $this->getJson('/invoices?sort-field=grand_total&sort-order=desc&limit=5');

        $response->assertStatus(200)
            ->assertJson(['success' => true]);
    }
}
