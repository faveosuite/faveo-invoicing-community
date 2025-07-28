<?php

namespace Razorpay\Tests;

use Razorpay\Api\Request;

class RequestTest extends TestCase
{   

    public function setUp(): void
    {
        parent::setUp();
    }
    
    /**
     * Create customer
     */
    public function testaddHeader()
    {
        $data = $this->api->customer->create(array('name' => 'Razorpay User 38', 'email' => 'customer38@razorpay.com' ,'fail_existing'=>'0'));

        $this->assertTrue(is_array($data->toArray()));

        $this->assertTrue(in_array('customer',$data->toArray()));
    }
    
    /**
     * Edit customer
     */
    public function testEditCustomer()
    {
        $data = $this->api->customer->fetch($this->customerId)->edit(array('name' => 'Razorpay User 21' ,'contact'=>'9123456780'));
        
        $this->assertTrue(is_array($data->toArray()));

        $this->assertTrue(in_array($this->customerId, $data->toArray()));
    }

    /**
     * Fetch customer All
     */
    public function testFetchAll()
    {
        $data = $this->api->customer->all();
        
        $this->assertTrue(is_array($data->toArray()));

        $this->assertTrue(is_numeric($data->count()));

        $this->assertTrue(is_array($data['items']));
    }

    /**
     * Fetch a customer
     */
    public function testFetchCustomer()
    {
        $data = $this->api->customer->fetch($this->customerId);
        
        $this->assertTrue(is_array($data->toArray()));

        $this->assertTrue(in_array($this->customerId, $data->toArray()));
    }
}