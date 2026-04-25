<?php

namespace Razorpay\Tests;

use Razorpay\Api\Request;

class RefundTest extends TestCase
{
    /**
     * Specify unique payment id & refund id
     * for example pay_LatkcPaGiDxgRG & rfnd_IEjzeVghAS4vd1
     */

    private $paymentId = "pay_LatkcPaGiDxgRG";

    private $refundId = "rfnd_Lcsb6JNwQVAtpi";

    public function setUp(): void
    {
        parent::setUp();
    }
    
    /**
     * Create an instant refund
     */
    public function testCreateRefund()
    {
       $data = $this->api->payment->fetch($this->paymentId)->refund(array("amount"=> "100", "speed"=>"optimum", "receipt"=>"Receipt No. ".time()));

        $this->assertTrue(is_array($data->toArray()));

        $this->assertTrue(in_array('refund',$data->toArray()));
    }


    /**
     * Fetch multiple refunds for a payment
     */
    public function testFetchMultipalRefund()
    {
        $data = $this->api->payment->fetch($this->paymentId)->fetchMultipleRefund(array("count"=>1));

        $this->assertTrue(is_array($data->toArray()));

        $this->assertTrue(is_array($data['items']));
    }

    /**
     * Fetch a specific refund for a payment
     */
    public function testFetchRefund()
    {
        $data = $this->api->payment->fetch($this->paymentId)->fetchRefund($this->refundId);

        $this->assertTrue(is_array($data->toArray()));
      
    }

    /**
     * Fetch all refunds
     */
    public function testFetchAllRefund()
    {
        $data = $this->api->refund->all(array("count"=>1));

        $this->assertTrue(is_array($data->toArray()));

        $this->assertTrue(is_array($data['items']));
    }

    /**
     * Fetch particular refund
     */
    public function testParticularRefund()
    {
        $data = $this->api->refund->fetch($this->refundId);

        $this->assertTrue(is_array($data->toArray()));

        $this->assertTrue(in_array('refund',$data->toArray()));
    }
    
    /**
     * Update the refund
     */
    public function testUpdateRefund()
    {
        $data = $this->api->refund->fetch($this->refundId)->edit(array('notes'=> array('notes_key_1'=>'Beam me up Scotty.', 'notes_key_2'=>'Engage')));
        
        $this->assertTrue(is_array($data->toArray()));

        $this->assertTrue(in_array('refund',$data->toArray()));
    }
}