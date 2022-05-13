<?php

namespace Tests\Unit\Admin\Order;

use App\Http\Controllers\Order\OrderSearchController;
use App\Model\Order\Order;
use App\Model\Product\Product;
use App\Model\Product\ProductUpload;
use App\Model\Product\Subscription;
use App\User;
use Tests\DBTestCase;

class OrderSearchControllerTest extends DBTestCase
{
    private $classObject;

    public function setUp(): void
    {
        parent::setUp();
        $this->classObject = new OrderSearchController();
    }

    /** @group orderFilter */
    public function test_getBaseQueryForOrders_givesRequiredColumnsWhenCalled()
    {
        $user = factory(User::class)->create(['role' => 'admin', 'country' => 'IN']);
        $product = Product::create(['name'=>'Helpdesk']);
        $order = Order::create(['client'=> $user->id, 'order_status' => 'executed', 'product'=> $product->id]);
        $subscription = Subscription::create(['order_id'=>$order->id, 'product_id'=> $product->id, 'version'=>'v3.0.0']);
        $query = $this->getPrivateMethod($this->classObject, 'getBaseQueryForOrders');
        $record = $query->first();
        $this->assertEquals($order->id, $record->id);
        $this->assertEquals($order->order_status, $record->order_status);
        $this->assertEquals($product->name, $record->product_name);
        $this->assertEquals($user->first_name.' '.$user->last_name, $record->client_name);
        $this->assertEquals($subscription->version, $record->product_version);
    }

    /** @group orderFilter */
    public function test_getSelectedVersionOrders_whenVersionFromIsNullAndVersionTillIsNull_shouldNotChangeTheQuery()
    {
        $this->getLoggedInUser('admin');
        $this->createOrder('v3.0.0');
        $this->createOrder('v3.1.0');
        $this->createOrder('v3.2.0');
        $baseQuery = $this->getPrivateMethod($this->classObject, 'getBaseQueryForOrders');
        $query = $this->getPrivateMethod($this->classObject, 'getSelectedVersionOrders', [$baseQuery, null, null]);
        $this->assertEquals(3, $query->count());
    }

    /** @group orderFilter */
    public function test_getSelectedVersionOrders_whenVersionFromIsNullButVersionTillIsNotNull_shouldGiveResultWhichAreLessThanEqualPassedVersion()
    {
        $user = factory(User::class)->create(['role' => 'admin', 'country' => 'IN']);
        $this->createOrder('v3.0.0');
        $this->createOrder('v3.1.0');
        $this->createOrder('v3.2.0');
        $baseQuery = $this->getPrivateMethod($this->classObject, 'getBaseQueryForOrders');
        $query = $this->getPrivateMethod($this->classObject, 'getSelectedVersionOrders', [$baseQuery, null, 'v3.1.0']);
        $records = $query->get();
        $this->assertEquals(3, $records->count());
        $this->assertEquals('v3.0.0', $records[0]->product_version);
        $this->assertEquals('v3.1.0', $records[1]->product_version);
    }

    /** @group orderFilter */
    public function test_getSelectedVersionOrders_whenVersionFromIsNotNullButVersionTillIsNull_shouldGiveResultWhichAreGreaterThanEqualToPassedVersion()
    {
        $user = factory(User::class)->create(['role' => 'admin', 'country' => 'IN']);
        $this->createOrder('v3.0.0');
        $this->createOrder('v3.1.0');
        $this->createOrder('v3.1.1');
        $baseQuery = $this->getPrivateMethod($this->classObject, 'getBaseQueryForOrders');
        $query = $this->getPrivateMethod($this->classObject, 'getSelectedVersionOrders', [$baseQuery, 'v3.1.0', null]);
        $records = $query->get();
        $this->assertEquals(1, $records->count());
        $this->assertEquals('v3.1.0', $records[0]->product_version);
    }



    /** @group orderFilter */
    public function test_getSelectedVersionOrders_whenVersionFromIsNotNullAndVersionTillIsNotNull_shouldGiveIntersectionOfBoth()
    {
        $this->getLoggedInUser('admin');
        $this->createOrder('v3.0.0');
        $this->createOrder('v3.1.0');
        $this->createOrder('v3.2.0');
        $baseQuery = $this->getPrivateMethod($this->classObject, 'getBaseQueryForOrders');
        $query = $this->getPrivateMethod($this->classObject, 'getSelectedVersionOrders', [$baseQuery, 'v3.1.0', 'v3.1.0']);
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

    /** @group orderFilter */
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

    /** @group orderFilter */
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

    /** @group orderFilter */
    private function createOrder($version = 'v3.0.0')
    {

            $user = factory(User::class)->create(['role' => 'admin', 'country' => 'IN']);
            $product = Product::create(['name' => 'Helpdesk' . $version]);
            $order = Order::create(['client' => $user->id, 'order_status' => 'executed', 'product' => $product->id, 'number' => mt_rand(100000, 999999),]);
            Subscription::create(['order_id' => $order->id, 'product_id' => $product->id, 'version' => $version]);

    }
}
