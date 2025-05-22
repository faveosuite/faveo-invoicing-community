<?php

namespace Tests\Unit\Client\Invoice;

use App\Model\Order\Invoice;
use App\Model\Order\InvoiceItem;
use App\Model\Order\Order;
use App\Model\Order\OrderInvoiceRelation;
use App\Model\Payment\Plan;
use App\Model\Payment\PlanPrice;
use App\Model\Product\Product;
use App\User;
use Tests\DBTestCase;

class ClientInvoiceController extends DBTestCase
{
    /** @group invoice */
    public function test_return_invoice_details_correctly()
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        $this->withoutMiddleware();
        $invoice = Invoice::factory()->create(['user_id' => $user->id, 'status' => 'pending']);
        $order = Order::factory()->create(['client' => $user->id, 'invoice_id' => $invoice->id]);
        $relation = OrderInvoiceRelation::create(['order_id' => $order->id, 'invoice_id' => $invoice->id]);
        $response = $this->call('get', 'get-my-invoices', ['status' => '']);
        $content = $response->json();
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'draw',
            'recordsTotal',
            'recordsFiltered',
            'data' => ['*' => ['number', 'orderNo', 'date', 'total', 'status', 'Action']],
        ]);
        $this->assertEquals($content['data'][0]['user_id'], $user->id);
    }

    /** @group invoice */
    public function test_deleting_invoice()
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        $this->withoutMiddleware();
        $product = Product::create(['name' => 'Helpdesk Advance']);
        $invoice = Invoice::factory()->create(['user_id' => $user->id]);
        $invoiceItem = InvoiceItem::create(['invoice_id' => $invoice->id, 'product_name' => $product->name]);
        $order = Order::create(['client' => $user->id, 'order_status' => 'executed',
            'product' => 'Helpdesk Advance', 'number' => mt_rand(100000, 999999), 'invoice_id' => $invoice->id, ]);
        $plan = Plan::create(['id' => 'mt_rand(1,99)', 'name' => 'Hepldesk 1 year', 'product' => $product->id, 'days' => 365]);
        $planPrice = PlanPrice::factory()->create(['plan_id' => $plan->id]);
        $response = $this->call('delete', 'invoices/delete/'.$invoice->id);
        $response->assertStatus(200);
        $response->assertJson([
            'message' => 'Invoice deleted successfully',
        ]);
    }

    /** @group invoice */
    public function test_delete_fails_when_invoice_item_does_not_exist()
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        $this->withoutMiddleware();
        $product = Product::create(['name' => 'Helpdesk Advance']);
        $invoice = Invoice::factory()->create(['user_id' => $user->id]);
        $plan = Plan::create(['id' => 'mt_rand(1,99)', 'name' => 'Hepldesk 1 year', 'product' => $product->id, 'days' => 365]);
        $planPrice = PlanPrice::factory()->create(['plan_id' => $plan->id]);
        $response = $this->call('delete', 'invoices/delete/'.$invoice->id);
        $response->assertStatus(400);
        $response->assertJson([
            'error' => 'Cannot delete invoice.',
        ]);
    }

    /** @group invoice */
    public function test_returns_individual_invoice()
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        $this->withoutMiddleware();
        $product = Product::create(['name' => 'Helpdesk Advance']);
        $invoice = Invoice::factory()->create(['user_id' => $user->id]);
        $id = 221;
        $response = $this->call('get', 'my-invoice/'.$id);
        $response->assertSessionHas('fails', 'Invoice not found.');
    }

    /** @group invoice */
    public function test_when_user_id_is_not_same_as_authorized_user()
    {
        $user = User::factory()->create();
        $user1 = User::factory()->create();
        $this->actingAs($user);
        $this->withoutMiddleware();
        $product = Product::create(['name' => 'Helpdesk Advance']);
        $invoice = Invoice::factory()->create(['user_id' => $user1->id]);
        $response = $this->call('get', 'my-invoice/'.$invoice->id);
        $response->assertSessionHas('fails', 'Cannot view invoice. Invalid modification of data.');
    }

    /** @group invoice */
    public function test_when_user_invoice_id_are_correct()
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        $this->withoutMiddleware();
        $product = Product::factory()->create();
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
        $order = Order::factory()->create(['invoice_id' => $invoice->id,
            'invoice_item_id' => $invoiceItem->id, 'client' => $user->id, 'product' => $product->id]);
        $response = $this->call('get', 'my-invoice/'.$invoice->id);
        while (ob_get_level() > 1) {
            ob_end_clean();
        }
        $response->assertStatus(200);
        $response->assertViewIs('themes.default1.front.clients.show-invoice');
    }

    /** @group invoice */
    public function test_individual_data_in_datatable()
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        $this->withoutMiddleware();
        $invoice = Invoice::factory()->create(['user_id' => $user->id, 'status' => 'pending', 'is_renewed' => 0]);
        $order = Order::factory()->create(['client' => $user->id, 'invoice_id' => $invoice->id]);
        $relation = OrderInvoiceRelation::create(['order_id' => $order->id, 'invoice_id' => $invoice->id]);
        $response = $this->call('get', 'get-my-invoices', ['status' => '']);
        $content = $response->json();
        $response->assertStatus(200);
        $this->assertEquals($content['data'][0]['user_id'], $user->id);
        $this->assertEquals($content['data'][0]['id'], $invoice->id);
        $this->assertEquals($content['data'][0]['is_renewed'], $invoice->is_renewed);
        $this->assertEquals($content['data'][0]['currency'], $invoice->currency);
    }

    /** @group invoice */
    public function test_invoice_generate_pdf()
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        $this->withoutMiddleware();
        $invoice = Invoice::factory()->create(['user_id' => $user->id, 'status' => 'pending', 'is_renewed' => 0]);
        $response = $this->call('get', 'pdf');
        $response->assertSessionHas('fails', 'Invoice ID is required.');
    }

    /** @group invoice */
    public function test_when_wrong_id_given()
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        $this->withoutMiddleware();
        $invoice = Invoice::factory()->create(['user_id' => $user->id, 'status' => 'pending', 'is_renewed' => 0]);
        $response = $this->call('get', 'pdf', ['invoiceid' => 122]);
        $response->assertSessionHas('fails', 'Invalid Invoice');
    }

    /** @group invoice */
    public function test_generate_invoice_when_all_data_given()
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        $this->withoutMiddleware();
        $product = Product::factory()->create();
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
        $order = Order::factory()->create(['invoice_id' => $invoice->id,
            'invoice_item_id' => $invoiceItem->id, 'client' => $user->id, 'product' => $product->id]);
        $response = $this->call('get', 'pdf', ['invoiceid' => $invoice->id]);
        $response->assertStatus(200);
    }
}
