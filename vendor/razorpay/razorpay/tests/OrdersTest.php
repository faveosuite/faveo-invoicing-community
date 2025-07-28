<?php

namespace Razorpay\Tests;

use Razorpay\Api\Request;

class OrdersTest extends TestCase
{   
    /**
     * Specify unique order id
     * for example order_IEfF1OrQbqxYJq
     */

    private $orderId = "order_IEfF1OrQbqxYJq";

    public function setUp(): void
    {
        parent::setUp();
    }
    
    /**
     * Create order
     */
    public function testCreateOrder()
    {
        $data = $this->api->order->create(array('receipt' => '123', 'amount' => 100, 'currency' => 'INR', 'notes'=> array('key1'=> 'value3','key2'=> 'value2')));

        $this->assertTrue(is_array($data->toArray()));

        $this->assertArrayHasKey('id',$data->toArray());
    }

    /**
     * Fetch all orders
     */
    public function testAllOrders()
    {
        $data = $this->api->order->all();

        $this->assertTrue(is_array($data->toArray()));

        $this->assertTrue(is_array($data['items']));
    }
    
    /**
     * Fetch particular order
     */
    public function testFetchOrder()
    {
        $data = $this->api->order->fetch($this->orderId);

        $this->assertTrue(is_array($data->toArray()));

        $this->assertTrue(in_array('order',$data->toArray()));
    }

    /**
     * Fetch payments for an order
     */
    public function testOrderFetchById()
    {
        $data = $this->api->order->fetch($this->orderId)->payments();

        $this->assertTrue(is_array($data->toArray()));

    }
    
    /**
     * Update Order
     */
    public function testUpdateOrder()
    {
        $data = $this->api->order->fetch($this->orderId)->edit(array('notes'=> array('notes_key_1'=>'Beam me up Scotty. 1', 'notes_key_2'=>'Engage')));

        $this->assertTrue(is_array($data->toArray()));

        $this->assertArrayHasKey('id',$data->toArray());

    }
}