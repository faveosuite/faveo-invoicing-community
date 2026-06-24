<?php

declare(strict_types=1);

namespace Tests\Unit\Backend\Exports;

use App\Exports\InvoiceExport;
use App\Exports\OrderExport;
use App\Exports\UsersExport;
use Illuminate\Support\Collection;
use Tests\DBTestCase;

class ExportsTest extends DBTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware();
    }

    // =========================================================================
    // InvoiceExport
    // =========================================================================

    public function test_invoice_export_collection_returns_collection(): void
    {
        $export = new InvoiceExport(['id'], [], 0);

        $result = $export->collection();

        $this->assertInstanceOf(Collection::class, $result);
    }

    public function test_invoice_export_headings_returns_non_empty_array(): void
    {
        $export = new InvoiceExport(['id'], [], 0);

        $headings = $export->headings();

        $this->assertIsArray($headings);
        $this->assertNotEmpty($headings);
    }

    public function test_invoice_export_title_returns_non_empty_string(): void
    {
        $export = new InvoiceExport(['id'], [], 0);

        $title = $export->title();

        $this->assertIsString($title);
        $this->assertNotEmpty($title);
    }

    public function test_invoice_export_title_includes_sheet_index(): void
    {
        $export = new InvoiceExport(['id'], [], 3);

        $this->assertSame('Sheet 3', $export->title());
    }

    public function test_invoice_export_headings_maps_known_columns(): void
    {
        $export = new InvoiceExport(['email', 'status'], [], 0);

        $headings = $export->headings();

        $this->assertSame('Email', $headings[0]);
        $this->assertSame('Status', $headings[1]);
    }

    public function test_invoice_export_headings_passes_through_unknown_columns(): void
    {
        $export = new InvoiceExport(['unknown_col'], [], 0);

        $headings = $export->headings();

        $this->assertSame('unknown_col', $headings[0]);
    }

    // =========================================================================
    // OrderExport
    // =========================================================================

    public function test_order_export_collection_returns_collection(): void
    {
        $export = new OrderExport(['id'], [], 0);

        $result = $export->collection();

        $this->assertInstanceOf(Collection::class, $result);
    }

    public function test_order_export_headings_returns_non_empty_array(): void
    {
        $export = new OrderExport(['id'], [], 0);

        $headings = $export->headings();

        $this->assertIsArray($headings);
        $this->assertNotEmpty($headings);
    }

    public function test_order_export_title_returns_non_empty_string(): void
    {
        $export = new OrderExport(['id'], [], 0);

        $title = $export->title();

        $this->assertIsString($title);
        $this->assertNotEmpty($title);
    }

    public function test_order_export_title_is_orders(): void
    {
        $export = new OrderExport(['id'], [], 5);

        $this->assertSame('Orders', $export->title());
    }

    public function test_order_export_headings_maps_known_columns(): void
    {
        $export = new OrderExport(['client', 'email'], [], 0);

        $headings = $export->headings();

        $this->assertSame('User', $headings[0]);
        $this->assertSame('Email', $headings[1]);
    }

    public function test_order_export_headings_passes_through_unknown_columns(): void
    {
        $export = new OrderExport(['custom_col'], [], 0);

        $headings = $export->headings();

        $this->assertSame('custom_col', $headings[0]);
    }

    // =========================================================================
    // UsersExport
    // =========================================================================

    public function test_users_export_collection_returns_collection(): void
    {
        $export = new UsersExport(['id'], [], 0);

        $result = $export->collection();

        $this->assertInstanceOf(Collection::class, $result);
    }

    public function test_users_export_headings_returns_non_empty_array(): void
    {
        $export = new UsersExport(['id'], [], 0);

        $headings = $export->headings();

        $this->assertIsArray($headings);
        $this->assertNotEmpty($headings);
    }

    public function test_users_export_title_returns_non_empty_string(): void
    {
        $export = new UsersExport(['id'], [], 0);

        $title = $export->title();

        $this->assertIsString($title);
        $this->assertNotEmpty($title);
    }

    public function test_users_export_title_includes_sheet_index(): void
    {
        $export = new UsersExport(['id'], [], 7);

        $this->assertSame('Sheet 7', $export->title());
    }

    public function test_users_export_headings_maps_known_columns(): void
    {
        $export = new UsersExport(['name', 'email'], [], 0);

        $headings = $export->headings();

        $this->assertSame('Name', $headings[0]);
        $this->assertSame('Email', $headings[1]);
    }

    public function test_users_export_headings_passes_through_unknown_columns(): void
    {
        $export = new UsersExport(['some_field'], [], 0);

        $headings = $export->headings();

        $this->assertSame('some_field', $headings[0]);
    }

    public function test_users_export_collection_wraps_provided_data(): void
    {
        $data = [['name' => 'Alice'], ['name' => 'Bob']];
        $export = new UsersExport(['name'], $data, 0);

        $result = $export->collection();

        $this->assertCount(2, $result);
    }
}
