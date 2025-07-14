<?php

namespace Tests\Unit;

use App\Model\Order\Invoice;
use App\Model\Order\InvoiceItem;
use App\Model\Order\OrderInvoiceRelation;
use Tests\DBTestCase;

class invoiceDeletionTest extends DBTestCase
{
    /**
     * A basic unit test example.
     *
     * @return void
     */
    public function test_oldinvoice_deletion_if_not_renewal_return_success()
    {
        $this->withoutMiddleware();
        $this->getLoggedInUser();

        $user = $this->user;
        $invoice = Invoice::factory()->create(['user_id' => $user->id, 'is_renewed' => '0']);
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

        $response = $this->delete('/invoices/delete/'.$invoice->id);

        $response->assertStatus(200);

        $response->assertJson([
            'message' => 'Invoice deleted successfully',
        ]);
        $this->assertDatabaseMissing('invoices', ['id' => $invoice->id]);
    }

    public function test_oldinvoice_deletion_if__renewal_return_success()
    {
        $this->withoutMiddleware();
        $this->getLoggedInUser();

        $user = $this->user;
        $invoice = Invoice::factory()->create(['user_id' => $user->id, 'is_renewed' => '1']);
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
        $response = $this->delete('/invoices/delete/'.$invoice->id);
        $response->assertStatus(200);
        $response->assertJson([
            'message' => 'Invoice deleted successfully',
        ]);
        $this->assertDatabaseMissing('invoices', ['id' => $invoice->id]);
        $this->assertDatabaseMissing('order_invoice_relations', ['id' => $orderRelation->id]);
        $this->assertDatabaseMissing('invoice_items', ['invoice_id' => $invoiceItem->id]);
    }
}
