# Pipedrive\versions\v1\ActivityTypesApi

All URIs are relative to https://api.pipedrive.com/v1.

Method | HTTP request | Description
------------- | ------------- | -------------
[**addActivityType()**](ActivityTypesApi.md#addActivityType) | **POST** /activityTypes | Add new activity type
[**deleteActivityType()**](ActivityTypesApi.md#deleteActivityType) | **DELETE** /activityTypes/{id} | Delete an activity type
[**deleteActivityTypes()**](ActivityTypesApi.md#deleteActivityTypes) | **DELETE** /activityTypes | Delete multiple activity types in bulk
[**getActivityTypes()**](ActivityTypesApi.md#getActivityTypes) | **GET** /activityTypes | Get all activity types
[**updateActivityType()**](ActivityTypesApi.md#updateActivityType) | **PUT** /activityTypes/{id} | Update an activity type


## `addActivityType()`

```php
addActivityType($activity_type_create_request): \Pipedrive\versions\v1\Model\ActivityTypeCreateUpdateDeleteResponse
```

Add new activity type

Adds a new activity type.

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');


// Configure API key authorization: api_key
$config = (new Pipedrive\versions\v1\Configuration())->setApiKey('x-api-token', 'YOUR_API_KEY');
// Uncomment below to setup prefix (e.g. Bearer) for API key, if needed
// $config = (new Pipedrive\versions\v1\Configuration())->setApiKeyPrefix('x-api-token', 'Bearer');

// Configure OAuth2 access token for authorization: oauth2
$config = (new Pipedrive\versions\v1\Configuration())->setAccessToken('YOUR_ACCESS_TOKEN');


$apiInstance = new Pipedrive\versions\v1\Api\ActivityTypesApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$activity_type_create_request = new \Pipedrive\versions\v1\Model\ActivityTypeCreateRequest(); // \Pipedrive\versions\v1\Model\ActivityTypeCreateRequest

try {
    $result = $apiInstance->addActivityType($activity_type_create_request);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling ActivityTypesApi->addActivityType: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **activity_type_create_request** | [**\Pipedrive\versions\v1\Model\ActivityTypeCreateRequest**](../Model/ActivityTypeCreateRequest.md)|  | [optional]

### Return type

[**\Pipedrive\versions\v1\Model\ActivityTypeCreateUpdateDeleteResponse**](../Model/ActivityTypeCreateUpdateDeleteResponse.md)

### Authorization

[api_key](../README.md#api_key), [oauth2](../README.md#oauth2)

### HTTP request headers

- **Content-Type**: `application/json`
- **Accept**: `application/json`

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints)
[[Back to Model list]](../README.md#documentation-for-models)
[[Back to README]](../README.md)

## `deleteActivityType()`

```php
deleteActivityType($id): \Pipedrive\versions\v1\Model\ActivityTypeCreateUpdateDeleteResponse
```

Delete an activity type

Marks an activity type as deleted.

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');


// Configure API key authorization: api_key
$config = (new Pipedrive\versions\v1\Configuration())->setApiKey('x-api-token', 'YOUR_API_KEY');
// Uncomment below to setup prefix (e.g. Bearer) for API key, if needed
// $config = (new Pipedrive\versions\v1\Configuration())->setApiKeyPrefix('x-api-token', 'Bearer');

// Configure OAuth2 access token for authorization: oauth2
$config = (new Pipedrive\versions\v1\Configuration())->setAccessToken('YOUR_ACCESS_TOKEN');


$apiInstance = new Pipedrive\versions\v1\Api\ActivityTypesApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$id = 56; // int | The ID of the activity type

try {
    $result = $apiInstance->deleteActivityType($id);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling ActivityTypesApi->deleteActivityType: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **id** | **int**| The ID of the activity type |

### Return type

[**\Pipedrive\versions\v1\Model\ActivityTypeCreateUpdateDeleteResponse**](../Model/ActivityTypeCreateUpdateDeleteResponse.md)

### Authorization

[api_key](../README.md#api_key), [oauth2](../README.md#oauth2)

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json`

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints)
[[Back to Model list]](../README.md#documentation-for-models)
[[Back to README]](../README.md)

## `deleteActivityTypes()`

```php
deleteActivityTypes($ids): \Pipedrive\versions\v1\Model\ActivityTypeBulkDeleteResponse
```

Delete multiple activity types in bulk

Marks multiple activity types as deleted.

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');


// Configure API key authorization: api_key
$config = (new Pipedrive\versions\v1\Configuration())->setApiKey('x-api-token', 'YOUR_API_KEY');
// Uncomment below to setup prefix (e.g. Bearer) for API key, if needed
// $config = (new Pipedrive\versions\v1\Configuration())->setApiKeyPrefix('x-api-token', 'Bearer');

// Configure OAuth2 access token for authorization: oauth2
$config = (new Pipedrive\versions\v1\Configuration())->setAccessToken('YOUR_ACCESS_TOKEN');


$apiInstance = new Pipedrive\versions\v1\Api\ActivityTypesApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$ids = 'ids_example'; // string | The comma-separated activity type IDs

try {
    $result = $apiInstance->deleteActivityTypes($ids);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling ActivityTypesApi->deleteActivityTypes: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **ids** | **string**| The comma-separated activity type IDs |

### Return type

[**\Pipedrive\versions\v1\Model\ActivityTypeBulkDeleteResponse**](../Model/ActivityTypeBulkDeleteResponse.md)

### Authorization

[api_key](../README.md#api_key), [oauth2](../README.md#oauth2)

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json`

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints)
[[Back to Model list]](../README.md#documentation-for-models)
[[Back to README]](../README.md)

## `getActivityTypes()`

```php
getActivityTypes(): \Pipedrive\versions\v1\Model\ActivityTypeListResponse
```

Get all activity types

Returns all activity types.

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');


// Configure API key authorization: api_key
$config = (new Pipedrive\versions\v1\Configuration())->setApiKey('x-api-token', 'YOUR_API_KEY');
// Uncomment below to setup prefix (e.g. Bearer) for API key, if needed
// $config = (new Pipedrive\versions\v1\Configuration())->setApiKeyPrefix('x-api-token', 'Bearer');

// Configure OAuth2 access token for authorization: oauth2
$config = (new Pipedrive\versions\v1\Configuration())->setAccessToken('YOUR_ACCESS_TOKEN');


$apiInstance = new Pipedrive\versions\v1\Api\ActivityTypesApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);

try {
    $result = $apiInstance->getActivityTypes();
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling ActivityTypesApi->getActivityTypes: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

This endpoint does not need any parameter.

### Return type

[**\Pipedrive\versions\v1\Model\ActivityTypeListResponse**](../Model/ActivityTypeListResponse.md)

### Authorization

[api_key](../README.md#api_key), [oauth2](../README.md#oauth2)

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json`

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints)
[[Back to Model list]](../README.md#documentation-for-models)
[[Back to README]](../README.md)

## `updateActivityType()`

```php
updateActivityType($id, $activity_type_update_request): \Pipedrive\versions\v1\Model\ActivityTypeCreateUpdateDeleteResponse
```

Update an activity type

Updates an activity type.

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');


// Configure API key authorization: api_key
$config = (new Pipedrive\versions\v1\Configuration())->setApiKey('x-api-token', 'YOUR_API_KEY');
// Uncomment below to setup prefix (e.g. Bearer) for API key, if needed
// $config = (new Pipedrive\versions\v1\Configuration())->setApiKeyPrefix('x-api-token', 'Bearer');

// Configure OAuth2 access token for authorization: oauth2
$config = (new Pipedrive\versions\v1\Configuration())->setAccessToken('YOUR_ACCESS_TOKEN');


$apiInstance = new Pipedrive\versions\v1\Api\ActivityTypesApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$id = 56; // int | The ID of the activity type
$activity_type_update_request = new \Pipedrive\versions\v1\Model\ActivityTypeUpdateRequest(); // \Pipedrive\versions\v1\Model\ActivityTypeUpdateRequest

try {
    $result = $apiInstance->updateActivityType($id, $activity_type_update_request);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling ActivityTypesApi->updateActivityType: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **id** | **int**| The ID of the activity type |
 **activity_type_update_request** | [**\Pipedrive\versions\v1\Model\ActivityTypeUpdateRequest**](../Model/ActivityTypeUpdateRequest.md)|  | [optional]

### Return type

[**\Pipedrive\versions\v1\Model\ActivityTypeCreateUpdateDeleteResponse**](../Model/ActivityTypeCreateUpdateDeleteResponse.md)

### Authorization

[api_key](../README.md#api_key), [oauth2](../README.md#oauth2)

### HTTP request headers

- **Content-Type**: `application/json`
- **Accept**: `application/json`

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints)
[[Back to Model list]](../README.md#documentation-for-models)
[[Back to README]](../README.md)
