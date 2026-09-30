<?php

namespace Tests\Unit\Backend\Http\Controllers\Front\Order;

use App\Model\Order\Invoice;
use App\Model\Order\InvoiceItem;
use App\Model\Order\Order;
use App\Model\Order\OrderInvoiceRelation;
use App\User;
use Illuminate\Http\Request;
use Mockery;
use PHPUnit\Framework\Attributes\Group;
use Spatie\Html\Html;
use Tests\DBTestCase;

class ClientInvoiceControllerTest extends DBTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->request = resolve(Request::class);
        $this->html = Mockery::mock(Html::class, [$this->request])->makePartial();
        $this->html->shouldReceive('token')->andReturn('mocked-token');
        $this->app->instance(Html::class, $this->html);
    }

    #[Group('invoice')]
    public function test_return_invoice_details_correctly(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        $this->withoutMiddleware();
        $invoice = Invoice::factory()->create(['user_id' => $user->id, 'status' => 'pending']);
        $order = Order::factory()->create(['client' => $user->id]);
        OrderInvoiceRelation::create(['order_id' => $order->id, 'invoice_id' => $invoice->id]);
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
                        'date',
                        'grand_total',
                        'paid',
                        'balance',
                        'status',
                    ],
                ],
                'first_page_url',
                'from',
                'per_page',
                'to',
            ],
        ]);
        $this->assertEquals('Unpaid', $content['data']['data'][0]['status']);
    }

    #[Group('invoice')]
    public function test_deleting_invoice(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        $this->withoutMiddleware();
        $invoice = Invoice::factory()->create(['user_id' => $user->id]);
        InvoiceItem::create(['invoice_id' => $invoice->id, 'product_name' => 'Helpdesk Advance']);

        $response = $this->deleteJson('/invoices', ['invoice_ids' => [$invoice->id]]);
        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
        $this->assertDatabaseMissing('invoices', ['id' => $invoice->id]);
    }

    #[Group('invoice')]
    public function test_delete_fails_when_invoice_item_does_not_exist(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        $this->withoutMiddleware();

        // Deleting with no IDs returns 400
        $response = $this->deleteJson('/invoices', ['invoice_ids' => []]);
        $response->assertStatus(400);
        $response->assertJson(['success' => false]);
    }

    #[Group('invoice')]
    public function test_when_user_invoice_id_are_correct(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        $this->withoutMiddleware();
        $invoice = Invoice::factory()->create(['user_id' => $user->id]);
        $invoiceItem = InvoiceItem::create([
            'invoice_id' => $invoice->id,
            'product_name' => 'Helpdesk Advance',
            'regular_price' => 10000,
            'quantity' => 1,
            'subtotal' => 11800,
            'domain' => 'faveo.com',
            'plan_id' => 1,
        ]);
        $order = Order::factory()->create(['invoice_item_id' => $invoiceItem->id, 'client' => $user->id]);
        OrderInvoiceRelation::create(['order_id' => $order->id, 'invoice_id' => $invoice->id]);

        // Use admin invoice route GET invoice/{id}
        $response = $this->getJson("invoice/{$invoice->id}");
        $response->assertStatus(200);
        $response->assertJsonStructure(['data' => ['invoice', 'from', 'to', 'items', 'payments']]);
        $this->assertEquals($user->id, $response->json('data.to.id'));
    }

    #[Group('invoice')]
    public function test_individual_data_in_datatable(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        $this->withoutMiddleware();
        $invoice = Invoice::factory()->create(['user_id' => $user->id, 'status' => 'pending', 'is_renewed' => 0]);
        $order = Order::factory()->create(['client' => $user->id]);
        OrderInvoiceRelation::create(['order_id' => $order->id, 'invoice_id' => $invoice->id]);
        $response = $this->call('get', 'get-my-invoices', ['status' => '']);
        $content = $response->json();
        $response->assertStatus(200);
        $this->assertEquals('Unpaid', $content['data']['data'][0]['status']);
    }

    #[Group('invoice')]
    public function test_invoice_generate_pdf(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        $this->withoutMiddleware();
        Invoice::factory()->create(['user_id' => $user->id, 'status' => 'pending', 'is_renewed' => 0]);
        $response = $this->call('get', 'pdf');
        $content = $response->json();
        $this->assertEquals($content['message'], 'No invoice id');
    }
}
