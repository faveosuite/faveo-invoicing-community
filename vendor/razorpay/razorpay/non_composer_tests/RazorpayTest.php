<?php

namespace Razorpay\Api\Test;

include 'Razorpay.php';

use Razorpay\Api\Api;

class RazorpayTest extends \PHPUnit_Framework_TestCase
{
    function setUp()
    {
        $this->api = new Api($_SERVER['KEY_ID'], $_SERVER['KEY_SECRET']);
    }

    public function testApiAccess()
    {
		$this->assertInstanceOf('Razorpay\Api\Api', $this->api);
	}

    public function testRequests()
	{
		$this->assertTrue(class_exists('\Requests'));
	}
}