# Pipedrive\versions\v1\FiltersApi

All URIs are relative to https://api.pipedrive.com/v1.

Method | HTTP request | Description
------------- | ------------- | -------------
[**addFilter()**](FiltersApi.md#addFilter) | **POST** /filters | Add a new filter
[**deleteFilter()**](FiltersApi.md#deleteFilter) | **DELETE** /filters/{id} | Delete a filter
[**deleteFilters()**](FiltersApi.md#deleteFilters) | **DELETE** /filters | Delete multiple filters in bulk
[**getFilter()**](FiltersApi.md#getFilter) | **GET** /filters/{id} | Get one filter
[**getFilterHelpers()**](FiltersApi.md#getFilterHelpers) | **GET** /filters/helpers | Get all filter helpers
[**getFilters()**](FiltersApi.md#getFilters) | **GET** /filters | Get all filters
[**updateFilter()**](FiltersApi.md#updateFilter) | **PUT** /filters/{id} | Update filter


## `addFilter()`

```php
addFilter($add_filter_request): \Pipedrive\versions\v1\Model\FiltersPostResponse
```

Add a new filter

Adds a new filter, returns the ID upon success. Note that in the conditions JSON object only one first-level condition group is supported, and it must be glued with 'AND', and only two second level condition groups are supported of which one must be glued with 'AND' and the second with 'OR'. Other combinations do not work (yet) but the syntax supports introducing them in future. For more information, see the tutorial for <a href=\"https://pipedrive.readme.io/docs/adding-a-filter\" target=\"_blank\" rel=\"noopener noreferrer\">adding a filter</a>.

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


$apiInstance = new Pipedrive\versions\v1\Api\FiltersApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$add_filter_request = new \Pipedrive\versions\v1\Model\AddFilterRequest(); // \Pipedrive\versions\v1\Model\AddFilterRequest

try {
    $result = $apiInstance->addFilter($add_filter_request);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling FiltersApi->addFilter: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **add_filter_request** | [**\Pipedrive\versions\v1\Model\AddFilterRequest**](../Model/AddFilterRequest.md)|  | [optional]

### Return type

[**\Pipedrive\versions\v1\Model\FiltersPostResponse**](../Model/FiltersPostResponse.md)

### Authorization

[api_key](../README.md#api_key), [oauth2](../README.md#oauth2)

### HTTP request headers

- **Content-Type**: `application/json`
- **Accept**: `application/json`

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints)
[[Back to Model list]](../README.md#documentation-for-models)
[[Back to README]](../README.md)

## `deleteFilter()`

```php
deleteFilter($id): \Pipedrive\versions\v1\Model\FiltersDeleteResponse
```

Delete a filter

Marks a filter as deleted.

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


$apiInstance = new Pipedrive\versions\v1\Api\FiltersApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$id = 56; // int | The ID of the filter

try {
    $result = $apiInstance->deleteFilter($id);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling FiltersApi->deleteFilter: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **id** | **int**| The ID of the filter |

### Return type

[**\Pipedrive\versions\v1\Model\FiltersDeleteResponse**](../Model/FiltersDeleteResponse.md)

### Authorization

[api_key](../README.md#api_key), [oauth2](../README.md#oauth2)

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json`

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints)
[[Back to Model list]](../README.md#documentation-for-models)
[[Back to README]](../README.md)

## `deleteFilters()`

```php
deleteFilters($ids): \Pipedrive\versions\v1\Model\FiltersBulkDeleteResponse
```

Delete multiple filters in bulk

Marks multiple filters as deleted.

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


$apiInstance = new Pipedrive\versions\v1\Api\FiltersApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$ids = 'ids_example'; // string | The comma-separated filter IDs to delete

try {
    $result = $apiInstance->deleteFilters($ids);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling FiltersApi->deleteFilters: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **ids** | **string**| The comma-separated filter IDs to delete |

### Return type

[**\Pipedrive\versions\v1\Model\FiltersBulkDeleteResponse**](../Model/FiltersBulkDeleteResponse.md)

### Authorization

[api_key](../README.md#api_key), [oauth2](../README.md#oauth2)

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json`

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints)
[[Back to Model list]](../README.md#documentation-for-models)
[[Back to README]](../README.md)

## `getFilter()`

```php
getFilter($id): \Pipedrive\versions\v1\Model\FiltersGetResponse
```

Get one filter

Returns data about a specific filter. Note that this also returns the condition lines of the filter.

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


$apiInstance = new Pipedrive\versions\v1\Api\FiltersApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$id = 56; // int | The ID of the filter

try {
    $result = $apiInstance->getFilter($id);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling FiltersApi->getFilter: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **id** | **int**| The ID of the filter |

### Return type

[**\Pipedrive\versions\v1\Model\FiltersGetResponse**](../Model/FiltersGetResponse.md)

### Authorization

[api_key](../README.md#api_key), [oauth2](../README.md#oauth2)

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json`

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints)
[[Back to Model list]](../README.md#documentation-for-models)
[[Back to README]](../README.md)

## `getFilterHelpers()`

```php
getFilterHelpers(): object
```

Get all filter helpers

Returns all supported filter helpers. It helps to know what conditions and helpers are available when you want to <a href=\"/docs/api/v1/Filters#addFilter\">add</a> or <a href=\"/docs/api/v1/Filters#updateFilter\">update</a> filters. For more information, see the tutorial for <a href=\"https://pipedrive.readme.io/docs/adding-a-filter\" target=\"_blank\" rel=\"noopener noreferrer\">adding a filter</a>.

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


$apiInstance = new Pipedrive\versions\v1\Api\FiltersApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);

try {
    $result = $apiInstance->getFilterHelpers();
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling FiltersApi->getFilterHelpers: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

This endpoint does not need any parameter.

### Return type

**object**

### Authorization

[api_key](../README.md#api_key), [oauth2](../README.md#oauth2)

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json`

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints)
[[Back to Model list]](../README.md#documentation-for-models)
[[Back to README]](../README.md)

## `getFilters()`

```php
getFilters($type): \Pipedrive\versions\v1\Model\FiltersBulkGetResponse
```

Get all filters

Returns data about all filters.

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


$apiInstance = new Pipedrive\versions\v1\Api\FiltersApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$type = new \Pipedrive\versions\v1\Model\\Pipedrive\versions\v1\Model\FilterType(); // \Pipedrive\versions\v1\Model\FilterType | The types of filters to fetch

try {
    $result = $apiInstance->getFilters($type);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling FiltersApi->getFilters: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **type** | [**\Pipedrive\versions\v1\Model\FilterType**](../Model/.md)| The types of filters to fetch | [optional]

### Return type

[**\Pipedrive\versions\v1\Model\FiltersBulkGetResponse**](../Model/FiltersBulkGetResponse.md)

### Authorization

[api_key](../README.md#api_key), [oauth2](../README.md#oauth2)

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json`

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints)
[[Back to Model list]](../README.md#documentation-for-models)
[[Back to README]](../README.md)

## `updateFilter()`

```php
updateFilter($id, $update_filter_request): \Pipedrive\versions\v1\Model\FiltersPostResponse
```

Update filter

Updates an existing filter.

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


$apiInstance = new Pipedrive\versions\v1\Api\FiltersApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$id = 56; // int | The ID of the filter
$update_filter_request = new \Pipedrive\versions\v1\Model\UpdateFilterRequest(); // \Pipedrive\versions\v1\Model\UpdateFilterRequest

try {
    $result = $apiInstance->updateFilter($id, $update_filter_request);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling FiltersApi->updateFilter: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **id** | **int**| The ID of the filter |
 **update_filter_request** | [**\Pipedrive\versions\v1\Model\UpdateFilterRequest**](../Model/UpdateFilterRequest.md)|  | [optional]

### Return type

[**\Pipedrive\versions\v1\Model\FiltersPostResponse**](../Model/FiltersPostResponse.md)

### Authorization

[api_key](../README.md#api_key), [oauth2](../README.md#oauth2)

### HTTP request headers

- **Content-Type**: `application/json`
- **Accept**: `application/json`

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints)
[[Back to Model list]](../README.md#documentation-for-models)
[[Back to README]](../README.md)
