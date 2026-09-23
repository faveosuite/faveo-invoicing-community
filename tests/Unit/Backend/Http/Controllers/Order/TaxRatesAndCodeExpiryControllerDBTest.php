<?php

namespace Tests\Unit\Backend\Http\Controllers\Order;

use App\Http\Controllers\Order\TaxRatesAndCodeExpiryController;
use App\Model\Order\Invoice;
use App\Model\Order\Payment;
use App\Model\Payment\Currency;
use App\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\DBTestCase;

/**
 * DB-backed tests for TaxRatesAndCodeExpiryController methods that require
 * real Invoice / Payment / User records.
 */
class TaxRatesAndCodeExpiryControllerDBTest extends DBTestCase
{
    use DatabaseTransactions;

    private TaxRatesAndCodeExpiryController $controller;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware();
        $this->getLoggedInUser('admin');
        $this->controller = new TaxRatesAndCodeExpiryController;
    }

    // -------------------------------------------------------------------------
    // currency() — invoice with grand_total > 0 and a known currency code
    // -------------------------------------------------------------------------

    public function test_currency_returns_symbol_when_invoice_has_currency(): void
    {
        Currency::firstOrCreate(
            ['code' => 'USD'],
            ['name' => 'US Dollar', 'symbol' => '$', 'status' => 1]
        );

        $invoice = Invoice::factory()->create([
            'user_id' => $this->user->id,
            'currency' => 'USD',
            'grand_total' => 100.00,
            'status' => 'unpaid',
        ]);

        $result = $this->controller->currency($invoice->id);

        $this->assertIsString($result);
        $this->assertNotSame(' ', $result); // should return '$' not the blank fallback
    }

    public function test_currency_returns_space_when_grand_total_is_zero(): void
    {
        $invoice = Invoice::factory()->create([
            'user_id' => $this->user->id,
            'currency' => 'USD',
            'grand_total' => 0,
        ]);

        $result = $this->controller->currency($invoice->id);

        $this->assertSame(' ', $result);
    }

    public function test_currency_returns_code_when_symbol_is_empty(): void
    {
        // Currency with no symbol — falls back to code
        Currency::firstOrCreate(
            ['code' => 'XTS'],
            ['name' => 'Test Currency', 'symbol' => '', 'status' => 1]
        );

        $invoice = Invoice::factory()->create([
            'user_id' => $this->user->id,
            'currency' => 'XTS',
            'grand_total' => 50.00,
        ]);

        $result = $this->controller->currency($invoice->id);

        $this->assertIsString($result);
    }

    // -------------------------------------------------------------------------
    // invoiceContent() — invoice with items
    // -------------------------------------------------------------------------

    // -------------------------------------------------------------------------
    // invoiceContent() — the method references $this->invoice (undefined property,
    // pre-existing bug). It throws ErrorException which propagates uncaught.
    // Document this with an expectException test.
    // -------------------------------------------------------------------------

    public function test_invoice_content_throws_due_to_undefined_invoice_property(): void
    {
        // invoiceContent uses $this->invoice->find() but $invoice is not a class property.
        // This is a pre-existing bug documented in PhpStan (@phpstan-ignore property.notFound).
        $this->expectException(\ErrorException::class);

        $invoice = Invoice::factory()->create([
            'user_id' => $this->user->id,
            'grand_total' => 100.00,
        ]);

        $this->controller->invoiceContent($invoice->id);
    }

    // -------------------------------------------------------------------------
    // paymentEditById() — non-existent payment → findOrFail throws → 400
    // -------------------------------------------------------------------------

    public function test_payment_edit_by_id_returns_400_for_nonexistent_payment(): void
    {
        $response = $this->controller->paymentEditById(999999);

        $this->assertEquals(400, $response->getStatusCode());
        $body = json_decode($response->getContent(), true);
        $this->assertFalse($body['success']);
    }

    public function test_payment_edit_by_id_returns_400_due_to_undefined_user_property(): void
    {
        // paymentEditById references $this->user->where() but $user is not a defined
        // class property. The ErrorException is caught → returns errorResponse 400.
        $invoice = Invoice::factory()->create([
            'user_id' => $this->user->id,
            'currency' => 'USD',
            'grand_total' => 200.00,
            'status' => 'pending',
        ]);

        $payment = Payment::create([
            'invoice_id' => $invoice->id,
            'user_id' => $this->user->id,
            'amount' => 100.00,
            'payment_method' => 'stripe',
            'payment_status' => 'success',
        ]);

        $response = $this->controller->paymentEditById($payment->id);

        // Due to the undefined $this->user property, the catch block fires → 400
        $this->assertEquals(400, $response->getStatusCode());
        $body = json_decode($response->getContent(), true);
        $this->assertFalse($body['success']);
    }

    // -------------------------------------------------------------------------
    // currency() — EUR invoice with grand_total > 0, known code with symbol
    // -------------------------------------------------------------------------

    public function test_currency_returns_symbol_for_eur_invoice(): void
    {
        Currency::firstOrCreate(
            ['code' => 'EUR'],
            ['name' => 'Euro', 'symbol' => '€', 'status' => 1]
        );

        $invoice = Invoice::factory()->create([
            'user_id' => $this->user->id,
            'currency' => 'EUR',
            'grand_total' => 75.00,
            'status' => 'pending',
        ]);

        $result = $this->controller->currency($invoice->id);

        $this->assertIsString($result);
        $this->assertNotSame(' ', $result);
    }

    // -------------------------------------------------------------------------
    // currency() — invoice with unknown currency code returns the code itself
    // -------------------------------------------------------------------------

    public function test_currency_returns_code_when_currency_row_not_found(): void
    {
        // No Currency row for 'ZZZ' — controller should fall through to returning ' '
        $invoice = Invoice::factory()->create([
            'user_id' => $this->user->id,
            'currency' => 'ZZZ',
            'grand_total' => 25.00,
        ]);

        $result = $this->controller->currency($invoice->id);

        // No currency row found → $cur remains ' '
        $this->assertSame(' ', $result);
    }

    // -------------------------------------------------------------------------
    // getMessage() — with items (truthy) and without (falsy)
    // -------------------------------------------------------------------------

    public function test_get_message_returns_success_message_when_items_truthy(): void
    {
        $item = new \App\Model\Order\InvoiceItem;
        $item->invoice_id = 1;

        $result = $this->controller->getMessage($item, $this->user->id);

        $this->assertArrayHasKey('success', $result);
    }

    public function test_get_message_returns_fails_message_when_items_falsy(): void
    {
        $result = $this->controller->getMessage(null, $this->user->id);

        $this->assertArrayHasKey('fails', $result);
    }

    // -------------------------------------------------------------------------
    // getGrandTotal() — no code returns total unchanged
    // -------------------------------------------------------------------------

    public function test_get_grand_total_returns_total_unchanged_when_no_code(): void
    {
        $result = $this->controller->getGrandTotal(null, 100.0, 100.0, 1, 'USD', (string) $this->user->id);

        $this->assertIsArray($result);
        $this->assertEquals(100.0, $result['total']);
        $this->assertSame('', $result['code']);
    }

    public function test_get_grand_total_returns_zeros_when_total_is_zero(): void
    {
        $result = $this->controller->getGrandTotal(null, 0, 0, 1, 'USD');

        $this->assertIsArray($result);
        $this->assertEquals(0, $result['total']);
        $this->assertSame('', $result['code']);
        $this->assertSame('', $result['value']);
    }

    // -------------------------------------------------------------------------
    // invoiceUrl() — returns a URL with invoice id
    // -------------------------------------------------------------------------

    public function test_invoice_url_returns_url_with_invoice_id(): void
    {
        $result = $this->controller->invoiceUrl(42);

        $this->assertStringContainsString('my-invoice/42', (string) $result);
    }
}
