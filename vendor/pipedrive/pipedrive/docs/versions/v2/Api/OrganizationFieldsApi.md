# Pipedrive\versions\v2\OrganizationFieldsApi

All URIs are relative to https://api.pipedrive.com/api/v2.

Method | HTTP request | Description
------------- | ------------- | -------------
[**addOrganizationField()**](OrganizationFieldsApi.md#addOrganizationField) | **POST** /organizationFields | Create one organization field
[**addOrganizationFieldOptions()**](OrganizationFieldsApi.md#addOrganizationFieldOptions) | **POST** /organizationFields/{field_code}/options | Add organization field options in bulk
[**deleteOrganizationField()**](OrganizationFieldsApi.md#deleteOrganizationField) | **DELETE** /organizationFields/{field_code} | Delete one organization field
[**deleteOrganizationFieldOptions()**](OrganizationFieldsApi.md#deleteOrganizationFieldOptions) | **DELETE** /organizationFields/{field_code}/options | Delete organization field options in bulk
[**getOrganizationField()**](OrganizationFieldsApi.md#getOrganizationField) | **GET** /organizationFields/{field_code} | Get one organization field
[**getOrganizationFields()**](OrganizationFieldsApi.md#getOrganizationFields) | **GET** /organizationFields | Get all organization fields
[**updateOrganizationField()**](OrganizationFieldsApi.md#updateOrganizationField) | **PATCH** /organizationFields/{field_code} | Update one organization field
[**updateOrganizationFieldOptions()**](OrganizationFieldsApi.md#updateOrganizationFieldOptions) | **PATCH** /organizationFields/{field_code}/options | Update organization field options in bulk


## `addOrganizationField()`

```php
addOrganizationField($create_organization_field_request): \Pipedrive\versions\v2\Model\CreateOrganizationField
```

Create one organization field

Creates a new organization custom field.

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


$apiInstance = new Pipedrive\versions\v2\Api\OrganizationFieldsApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$create_organization_field_request = new \Pipedrive\versions\v2\Model\CreateOrganizationFieldRequest(); // \Pipedrive\versions\v2\Model\CreateOrganizationFieldRequest

try {
    $result = $apiInstance->addOrganizationField($create_organization_field_request);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling OrganizationFieldsApi->addOrganizationField: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **create_organization_field_request** | [**\Pipedrive\versions\v2\Model\CreateOrganizationFieldRequest**](../Model/CreateOrganizationFieldRequest.md)|  |

### Return type

[**\Pipedrive\versions\v2\Model\CreateOrganizationField**](../Model/CreateOrganizationField.md)

### Authorization

[api_key](../README.md#api_key), [oauth2](../README.md#oauth2)

### HTTP request headers

- **Content-Type**: `application/json`
- **Accept**: `application/json`

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints)
[[Back to Model list]](../README.md#documentation-for-models)
[[Back to README]](../README.md)

## `addOrganizationFieldOptions()`

```php
addOrganizationFieldOptions($field_code, $request_body): \Pipedrive\versions\v2\Model\InlineResponse200
```

Add organization field options in bulk

Adds new options to an organization custom field that supports options (enum or set field types). This operation is atomic - all options are added or none are added. Returns only the newly added options.

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


$apiInstance = new Pipedrive\versions\v2\Api\OrganizationFieldsApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$field_code = 'field_code_example'; // string | The unique code identifying the field
$request_body = array(new \stdClass); // object[]

try {
    $result = $apiInstance->addOrganizationFieldOptions($field_code, $request_body);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling OrganizationFieldsApi->addOrganizationFieldOptions: ', $e->getMessage(), PHP_EOL;
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

## `deleteOrganizationField()`

```php
deleteOrganizationField($field_code): \Pipedrive\versions\v2\Model\DeleteOrganizationField
```

Delete one organization field

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


$apiInstance = new Pipedrive\versions\v2\Api\OrganizationFieldsApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$field_code = 'field_code_example'; // string | The unique code identifying the field

try {
    $result = $apiInstance->deleteOrganizationField($field_code);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling OrganizationFieldsApi->deleteOrganizationField: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **field_code** | **string**| The unique code identifying the field |

### Return type

[**\Pipedrive\versions\v2\Model\DeleteOrganizationField**](../Model/DeleteOrganizationField.md)

### Authorization

[api_key](../README.md#api_key), [oauth2](../README.md#oauth2)

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json`

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints)
[[Back to Model list]](../README.md#documentation-for-models)
[[Back to README]](../README.md)

## `deleteOrganizationFieldOptions()`

```php
deleteOrganizationFieldOptions($field_code, $request_body): \Pipedrive\versions\v2\Model\InlineResponse200
```

Delete organization field options in bulk

Removes existing options from an organization custom field. This operation is atomic and fails if any of the specified option IDs do not exist. Returns only the deleted options.

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


$apiInstance = new Pipedrive\versions\v2\Api\OrganizationFieldsApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$field_code = 'field_code_example'; // string | The unique code identifying the field
$request_body = array(new \stdClass); // object[]

try {
    $result = $apiInstance->deleteOrganizationFieldOptions($field_code, $request_body);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling OrganizationFieldsApi->deleteOrganizationFieldOptions: ', $e->getMessage(), PHP_EOL;
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

## `getOrganizationField()`

```php
getOrganizationField($field_code, $include_fields): \Pipedrive\versions\v2\Model\GetOrganizationField
```

Get one organization field

Returns metadata about a specific organization field.

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


$apiInstance = new Pipedrive\versions\v2\Api\OrganizationFieldsApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$field_code = 'field_code_example'; // string | The unique code identifying the field
$include_fields = 'include_fields_example'; // string | Optional comma separated string array of additional data namespaces to include in response

try {
    $result = $apiInstance->getOrganizationField($field_code, $include_fields);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling OrganizationFieldsApi->getOrganizationField: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **field_code** | **string**| The unique code identifying the field |
 **include_fields** | **string**| Optional comma separated string array of additional data namespaces to include in response | [optional]

### Return type

[**\Pipedrive\versions\v2\Model\GetOrganizationField**](../Model/GetOrganizationField.md)

### Authorization

[api_key](../README.md#api_key), [oauth2](../README.md#oauth2)

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json`

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints)
[[Back to Model list]](../README.md#documentation-for-models)
[[Back to README]](../README.md)

## `getOrganizationFields()`

```php
getOrganizationFields($include_fields, $limit, $cursor): \Pipedrive\versions\v2\Model\GetOrganizationFields
```

Get all organization fields

Returns metadata about all organization fields in the company.

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


$apiInstance = new Pipedrive\versions\v2\Api\OrganizationFieldsApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$include_fields = 'include_fields_example'; // string | Optional comma separated string array of additional data namespaces to include in response
$limit = 100; // int | For pagination, the limit of entries to be returned. If not provided, 100 items will be returned. Please note that a maximum value of 500 is allowed.
$cursor = 'cursor_example'; // string | For pagination, the marker (an opaque string value) representing the first item on the next page

try {
    $result = $apiInstance->getOrganizationFields($include_fields, $limit, $cursor);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling OrganizationFieldsApi->getOrganizationFields: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **include_fields** | **string**| Optional comma separated string array of additional data namespaces to include in response | [optional]
 **limit** | **int**| For pagination, the limit of entries to be returned. If not provided, 100 items will be returned. Please note that a maximum value of 500 is allowed. | [optional]
 **cursor** | **string**| For pagination, the marker (an opaque string value) representing the first item on the next page | [optional]

### Return type

[**\Pipedrive\versions\v2\Model\GetOrganizationFields**](../Model/GetOrganizationFields.md)

### Authorization

[api_key](../README.md#api_key), [oauth2](../README.md#oauth2)

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json`

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints)
[[Back to Model list]](../README.md#documentation-for-models)
[[Back to README]](../README.md)

## `updateOrganizationField()`

```php
updateOrganizationField($field_code, $update_organization_field_request): \Pipedrive\versions\v2\Model\GetOrganizationField
```

Update one organization field

Updates an organization custom field. The field_code and field_type cannot be changed. At least one field must be provided in the request body.

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


$apiInstance = new Pipedrive\versions\v2\Api\OrganizationFieldsApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$field_code = 'field_code_example'; // string | The unique code identifying the field
$update_organization_field_request = new \Pipedrive\versions\v2\Model\UpdateOrganizationFieldRequest(); // \Pipedrive\versions\v2\Model\UpdateOrganizationFieldRequest

try {
    $result = $apiInstance->updateOrganizationField($field_code, $update_organization_field_request);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling OrganizationFieldsApi->updateOrganizationField: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **field_code** | **string**| The unique code identifying the field |
 **update_organization_field_request** | [**\Pipedrive\versions\v2\Model\UpdateOrganizationFieldRequest**](../Model/UpdateOrganizationFieldRequest.md)|  |

### Return type

[**\Pipedrive\versions\v2\Model\GetOrganizationField**](../Model/GetOrganizationField.md)

### Authorization

[api_key](../README.md#api_key), [oauth2](../README.md#oauth2)

### HTTP request headers

- **Content-Type**: `application/json`
- **Accept**: `application/json`

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints)
[[Back to Model list]](../README.md#documentation-for-models)
[[Back to README]](../README.md)

## `updateOrganizationFieldOptions()`

```php
updateOrganizationFieldOptions($field_code, $request_body): \Pipedrive\versions\v2\Model\InlineResponse200
```

Update organization field options in bulk

Updates existing options for an organization custom field. This operation is atomic and fails if any of the specified option IDs do not exist. Returns only the updated options.

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


$apiInstance = new Pipedrive\versions\v2\Api\OrganizationFieldsApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$field_code = 'field_code_example'; // string | The unique code identifying the field
$request_body = array(new \stdClass); // object[]

try {
    $result = $apiInstance->updateOrganizationFieldOptions($field_code, $request_body);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling OrganizationFieldsApi->updateOrganizationFieldOptions: ', $e->getMessage(), PHP_EOL;
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
