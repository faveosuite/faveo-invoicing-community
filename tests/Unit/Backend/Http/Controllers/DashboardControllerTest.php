<?php

namespace Tests\Unit\Backend\Http\Controllers;

use App\Http\Controllers\DashboardController;
use App\Model\Common\Setting;
use App\Model\Order\Invoice;
use App\Model\Order\Order;
use App\Model\Order\Payment;
use App\Model\Payment\Currency;
use App\Model\Product\Product;
use App\Model\Product\Subscription;
use App\User;
use Carbon\Carbon;
use DB;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Date;
use Tests\DBTestCase;

class DashboardControllerTest extends DBTestCase
{
    use DatabaseTransactions;

    private DashboardController $classObject;

    protected function setUp(): void
    {
        parent::setUp();
        $this->classObject = new DashboardController;
    }

    #[Group('Dashboard')]
    public function test_get_total_sales_in_inr_getting_total_sales_inr(): void
    {
        $this->withoutMiddleware();
        $this->getLoggedInUser();
        $user = $this->user;
        $invoice = Invoice::factory()->create(['user_id' => $user->id, 'currency' => 'INR', 'status' => 'success']);
        Payment::create(['invoice_id' => $invoice->id, 'user_id' => $user->id, 'amount' => '10000']);
        $controller = new DashboardController;
        $allowedCurrencies2 = 'INR';
        $response = $controller->getTotalSales($allowedCurrencies2);
        $this->assertEquals(10000, $response);
    }

    #[Group('Dashboard')]
    public function test_get_yearly_sales_in_inr_getting_yearly_sales_inr(): void
    {
        $this->withoutMiddleware();
        $this->getLoggedInUser();
        $user = $this->user;
        $date = date('Y-m-d H:m:i');
        $invoice = Invoice::factory()->create(['user_id' => $user->id, 'currency' => 'INR', 'status' => 'success', 'date' => $date]);
        Payment::create(['invoice_id' => $invoice->id, 'user_id' => $user->id, 'amount' => '10000']);
        $controller = new DashboardController;
        $allowedCurrencies2 = 'INR';
        $response = $controller->getYearlySales($allowedCurrencies2);
        $this->assertEquals(10000, $response);

        // dd($response);
    }

    #[Group('Dashboard')]
    public function test_get_yearly_sales_in_inr_when_invoice_total_is_from_previous_year(): void
    {
        $this->withoutMiddleware();
        $this->getLoggedInUser();
        $user = $this->user;
        $date = date('Y-m-d H:m:i');
        Invoice::factory()->count(3)->create(['created_at' => 2017, 'user_id' => $user->id, 'date' => $date]);
        $controller = new DashboardController;
        $allowedCurrencies2 = 'INR';
        $response = $controller->getYearlySales($allowedCurrencies2);
        $this->assertEquals($response, '0');
    }

    #[Group('Dashboard')]
    public function test_get_all_users_get_list_of_recent_users(): void
    {
        $user = User::factory()->count(3)->create();
        $controller = new DashboardController;
        $controller->getAllUsers();
        $this->assertCount(1, [$user]);
    }

    #[Group('Dashboard')]
    public function test_get_recent_orders_gets_recently_sold_product_in_last30_days_with_corresponding_count(): void
    {
        $this->getLoggedInUser('admin');
        $productOne = Product::create(['name' => 'one']);
        $productTwo = Product::create(['name' => 'two']);
        $orderOne = $productOne->order()->create(['client' => $this->user->id, 'number' => 1, 'price_override' => 10]);
        $orderTwo = $productOne->order()->create(['client' => $this->user->id, 'number' => 2, 'price_override' => 20]);

        // creating one without price override
        $productOne->order()->create(['client' => $this->user->id, 'number' => 3]);
        $orderFour = $productTwo->order()->create(['client' => $this->user->id, 'number' => 4, 'price_override' => 10]);
        $response = $this->classObject->getRecentOrders();

        $this->assertCount(3, $response);

        $this->assertEquals($orderFour->number, $response[0]->order_number);
        $this->assertEquals($productTwo->name, $response[0]->product_name);
        $this->assertEquals($this->user->first_name.' '.$this->user->last_name, $response[0]->client_name);

        $this->assertEquals($orderTwo->number, $response[1]->order_number);
        $this->assertEquals($productOne->name, $response[1]->product_name);
        $this->assertEquals($this->user->first_name.' '.$this->user->last_name, $response[1]->client_name);

        $this->assertEquals($orderOne->number, $response[2]->order_number);
        $this->assertEquals($productOne->name, $response[2]->product_name);
        $this->assertEquals($this->user->first_name.' '.$this->user->last_name, $response[2]->client_name);
    }

