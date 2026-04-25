<?php

namespace Razorpay\Tests;

use Razorpay\Api\Request;

class FundTest extends TestCase
{   
    /**
     * Specify unique customer id
     * for example cust_IEfAt3ruD4OEzo
     */

    private $customerId = "cust_IEfAt3ruD4OEzo";

    public function setUp(): void
    {
        parent::setUp();
    }
    
    /**
     * Create a fund account
     */
    public function testCreateFundAccount()
    {
        $data = $this->api->fundAccount->create(array('customer_id'=>$this->customerId,'account_type'=>'bank_account','bank_account'=>array('name'=>'Gaurav Kumar', 'account_number'=>'11214311215411', 'ifsc'=>'HDFC0000053')));

        $this->assertTrue(is_array($data->toArray()));

        $this->assertTrue(in_array('customer_id',$data->toArray()));
    }
    
    /**
     * Fetch all fund accounts
     */
    public function testCreateOrder()
    {
        $data = $this->api->fundAccount->all(array('customer_id'=>$this->customerId));

        $this->assertTrue(is_array($data->toArray()));

        $this->assertTrue(is_array($data['items']));
    }

}