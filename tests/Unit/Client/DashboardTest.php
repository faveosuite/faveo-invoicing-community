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

    #[\PHPUnit\Framework\Attributes\Group('dashboard')]
    public function test_dashboard_returning_correct_view()
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        $this->withoutMiddleware();
        $order = Order::factory()->create(['client' => $user->id]);
        $response = $this->call('get', 'client-dashboard-details');
        $content = $response->json()['data'];
        $response->assertStatus(200);
        $this->assertEquals(1, $content['ordersCount']);
        $this->assertEquals(0, $content['pendingInvoicesCount']);
        $this->assertDatabaseCount('orders', 1);
    }

    #[\PHPUnit\Framework\Attributes\Group('dashboard')]
    public function test_when_no_orders_are_created()
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        $this->withoutMiddleware();
        $invoice = Invoice::factory()->create(['user_id' => $user->id]);
        $response = $this->call('get', 'client-dashboard-details');
        $response->assertStatus(200);
        $this->assertDatabaseCount('orders', 0);
    }

    #[\PHPUnit\Framework\Attributes\Group('dashboard')]
    public function test_when_no_invoices_are_created()
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        $this->withoutMiddleware();
        $order = Order::factory()->create(['client' => $user->id]);
        $response = $this->call('get', 'client-dashboard-details');
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                'pendingInvoicesCount',
                'ordersCount',
                'renewalCount',
            ],
        ]);
    }

    #[\PHPUnit\Framework\Attributes\Group('dashboard')]
    public function test_when_user_is_not_authenticated()
    {
        $user = User::factory()->create();
        $this->withoutMiddleware();
        $order = Order::factory()->create(['client' => $user->id]);
        $response = $this->call('get', 'client-dashboard-details');
        $response->assertStatus(500);
    }

    #[\PHPUnit\Framework\Attributes\Group('dashboard')]
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
        $response = $this->call('get', 'client-dashboard-details');
        $content = $response->json();
        $this->assertEquals($content['data']['renewalCount'], 1);

        $this->assertDatabaseCount('subscriptions', 1);
    }

    #[\PHPUnit\Framework\Attributes\Group('dashboard')]
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
        $response = $this->call('get', 'client-dashboard-details');
        $content = $response->json();
        $this->assertEquals($content['data']['renewalCount'], 0);
        $this->assertDatabaseCount('subscriptions', 1);
    }

    #[\PHPUnit\Framework\Attributes\Group('dashboard')]
    public function test_return_to_invoice_correctly()
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        $this->withoutMiddleware();
        $invoice = Invoice::factory()->create(['user_id' => $user->id, 'status' => 'pending']);
        $response = $this->call('get', 'my-invoices?status=pending');
        $content = $response->json();
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                'amount',
                'formattedValue',
                'payment_id',
            ],
        ]);
    }

    #[\PHPUnit\Framework\Attributes\Group('dashboard')]
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
                    ],
                ],
                'first_page_url',
                'from',
                'next_page_url',
                'path',
                'per_page',
                'prev_page_url',
                'to',
            ],
        ]);
    }

    public function test_get_client_dashboard_details()
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        $this->withoutMiddleware();
        Invoice::factory(['user_id' => $user->id, 'status' => 'pending'])->create();
        Order::factory(10)->create();
        $pendingInvoicesCount = $user->invoice()->where('status', 'pending')->count();
        $ordersCount = $user->order()->count();
        $renewalCount = $user->order()
            ->whereHas('subscription', function ($query) {
                $query->where('update_ends_at', '<', now());
            })
            ->count();

        $response = $this->call('get', 'client-dashboard-details');
        $data = $response['data'];
        $this->assertEquals($data['status'], 'pending');
        $this->assertEquals($data['updated_ends_at'], 'expired');
        $this->assertDatabaseHas('users', ['id' => $user->id]);
        $this->assertEquals($data['pendingInvoicesCount'], $pendingInvoicesCount);
    }
}