    #[Group('Dashboard')]
    public function test_get_sold_products_when_number_of_days_is_passed_should_get_orders_for_passed_number_of_days(): void
    {
        $this->getLoggedInUser('admin');
        $productOne = Product::create(['name' => 'one']);
        $productTwo = Product::create(['name' => 'two']);
        $productOne->order()->create(['client' => $this->user->id, 'number' => 1, 'order_status' => 'executed']);
        $productOne->order()->create(['client' => $this->user->id, 'number' => 2, 'order_status' => 'executed']);

        $order = $productOne->order()->create(['client' => $this->user->id, 'number' => 3, 'order_status' => 'executed']);
        $order->created_at = Date::now()->subDays(2);
        $order->save();

        $productTwo->order()->create(['client' => $this->user->id, 'number' => 4, 'order_status' => 'executed']);
        $response = $this->classObject->getSoldProducts(1);
        $this->assertEquals(2, $response[0]->order_count);
        $this->assertEquals($productOne->id, $response[0]->product_id);
        $this->assertEquals($productOne->name, $response[0]->product_name);
        $this->assertEquals(1, $response[1]->order_count);
        $this->assertEquals($productTwo->id, $response[1]->product_id);
        $this->assertEquals($productTwo->name, $response[1]->product_name);
    }

    #[Group('Dashboard')]
    public function test_get_sold_products_when_number_of_days_is_not_passed_should_give_all_records(): void
    {
        $this->getLoggedInUser('admin');
        $productOne = Product::create(['name' => 'one']);
        $productTwo = Product::create(['name' => 'two']);
        $productOne->order()->create(['client' => $this->user->id, 'number' => 1, 'order_status' => 'executed']);
        $productOne->order()->create(['client' => $this->user->id, 'number' => 2, 'order_status' => 'executed']);

        $order = $productOne->order()->create(['client' => $this->user->id, 'number' => 3, 'order_status' => 'executed']);
        $order->created_at = Date::now()->subDays(2);
        $order->save();

        $productTwo->order()->create(['client' => $this->user->id, 'number' => 4, 'order_status' => 'executed']);
        $response = $this->classObject->getSoldProducts();

        $this->assertEquals(3, $response[0]->order_count);
        $this->assertEquals($productOne->id, $response[0]->product_id);
        $this->assertEquals($productOne->name, $response[0]->product_name);
        $this->assertEquals(1, $response[1]->order_count);
        $this->assertEquals($productTwo->id, $response[1]->product_id);
        $this->assertEquals($productTwo->name, $response[1]->product_name);
    }

    #[Group('Dashboard')]
    public function test_get_expiring_subscriptions_when_last30_days_is_false_should_give_subscriptions_which_are_expiring_in30_days(): void
    {
        $this->getLoggedInUser('admin');
        $product = Product::create(['name' => 'one']);
        $orderOne = $product->order()->create(['client' => $this->user->id, 'number' => 1, 'price_override' => 10]);
        $orderTwo = $product->order()->create(['client' => $this->user->id, 'number' => 2, 'price_override' => 10]);
        Subscription::create(['update_ends_at' => Date::now()->addDays(2), 'order_id' => $orderOne->id, 'product_id' => $product->id, 'user_id' => $this->user->id]);
        Subscription::create(['update_ends_at' => Date::now()->addDays(3), 'order_id' => $orderOne->id, 'product_id' => $product->id, 'user_id' => $this->user->id]);
        Subscription::create(['update_ends_at' => Date::now()->addDays(4), 'order_id' => $orderTwo->id, 'product_id' => $product->id, 'user_id' => $this->user->id]);
        Subscription::create(['update_ends_at' => Date::now()->addDays(5), 'order_id' => $orderTwo->id, 'product_id' => $product->id, 'user_id' => $this->user->id]);
        Subscription::create(['update_ends_at' => Date::now()->subDays(5), 'order_id' => $orderTwo->id, 'product_id' => $product->id, 'user_id' => $this->user->id]);
        Subscription::create(['update_ends_at' => Date::now()->addMonth(), 'order_id' => $orderTwo->id, 'product_id' => $product->id, 'user_id' => $this->user->id]);
        $mouthDiff = ((int) Date::now()->addMonth()->diffInDays(Date::now(), absolute: true)).' days';
        $response = $this->classObject->getExpiringSubscriptions(past30Days: false);
        $this->assertCount(5, $response);
        $this->assertEquals($mouthDiff, $response[0]->days_difference);
        $this->assertEquals('4 days', $response[1]->days_difference);
        $this->assertEquals('3 days', $response[2]->days_difference);
        $this->assertEquals('2 days', $response[3]->days_difference);
        $this->assertEquals('1 days', $response[4]->days_difference);

        $this->assertEquals($this->user->first_name.' '.$this->user->last_name, $response[0]->client_name);
        $this->assertEquals($this->user->first_name.' '.$this->user->last_name, $response[1]->client_name);
        $this->assertEquals($this->user->first_name.' '.$this->user->last_name, $response[2]->client_name);
        $this->assertEquals($this->user->first_name.' '.$this->user->last_name, $response[3]->client_name);
    }

