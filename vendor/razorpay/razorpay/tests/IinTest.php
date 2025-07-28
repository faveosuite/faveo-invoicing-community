<?php

namespace Razorpay\Tests;

use Razorpay\Api\Request;

class IinTest extends TestCase
{   

    public function testFetchIinAll()
    {
        $data = $this->api->iin->all();

        $this->assertTrue(is_array($data->toArray()));
    }
}