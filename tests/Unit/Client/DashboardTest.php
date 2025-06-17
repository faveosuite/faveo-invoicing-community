<?php

namespace Tests\Unit\Client;

use App\Model\Order\Invoice;
use App\Model\Order\Order;
use App\Model\Order\OrderInvoiceRelation;
use App\Model\Payment\Plan;
use App\Model\Product\Product;
use App\Model\Product\Subscription;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Mockery;
use Spatie\Html\Html;
use Tests\DBTestCase;

class DashboardTest extends DBTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->request = app(Request::class);
        $this->html = Mockery::mock(Html::class, [$this->request])->makePartial();
        $this->html->shouldReceive('token')->andReturn('mocked-token');
        $this->app->instance(Html::class, $this->html);
    }

    /** @group dashboard */
    public function test_dashboard_returning_correct_view()
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        $this->withoutMiddleware();
        $order = Order::factory()->create(['client' => $user->id]);
        $invoice = Invoice::factory()->create(['user_id' => $user->id]);
        $response = $this->call('get', 'client-dashboard');
        $response->assertStatus(200);
        $this->assertDatabaseCount('invoices', 1);
        $this->assertDatabaseCount('orders', 1);
        $response->assertViewIs('themes.default1.front.clients.index');
    }

    /** @group dashboard */
    public function test_when_no_orders_are_created()
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        $this->withoutMiddleware();
        $invoice = Invoice::factory()->create(['user_id' => $user->id]);
        $response = $this->call('get', 'client-dashboard');
        $response->assertStatus(200);
        $this->assertDatabaseCount('orders', 0);
        $response->assertViewIs('themes.default1.front.clients.index');
    }

    /** @group dashboard */
    public function test_when_no_invoices_are_created()
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        $this->withoutMiddleware();
        $order = Order::factory()->create(['client' => $user->id]);
        $response = $this->call('get', 'client-dashboard');
        $response->assertStatus(200);
        $response->assertViewHasAll(['pendingInvoicesCount', 'ordersCount', 'renewalCount']);
        $this->assertDatabaseCount('invoices', 0);
        $response->assertViewIs('themes.default1.front.clients.index');
    }

    /** @group dashboard */
    public function test_when_user_is_not_authenticated()
    {
        $user = User::factory()->create();
        $this->withoutMiddleware();
        $order = Order::factory()->create(['client' => $user->id]);
        $response = $this->call('get', 'client-dashboard');
        $response->assertStatus(500);
    }

    /** @group dashboard */
    public function test_when_there_are_order_renewals()
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        $this->withoutMiddleware();
        $date = '2025-03-02 18:15:02';
        $product = Product::create(['name' => 'Helpdesk Advance', 'description' => 'goodProduct']);
        $order = Order::factory()->create(['client' => $user->id]);
        $invoice = Invoice::factory()->create(['user_id' => $user->id, 'status' => 'pending']);
        $plan = Plan::create(['id' => 'mt_rand(1,99)', 'name' => 'Hepldesk 1 year', 'product' => $product->id, 'days' => 365]);
        $subscription = Subscription::create(['plan_id' => $plan->id, 'order_id' => $order->id, 'product_id' => $product->id,
            'version' => 'v6.0.0', 'update_ends_at' => $date]);
        $response = $this->call('get', 'client-dashboard');
        $content = $response->getOriginalContent()->getData();
        $this->assertEquals($content['renewalCount'], 1);
        $this->assertDatabaseCount('subscriptions', 1);
    }

    /** @group dashboard */
    public function test_when_there_are_no_order_renewals()
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        $this->withoutMiddleware();
        $date = '2025-04-30 18:15:02';
        $product = Product::create(['name' => 'Helpdesk Advance', 'description' => 'goodProduct']);
        $order = Order::factory()->create(['client' => $user->id]);
        $invoice = Invoice::factory()->create(['user_id' => $user->id, 'status' => 'pending']);
        $plan = Plan::create(['id' => 'mt_rand(1,99)', 'name' => 'Hepldesk 1 year', 'product' => $product->id, 'days' => 365]);
        $subscription = Subscription::create(['plan_id' => $plan->id, 'order_id' => $order->id, 'product_id' => $product->id,
            'version' => 'v6.0.0', 'update_ends_at' => Carbon::now()]);
        $response = $this->call('get', 'client-dashboard');
        $content = $response->getOriginalContent()->getData();
        $this->assertEquals($content['renewalCount'], 0);
        $this->assertDatabaseCount('subscriptions', 1);
    }

    /** @group dashboard */
    public function test_return_to_invoice_correctly()
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        $this->withoutMiddleware();
        $invoice = Invoice::factory()->create(['user_id' => $user->id, 'status' => 'pending']);
        $response = $this->call('get', 'my-invoices?status=pending');
        $response->assertViewIs('themes.default1.front.clients.invoice');
    }

    /** @group dashboard */
    public function test_return_invoice_details_correctly()
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        $this->withoutMiddleware();
        $invoice = Invoice::factory()->create(['user_id' => $user->id, 'status' => 'pending']);
        $order = Order::factory()->create(['client' => $user->id, 'invoice_id' => $invoice->id]);
        $relation = OrderInvoiceRelation::create(['order_id' => $order->id, 'invoice_id' => $invoice->id]);
        $response = $this->call('get', 'get-my-invoices', ['status' => 'pending']);
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'draw',
            'recordsTotal',
            'recordsFiltered',
            'data' => ['*' => ['number', 'orderNo', 'date', 'total', 'status', 'Action']],
        ]);
    }
}
