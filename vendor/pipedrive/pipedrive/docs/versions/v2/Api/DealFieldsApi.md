# Pipedrive\versions\v2\DealFieldsApi

All URIs are relative to https://api.pipedrive.com/api/v2.

Method | HTTP request | Description
------------- | ------------- | -------------
[**addDealField()**](DealFieldsApi.md#addDealField) | **POST** /dealFields | Create one deal field
[**addDealFieldOptions()**](DealFieldsApi.md#addDealFieldOptions) | **POST** /dealFields/{field_code}/options | Add deal field options in bulk
[**deleteDealField()**](DealFieldsApi.md#deleteDealField) | **DELETE** /dealFields/{field_code} | Delete one deal field
[**deleteDealFieldOptions()**](DealFieldsApi.md#deleteDealFieldOptions) | **DELETE** /dealFields/{field_code}/options | Delete deal field options in bulk
[**getDealField()**](DealFieldsApi.md#getDealField) | **GET** /dealFields/{field_code} | Get one deal field
[**getDealFields()**](DealFieldsApi.md#getDealFields) | **GET** /dealFields | Get all deal fields
[**updateDealField()**](DealFieldsApi.md#updateDealField) | **PATCH** /dealFields/{field_code} | Update one deal field
[**updateDealFieldOptions()**](DealFieldsApi.md#updateDealFieldOptions) | **PATCH** /dealFields/{field_code}/options | Update deal field options in bulk


## `addDealField()`

```php
addDealField($create_deal_field_request): \Pipedrive\versions\v2\Model\CreateDealField
```

Create one deal field

Creates a new deal custom field.

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


$apiInstance = new Pipedrive\versions\v2\Api\DealFieldsApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$create_deal_field_request = new \Pipedrive\versions\v2\Model\CreateDealFieldRequest(); // \Pipedrive\versions\v2\Model\CreateDealFieldRequest

try {
    $result = $apiInstance->addDealField($create_deal_field_request);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling DealFieldsApi->addDealField: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **create_deal_field_request** | [**\Pipedrive\versions\v2\Model\CreateDealFieldRequest**](../Model/CreateDealFieldRequest.md)|  |

### Return type

[**\Pipedrive\versions\v2\Model\CreateDealField**](../Model/CreateDealField.md)

### Authorization

[api_key](../README.md#api_key), [oauth2](../README.md#oauth2)

### HTTP request headers

- **Content-Type**: `application/json`
- **Accept**: `application/json`

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints)
[[Back to Model list]](../README.md#documentation-for-models)
[[Back to README]](../README.md)

## `addDealFieldOptions()`

```php
addDealFieldOptions($field_code, $request_body): \Pipedrive\versions\v2\Model\InlineResponse200
```

Add deal field options in bulk

Adds new options to a deal custom field that supports options (enum or set field types). This operation is atomic - all options are added or none are added. Returns only the newly added options.

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


$apiInstance = new Pipedrive\versions\v2\Api\DealFieldsApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$field_code = 'field_code_example'; // string | The unique code identifying the field
$request_body = array(new \stdClass); // object[]

try {
    $result = $apiInstance->addDealFieldOptions($field_code, $request_body);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling DealFieldsApi->addDealFieldOptions: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **field_code** | **string**| The unique code identifying the field |
 **request_body** | [**object[]**](../Model/object.md)|  |

### Return type

[**\Pipedrive\versions\v2\Model\InlineResponse200**](../Model/InlineResponse200.md)

### Authorization

[api_key](../README.md#api_key), [oauth2](../README.md#oauth2)

### HTTP request headers

- **Content-Type**: `application/json`
- **Accept**: `application/json`

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints)
[[Back to Model list]](../README.md#documentation-for-models)
[[Back to README]](../README.md)

## `deleteDealField()`

```php
deleteDealField($field_code): \Pipedrive\versions\v2\Model\DeleteDealField
```

Delete one deal field

Marks a custom field as deleted.

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


$apiInstance = new Pipedrive\versions\v2\Api\DealFieldsApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$field_code = 'field_code_example'; // string | The unique code identifying the field

try {
    $result = $apiInstance->deleteDealField($field_code);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling DealFieldsApi->deleteDealField: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **field_code** | **string**| The unique code identifying the field |

### Return type

[**\Pipedrive\versions\v2\Model\DeleteDealField**](../Model/DeleteDealField.md)

### Authorization

[api_key](../README.md#api_key), [oauth2](../README.md#oauth2)

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json`

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints)
[[Back to Model list]](../README.md#documentation-for-models)
[[Back to README]](../README.md)

## `deleteDealFieldOptions()`

```php
deleteDealFieldOptions($field_code, $request_body): \Pipedrive\versions\v2\Model\InlineResponse200
```

Delete deal field options in bulk

Removes existing options from a deal custom field. This operation is atomic and fails if any of the specified option IDs do not exist. Returns only the deleted options.

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


$apiInstance = new Pipedrive\versions\v2\Api\DealFieldsApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$field_code = 'field_code_example'; // string | The unique code identifying the field
$request_body = array(new \stdClass); // object[]

try {
    $result = $apiInstance->deleteDealFieldOptions($field_code, $request_body);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling DealFieldsApi->deleteDealFieldOptions: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **field_code** | **string**| The unique code identifying the field |
 **request_body** | [**object[]**](../Model/object.md)|  |

### Return type

[**\Pipedrive\versions\v2\Model\InlineResponse200**](../Model/InlineResponse200.md)

### Authorization

[api_key](../README.md#api_key), [oauth2](../README.md#oauth2)

### HTTP request headers

- **Content-Type**: `application/json`
- **Accept**: `application/json`

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints)
[[Back to Model list]](../README.md#documentation-for-models)
[[Back to README]](../README.md)

## `getDealField()`

```php
getDealField($field_code, $include_fields): \Pipedrive\versions\v2\Model\GetDealField
```

Get one deal field

Returns metadata about a specific deal field.

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


$apiInstance = new Pipedrive\versions\v2\Api\DealFieldsApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$field_code = 'field_code_example'; // string | The unique code identifying the field
$include_fields = 'include_fields_example'; // string | Optional comma separated string array of additional data namespaces to include in response

try {
    $result = $apiInstance->getDealField($field_code, $include_fields);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling DealFieldsApi->getDealField: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **field_code** | **string**| The unique code identifying the field |
 **include_fields** | **string**| Optional comma separated string array of additional data namespaces to include in response | [optional]

### Return type

[**\Pipedrive\versions\v2\Model\GetDealField**](../Model/GetDealField.md)

### Authorization

[api_key](../README.md#api_key), [oauth2](../README.md#oauth2)

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json`

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints)
[[Back to Model list]](../README.md#documentation-for-models)
[[Back to README]](../README.md)

## `getDealFields()`

```php
getDealFields($include_fields, $limit, $cursor): \Pipedrive\versions\v2\Model\GetDealFields
```

Get all deal fields

Returns metadata about all deal fields in the company.

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


$apiInstance = new Pipedrive\versions\v2\Api\DealFieldsApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$include_fields = 'include_fields_example'; // string | Optional comma separated string array of additional data namespaces to include in response
$limit = 100; // int | For pagination, the limit of entries to be returned. If not provided, 100 items will be returned. Please note that a maximum value of 500 is allowed.
$cursor = 'cursor_example'; // string | For pagination, the marker (an opaque string value) representing the first item on the next page

try {
    $result = $apiInstance->getDealFields($include_fields, $limit, $cursor);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling DealFieldsApi->getDealFields: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **include_fields** | **string**| Optional comma separated string array of additional data namespaces to include in response | [optional]
 **limit** | **int**| For pagination, the limit of entries to be returned. If not provided, 100 items will be returned. Please note that a maximum value of 500 is allowed. | [optional]
 **cursor** | **string**| For pagination, the marker (an opaque string value) representing the first item on the next page | [optional]

### Return type

[**\Pipedrive\versions\v2\Model\GetDealFields**](../Model/GetDealFields.md)

### Authorization

[api_key](../README.md#api_key), [oauth2](../README.md#oauth2)

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json`

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints)
[[Back to Model list]](../README.md#documentation-for-models)
[[Back to README]](../README.md)

## `updateDealField()`

```php
updateDealField($field_code, $update_deal_field_request): \Pipedrive\versions\v2\Model\GetDealField
```

Update one deal field

Updates a deal custom field. The field_code and field_type cannot be changed. At least one field must be provided in the request body.

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


$apiInstance = new Pipedrive\versions\v2\Api\DealFieldsApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$field_code = 'field_code_example'; // string | The unique code identifying the field
$update_deal_field_request = new \Pipedrive\versions\v2\Model\UpdateDealFieldRequest(); // \Pipedrive\versions\v2\Model\UpdateDealFieldRequest

try {
    $result = $apiInstance->updateDealField($field_code, $update_deal_field_request);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling DealFieldsApi->updateDealField: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **field_code** | **string**| The unique code identifying the field |
 **update_deal_field_request** | [**\Pipedrive\versions\v2\Model\UpdateDealFieldRequest**](../Model/UpdateDealFieldRequest.md)|  |

### Return type

[**\Pipedrive\versions\v2\Model\GetDealField**](../Model/GetDealField.md)

### Authorization

[api_key](../README.md#api_key), [oauth2](../README.md#oauth2)

### HTTP request headers

- **Content-Type**: `application/json`
- **Accept**: `application/json`

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints)
[[Back to Model list]](../README.md#documentation-for-models)
[[Back to README]](../README.md)

## `updateDealFieldOptions()`

```php
updateDealFieldOptions($field_code, $request_body): \Pipedrive\versions\v2\Model\InlineResponse200
```

Update deal field options in bulk

Updates existing options for a deal custom field. This operation is atomic and fails if any of the specified option IDs do not exist. Returns only the updated options.

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


$apiInstance = new Pipedrive\versions\v2\Api\DealFieldsApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$field_code = 'field_code_example'; // string | The unique code identifying the field
$request_body = array(new \stdClass); // object[]

try {
    $result = $apiInstance->updateDealFieldOptions($field_code, $request_body);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling DealFieldsApi->updateDealFieldOptions: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **field_code** | **string**| The unique code identifying the field |
 **request_body** | [**object[]**](../Model/object.md)|  |

### Return type

[**\Pipedrive\versions\v2\Model\InlineResponse200**](../Model/InlineResponse200.md)

### Authorization

[api_key](../README.md#api_key), [oauth2](../README.md#oauth2)

### HTTP request headers

- **Content-Type**: `application/json`
- **Accept**: `application/json`

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints)
[[Back to Model list]](../README.md#documentation-for-models)
[[Back to README]](../README.md)
