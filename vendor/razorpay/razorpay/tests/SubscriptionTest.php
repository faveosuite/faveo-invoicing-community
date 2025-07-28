<?php

namespace Razorpay\Tests;

use Razorpay\Api\Request;

class SubscriptionTest extends TestCase
{
    /**
     * Specify unique subscription id & plan id
     * for example : sub_IEKtBfPIqTHLWd & plan_IEeswu4zFBRGwi
     */  

    private $subscriptionId = "sub_IEllLOZcf0PODu";

    private $plan = "plan_IEeswu4zFBRGwi";

    public function setUp(): void
    {
        parent::setUp();
    }
    
    /**
     * Create a Subscription Link
     */
    public function testCreateSubscription()
    {
        $data = $this->api->subscription->create(array('plan_id' => $this->plan, 'customer_notify' => 1,'quantity'=>1, 'total_count' => 6, 'addons' => array(array('item' => array('name' => 'Delivery charges', 'amount' => 3000, 'currency' => 'INR'))),'notes'=> array('key1'=> 'value3','key2'=> 'value2')));

        $this->assertTrue(is_array($data->toArray()));

        $this->assertTrue(in_array('id',$data->toArray()));
    }
    
    /**
     * Fetch Subscription Link by ID
     */
    public function testSubscriptionFetchId()
    {
        $data = $this->api->subscription->fetch($this->subscriptionId);
        
        $this->assertTrue(is_array($data->toArray()));

        $this->assertTrue(in_array('plan_id',$data->toArray()));
    }

    /**
     * Pause a Subscription
     */
    public function testPauseSubscription()
    {

      $data = $this->api->subscription->fetch($this->subscriptionId)->pause(['pause_at'=>'now']);

      $this->assertTrue(is_array($data->toArray()));

      $this->assertTrue(in_array('id',$data->toArray()));

      $this->assertTrue($data['status'] == 'paused');

    }  
    
    /**
     * Resume a Subscription
     */
    public function testResumeSubscription()
    {
      $data = $this->api->subscription->fetch($this->subscriptionId)->resume(['resume_at'=>'now']);

      $this->assertTrue(is_array($data->toArray()));

      $this->assertTrue(in_array('id',$data->toArray()));

    } 
    
    /**
     * Update a Subscription
     */
    public function testUpdateSubscription()
    {
        $data = $this->api->subscription->fetch($this->subscriptionId)->update(array('schedule_change_at'=>'cycle_end','quantity'=>2));
        
        $this->assertTrue(is_array($data->toArray()));

        $this->assertTrue(in_array('customer_id',$data->toArray()));
    }

    /**
     * Fetch Details of a Pending Update
     */
    public function testPendingUpdate()
    {
      $data = $this->api->subscription->fetch($this->subscriptionId)->pendingUpdate();

      $this->assertTrue(is_array($data->toArray()));

      $this->assertTrue(in_array('id',$data->toArray()));
    }

    /**
     * Cancel an Update
     */
    public function testCancelUpdate()
    {
      $data = $this->api->subscription->fetch($this->subscriptionId)->cancelScheduledChanges();

      $this->assertTrue(is_array($data->toArray()));

      $this->assertTrue(in_array('id',$data->toArray()));
    }  

    /**
     * Fetch All Invoices for a Subscription
     */
    public function testSubscriptionInvoices()
    {
      $data = $this->api->invoice->all(['subscription_id'=>$this->subscriptionId]);

      $this->assertTrue(is_array($data->toArray()));

      $this->assertTrue(is_array($data['items']));
    } 

    /**
     * Fetch all Add-ons
     */
    public function testFetchAddons()
    {
      $data =  $this->api->addon->fetchAll();

      $this->assertTrue(is_array($data->toArray()));

      $this->assertTrue(is_array($data['items']));
    }
    
    /**
     * Fetch all subscriptions
     */
    public function testFetchAllSubscriptions()
    {
        $data = $this->api->subscription->all();

        $this->assertTrue(is_array($data->toArray()));

        $this->assertTrue(is_array($data['items']));
    }
}