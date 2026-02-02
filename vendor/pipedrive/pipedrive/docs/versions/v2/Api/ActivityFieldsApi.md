# Pipedrive\versions\v2\ActivityFieldsApi

All URIs are relative to https://api.pipedrive.com/api/v2.

Method | HTTP request | Description
------------- | ------------- | -------------
[**getActivityField()**](ActivityFieldsApi.md#getActivityField) | **GET** /activityFields/{field_code} | Get one activity field
[**getActivityFields()**](ActivityFieldsApi.md#getActivityFields) | **GET** /activityFields | Get all activity fields


## `getActivityField()`

```php
getActivityField($field_code, $include_fields): \Pipedrive\versions\v2\Model\GetActivityField
```

Get one activity field

Returns metadata about a specific activity field.

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');


// Configure API key authorization: api_key
$config = (new Pipedrive\versions\v2\Configuration())->setApiKey('x-api-token', 'YOUR_API_KEY');
// Uncomment below to setup prefix (e.g. Bearer) for API key, if needed
// $config = (new Pipedrive\versions\v2\Configuration())->setApiKeyPrefix('x-api-token', 'Bearer');

// Configure OAuth2 access token for authorization: oauth2
$config = (new Pipedrive\versions\v2\Configuration())->setAccessToken('YOUR_ACCESS_TOKEN');


$apiInstance = new Pipedrive\versions\v2\Api\ActivityFieldsApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$field_code = 'field_code_example'; // string | The unique code identifying the field
$include_fields = 'include_fields_example'; // string | Optional comma separated string array of additional data namespaces to include in response

try {
    $result = $apiInstance->getActivityField($field_code, $include_fields);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling ActivityFieldsApi->getActivityField: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **field_code** | **string**| The unique code identifying the field |
 **include_fields** | **string**| Optional comma separated string array of additional data namespaces to include in response | [optional]

### Return type

[**\Pipedrive\versions\v2\Model\GetActivityField**](../Model/GetActivityField.md)

### Authorization

[api_key](../README.md#api_key), [oauth2](../README.md#oauth2)

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json`

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints)
[[Back to Model list]](../README.md#documentation-for-models)
[[Back to README]](../README.md)

## `getActivityFields()`

```php
getActivityFields($include_fields, $limit, $cursor): \Pipedrive\versions\v2\Model\GetActivityFields
```

Get all activity fields

Returns metadata about all activity fields in the company.

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');


// Configure API key authorization: api_key
$config = (new Pipedrive\versions\v2\Configuration())->setApiKey('x-api-token', 'YOUR_API_KEY');
// Uncomment below to setup prefix (e.g. Bearer) for API key, if needed
// $config = (new Pipedrive\versions\v2\Configuration())->setApiKeyPrefix('x-api-token', 'Bearer');

// Configure OAuth2 access token for authorization: oauth2
$config = (new Pipedrive\versions\v2\Configuration())->setAccessToken('YOUR_ACCESS_TOKEN');


$apiInstance = new Pipedrive\versions\v2\Api\ActivityFieldsApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$include_fields = 'include_fields_example'; // string | Optional comma separated string array of additional data namespaces to include in response
$limit = 100; // int | For pagination, the limit of entries to be returned. If not provided, 100 items will be returned. Please note that a maximum value of 500 is allowed.
$cursor = 'cursor_example'; // string | For pagination, the marker (an opaque string value) representing the first item on the next page

try {
    $result = $apiInstance->getActivityFields($include_fields, $limit, $cursor);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling ActivityFieldsApi->getActivityFields: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **include_fields** | **string**| Optional comma separated string array of additional data namespaces to include in response | [optional]
 **limit** | **int**| For pagination, the limit of entries to be returned. If not provided, 100 items will be returned. Please note that a maximum value of 500 is allowed. | [optional]
 **cursor** | **string**| For pagination, the marker (an opaque string value) representing the first item on the next page | [optional]

### Return type

[**\Pipedrive\versions\v2\Model\GetActivityFields**](../Model/GetActivityFields.md)

### Authorization

[api_key](../README.md#api_key), [oauth2](../README.md#oauth2)

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json`

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints)
[[Back to Model list]](../README.md#documentation-for-models)
[[Back to README]](../README.md)
