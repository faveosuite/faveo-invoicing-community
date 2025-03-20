# Pipedrive\versions\v1\MeetingsApi

All URIs are relative to https://api.pipedrive.com/v1.

Method | HTTP request | Description
------------- | ------------- | -------------
[**deleteUserProviderLink()**](MeetingsApi.md#deleteUserProviderLink) | **DELETE** /meetings/userProviderLinks/{id} | Delete the link between a user and the installed video call integration
[**saveUserProviderLink()**](MeetingsApi.md#saveUserProviderLink) | **POST** /meetings/userProviderLinks | Link a user with the installed video call integration


## `deleteUserProviderLink()`

```php
deleteUserProviderLink($id): \Pipedrive\versions\v1\Model\UserProviderLinkSuccessResponse
```

Delete the link between a user and the installed video call integration

A video calling provider must call this endpoint to remove the link between a user and the installed video calling app.

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');


// Configure API key authorization: api_key
$config = (new Pipedrive\versions\v1\Configuration())->setApiKey('api_token', 'YOUR_API_KEY');
// Uncomment below to setup prefix (e.g. Bearer) for API key, if needed
// $config = (new Pipedrive\versions\v1\Configuration())->setApiKeyPrefix('api_token', 'Bearer');

// Configure OAuth2 access token for authorization: oauth2
$config = (new Pipedrive\versions\v1\Configuration())->setAccessToken('YOUR_ACCESS_TOKEN');


$apiInstance = new Pipedrive\versions\v1\Api\MeetingsApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$id = 'id_example'; // string | Unique identifier linking a user to the installed integration

try {
    $result = $apiInstance->deleteUserProviderLink($id);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling MeetingsApi->deleteUserProviderLink: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **id** | **string**| Unique identifier linking a user to the installed integration |

### Return type

[**\Pipedrive\versions\v1\Model\UserProviderLinkSuccessResponse**](../Model/UserProviderLinkSuccessResponse.md)

### Authorization

[api_key](../README.md#api_key), [oauth2](../README.md#oauth2)

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json`

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints)
[[Back to Model list]](../README.md#documentation-for-models)
[[Back to README]](../README.md)

## `saveUserProviderLink()`

```php
saveUserProviderLink($user_provider_link_create_request): \Pipedrive\versions\v1\Model\UserProviderLinkSuccessResponse
```

Link a user with the installed video call integration

A video calling provider must call this endpoint after a user has installed the video calling app so that the new user's information is sent.

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');


// Configure API key authorization: api_key
$config = (new Pipedrive\versions\v1\Configuration())->setApiKey('api_token', 'YOUR_API_KEY');
// Uncomment below to setup prefix (e.g. Bearer) for API key, if needed
// $config = (new Pipedrive\versions\v1\Configuration())->setApiKeyPrefix('api_token', 'Bearer');

// Configure OAuth2 access token for authorization: oauth2
$config = (new Pipedrive\versions\v1\Configuration())->setAccessToken('YOUR_ACCESS_TOKEN');


$apiInstance = new Pipedrive\versions\v1\Api\MeetingsApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$user_provider_link_create_request = new \Pipedrive\versions\v1\Model\UserProviderLinkCreateRequest(); // \Pipedrive\versions\v1\Model\UserProviderLinkCreateRequest

try {
    $result = $apiInstance->saveUserProviderLink($user_provider_link_create_request);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling MeetingsApi->saveUserProviderLink: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **user_provider_link_create_request** | [**\Pipedrive\versions\v1\Model\UserProviderLinkCreateRequest**](../Model/UserProviderLinkCreateRequest.md)|  | [optional]

### Return type

[**\Pipedrive\versions\v1\Model\UserProviderLinkSuccessResponse**](../Model/UserProviderLinkSuccessResponse.md)

### Authorization

[api_key](../README.md#api_key), [oauth2](../README.md#oauth2)

### HTTP request headers

- **Content-Type**: `application/json`
- **Accept**: `application/json`

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints)
[[Back to Model list]](../README.md#documentation-for-models)
[[Back to README]](../README.md)
