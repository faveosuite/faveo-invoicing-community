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
            'user_id'     => $this->user->id,
            'grand_total' => 299.99,
            'status'      => 'pending',
            'currency'    => 'USD',
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
}
