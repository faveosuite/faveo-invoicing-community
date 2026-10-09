<?php

namespace Tests\Unit\Backend\Http\Controllers\User;

use App\Model\Order\Invoice;
use App\Model\Order\InvoiceItem;
use App\Model\Order\Order;
use App\Model\Order\OrderInvoiceRelation;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use PHPUnit\Framework\Attributes\Group;
use Tests\DBTestCase;

class InvoiceAndPaymentCalculationTest extends DBTestCase
{
    use DatabaseTransactions;

    #[Group('InvoiceAndPayment')]
    public function test_change_invoice_total_when_invoice_is_updated_returns_405(): void
    {
        $this->withoutMiddleware();
        $this->getLoggedInUser('admin');
        $invoice = Invoice::factory()->create(['user_id' => $this->user->id, 'grand_total' => '10000']);

        $response = $this->postJson("invoice/edit/{$invoice->id}", [
            'total' => '12000',
            'date' => now()->format('Y-m-d'),
            'status' => 'Active',
        ]);

        $response->assertStatus(405);
    }

    #[Group('InvoiceAndPayment')]
    public function test_change_get_clients_invoice_details_when_invoice_is_viewed(): void
    {
        $this->getLoggedInUser();
        $user = $this->user;
        $invoice = Invoice::factory()->create(['user_id' => $user->id]);
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
        $order = Order::factory()->create(['invoice_item_id' => $invoiceItem->id, 'client' => $user->id]);
        OrderInvoiceRelation::create(['order_id' => $order->id, 'invoice_id' => $invoice->id]);

        $response = $this->call('GET', 'clients/'.$user->id);
        $this->assertStringContainsSubstring($response->content(), '<!DOCTYPE html>');
    }
}
