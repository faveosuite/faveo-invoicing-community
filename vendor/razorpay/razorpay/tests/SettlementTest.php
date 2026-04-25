<?php

namespace Razorpay\Tests;

use Razorpay\Api\Request;

class SettlementTest extends TestCase
{
    /**
     * Specify unique settlement id
     * for example : setl_IAj6iuvvTATqOM 
     */

    private $settlementId =  "setl_IAj6iuvvTATqOM";

    public function setUp(): void
    {
        parent::setUp();
    }

    /**
     * Create on-demand settlement
     */
    public function testCreateOndemandSettlement()
    {
      $data = $this->api->settlement->createOndemandSettlement(array("amount"=> 1221, "settle_full_balance"=> false, "description"=>"Testing","notes" => array("notes_key_1"=> "Tea, Earl Grey, Hot","notes_key_2"=> "Tea, Earl Grey… decaf.")));
      
      $this->assertTrue(is_array($data->toArray()));

      $this->assertTrue(in_array('settlement.ondemand',$data->toArray()));
    }
    
    /**
     * Fetch all settlements
     */
    public function testAllSettlements()
    {
        $data = $this->api->settlement->all();

        $this->assertTrue(is_array($data->toArray()));
        
        $this->assertTrue(in_array('collection',$data->toArray()));
    }

    /**
     * Fetch a settlement
     */
    public function testFetchSettlement()
    {
        $data = $this->api->settlement->fetch($this->settlementId);
        
        $this->assertTrue(is_array($data->toArray()));

        $this->assertTrue(in_array('settlement',$data->toArray()));
    }

    /**
     * Settlement report for a month
     */
    public function testReports()
    {
        $data = $this->api->settlement->reports(array("year"=>2021,"month"=>9));

        $this->assertTrue(is_array($data->toArray()));

    }

    /**
     * Settlement recon
     */
    public function testSettlementRecon()
    {
        $data = $this->api->settlement->settlementRecon(array('year' => 2021, 'month' => 9));

        $this->assertTrue(is_array($data->toArray()));

        $this->assertArrayHasKey('items',$data);
    }
   
    /**
     * Fetch all on-demand settlements
     */
    public function testFetchAllOndemandSettlement()
    {
        $data = $this->api->settlement->fetchAllOndemandSettlement();

        $this->assertTrue(is_array($data->toArray()));

        $this->assertTrue(is_array($data['items']));
    }

    /**
     * Fetch on-demand settlement by ID
     */
    public function testFetchAllOndemandSettlementById()
    {
        $data = $this->api->settlement->fetch($this->settlementId)->TestFetchAllOndemandSettlementById();

        $this->assertTrue(is_array($data->toArray()));

        $this->assertTrue(is_array($data['items']));
    }
}