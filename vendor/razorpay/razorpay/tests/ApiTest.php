<?php

namespace Razorpay\Tests;

use Razorpay\Api\Request;

class ApiTest extends TestCase
{
    
    private $title = "codecov_test";

    private $url = 'https://api.razorpay.com';

    public function setUp(): void
    {
        parent::setUp();
    }
    
    /**
     * Get app details
     */
    public function testGetAppDetails()
    {
        $this->api->setAppDetails($this->title);

        $data = $this->api->getAppsDetails();

        $this->assertTrue(is_array($data));

        $this->assertTrue($this->title==$data[0]['title']);
    }

    /**
     * Get app details
     */
    public function testSetBaseUrl()
    {
        $this->api->setBaseUrl($this->url);

        $data = $this->api->getBaseUrl();
 
        $this->assertTrue($this->url==$data);

    }

    public function testGetkey()
    {
        $data = $this->api->getKey();

        $this->assertTrue(strlen($data) > 0);
    }

    public function testGetSecret()
    {
        $data = $this->api->getSecret();
        $this->assertTrue(strlen($data) > 0);
    }

    public function testFullUrl()
    {
        $pattern = '/^(https?:\/\/)?([a-z0-9-]+\.)+[a-z]{2,}(\/.*)?$/i';
        $url = $this->api->getFullUrl($this->api->getBaseUrl()."orders","v1");
        $this->assertTrue(preg_match($pattern, $url, $matches)==true);      
    }

    /**
     * @covers \Request
     */
    public function testgetheader()
    {
        $data = Request::getHeaders();
        $this->assertTrue(is_array($data));
    }
}