    #[Group('Dashboard')]
    public function test_get_expiring_subscriptions_when_last30_days_is_true_should_give_subscriptions_which_has_expired_in_last30_days(): void
    {
        $this->getLoggedInUser('admin');
        $product = Product::create(['name' => 'one']);
        $orderOne = $product->order()->create(['client' => $this->user->id, 'number' => 1, 'price_override' => 10]);
        $orderTwo = $product->order()->create(['client' => $this->user->id, 'number' => 2, 'price_override' => 10]);
        Subscription::create(['update_ends_at' => Date::now()->subDays(2), 'order_id' => $orderOne->id, 'product_id' => $product->id, 'user_id' => $this->user->id]);
        Subscription::create(['update_ends_at' => Date::now()->subDays(3), 'order_id' => $orderOne->id, 'product_id' => $product->id, 'user_id' => $this->user->id]);
        Subscription::create(['update_ends_at' => Date::now()->subDays(4), 'order_id' => $orderTwo->id, 'product_id' => $product->id, 'user_id' => $this->user->id]);
        Subscription::create(['update_ends_at' => Date::now()->subDays(5), 'order_id' => $orderTwo->id, 'product_id' => $product->id, 'user_id' => $this->user->id]);
        Subscription::create(['update_ends_at' => Date::now()->addDays(5), 'order_id' => $orderTwo->id, 'product_id' => $product->id, 'user_id' => $this->user->id]);
        Subscription::create(['update_ends_at' => Date::now()->subMonth(), 'order_id' => $orderTwo->id, 'product_id' => $product->id, 'user_id' => $this->user->id]);
        $mouthDiff = ((int) Date::now()->subMonth()->diffInDays(Date::now(), absolute: true)).' days';
        $response = $this->classObject->getExpiringSubscriptions(past30Days: true);

        $this->assertCount(5, $response);
        $this->assertEquals('2 days', $response[0]->days_difference);
        $this->assertEquals('3 days', $response[1]->days_difference);
        $this->assertEquals('4 days', $response[2]->days_difference);
        $this->assertEquals('5 days', $response[3]->days_difference);
        $this->assertEquals($mouthDiff, $response[4]->days_difference);

        $this->assertEquals($this->user->first_name.' '.$this->user->last_name, $response[0]->client_name);
        $this->assertEquals($this->user->first_name.' '.$this->user->last_name, $response[1]->client_name);
        $this->assertEquals($this->user->first_name.' '.$this->user->last_name, $response[2]->client_name);
        $this->assertEquals($this->user->first_name.' '.$this->user->last_name, $response[3]->client_name);
    }

    #[Group('Dashboard')]
    public function test_get_recent_orders_should_give_orders_in_last30_days_ordered_by_desc_created_at(): void
    {
        $this->getLoggedInUser('admin');
        $product = Product::create(['name' => 'one']);
        $orderOne = $product->order()->create(['client' => $this->user->id, 'number' => 1, 'price_override' => 10]);
        $orderTwo = $product->order()->create(['client' => $this->user->id, 'number' => 2, 'price_override' => 10]);

        $response = $this->classObject->getRecentOrders();

        $this->assertCount(2, $response);

        $this->assertEquals($orderTwo->number, $response[0]->order_number);
        $this->assertEquals($orderOne->number, $response[1]->order_number);

        $this->assertEquals($this->user->first_name.' '.$this->user->last_name, $response[0]->client_name);
        $this->assertEquals($this->user->first_name.' '.$this->user->last_name, $response[1]->client_name);
    }

    #[Group('Dashboard')]
    public function test_get_clients_using_old_versions_when_no_subscription_is_present_in_the_d_b_should_give_empty_array(): void
    {
        $this->getLoggedInUser('admin');

        $methodResponse = $this->getPrivateMethod($this->classObject, 'getClientsUsingOldVersions');

        $this->assertCount(0, $methodResponse);
    }

