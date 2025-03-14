<?php

namespace Tests\Unit\Admin\Dashboard;

use App\Http\Controllers\DashboardController;
use App\Model\Order\Invoice;
use App\Model\Order\Order;
use App\Model\Order\Payment;
use App\Model\Product\Product;
use App\Model\Product\Subscription;
use App\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\DBTestCase;

class DashboardControllerTest extends DBTestCase
{
    use DatabaseTransactions;

    private $classObject;

    protected function setUp(): void
    {
        parent::setUp();

        $this->classObject = new DashboardController();
    }

    #[Group('Dashboard')]
    public function test_getTotalSalesInInr_gettingTotalSalesInr()
    {
        $this->withoutMiddleware();
        $this->getLoggedInUser();
        $user = $this->user;
        $invoice = Invoice::factory()->create(['user_id' => $user->id]);
        Payment::create(['invoice_id' => $invoice->id, 'user_id' => $user->id, 'amount' => '10000']);
        $controller = new \App\Http\Controllers\DashboardController();
        $allowedCurrencies2 = 'INR';
        $response = $controller->getTotalSales($allowedCurrencies2);
        $this->assertEquals($response, '10000');
    }

      #[Group('Dashboard')]
    public function test_getYearlySalesInInr_gettingYearlySalesInr()
    {
        $this->withoutMiddleware();
        $this->getLoggedInUser();
        $user = $this->user;
        $date = date('Y-m-d H:m:i');
        $invoice = Invoice::factory()->create(['user_id' => $user->id, 'date' => $date]);
        $payment = Payment::create(['invoice_id' => $invoice->id, 'user_id' => $user->id, 'amount' => '10000']);
        $controller = new \App\Http\Controllers\DashboardController();
        $allowedCurrencies2 = 'INR';
        $response = $controller->getYearlySales($allowedCurrencies2);
        $this->assertEquals($response, '10000');

        // dd($response);
    }

      #[Group('Dashboard')]
    public function test_getYearlySalesInInr_whenInvoiceTotalIsFromPreviousYear()
    {
        $this->withoutMiddleware();
        $this->getLoggedInUser();
        $user = $this->user;
        $date = date('Y-m-d H:m:i');
        $invoice = Invoice::factory()->count(3)->create(['created_at' => 2017, 'user_id' => $user->id, 'date' => $date]);
        $controller = new \App\Http\Controllers\DashboardController();
        $allowedCurrencies2 = 'INR';
        $response = $controller->getYearlySales($allowedCurrencies2);
        $this->assertEquals($response, '0');
    }

      #[Group('Dashboard')]
    public function test_getAllUsers_getListOfRecentUsers()
    {
        $user = User::factory()->count(3)->create();
        $controller = new \App\Http\Controllers\DashboardController();
        $response = $controller->getAllUsers();
        $this->assertCount(1, [$user]);
    }

