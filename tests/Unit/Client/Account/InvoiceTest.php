<?php

namespace Tests\Unit\Client\Account;

use App\Model\Order\Invoice;
use App\Model\Order\InvoiceItem;
use Tests\DBTestCase;

class InvoiceTest extends DBTestCase
{
    /**
     * A basic test example.
     *
     * @return void
     */
    #[Group('ClientController')]
    public function test_Invoices()
    {
        $this->expectException(\Exception::class);
        $this->withoutMiddleware();
        $this->getLoggedInUser();
        $user = $this->user;
        $user_id = $user->id;
        $invoice = Invoice::factory()->create(['user_id' => $user_id]);
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
        $response = $this->call('GET', 'my-invoice/'.$invoice->id, [
            'invoice' => $invoice,
            'items' => $invoiceItem,
            'user' => $user,
        ]);
        $response->setExpectedException(\Exception::class);
    }
}
