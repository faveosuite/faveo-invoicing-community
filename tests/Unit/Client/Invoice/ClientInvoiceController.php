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
use Illuminate\Http\Request;
use Mockery;
use Spatie\Html\Html;
use Tests\DBTestCase;

class ClientInvoiceController extends DBTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->request = app(Request::class);
        $this->html = Mockery::mock(Html::class, [$this->request])->makePartial();
        $this->html->shouldReceive('token')->andReturn('mocked-token');
        $this->app->instance(Html::class, $this->html);
    }

    #[\PHPUnit\Framework\Attributes\Group('invoice')]
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
            'success',
            'data' => [
                'current_page',
                'data' => [
                    '*' => [
                        'number',
                        'OrderNo',
                        'date',
                        'total',
                        'paid',
                        'balance',
                        'status',
                        'action',
                    ]
                ],
                'first_page_url',
                'from',
                'next_page_url',
                'path',
                'per_page',
                'prev_page_url',
                'to'
            ],
        ]);
        $this->assertEquals($content['data']['data'][0]['status'], 'Unpaid');
    }

    #[\PHPUnit\Framework\Attributes\Group('invoice')]
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

    #[\PHPUnit\Framework\Attributes\Group('invoice')]
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

    #[\PHPUnit\Framework\Attributes\Group('invoice')]
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

    #[\PHPUnit\Framework\Attributes\Group('invoice')]
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

    #[\PHPUnit\Framework\Attributes\Group('invoice')]
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

        $content=$response->json();
        while (ob_get_level() > 1) {
            ob_end_clean();
        }
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => ['payments','items','user','processingFeeAmount','values','statusText','statusClass','status']
        ]);
        $this->assertEquals($user->id,$content['data']['user']['id']);
    }

    #[\PHPUnit\Framework\Attributes\Group('invoice')]
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
        $this->assertEquals($content['data']['data'][0]['status'], 'Unpaid');
        $this->assertEquals($content['data']['data'][0]['OrderNo'], '<a href='.url('my-order/'.$order->id).'>'.$order->number.'</a>');
        $this->assertEquals($content['data']['data'][0]['number'], '<a href='.url('my-invoice/'.$invoice->id).'>'.$invoice->number.'</a>');
    }

    #[\PHPUnit\Framework\Attributes\Group('invoice')]
    public function test_invoice_generate_pdf()
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        $this->withoutMiddleware();
        $invoice = Invoice::factory()->create(['user_id' => $user->id, 'status' => 'pending', 'is_renewed' => 0]);
        $response = $this->call('get', 'pdf');
        $response->assertSessionHas('fails', 'Invoice ID is required.');
    }

    #[\PHPUnit\Framework\Attributes\Group('invoice')]
    public function test_when_wrong_id_given()
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        $this->withoutMiddleware();
        $invoice = Invoice::factory()->create(['user_id' => $user->id, 'status' => 'pending', 'is_renewed' => 0]);
        $response = $this->call('get', 'pdf', ['invoiceid' => 122]);
        $response->assertSessionHas('fails', 'Invalid Invoice');
    }

    #[\PHPUnit\Framework\Attributes\Group('invoice')]
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
