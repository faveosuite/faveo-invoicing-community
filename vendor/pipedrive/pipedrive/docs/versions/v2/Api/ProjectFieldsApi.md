# Pipedrive\versions\v2\ProjectFieldsApi

All URIs are relative to https://api.pipedrive.com/api/v2.

Method | HTTP request | Description
------------- | ------------- | -------------
[**addProjectField()**](ProjectFieldsApi.md#addProjectField) | **POST** /projectFields | Create one project field
[**addProjectFieldOptions()**](ProjectFieldsApi.md#addProjectFieldOptions) | **POST** /projectFields/{field_code}/options | Add project field options in bulk
[**deleteProjectField()**](ProjectFieldsApi.md#deleteProjectField) | **DELETE** /projectFields/{field_code} | Delete one project field
[**deleteProjectFieldOptions()**](ProjectFieldsApi.md#deleteProjectFieldOptions) | **DELETE** /projectFields/{field_code}/options | Delete project field options in bulk
[**getProjectField()**](ProjectFieldsApi.md#getProjectField) | **GET** /projectFields/{field_code} | Get one project field
[**getProjectFields()**](ProjectFieldsApi.md#getProjectFields) | **GET** /projectFields | Get all project fields
[**updateProjectField()**](ProjectFieldsApi.md#updateProjectField) | **PATCH** /projectFields/{field_code} | Update one project field
[**updateProjectFieldOptions()**](ProjectFieldsApi.md#updateProjectFieldOptions) | **PATCH** /projectFields/{field_code}/options | Update project field options in bulk


## `addProjectField()`

```php
addProjectField($create_project_field_request): \Pipedrive\versions\v2\Model\CreateProjectField
```

Create one project field

Creates a new project custom field.

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


$apiInstance = new Pipedrive\versions\v2\Api\ProjectFieldsApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$create_project_field_request = new \Pipedrive\versions\v2\Model\CreateProjectFieldRequest(); // \Pipedrive\versions\v2\Model\CreateProjectFieldRequest

try {
    $result = $apiInstance->addProjectField($create_project_field_request);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling ProjectFieldsApi->addProjectField: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **create_project_field_request** | [**\Pipedrive\versions\v2\Model\CreateProjectFieldRequest**](../Model/CreateProjectFieldRequest.md)|  |

### Return type

[**\Pipedrive\versions\v2\Model\CreateProjectField**](../Model/CreateProjectField.md)

### Authorization

[api_key](../README.md#api_key), [oauth2](../README.md#oauth2)

### HTTP request headers

- **Content-Type**: `application/json`
- **Accept**: `application/json`

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints)
[[Back to Model list]](../README.md#documentation-for-models)
[[Back to README]](../README.md)

## `addProjectFieldOptions()`

```php
addProjectFieldOptions($field_code, $request_body): \Pipedrive\versions\v2\Model\InlineResponse200
```

Add project field options in bulk

Adds new options to a project custom field that supports options (enum or set field types). This operation is atomic - all options are added or none are added. Returns only the newly added options.

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


$apiInstance = new Pipedrive\versions\v2\Api\ProjectFieldsApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$field_code = 'field_code_example'; // string | The unique code identifying the field
$request_body = array(new \stdClass); // object[]

try {
    $result = $apiInstance->addProjectFieldOptions($field_code, $request_body);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling ProjectFieldsApi->addProjectFieldOptions: ', $e->getMessage(), PHP_EOL;
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

## `deleteProjectField()`

```php
deleteProjectField($field_code): \Pipedrive\versions\v2\Model\DeleteProjectField
```

Delete one project field

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


$apiInstance = new Pipedrive\versions\v2\Api\ProjectFieldsApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$field_code = 'field_code_example'; // string | The unique code identifying the field

try {
    $result = $apiInstance->deleteProjectField($field_code);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling ProjectFieldsApi->deleteProjectField: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **field_code** | **string**| The unique code identifying the field |

### Return type

[**\Pipedrive\versions\v2\Model\DeleteProjectField**](../Model/DeleteProjectField.md)

### Authorization

[api_key](../README.md#api_key), [oauth2](../README.md#oauth2)

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json`

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints)
[[Back to Model list]](../README.md#documentation-for-models)
[[Back to README]](../README.md)

## `deleteProjectFieldOptions()`

```php
deleteProjectFieldOptions($field_code, $request_body): \Pipedrive\versions\v2\Model\InlineResponse200
```

Delete project field options in bulk

Removes existing options from a project custom field. This operation is atomic and fails if any of the specified option IDs do not exist. Returns only the deleted options.

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


$apiInstance = new Pipedrive\versions\v2\Api\ProjectFieldsApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$field_code = 'field_code_example'; // string | The unique code identifying the field
$request_body = array(new \stdClass); // object[]

try {
    $result = $apiInstance->deleteProjectFieldOptions($field_code, $request_body);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling ProjectFieldsApi->deleteProjectFieldOptions: ', $e->getMessage(), PHP_EOL;
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

## `getProjectField()`

```php
getProjectField($field_code): \Pipedrive\versions\v2\Model\GetProjectField
```

Get one project field

Returns metadata about a specific project field.

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


$apiInstance = new Pipedrive\versions\v2\Api\ProjectFieldsApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$field_code = 'field_code_example'; // string | The unique code identifying the field

try {
    $result = $apiInstance->getProjectField($field_code);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling ProjectFieldsApi->getProjectField: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **field_code** | **string**| The unique code identifying the field |

### Return type

[**\Pipedrive\versions\v2\Model\GetProjectField**](../Model/GetProjectField.md)

### Authorization

[api_key](../README.md#api_key), [oauth2](../README.md#oauth2)

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json`

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints)
[[Back to Model list]](../README.md#documentation-for-models)
[[Back to README]](../README.md)

## `getProjectFields()`

```php
getProjectFields($limit, $cursor): \Pipedrive\versions\v2\Model\GetProjectFields
```

Get all project fields

Returns metadata about all project fields in the company.

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


$apiInstance = new Pipedrive\versions\v2\Api\ProjectFieldsApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$limit = 100; // int | For pagination, the limit of entries to be returned. If not provided, 100 items will be returned. Please note that a maximum value of 500 is allowed.
$cursor = 'cursor_example'; // string | For pagination, the marker (an opaque string value) representing the first item on the next page

try {
    $result = $apiInstance->getProjectFields($limit, $cursor);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling ProjectFieldsApi->getProjectFields: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **limit** | **int**| For pagination, the limit of entries to be returned. If not provided, 100 items will be returned. Please note that a maximum value of 500 is allowed. | [optional]
 **cursor** | **string**| For pagination, the marker (an opaque string value) representing the first item on the next page | [optional]

### Return type

[**\Pipedrive\versions\v2\Model\GetProjectFields**](../Model/GetProjectFields.md)

### Authorization

[api_key](../README.md#api_key), [oauth2](../README.md#oauth2)

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json`

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints)
[[Back to Model list]](../README.md#documentation-for-models)
[[Back to README]](../README.md)

## `updateProjectField()`

```php
updateProjectField($field_code, $update_project_field_request): \Pipedrive\versions\v2\Model\GetProjectField
```

Update one project field

Updates a project custom field. The field_code and field_type cannot be changed. At least one field must be provided in the request body.

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


$apiInstance = new Pipedrive\versions\v2\Api\ProjectFieldsApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$field_code = 'field_code_example'; // string | The unique code identifying the field
$update_project_field_request = new \Pipedrive\versions\v2\Model\UpdateProjectFieldRequest(); // \Pipedrive\versions\v2\Model\UpdateProjectFieldRequest

try {
    $result = $apiInstance->updateProjectField($field_code, $update_project_field_request);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling ProjectFieldsApi->updateProjectField: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **field_code** | **string**| The unique code identifying the field |
 **update_project_field_request** | [**\Pipedrive\versions\v2\Model\UpdateProjectFieldRequest**](../Model/UpdateProjectFieldRequest.md)|  |

### Return type

[**\Pipedrive\versions\v2\Model\GetProjectField**](../Model/GetProjectField.md)

### Authorization

[api_key](../README.md#api_key), [oauth2](../README.md#oauth2)

### HTTP request headers

- **Content-Type**: `application/json`
- **Accept**: `application/json`

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints)
[[Back to Model list]](../README.md#documentation-for-models)
[[Back to README]](../README.md)

## `updateProjectFieldOptions()`

```php
updateProjectFieldOptions($field_code, $request_body): \Pipedrive\versions\v2\Model\InlineResponse200
```

Update project field options in bulk

Updates existing options for a project custom field. This operation is atomic and fails if any of the specified option IDs do not exist. Returns only the updated options.

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


$apiInstance = new Pipedrive\versions\v2\Api\ProjectFieldsApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$field_code = 'field_code_example'; // string | The unique code identifying the field
$request_body = array(new \stdClass); // object[]

try {
    $result = $apiInstance->updateProjectFieldOptions($field_code, $request_body);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling ProjectFieldsApi->updateProjectFieldOptions: ', $e->getMessage(), PHP_EOL;
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
