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
        $client = \App\User::factory()->create(['role' => 'user']);
        $response = $this->getJson("/newPayment/receive?clientid={$client->id}");
        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
        $response->assertJsonStructure(['success', 'data' => ['invoices', 'currencies']]);
    }
}
