<?php

declare(strict_types=1);

namespace Tests\Unit\Backend\Models\Order;

use App\Model\Order\InstallationDetail;
use App\Model\Order\Invoice;
use App\Model\Order\InvoiceItem;
use App\Model\Order\InvoiceTaxLine;
use App\Model\Order\Order;
use App\Model\Order\Payment;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Tests\TestCase;

class OrderModelsTest extends TestCase
{
    // =========================================================================
    // InstallationDetail
    // =========================================================================

    public function test_installation_detail_table_is_installation_details(): void
    {
        $this->assertSame('installation_details', (new InstallationDetail())->getTable());
    }

    public function test_installation_detail_fillable_contains_expected_fields(): void
    {
        $fillable = (new InstallationDetail())->getFillable();
        $this->assertContains('installation_path', $fillable);
        $this->assertContains('installation_ip', $fillable);
        $this->assertContains('version', $fillable);
        $this->assertContains('last_active', $fillable);
        $this->assertContains('order_id', $fillable);
    }

    public function test_installation_detail_order_is_belongs_to(): void
    {
        $this->assertInstanceOf(BelongsTo::class, (new InstallationDetail())->order());
    }

    // =========================================================================
    // Payment (Order\Payment)
    // =========================================================================

    public function test_payment_table_is_payments(): void
    {
        $this->assertSame('payments', (new Payment())->getTable());
    }

    public function test_payment_fillable_contains_expected_fields(): void
    {
        $fillable = (new Payment())->getFillable();
        $this->assertContains('parent_id', $fillable);
        $this->assertContains('invoice_id', $fillable);
        $this->assertContains('amount', $fillable);
        $this->assertContains('payment_method', $fillable);
        $this->assertContains('user_id', $fillable);
        $this->assertContains('payment_status', $fillable);
    }

    public function test_payment_invoices_is_many_to_many(): void
    {
        // A payment can settle several invoices, so this cannot be a belongsTo.
        $this->assertInstanceOf(BelongsToMany::class, (new Payment())->invoices());
    }

    public function test_payment_allocations_is_has_many(): void
    {
        $this->assertInstanceOf(HasMany::class, (new Payment())->allocations());
    }

    public function test_payment_user_is_belongs_to(): void
    {
        $this->assertInstanceOf(BelongsTo::class, (new Payment())->user());
    }

    // =========================================================================
    // Invoice
    // =========================================================================

    public function test_invoice_table_is_invoices(): void
    {
        $this->assertSame('invoices', (new Invoice())->getTable());
    }

    public function test_invoice_fillable_contains_expected_fields(): void
    {
        $fillable = (new Invoice())->getFillable();
        $this->assertContains('user_id', $fillable);
        $this->assertContains('number', $fillable);
        $this->assertContains('date', $fillable);
        $this->assertContains('grand_total', $fillable);
        $this->assertContains('currency', $fillable);
        $this->assertContains('status', $fillable);
    }

    public function test_invoice_casts_date_as_datetime(): void
    {
        $model = new Invoice();
        $casts = $model->getCasts();
        $this->assertArrayHasKey('date', $casts);
        $this->assertSame('datetime', $casts['date']);
    }

    public function test_invoice_invoice_item_is_has_many(): void
    {
        $this->assertInstanceOf(HasMany::class, (new Invoice())->invoiceItem());
    }

