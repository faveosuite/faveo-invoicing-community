<?php

namespace Razorpay\Tests;

use Razorpay\Api\Request;

class CustomerTest extends TestCase
{   
    /**
     * Specify unique customer id
     * for example cust_IEfAt3ruD4OEzo
     */
    
    private $customerId = "cust_IEfAt3ruD4OEzo";

    static private $baId;

    public function setUp(): void
    {
        parent::setUp();
    }
    
    /**
     * Create customer
     */
    public function testCreateCustomer()
    {
        $data = $this->api->customer->create(array('name' => 'Razorpay User 38', 'email' => 'customer38@razorpay.com' ,'fail_existing'=>'0'));

        $this->assertTrue(is_array($data->toArray()));

        $this->assertTrue(in_array('customer',$data->toArray()));
    }
    
    /**
     * Edit customer
     */
    public function testEditCustomer()
    {
        $data = $this->api->customer->fetch($this->customerId)->edit(array('name' => 'Razorpay User 21' ,'contact'=>'9123456780'));
        
        $this->assertTrue(is_array($data->toArray()));

        $this->assertTrue(in_array($this->customerId, $data->toArray()));
    }

    /**
     * Fetch customer All
     */
    public function testFetchAll()
    {
        $data = $this->api->customer->all();
        
        $this->assertTrue(is_array($data->toArray()));

        $this->assertTrue(is_numeric($data->count()));

        $this->assertTrue(is_array($data['items']));
    }

    /**
     * Fetch a customer
     */
    public function testFetchCustomer()
    {
        $data = $this->api->customer->fetch($this->customerId);
        
        $this->assertTrue(is_array($data->toArray()));

        $this->assertTrue(in_array($this->customerId, $data->toArray()));
    }

    /**
     * Add Bank account
     */
    public function testBankAccount()
    {
        $data = $this->api->customer->fetch($this->customerId)->addBankAccount([
            "ifsc_code" => "UTIB0000194",
            "account_number" => "919999999999",
            "beneficiary_name" => "Pratheek",
            "beneficiary_address1" => "address 1",
            "beneficiary_address2" => "address 2",
            "beneficiary_address3" => "address 3",
            "beneficiary_address4" => "address 4",
            "beneficiary_email" => "random@email.com",
            "beneficiary_mobile" => "8762489310",
            "beneficiary_city" => "Bangalore",
            "beneficiary_state" => "KA",
            "beneficiary_country" => "IN",
        ]);

        CustomerTest::$baId = $data->id;

        $this->assertTrue(is_array($data->toArray()));

        $this->assertTrue(in_array("bank_account", $data->toArray()));
    }

    public function testDeleteBankAccount(){
        if(CustomerTest::$baId)
        {
          $data = $this->api->customer->fetch($this->customerId)->deleteBankAccount(CustomerTest::$baId); 
          
          $this->assertTrue(is_array($data->toArray()));

          $this->assertTrue(in_array("bank_account", $data->toArray()));
        }
    }
}