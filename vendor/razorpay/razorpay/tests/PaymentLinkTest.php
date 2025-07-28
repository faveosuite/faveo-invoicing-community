<?php

namespace Razorpay\Tests;

use Razorpay\Api\Request;

class PaymentLinkTest extends TestCase
{
    /**
     * Specify unique paymentlink id
     * for example plink_IEjOvfQs5AyjMN 
     */
    private $paymentLinkId = "plink_IEjOvfQs5AyjMN";

    public function setUp(): void
    {
        parent::setUp();
    }
    
    /**
     * Create Payment Link
     */
    public function testCreatePaymentLink()
    {
        $data = $this->api->paymentLink->create(array('amount'=>500, 'currency'=>'INR', 'accept_partial'=>true,
        'first_min_partial_amount'=>100, 'description' => 'For XYZ purpose', 'customer' => array('name'=>'Gaurav Kumar',
        'email' => 'gaurav.kumar@example.com', 'contact'=>'9000090000'),  'notify'=>array('sms'=>true, 'email'=>true) ,
        'reminder_enable'=>true ,'notes'=>array('policy_name'=> 'Jeevan Bima'),'callback_url' => 'https://example-callback-url.com/',
        'callback_method'=>'get'));

        $this->cancelLinkId = $data->id;

        $this->assertTrue(is_array($data->toArray()));

        $this->assertTrue(in_array('accept_partial',$data->toArray()));
    }

    /**
     * Fetch multiple refunds for a payment
     */
    public function testFetchAllMutlipleRefund()
    {
        $data = $this->api->paymentLink->all();

        $this->assertTrue(is_array($data->toArray()));

        $this->assertTrue(is_array($data['payment_links']));
    }

    /**
     * Fetch a specific refund for a payment
     */
    public function testFetchRefund()
    {
        $data = $this->api->paymentLink->fetch($this->paymentLinkId);

        $this->assertTrue(is_array($data->toArray()));
        
        $this->assertTrue(in_array('amount',$data->toArray()));
    }

    /**
     * Update Payment Link
     */
    public function testUpdatePaymentLink()
    {
        $payment = $this->api->paymentLink->create(array('amount'=>500, 'currency'=>'INR', 'accept_partial'=>true,
        'first_min_partial_amount'=>100, 'description' => 'For XYZ purpose', 'customer' => array('name'=>'Gaurav Kumar',
        'email' => 'gaurav.kumar@example.com', 'contact'=>'9000090000'),  'notify'=>array('sms'=>true, 'email'=>true) ,
        'reminder_enable'=>true ,'notes'=>array('policy_name'=> 'Jeevan Bima'),'callback_url' => 'https://example-callback-url.com/',
        'callback_method'=>'get'));

        $data = $this->api->paymentLink->fetch($payment->id)->edit(array("reference_id"=>"TS".time(), "reminder_enable"=>0, "notes"=>["policy_name"=>"Jeevan Saral 2"]));

        $this->assertTrue(is_array($data->toArray()));

        $this->assertTrue(in_array('accept_partial',$data->toArray()));
    }

    
    /**
     * Send notification
     */
    public function testSendNotification()
    {
        $data = $this->api->paymentLink->fetch($this->paymentLinkId)->notifyBy('email');

        $this->assertTrue(is_array($data));

        $this->assertTrue(in_array('success',$data));
    }

    /**
     * Cancel Payment Link
     */
    public function testCancelPaymentLink()
    {
        $paymentLink = $this->api->paymentLink->create(array('amount'=>500, 'currency'=>'INR', 'accept_partial'=>true,
        'first_min_partial_amount'=>100, 'description' => 'For XYZ purpose', 'customer' => array('name'=>'Gaurav Kumar',
        'email' => 'gaurav.kumar@example.com', 'contact'=>'9000090000'),  'notify'=>array('sms'=>true, 'email'=>true) ,
        'reminder_enable'=>true ,'notes'=>array('policy_name'=> 'Jeevan Bima'),'callback_url' => 'https://example-callback-url.com/',
        'callback_method'=>'get'));

        $data = $this->api->paymentLink->fetch($paymentLink->id)->cancel();

        $this->assertTrue(is_array($data->toArray()));

        $this->assertTrue(in_array('accept_partial',$data->toArray()));
    }

    /**
     * Transfer payments received using payment links
     */
    public function testCreateTransferPayments()
    {
        $data = $this->api->paymentLink->create(array('amount'=>20000, 'currency'=>'INR', 'accept_partial'=>false, 'description' => 'For XYZ purpose', 'customer' => array('name'=>'Gaurav Kumar', 'email' => 'gaurav.kumar@example.com', 'contact'=>'9000090000'),  'notify'=>array('sms'=>true, 'email'=>true) ,'reminder_enable'=>true , 'options'=>array('order'=>array('transfers'=>array('account'=>'acc_CPRsN1LkFccllA', 'amount'=>500, 'currency'=>'INR', 'notes'=>array('branch'=>'Acme Corp Bangalore North', 'name'=>'Bhairav Kumar' ,'linked_account_notes'=>array('branch'))), array('account'=>'acc_CNo3jSI8OkFJJJ', 'amount'=>500, 'currency'=>'INR', 'notes'=>array('branch'=>'Acme Corp Bangalore North', 'name'=>'Saurav Kumar' ,'linked_account_notes'=>array('branch')))))));

        $this->assertTrue(is_array($data->toArray()));

        $this->assertTrue(in_array('accept_partial',$data->toArray()));
    }   

