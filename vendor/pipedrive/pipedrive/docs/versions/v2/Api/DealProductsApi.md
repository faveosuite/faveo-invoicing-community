# Pipedrive\versions\v2\DealProductsApi

All URIs are relative to https://api.pipedrive.com/api/v2.

Method | HTTP request | Description
------------- | ------------- | -------------
[**addDealProduct()**](DealProductsApi.md#addDealProduct) | **POST** /deals/{id}/products | Add a product to a deal
[**addManyDealProducts()**](DealProductsApi.md#addManyDealProducts) | **POST** /deals/{id}/products/bulk | Add multiple products to a deal
[**deleteDealProduct()**](DealProductsApi.md#deleteDealProduct) | **DELETE** /deals/{id}/products/{product_attachment_id} | Delete an attached product from a deal
[**deleteManyDealProducts()**](DealProductsApi.md#deleteManyDealProducts) | **DELETE** /deals/{id}/products | Delete many products from a deal
[**getDealProducts()**](DealProductsApi.md#getDealProducts) | **GET** /deals/{id}/products | List products attached to a deal
[**getDealsProducts()**](DealProductsApi.md#getDealsProducts) | **GET** /deals/products | Get deal products of several deals
[**updateDealProduct()**](DealProductsApi.md#updateDealProduct) | **PATCH** /deals/{id}/products/{product_attachment_id} | Update the product attached to a deal


## `addDealProduct()`

```php
addDealProduct($id, $new_deal_product_request_body): \Pipedrive\versions\v2\Model\AddDealProductResponse
```

Add a product to a deal

Adds a product to a deal, creating a new item called a deal-product.

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


$apiInstance = new Pipedrive\versions\v2\Api\DealProductsApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$id = 56; // int | The ID of the deal
$new_deal_product_request_body = new \Pipedrive\versions\v2\Model\NewDealProductRequestBody(); // \Pipedrive\versions\v2\Model\NewDealProductRequestBody

try {
    $result = $apiInstance->addDealProduct($id, $new_deal_product_request_body);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling DealProductsApi->addDealProduct: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **id** | **int**| The ID of the deal |
 **new_deal_product_request_body** | [**\Pipedrive\versions\v2\Model\NewDealProductRequestBody**](../Model/NewDealProductRequestBody.md)|  | [optional]

### Return type

[**\Pipedrive\versions\v2\Model\AddDealProductResponse**](../Model/AddDealProductResponse.md)

### Authorization

[api_key](../README.md#api_key), [oauth2](../README.md#oauth2)

### HTTP request headers

- **Content-Type**: `application/json`
- **Accept**: `application/json`

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints)
[[Back to Model list]](../README.md#documentation-for-models)
[[Back to README]](../README.md)

## `addManyDealProducts()`

```php
addManyDealProducts($id, $create_many_deal_product_request_body): \Pipedrive\versions\v2\Model\CreateManyDealProductResponse
```

Add multiple products to a deal

Adds multiple products to a deal in a single request. Maximum of 100 products allowed per request.

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


$apiInstance = new Pipedrive\versions\v2\Api\DealProductsApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$id = 56; // int | The ID of the deal
$create_many_deal_product_request_body = new \Pipedrive\versions\v2\Model\CreateManyDealProductRequestBody(); // \Pipedrive\versions\v2\Model\CreateManyDealProductRequestBody

try {
    $result = $apiInstance->addManyDealProducts($id, $create_many_deal_product_request_body);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling DealProductsApi->addManyDealProducts: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **id** | **int**| The ID of the deal |
 **create_many_deal_product_request_body** | [**\Pipedrive\versions\v2\Model\CreateManyDealProductRequestBody**](../Model/CreateManyDealProductRequestBody.md)|  | [optional]

### Return type

[**\Pipedrive\versions\v2\Model\CreateManyDealProductResponse**](../Model/CreateManyDealProductResponse.md)

### Authorization

[api_key](../README.md#api_key), [oauth2](../README.md#oauth2)

### HTTP request headers

- **Content-Type**: `application/json`
- **Accept**: `application/json`

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints)
[[Back to Model list]](../README.md#documentation-for-models)
[[Back to README]](../README.md)

## `deleteDealProduct()`

```php
deleteDealProduct($id, $product_attachment_id): \Pipedrive\versions\v2\Model\DeleteDealProduct
```

Delete an attached product from a deal

Deletes a product attachment from a deal, using the `product_attachment_id`.

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


$apiInstance = new Pipedrive\versions\v2\Api\DealProductsApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$id = 56; // int | The ID of the deal
$product_attachment_id = 56; // int | The product attachment ID

try {
    $result = $apiInstance->deleteDealProduct($id, $product_attachment_id);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling DealProductsApi->deleteDealProduct: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **id** | **int**| The ID of the deal |
 **product_attachment_id** | **int**| The product attachment ID |

### Return type

[**\Pipedrive\versions\v2\Model\DeleteDealProduct**](../Model/DeleteDealProduct.md)

### Authorization

[api_key](../README.md#api_key), [oauth2](../README.md#oauth2)

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json`

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints)
[[Back to Model list]](../README.md#documentation-for-models)
[[Back to README]](../README.md)

## `deleteManyDealProducts()`

```php
deleteManyDealProducts($id, $ids): \Pipedrive\versions\v2\Model\DeleteManyDealProductResponse
```

Delete many products from a deal

Deletes multiple products from a deal. If no product IDs are specified, up to 100 products will be removed from the deal. A maximum of 100 product IDs can be provided per request.

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


$apiInstance = new Pipedrive\versions\v2\Api\DealProductsApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$id = 56; // int | The ID of the deal
$ids = 1,2,3; // string | Comma-separated list of deal product IDs to delete. If not provided, all deal products will be deleted up to 100 items. Maximum 100 IDs allowed.

try {
    $result = $apiInstance->deleteManyDealProducts($id, $ids);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling DealProductsApi->deleteManyDealProducts: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **id** | **int**| The ID of the deal |
 **ids** | **string**| Comma-separated list of deal product IDs to delete. If not provided, all deal products will be deleted up to 100 items. Maximum 100 IDs allowed. | [optional]

### Return type

[**\Pipedrive\versions\v2\Model\DeleteManyDealProductResponse**](../Model/DeleteManyDealProductResponse.md)

### Authorization

[api_key](../README.md#api_key), [oauth2](../README.md#oauth2)

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json`

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints)
[[Back to Model list]](../README.md#documentation-for-models)
[[Back to README]](../README.md)

## `getDealProducts()`

```php
getDealProducts($id, $cursor, $limit, $sort_by, $sort_direction): \Pipedrive\versions\v2\Model\DealsProductsResponse
```

List products attached to a deal

Lists products attached to a deal.

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


$apiInstance = new Pipedrive\versions\v2\Api\DealProductsApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$id = 56; // int | The ID of the deal
$cursor = 'cursor_example'; // string | For pagination, the marker (an opaque string value) representing the first item on the next page
$limit = 100; // int | For pagination, the limit of entries to be returned. If not provided, 100 items will be returned. Please note that a maximum value of 500 is allowed.
$sort_by = 'id'; // string | The field to sort by. Supported fields: `id`, `add_time`, `update_time`, `order_nr`.
$sort_direction = 'asc'; // string | The sorting direction. Supported values: `asc`, `desc`.

try {
    $result = $apiInstance->getDealProducts($id, $cursor, $limit, $sort_by, $sort_direction);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling DealProductsApi->getDealProducts: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **id** | **int**| The ID of the deal |
 **cursor** | **string**| For pagination, the marker (an opaque string value) representing the first item on the next page | [optional]
 **limit** | **int**| For pagination, the limit of entries to be returned. If not provided, 100 items will be returned. Please note that a maximum value of 500 is allowed. | [optional]
 **sort_by** | **string**| The field to sort by. Supported fields: &#x60;id&#x60;, &#x60;add_time&#x60;, &#x60;update_time&#x60;, &#x60;order_nr&#x60;. | [optional] [default to &#39;id&#39;]
 **sort_direction** | **string**| The sorting direction. Supported values: &#x60;asc&#x60;, &#x60;desc&#x60;. | [optional] [default to &#39;asc&#39;]

### Return type

[**\Pipedrive\versions\v2\Model\DealsProductsResponse**](../Model/DealsProductsResponse.md)

### Authorization

[api_key](../README.md#api_key), [oauth2](../README.md#oauth2)

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json`

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints)
[[Back to Model list]](../README.md#documentation-for-models)
[[Back to README]](../README.md)

## `getDealsProducts()`

```php
getDealsProducts($deal_ids, $cursor, $limit, $sort_by, $sort_direction): \Pipedrive\versions\v2\Model\DealsProductsResponse
```

Get deal products of several deals

Returns data about products attached to deals

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


$apiInstance = new Pipedrive\versions\v2\Api\DealProductsApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$deal_ids = array(56); // int[] | An array of integers with the IDs of the deals for which the attached products will be returned. A maximum of 100 deal IDs can be provided.
$cursor = 'cursor_example'; // string | For pagination, the marker (an opaque string value) representing the first item on the next page
$limit = 100; // int | For pagination, the limit of entries to be returned. If not provided, 100 items will be returned. Please note that a maximum value of 500 is allowed.
$sort_by = 'id'; // string | The field to sort by. Supported fields: `id`, `deal_id`, `add_time`, `update_time`, `order_nr`.
$sort_direction = 'asc'; // string | The sorting direction. Supported values: `asc`, `desc`.

try {
    $result = $apiInstance->getDealsProducts($deal_ids, $cursor, $limit, $sort_by, $sort_direction);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling DealProductsApi->getDealsProducts: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **deal_ids** | [**int[]**](../Model/int.md)| An array of integers with the IDs of the deals for which the attached products will be returned. A maximum of 100 deal IDs can be provided. |
 **cursor** | **string**| For pagination, the marker (an opaque string value) representing the first item on the next page | [optional]
 **limit** | **int**| For pagination, the limit of entries to be returned. If not provided, 100 items will be returned. Please note that a maximum value of 500 is allowed. | [optional]
 **sort_by** | **string**| The field to sort by. Supported fields: &#x60;id&#x60;, &#x60;deal_id&#x60;, &#x60;add_time&#x60;, &#x60;update_time&#x60;, &#x60;order_nr&#x60;. | [optional] [default to &#39;id&#39;]
 **sort_direction** | **string**| The sorting direction. Supported values: &#x60;asc&#x60;, &#x60;desc&#x60;. | [optional] [default to &#39;asc&#39;]

### Return type

[**\Pipedrive\versions\v2\Model\DealsProductsResponse**](../Model/DealsProductsResponse.md)

### Authorization

[api_key](../README.md#api_key), [oauth2](../README.md#oauth2)

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json`

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints)
[[Back to Model list]](../README.md#documentation-for-models)
[[Back to README]](../README.md)

## `updateDealProduct()`

```php
updateDealProduct($id, $product_attachment_id, $update_deal_product_request_body): \Pipedrive\versions\v2\Model\AddDealProductResponse
```

Update the product attached to a deal

Updates the details of the product that has been attached to a deal.

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


$apiInstance = new Pipedrive\versions\v2\Api\DealProductsApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$id = 56; // int | The ID of the deal
$product_attachment_id = 56; // int | The ID of the deal-product (the ID of the product attached to the deal)
$update_deal_product_request_body = new \Pipedrive\versions\v2\Model\UpdateDealProductRequestBody(); // \Pipedrive\versions\v2\Model\UpdateDealProductRequestBody

try {
    $result = $apiInstance->updateDealProduct($id, $product_attachment_id, $update_deal_product_request_body);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling DealProductsApi->updateDealProduct: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **id** | **int**| The ID of the deal |
 **product_attachment_id** | **int**| The ID of the deal-product (the ID of the product attached to the deal) |
 **update_deal_product_request_body** | [**\Pipedrive\versions\v2\Model\UpdateDealProductRequestBody**](../Model/UpdateDealProductRequestBody.md)|  | [optional]

### Return type

[**\Pipedrive\versions\v2\Model\AddDealProductResponse**](../Model/AddDealProductResponse.md)

### Authorization

[api_key](../README.md#api_key), [oauth2](../README.md#oauth2)

### HTTP request headers

- **Content-Type**: `application/json`
- **Accept**: `application/json`

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints)
[[Back to Model list]](../README.md#documentation-for-models)
[[Back to README]](../README.md)
