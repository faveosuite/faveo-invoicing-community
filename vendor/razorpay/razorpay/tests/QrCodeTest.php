<?php

namespace Razorpay\Tests;

use Razorpay\Api\Request;

class QrCodeTest extends TestCase
{
    /**
     * Specify unique qrcode id & customer id
     * for example qr_IEjmDxjAY3iCnw & cust_IEfAt3ruD4OEzo
     */

    private $qrCodeId = "qr_IEjmDxjAY3iCnw";

    private $customerId = "cust_IEfAt3ruD4OEzo";

    public function setUp(): void
    {
        parent::setUp();
    }
    
    /**
     * Create Qr code
     */
    public function testCreateQrCode()
    {   
        $data = $this->api->qrCode->create(array("type" => "upi_qr","name" => "Store_1", "usage" => "single_use","fixed_amount" => 1,"payment_amount" => 300,"customer_id" => $this->customerId, "description" => "For Store 1","close_by" => strtotime('+3 minutes', time()),"notes" => array("purpose" => "Test UPI QR code notes")));

        $this->assertTrue(is_array($data->toArray()));

        $this->assertTrue(in_array('qr_code',$data->toArray()));
    }

    /**
     * Fetch all Qr code
     */
    public function testFetchAllQrCode()
    {
        $data = $this->api->plan->all();

        $this->assertTrue(is_array($data->toArray()));

        $this->assertTrue(is_array($data['items']));
    }

    /**
     * Fetch a Qr code
     */
    public function testFetchQrCode()
    {
        $data = $this->api->qrCode->fetch($this->qrCodeId);

        $this->assertTrue(is_array($data->toArray()));
        
    }
    
    /**
     * Fetch a Qr code for customer id
     */
    public function testFetchQrCodeByCustomerId()
    {
        $data = $this->api->qrCode->all(["customer_id" => $this->customerId ]);

        $this->assertTrue(is_array($data->toArray()));

        $this->assertTrue(is_array($data['items']));
    }

    /**
     * Fetch a Qr code for payment id
     */
    public function testFetchQrCodePaymentById()
    {
        $data = $this->api->qrCode->all();

        $this->assertTrue(is_array($data->toArray()));
        
    }

    /**
     * Close a QR Code
     */
    public function testCloseQrCode()
    {
        $qrCodeId = $this->api->qrCode->create(array("type" => "upi_qr","name" => "Store_1", "usage" => "single_use","fixed_amount" => 1,"payment_amount" => 300,"customer_id" => $this->customerId, "description" => "For Store 1","close_by" => strtotime('+3 minutes', time()), "notes" => array("purpose" => "Test UPI QR code notes")));
        
        $data = $this->api->qrCode->fetch($qrCodeId->id)->close();

        $this->assertTrue(is_array($data->toArray()));
        
        $this->assertTrue(in_array('qr_code',$data->toArray()));
    }
}