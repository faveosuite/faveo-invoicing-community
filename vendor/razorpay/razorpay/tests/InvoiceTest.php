<?php

namespace Razorpay\Tests;

use Razorpay\Api\Request;

class InvoiceTest extends TestCase
{   
    /**
     * Specify unique invoice id & customer id
     * for example inv_IF37M4q6SdOpjT & cust_IEfAt3ruD4OEzo
     */

    private $invoiceId = "inv_IH69XFNA9IMQ7k";

    private $invoiceIdNotify = "inv_JM5rC3ddYKVWgy";

    private $customerId = "cust_IEfAt3ruD4OEzo";

    public function setUp(): void
    {
        parent::setUp();
    }
    
    /**
     * Create Invoice
     */
    public function testCreateInvoice()
    {
        $data = $this->api->invoice->create(array ('type' => 'invoice', 'date' => time(), 'customer_id'=> $this->customerId, 'line_items'=>array(array("name"=> "Master Cloud Computing in 30 Days", "amount"=>10000, "currency" => "INR", "quantity"=> 1))));

        $this->assertTrue(is_array($data->toArray()));

        $this->assertTrue(in_array('invoice_number',$data->toArray()));
    }

    /**
     * Fetch all invoices
     */
    public function testFetchAllInvoice()
    {

        $data = $this->api->invoice->fetch($this->invoiceId);

        $this->assertTrue(is_array($data->toArray()));

        $this->assertTrue(in_array('invoice_number',$data->toArray()));
    }
    
    /**
     * Update invoice
     */
    public function testUpdateInvoice()
    {
        $data = $this->api->invoice->fetch($this->invoiceId)->edit(array('notes' => array('updated-key' => 'An updated note.')));
        
        $this->assertTrue(is_array($data->toArray()));

        $this->assertTrue(in_array('invoice_number',$data->toArray()));

    }
    
    /**
     * Send notification
     */
    public function testSendNotification()
    {
        $data = $this->api->invoice->fetch($this->invoiceId)->notifyBy('sms');

        $this->assertTrue(is_array($data));

    }

    /**
     * Issue an invoice
     */
    public function testInvoiceIssue()
    {
        $invoice = $this->api->invoice->create(array ('type' => 'invoice', 'draft'=> true , 'date' => time(), 'customer_id'=> $this->customerId, 'line_items'=>array(array("name"=> "Master Cloud Computing in 30 Days", "amount"=>10000, "currency" => "INR", "quantity"=> 1))));
        
        $data = $this->api->invoice->fetch($invoice->id)->issue();

        $this->assertTrue(is_array($data->toArray()));

        $this->assertTrue(in_array('invoice_number',$data->toArray()));
    
    }

    /**
     * Delete an invoice
     */
    public function testDeleteInvoice()
    {
        $invoice = $this->api->invoice->create(array ('type' => 'invoice', 'draft'=> true , 'date' => time(), 'customer_id'=> $this->customerId, 'line_items'=>array(array("name"=> "Master Cloud Computing in 30 Days", "amount"=>10000, "currency" => "INR", "quantity"=> 1))));

        $data = $this->api->invoice->fetch($invoice->id)->delete();

        $this->assertTrue(is_array($data));

    }
    
    /**
     * Cancel an invoice
     */
    public function testCancelInvoice()
    {
        $invoice = $this->api->invoice->create(array ('type' => 'invoice', 'draft'=> true , 'date' => time(), 'customer_id'=> $this->customerId, 'line_items'=>array(array("name"=> "Master Cloud Computing in 30 Days", "amount"=>10000, "currency" => "INR", "quantity"=> 1))));

        $data = $this->api->invoice->fetch($invoice->id)->cancel();

        $this->assertTrue(is_array($data->toArray()));

        $this->assertTrue(in_array('invoice_number',$data->toArray()));

    }

}