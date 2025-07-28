# Pipedrive\versions\v2\DealsApi

All URIs are relative to https://api.pipedrive.com/api/v2.

Method | HTTP request | Description
------------- | ------------- | -------------
[**addDeal()**](DealsApi.md#addDeal) | **POST** /deals | Add a new deal
[**addDealFollower()**](DealsApi.md#addDealFollower) | **POST** /deals/{id}/followers | Add a follower to a deal
[**addDealProduct()**](DealsApi.md#addDealProduct) | **POST** /deals/{id}/products | Add a product to a deal
[**convertDealToLead()**](DealsApi.md#convertDealToLead) | **POST** /deals/{id}/convert/lead | Convert a deal to a lead (BETA)
[**deleteAdditionalDiscount()**](DealsApi.md#deleteAdditionalDiscount) | **DELETE** /deals/{id}/discounts/{discount_id} | Delete a discount from a deal
[**deleteDeal()**](DealsApi.md#deleteDeal) | **DELETE** /deals/{id} | Delete a deal
[**deleteDealFollower()**](DealsApi.md#deleteDealFollower) | **DELETE** /deals/{id}/followers/{follower_id} | Delete a follower from a deal
[**deleteDealProduct()**](DealsApi.md#deleteDealProduct) | **DELETE** /deals/{id}/products/{product_attachment_id} | Delete an attached product from a deal
[**deleteInstallment()**](DealsApi.md#deleteInstallment) | **DELETE** /deals/{id}/installments/{installment_id} | Delete an installment from a deal
[**getAdditionalDiscounts()**](DealsApi.md#getAdditionalDiscounts) | **GET** /deals/{id}/discounts | List discounts added to a deal
[**getArchivedDeals()**](DealsApi.md#getArchivedDeals) | **GET** /deals/archived | Get all archived deals
[**getDeal()**](DealsApi.md#getDeal) | **GET** /deals/{id} | Get details of a deal
[**getDealConversionStatus()**](DealsApi.md#getDealConversionStatus) | **GET** /deals/{id}/convert/status/{conversion_id} | Get Deal conversion status (BETA)
[**getDealFollowers()**](DealsApi.md#getDealFollowers) | **GET** /deals/{id}/followers | List followers of a deal
[**getDealFollowersChangelog()**](DealsApi.md#getDealFollowersChangelog) | **GET** /deals/{id}/followers/changelog | List followers changelog of a deal
[**getDealProducts()**](DealsApi.md#getDealProducts) | **GET** /deals/{id}/products | List products attached to a deal
[**getDeals()**](DealsApi.md#getDeals) | **GET** /deals | Get all deals
[**getDealsProducts()**](DealsApi.md#getDealsProducts) | **GET** /deals/products | Get deal products of several deals
[**getInstallments()**](DealsApi.md#getInstallments) | **GET** /deals/installments | List installments added to a list of deals
[**postAdditionalDiscount()**](DealsApi.md#postAdditionalDiscount) | **POST** /deals/{id}/discounts | Add a discount to a deal
[**postInstallment()**](DealsApi.md#postInstallment) | **POST** /deals/{id}/installments | Add an installment to a deal
[**searchDeals()**](DealsApi.md#searchDeals) | **GET** /deals/search | Search deals
[**updateAdditionalDiscount()**](DealsApi.md#updateAdditionalDiscount) | **PATCH** /deals/{id}/discounts/{discount_id} | Update a discount added to a deal
[**updateDeal()**](DealsApi.md#updateDeal) | **PATCH** /deals/{id} | Update a deal
[**updateDealProduct()**](DealsApi.md#updateDealProduct) | **PATCH** /deals/{id}/products/{product_attachment_id} | Update the product attached to a deal
[**updateInstallment()**](DealsApi.md#updateInstallment) | **PATCH** /deals/{id}/installments/{installment_id} | Update an installment added to a deal


## `addDeal()`

```php
addDeal($deal_request_body): \Pipedrive\versions\v2\Model\PostPatchGetDeal
```

Add a new deal

Adds a new deal.

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


$apiInstance = new Pipedrive\versions\v2\Api\DealsApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$deal_request_body = new \Pipedrive\versions\v2\Model\DealRequestBody(); // \Pipedrive\versions\v2\Model\DealRequestBody

try {
    $result = $apiInstance->addDeal($deal_request_body);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling DealsApi->addDeal: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **deal_request_body** | [**\Pipedrive\versions\v2\Model\DealRequestBody**](../Model/DealRequestBody.md)|  | [optional]

### Return type

[**\Pipedrive\versions\v2\Model\PostPatchGetDeal**](../Model/PostPatchGetDeal.md)

### Authorization

[api_key](../README.md#api_key), [oauth2](../README.md#oauth2)

### HTTP request headers

- **Content-Type**: `application/json`
- **Accept**: `application/json`

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints)
[[Back to Model list]](../README.md#documentation-for-models)
[[Back to README]](../README.md)

## `addDealFollower()`

```php
addDealFollower($id, $follower_request_body): \Pipedrive\versions\v2\Model\PostFollower
```

Add a follower to a deal

Adds a user as a follower to the deal.

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


$apiInstance = new Pipedrive\versions\v2\Api\DealsApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$id = 56; // int | The ID of the deal
$follower_request_body = new \Pipedrive\versions\v2\Model\FollowerRequestBody(); // \Pipedrive\versions\v2\Model\FollowerRequestBody

try {
    $result = $apiInstance->addDealFollower($id, $follower_request_body);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling DealsApi->addDealFollower: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **id** | **int**| The ID of the deal |
 **follower_request_body** | [**\Pipedrive\versions\v2\Model\FollowerRequestBody**](../Model/FollowerRequestBody.md)|  | [optional]

### Return type

[**\Pipedrive\versions\v2\Model\PostFollower**](../Model/PostFollower.md)

### Authorization

[api_key](../README.md#api_key), [oauth2](../README.md#oauth2)

### HTTP request headers

- **Content-Type**: `application/json`
- **Accept**: `application/json`

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints)
[[Back to Model list]](../README.md#documentation-for-models)
[[Back to README]](../README.md)

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


$apiInstance = new Pipedrive\versions\v2\Api\DealsApi(
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
    echo 'Exception when calling DealsApi->addDealProduct: ', $e->getMessage(), PHP_EOL;
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

## `convertDealToLead()`

```php
convertDealToLead($id): \Pipedrive\versions\v2\Model\AddConvertDealToLeadResponse
```

Convert a deal to a lead (BETA)

Initiates a conversion of a deal to a lead. The return value is an ID of a job that was assigned to perform the conversion. Related entities (notes, files, emails, activities, ...) are transferred during the process to the target entity. There are exceptions for entities like invoices or history that are not transferred and remain linked to the original deal. If the conversion is successful, the deal is marked as deleted. To retrieve the created entity ID and the result of the conversion, call the <a href=\"https://developers.pipedrive.com/docs/api/v1/Deals#getDealConversionStatus\">/api/v2/deals/{deal_id}/convert/status/{conversion_id}</a> endpoint.

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


$apiInstance = new Pipedrive\versions\v2\Api\DealsApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$id = 56; // int | The ID of the deal to convert

try {
    $result = $apiInstance->convertDealToLead($id);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling DealsApi->convertDealToLead: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **id** | **int**| The ID of the deal to convert |

### Return type

[**\Pipedrive\versions\v2\Model\AddConvertDealToLeadResponse**](../Model/AddConvertDealToLeadResponse.md)

### Authorization

[api_key](../README.md#api_key), [oauth2](../README.md#oauth2)

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json`

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints)
[[Back to Model list]](../README.md#documentation-for-models)
[[Back to README]](../README.md)

## `deleteAdditionalDiscount()`

```php
deleteAdditionalDiscount($id, $discount_id): \Pipedrive\versions\v2\Model\DeleteAdditionalDiscountResponse
```

Delete a discount from a deal

Removes a discount from a deal, changing the deal value if the deal has one-time products attached.

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


$apiInstance = new Pipedrive\versions\v2\Api\DealsApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$id = 56; // int | The ID of the deal
$discount_id = 56; // int | The ID of the discount

try {
    $result = $apiInstance->deleteAdditionalDiscount($id, $discount_id);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling DealsApi->deleteAdditionalDiscount: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **id** | **int**| The ID of the deal |
 **discount_id** | **int**| The ID of the discount |

### Return type

[**\Pipedrive\versions\v2\Model\DeleteAdditionalDiscountResponse**](../Model/DeleteAdditionalDiscountResponse.md)

### Authorization

[api_key](../README.md#api_key), [oauth2](../README.md#oauth2)

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json`

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints)
[[Back to Model list]](../README.md#documentation-for-models)
[[Back to README]](../README.md)

## `deleteDeal()`

```php
deleteDeal($id): \Pipedrive\versions\v2\Model\DeleteDealResponse
```

Delete a deal

Marks a deal as deleted. After 30 days, the deal will be permanently deleted.

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


$apiInstance = new Pipedrive\versions\v2\Api\DealsApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$id = 56; // int | The ID of the deal

try {
    $result = $apiInstance->deleteDeal($id);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling DealsApi->deleteDeal: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **id** | **int**| The ID of the deal |

### Return type

[**\Pipedrive\versions\v2\Model\DeleteDealResponse**](../Model/DeleteDealResponse.md)

### Authorization

[api_key](../README.md#api_key), [oauth2](../README.md#oauth2)

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json`

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints)
[[Back to Model list]](../README.md#documentation-for-models)
[[Back to README]](../README.md)

## `deleteDealFollower()`

```php
deleteDealFollower($id, $follower_id): \Pipedrive\versions\v2\Model\DeleteFollowerResponse
```

Delete a follower from a deal

Deletes a user follower from the deal.

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


$apiInstance = new Pipedrive\versions\v2\Api\DealsApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$id = 56; // int | The ID of the deal
$follower_id = 56; // int | The ID of the following user

try {
    $result = $apiInstance->deleteDealFollower($id, $follower_id);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling DealsApi->deleteDealFollower: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **id** | **int**| The ID of the deal |
 **follower_id** | **int**| The ID of the following user |

### Return type

[**\Pipedrive\versions\v2\Model\DeleteFollowerResponse**](../Model/DeleteFollowerResponse.md)

### Authorization

[api_key](../README.md#api_key), [oauth2](../README.md#oauth2)

### HTTP request headers

- **Content-Type**: Not defined
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


$apiInstance = new Pipedrive\versions\v2\Api\DealsApi(
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
    echo 'Exception when calling DealsApi->deleteDealProduct: ', $e->getMessage(), PHP_EOL;
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

## `deleteInstallment()`

```php
deleteInstallment($id, $installment_id): \Pipedrive\versions\v2\Model\DeleteInstallmentResponse
```

Delete an installment from a deal

Removes an installment from a deal.  Only available in Advanced and above plans.

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


$apiInstance = new Pipedrive\versions\v2\Api\DealsApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$id = 56; // int | The ID of the deal
$installment_id = 56; // int | The ID of the installment

try {
    $result = $apiInstance->deleteInstallment($id, $installment_id);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling DealsApi->deleteInstallment: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **id** | **int**| The ID of the deal |
 **installment_id** | **int**| The ID of the installment |

### Return type

[**\Pipedrive\versions\v2\Model\DeleteInstallmentResponse**](../Model/DeleteInstallmentResponse.md)

### Authorization

[api_key](../README.md#api_key), [oauth2](../README.md#oauth2)

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json`

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints)
[[Back to Model list]](../README.md#documentation-for-models)
[[Back to README]](../README.md)

## `getAdditionalDiscounts()`

```php
getAdditionalDiscounts($id): \Pipedrive\versions\v2\Model\AdditionalDiscountsResponse
```

List discounts added to a deal

Lists discounts attached to a deal.

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


$apiInstance = new Pipedrive\versions\v2\Api\DealsApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$id = 56; // int | The ID of the deal

try {
    $result = $apiInstance->getAdditionalDiscounts($id);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling DealsApi->getAdditionalDiscounts: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **id** | **int**| The ID of the deal |

### Return type

[**\Pipedrive\versions\v2\Model\AdditionalDiscountsResponse**](../Model/AdditionalDiscountsResponse.md)

### Authorization

[api_key](../README.md#api_key), [oauth2](../README.md#oauth2)

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json`

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints)
[[Back to Model list]](../README.md#documentation-for-models)
[[Back to README]](../README.md)

## `getArchivedDeals()`

```php
getArchivedDeals($filter_id, $ids, $owner_id, $person_id, $org_id, $pipeline_id, $stage_id, $status, $updated_since, $updated_until, $sort_by, $sort_direction, $include_fields, $custom_fields, $limit, $cursor): \Pipedrive\versions\v2\Model\GetDeals
```

Get all archived deals

Returns data about all archived deals.

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


$apiInstance = new Pipedrive\versions\v2\Api\DealsApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$filter_id = 56; // int | If supplied, only deals matching the specified filter are returned
$ids = 'ids_example'; // string | Optional comma separated string array of up to 100 entity ids to fetch. If filter_id is provided, this is ignored. If any of the requested entities do not exist or are not visible, they are not included in the response.
$owner_id = 56; // int | If supplied, only deals owned by the specified user are returned. If filter_id is provided, this is ignored.
$person_id = 56; // int | If supplied, only deals linked to the specified person are returned. If filter_id is provided, this is ignored.
$org_id = 56; // int | If supplied, only deals linked to the specified organization are returned. If filter_id is provided, this is ignored.
$pipeline_id = 56; // int | If supplied, only deals in the specified pipeline are returned. If filter_id is provided, this is ignored.
$stage_id = 56; // int | If supplied, only deals in the specified stage are returned. If filter_id is provided, this is ignored.
$status = 'status_example'; // string | Only fetch deals with a specific status. If omitted, all not deleted deals are returned. If set to deleted, deals that have been deleted up to 30 days ago will be included. Multiple statuses can be included as a comma separated array. If filter_id is provided, this is ignored.
$updated_since = 'updated_since_example'; // string | If set, only deals with an `update_time` later than or equal to this time are returned. In RFC3339 format, e.g. 2025-01-01T10:20:00Z.
$updated_until = 'updated_until_example'; // string | If set, only deals with an `update_time` earlier than this time are returned. In RFC3339 format, e.g. 2025-01-01T10:20:00Z.
$sort_by = 'id'; // string | The field to sort by. Supported fields: `id`, `update_time`, `add_time`.
$sort_direction = 'asc'; // string | The sorting direction. Supported values: `asc`, `desc`.
$include_fields = 'include_fields_example'; // string | Optional comma separated string array of additional fields to include
$custom_fields = 'custom_fields_example'; // string | Optional comma separated string array of custom fields keys to include. If you are only interested in a particular set of custom fields, please use this parameter for faster results and smaller response.<br/>A maximum of 15 keys is allowed.
$limit = 100; // int | For pagination, the limit of entries to be returned. If not provided, 100 items will be returned. Please note that a maximum value of 500 is allowed.
$cursor = 'cursor_example'; // string | For pagination, the marker (an opaque string value) representing the first item on the next page

try {
    $result = $apiInstance->getArchivedDeals($filter_id, $ids, $owner_id, $person_id, $org_id, $pipeline_id, $stage_id, $status, $updated_since, $updated_until, $sort_by, $sort_direction, $include_fields, $custom_fields, $limit, $cursor);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling DealsApi->getArchivedDeals: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **filter_id** | **int**| If supplied, only deals matching the specified filter are returned | [optional]
 **ids** | **string**| Optional comma separated string array of up to 100 entity ids to fetch. If filter_id is provided, this is ignored. If any of the requested entities do not exist or are not visible, they are not included in the response. | [optional]
 **owner_id** | **int**| If supplied, only deals owned by the specified user are returned. If filter_id is provided, this is ignored. | [optional]
 **person_id** | **int**| If supplied, only deals linked to the specified person are returned. If filter_id is provided, this is ignored. | [optional]
 **org_id** | **int**| If supplied, only deals linked to the specified organization are returned. If filter_id is provided, this is ignored. | [optional]
 **pipeline_id** | **int**| If supplied, only deals in the specified pipeline are returned. If filter_id is provided, this is ignored. | [optional]
 **stage_id** | **int**| If supplied, only deals in the specified stage are returned. If filter_id is provided, this is ignored. | [optional]
 **status** | **string**| Only fetch deals with a specific status. If omitted, all not deleted deals are returned. If set to deleted, deals that have been deleted up to 30 days ago will be included. Multiple statuses can be included as a comma separated array. If filter_id is provided, this is ignored. | [optional]
 **updated_since** | **string**| If set, only deals with an &#x60;update_time&#x60; later than or equal to this time are returned. In RFC3339 format, e.g. 2025-01-01T10:20:00Z. | [optional]
 **updated_until** | **string**| If set, only deals with an &#x60;update_time&#x60; earlier than this time are returned. In RFC3339 format, e.g. 2025-01-01T10:20:00Z. | [optional]
 **sort_by** | **string**| The field to sort by. Supported fields: &#x60;id&#x60;, &#x60;update_time&#x60;, &#x60;add_time&#x60;. | [optional] [default to &#39;id&#39;]
 **sort_direction** | **string**| The sorting direction. Supported values: &#x60;asc&#x60;, &#x60;desc&#x60;. | [optional] [default to &#39;asc&#39;]
 **include_fields** | **string**| Optional comma separated string array of additional fields to include | [optional]
 **custom_fields** | **string**| Optional comma separated string array of custom fields keys to include. If you are only interested in a particular set of custom fields, please use this parameter for faster results and smaller response.&lt;br/&gt;A maximum of 15 keys is allowed. | [optional]
 **limit** | **int**| For pagination, the limit of entries to be returned. If not provided, 100 items will be returned. Please note that a maximum value of 500 is allowed. | [optional]
 **cursor** | **string**| For pagination, the marker (an opaque string value) representing the first item on the next page | [optional]

### Return type

[**\Pipedrive\versions\v2\Model\GetDeals**](../Model/GetDeals.md)

### Authorization

[api_key](../README.md#api_key), [oauth2](../README.md#oauth2)

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json`

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints)
[[Back to Model list]](../README.md#documentation-for-models)
[[Back to README]](../README.md)

## `getDeal()`

```php
getDeal($id, $include_fields, $custom_fields): \Pipedrive\versions\v2\Model\PostPatchGetDeal
```

Get details of a deal

Returns the details of a specific deal.

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


$apiInstance = new Pipedrive\versions\v2\Api\DealsApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$id = 56; // int | The ID of the deal
$include_fields = 'include_fields_example'; // string | Optional comma separated string array of additional fields to include
$custom_fields = 'custom_fields_example'; // string | Optional comma separated string array of custom fields keys to include. If you are only interested in a particular set of custom fields, please use this parameter for faster results and smaller response.<br/>A maximum of 15 keys is allowed.

try {
    $result = $apiInstance->getDeal($id, $include_fields, $custom_fields);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling DealsApi->getDeal: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **id** | **int**| The ID of the deal |
 **include_fields** | **string**| Optional comma separated string array of additional fields to include | [optional]
 **custom_fields** | **string**| Optional comma separated string array of custom fields keys to include. If you are only interested in a particular set of custom fields, please use this parameter for faster results and smaller response.&lt;br/&gt;A maximum of 15 keys is allowed. | [optional]

### Return type

[**\Pipedrive\versions\v2\Model\PostPatchGetDeal**](../Model/PostPatchGetDeal.md)

### Authorization

[api_key](../README.md#api_key), [oauth2](../README.md#oauth2)

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json`

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints)
[[Back to Model list]](../README.md#documentation-for-models)
[[Back to README]](../README.md)

## `getDealConversionStatus()`

```php
getDealConversionStatus($id, $conversion_id)
```

Get Deal conversion status (BETA)

Returns information about the conversion. Status is always present and its value (not_started, running, completed, failed, rejected) represents the current state of the conversion. Lead ID is only present if the conversion was successfully finished. This data is only temporary and removed after a few days.

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


$apiInstance = new Pipedrive\versions\v2\Api\DealsApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$id = 56; // int | The ID of a deal
$conversion_id = 'conversion_id_example'; // string | The ID of the conversion

try {
    $apiInstance->getDealConversionStatus($id, $conversion_id);
} catch (Exception $e) {
    echo 'Exception when calling DealsApi->getDealConversionStatus: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **id** | **int**| The ID of a deal |
 **conversion_id** | **string**| The ID of the conversion |

### Return type

void (empty response body)

### Authorization

[api_key](../README.md#api_key), [oauth2](../README.md#oauth2)

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json`

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints)
[[Back to Model list]](../README.md#documentation-for-models)
[[Back to README]](../README.md)

## `getDealFollowers()`

```php
getDealFollowers($id, $limit, $cursor): \Pipedrive\versions\v2\Model\GetFollowers
```

List followers of a deal

Lists users who are following the deal.

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


$apiInstance = new Pipedrive\versions\v2\Api\DealsApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$id = 56; // int | The ID of the deal
$limit = 100; // int | For pagination, the limit of entries to be returned. If not provided, 100 items will be returned. Please note that a maximum value of 500 is allowed.
$cursor = 'cursor_example'; // string | For pagination, the marker (an opaque string value) representing the first item on the next page

try {
    $result = $apiInstance->getDealFollowers($id, $limit, $cursor);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling DealsApi->getDealFollowers: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **id** | **int**| The ID of the deal |
 **limit** | **int**| For pagination, the limit of entries to be returned. If not provided, 100 items will be returned. Please note that a maximum value of 500 is allowed. | [optional]
 **cursor** | **string**| For pagination, the marker (an opaque string value) representing the first item on the next page | [optional]

### Return type

[**\Pipedrive\versions\v2\Model\GetFollowers**](../Model/GetFollowers.md)

### Authorization

[api_key](../README.md#api_key), [oauth2](../README.md#oauth2)

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json`

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints)
[[Back to Model list]](../README.md#documentation-for-models)
[[Back to README]](../README.md)

## `getDealFollowersChangelog()`

```php
getDealFollowersChangelog($id, $limit, $cursor): \Pipedrive\versions\v2\Model\GetFollowerChangelogs
```

List followers changelog of a deal

Lists changelogs about users have followed the deal.

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


$apiInstance = new Pipedrive\versions\v2\Api\DealsApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$id = 56; // int | The ID of the deal
$limit = 100; // int | For pagination, the limit of entries to be returned. If not provided, 100 items will be returned. Please note that a maximum value of 500 is allowed.
$cursor = 'cursor_example'; // string | For pagination, the marker (an opaque string value) representing the first item on the next page

try {
    $result = $apiInstance->getDealFollowersChangelog($id, $limit, $cursor);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling DealsApi->getDealFollowersChangelog: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **id** | **int**| The ID of the deal |
 **limit** | **int**| For pagination, the limit of entries to be returned. If not provided, 100 items will be returned. Please note that a maximum value of 500 is allowed. | [optional]
 **cursor** | **string**| For pagination, the marker (an opaque string value) representing the first item on the next page | [optional]

### Return type

[**\Pipedrive\versions\v2\Model\GetFollowerChangelogs**](../Model/GetFollowerChangelogs.md)

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


$apiInstance = new Pipedrive\versions\v2\Api\DealsApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$id = 56; // int | The ID of the deal
$cursor = 'cursor_example'; // string | For pagination, the marker (an opaque string value) representing the first item on the next page
$limit = 100; // int | For pagination, the limit of entries to be returned. If not provided, 100 items will be returned. Please note that a maximum value of 500 is allowed.
$sort_by = 'id'; // string | The field to sort by. Supported fields: `id`, `add_time`, `update_time`.
$sort_direction = 'asc'; // string | The sorting direction. Supported values: `asc`, `desc`.

try {
    $result = $apiInstance->getDealProducts($id, $cursor, $limit, $sort_by, $sort_direction);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling DealsApi->getDealProducts: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **id** | **int**| The ID of the deal |
 **cursor** | **string**| For pagination, the marker (an opaque string value) representing the first item on the next page | [optional]
 **limit** | **int**| For pagination, the limit of entries to be returned. If not provided, 100 items will be returned. Please note that a maximum value of 500 is allowed. | [optional]
 **sort_by** | **string**| The field to sort by. Supported fields: &#x60;id&#x60;, &#x60;add_time&#x60;, &#x60;update_time&#x60;. | [optional] [default to &#39;id&#39;]
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

## `getDeals()`

```php
getDeals($filter_id, $ids, $owner_id, $person_id, $org_id, $pipeline_id, $stage_id, $status, $updated_since, $updated_until, $sort_by, $sort_direction, $include_fields, $custom_fields, $limit, $cursor): \Pipedrive\versions\v2\Model\GetDeals
```

Get all deals

Returns data about all not archived deals.

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


$apiInstance = new Pipedrive\versions\v2\Api\DealsApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$filter_id = 56; // int | If supplied, only deals matching the specified filter are returned
$ids = 'ids_example'; // string | Optional comma separated string array of up to 100 entity ids to fetch. If filter_id is provided, this is ignored. If any of the requested entities do not exist or are not visible, they are not included in the response.
$owner_id = 56; // int | If supplied, only deals owned by the specified user are returned. If filter_id is provided, this is ignored.
$person_id = 56; // int | If supplied, only deals linked to the specified person are returned. If filter_id is provided, this is ignored.
$org_id = 56; // int | If supplied, only deals linked to the specified organization are returned. If filter_id is provided, this is ignored.
$pipeline_id = 56; // int | If supplied, only deals in the specified pipeline are returned. If filter_id is provided, this is ignored.
$stage_id = 56; // int | If supplied, only deals in the specified stage are returned. If filter_id is provided, this is ignored.
$status = 'status_example'; // string | Only fetch deals with a specific status. If omitted, all not deleted deals are returned. If set to deleted, deals that have been deleted up to 30 days ago will be included. Multiple statuses can be included as a comma separated array. If filter_id is provided, this is ignored.
$updated_since = 'updated_since_example'; // string | If set, only deals with an `update_time` later than or equal to this time are returned. In RFC3339 format, e.g. 2025-01-01T10:20:00Z.
$updated_until = 'updated_until_example'; // string | If set, only deals with an `update_time` earlier than this time are returned. In RFC3339 format, e.g. 2025-01-01T10:20:00Z.
$sort_by = 'id'; // string | The field to sort by. Supported fields: `id`, `update_time`, `add_time`.
$sort_direction = 'asc'; // string | The sorting direction. Supported values: `asc`, `desc`.
$include_fields = 'include_fields_example'; // string | Optional comma separated string array of additional fields to include
$custom_fields = 'custom_fields_example'; // string | Optional comma separated string array of custom fields keys to include. If you are only interested in a particular set of custom fields, please use this parameter for faster results and smaller response.<br/>A maximum of 15 keys is allowed.
$limit = 100; // int | For pagination, the limit of entries to be returned. If not provided, 100 items will be returned. Please note that a maximum value of 500 is allowed.
$cursor = 'cursor_example'; // string | For pagination, the marker (an opaque string value) representing the first item on the next page

try {
    $result = $apiInstance->getDeals($filter_id, $ids, $owner_id, $person_id, $org_id, $pipeline_id, $stage_id, $status, $updated_since, $updated_until, $sort_by, $sort_direction, $include_fields, $custom_fields, $limit, $cursor);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling DealsApi->getDeals: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **filter_id** | **int**| If supplied, only deals matching the specified filter are returned | [optional]
 **ids** | **string**| Optional comma separated string array of up to 100 entity ids to fetch. If filter_id is provided, this is ignored. If any of the requested entities do not exist or are not visible, they are not included in the response. | [optional]
 **owner_id** | **int**| If supplied, only deals owned by the specified user are returned. If filter_id is provided, this is ignored. | [optional]
 **person_id** | **int**| If supplied, only deals linked to the specified person are returned. If filter_id is provided, this is ignored. | [optional]
 **org_id** | **int**| If supplied, only deals linked to the specified organization are returned. If filter_id is provided, this is ignored. | [optional]
 **pipeline_id** | **int**| If supplied, only deals in the specified pipeline are returned. If filter_id is provided, this is ignored. | [optional]
 **stage_id** | **int**| If supplied, only deals in the specified stage are returned. If filter_id is provided, this is ignored. | [optional]
 **status** | **string**| Only fetch deals with a specific status. If omitted, all not deleted deals are returned. If set to deleted, deals that have been deleted up to 30 days ago will be included. Multiple statuses can be included as a comma separated array. If filter_id is provided, this is ignored. | [optional]
 **updated_since** | **string**| If set, only deals with an &#x60;update_time&#x60; later than or equal to this time are returned. In RFC3339 format, e.g. 2025-01-01T10:20:00Z. | [optional]
 **updated_until** | **string**| If set, only deals with an &#x60;update_time&#x60; earlier than this time are returned. In RFC3339 format, e.g. 2025-01-01T10:20:00Z. | [optional]
 **sort_by** | **string**| The field to sort by. Supported fields: &#x60;id&#x60;, &#x60;update_time&#x60;, &#x60;add_time&#x60;. | [optional] [default to &#39;id&#39;]
 **sort_direction** | **string**| The sorting direction. Supported values: &#x60;asc&#x60;, &#x60;desc&#x60;. | [optional] [default to &#39;asc&#39;]
 **include_fields** | **string**| Optional comma separated string array of additional fields to include | [optional]
 **custom_fields** | **string**| Optional comma separated string array of custom fields keys to include. If you are only interested in a particular set of custom fields, please use this parameter for faster results and smaller response.&lt;br/&gt;A maximum of 15 keys is allowed. | [optional]
 **limit** | **int**| For pagination, the limit of entries to be returned. If not provided, 100 items will be returned. Please note that a maximum value of 500 is allowed. | [optional]
 **cursor** | **string**| For pagination, the marker (an opaque string value) representing the first item on the next page | [optional]

### Return type

[**\Pipedrive\versions\v2\Model\GetDeals**](../Model/GetDeals.md)

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


$apiInstance = new Pipedrive\versions\v2\Api\DealsApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$deal_ids = array(56); // int[] | An array of integers with the IDs of the deals for which the attached products will be returned. A maximum of 100 deal IDs can be provided.
$cursor = 'cursor_example'; // string | For pagination, the marker (an opaque string value) representing the first item on the next page
$limit = 100; // int | For pagination, the limit of entries to be returned. If not provided, 100 items will be returned. Please note that a maximum value of 500 is allowed.
$sort_by = 'id'; // string | The field to sort by. Supported fields: `id`, `deal_id`, `add_time`, `update_time`.
$sort_direction = 'asc'; // string | The sorting direction. Supported values: `asc`, `desc`.

try {
    $result = $apiInstance->getDealsProducts($deal_ids, $cursor, $limit, $sort_by, $sort_direction);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling DealsApi->getDealsProducts: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **deal_ids** | [**int[]**](../Model/int.md)| An array of integers with the IDs of the deals for which the attached products will be returned. A maximum of 100 deal IDs can be provided. |
 **cursor** | **string**| For pagination, the marker (an opaque string value) representing the first item on the next page | [optional]
 **limit** | **int**| For pagination, the limit of entries to be returned. If not provided, 100 items will be returned. Please note that a maximum value of 500 is allowed. | [optional]
 **sort_by** | **string**| The field to sort by. Supported fields: &#x60;id&#x60;, &#x60;deal_id&#x60;, &#x60;add_time&#x60;, &#x60;update_time&#x60;. | [optional] [default to &#39;id&#39;]
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

## `getInstallments()`

```php
getInstallments($deal_ids, $cursor, $limit, $sort_by, $sort_direction): \Pipedrive\versions\v2\Model\InstallmentsResponse
```

List installments added to a list of deals

Lists installments attached to a list of deals.  Only available in Advanced and above plans.

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


$apiInstance = new Pipedrive\versions\v2\Api\DealsApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$deal_ids = array(56); // int[] | An array of integers with the IDs of the deals for which the attached installments will be returned. A maximum of 100 deal IDs can be provided.
$cursor = 'cursor_example'; // string | For pagination, the marker (an opaque string value) representing the first item on the next page
$limit = 100; // int | For pagination, the limit of entries to be returned. If not provided, 100 items will be returned. Please note that a maximum value of 500 is allowed.
$sort_by = 'id'; // string | The field to sort by. Supported fields: `id`, `billing_date`, `deal_id`.
$sort_direction = 'asc'; // string | The sorting direction. Supported values: `asc`, `desc`.

try {
    $result = $apiInstance->getInstallments($deal_ids, $cursor, $limit, $sort_by, $sort_direction);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling DealsApi->getInstallments: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **deal_ids** | [**int[]**](../Model/int.md)| An array of integers with the IDs of the deals for which the attached installments will be returned. A maximum of 100 deal IDs can be provided. |
 **cursor** | **string**| For pagination, the marker (an opaque string value) representing the first item on the next page | [optional]
 **limit** | **int**| For pagination, the limit of entries to be returned. If not provided, 100 items will be returned. Please note that a maximum value of 500 is allowed. | [optional]
 **sort_by** | **string**| The field to sort by. Supported fields: &#x60;id&#x60;, &#x60;billing_date&#x60;, &#x60;deal_id&#x60;. | [optional] [default to &#39;id&#39;]
 **sort_direction** | **string**| The sorting direction. Supported values: &#x60;asc&#x60;, &#x60;desc&#x60;. | [optional] [default to &#39;asc&#39;]

### Return type

[**\Pipedrive\versions\v2\Model\InstallmentsResponse**](../Model/InstallmentsResponse.md)

### Authorization

[api_key](../README.md#api_key), [oauth2](../README.md#oauth2)

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json`

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints)
[[Back to Model list]](../README.md#documentation-for-models)
[[Back to README]](../README.md)

## `postAdditionalDiscount()`

```php
postAdditionalDiscount($id, $body): \Pipedrive\versions\v2\Model\AddAdditionalDiscountResponse
```

Add a discount to a deal

Adds a discount to a deal changing, the deal value if the deal has one-time products attached.

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


$apiInstance = new Pipedrive\versions\v2\Api\DealsApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$id = 56; // int | The ID of the deal
$body = new \stdClass; // object

try {
    $result = $apiInstance->postAdditionalDiscount($id, $body);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling DealsApi->postAdditionalDiscount: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **id** | **int**| The ID of the deal |
 **body** | **object**|  | [optional]

### Return type

[**\Pipedrive\versions\v2\Model\AddAdditionalDiscountResponse**](../Model/AddAdditionalDiscountResponse.md)

### Authorization

[api_key](../README.md#api_key), [oauth2](../README.md#oauth2)

### HTTP request headers

- **Content-Type**: `application/json`
- **Accept**: `application/json`

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints)
[[Back to Model list]](../README.md#documentation-for-models)
[[Back to README]](../README.md)

## `postInstallment()`

```php
postInstallment($id, $body): \Pipedrive\versions\v2\Model\AddInstallmentResponse
```

Add an installment to a deal

Adds an installment to a deal.  An installment can only be added if the deal includes at least one one-time product.  If the deal contains at least one recurring product, adding installments is not allowed.  Only available in Advanced and above plans.

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


$apiInstance = new Pipedrive\versions\v2\Api\DealsApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$id = 56; // int | The ID of the deal
$body = new \stdClass; // object

try {
    $result = $apiInstance->postInstallment($id, $body);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling DealsApi->postInstallment: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **id** | **int**| The ID of the deal |
 **body** | **object**|  | [optional]

### Return type

[**\Pipedrive\versions\v2\Model\AddInstallmentResponse**](../Model/AddInstallmentResponse.md)

### Authorization

[api_key](../README.md#api_key), [oauth2](../README.md#oauth2)

### HTTP request headers

- **Content-Type**: `application/json`
- **Accept**: `application/json`

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints)
[[Back to Model list]](../README.md#documentation-for-models)
[[Back to README]](../README.md)

## `searchDeals()`

```php
searchDeals($term, $fields, $exact_match, $person_id, $organization_id, $status, $include_fields, $limit, $cursor): \Pipedrive\versions\v2\Model\GetDealSearchResponse
```

Search deals

Searches all deals by title, notes and/or custom fields. This endpoint is a wrapper of <a href=\"https://developers.pipedrive.com/docs/api/v1/ItemSearch#searchItem\">/v1/itemSearch</a> with a narrower OAuth scope. Found deals can be filtered by the person ID and the organization ID.

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


$apiInstance = new Pipedrive\versions\v2\Api\DealsApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$term = 'term_example'; // string | The search term to look for. Minimum 2 characters (or 1 if using `exact_match`). Please note that the search term has to be URL encoded.
$fields = 'fields_example'; // string | A comma-separated string array. The fields to perform the search from. Defaults to all of them. Only the following custom field types are searchable: `address`, `varchar`, `text`, `varchar_auto`, `double`, `monetary` and `phone`. Read more about searching by custom fields <a href=\"https://support.pipedrive.com/en/article/search-finding-what-you-need#searching-by-custom-fields\" target=\"_blank\" rel=\"noopener noreferrer\">here</a>.
$exact_match = True; // bool | When enabled, only full exact matches against the given term are returned. It is <b>not</b> case sensitive.
$person_id = 56; // int | Will filter deals by the provided person ID. The upper limit of found deals associated with the person is 2000.
$organization_id = 56; // int | Will filter deals by the provided organization ID. The upper limit of found deals associated with the organization is 2000.
$status = 'status_example'; // string | Will filter deals by the provided specific status. open = Open, won = Won, lost = Lost. The upper limit of found deals associated with the status is 2000.
$include_fields = 'include_fields_example'; // string | Supports including optional fields in the results which are not provided by default
$limit = 100; // int | For pagination, the limit of entries to be returned. If not provided, 100 items will be returned. Please note that a maximum value of 500 is allowed.
$cursor = 'cursor_example'; // string | For pagination, the marker (an opaque string value) representing the first item on the next page

try {
    $result = $apiInstance->searchDeals($term, $fields, $exact_match, $person_id, $organization_id, $status, $include_fields, $limit, $cursor);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling DealsApi->searchDeals: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **term** | **string**| The search term to look for. Minimum 2 characters (or 1 if using &#x60;exact_match&#x60;). Please note that the search term has to be URL encoded. |
 **fields** | **string**| A comma-separated string array. The fields to perform the search from. Defaults to all of them. Only the following custom field types are searchable: &#x60;address&#x60;, &#x60;varchar&#x60;, &#x60;text&#x60;, &#x60;varchar_auto&#x60;, &#x60;double&#x60;, &#x60;monetary&#x60; and &#x60;phone&#x60;. Read more about searching by custom fields &lt;a href&#x3D;\&quot;https://support.pipedrive.com/en/article/search-finding-what-you-need#searching-by-custom-fields\&quot; target&#x3D;\&quot;_blank\&quot; rel&#x3D;\&quot;noopener noreferrer\&quot;&gt;here&lt;/a&gt;. | [optional]
 **exact_match** | **bool**| When enabled, only full exact matches against the given term are returned. It is &lt;b&gt;not&lt;/b&gt; case sensitive. | [optional]
 **person_id** | **int**| Will filter deals by the provided person ID. The upper limit of found deals associated with the person is 2000. | [optional]
 **organization_id** | **int**| Will filter deals by the provided organization ID. The upper limit of found deals associated with the organization is 2000. | [optional]
 **status** | **string**| Will filter deals by the provided specific status. open &#x3D; Open, won &#x3D; Won, lost &#x3D; Lost. The upper limit of found deals associated with the status is 2000. | [optional]
 **include_fields** | **string**| Supports including optional fields in the results which are not provided by default | [optional]
 **limit** | **int**| For pagination, the limit of entries to be returned. If not provided, 100 items will be returned. Please note that a maximum value of 500 is allowed. | [optional]
 **cursor** | **string**| For pagination, the marker (an opaque string value) representing the first item on the next page | [optional]

### Return type

[**\Pipedrive\versions\v2\Model\GetDealSearchResponse**](../Model/GetDealSearchResponse.md)

### Authorization

[api_key](../README.md#api_key), [oauth2](../README.md#oauth2)

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json`

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints)
[[Back to Model list]](../README.md#documentation-for-models)
[[Back to README]](../README.md)

## `updateAdditionalDiscount()`

```php
updateAdditionalDiscount($id, $discount_id, $body): \Pipedrive\versions\v2\Model\UpdateAdditionalDiscountResponse
```

Update a discount added to a deal

Edits a discount added to a deal, changing the deal value if the deal has one-time products attached.

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


$apiInstance = new Pipedrive\versions\v2\Api\DealsApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$id = 56; // int | The ID of the deal
$discount_id = 56; // int | The ID of the discount
$body = new \stdClass; // object

try {
    $result = $apiInstance->updateAdditionalDiscount($id, $discount_id, $body);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling DealsApi->updateAdditionalDiscount: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **id** | **int**| The ID of the deal |
 **discount_id** | **int**| The ID of the discount |
 **body** | **object**|  | [optional]

### Return type

[**\Pipedrive\versions\v2\Model\UpdateAdditionalDiscountResponse**](../Model/UpdateAdditionalDiscountResponse.md)

### Authorization

[api_key](../README.md#api_key), [oauth2](../README.md#oauth2)

### HTTP request headers

- **Content-Type**: `application/json`
- **Accept**: `application/json`

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints)
[[Back to Model list]](../README.md#documentation-for-models)
[[Back to README]](../README.md)

## `updateDeal()`

```php
updateDeal($id, $deal_request_body): \Pipedrive\versions\v2\Model\PostPatchGetDeal
```

Update a deal

Updates the properties of a deal.

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


$apiInstance = new Pipedrive\versions\v2\Api\DealsApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$id = 56; // int | The ID of the deal
$deal_request_body = new \Pipedrive\versions\v2\Model\DealRequestBody(); // \Pipedrive\versions\v2\Model\DealRequestBody

try {
    $result = $apiInstance->updateDeal($id, $deal_request_body);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling DealsApi->updateDeal: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **id** | **int**| The ID of the deal |
 **deal_request_body** | [**\Pipedrive\versions\v2\Model\DealRequestBody**](../Model/DealRequestBody.md)|  | [optional]

### Return type

[**\Pipedrive\versions\v2\Model\PostPatchGetDeal**](../Model/PostPatchGetDeal.md)

### Authorization

[api_key](../README.md#api_key), [oauth2](../README.md#oauth2)

### HTTP request headers

- **Content-Type**: `application/json`
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


$apiInstance = new Pipedrive\versions\v2\Api\DealsApi(
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
    echo 'Exception when calling DealsApi->updateDealProduct: ', $e->getMessage(), PHP_EOL;
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

## `updateInstallment()`

```php
updateInstallment($id, $installment_id, $body): \Pipedrive\versions\v2\Model\UpdateInstallmentResponse
```

Update an installment added to a deal

Edits an installment added to a deal.  Only available in Advanced and above plans.

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


$apiInstance = new Pipedrive\versions\v2\Api\DealsApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$id = 56; // int | The ID of the deal
$installment_id = 56; // int | The ID of the installment
$body = new \stdClass; // object

try {
    $result = $apiInstance->updateInstallment($id, $installment_id, $body);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling DealsApi->updateInstallment: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **id** | **int**| The ID of the deal |
 **installment_id** | **int**| The ID of the installment |
 **body** | **object**|  | [optional]

### Return type

[**\Pipedrive\versions\v2\Model\UpdateInstallmentResponse**](../Model/UpdateInstallmentResponse.md)

### Authorization

[api_key](../README.md#api_key), [oauth2](../README.md#oauth2)

### HTTP request headers

- **Content-Type**: `application/json`
- **Accept**: `application/json`

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints)
[[Back to Model list]](../README.md#documentation-for-models)
[[Back to README]](../README.md)
