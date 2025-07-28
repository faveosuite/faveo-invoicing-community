<?php

namespace Razorpay\Tests;

use Razorpay\Api\Request;
use Razorpay\Api\Errors;

class ExceptionTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();
    }
    
    /**
     * Create an order from set header application/json
     */
    public function testCreateOrderSetHeaderException()
    {
        $attribute = $this->payload();
        
        try
        {
         $this->api->setHeader('content-type', 'application/json');

         $data = $this->api->order->create($attribute);

         $this->assertTrue(is_array($data->toArray()));
        
        }
        catch(Error $e){
            throw new InvalidArgumentException($e);
        }
    }

    /**
     * Create an order
     */
    public function testCreateOrderSuccess()
    {
        $attribute = $this->payload();
        try
        {   
         $data = $this->api->order->create($attribute);

         $this->assertTrue(is_array($data->toArray()));

        }
        catch(Error $e){
            throw new InvalidArgumentException($e);
        }
    }
    
    private function payload(){
        $date = new \DateTime();
        $receiptId = $date->getTimestamp();
        return [
                 "receipt"=> (string) $receiptId,
                 "amount"=>54900,
                 "currency"=>"INR",
                 "payment_capture"=>1,
                 "app_offer"=>0,
                 "notes" => [
                    "woocommerce_order_number" => 240186
                 ],
                 "line_items_total" => 54900,
                 "line_items" => [
                        [
                            "type" => "e-commerce",
                            "sku"=> "",
                            "variant_id" => "211444",
                            "price" => "54900",
                            "offer_price" => "54900",
                            "quantity" => 1,
                            "name" => "Personalised Kids T-shirts",
                            "description" => "description"
                        ]
                    ]
                ];
    }
}