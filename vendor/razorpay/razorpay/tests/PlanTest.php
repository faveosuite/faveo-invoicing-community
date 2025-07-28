<?php

namespace Razorpay\Tests;

use Razorpay\Api\Request;

class PlanTest extends TestCase
{
    /**
     * Specify unique plan id
     * for example plan_IEeswu4zFBRGwi 
     */

    private $planId = "plan_IEeswu4zFBRGwi";

    public function setUp(): void
    {
        parent::setUp();
    }
    
    /**
     * Create Plan
     */
    public function testCreatePlan()
    {
        $data = $this->api->plan->create(array('period' => 'weekly', 'interval' => 1, 'item' => array('name' => 'Test Weekly 1 plan', 'description' => 'Description for the weekly 1 plan', 'amount' => 600, 'currency' => 'INR'),'notes'=> array('key1'=> 'value3','key2'=> 'value2')));

        $this->assertTrue(is_array($data->toArray()));

        $this->assertTrue(in_array('plan',$data->toArray()));
    }

    /**
     * Fetch all plans
     * @covers \Razorpay\Api\Collection::count
     */
    public function testFetchAllPlans()
    {
        $data = $this->api->plan->all();

        $this->assertTrue(is_array($data->toArray()));

        $this->assertTrue($data->count() >= 0);

        $this->assertTrue(is_array($data['items']));
    }

    /**
     * Fetch particular plan
     */
    public function testFetchPlan()
    {
        $data = $this->api->plan->fetch($this->planId);

        $this->assertTrue(is_array($data->toArray()));
        
        $this->assertTrue(in_array('plan',$data->toArray()));
    } 
}