    /**
     * Offers on payment links
     */
    public function testOfferPaymentLinks()
    {
        $data = $this->api->paymentLink->create(array('amount'=>20000, 'currency'=>'INR', 'accept_partial'=>false, 'description' => 'For XYZ purpose', 'customer' => array('name'=>'Gaurav Kumar', 'email' => 'gaurav.kumar@example.com', 'contact'=>'9000090000'),  'notify'=>array('sms'=>true, 'email'=>true) ,'reminder_enable'=>true , 'options'=>array('order'=>array('transfers'=>array('account'=>'acc_CPRsN1LkFccllA', 'amount'=>500, 'currency'=>'INR', 'notes'=>array('branch'=>'Acme Corp Bangalore North', 'name'=>'Bhairav Kumar' ,'linked_account_notes'=>array('branch'))), array('account'=>'acc_CNo3jSI8OkFJJJ', 'amount'=>500, 'currency'=>'INR', 'notes'=>array('branch'=>'Acme Corp Bangalore North', 'name'=>'Saurav Kumar' ,'linked_account_notes'=>array('branch')))))));

        $this->assertTrue(is_array($data->toArray()));

        $this->assertTrue(in_array('accept_partial',$data->toArray()));
    }  

    /**
     * Managing reminders for payment links
     */
    public function testManagingRemainder()
    {
        $data = $this->api->paymentLink->create(array('amount'=>500, 'currency'=>'INR', 'accept_partial'=>true, 'first_min_partial_amount'=>100, 'description' => 'For XYZ purpose', 'customer' => array('name'=>'Gaurav Kumar', 'email' => 'gaurav.kumar@example.com', 'contact'=>'9000090000'),  'notify'=>array('sms'=>true, 'email'=>true) ,'reminder_enable'=>false));

        $this->assertTrue(is_array($data->toArray()));

        $this->assertTrue(in_array('amount',$data->toArray()));
    }
  
    /**
     * Rename labels in checkout section
     */
    public function testRenameLabelsCheckout()
    {
        $data = $this->api->paymentLink->create(array('amount'=>500, 'currency'=>'INR', 'accept_partial'=>true, 'first_min_partial_amount'=>100, 'description' => 'For XYZ purpose', 'customer' => array('name'=>'Gaurav Kumar', 'email' => 'gaurav.kumar@example.com', 'contact'=>'9000090000'),  'notify'=>array('sms'=>true, 'email'=>true) ,'reminder_enable'=>true , 'options'=>array('checkout'=>array('partial_payment'=>array('min_amount_label'=>'Minimum Money to be paid', 'partial_amount_label'=>'Pay in parts', 'partial_amount_description'=>'Pay at least ₹100', 'full_amount_label'=>'Pay the entire amount')))));

        $this->assertTrue(is_array($data->toArray()));

        $this->assertTrue(in_array('amount',$data->toArray()));
    }  

    /**
     * Change Business name
     */
    public function testBusinessName()
    {
        $data = $this->api->paymentLink->create(array('amount'=>500, 'currency'=>'INR', 'accept_partial'=>true, 'first_min_partial_amount'=>100, 'description' => 'For XYZ purpose', 'customer' => array('name'=>'Gaurav Kumar', 'email' => 'gaurav.kumar@example.com', 'contact'=>'9000090000'),  'notify'=>array('sms'=>true, 'email'=>true) ,'reminder_enable'=>true , 'options'=>array('checkout'=>array('name'=>'Lacme Corp'))));

        $this->assertTrue(is_array($data->toArray()));

        $this->assertTrue(in_array('accept_partial',$data->toArray()));
    }

    /**
     * Change checkouts fields
     */
    public function testCheckoutFields()
    {
        $data = $this->api->paymentLink->create(array('amount'=>500, 'currency'=>'INR', 'accept_partial'=>true, 'first_min_partial_amount'=>100, 'description' => 'For XYZ purpose', 'customer' => array('name'=>'Gaurav Kumar', 'email' => 'gaurav.kumar@example.com', 'contact'=>'9000090000'),  'notify'=>array('sms'=>true, 'email'=>true) ,'reminder_enable'=>true , 'options'=>array('checkout'=>array('method'=>array('netbanking'=>'1', 'card'=>'1', 'upi'=>'0', 'wallet'=>'0')))));

        $this->assertTrue(is_array($data->toArray()));

        $this->assertTrue(in_array('accept_partial',$data->toArray()));
    }

    /**
     * Rename labels in checkout section
     */
    public function testRenameLabelsPayments()
    {
        $data = $this->api->paymentLink->create(array('amount'=>500, 'currency'=>'INR', 'accept_partial'=>true, 'first_min_partial_amount'=>100, 'description' => 'For XYZ purpose', 'customer' => array('name'=>'Gaurav Kumar', 'email' => 'gaurav.kumar@example.com', 'contact'=>'9000090000'),  'notify'=>array('sms'=>true, 'email'=>true) ,'reminder_enable'=>true , 'options'=>array('hosted_page'=>array('label'=>array('receipt'=>'Ref No.'.time(), 'description'=>'Course Name', 'amount_payable'=>'Course Fee Payable', 'amount_paid'=>'Course Fee Paid', 'partial_amount_due'=>'Fee Installment Due', 'partial_amount_paid'=>'Fee Installment Paid', 'expire_by'=>'Pay Before', 'expired_on'=>'1632223497','amount_due'=>'Course Fee Due'), 'show_preferences'=>array('issued_to'=>false)))));
        
        $this->assertTrue(is_array($data->toArray()));

        $this->assertTrue(in_array('accept_partial',$data->toArray()));
    }
}