<?php

namespace Razorpay\Tests;

use Razorpay\Api\Request;

class CardTest extends TestCase
{
    /**
     * Specify unique card id
     */
    
    private $cardId = "card_LcQgzpfvWP0UKF";

    public function setUp(): void
    {
        parent::setUp();
    }
    
    /**
     * Fetch Card details
     */
    public function testFetchCard()
    {
        $data = $this->api->card->fetch($this->cardId);
        
        $this->assertTrue(in_array($this->cardId, $data->toArray()));
    }

}