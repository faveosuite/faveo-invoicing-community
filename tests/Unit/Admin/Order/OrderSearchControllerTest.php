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
    private $classObject;

    protected function setUp(): void
    {
        parent::setUp();
        $this->classObject = new OrderSearchController();
    }

    #[Group('orderFilter')]
    public function test_getBaseQueryForOrders_givesRequiredColumnsWhenCalled()
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
    public function test_getSelectedVersionOrders_whenVersionFromIsNullAndVersionTillIsNull_shouldNotChangeTheQuery()
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
    public function test_getSelectedVersionOrders_whenVersionFromIsNull_shouldGiveResultWhichAllPassedVersion()
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
    public function test_getSelectedVersionOrders_whenVersionFromIsNotNullproductidisnull_shouldGiveResultWhichAreLessThanToPassedVersion()
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
    public function test_getSelectedVersionOrders_whenVersionFromIsNotNullAndVersionTillIsNotNull_shouldGiveIntersectionOfBoth()
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

    public function test_allInstallations_seachInstalledProducts_shouldGiveInstalledProduct()
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
    public function test_allInstallations_seachINotnstalledProducts_shouldGiveNotInstalledSubscripion()
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
    public function test_allInstallations_checkActiveInstallation_shouldGiveActiveInstallation()
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
    private function createOrder($version = 'v3.0.0')
    {
        $product = Product::create(['name' => 'Helpdesk'.$version]);
        $order = Order::create(['client' => $this->user->id, 'order_status' => 'executed',
            'product' => $product->id, 'number' => mt_rand(100000, 999999), ]);
        Subscription::create(['order_id' => $order->id, 'product_id' => $product->id, 'version' => $version]);
    }

    public function test_getBaseQueryForOrders_shouldNotGiveDuplicates_WhenSameOrderHasMoreThanOneInstallationpath()
    {
        $this->getLoggedInUser('admin');
        $product = Product::create(['name' => 'Helpdesk']);
        $order = Order::create(['client' => $this->user->id, 'order_status' => 'executed', 'product' => $product->id]);

        // Create multiple installation details for the same order
        $installationDetails1 = InstallationDetail::create(['installation_path' => 'test1.com', 'order_id' => $order->id]);
        $installationDetails2 = InstallationDetail::create(['installation_path' => 'test2.com', 'order_id' => $order->id]);

        $query = $this->getPrivateMethod($this->classObject, 'getBaseQueryForOrders');
        $results = $query->get();
        $uniqueOrder = $results->pluck('id')->unique();
        $this->assertCount(1, $uniqueOrder);
    }
}
