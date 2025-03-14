<?php

namespace Tests\Unit\Admin\User;

use App\Model\Order\Invoice;
use App\Model\Order\InvoiceItem;
use App\Model\Order\Order;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\DBTestCase;

class InvoiceAndPaymentCalculationTest extends DBTestCase
{
    use DatabaseTransactions;

    #[Group('InvoiceAndPayment')]
    public function test_change_invoiceTotal_whenInvoiceIsUpdated()
    {
        $this->withoutMiddleware();
        $this->getLoggedInUser();
        $user = $this->user;
        $user_id = $user->id;
        $invoice = Invoice::factory()->create(['user_id' => $user_id, 'grand_total' => '10000']);
        $response = $this->call('POST', 'change-invoiceTotal', [
            'total' => '12000',
            'number' => $invoice->number,
            'user_id' => $user_id,
        ]);

        $response->assertStatus(200);
    }

    #[Group('InvoiceAndPayment')]
    public function test_change_get_clients_invoiceDetailsWhenInvoiceIsViewed()
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
        $order = Order::factory()->create(['invoice_id' => $invoice->id,
            'invoice_item_id' => $invoiceItem->id, 'client' => $user_id, ]);

        $response = $this->call('GET', 'clients/'.$user_id, [
            'total' => '12000',
            'number' => $invoice->number,
            'user_id' => $user_id,
        ]);
        $this->assertStringContainsSubstring($response->content(), '<!DOCTYPE html>');
        $response->setExpectedException(\Exception::class);
    }
}
