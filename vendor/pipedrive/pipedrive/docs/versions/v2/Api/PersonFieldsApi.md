# Pipedrive\versions\v2\PersonFieldsApi

All URIs are relative to https://api.pipedrive.com/api/v2.

Method | HTTP request | Description
------------- | ------------- | -------------
[**addPersonField()**](PersonFieldsApi.md#addPersonField) | **POST** /personFields | Create one person field
[**addPersonFieldOptions()**](PersonFieldsApi.md#addPersonFieldOptions) | **POST** /personFields/{field_code}/options | Add person field options in bulk
[**deletePersonField()**](PersonFieldsApi.md#deletePersonField) | **DELETE** /personFields/{field_code} | Delete one person field
[**deletePersonFieldOptions()**](PersonFieldsApi.md#deletePersonFieldOptions) | **DELETE** /personFields/{field_code}/options | Delete person field options in bulk
[**getPersonField()**](PersonFieldsApi.md#getPersonField) | **GET** /personFields/{field_code} | Get one person field
[**getPersonFields()**](PersonFieldsApi.md#getPersonFields) | **GET** /personFields | Get all person fields
[**updatePersonField()**](PersonFieldsApi.md#updatePersonField) | **PATCH** /personFields/{field_code} | Update one person field
[**updatePersonFieldOptions()**](PersonFieldsApi.md#updatePersonFieldOptions) | **PATCH** /personFields/{field_code}/options | Update person field options in bulk


## `addPersonField()`

```php
addPersonField($create_person_field_request): \Pipedrive\versions\v2\Model\CreatePersonField
```

Create one person field

Creates a new person custom field.

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


$apiInstance = new Pipedrive\versions\v2\Api\PersonFieldsApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$create_person_field_request = new \Pipedrive\versions\v2\Model\CreatePersonFieldRequest(); // \Pipedrive\versions\v2\Model\CreatePersonFieldRequest

try {
    $result = $apiInstance->addPersonField($create_person_field_request);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling PersonFieldsApi->addPersonField: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **create_person_field_request** | [**\Pipedrive\versions\v2\Model\CreatePersonFieldRequest**](../Model/CreatePersonFieldRequest.md)|  |

### Return type

[**\Pipedrive\versions\v2\Model\CreatePersonField**](../Model/CreatePersonField.md)

### Authorization

[api_key](../README.md#api_key), [oauth2](../README.md#oauth2)

### HTTP request headers

- **Content-Type**: `application/json`
- **Accept**: `application/json`

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints)
[[Back to Model list]](../README.md#documentation-for-models)
[[Back to README]](../README.md)

## `addPersonFieldOptions()`

```php
addPersonFieldOptions($field_code, $request_body): \Pipedrive\versions\v2\Model\InlineResponse200
```

Add person field options in bulk

Adds new options to a person custom field that supports options (enum or set field types). This operation is atomic - all options are added or none are added. Returns only the newly added options.

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


$apiInstance = new Pipedrive\versions\v2\Api\PersonFieldsApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$field_code = 'field_code_example'; // string | The unique code identifying the field
$request_body = array(new \stdClass); // object[]

try {
    $result = $apiInstance->addPersonFieldOptions($field_code, $request_body);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling PersonFieldsApi->addPersonFieldOptions: ', $e->getMessage(), PHP_EOL;
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

## `deletePersonField()`

```php
deletePersonField($field_code): \Pipedrive\versions\v2\Model\DeletePersonField
```

Delete one person field

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


$apiInstance = new Pipedrive\versions\v2\Api\PersonFieldsApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$field_code = 'field_code_example'; // string | The unique code identifying the field

try {
    $result = $apiInstance->deletePersonField($field_code);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling PersonFieldsApi->deletePersonField: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **field_code** | **string**| The unique code identifying the field |

### Return type

[**\Pipedrive\versions\v2\Model\DeletePersonField**](../Model/DeletePersonField.md)

### Authorization

[api_key](../README.md#api_key), [oauth2](../README.md#oauth2)

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json`

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints)
[[Back to Model list]](../README.md#documentation-for-models)
[[Back to README]](../README.md)

## `deletePersonFieldOptions()`

```php
deletePersonFieldOptions($field_code, $request_body): \Pipedrive\versions\v2\Model\InlineResponse200
```

Delete person field options in bulk

Removes existing options from a person custom field. This operation is atomic and fails if any of the specified option IDs do not exist. Returns only the deleted options.

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


$apiInstance = new Pipedrive\versions\v2\Api\PersonFieldsApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$field_code = 'field_code_example'; // string | The unique code identifying the field
$request_body = array(new \stdClass); // object[]

try {
    $result = $apiInstance->deletePersonFieldOptions($field_code, $request_body);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling PersonFieldsApi->deletePersonFieldOptions: ', $e->getMessage(), PHP_EOL;
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

## `getPersonField()`

```php
getPersonField($field_code, $include_fields): \Pipedrive\versions\v2\Model\GetPersonField
```

Get one person field

Returns metadata about a specific person field.

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


$apiInstance = new Pipedrive\versions\v2\Api\PersonFieldsApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$field_code = 'field_code_example'; // string | The unique code identifying the field
$include_fields = 'include_fields_example'; // string | Optional comma separated string array of additional data namespaces to include in response

try {
    $result = $apiInstance->getPersonField($field_code, $include_fields);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling PersonFieldsApi->getPersonField: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **field_code** | **string**| The unique code identifying the field |
 **include_fields** | **string**| Optional comma separated string array of additional data namespaces to include in response | [optional]

### Return type

[**\Pipedrive\versions\v2\Model\GetPersonField**](../Model/GetPersonField.md)

### Authorization

[api_key](../README.md#api_key), [oauth2](../README.md#oauth2)

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json`

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints)
[[Back to Model list]](../README.md#documentation-for-models)
[[Back to README]](../README.md)

## `getPersonFields()`

```php
getPersonFields($include_fields, $limit, $cursor): \Pipedrive\versions\v2\Model\GetPersonFields
```

Get all person fields

Returns metadata about all person fields in the company.

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


$apiInstance = new Pipedrive\versions\v2\Api\PersonFieldsApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$include_fields = 'include_fields_example'; // string | Optional comma separated string array of additional data namespaces to include in response
$limit = 100; // int | For pagination, the limit of entries to be returned. If not provided, 100 items will be returned. Please note that a maximum value of 500 is allowed.
$cursor = 'cursor_example'; // string | For pagination, the marker (an opaque string value) representing the first item on the next page

try {
    $result = $apiInstance->getPersonFields($include_fields, $limit, $cursor);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling PersonFieldsApi->getPersonFields: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **include_fields** | **string**| Optional comma separated string array of additional data namespaces to include in response | [optional]
 **limit** | **int**| For pagination, the limit of entries to be returned. If not provided, 100 items will be returned. Please note that a maximum value of 500 is allowed. | [optional]
 **cursor** | **string**| For pagination, the marker (an opaque string value) representing the first item on the next page | [optional]

### Return type

[**\Pipedrive\versions\v2\Model\GetPersonFields**](../Model/GetPersonFields.md)

### Authorization

[api_key](../README.md#api_key), [oauth2](../README.md#oauth2)

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json`

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints)
[[Back to Model list]](../README.md#documentation-for-models)
[[Back to README]](../README.md)

## `updatePersonField()`

```php
updatePersonField($field_code, $update_person_field_request): \Pipedrive\versions\v2\Model\GetPersonField
```

Update one person field

Updates a person custom field. The field_code and field_type cannot be changed. At least one field must be provided in the request body.

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


$apiInstance = new Pipedrive\versions\v2\Api\PersonFieldsApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$field_code = 'field_code_example'; // string | The unique code identifying the field
$update_person_field_request = new \Pipedrive\versions\v2\Model\UpdatePersonFieldRequest(); // \Pipedrive\versions\v2\Model\UpdatePersonFieldRequest

try {
    $result = $apiInstance->updatePersonField($field_code, $update_person_field_request);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling PersonFieldsApi->updatePersonField: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **field_code** | **string**| The unique code identifying the field |
 **update_person_field_request** | [**\Pipedrive\versions\v2\Model\UpdatePersonFieldRequest**](../Model/UpdatePersonFieldRequest.md)|  |

### Return type

[**\Pipedrive\versions\v2\Model\GetPersonField**](../Model/GetPersonField.md)

### Authorization

[api_key](../README.md#api_key), [oauth2](../README.md#oauth2)

### HTTP request headers

- **Content-Type**: `application/json`
- **Accept**: `application/json`

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints)
[[Back to Model list]](../README.md#documentation-for-models)
[[Back to README]](../README.md)

## `updatePersonFieldOptions()`

```php
updatePersonFieldOptions($field_code, $request_body): \Pipedrive\versions\v2\Model\InlineResponse200
```

Update person field options in bulk

Updates existing options for a person custom field. This operation is atomic and fails if any of the specified option IDs do not exist. Returns only the updated options.

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


$apiInstance = new Pipedrive\versions\v2\Api\PersonFieldsApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$field_code = 'field_code_example'; // string | The unique code identifying the field
$request_body = array(new \stdClass); // object[]

try {
    $result = $apiInstance->updatePersonFieldOptions($field_code, $request_body);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling PersonFieldsApi->updatePersonFieldOptions: ', $e->getMessage(), PHP_EOL;
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
