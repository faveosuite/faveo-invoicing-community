<?php

namespace Tests\Unit\Backend\Http\Controllers\Front;

use App\Model\Order\Invoice;
use App\Model\Order\Order;
use App\Model\Order\OrderInvoiceRelation;
use App\Model\Payment\Plan;
use App\Model\Product\Product;
use App\Model\Product\Subscription;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Date;
use Mockery;
use PHPUnit\Framework\Attributes\Group;
use Spatie\Html\Html;
use Tests\DBTestCase;

class DashboardTest extends DBTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->request = resolve(Request::class);
        $this->html = Mockery::mock(Html::class, [$this->request])->makePartial();
        $this->html->shouldReceive('token')->andReturn('mocked-token');
        $this->app->instance(Html::class, $this->html);
    }

    #[Group('dashboard')]
    public function test_dashboard_returning_correct_view(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        $this->withoutMiddleware();
        Order::factory()->create(['client' => $user->id]);
        $response = $this->call('get', 'client-dashboard-details');
        $content = $response->json()['data'];
        $response->assertStatus(200);
        $this->assertEquals(1, $content['total_orders_count']);
        $this->assertEquals(0, $content['pending_invoices_count']);
        // Count orders for this specific user (not globally — real DB has other orders)
        $this->assertEquals(1, Order::where('client', $user->id)->count());
    }

    #[Group('dashboard')]
    public function test_when_no_orders_are_created(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        $this->withoutMiddleware();
        Invoice::factory()->create(['user_id' => $user->id]);
        $response = $this->call('get', 'client-dashboard-details');
        $response->assertStatus(200);
        // No orders for this specific user (not globally)
        $this->assertEquals(0, Order::where('client', $user->id)->count());
    }

    #[Group('dashboard')]
    public function test_when_no_invoices_are_created(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        $this->withoutMiddleware();
        Order::factory()->create(['client' => $user->id]);
        $response = $this->call('get', 'client-dashboard-details');
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                'pending_invoices_count',
                'total_orders_count',
                'order_renewals_count',
            ],
        ]);
    }

    #[Group('dashboard')]
    public function test_when_user_is_not_authenticated(): void
    {
        $user = User::factory()->create();
        $this->withoutMiddleware();
        Order::factory()->create(['client' => $user->id]);
        $response = $this->call('get', 'client-dashboard-details');
        $response->assertStatus(401);
    }

    #[Group('dashboard')]
    public function test_when_there_are_order_renewals(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        $this->withoutMiddleware();
        $date = '2025-03-02 18:15:02';
        $product = Product::create(['name' => 'Helpdesk Advance', 'description' => 'goodProduct']);
        $order = Order::factory()->create(['client' => $user->id]);
        Invoice::factory()->create(['user_id' => $user->id, 'status' => 'pending']);
        $plan = Plan::create(['id' => 'mt_rand(1,99)', 'name' => 'Hepldesk 1 year', 'product' => $product->id, 'days' => 365]);
        Subscription::create(['plan_id' => $plan->id, 'order_id' => $order->id, 'product_id' => $product->id,
            'version' => 'v6.0.0', 'update_ends_at' => $date]);
        $response = $this->call('get', 'client-dashboard-details');
        $content = $response->json();
        $this->assertEquals(1, $content['data']['order_renewals_count']);

        $this->assertDatabaseCount('subscriptions', 1);
    }

    #[Group('dashboard')]
    public function test_when_there_are_no_order_renewals(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        $this->withoutMiddleware();
        $product = Product::create(['name' => 'Helpdesk Advance', 'description' => 'goodProduct']);
        $order = Order::factory()->create(['client' => $user->id]);
        Invoice::factory()->create(['user_id' => $user->id, 'status' => 'pending']);
        $plan = Plan::create(['id' => 'mt_rand(1,99)', 'name' => 'Hepldesk 1 year', 'product' => $product->id, 'days' => 365]);
        Subscription::create(['plan_id' => $plan->id, 'order_id' => $order->id, 'product_id' => $product->id,
            'version' => 'v6.0.0', 'update_ends_at' => Date::now()]);
        $response = $this->call('get', 'client-dashboard-details');
        $content = $response->json();
        $this->assertEquals(0, $content['data']['order_renewals_count']);
        $this->assertDatabaseCount('subscriptions', 1);
    }

    #[Group('dashboard')]
    public function test_return_to_invoice_correctly(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        $this->withoutMiddleware();
        Invoice::factory()->create(['user_id' => $user->id, 'status' => 'pending']);
        $response = $this->call('get', 'get-my-invoices', ['status' => 'pending']);
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                'current_page',
                'data',
                'first_page_url',
                'from',
                'per_page',
                'to',
            ],
        ]);
    }

    #[Group('dashboard')]
    public function test_return_invoice_details_correctly(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        $this->withoutMiddleware();
        $invoice = Invoice::factory()->create(['user_id' => $user->id, 'status' => 'pending']);
        $order = Order::factory()->create(['client' => $user->id]);
        OrderInvoiceRelation::create(['order_id' => $order->id, 'invoice_id' => $invoice->id]);
        $response = $this->call('get', 'get-my-invoices', ['status' => 'pending']);
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
    }

    public function test_get_client_dashboard_details(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        $this->withoutMiddleware();
        Invoice::factory(['user_id' => $user->id, 'status' => 'pending'])->create();
        Order::factory(10)->create();
        $pendingInvoicesCount = $user->invoice()->where('status', 'pending')->count();

        $response = $this->call('get', 'client-dashboard-details');
        $data = $response->json()['data'];
        $response->assertStatus(200);
        $this->assertEquals($pendingInvoicesCount, $data['pending_invoices_count']);
    }
}
