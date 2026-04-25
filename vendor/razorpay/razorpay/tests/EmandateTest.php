<?php

namespace Razorpay\Tests;

use Razorpay\Api\Request;

class EmandateTest extends TestCase
{ 
    /**
     * Specify unique customer id, invoice id & token id 
     * for example cust_IEfAt3ruD4OEzo, inv_IF37M4q6SdOpjT & token_IF1ThOcFC9J7pU
     */
    
    private $customerId = "cust_IEfAt3ruD4OEzo";
    
    private $invoiceId = "inv_JM5rC3ddYKVWgy";

    private $tokenId = "token_IF1ThOcFC9J7pU";

    public function setUp(): void
    {
        parent::setUp();
    }
    
    /**
     * Create customer
     */
    public function testCreateCustomerEmandate()
    {
        $data = $this->api->customer->create(array('name' => 'Razorpay User 71', 'email' => 'customer71@razorpay.com', 'contact'=> 9999999999, 'fail_existing'=>'0'));

        $this->assertTrue(is_array($data->toArray()));

        $this->assertTrue(in_array('customer',$data->toArray()));
    }
    
    /**
     * Create Order
     */
    public function testCreateOrderEmandate()
    {
        $data = $this->api->order->create(array('amount' => 100,'currency' => 'INR','method' => 'emandate','customer_id' => $this->customerId,'receipt' => 'Receipt No. '.time(), 'notes' => array('notes_key_1' => 'Beam me up Scotty','notes_key_2' => 'Engage'),'token' => array('auth_type' => 'netbanking','max_amount' => 9999900,'expire_at' => 4102444799,'notes' => array('notes_key_1' => 'Tea, Earl Grey, Hot','notes_key_2' => 'Tea, Earl Grey… decaf.'),'bank_account' => array('beneficiary_name' => 'Gaurav Kumar','account_number' => '1121431121541121','account_type' => 'savings','ifsc_code' => 'HDFC0000001'))));
     
        $this->assertTrue(is_array($data->toArray()));
        
        $this->assertArrayHasKey('id',$data->toArray());
    }

    /**
     * Create registration link
     */
    public function testCreateSubscriptionRegistrationEmandate()
    {
        $data = $this->api->subscription->createSubscriptionRegistration(array('customer'=>array('name'=>'Gaurav Kumar','email'=>'gaurav.kumar@example.com','contact'=>'9999999999'),'type'=>'link','amount'=>100,'currency'=>'INR','description'=>'Registration Link for Gaurav Kumar','subscription_registration'=>array('method'=>'card','max_amount'=>'500','expire_at'=> strtotime("+1 month") ),'receipt'=>'Receipt No. '.time(),'email_notify'=>1,'sms_notify'=>1,'expire_by'=>strtotime("+1 month"),'notes' => array('note_key 1' => 'Beam me up Scotty','note_key 2' => 'Tea. Earl Gray. Hot.')));

        $this->assertTrue(is_array($data->toArray()));

        $this->assertTrue(in_array('customer',$data->toArray()));
    }
    
    /**
     * Send/Resend notifications
     */
    public function testSendNotification()
    {
        $data = $this->api->invoice->fetch($this->invoiceId)->notifyBy('sms');

        $this->assertTrue(is_array($data));

        $this->assertArrayHasKey('success',$data);
            
    }

    /**
     * Fetch token by payment ID
     */
    public function testFetchTokenByPaymentId()
    {
       $payment = $this->api->payment->all();

       $data = $this->api->payment->fetch($payment['items'][0]['id']);

       $this->assertTrue(is_array($data->toArray()));

       $this->assertArrayHasKey('id',$data->toArray());
    }

    /**
     * Fetch tokens by customer id
     */
    public function testFetchTokenByCustomerId()
    {
       $data = $this->api->customer->fetch($this->customerId)->tokens()->all();

       $this->assertTrue(is_array($data->toArray()));
    }

}