    #[Group('Dashboard')]
    public function test_get_clients_using_old_versions_should_show_clients_which_are_using_older_version_in_order_of_their_version(): void
    {
        $this->getLoggedInUser('admin');
        $this->createOrder('v3.2.0');
        $this->createOrder('v3.1.0');
        $this->createOrder('v3.0.0');
        $this->createOrder('v2.9.0');

        $methodResponse = $this->getPrivateMethod($this->classObject, 'getClientsUsingOldVersions');
        $this->assertCount(3, $methodResponse);
        // $this->assertEquals('v3.1.0', $methodResponse[0]->product_version);
        // $this->assertEquals('v3.0.0', $methodResponse[1]->product_version);
        // $this->assertEquals('v2.9.0', $methodResponse[2]->product_version);
        // $this->assertStringContainsString($this->user->first_name.' '.$this->user->last_name, $methodResponse[0]->client_name);
        // $this->assertStringContainsString($this->user->first_name.' '.$this->user->last_name, $methodResponse[1]->client_name);
        // $this->assertStringContainsString($this->user->first_name.' '.$this->user->last_name, $methodResponse[2]->client_name);

        // $this->assertEquals('Helpdesk v3.1.0', $methodResponse[0]->product_name);
        // $this->assertEquals('Helpdesk v3.0.0', $methodResponse[1]->product_name);
        // $this->assertEquals('Helpdesk v2.9.0', $methodResponse[2]->product_name);
    }

    #[Group('Dashboard')]
    public function test_get_clients_using_old_versions_when_unpaid_order_are_present_should_exclude_those_orders(): void
    {
        $this->getLoggedInUser('admin');
        $this->createOrder('v3.2.0');
        $this->createOrder('v3.1.0', 0);
        $this->createOrder('v3.0.0');
        $this->createOrder('v2.9.0');

        $methodResponse = $this->getPrivateMethod($this->classObject, 'getClientsUsingOldVersions');
        // dd($methodResponse);
        $this->assertCount(2, $methodResponse);
    }

    #[Group('Dashboard')]
    public function test_get_clients_using_old_versions_when_subscription_update_older_than30_days_are_present_should_exclude_those_orders(): void
    {
        $this->getLoggedInUser('admin');
        $this->createOrder('v3.2.0');
        $this->createOrder('v3.1.0', 1000, new Carbon('-31 days'));
        $this->createOrder('v3.0.0');
        $this->createOrder('v2.9.0');

        $methodResponse = $this->getPrivateMethod($this->classObject, 'getClientsUsingOldVersions');

        $this->assertCount(3, $methodResponse);
    }

    #[Group('Dashboard')]
    private function createOrder(string $version = 'v3.0.0', int $price = 1000, $subscriptionUpdatedAt = null)
    {
        $product = Product::create(['name' => 'Helpdesk '.$version]);
        $order = Order::create(['client' => $this->user->id, 'order_status' => 'executed',
            'product' => $product->id, 'number' => mt_rand(100000, 999999), 'price_override' => $price, ]);
        $subscriptionId = Subscription::create(['order_id' => $order->id, 'product_id' => $product->id, 'version' => $version])->id;

        if ($subscriptionUpdatedAt) {
            DB::table('subscriptions')->where('id', $subscriptionId)->update(['updated_at' => $subscriptionUpdatedAt]);
        }

        return $order;
    }

    #[Group('Dashboard')]
    public function test_get_conversion_rate_based_on_orders(): void
    {
        $product = Product::create(['name' => "Helpdesk v3.0.0'"]);
        $this->withoutMiddleware();
        $this->getLoggedInUser();
        $user = $this->user;
        Order::create(['client' => $user->id, 'order_status' => 'executed',
            'product' => $product->id, 'number' => mt_rand(100000, 999999), 'price_override' => 1000, ]);

        Order::create(['client' => $user->id, 'order_status' => 'executed',
            'product' => $product->id, 'number' => mt_rand(100000, 999999), 'price_override' => 0, ]);
        $response = $this->getPrivateMethod($this->classObject, 'getConversionRate');
        $this->assertEquals('50.0', $response['rate']);
        $this->assertEquals('2', $response['all_orders']);
        $this->assertEquals('1', $response['paid_orders']);
    }

    #[Group('Dashboard')]
    public function test_get_pending_payments(): void
    {
        $this->withoutMiddleware();
        $this->getLoggedInUser();
        $user = $this->user;
        Invoice::factory()->create(['user_id' => $user->id, 'status' => 'pending', 'currency' => 'INR', 'grand_total' => 10000]);
        $allowedCurrencies2 = 'INR';
        $response = $this->getPrivateMethod($this->classObject, 'getPendingPayments', [$allowedCurrencies2]);
        $this->assertEquals(10000, $response);
    }

