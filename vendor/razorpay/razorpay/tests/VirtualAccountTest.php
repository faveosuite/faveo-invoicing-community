<?php

namespace Razorpay\Tests;

use Razorpay\Api\Request;

class VirtualAccountTest extends TestCase
{
    /**
     * Specify unique customer id, payment id & virtual-account id
     * for example cust_IEm1ERQLCdRGPV, pay_IEljgrElHGxXAC &
     * va_IEmC8SOoyGxsNn
     */

    private $customerId = "cust_IEm1ERQLCdRGPV";

    private $paymentId = "pay_IEljgrElHGxXAC";

    private $virtualAccountId = "va_IEmC8SOoyGxsNn";

    public function setUp(): void
    {
        parent::setUp();
    }
    
    /**
     * Create a virtual account
     */
    public function testCreateVirtualAccount()
    {       
        $data = $this->api->virtualAccount->create(array('receivers' => array('types' => array('bank_account')),'description' => 'Virtual Account created for Raftar Soft','customer_id' => $this->customerId ,'close_by' => strtotime('+16 minutes', time()), 'notes' => array('project_name' => 'Banking Software')));

        $this->assertTrue(is_array($data->toArray()));

        $this->assertArrayHasKey('customer_id',$data->toArray());
    }

    /**
     * Create a virtual account with TPV
     */
    public function testCreateVirtualAccountTpv()
    {
        $data = $this->api->virtualAccount->create(array('receivers' => array('types'=> array('bank_account')),'allowed_payers' => array(array('type'=>'bank_account','bank_account'=>array('ifsc'=>'RATN0VAAPIS','account_number'=>'2223330027558515'))),'description' => 'Virtual Account created for Raftar Soft','customer_id' => $this->customerId, 'notes' => array('project_name' => 'Banking Software')));

        $this->assertTrue(is_array($data->toArray()));

        $this->assertArrayHasKey('customer_id',$data->toArray());
    }

    /**
     * Fetch all virtual account
     */
    public function testFetchAllVirtualAccounts()
    {
        $data = $this->api->virtualAccount->all();

        $this->assertTrue(is_array($data->toArray()));

        $this->assertTrue(in_array('collection',$data->toArray()));
    }

    /**
     * Fetch payments for a virtual account
     */
    public function testFetchPayment()
    {
        $data = $this->api->virtualAccount->fetch($this->virtualAccountId)->payments();

        $this->assertTrue(is_array($data->toArray()));

        $this->assertArrayHasKey('items',$data->toArray());
    }

    /**
     * Refund payments made to a virtual account
     */
    public function testFetchRefund()
    {
        $payment = $this->api->payment->all();

        $data = $this->api->payment->fetch($this->paymentId)->refunds();
        
        $this->assertTrue(is_array($data->toArray()));
        
    }

    /**
     * Close virtual account
     */
    public function testCloseVirtualAccount()
    {
        $payment = $this->api->virtualAccount->all();

        if($payment['count'] !== 0){

            $data = $this->api->virtualAccount->fetch($payment['items'][0]['id'])->close();
            
            $this->assertTrue(is_array($data->toArray()));
    
            $this->assertArrayHasKey('id',$data->toArray());
        }
    }
}