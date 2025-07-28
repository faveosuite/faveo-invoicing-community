<?php

namespace Razorpay\Tests;

use Razorpay\Api\Request;
use Razorpay\Api\Api;

class PaymentTest extends TestCase
{
    /**
     * Specify unique order id & payment id d
     * for example order_IEcrUMyevZFuCS & pay_IEczPDny6uzSnx
     */

    private $orderId = "order_IEcrUMyevZFuCS";

    private $paymentId = "pay_IEczPDny6uzSnx";

    private $OtpPaymentId = "";

    public function setUp(): void
    {
        parent::setUp();
    }    

    /**
     * Fetch all payment
     */
    public function testFetchAllPayment()
    {
        $data = $this->api->payment->all();

        $this->assertTrue(is_array($data->toArray()));

        $this->assertTrue(is_array($data['items']));
    }

    /**
     * Fetch a payment
     */
    public function testFetchPayment()
    {
        $payment = $this->api->payment->all();

        if($payment['count'] !== 0){

            $data = $this->api->payment->fetch($payment['items'][0]['id']);

            $this->assertTrue(is_array($data->toArray()));

            $this->assertTrue(in_array('payment',$data->toArray()));
        }
    } 

    /**
     * Fetch a payment
     */
    public function testFetchOrderPayment()
    {
        $data = $this->api->order->fetch($this->orderId)->payments();

        $this->assertTrue(is_array($data->toArray()));

        $this->assertTrue(is_array($data['items']));
    }

    /**
     * Update a payment
     */
    public function testUpdatePayment()
    {
        $data = $this->api->payment->fetch($this->paymentId)->edit(array('notes'=> array('key_1'=> 'value1','key_2'=> 'value2')));

        $this->assertTrue(is_array($data->toArray()));

        $this->assertTrue(in_array('payment',$data->toArray()));
    }

    /**
     * Fetch card details with paymentId
     */
    public function testFetchCardWithPaymentId()
    {
        $data = $this->api->payment->fetch($this->paymentId)->fetchCardDetails();

        $this->assertTrue(is_array($data->toArray()));

        $this->assertTrue(in_array('card',$data->toArray())); 
    }

    /**
     * Fetch Payment Downtime Details
     */
    public function testfetchPaymentDowntime()
    {
        $data = $this->api->payment->fetchPaymentDowntime();

        $this->assertTrue(is_array($data->toArray()));

        $this->assertArrayHasKey('count',$data->toArray());
    }

    /**
     * Fetch Payment Downtime Details
     */
    public function testfetchPaymentDowntimeById()
    {
        $downtime = $this->api->payment->fetchPaymentDowntime();
        if(count($downtime['items'])>0){
          $data = $this->api->payment->fetchPaymentDowntimeById($downtime['items'][0]['id']);
          $this->assertTrue(is_array($data->toArray()));
        }else{
          $this->assertArrayHasKey('count',$downtime->toArray());
        }
    }

    /**
     * Otp Generate
     */
    public function testOtpGenerate()
    {
        $api = new Api("key", "");

        $data = $api->payment->otpGenerate($OtpPaymentId);

        $this->assertTrue(is_array($data->toArray()));

        $this->assertArrayHasKey('razorpay_payment_id',$data->toArray());
    }

    /**
     * Otp Submit
     */
    public function testOtpSubmit()
    {
        $data = $this->api->payment->fetch($paymentId)->otpSubmit(array('otp'=> '12345'));
        
        $this->assertTrue(is_array($data->toArray()));

        $this->assertArrayHasKey('razorpay_payment_id',$data->toArray());
    }

    /**
     * Otp Resend
     */
    public function testOtpResend()
    {
        $data = $this->api->payment->fetch($paymentId)->otpResend();
        
        $this->assertTrue(is_array($data->toArray()));
        
        $this->assertArrayHasKey('razorpay_payment_id',$data->toArray());
    }

}