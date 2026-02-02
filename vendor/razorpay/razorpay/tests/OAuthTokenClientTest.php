<?php

use PHPUnit\Framework\TestCase;
use Razorpay\Api\OAuthClient;
use Razorpay\Api\Request;
use Razorpay\Api\Errors\BadRequestError;
use Razorpay\Api\Api;


class OAuthTokenClientTest extends TestCase
{

    private $mockRequest;
    private $mockValidator;
    private $oauthClient;
    private $clientId = 'MxgghoicPe02tM';

    protected function setUp(): void
    {
        $this->mockRequest = $this->createMock(Request::class);
        $this->oauthClient = new OAuthClient();

        // Use Reflection to override private $request property
        $reflection = new \ReflectionClass($this->oauthClient);
        $property = $reflection->getProperty('request');
        $property->setAccessible(true);
        $property->setValue($this->oauthClient, $this->mockRequest);

    }

    public function testGetAccessTokenExecutesWithMockedResponse(){

        $fakeResponse = [
            "public_token" => "rzp_test_oauth_NX72KaLNHaFTC4", 
            "razorpay_account_id" => "<account_id>", 
            "token_type" => "Bearer", 
            "expires_in" => 7775997, 
            "access_token" => "eyJ0eXAiOiJKV1QiLCJhbGciOiJFUzI1NiJ9.eyJhdWQiOiJLdVRiTW" 
         ];

        $this->mockRequest->method('request')->willReturn($fakeResponse);

        $response = $this->oauthClient->getAccessToken([
            "client_id" => $this->clientId,
            "client_secret" => "eZ3LK9trPs4l0bnpTiHx7r3G",
            "grant_type" => "authorization_code",
            "redirect_uri" => "http://localhost",
            "code" => "def50200bff542c1e31d53dd78e5d490ad6b6a2d03b34500ff5f768faff098a",
            "mode" => "test"
        ]);

        $this->assertEquals('rzp_test_oauth_NX72KaLNHaFTC4', $response['public_token']);
        $this->assertEquals('Bearer', $response['token_type']);
    }

    public function testGetAccessTokenValidationFailure(){
        $this->expectException(BadRequestError::class);
        $this->expectExceptionMessage("The redirect_uri is not valid");
    
        $response = $this->oauthClient->getAccessToken([
            "client_id" => $this->clientId,  
            "client_secret" => "eZ3LK9trPs4l0bnpTiHx7r3G",
            "redirect_uri" => "http//example.com"
        ]);      
    }

    public function testGetRefreshTokenExecutesWithMockedResponse(){

        $fakeResponse = [
            "public_token" => "rzp_test_oauth_NX72KaLNHaFTC4", 
            "token_type" => "Bearer", 
            "expires_in" => 7862400, 
            "access_token" => "eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiIsImp0aSI6Ijl4dTF", 
            "refresh_token" => "def5020096e1c470c901d34cd60fa53abdaf36620e823ffa53" 
        ]; 

        $this->mockRequest->method('request')->willReturn($fakeResponse);

        $response = $this->oauthClient->getRefreshToken([
            "client_id" => $this->clientId,  
            "client_secret" => "eZ3LK9trPs4l0bnpTiHx7r3G",
            "grant_type" => "refresh_token",
            "refresh_token" => "eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiIsImp0aSI6Ijl4dTF",
        ]);

        $this->assertEquals('rzp_test_oauth_NX72KaLNHaFTC4', $response['public_token']);
        $this->assertEquals('Bearer', $response['token_type']);
    }

    public function testRevokeTokenExecutesWithMockedResponse(){

        $fakeResponse = [
            "message" => "Token Revoked", 
        ]; 

        $this->mockRequest->method('request')->willReturn($fakeResponse);

        $response = $this->oauthClient->revokeToken([
            "client_id" => $this->clientId,  
            "client_secret" => "eZ3LK9trPs4l0bnpTiHx7r3G",
            "token" => "eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9.",
            "token_type_hint" => "access_token"
        ]);

        $this->assertIsArray($response);
    }

    public function testRevokeTokenExecutesValidationFailure(){
        $this->expectException(BadRequestError::class);
        $this->expectExceptionMessage('The client_id field is required.');
    
        $response = $this->oauthClient->revokeToken([
            "client_id" => "",  
            "client_secret" => "eZ3LK9trPs4l0bnpTiHx7r3G",
            "token" => "eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9.",
            "token_type_hint" => "access_token"
        ]);      
    }

    public function testGenerateOnboardingSignature(){
        // encrypted from Java sdk;
        $expectedSignature = "37ad80c568a44f6999aa8f80bb5080dbc50eed353d325cb94d624bf82a9a36d12e4fd00490bc06271e06628c889c6b1c2a48e2f355f8598210d1b1c8c1c42dfcd02502f1515294028fd4";
        $api = new Api("key", "secret");
        $secret = "mzhK9zRdA2QoLxhlSR6Pg721";
        $attributes = [
            "submerchant_id" => "avaBWdazt7LoYu",
            "timestamp" => 1741098479 
        ];
        $actualSignature = $api->utility->generateOnboardingSignature($attributes, $secret);
        $this->assertEquals($expectedSignature, $actualSignature);    
    }
}