    #[Group('Dashboard')]
    public function test_to_get_recent_invoices(): void
    {
        $this->withoutMiddleware();
        $this->getLoggedInUser();
        $user = $this->user;
        $invoice = Invoice::factory()->create(['id' => 22, 'user_id' => $user->id, 'status' => 'Pending', 'date' => Date::now()]);
        Payment::create(['invoice_id' => $invoice->id, 'user_id' => $user->id, 'amount' => '50000']);
        $invoice1 = Invoice::factory()->create(['id' => 23, 'user_id' => $user->id, 'status' => 'success', 'date' => Date::now()]);
        Payment::create(['invoice_id' => $invoice1->id, 'user_id' => $user->id, 'amount' => '20000']);
        $invoice2 = Invoice::factory()->create(['id' => 24, 'user_id' => $user->id, 'status' => 'Pending', 'date' => Date::now()]);
        Payment::create(['invoice_id' => $invoice2->id, 'user_id' => $user->id, 'amount' => '80000']);
        Currency::create(['code' => 'INR', 'symbol' => '₹', 'name' => 'Indian Rupees', 'dashboard_currency=0']);
        $response = $this->getPrivateMethod($this->classObject, 'getRecentInvoices');
        $content = $response->toArray();
        $this->assertEquals($invoice->id, $content[0]['invoice_id']);
        $this->assertEquals($invoice1->id, $content[1]['invoice_id']);
        $this->assertEquals($invoice2->id, $content[2]['invoice_id']);
    }

    #[Group('Dashboard')]
    public function test_to_get_monthly_sales(): void
    {
        $this->withoutMiddleware();
        $this->getLoggedInUser();
        $user = $this->user;
        $invoice = Invoice::factory()->create(['user_id' => $user->id, 'status' => 'success', 'date' => Date::now(), 'currency' => 'INR']);
        Payment::create(['invoice_id' => $invoice->id, 'user_id' => $user->id, 'amount' => '50000']);
        $response = $this->getPrivateMethod($this->classObject, 'getMonthlySales', ['INR']);
        $this->assertEquals(50000, $response);
    }

    #[Group('Dashboard')]
    public function test_to_check_overall_operation_dashboard(): void
    {
        $this->withoutMiddleware();
        $this->getLoggedInUser();
        $user = $this->user;
        Setting::create(['default_currency' => 'INR', 'default_symbol' => '₹']);
        $invoice = Invoice::factory()->create(['user_id' => $user->id, 'status' => 'pending', 'date' => Date::now()]);
        Payment::create(['invoice_id' => $invoice->id, 'user_id' => $user->id, 'amount' => '50000']);
        $invoice1 = Invoice::factory()->create(['user_id' => $user->id, 'status' => 'success', 'date' => Date::now()]);
        Payment::create(['invoice_id' => $invoice1->id, 'user_id' => $user->id, 'amount' => '20000']);
        $invoice2 = Invoice::factory()->create(['user_id' => $user->id, 'status' => 'pending', 'date' => Date::now()]);
        Payment::create(['invoice_id' => $invoice2->id, 'user_id' => $user->id, 'amount' => '80000']);
        Currency::create(['code' => 'INR', 'symbol' => '₹', 'name' => 'Indian Rupees', 'dashboard_currency=1']);
        $product = Product::create(['name' => "Helpdesk v3.0.0'"]);
        Order::create(['client' => $user->id, 'order_status' => 'executed',
            'product' => $product->id, 'number' => mt_rand(100000, 999999), 'price_override' => 1000, ]);

        Order::create(['client' => $user->id, 'order_status' => 'executed',
            'product' => $product->id, 'number' => mt_rand(100000, 999999), 'price_override' => 0, ]);

        // Admin dashboard blade was replaced by Vue SPA — just assert no 500 error
        $response = $this->call('get', 'admin-dashboard-data');
        $this->assertNotEquals(500, $response->getStatusCode());
    }

    // =========================================================================
    // GET /dashboard – covers dashboard() and all internal helper methods
    // =========================================================================

    public function test_dashboard_endpoint_returns_non_500(): void
    {
        $this->withoutMiddleware();
        $this->getLoggedInUser('admin');

        // The /dashboard route calls dashboard() which internally calls all helper methods
        $response = $this->getJson('/dashboard');
        $this->assertNotEquals(500, $response->getStatusCode());
    }
}
