# Pipedrive\versions\v1\OrganizationFieldsApi

All URIs are relative to https://api.pipedrive.com/v1.

Method | HTTP request | Description
------------- | ------------- | -------------
[**addOrganizationField()**](OrganizationFieldsApi.md#addOrganizationField) | **POST** /organizationFields | Add a new organization field
[**deleteOrganizationField()**](OrganizationFieldsApi.md#deleteOrganizationField) | **DELETE** /organizationFields/{id} | Delete an organization field
[**deleteOrganizationFields()**](OrganizationFieldsApi.md#deleteOrganizationFields) | **DELETE** /organizationFields | Delete multiple organization fields in bulk
[**getOrganizationField()**](OrganizationFieldsApi.md#getOrganizationField) | **GET** /organizationFields/{id} | Get one organization field
[**getOrganizationFields()**](OrganizationFieldsApi.md#getOrganizationFields) | **GET** /organizationFields | Get all organization fields
[**updateOrganizationField()**](OrganizationFieldsApi.md#updateOrganizationField) | **PUT** /organizationFields/{id} | Update an organization field


## `addOrganizationField()`

```php
addOrganizationField($field_create_request): \Pipedrive\versions\v1\Model\FieldResponse
```

Add a new organization field

Adds a new organization field. For more information, see the tutorial for <a href=\"https://pipedrive.readme.io/docs/adding-a-new-custom-field\" target=\"_blank\" rel=\"noopener noreferrer\">adding a new custom field</a>.

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


$apiInstance = new Pipedrive\versions\v1\Api\OrganizationFieldsApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$field_create_request = new \Pipedrive\versions\v1\Model\FieldCreateRequest(); // \Pipedrive\versions\v1\Model\FieldCreateRequest

try {
    $result = $apiInstance->addOrganizationField($field_create_request);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling OrganizationFieldsApi->addOrganizationField: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **field_create_request** | [**\Pipedrive\versions\v1\Model\FieldCreateRequest**](../Model/FieldCreateRequest.md)|  | [optional]

### Return type

[**\Pipedrive\versions\v1\Model\FieldResponse**](../Model/FieldResponse.md)

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
deleteOrganizationField($id): \Pipedrive\versions\v1\Model\DeleteResponse
```

Delete an organization field

Marks a field as deleted. For more information, see the tutorial for <a href=\"https://pipedrive.readme.io/docs/deleting-a-custom-field\" target=\"_blank\" rel=\"noopener noreferrer\">deleting a custom field</a>.

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


$apiInstance = new Pipedrive\versions\v1\Api\OrganizationFieldsApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$id = 56; // int | The ID of the field

try {
    $result = $apiInstance->deleteOrganizationField($id);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling OrganizationFieldsApi->deleteOrganizationField: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **id** | **int**| The ID of the field |

### Return type

[**\Pipedrive\versions\v1\Model\DeleteResponse**](../Model/DeleteResponse.md)

### Authorization

[api_key](../README.md#api_key), [oauth2](../README.md#oauth2)

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json`

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints)
[[Back to Model list]](../README.md#documentation-for-models)
[[Back to README]](../README.md)

## `deleteOrganizationFields()`

```php
deleteOrganizationFields($ids): \Pipedrive\versions\v1\Model\BulkDeleteResponse
```

Delete multiple organization fields in bulk

Marks multiple fields as deleted.

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


$apiInstance = new Pipedrive\versions\v1\Api\OrganizationFieldsApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$ids = 'ids_example'; // string | The comma-separated field IDs to delete

try {
    $result = $apiInstance->deleteOrganizationFields($ids);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling OrganizationFieldsApi->deleteOrganizationFields: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **ids** | **string**| The comma-separated field IDs to delete |

### Return type

[**\Pipedrive\versions\v1\Model\BulkDeleteResponse**](../Model/BulkDeleteResponse.md)

### Authorization

[api_key](../README.md#api_key), [oauth2](../README.md#oauth2)

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json`

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints)
[[Back to Model list]](../README.md#documentation-for-models)
[[Back to README]](../README.md)

## `getOrganizationField()`

```php
getOrganizationField($id): \Pipedrive\versions\v1\Model\FieldResponse
```

Get one organization field

Returns data about a specific organization field.

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


$apiInstance = new Pipedrive\versions\v1\Api\OrganizationFieldsApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$id = 56; // int | The ID of the field

try {
    $result = $apiInstance->getOrganizationField($id);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling OrganizationFieldsApi->getOrganizationField: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **id** | **int**| The ID of the field |

### Return type

[**\Pipedrive\versions\v1\Model\FieldResponse**](../Model/FieldResponse.md)

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
getOrganizationFields($start, $limit): \Pipedrive\versions\v1\Model\FieldsResponse
```

Get all organization fields

Returns data about all organization fields.

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


$apiInstance = new Pipedrive\versions\v1\Api\OrganizationFieldsApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$start = 0; // int | Pagination start
$limit = 56; // int | Items shown per page

try {
    $result = $apiInstance->getOrganizationFields($start, $limit);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling OrganizationFieldsApi->getOrganizationFields: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **start** | **int**| Pagination start | [optional] [default to 0]
 **limit** | **int**| Items shown per page | [optional]

### Return type

[**\Pipedrive\versions\v1\Model\FieldsResponse**](../Model/FieldsResponse.md)

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
updateOrganizationField($id, $field_update_request): \Pipedrive\versions\v1\Model\FieldResponse
```

Update an organization field

Updates an organization field. For more information, see the tutorial for <a href=\" https://pipedrive.readme.io/docs/updating-custom-field-value \" target=\"_blank\" rel=\"noopener noreferrer\">updating custom fields' values</a>.

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


$apiInstance = new Pipedrive\versions\v1\Api\OrganizationFieldsApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$id = 56; // int | The ID of the field
$field_update_request = new \Pipedrive\versions\v1\Model\FieldUpdateRequest(); // \Pipedrive\versions\v1\Model\FieldUpdateRequest

try {
    $result = $apiInstance->updateOrganizationField($id, $field_update_request);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling OrganizationFieldsApi->updateOrganizationField: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **id** | **int**| The ID of the field |
 **field_update_request** | [**\Pipedrive\versions\v1\Model\FieldUpdateRequest**](../Model/FieldUpdateRequest.md)|  | [optional]

### Return type

[**\Pipedrive\versions\v1\Model\FieldResponse**](../Model/FieldResponse.md)

### Authorization

[api_key](../README.md#api_key), [oauth2](../README.md#oauth2)

### HTTP request headers

- **Content-Type**: `application/json`
- **Accept**: `application/json`

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints)
[[Back to Model list]](../README.md#documentation-for-models)
[[Back to README]](../README.md)
