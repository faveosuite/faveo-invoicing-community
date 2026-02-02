<?php

namespace Razorpay\Api;

use Razorpay\Api\Request;
use Razorpay\Api\OAuthValidator;

class OAuthClient 
{
    protected static $baseUrl = 'https://auth.razorpay.com';

    protected static $authorize = 'authorize';

    protected static $version = '';

    protected static $CLIENT_ID = 'client_id';
    protected static $CLIENT_SECRET = 'client_secret';
    protected static $REDIRECT_URI = 'redirect_uri';
    protected static $STATE = 'state';
    protected static $SCOPES = 'scopes';
    protected static $GRANT_TYPE = 'grant_type';
    protected static $REFRESH_TOKEN = 'refresh_token';
    protected static $TOKEN = "token";
    protected static $REVOKE = "revoke";
    protected static $TOKEN_TYPE_HINT = "token_type_hint";

    private $request;

    public function __construct(){
       $this->request = new Request(Request::$OAUTH);
    }

    function getAuthURL(array $request) { 
        $validator = new OAuthValidator($request, $this->getauthURLRule());
        $validator->validateOrFail();

        $clientId = $request['client_id'];
        $redirectUri = $request['redirect_uri'];
        $state = $request['state'];
        $scopes = $request['scopes'];
    
        $queryParams = [
          'response_type' => 'code',
          'client_id' => $clientId,
          'redirect_uri' => $redirectUri,
          'state' => $state
        ];
    
        $scopesParam = null;
        foreach ($scopes as $scope) {
            $scopesParam .= "scope[]=$scope&";
        }

        if (!empty($request['onboarding_signature'])) {
            $queryParams['onboarding_signature'] = $request['onboarding_signature'];
        }

        $authUrl = self::$baseUrl . "/" . self::$authorize . "?" . $scopesParam . http_build_query($queryParams);
        return $authUrl;
    }
    
    public function getAccessToken(array $data){
        $validator = new OAuthValidator($data, $this->getAccessTokenRule());
        $validator->validateOrFail();
        return $this->request->request('POST', self::$TOKEN, $data, self::$version);
    }

    public function getRefreshToken(array $data){
        $validator = new OAuthValidator($data, $this->getRefreshTokenRule());
        $validator->validateOrFail();
        return $this->request->request('POST', self::$TOKEN, $data, self::$version);
    }

    public function revokeToken(array $data){
        $validator = new OAuthValidator($data, $this->revokeTokenRule());
        $validator->validateOrFail();
        return $this->request->request('POST', self::$REVOKE, $data, self::$version);
    }

    protected function getauthURLRule(){
        return [
            self::$CLIENT_ID => 'required|id',
            self::$REDIRECT_URI => 'required|url',
            self::$SCOPES => 'required',
            self::$STATE => 'required'
        ];
    }

    protected function getAccessTokenRule(){
        return [
            self::$CLIENT_ID => 'required|id',
            self::$CLIENT_SECRET => 'required',
            self::$REDIRECT_URI => 'required|url',
            self::$GRANT_TYPE => 'required'
        ];
    }

    protected function getRefreshTokenRule(){
        return [
            self::$CLIENT_ID => 'required|id',
            self::$CLIENT_SECRET => 'required',
            self::$REFRESH_TOKEN => 'required'
        ];
    }

    protected function revokeTokenRule(){
        return [
            self::$CLIENT_ID => 'required|id',
            self::$CLIENT_SECRET => 'required',
            self::$TOKEN_TYPE_HINT => 'required|token_type',
            self::$TOKEN => 'required'
        ];
    }
}