<?php

namespace Tests\Unit\Backend\Http\Controllers\Order;

use App\Model\Order\Invoice;
use App\Model\Order\InvoiceItem;
use App\Model\Order\OrderInvoiceRelation;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\DBTestCase;

class invoiceDeletionTest extends DBTestCase
{
    use DatabaseTransactions;

    public function test_oldinvoice_deletion_if_not_renewal_return_success(): void
    {
        $this->withoutMiddleware();
        $this->getLoggedInUser('admin');

        $invoice = Invoice::factory()->create(['user_id' => $this->user->id, 'is_renewed' => '0']);
        InvoiceItem::create([
            'invoice_id' => $invoice->id,
            'product_name' => 'Helpdesk Advance',
            'regular_price' => 10000,
            'quantity' => 1,
            'tax_name' => 'CGST+SGST',
            'tax_percentage' => 18,
            'subtotal' => 11800,
            'domain' => 'faveo.com',
            'plan_id' => 1,
        ]);

        $response = $this->deleteJson('/invoices', [
            'invoice_ids' => [$invoice->id],
        ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
        $this->assertDatabaseMissing('invoices', ['id' => $invoice->id]);
    }

    public function test_oldinvoice_deletion_if__renewal_return_success(): void
    {
        $this->withoutMiddleware();
        $this->getLoggedInUser('admin');

        $invoice = Invoice::factory()->create(['user_id' => $this->user->id, 'is_renewed' => '1']);
        $invoiceItem = InvoiceItem::create([
            'invoice_id' => $invoice->id,
            'product_name' => 'Helpdesk Advance',
            'regular_price' => 10000,
            'quantity' => 1,
            'tax_name' => 'CGST+SGST',
            'tax_percentage' => 18,
            'subtotal' => 11800,
            'domain' => 'faveo.com',
            'plan_id' => 1,
        ]);
        $orderRelation = OrderInvoiceRelation::create(['invoice_id' => $invoice->id]);

        $response = $this->deleteJson('/invoices', [
            'invoice_ids' => [$invoice->id],
        ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
        $this->assertDatabaseMissing('invoices', ['id' => $invoice->id]);
    }
}
