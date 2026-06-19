<?php

namespace Tests\Unit\Admin\Order;

use App\Http\Controllers\Order\OrderSearchController;
use App\Model\Order\InstallationDetail;
use App\Model\Order\Order;
use App\Model\Product\Product;
use App\Model\Product\Subscription;
use Tests\DBTestCase;

class OrderSearchControllerTest extends DBTestCase
{
    private OrderSearchController $classObject;

    protected function setUp(): void
    {
        parent::setUp();
        $this->classObject = new OrderSearchController;
    }

    #[Group('orderFilter')]
    public function test_get_base_query_for_orders_gives_required_columns_when_called(): void
    {
        $this->getLoggedInUser('admin');
        $product = Product::create(['name' => 'Helpdesk']);
        $order = Order::create(['client' => $this->user->id, 'order_status' => 'executed', 'product' => $product->id]);
        $subscription = Subscription::create(['order_id' => $order->id, 'product_id' => $product->id, 'version' => 'v3.0.0']);
        $query = $this->getPrivateMethod($this->classObject, 'getBaseQueryForOrders');
        $record = $query->first();
        $this->assertEquals($order->id, $record->id);
        $this->assertEquals($order->order_status, $record->order_status);
        $this->assertEquals($product->name, $record->product_name);
        $this->assertEquals($this->user->first_name.' '.$this->user->last_name, $record->client_name);
        $this->assertEquals($subscription->version, $record->product_version);
    }

    #[Group('orderFilter')]
    public function test_get_selected_version_orders_when_version_from_is_null_and_version_till_is_null_should_not_change_the_query(): void
    {
        $this->getLoggedInUser('admin');
        $this->createOrder('v3.0.0');
        $this->createOrder('v3.1.0');
        $this->createOrder('v3.2.0');
        $baseQuery = $this->getPrivateMethod($this->classObject, 'getBaseQueryForOrders');
        $query = $this->getPrivateMethod($this->classObject, 'getSelectedVersionOrders', [$baseQuery, null, null, null]);
        $this->assertEquals(3, $query->get()->count());
    }

    #[Group('orderFilter')]
    public function test_get_selected_version_orders_when_version_from_is_null_should_give_result_which_all_passed_version(): void
    {
        $this->getLoggedInUser('admin');
        $this->createOrder('v3.0.0');
        $this->createOrder('v3.1.0');
        $this->createOrder('v3.2.0');
        $baseQuery = $this->getPrivateMethod($this->classObject, 'getBaseQueryForOrders');
        $query = $this->getPrivateMethod($this->classObject, 'getSelectedVersionOrders', [$baseQuery, null, 'v3.1.0', null]);
        $records = $query->get();
        $this->assertEquals(3, $records->count());
    }

    #[Group('orderFilter')]
    public function test_get_selected_version_orders_when_version_from_is_not_nullproductidisnull_should_give_result_which_are_less_than_to_passed_version(): void
    {
        $this->getLoggedInUser('admin');
        $this->createOrder('v3.0.0');
        $this->createOrder('v3.1.0');
        $this->createOrder('v3.2.0');
        $baseQuery = $this->getPrivateMethod($this->classObject, 'getBaseQueryForOrders');
        $query = $this->getPrivateMethod($this->classObject, 'getSelectedVersionOrders', [$baseQuery, 'v3.1.0', null, null]);
        $records = $query->get();
        $this->assertEquals(1, $records->count());
        $this->assertEquals('v3.1.0', $records[0]->product_version);
    }

    #[Group('orderFilter')]
    public function test_get_selected_version_orders_when_version_from_is_not_null_and_version_till_is_not_null_should_give_intersection_of_both(): void
    {
        $this->getLoggedInUser('admin');
        $this->createOrder('v3.0.0');
        $this->createOrder('v3.1.0');
        $this->createOrder('v3.2.0');
        $baseQuery = $this->getPrivateMethod($this->classObject, 'getBaseQueryForOrders');
        $query = $this->getPrivateMethod($this->classObject, 'getSelectedVersionOrders', [$baseQuery, 'v3.1.0', 'v3.1.0', null]);
        $records = $query->get();
        $this->assertEquals(1, $records->count());
        $this->assertEquals('v3.1.0', $records[0]->product_version);
    }

    public function test_all_installations_seach_installed_products_should_give_installed_product(): void
    {
        $this->getLoggedInUser('admin');
        $this->createOrder('v3.0.0');
        $this->createOrder('v3.1.0');
        $this->createOrder('v3.2.0');
        $baseQuery = $this->getPrivateMethod($this->classObject, 'getBaseQueryForOrders');
        $query = $this->getPrivateMethod($this->classObject, 'allInstallations', ['installed', $baseQuery]);
        $records = $query->get();
        $this->assertEquals(0, $records->count());
    }

    #[Group('orderFilter')]
    public function test_all_installations_seach_i_notnstalled_products_should_give_not_installed_subscripion(): void
    {
        $this->getLoggedInUser('admin');
        $this->createOrder('v3.0.0');
        $this->createOrder('v3.1.0');
        $this->createOrder('v3.2.0');
        $baseQuery = $this->getPrivateMethod($this->classObject, 'getBaseQueryForOrders');
        $query = $this->getPrivateMethod($this->classObject, 'allInstallations', ['not_installed', $baseQuery]);
        $records = $query->get();
        $this->assertEquals(3, $records->count());
    }

    #[Group('orderFilter')]
    public function test_all_installations_check_active_installation_should_give_active_installation(): void
    {
        $this->getLoggedInUser('admin');
        $this->createOrder('v3.0.0');
        $this->createOrder('v3.1.0');
        $this->createOrder('v3.2.0');
        $baseQuery = $this->getPrivateMethod($this->classObject, 'getBaseQueryForOrders');
        $query = $this->getPrivateMethod($this->classObject, 'allInstallations', ['paid_inactive_ins', $baseQuery]);
        $records = $query->get();
        $this->assertEquals(0, $records->count());
    }

    #[Group('orderFilter')]
    private function createOrder(string $version = 'v3.0.0'): void
    {
        $product = Product::create(['name' => 'Helpdesk'.$version]);
        $order = Order::create(['client' => $this->user->id, 'order_status' => 'executed',
            'product' => $product->id, 'number' => mt_rand(100000, 999999), ]);
        Subscription::create(['order_id' => $order->id, 'product_id' => $product->id, 'version' => $version]);
    }

    public function test_get_base_query_for_orders_should_not_give_duplicates_when_same_order_has_more_than_one_installationpath(): void
    {
        $this->getLoggedInUser('admin');
        $product = Product::create(['name' => 'Helpdesk']);
        $order = Order::create(['client' => $this->user->id, 'order_status' => 'executed', 'product' => $product->id]);

        // Create multiple installation details for the same order
        InstallationDetail::create(['installation_path' => 'test1.com', 'order_id' => $order->id]);
        InstallationDetail::create(['installation_path' => 'test2.com', 'order_id' => $order->id]);

        $query = $this->getPrivateMethod($this->classObject, 'getBaseQueryForOrders');
        $results = $query->get();
        $uniqueOrder = $results->pluck('id')->unique();
        $this->assertCount(1, $uniqueOrder);
    }
}
