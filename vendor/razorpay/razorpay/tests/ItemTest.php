<?php

namespace Razorpay\Tests;

use Razorpay\Api\Request;

class ItemTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();
    }
    
    /**
     * Create item
     */
    public function testcreate()
    {
        $data = $this->api->Item->create(array(
            "name" => "Book / English August",
            "description" => "An indian story, Booker prize winner.",
            "amount" => 20000,
            "currency" => "INR"
        ));

        $this->assertTrue(is_array($data->toArray()));

        $this->assertTrue(in_array('id',$data->toArray()));
    }

    /**
     * Fetch all orders
     */
    public function testAllItems()
    {
        $data = $this->api->Item->all();

        $this->assertTrue(is_array($data->toArray()));

        $this->assertTrue(is_array($data['items']));
    }
    
    /**
     * Fetch particular item
     */
    public function testfetchItem()
    {
        $item = $this->api->Item->create(array(
            "name" => "Book / English August",
            "description" => "An indian story, Booker prize winner.",
            "amount" => 20000,
            "currency" => "INR"
        ));

        $data = $this->api->Item->fetch($item->id);

        $this->assertTrue(is_array($data->toArray()));

        $this->assertTrue(in_array($item->id, $data->toArray()));
    }
    
    /**
     * Update item
     */
    public function testUpdate()
    {
        $item = $this->api->Item->create(array(
            "name" => "Book / English August",
            "description" => "An indian story, Booker prize winner.",
            "amount" => 20000,
            "currency" => "INR"
        ));

        $data = $this->api->Item->fetch($item->id)->edit(array(
            "name" => "Book / English August",
            "description" => "An indian story, Booker prize winner.",
            "amount" => 20000,
            "currency" => "INR"
        ));

        $this->assertTrue(is_array($data->toArray()));

    }

    /**
     * Delete item
     */
    public function testDelete()
    {
        $item = $this->api->Item->create(array(
            "name" => "Book / English August",
            "description" => "An indian story, Booker prize winner.",
            "amount" => 20000,
            "currency" => "INR"
        ));

        $data = $this->api->Item->fetch($item->id)->delete();

        $this->assertTrue(is_array($data->toArray()));
    }
}