# Pipedrive\versions\v1\ProductsApi

All URIs are relative to https://api.pipedrive.com/v1.

Method | HTTP request | Description
------------- | ------------- | -------------
[**addProductFollower()**](ProductsApi.md#addProductFollower) | **POST** /products/{id}/followers | Add a follower to a product
[**deleteProductFollower()**](ProductsApi.md#deleteProductFollower) | **DELETE** /products/{id}/followers/{follower_id} | Delete a follower from a product
[**getProductDeals()**](ProductsApi.md#getProductDeals) | **GET** /products/{id}/deals | Get deals where a product is attached to
[**getProductFiles()**](ProductsApi.md#getProductFiles) | **GET** /products/{id}/files | List files attached to a product
[**getProductFollowers()**](ProductsApi.md#getProductFollowers) | **GET** /products/{id}/followers | List followers of a product
[**getProductUsers()**](ProductsApi.md#getProductUsers) | **GET** /products/{id}/permittedUsers | List permitted users


## `addProductFollower()`

```php
addProductFollower($id, $add_product_follower_request): \Pipedrive\versions\v1\Model\NewFollowerResponse
```

Add a follower to a product

Adds a follower to a product.

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


$apiInstance = new Pipedrive\versions\v1\Api\ProductsApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$id = 56; // int | The ID of the product
$add_product_follower_request = new \Pipedrive\versions\v1\Model\AddProductFollowerRequest(); // \Pipedrive\versions\v1\Model\AddProductFollowerRequest

try {
    $result = $apiInstance->addProductFollower($id, $add_product_follower_request);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling ProductsApi->addProductFollower: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **id** | **int**| The ID of the product |
 **add_product_follower_request** | [**\Pipedrive\versions\v1\Model\AddProductFollowerRequest**](../Model/AddProductFollowerRequest.md)|  | [optional]

### Return type

[**\Pipedrive\versions\v1\Model\NewFollowerResponse**](../Model/NewFollowerResponse.md)

### Authorization

[api_key](../README.md#api_key), [oauth2](../README.md#oauth2)

### HTTP request headers

- **Content-Type**: `application/json`
- **Accept**: `application/json`

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints)
[[Back to Model list]](../README.md#documentation-for-models)
[[Back to README]](../README.md)

## `deleteProductFollower()`

```php
deleteProductFollower($id, $follower_id): \Pipedrive\versions\v1\Model\DeleteProductFollowerResponse
```

Delete a follower from a product

Deletes a follower from a product.

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


$apiInstance = new Pipedrive\versions\v1\Api\ProductsApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$id = 56; // int | The ID of the product
$follower_id = 56; // int | The ID of the relationship between the follower and the product

try {
    $result = $apiInstance->deleteProductFollower($id, $follower_id);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling ProductsApi->deleteProductFollower: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **id** | **int**| The ID of the product |
 **follower_id** | **int**| The ID of the relationship between the follower and the product |

### Return type

[**\Pipedrive\versions\v1\Model\DeleteProductFollowerResponse**](../Model/DeleteProductFollowerResponse.md)

### Authorization

[api_key](../README.md#api_key), [oauth2](../README.md#oauth2)

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json`

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints)
[[Back to Model list]](../README.md#documentation-for-models)
[[Back to README]](../README.md)

## `getProductDeals()`

```php
getProductDeals($id, $start, $limit, $status): \Pipedrive\versions\v1\Model\ListDealsResponse
```

Get deals where a product is attached to

Returns data about deals that have a product attached to it.

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


$apiInstance = new Pipedrive\versions\v1\Api\ProductsApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$id = 56; // int | The ID of the product
$start = 0; // int | Pagination start
$limit = 56; // int | Items shown per page
$status = 'all_not_deleted'; // string | Only fetch deals with a specific status. If omitted, all not deleted deals are returned. If set to deleted, deals that have been deleted up to 30 days ago will be included.

try {
    $result = $apiInstance->getProductDeals($id, $start, $limit, $status);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling ProductsApi->getProductDeals: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **id** | **int**| The ID of the product |
 **start** | **int**| Pagination start | [optional] [default to 0]
 **limit** | **int**| Items shown per page | [optional]
 **status** | **string**| Only fetch deals with a specific status. If omitted, all not deleted deals are returned. If set to deleted, deals that have been deleted up to 30 days ago will be included. | [optional] [default to &#39;all_not_deleted&#39;]

### Return type

[**\Pipedrive\versions\v1\Model\ListDealsResponse**](../Model/ListDealsResponse.md)

### Authorization

[api_key](../README.md#api_key), [oauth2](../README.md#oauth2)

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json`

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints)
[[Back to Model list]](../README.md#documentation-for-models)
[[Back to README]](../README.md)

## `getProductFiles()`

```php
getProductFiles($id, $start, $limit, $sort): \Pipedrive\versions\v1\Model\ListProductFilesResponse
```

List files attached to a product

Lists files associated with a product.

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


$apiInstance = new Pipedrive\versions\v1\Api\ProductsApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$id = 56; // int | The ID of the product
$start = 0; // int | Pagination start
$limit = 56; // int | Items shown per page. Please note that a maximum value of 100 is allowed.
$sort = 'sort_example'; // string | Supported fields: `id`, `update_time`

try {
    $result = $apiInstance->getProductFiles($id, $start, $limit, $sort);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling ProductsApi->getProductFiles: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **id** | **int**| The ID of the product |
 **start** | **int**| Pagination start | [optional] [default to 0]
 **limit** | **int**| Items shown per page. Please note that a maximum value of 100 is allowed. | [optional]
 **sort** | **string**| Supported fields: &#x60;id&#x60;, &#x60;update_time&#x60; | [optional]

### Return type

[**\Pipedrive\versions\v1\Model\ListProductFilesResponse**](../Model/ListProductFilesResponse.md)

### Authorization

[api_key](../README.md#api_key), [oauth2](../README.md#oauth2)

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json`

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints)
[[Back to Model list]](../README.md#documentation-for-models)
[[Back to README]](../README.md)

## `getProductFollowers()`

```php
getProductFollowers($id, $start, $limit): \Pipedrive\versions\v1\Model\ListProductFollowersResponse
```

List followers of a product

Lists the followers of a product.

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


$apiInstance = new Pipedrive\versions\v1\Api\ProductsApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$id = 56; // int | The ID of the product
$start = 0; // int | Pagination start
$limit = 56; // int | Items shown per page

try {
    $result = $apiInstance->getProductFollowers($id, $start, $limit);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling ProductsApi->getProductFollowers: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **id** | **int**| The ID of the product |
 **start** | **int**| Pagination start | [optional] [default to 0]
 **limit** | **int**| Items shown per page | [optional]

### Return type

[**\Pipedrive\versions\v1\Model\ListProductFollowersResponse**](../Model/ListProductFollowersResponse.md)

### Authorization

[api_key](../README.md#api_key), [oauth2](../README.md#oauth2)

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json`

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints)
[[Back to Model list]](../README.md#documentation-for-models)
[[Back to README]](../README.md)

## `getProductUsers()`

```php
getProductUsers($id): \Pipedrive\versions\v1\Model\UserIDs
```

List permitted users

Lists users permitted to access a product.

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


$apiInstance = new Pipedrive\versions\v1\Api\ProductsApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$id = 56; // int | The ID of the product

try {
    $result = $apiInstance->getProductUsers($id);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling ProductsApi->getProductUsers: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **id** | **int**| The ID of the product |

### Return type

[**\Pipedrive\versions\v1\Model\UserIDs**](../Model/UserIDs.md)

### Authorization

[api_key](../README.md#api_key), [oauth2](../README.md#oauth2)

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json`

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints)
[[Back to Model list]](../README.md#documentation-for-models)
[[Back to README]](../README.md)