      #[Group('Dashboard')]
    public function test_getRecentOrders_getsRecentlySoldProductInLast30DaysWithCorrespondingCount()
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
    public function test_getSoldProducts_whenNumberOfDaysIsPassed_shouldGetOrdersForPassedNumberOfDays()
    {
        $this->getLoggedInUser('admin');
        $productOne = Product::create(['name' => 'one']);
        $productTwo = Product::create(['name' => 'two']);
        $productOne->order()->create(['client' => $this->user->id, 'number' => 1, 'order_status' => 'executed']);
        $productOne->order()->create(['client' => $this->user->id, 'number' => 2, 'order_status' => 'executed']);

        $order = $productOne->order()->create(['client' => $this->user->id, 'number' => 3, 'order_status' => 'executed']);
        $order->created_at = Carbon::now()->subDays(2);
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
    public function test_getSoldProducts_whenNumberOfDaysIsNotPassed_shouldGiveAllRecords()
    {
        $this->getLoggedInUser('admin');
        $productOne = Product::create(['name' => 'one']);
        $productTwo = Product::create(['name' => 'two']);
        $productOne->order()->create(['client' => $this->user->id, 'number' => 1, 'order_status' => 'executed']);
        $productOne->order()->create(['client' => $this->user->id, 'number' => 2, 'order_status' => 'executed']);

        $order = $productOne->order()->create(['client' => $this->user->id, 'number' => 3, 'order_status' => 'executed']);
        $order->created_at = Carbon::now()->subDays(2);
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
    public function test_getExpiringSubscriptions_whenLast30DaysIsFalse_shouldGiveSubscriptionsWhichAreExpiringIn30Days()
    {
        $this->getLoggedInUser('admin');
        $product = Product::create(['name' => 'one']);
        $orderOne = $product->order()->create(['client' => $this->user->id, 'number' => 1, 'price_override' => 10]);
        $orderTwo = $product->order()->create(['client' => $this->user->id, 'number' => 2, 'price_override' => 10]);
        Subscription::create(['update_ends_at' => Carbon::now()->addDays(2), 'order_id' => $orderOne->id, 'product_id' => $product->id, 'user_id' => $this->user->id]);
        Subscription::create(['update_ends_at' => Carbon::now()->addDays(3), 'order_id' => $orderOne->id, 'product_id' => $product->id, 'user_id' => $this->user->id]);
        Subscription::create(['update_ends_at' => Carbon::now()->addDays(4), 'order_id' => $orderTwo->id, 'product_id' => $product->id, 'user_id' => $this->user->id]);
        Subscription::create(['update_ends_at' => Carbon::now()->addDays(5), 'order_id' => $orderTwo->id, 'product_id' => $product->id, 'user_id' => $this->user->id]);
        Subscription::create(['update_ends_at' => Carbon::now()->subDays(5), 'order_id' => $orderTwo->id, 'product_id' => $product->id, 'user_id' => $this->user->id]);
        Subscription::create(['update_ends_at' => Carbon::now()->addMonth(), 'order_id' => $orderTwo->id, 'product_id' => $product->id, 'user_id' => $this->user->id]);
        $mouthDiff = Carbon::now()->addMonth()->diffInDays(Carbon::now()).' days';
        $response = $this->classObject->getExpiringSubscriptions(false);
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
    public function test_getExpiringSubscriptions_whenLast30DaysIsTrue_shouldGiveSubscriptionsWhichHasExpiredInLast30Days()
    {
        $this->getLoggedInUser('admin');
        $product = Product::create(['name' => 'one']);
        $orderOne = $product->order()->create(['client' => $this->user->id, 'number' => 1, 'price_override' => 10]);
        $orderTwo = $product->order()->create(['client' => $this->user->id, 'number' => 2, 'price_override' => 10]);
        Subscription::create(['update_ends_at' => Carbon::now()->subDays(2), 'order_id' => $orderOne->id, 'product_id' => $product->id, 'user_id' => $this->user->id]);
        Subscription::create(['update_ends_at' => Carbon::now()->subDays(3), 'order_id' => $orderOne->id, 'product_id' => $product->id, 'user_id' => $this->user->id]);
        Subscription::create(['update_ends_at' => Carbon::now()->subDays(4), 'order_id' => $orderTwo->id, 'product_id' => $product->id, 'user_id' => $this->user->id]);
        Subscription::create(['update_ends_at' => Carbon::now()->subDays(5), 'order_id' => $orderTwo->id, 'product_id' => $product->id, 'user_id' => $this->user->id]);
        Subscription::create(['update_ends_at' => Carbon::now()->addDays(5), 'order_id' => $orderTwo->id, 'product_id' => $product->id, 'user_id' => $this->user->id]);
        Subscription::create(['update_ends_at' => Carbon::now()->subMonth(), 'order_id' => $orderTwo->id, 'product_id' => $product->id, 'user_id' => $this->user->id]);
        $mouthDiff = Carbon::now()->subMonth()->diffInDays(Carbon::now()).' days';
        $response = $this->classObject->getExpiringSubscriptions(true);

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
    public function test_getRecentOrders_shouldGiveOrdersInLast30DaysOrderedByDescCreatedAt()
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
    public function test_getClientsUsingOldVersions_whenNoSubscriptionIsPresentInTheDB_shouldGiveEmptyArray()
    {
        $this->getLoggedInUser('admin');

        $methodResponse = $this->getPrivateMethod($this->classObject, 'getClientsUsingOldVersions');

        $this->assertCount(0, $methodResponse);
    }

      #[Group('Dashboard')]
    public function test_getClientsUsingOldVersions_shouldShowClientsWhichAreUsingOlderVersionInOrderOfTheirVersion()
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
    public function test_getClientsUsingOldVersions_whenUnpaidOrderArePresent_shouldExcludeThoseOrders()
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
    public function test_getClientsUsingOldVersions_whenSubscriptionUpdateOlderThan30DaysArePresent_shouldExcludeThoseOrders()
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
    private function createOrder($version = 'v3.0.0', $price = 1000, $subscriptionUpdatedAt = null)
    {
        $product = Product::create(['name' => "Helpdesk $version"]);
        $order = Order::create(['client' => $this->user->id, 'order_status' => 'executed',
            'product' => $product->id, 'number' => mt_rand(100000, 999999), 'price_override' => $price, ]);
        $subscriptionId = Subscription::create(['order_id' => $order->id, 'product_id' => $product->id, 'version' => $version])->id;

        if ($subscriptionUpdatedAt) {
            \DB::table('subscriptions')->where('id', $subscriptionId)->update(['updated_at' => $subscriptionUpdatedAt]);
        }

        return $order;
    }
}
