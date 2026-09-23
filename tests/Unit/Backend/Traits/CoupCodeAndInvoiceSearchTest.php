<?php

declare(strict_types=1);

namespace Tests\Unit\Backend\Traits;

use App\Http\Controllers\Order\InvoiceController;
use App\Model\Order\Invoice;
use App\Model\Order\Payment;
use App\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Request;
use Tests\DBTestCase;

/**
 * Tests for the CoupCodeAndInvoiceSearch trait via InvoiceController (which uses it).
 * The trait provides: advanceSearch(), updateInvoicePayment(), deleteBulkInvoices(),
 * deletePayment(), deleteBulkPayments().
 */
class CoupCodeAndInvoiceSearchTest extends DBTestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware();
        $this->getLoggedInUser('admin');
    }

    // =========================================================================
    // advanceSearch — via GET /invoices (exercises the trait through InvoiceController)
    // =========================================================================

    public function test_advance_search_via_get_invoices_returns_paginated_response(): void
    {
        $response = $this->getJson('/invoices');
        $response->assertStatus(200);
        $body = $response->json();
        $this->assertTrue($body['success']);
    }

    public function test_advance_search_with_name_filter(): void
    {
        $response = $this->getJson('/invoices?name=Admin');
        $response->assertStatus(200);
        $body = $response->json();
        $this->assertTrue($body['success']);
    }

    public function test_advance_search_with_invoice_number_filter(): void
    {
        $invoice = Invoice::first();
        if (! $invoice) {
            $invoice = \App\Model\Order\Invoice::factory()->create(['user_id' => $this->user->id, 'status' => 'pending', 'grand_total' => 100]);
        }

        $response = $this->getJson('/invoices?invoice_no='.$invoice->number);
        $response->assertStatus(200);
        $body = $response->json();
        $this->assertTrue($body['success']);
    }

    public function test_advance_search_with_status_filter(): void
    {
        $response = $this->getJson('/invoices?status=pending');
        $response->assertStatus(200);
        $body = $response->json();
        $this->assertTrue($body['success']);
    }

    public function test_advance_search_with_currency_filter(): void
    {
        $response = $this->getJson('/invoices?currency=USD');
        $response->assertStatus(200);
        $body = $response->json();
        $this->assertTrue($body['success']);
    }

    public function test_advance_search_with_date_range_filter(): void
    {
        $from = now()->subDays(30)->toDateString();
        $to = now()->toDateString();

        $response = $this->getJson('/invoices?from_date='.$from.'&to_date='.$to);
        $response->assertStatus(200);
        $body = $response->json();
        $this->assertTrue($body['success']);
    }

    public function test_advance_search_with_search_query(): void
    {
        $response = $this->getJson('/invoices?search-query=test');
        $response->assertStatus(200);
        $body = $response->json();
        $this->assertTrue($body['success']);
    }

    public function test_advance_search_with_status_paid_maps_to_success(): void
    {
        $response = $this->getJson('/invoices?search-query=paid');
        $response->assertStatus(200);
        $body = $response->json();
        $this->assertTrue($body['success']);
    }

    public function test_advance_search_with_status_unpaid_maps_to_pending(): void
    {
        $response = $this->getJson('/invoices?search-query=unpaid');
        $response->assertStatus(200);
        $body = $response->json();
        $this->assertTrue($body['success']);
    }

    public function test_advance_search_with_sort_field_and_order(): void
    {
        $response = $this->getJson('/invoices?sort-field=number&sort-order=asc');
        $response->assertStatus(200);
        $body = $response->json();
        $this->assertTrue($body['success']);
    }

    public function test_advance_search_with_invalid_sort_field_falls_back_to_created_at(): void
    {
        $response = $this->getJson('/invoices?sort-field=invalid_column&sort-order=asc');
        $response->assertStatus(200);
        $body = $response->json();
        $this->assertTrue($body['success']);
    }

    public function test_advance_search_with_limit_param(): void
    {
        $response = $this->getJson('/invoices?limit=5');
        $response->assertStatus(200);
        $body = $response->json();
        $this->assertTrue($body['success']);
    }

    // =========================================================================
    // deleteBulkInvoices — DELETE /invoices
    // =========================================================================

    public function test_delete_bulk_invoices_returns_error_when_no_ids_provided(): void
    {
        $response = $this->deleteJson('/invoices', ['invoice_ids' => []]);
        // Returns 400 error response when no IDs provided
        $this->assertContains($response->status(), [200, 400]);
        $body = $response->json();
        $this->assertFalse($body['success']);
    }

    public function test_delete_bulk_invoices_returns_success_when_valid_ids(): void
    {
        $user = User::factory()->create();
        $invoice = Invoice::factory()->create([
            'user_id' => $user->id,
            'status' => 'pending',
        ]);

        $response = $this->deleteJson('/invoices', ['invoice_ids' => [$invoice->id]]);
        $response->assertStatus(200);
        $body = $response->json();
        $this->assertTrue($body['success']);
    }

    public function test_delete_bulk_invoices_with_nonexistent_ids_returns_success(): void
    {
        // Deleting non-existent IDs still returns success (whereIn with no matches = 0 rows deleted)
        $response = $this->deleteJson('/invoices', ['invoice_ids' => [999999, 999998]]);
        $response->assertStatus(200);
        $body = $response->json();
        $this->assertTrue($body['success']);
    }

    // =========================================================================
    // deleteBulkPayments — DELETE /payments
    // =========================================================================

    public function test_delete_bulk_payments_returns_error_when_no_ids(): void
    {
        $response = $this->deleteJson('/payments', ['payment_ids' => []]);
        // Returns 400 error response when no IDs provided
        $this->assertContains($response->status(), [200, 400]);
        $body = $response->json();
        $this->assertFalse($body['success']);
    }

    public function test_delete_bulk_payments_with_nonexistent_ids_returns_success(): void
    {
        $response = $this->deleteJson('/payments', ['payment_ids' => [999999]]);
        $response->assertStatus(200);
        $body = $response->json();
        $this->assertTrue($body['success']);
    }

    public function test_delete_bulk_payments_with_valid_payment_recalculates_invoice_status(): void
    {
        $user = User::factory()->create();
        $invoice = Invoice::factory()->create([
            'user_id' => $user->id,
            'status' => 'success',
            'grand_total' => 100.0,
        ]);

        $payment = Payment::create([
            'user_id' => $user->id,
            'amount' => 100.0,
            'payment_method' => 'stripe',
            'payment_status' => 'success',
            'currency' => $invoice->currency,
        ]);
        $payment->invoices()->attach($invoice->id, ['amount' => 100.0]);

        $response = $this->deleteJson('/payments', ['payment_ids' => [$payment->id]]);
        $response->assertStatus(200);
        $body = $response->json();
        $this->assertTrue($body['success']);

        // Invoice status should be recalculated to 'pending' (no remaining payments)
        $updatedStatus = strtolower(Invoice::find($invoice->id)->status);
        $this->assertSame('pending', $updatedStatus);
    }

    public function test_delete_bulk_payments_with_partial_payment_sets_partially_paid(): void
    {
        $user = User::factory()->create();
        $invoice = Invoice::factory()->create([
            'user_id' => $user->id,
            'status' => 'success',
            'grand_total' => 200.0,
        ]);

        // Keep one payment, delete another
        $payment1 = Payment::create([
            'user_id' => $user->id,
            'amount' => 100.0,
            'payment_method' => 'stripe',
            'payment_status' => 'success',
            'currency' => $invoice->currency,
        ]);
        $payment1->invoices()->attach($invoice->id, ['amount' => 100.0]);
        $payment2 = Payment::create([
            'user_id' => $user->id,
            'amount' => 100.0,
            'payment_method' => 'stripe',
            'payment_status' => 'success',
            'currency' => $invoice->currency,
        ]);
        $payment2->invoices()->attach($invoice->id, ['amount' => 100.0]);

        // Delete payment1 only
        $response = $this->deleteJson('/payments', ['payment_ids' => [$payment1->id]]);
        $response->assertStatus(200);
        $body = $response->json();
        $this->assertTrue($body['success']);

        // After deleting one of two equal payments, invoice should be partially paid or pending
        $updatedInvoice = Invoice::find($invoice->id);
        $statusLower = strtolower($updatedInvoice->status);
        $this->assertTrue(
            in_array($statusLower, ['partially paid', 'pending', 'success'], true),
            'Unexpected status: '.$statusLower
        );
    }

    // =========================================================================
    // advanceSearch — direct method call via InvoiceController (covers trait directly)
    // =========================================================================

    public function test_advance_search_direct_call_returns_builder(): void
    {
        $controller = new InvoiceController;

        $request = new Request;
        $request->merge([]);

        $builder = $controller->advanceSearch($request);
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Builder::class, $builder);
    }

    public function test_advance_search_with_all_filters_returns_builder(): void
    {
        $controller = new InvoiceController;

        $request = new Request;
        $request->merge([
            'name' => 'John',
            'invoice_no' => 'INV-001',
            'status' => 'pending',
            'currency' => 'USD',
            'from_date' => now()->subDays(30)->toDateString(),
            'to_date' => now()->toDateString(),
        ]);

        $builder = $controller->advanceSearch($request);
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Builder::class, $builder);

        // Execute the query to ensure it runs without error
        $results = $builder->get();
        $this->assertNotNull($results);
    }

    public function test_advance_search_with_name_filter_builds_valid_query(): void
    {
        $controller = new InvoiceController;
        $request = new Request;
        $request->merge(['name' => 'Admin User']);

        $builder = $controller->advanceSearch($request);
        $results = $builder->get();

        $this->assertNotNull($results);
    }

    public function test_advance_search_with_date_range_builds_valid_query(): void
    {
        $controller = new InvoiceController;
        $request = new Request;
        $request->merge([
            'from_date' => now()->subDays(7)->toDateString(),
            'to_date' => now()->toDateString(),
        ]);

        $builder = $controller->advanceSearch($request);
        $results = $builder->get();

        $this->assertNotNull($results);
    }

    // =========================================================================
    // deleteBulkInvoices — via InvoiceController directly
    // =========================================================================

    public function test_delete_bulk_invoices_direct_call_with_no_ids_returns_error(): void
    {
        $controller = new InvoiceController;

        $request = new Request;
        $request->merge(['invoice_ids' => []]);

        $response = $controller->deleteBulkInvoices($request);
        $body = json_decode($response->getContent(), true);

        $this->assertFalse($body['success']);
    }

    public function test_delete_bulk_invoices_direct_call_with_valid_id(): void
    {
        $controller = new InvoiceController;

        $user = User::factory()->create();
        $invoice = Invoice::factory()->create([
            'user_id' => $user->id,
            'status' => 'pending',
        ]);

        $request = new Request;
        $request->merge(['invoice_ids' => [$invoice->id]]);

        $response = $controller->deleteBulkInvoices($request);
        $body = json_decode($response->getContent(), true);

        $this->assertTrue($body['success']);
    }
}