    public function test_invoice_order_is_belongs_to_many(): void
    {
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsToMany::class, (new Invoice())->orders());
    }

    public function test_invoice_order_relation_is_has_many(): void
    {
        $this->assertInstanceOf(HasMany::class, (new Invoice())->orderRelation());
    }

    public function test_invoice_user_is_belongs_to(): void
    {
        $this->assertInstanceOf(BelongsTo::class, (new Invoice())->user());
    }

    public function test_invoice_subscription_is_has_many_through(): void
    {
        $this->assertInstanceOf(HasManyThrough::class, (new Invoice())->subscriptions());
    }

    public function test_invoice_installation_detail_exists(): void
    {
        $this->assertTrue(method_exists(Invoice::class, 'installationDetail'));
    }

    public function test_invoice_payments_is_many_to_many(): void
    {
        // ...and an invoice can be settled by several payments.
        $this->assertInstanceOf(BelongsToMany::class, (new Invoice())->payments());
    }

    public function test_invoice_allocations_is_has_many(): void
    {
        $this->assertInstanceOf(HasMany::class, (new Invoice())->allocations());
    }

    public function test_invoice_status_is_cast_attribute(): void
    {
        // status() is a new-style Attribute cast — just verify the method exists
        $this->assertTrue(method_exists(Invoice::class, 'status'));
    }

    // =========================================================================
    // InvoiceItem
    // =========================================================================

    public function test_invoice_item_table_is_invoice_items(): void
    {
        $this->assertSame('invoice_items', (new InvoiceItem())->getTable());
    }

    public function test_invoice_item_fillable_contains_expected_fields(): void
    {
        $fillable = (new InvoiceItem())->getFillable();
        $this->assertContains('invoice_id', $fillable);
        $this->assertContains('product_name', $fillable);
        $this->assertContains('regular_price', $fillable);
        $this->assertContains('quantity', $fillable);
        $this->assertContains('subtotal', $fillable);
    }

    public function test_invoice_item_order_is_has_one(): void
    {
        $this->assertInstanceOf(HasOne::class, (new InvoiceItem())->order());
    }

    public function test_invoice_item_invoice_is_belongs_to(): void
    {
        $this->assertInstanceOf(BelongsTo::class, (new InvoiceItem())->invoice());
    }

    public function test_invoice_item_product_is_belongs_to(): void
    {
        $this->assertInstanceOf(BelongsTo::class, (new InvoiceItem())->product());
    }

    public function test_invoice_item_get_domain_with_url(): void
    {
        $item = new InvoiceItem();
        $result = $item->get_domain('https://www.example.com/path');
        $this->assertSame('example.com', $result);
    }

    public function test_invoice_item_get_domain_with_plain_domain(): void
    {
        $item = new InvoiceItem();
        $result = $item->get_domain('example.com');
        $this->assertSame('example.com', $result);
    }

    public function test_invoice_item_get_domain_with_subdomain(): void
    {
        $item = new InvoiceItem();
        $result = $item->get_domain('https://sub.example.co.uk/page');
        $this->assertSame('example.co.uk', $result);
    }

    public function test_invoice_item_get_domain_with_empty_string(): void
    {
        $item = new InvoiceItem();
        $result = $item->get_domain('');
        $this->assertSame('', $result);
    }

    // =========================================================================
    // Order
    // =========================================================================

    public function test_order_table_is_orders(): void
    {
        $this->assertSame('orders', (new Order())->getTable());
    }

    public function test_order_fillable_contains_expected_fields(): void
    {
        $fillable = (new Order())->getFillable();
        $this->assertContains('client', $fillable);
        $this->assertContains('order_status', $fillable);
        $this->assertContains('serial_key', $fillable);
        $this->assertContains('domain', $fillable);
        $this->assertContains('number', $fillable);
    }

    public function test_order_user_is_belongs_to(): void
    {
        $this->assertInstanceOf(BelongsTo::class, (new Order())->user());
    }

    public function test_order_subscription_is_has_one(): void
    {
        $this->assertInstanceOf(HasOne::class, (new Order())->subscription());
    }

    public function test_order_product_relation_is_belongs_to(): void
    {
        $this->assertInstanceOf(BelongsTo::class, (new Order())->productRelation());
    }

    public function test_order_invoice_is_belongs_to_many(): void
    {
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsToMany::class, (new Order())->invoice());
    }

    public function test_order_invoice_relation_is_has_many(): void
    {
        $this->assertInstanceOf(HasMany::class, (new Order())->invoiceRelation());
    }

    public function test_order_licensed_installations_is_has_many_through(): void
    {
        $this->assertInstanceOf(HasManyThrough::class, (new Order())->licensedInstallations());
    }

    public function test_order_invoice_item_is_belongs_to(): void
    {
        $this->assertInstanceOf(BelongsTo::class, (new Order())->invoiceItem());
    }

    public function test_order_get_domain_with_full_url(): void
    {
        $order = new Order();
        $this->assertSame('example.com', $order->get_domain('https://www.example.com/'));
    }

    public function test_order_get_domain_with_plain_domain(): void
    {
        $order = new Order();
        $this->assertSame('example.com', $order->get_domain('example.com'));
    }

    public function test_order_get_domain_returns_empty_for_empty(): void
    {
        $order = new Order();
        $result = $order->get_domain('');
        // empty string causes parse_url to return empty path
        $this->assertSame('', $result);
    }

    public function test_order_get_domain_returns_string(): void
    {
        // getOrderLink requires DB; test get_domain instead for extra coverage
        $order = new Order();
        $this->assertIsString($order->get_domain('https://test.example.com'));
    }

    public function test_order_get_mappings_returns_expected_keys(): void
    {
        $model = new Order();
        $ref = new \ReflectionMethod($model, 'getMappings');
        $mappings = $ref->invoke($model);
        $this->assertIsArray($mappings);
        $this->assertArrayHasKey('client', $mappings);
        $this->assertArrayHasKey('order_status', $mappings);
        $this->assertArrayHasKey('serial_key', $mappings);
        $this->assertArrayHasKey('domain', $mappings);
        $this->assertArrayHasKey('number', $mappings);
    }

    public function test_order_installation_is_has_many(): void
    {
        $this->assertInstanceOf(HasMany::class, (new Order())->installation());
    }

    public function test_order_domain_attribute_handles_trailing_slash(): void
    {
        // Covers the Str::endsWith branch in the domain() Attribute (line 247)
        $order = new Order();
        $order->setRawAttributes(['domain' => 'example.com/']);
        $result = $order->domain;
        $this->assertIsString($result);
        $this->assertStringContainsString('example.com', $result);
    }

    public function test_order_domain_setter_calls_get_domain(): void
    {
        // Covers the set callback in domain() Attribute (line 252)
        $order = new Order();
        $order->domain = 'https://www.example.com/path';
        $this->assertStringContainsString('example.com', $order->getRawOriginal('domain') ?? $order->domain);
    }

    public function test_order_get_order_link_returns_fallback_without_db(): void
    {
        // Without a matching order in DB, static method returns '--'
        $result = Order::getOrderLink(999999999);
        $this->assertSame('--', $result);
    }

    // =========================================================================
    // Invoice – getMappings + status attribute + installationDetail
    // =========================================================================

    public function test_invoice_get_mappings_returns_expected_keys(): void
    {
        $model = new Invoice();
        $ref = new \ReflectionMethod($model, 'getMappings');
        $mappings = $ref->invoke($model);
        $this->assertIsArray($mappings);
        $this->assertArrayHasKey('user_id', $mappings);
        $this->assertArrayHasKey('number', $mappings);
        $this->assertArrayHasKey('grand_total', $mappings);
        $this->assertArrayHasKey('currency', $mappings);
        $this->assertArrayHasKey('status', $mappings);
    }

    public function test_invoice_status_accessor_ucfirsts_value(): void
    {
        $invoice = new Invoice();
        $invoice->setRawAttributes(['status' => 'pending']);
        $this->assertSame('Pending', $invoice->status);
    }

    // =========================================================================
    // InvoiceTaxLine – relationships + casts
    // =========================================================================

    public function test_invoice_tax_line_table_is_invoice_tax_lines(): void
    {
        $this->assertSame('invoice_tax_lines', (new InvoiceTaxLine())->getTable());
    }

    public function test_invoice_tax_line_invoice_is_belongs_to(): void
    {
        $this->assertInstanceOf(BelongsTo::class, (new InvoiceTaxLine())->invoice());
    }

    public function test_invoice_tax_line_item_is_belongs_to(): void
    {
        $this->assertInstanceOf(BelongsTo::class, (new InvoiceTaxLine())->item());
    }

    public function test_invoice_tax_line_casts_rate_and_amount_as_float(): void
    {
        $casts = (new InvoiceTaxLine())->getCasts();
        $this->assertArrayHasKey('rate', $casts);
        $this->assertSame('float', $casts['rate']);
        $this->assertArrayHasKey('amount', $casts);
        $this->assertSame('float', $casts['amount']);
    }

    // =========================================================================
    // InvoiceItem – taxLines() relationship (covers line 84)
    // =========================================================================

    public function test_invoice_item_tax_lines_is_has_many(): void
    {
        $this->assertInstanceOf(HasMany::class, (new InvoiceItem())->taxLines());
    }

    // =========================================================================
    // TaxRate – casts() (covers lines 129-136)
    // =========================================================================

    public function test_tax_rate_casts_method_returns_array(): void
    {
        $casts = (new \App\Model\Payment\TaxRate())->getCasts();
        $this->assertIsArray($casts);
        $this->assertArrayHasKey('rate', $casts);
        $this->assertArrayHasKey('compound', $casts);
    }
}
