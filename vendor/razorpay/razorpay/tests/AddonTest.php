<?php

namespace Razorpay\Tests;

use Razorpay\Api\Request;

class AddonTest extends TestCase
{
    /**
     * Specify unique addon id & plan id 
     * for example ao_IEf05Yeu52LlKL & plan_IEeswu4zFBRGwi
     */
    
    private $addonId = "ao_IEf05Yeu52LlKL";

    private $planId = "plan_IEeswu4zFBRGwi";

    public function setUp(): void
    {
        parent::setUp();
    }
    
    /**
     * Create an Add-on
     */
    public function testCreateAddon()
    {
        $subscription = $this->api->subscription->create(array('plan_id' => $this->planId, 'customer_notify' => 1,'quantity'=>1, 'total_count' => 6, 'addons' => array(array('item' => array('name' => 'Delivery charges', 'amount' => 3000, 'currency' => 'INR'))),'notes'=> array('key1'=> 'value3','key2'=> 'value2')));
        
        $data =  $this->api->subscription->fetch($subscription->id)->createAddon(array('item' => array('name' => 'Extra Chair', 'amount' => 3000, 'currency' => 'INR'), 'quantity' => 1));

        $this->assertTrue(is_array($data->toArray()));
        
        $this->assertTrue(is_object($data['item']));
    }
    
    /**
     * Fetch Subscription Link by ID
     */
    public function testFetchSubscriptionLink()
    {
        $data = $this->api->addon->fetch($this->addonId);
        
        $this->assertTrue(is_array($data->toArray()));
        
        $this->assertTrue($data['entity']=='addon');
    }
    
    /**
     * Fetch all addons
     */
    public function testFetchAllAddon()
    {
        $data = $this->api->addon->fetchAll();
        
        $this->assertTrue(is_array($data->toArray()));
        
        $this->assertTrue(is_array($data['items']));
    }

}