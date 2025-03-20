# Pipedrive\versions\v2\BetaApi

All URIs are relative to https://api.pipedrive.com/api/v2.

Method | HTTP request | Description
------------- | ------------- | -------------
[**addActivity()**](BetaApi.md#addActivity) | **POST** /activities | Add a new activity
[**addDeal()**](BetaApi.md#addDeal) | **POST** /deals | Add a new deal
[**addDealProduct()**](BetaApi.md#addDealProduct) | **POST** /deals/{id}/products | Add a product to a deal
[**addOrganization()**](BetaApi.md#addOrganization) | **POST** /organizations | Add a new organization
[**addPerson()**](BetaApi.md#addPerson) | **POST** /persons | Add a new person
[**deleteActivity()**](BetaApi.md#deleteActivity) | **DELETE** /activities/{id} | Delete an activity
[**deleteAdditionalDiscount()**](BetaApi.md#deleteAdditionalDiscount) | **DELETE** /deals/{id}/discounts/{discount_id} | Delete a discount from a deal
[**deleteDeal()**](BetaApi.md#deleteDeal) | **DELETE** /deals/{id} | Delete a deal
[**deleteDealProduct()**](BetaApi.md#deleteDealProduct) | **DELETE** /deals/{id}/products/{product_attachment_id} | Delete an attached product from a deal
[**deleteInstallment()**](BetaApi.md#deleteInstallment) | **DELETE** /deals/{id}/installments/{installment_id} | Delete an installment from a deal
[**deleteOrganization()**](BetaApi.md#deleteOrganization) | **DELETE** /organizations/{id} | Delete a organization
[**deletePerson()**](BetaApi.md#deletePerson) | **DELETE** /persons/{id} | Delete a person
[**getActivities()**](BetaApi.md#getActivities) | **GET** /activities | Get all activities
[**getActivity()**](BetaApi.md#getActivity) | **GET** /activities/{id} | Get details of an activity
[**getAdditionalDiscounts()**](BetaApi.md#getAdditionalDiscounts) | **GET** /deals/{id}/discounts | List discounts added to a deal
[**getDeal()**](BetaApi.md#getDeal) | **GET** /deals/{id} | Get details of a deal
[**getDealProducts()**](BetaApi.md#getDealProducts) | **GET** /deals/{id}/products | List products attached to a deal
[**getDeals()**](BetaApi.md#getDeals) | **GET** /deals | Get all deals
[**getDealsProducts()**](BetaApi.md#getDealsProducts) | **GET** /deals/products | Get deal products of several deals
[**getInstallments()**](BetaApi.md#getInstallments) | **GET** /deals/installments | List installments added to a list of deals
[**getOrganization()**](BetaApi.md#getOrganization) | **GET** /organizations/{id} | Get details of a organization
[**getOrganizations()**](BetaApi.md#getOrganizations) | **GET** /organizations | Get all organizations
[**getPerson()**](BetaApi.md#getPerson) | **GET** /persons/{id} | Get details of a person
[**getPersons()**](BetaApi.md#getPersons) | **GET** /persons | Get all persons
[**postAdditionalDiscount()**](BetaApi.md#postAdditionalDiscount) | **POST** /deals/{id}/discounts | Add a discount to a deal
[**postInstallment()**](BetaApi.md#postInstallment) | **POST** /deals/{id}/installments | Add an installment to a deal
[**searchDeals()**](BetaApi.md#searchDeals) | **GET** /deals/search | Search deals
[**searchItem()**](BetaApi.md#searchItem) | **GET** /itemSearch | Perform a search from multiple item types
[**searchItemByField()**](BetaApi.md#searchItemByField) | **GET** /itemSearch/field | Perform a search using a specific field from an item type
[**searchLeads()**](BetaApi.md#searchLeads) | **GET** /leads/search | Search leads
[**searchOrganization()**](BetaApi.md#searchOrganization) | **GET** /organizations/search | Search organizations
[**searchPersons()**](BetaApi.md#searchPersons) | **GET** /persons/search | Search persons
[**updateActivity()**](BetaApi.md#updateActivity) | **PATCH** /activities/{id} | Update an activity
[**updateAdditionalDiscount()**](BetaApi.md#updateAdditionalDiscount) | **PATCH** /deals/{id}/discounts/{discount_id} | Update a discount added to a deal
[**updateDeal()**](BetaApi.md#updateDeal) | **PATCH** /deals/{id} | Update a deal
[**updateDealProduct()**](BetaApi.md#updateDealProduct) | **PATCH** /deals/{id}/products/{product_attachment_id} | Update the product attached to a deal
[**updateInstallment()**](BetaApi.md#updateInstallment) | **PATCH** /deals/{id}/installments/{installment_id} | Update an installment added to a deal
[**updateOrganization()**](BetaApi.md#updateOrganization) | **PATCH** /organizations/{id} | Update a organization
[**updatePerson()**](BetaApi.md#updatePerson) | **PATCH** /persons/{id} | Update a person


## `addActivity()`

```php
addActivity($activity_request_body): \Pipedrive\versions\v2\Model\PostPatchGetActivity
```

Add a new activity

Adds a new activity.

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');


// Configure API key authorization: api_key
$config = (new Pipedrive\versions\v2\Configuration())->setApiKey('api_token', 'YOUR_API_KEY');
// Uncomment below to setup prefix (e.g. Bearer) for API key, if needed
// $config = (new Pipedrive\versions\v2\Configuration())->setApiKeyPrefix('api_token', 'Bearer');

// Configure OAuth2 access token for authorization: oauth2
$config = (new Pipedrive\versions\v2\Configuration())->setAccessToken('YOUR_ACCESS_TOKEN');


$apiInstance = new Pipedrive\versions\v2\Api\BetaApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$activity_request_body = new \Pipedrive\versions\v2\Model\ActivityRequestBody(); // \Pipedrive\versions\v2\Model\ActivityRequestBody

try {
    $result = $apiInstance->addActivity($activity_request_body);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling BetaApi->addActivity: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **activity_request_body** | [**\Pipedrive\versions\v2\Model\ActivityRequestBody**](../Model/ActivityRequestBody.md)|  | [optional]

### Return type

[**\Pipedrive\versions\v2\Model\PostPatchGetActivity**](../Model/PostPatchGetActivity.md)

### Authorization

[api_key](../README.md#api_key), [oauth2](../README.md#oauth2)

### HTTP request headers

- **Content-Type**: `application/json`
- **Accept**: `application/json`

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints)
[[Back to Model list]](../README.md#documentation-for-models)
[[Back to README]](../README.md)

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
$config = (new Pipedrive\versions\v2\Configuration())->setApiKey('api_token', 'YOUR_API_KEY');
// Uncomment below to setup prefix (e.g. Bearer) for API key, if needed
// $config = (new Pipedrive\versions\v2\Configuration())->setApiKeyPrefix('api_token', 'Bearer');

// Configure OAuth2 access token for authorization: oauth2
$config = (new Pipedrive\versions\v2\Configuration())->setAccessToken('YOUR_ACCESS_TOKEN');


$apiInstance = new Pipedrive\versions\v2\Api\BetaApi(
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
    echo 'Exception when calling BetaApi->addDeal: ', $e->getMessage(), PHP_EOL;
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
$config = (new Pipedrive\versions\v2\Configuration())->setApiKey('api_token', 'YOUR_API_KEY');
// Uncomment below to setup prefix (e.g. Bearer) for API key, if needed
// $config = (new Pipedrive\versions\v2\Configuration())->setApiKeyPrefix('api_token', 'Bearer');

// Configure OAuth2 access token for authorization: oauth2
$config = (new Pipedrive\versions\v2\Configuration())->setAccessToken('YOUR_ACCESS_TOKEN');


$apiInstance = new Pipedrive\versions\v2\Api\BetaApi(
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
    echo 'Exception when calling BetaApi->addDealProduct: ', $e->getMessage(), PHP_EOL;
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

## `addOrganization()`

```php
addOrganization($organization_request_body): \Pipedrive\versions\v2\Model\PostPatchGetOrganization
```

Add a new organization

Adds a new organization.

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');


// Configure API key authorization: api_key
$config = (new Pipedrive\versions\v2\Configuration())->setApiKey('api_token', 'YOUR_API_KEY');
// Uncomment below to setup prefix (e.g. Bearer) for API key, if needed
// $config = (new Pipedrive\versions\v2\Configuration())->setApiKeyPrefix('api_token', 'Bearer');

// Configure OAuth2 access token for authorization: oauth2
$config = (new Pipedrive\versions\v2\Configuration())->setAccessToken('YOUR_ACCESS_TOKEN');


$apiInstance = new Pipedrive\versions\v2\Api\BetaApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$organization_request_body = new \Pipedrive\versions\v2\Model\OrganizationRequestBody(); // \Pipedrive\versions\v2\Model\OrganizationRequestBody

try {
    $result = $apiInstance->addOrganization($organization_request_body);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling BetaApi->addOrganization: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **organization_request_body** | [**\Pipedrive\versions\v2\Model\OrganizationRequestBody**](../Model/OrganizationRequestBody.md)|  | [optional]

### Return type

[**\Pipedrive\versions\v2\Model\PostPatchGetOrganization**](../Model/PostPatchGetOrganization.md)

### Authorization

[api_key](../README.md#api_key), [oauth2](../README.md#oauth2)

### HTTP request headers

- **Content-Type**: `application/json`
- **Accept**: `application/json`

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints)
[[Back to Model list]](../README.md#documentation-for-models)
[[Back to README]](../README.md)

## `addPerson()`

```php
addPerson($person_request_body): \Pipedrive\versions\v2\Model\PostPatchGetPerson
```

Add a new person

Adds a new person.

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');


// Configure API key authorization: api_key
$config = (new Pipedrive\versions\v2\Configuration())->setApiKey('api_token', 'YOUR_API_KEY');
// Uncomment below to setup prefix (e.g. Bearer) for API key, if needed
// $config = (new Pipedrive\versions\v2\Configuration())->setApiKeyPrefix('api_token', 'Bearer');

// Configure OAuth2 access token for authorization: oauth2
$config = (new Pipedrive\versions\v2\Configuration())->setAccessToken('YOUR_ACCESS_TOKEN');


$apiInstance = new Pipedrive\versions\v2\Api\BetaApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$person_request_body = new \Pipedrive\versions\v2\Model\PersonRequestBody(); // \Pipedrive\versions\v2\Model\PersonRequestBody

try {
    $result = $apiInstance->addPerson($person_request_body);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling BetaApi->addPerson: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **person_request_body** | [**\Pipedrive\versions\v2\Model\PersonRequestBody**](../Model/PersonRequestBody.md)|  | [optional]

### Return type

[**\Pipedrive\versions\v2\Model\PostPatchGetPerson**](../Model/PostPatchGetPerson.md)

### Authorization

[api_key](../README.md#api_key), [oauth2](../README.md#oauth2)

### HTTP request headers

- **Content-Type**: `application/json`
- **Accept**: `application/json`

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints)
[[Back to Model list]](../README.md#documentation-for-models)
[[Back to README]](../README.md)

## `deleteActivity()`

```php
deleteActivity($id): \Pipedrive\versions\v2\Model\DeleteActivityResponse
```

Delete an activity

Marks an activity as deleted. After 30 days, the activity will be permanently deleted.

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');


// Configure API key authorization: api_key
$config = (new Pipedrive\versions\v2\Configuration())->setApiKey('api_token', 'YOUR_API_KEY');
// Uncomment below to setup prefix (e.g. Bearer) for API key, if needed
// $config = (new Pipedrive\versions\v2\Configuration())->setApiKeyPrefix('api_token', 'Bearer');

// Configure OAuth2 access token for authorization: oauth2
$config = (new Pipedrive\versions\v2\Configuration())->setAccessToken('YOUR_ACCESS_TOKEN');


$apiInstance = new Pipedrive\versions\v2\Api\BetaApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$id = 56; // int | The ID of the activity

try {
    $result = $apiInstance->deleteActivity($id);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling BetaApi->deleteActivity: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **id** | **int**| The ID of the activity |

### Return type

[**\Pipedrive\versions\v2\Model\DeleteActivityResponse**](../Model/DeleteActivityResponse.md)

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
$config = (new Pipedrive\versions\v2\Configuration())->setApiKey('api_token', 'YOUR_API_KEY');
// Uncomment below to setup prefix (e.g. Bearer) for API key, if needed
// $config = (new Pipedrive\versions\v2\Configuration())->setApiKeyPrefix('api_token', 'Bearer');

// Configure OAuth2 access token for authorization: oauth2
$config = (new Pipedrive\versions\v2\Configuration())->setAccessToken('YOUR_ACCESS_TOKEN');


$apiInstance = new Pipedrive\versions\v2\Api\BetaApi(
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
    echo 'Exception when calling BetaApi->deleteAdditionalDiscount: ', $e->getMessage(), PHP_EOL;
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
$config = (new Pipedrive\versions\v2\Configuration())->setApiKey('api_token', 'YOUR_API_KEY');
// Uncomment below to setup prefix (e.g. Bearer) for API key, if needed
// $config = (new Pipedrive\versions\v2\Configuration())->setApiKeyPrefix('api_token', 'Bearer');

// Configure OAuth2 access token for authorization: oauth2
$config = (new Pipedrive\versions\v2\Configuration())->setAccessToken('YOUR_ACCESS_TOKEN');


$apiInstance = new Pipedrive\versions\v2\Api\BetaApi(
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
    echo 'Exception when calling BetaApi->deleteDeal: ', $e->getMessage(), PHP_EOL;
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
$config = (new Pipedrive\versions\v2\Configuration())->setApiKey('api_token', 'YOUR_API_KEY');
// Uncomment below to setup prefix (e.g. Bearer) for API key, if needed
// $config = (new Pipedrive\versions\v2\Configuration())->setApiKeyPrefix('api_token', 'Bearer');

// Configure OAuth2 access token for authorization: oauth2
$config = (new Pipedrive\versions\v2\Configuration())->setAccessToken('YOUR_ACCESS_TOKEN');


$apiInstance = new Pipedrive\versions\v2\Api\BetaApi(
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
    echo 'Exception when calling BetaApi->deleteDealProduct: ', $e->getMessage(), PHP_EOL;
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
$config = (new Pipedrive\versions\v2\Configuration())->setApiKey('api_token', 'YOUR_API_KEY');
// Uncomment below to setup prefix (e.g. Bearer) for API key, if needed
// $config = (new Pipedrive\versions\v2\Configuration())->setApiKeyPrefix('api_token', 'Bearer');

// Configure OAuth2 access token for authorization: oauth2
$config = (new Pipedrive\versions\v2\Configuration())->setAccessToken('YOUR_ACCESS_TOKEN');


$apiInstance = new Pipedrive\versions\v2\Api\BetaApi(
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
    echo 'Exception when calling BetaApi->deleteInstallment: ', $e->getMessage(), PHP_EOL;
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

## `deleteOrganization()`

```php
deleteOrganization($id): \Pipedrive\versions\v2\Model\DeleteOrganizationResponse
```

Delete a organization

Marks a organization as deleted. After 30 days, the organization will be permanently deleted.

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');


// Configure API key authorization: api_key
$config = (new Pipedrive\versions\v2\Configuration())->setApiKey('api_token', 'YOUR_API_KEY');
// Uncomment below to setup prefix (e.g. Bearer) for API key, if needed
// $config = (new Pipedrive\versions\v2\Configuration())->setApiKeyPrefix('api_token', 'Bearer');

// Configure OAuth2 access token for authorization: oauth2
$config = (new Pipedrive\versions\v2\Configuration())->setAccessToken('YOUR_ACCESS_TOKEN');


$apiInstance = new Pipedrive\versions\v2\Api\BetaApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$id = 56; // int | The ID of the organization

try {
    $result = $apiInstance->deleteOrganization($id);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling BetaApi->deleteOrganization: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **id** | **int**| The ID of the organization |

### Return type

[**\Pipedrive\versions\v2\Model\DeleteOrganizationResponse**](../Model/DeleteOrganizationResponse.md)

### Authorization

[api_key](../README.md#api_key), [oauth2](../README.md#oauth2)

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json`

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints)
[[Back to Model list]](../README.md#documentation-for-models)
[[Back to README]](../README.md)

## `deletePerson()`

```php
deletePerson($id): \Pipedrive\versions\v2\Model\DeletePersonResponse
```

Delete a person

Marks a person as deleted. After 30 days, the person will be permanently deleted.

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');


// Configure API key authorization: api_key
$config = (new Pipedrive\versions\v2\Configuration())->setApiKey('api_token', 'YOUR_API_KEY');
// Uncomment below to setup prefix (e.g. Bearer) for API key, if needed
// $config = (new Pipedrive\versions\v2\Configuration())->setApiKeyPrefix('api_token', 'Bearer');

// Configure OAuth2 access token for authorization: oauth2
$config = (new Pipedrive\versions\v2\Configuration())->setAccessToken('YOUR_ACCESS_TOKEN');


$apiInstance = new Pipedrive\versions\v2\Api\BetaApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$id = 56; // int | The ID of the person

try {
    $result = $apiInstance->deletePerson($id);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling BetaApi->deletePerson: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **id** | **int**| The ID of the person |

### Return type

[**\Pipedrive\versions\v2\Model\DeletePersonResponse**](../Model/DeletePersonResponse.md)

### Authorization

[api_key](../README.md#api_key), [oauth2](../README.md#oauth2)

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json`

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints)
[[Back to Model list]](../README.md#documentation-for-models)
[[Back to README]](../README.md)

## `getActivities()`

```php
getActivities($filter_id, $ids, $owner_id, $deal_id, $lead_id, $person_id, $org_id, $updated_since, $updated_until, $sort_by, $sort_direction, $include_fields, $limit, $cursor): \Pipedrive\versions\v2\Model\GetActivities
```

Get all activities

Returns data about all activities.

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');


// Configure API key authorization: api_key
$config = (new Pipedrive\versions\v2\Configuration())->setApiKey('api_token', 'YOUR_API_KEY');
// Uncomment below to setup prefix (e.g. Bearer) for API key, if needed
// $config = (new Pipedrive\versions\v2\Configuration())->setApiKeyPrefix('api_token', 'Bearer');

// Configure OAuth2 access token for authorization: oauth2
$config = (new Pipedrive\versions\v2\Configuration())->setAccessToken('YOUR_ACCESS_TOKEN');


$apiInstance = new Pipedrive\versions\v2\Api\BetaApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$filter_id = 56; // int | If supplied, only activities matching the specified filter are returned
$ids = 'ids_example'; // string | Optional comma separated string array of up to 100 entity ids to fetch. If filter_id is provided, this is ignored. If any of the requested entities do not exist or are not visible, they are not included in the response.
$owner_id = 56; // int | If supplied, only activities owned by the specified user are returned. If filter_id is provided, this is ignored.
$deal_id = 56; // int | If supplied, only activities linked to the specified deal are returned. If filter_id is provided, this is ignored.
$lead_id = 'lead_id_example'; // string | If supplied, only activities linked to the specified lead are returned. If filter_id is provided, this is ignored.
$person_id = 56; // int | If supplied, only activities whose primary participant is the given person are returned. If filter_id is provided, this is ignored.
$org_id = 56; // int | If supplied, only activities linked to the specified organization are returned. If filter_id is provided, this is ignored.
$updated_since = 'updated_since_example'; // string | If set, only activities with an `update_time` later than or equal to this time are returned. In RFC3339 format, e.g. 2025-01-01T10:20:00Z.
$updated_until = 'updated_until_example'; // string | If set, only activities with an `update_time` earlier than this time are returned. In RFC3339 format, e.g. 2025-01-01T10:20:00Z.
$sort_by = 'id'; // string | The field to sort by. Supported fields: `id`, `update_time`, `add_time`.
$sort_direction = 'asc'; // string | The sorting direction. Supported values: `asc`, `desc`.
$include_fields = 'include_fields_example'; // string | Optional comma separated string array of additional fields to include
$limit = 100; // int | For pagination, the limit of entries to be returned. If not provided, 100 items will be returned. Please note that a maximum value of 500 is allowed.
$cursor = 'cursor_example'; // string | For pagination, the marker (an opaque string value) representing the first item on the next page

try {
    $result = $apiInstance->getActivities($filter_id, $ids, $owner_id, $deal_id, $lead_id, $person_id, $org_id, $updated_since, $updated_until, $sort_by, $sort_direction, $include_fields, $limit, $cursor);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling BetaApi->getActivities: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **filter_id** | **int**| If supplied, only activities matching the specified filter are returned | [optional]
 **ids** | **string**| Optional comma separated string array of up to 100 entity ids to fetch. If filter_id is provided, this is ignored. If any of the requested entities do not exist or are not visible, they are not included in the response. | [optional]
 **owner_id** | **int**| If supplied, only activities owned by the specified user are returned. If filter_id is provided, this is ignored. | [optional]
 **deal_id** | **int**| If supplied, only activities linked to the specified deal are returned. If filter_id is provided, this is ignored. | [optional]
 **lead_id** | **string**| If supplied, only activities linked to the specified lead are returned. If filter_id is provided, this is ignored. | [optional]
 **person_id** | **int**| If supplied, only activities whose primary participant is the given person are returned. If filter_id is provided, this is ignored. | [optional]
 **org_id** | **int**| If supplied, only activities linked to the specified organization are returned. If filter_id is provided, this is ignored. | [optional]
 **updated_since** | **string**| If set, only activities with an &#x60;update_time&#x60; later than or equal to this time are returned. In RFC3339 format, e.g. 2025-01-01T10:20:00Z. | [optional]
 **updated_until** | **string**| If set, only activities with an &#x60;update_time&#x60; earlier than this time are returned. In RFC3339 format, e.g. 2025-01-01T10:20:00Z. | [optional]
 **sort_by** | **string**| The field to sort by. Supported fields: &#x60;id&#x60;, &#x60;update_time&#x60;, &#x60;add_time&#x60;. | [optional] [default to &#39;id&#39;]
 **sort_direction** | **string**| The sorting direction. Supported values: &#x60;asc&#x60;, &#x60;desc&#x60;. | [optional] [default to &#39;asc&#39;]
 **include_fields** | **string**| Optional comma separated string array of additional fields to include | [optional]
 **limit** | **int**| For pagination, the limit of entries to be returned. If not provided, 100 items will be returned. Please note that a maximum value of 500 is allowed. | [optional]
 **cursor** | **string**| For pagination, the marker (an opaque string value) representing the first item on the next page | [optional]

### Return type

[**\Pipedrive\versions\v2\Model\GetActivities**](../Model/GetActivities.md)

### Authorization

[api_key](../README.md#api_key), [oauth2](../README.md#oauth2)

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json`

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints)
[[Back to Model list]](../README.md#documentation-for-models)
[[Back to README]](../README.md)

## `getActivity()`

```php
getActivity($id, $include_fields): \Pipedrive\versions\v2\Model\PostPatchGetActivity
```

Get details of an activity

Returns the details of a specific activity.

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');


// Configure API key authorization: api_key
$config = (new Pipedrive\versions\v2\Configuration())->setApiKey('api_token', 'YOUR_API_KEY');
// Uncomment below to setup prefix (e.g. Bearer) for API key, if needed
// $config = (new Pipedrive\versions\v2\Configuration())->setApiKeyPrefix('api_token', 'Bearer');

// Configure OAuth2 access token for authorization: oauth2
$config = (new Pipedrive\versions\v2\Configuration())->setAccessToken('YOUR_ACCESS_TOKEN');


$apiInstance = new Pipedrive\versions\v2\Api\BetaApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$id = 56; // int | The ID of the activity
$include_fields = 'include_fields_example'; // string | Optional comma separated string array of additional fields to include

try {
    $result = $apiInstance->getActivity($id, $include_fields);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling BetaApi->getActivity: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **id** | **int**| The ID of the activity |
 **include_fields** | **string**| Optional comma separated string array of additional fields to include | [optional]

### Return type

[**\Pipedrive\versions\v2\Model\PostPatchGetActivity**](../Model/PostPatchGetActivity.md)

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
$config = (new Pipedrive\versions\v2\Configuration())->setApiKey('api_token', 'YOUR_API_KEY');
// Uncomment below to setup prefix (e.g. Bearer) for API key, if needed
// $config = (new Pipedrive\versions\v2\Configuration())->setApiKeyPrefix('api_token', 'Bearer');

// Configure OAuth2 access token for authorization: oauth2
$config = (new Pipedrive\versions\v2\Configuration())->setAccessToken('YOUR_ACCESS_TOKEN');


$apiInstance = new Pipedrive\versions\v2\Api\BetaApi(
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
    echo 'Exception when calling BetaApi->getAdditionalDiscounts: ', $e->getMessage(), PHP_EOL;
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
$config = (new Pipedrive\versions\v2\Configuration())->setApiKey('api_token', 'YOUR_API_KEY');
// Uncomment below to setup prefix (e.g. Bearer) for API key, if needed
// $config = (new Pipedrive\versions\v2\Configuration())->setApiKeyPrefix('api_token', 'Bearer');

// Configure OAuth2 access token for authorization: oauth2
$config = (new Pipedrive\versions\v2\Configuration())->setAccessToken('YOUR_ACCESS_TOKEN');


$apiInstance = new Pipedrive\versions\v2\Api\BetaApi(
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
    echo 'Exception when calling BetaApi->getDeal: ', $e->getMessage(), PHP_EOL;
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
$config = (new Pipedrive\versions\v2\Configuration())->setApiKey('api_token', 'YOUR_API_KEY');
// Uncomment below to setup prefix (e.g. Bearer) for API key, if needed
// $config = (new Pipedrive\versions\v2\Configuration())->setApiKeyPrefix('api_token', 'Bearer');

// Configure OAuth2 access token for authorization: oauth2
$config = (new Pipedrive\versions\v2\Configuration())->setAccessToken('YOUR_ACCESS_TOKEN');


$apiInstance = new Pipedrive\versions\v2\Api\BetaApi(
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
    echo 'Exception when calling BetaApi->getDealProducts: ', $e->getMessage(), PHP_EOL;
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

Returns data about all deals.

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');


// Configure API key authorization: api_key
$config = (new Pipedrive\versions\v2\Configuration())->setApiKey('api_token', 'YOUR_API_KEY');
// Uncomment below to setup prefix (e.g. Bearer) for API key, if needed
// $config = (new Pipedrive\versions\v2\Configuration())->setApiKeyPrefix('api_token', 'Bearer');

// Configure OAuth2 access token for authorization: oauth2
$config = (new Pipedrive\versions\v2\Configuration())->setAccessToken('YOUR_ACCESS_TOKEN');


$apiInstance = new Pipedrive\versions\v2\Api\BetaApi(
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
    echo 'Exception when calling BetaApi->getDeals: ', $e->getMessage(), PHP_EOL;
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
$config = (new Pipedrive\versions\v2\Configuration())->setApiKey('api_token', 'YOUR_API_KEY');
// Uncomment below to setup prefix (e.g. Bearer) for API key, if needed
// $config = (new Pipedrive\versions\v2\Configuration())->setApiKeyPrefix('api_token', 'Bearer');

// Configure OAuth2 access token for authorization: oauth2
$config = (new Pipedrive\versions\v2\Configuration())->setAccessToken('YOUR_ACCESS_TOKEN');


$apiInstance = new Pipedrive\versions\v2\Api\BetaApi(
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
    echo 'Exception when calling BetaApi->getDealsProducts: ', $e->getMessage(), PHP_EOL;
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
$config = (new Pipedrive\versions\v2\Configuration())->setApiKey('api_token', 'YOUR_API_KEY');
// Uncomment below to setup prefix (e.g. Bearer) for API key, if needed
// $config = (new Pipedrive\versions\v2\Configuration())->setApiKeyPrefix('api_token', 'Bearer');

// Configure OAuth2 access token for authorization: oauth2
$config = (new Pipedrive\versions\v2\Configuration())->setAccessToken('YOUR_ACCESS_TOKEN');


$apiInstance = new Pipedrive\versions\v2\Api\BetaApi(
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
    echo 'Exception when calling BetaApi->getInstallments: ', $e->getMessage(), PHP_EOL;
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

## `getOrganization()`

```php
getOrganization($id, $include_fields, $custom_fields): \Pipedrive\versions\v2\Model\PostPatchGetOrganization
```

Get details of a organization

Returns the details of a specific organization.

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');


// Configure API key authorization: api_key
$config = (new Pipedrive\versions\v2\Configuration())->setApiKey('api_token', 'YOUR_API_KEY');
// Uncomment below to setup prefix (e.g. Bearer) for API key, if needed
// $config = (new Pipedrive\versions\v2\Configuration())->setApiKeyPrefix('api_token', 'Bearer');

// Configure OAuth2 access token for authorization: oauth2
$config = (new Pipedrive\versions\v2\Configuration())->setAccessToken('YOUR_ACCESS_TOKEN');


$apiInstance = new Pipedrive\versions\v2\Api\BetaApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$id = 56; // int | The ID of the organization
$include_fields = 'include_fields_example'; // string | Optional comma separated string array of additional fields to include
$custom_fields = 'custom_fields_example'; // string | Optional comma separated string array of custom fields keys to include. If you are only interested in a particular set of custom fields, please use this parameter for faster results and smaller response.<br/>A maximum of 15 keys is allowed.

try {
    $result = $apiInstance->getOrganization($id, $include_fields, $custom_fields);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling BetaApi->getOrganization: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **id** | **int**| The ID of the organization |
 **include_fields** | **string**| Optional comma separated string array of additional fields to include | [optional]
 **custom_fields** | **string**| Optional comma separated string array of custom fields keys to include. If you are only interested in a particular set of custom fields, please use this parameter for faster results and smaller response.&lt;br/&gt;A maximum of 15 keys is allowed. | [optional]

### Return type

[**\Pipedrive\versions\v2\Model\PostPatchGetOrganization**](../Model/PostPatchGetOrganization.md)

### Authorization

[api_key](../README.md#api_key), [oauth2](../README.md#oauth2)

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json`

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints)
[[Back to Model list]](../README.md#documentation-for-models)
[[Back to README]](../README.md)

## `getOrganizations()`

```php
getOrganizations($filter_id, $ids, $owner_id, $updated_since, $updated_until, $sort_by, $sort_direction, $include_fields, $custom_fields, $limit, $cursor): \Pipedrive\versions\v2\Model\GetOrganizations
```

Get all organizations

Returns data about all organizations.

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');


// Configure API key authorization: api_key
$config = (new Pipedrive\versions\v2\Configuration())->setApiKey('api_token', 'YOUR_API_KEY');
// Uncomment below to setup prefix (e.g. Bearer) for API key, if needed
// $config = (new Pipedrive\versions\v2\Configuration())->setApiKeyPrefix('api_token', 'Bearer');

// Configure OAuth2 access token for authorization: oauth2
$config = (new Pipedrive\versions\v2\Configuration())->setAccessToken('YOUR_ACCESS_TOKEN');


$apiInstance = new Pipedrive\versions\v2\Api\BetaApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$filter_id = 56; // int | If supplied, only organizations matching the specified filter are returned
$ids = 'ids_example'; // string | Optional comma separated string array of up to 100 entity ids to fetch. If filter_id is provided, this is ignored. If any of the requested entities do not exist or are not visible, they are not included in the response.
$owner_id = 56; // int | If supplied, only organization owned by the specified user are returned. If filter_id is provided, this is ignored.
$updated_since = 'updated_since_example'; // string | If set, only organizations with an `update_time` later than or equal to this time are returned. In RFC3339 format, e.g. 2025-01-01T10:20:00Z.
$updated_until = 'updated_until_example'; // string | If set, only organizations with an `update_time` earlier than this time are returned. In RFC3339 format, e.g. 2025-01-01T10:20:00Z.
$sort_by = 'id'; // string | The field to sort by. Supported fields: `id`, `update_time`, `add_time`.
$sort_direction = 'asc'; // string | The sorting direction. Supported values: `asc`, `desc`.
$include_fields = 'include_fields_example'; // string | Optional comma separated string array of additional fields to include
$custom_fields = 'custom_fields_example'; // string | Optional comma separated string array of custom fields keys to include. If you are only interested in a particular set of custom fields, please use this parameter for faster results and smaller response.<br/>A maximum of 15 keys is allowed.
$limit = 100; // int | For pagination, the limit of entries to be returned. If not provided, 100 items will be returned. Please note that a maximum value of 500 is allowed.
$cursor = 'cursor_example'; // string | For pagination, the marker (an opaque string value) representing the first item on the next page

try {
    $result = $apiInstance->getOrganizations($filter_id, $ids, $owner_id, $updated_since, $updated_until, $sort_by, $sort_direction, $include_fields, $custom_fields, $limit, $cursor);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling BetaApi->getOrganizations: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **filter_id** | **int**| If supplied, only organizations matching the specified filter are returned | [optional]
 **ids** | **string**| Optional comma separated string array of up to 100 entity ids to fetch. If filter_id is provided, this is ignored. If any of the requested entities do not exist or are not visible, they are not included in the response. | [optional]
 **owner_id** | **int**| If supplied, only organization owned by the specified user are returned. If filter_id is provided, this is ignored. | [optional]
 **updated_since** | **string**| If set, only organizations with an &#x60;update_time&#x60; later than or equal to this time are returned. In RFC3339 format, e.g. 2025-01-01T10:20:00Z. | [optional]
 **updated_until** | **string**| If set, only organizations with an &#x60;update_time&#x60; earlier than this time are returned. In RFC3339 format, e.g. 2025-01-01T10:20:00Z. | [optional]
 **sort_by** | **string**| The field to sort by. Supported fields: &#x60;id&#x60;, &#x60;update_time&#x60;, &#x60;add_time&#x60;. | [optional] [default to &#39;id&#39;]
 **sort_direction** | **string**| The sorting direction. Supported values: &#x60;asc&#x60;, &#x60;desc&#x60;. | [optional] [default to &#39;asc&#39;]
 **include_fields** | **string**| Optional comma separated string array of additional fields to include | [optional]
 **custom_fields** | **string**| Optional comma separated string array of custom fields keys to include. If you are only interested in a particular set of custom fields, please use this parameter for faster results and smaller response.&lt;br/&gt;A maximum of 15 keys is allowed. | [optional]
 **limit** | **int**| For pagination, the limit of entries to be returned. If not provided, 100 items will be returned. Please note that a maximum value of 500 is allowed. | [optional]
 **cursor** | **string**| For pagination, the marker (an opaque string value) representing the first item on the next page | [optional]

### Return type

[**\Pipedrive\versions\v2\Model\GetOrganizations**](../Model/GetOrganizations.md)

### Authorization

[api_key](../README.md#api_key), [oauth2](../README.md#oauth2)

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json`

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints)
[[Back to Model list]](../README.md#documentation-for-models)
[[Back to README]](../README.md)

## `getPerson()`

```php
getPerson($id, $include_fields, $custom_fields): \Pipedrive\versions\v2\Model\PostPatchGetPerson
```

Get details of a person

Returns the details of a specific person. Fields `ims`, `postal_address`, `notes`, `birthday`, and `job_title` are only included if contact sync is enabled for the company.

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');


// Configure API key authorization: api_key
$config = (new Pipedrive\versions\v2\Configuration())->setApiKey('api_token', 'YOUR_API_KEY');
// Uncomment below to setup prefix (e.g. Bearer) for API key, if needed
// $config = (new Pipedrive\versions\v2\Configuration())->setApiKeyPrefix('api_token', 'Bearer');

// Configure OAuth2 access token for authorization: oauth2
$config = (new Pipedrive\versions\v2\Configuration())->setAccessToken('YOUR_ACCESS_TOKEN');


$apiInstance = new Pipedrive\versions\v2\Api\BetaApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$id = 56; // int | The ID of the person
$include_fields = 'include_fields_example'; // string | Optional comma separated string array of additional fields to include. `marketing_status` and `doi_status` can only be included if the company has marketing app enabled.
$custom_fields = 'custom_fields_example'; // string | Optional comma separated string array of custom fields keys to include. If you are only interested in a particular set of custom fields, please use this parameter for faster results and smaller response.<br/>A maximum of 15 keys is allowed.

try {
    $result = $apiInstance->getPerson($id, $include_fields, $custom_fields);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling BetaApi->getPerson: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **id** | **int**| The ID of the person |
 **include_fields** | **string**| Optional comma separated string array of additional fields to include. &#x60;marketing_status&#x60; and &#x60;doi_status&#x60; can only be included if the company has marketing app enabled. | [optional]
 **custom_fields** | **string**| Optional comma separated string array of custom fields keys to include. If you are only interested in a particular set of custom fields, please use this parameter for faster results and smaller response.&lt;br/&gt;A maximum of 15 keys is allowed. | [optional]

### Return type

[**\Pipedrive\versions\v2\Model\PostPatchGetPerson**](../Model/PostPatchGetPerson.md)

### Authorization

[api_key](../README.md#api_key), [oauth2](../README.md#oauth2)

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json`

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints)
[[Back to Model list]](../README.md#documentation-for-models)
[[Back to README]](../README.md)

## `getPersons()`

```php
getPersons($filter_id, $ids, $owner_id, $org_id, $updated_since, $updated_until, $sort_by, $sort_direction, $include_fields, $custom_fields, $limit, $cursor): \Pipedrive\versions\v2\Model\GetPersons
```

Get all persons

Returns data about all persons. Fields `ims`, `postal_address`, `notes`, `birthday`, and `job_title` are only included if contact sync is enabled for the company.

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');


// Configure API key authorization: api_key
$config = (new Pipedrive\versions\v2\Configuration())->setApiKey('api_token', 'YOUR_API_KEY');
// Uncomment below to setup prefix (e.g. Bearer) for API key, if needed
// $config = (new Pipedrive\versions\v2\Configuration())->setApiKeyPrefix('api_token', 'Bearer');

// Configure OAuth2 access token for authorization: oauth2
$config = (new Pipedrive\versions\v2\Configuration())->setAccessToken('YOUR_ACCESS_TOKEN');


$apiInstance = new Pipedrive\versions\v2\Api\BetaApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$filter_id = 56; // int | If supplied, only persons matching the specified filter are returned
$ids = 'ids_example'; // string | Optional comma separated string array of up to 100 entity ids to fetch. If filter_id is provided, this is ignored. If any of the requested entities do not exist or are not visible, they are not included in the response.
$owner_id = 56; // int | If supplied, only persons owned by the specified user are returned. If filter_id is provided, this is ignored.
$org_id = 56; // int | If supplied, only persons linked to the specified organization are returned. If filter_id is provided, this is ignored.
$updated_since = 'updated_since_example'; // string | If set, only persons with an `update_time` later than or equal to this time are returned. In RFC3339 format, e.g. 2025-01-01T10:20:00Z.
$updated_until = 'updated_until_example'; // string | If set, only persons with an `update_time` earlier than this time are returned. In RFC3339 format, e.g. 2025-01-01T10:20:00Z.
$sort_by = 'id'; // string | The field to sort by. Supported fields: `id`, `update_time`, `add_time`.
$sort_direction = 'asc'; // string | The sorting direction. Supported values: `asc`, `desc`.
$include_fields = 'include_fields_example'; // string | Optional comma separated string array of additional fields to include. `marketing_status` and `doi_status` can only be included if the company has marketing app enabled.
$custom_fields = 'custom_fields_example'; // string | Optional comma separated string array of custom fields keys to include. If you are only interested in a particular set of custom fields, please use this parameter for faster results and smaller response.<br/>A maximum of 15 keys is allowed.
$limit = 100; // int | For pagination, the limit of entries to be returned. If not provided, 100 items will be returned. Please note that a maximum value of 500 is allowed.
$cursor = 'cursor_example'; // string | For pagination, the marker (an opaque string value) representing the first item on the next page

try {
    $result = $apiInstance->getPersons($filter_id, $ids, $owner_id, $org_id, $updated_since, $updated_until, $sort_by, $sort_direction, $include_fields, $custom_fields, $limit, $cursor);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling BetaApi->getPersons: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **filter_id** | **int**| If supplied, only persons matching the specified filter are returned | [optional]
 **ids** | **string**| Optional comma separated string array of up to 100 entity ids to fetch. If filter_id is provided, this is ignored. If any of the requested entities do not exist or are not visible, they are not included in the response. | [optional]
 **owner_id** | **int**| If supplied, only persons owned by the specified user are returned. If filter_id is provided, this is ignored. | [optional]
 **org_id** | **int**| If supplied, only persons linked to the specified organization are returned. If filter_id is provided, this is ignored. | [optional]
 **updated_since** | **string**| If set, only persons with an &#x60;update_time&#x60; later than or equal to this time are returned. In RFC3339 format, e.g. 2025-01-01T10:20:00Z. | [optional]
 **updated_until** | **string**| If set, only persons with an &#x60;update_time&#x60; earlier than this time are returned. In RFC3339 format, e.g. 2025-01-01T10:20:00Z. | [optional]
 **sort_by** | **string**| The field to sort by. Supported fields: &#x60;id&#x60;, &#x60;update_time&#x60;, &#x60;add_time&#x60;. | [optional] [default to &#39;id&#39;]
 **sort_direction** | **string**| The sorting direction. Supported values: &#x60;asc&#x60;, &#x60;desc&#x60;. | [optional] [default to &#39;asc&#39;]
 **include_fields** | **string**| Optional comma separated string array of additional fields to include. &#x60;marketing_status&#x60; and &#x60;doi_status&#x60; can only be included if the company has marketing app enabled. | [optional]
 **custom_fields** | **string**| Optional comma separated string array of custom fields keys to include. If you are only interested in a particular set of custom fields, please use this parameter for faster results and smaller response.&lt;br/&gt;A maximum of 15 keys is allowed. | [optional]
 **limit** | **int**| For pagination, the limit of entries to be returned. If not provided, 100 items will be returned. Please note that a maximum value of 500 is allowed. | [optional]
 **cursor** | **string**| For pagination, the marker (an opaque string value) representing the first item on the next page | [optional]

### Return type

[**\Pipedrive\versions\v2\Model\GetPersons**](../Model/GetPersons.md)

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
$config = (new Pipedrive\versions\v2\Configuration())->setApiKey('api_token', 'YOUR_API_KEY');
// Uncomment below to setup prefix (e.g. Bearer) for API key, if needed
// $config = (new Pipedrive\versions\v2\Configuration())->setApiKeyPrefix('api_token', 'Bearer');

// Configure OAuth2 access token for authorization: oauth2
$config = (new Pipedrive\versions\v2\Configuration())->setAccessToken('YOUR_ACCESS_TOKEN');


$apiInstance = new Pipedrive\versions\v2\Api\BetaApi(
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
    echo 'Exception when calling BetaApi->postAdditionalDiscount: ', $e->getMessage(), PHP_EOL;
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
$config = (new Pipedrive\versions\v2\Configuration())->setApiKey('api_token', 'YOUR_API_KEY');
// Uncomment below to setup prefix (e.g. Bearer) for API key, if needed
// $config = (new Pipedrive\versions\v2\Configuration())->setApiKeyPrefix('api_token', 'Bearer');

// Configure OAuth2 access token for authorization: oauth2
$config = (new Pipedrive\versions\v2\Configuration())->setAccessToken('YOUR_ACCESS_TOKEN');


$apiInstance = new Pipedrive\versions\v2\Api\BetaApi(
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
    echo 'Exception when calling BetaApi->postInstallment: ', $e->getMessage(), PHP_EOL;
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
$config = (new Pipedrive\versions\v2\Configuration())->setApiKey('api_token', 'YOUR_API_KEY');
// Uncomment below to setup prefix (e.g. Bearer) for API key, if needed
// $config = (new Pipedrive\versions\v2\Configuration())->setApiKeyPrefix('api_token', 'Bearer');

// Configure OAuth2 access token for authorization: oauth2
$config = (new Pipedrive\versions\v2\Configuration())->setAccessToken('YOUR_ACCESS_TOKEN');


$apiInstance = new Pipedrive\versions\v2\Api\BetaApi(
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
    echo 'Exception when calling BetaApi->searchDeals: ', $e->getMessage(), PHP_EOL;
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

## `searchItem()`

```php
searchItem($term, $item_types, $fields, $search_for_related_items, $exact_match, $include_fields, $limit, $cursor): \Pipedrive\versions\v2\Model\ItemSearchResponse
```

Perform a search from multiple item types

Performs a search from your choice of item types and fields.

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');


// Configure API key authorization: api_key
$config = (new Pipedrive\versions\v2\Configuration())->setApiKey('api_token', 'YOUR_API_KEY');
// Uncomment below to setup prefix (e.g. Bearer) for API key, if needed
// $config = (new Pipedrive\versions\v2\Configuration())->setApiKeyPrefix('api_token', 'Bearer');

// Configure OAuth2 access token for authorization: oauth2
$config = (new Pipedrive\versions\v2\Configuration())->setAccessToken('YOUR_ACCESS_TOKEN');


$apiInstance = new Pipedrive\versions\v2\Api\BetaApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$term = 'term_example'; // string | The search term to look for. Minimum 2 characters (or 1 if using `exact_match`). Please note that the search term has to be URL encoded.
$item_types = 'item_types_example'; // string | A comma-separated string array. The type of items to perform the search from. Defaults to all.
$fields = 'fields_example'; // string | A comma-separated string array. The fields to perform the search from. Defaults to all. Relevant for each item type are:<br> <table> <tr><th><b>Item type</b></th><th><b>Field</b></th></tr> <tr><td>Deal</td><td>`custom_fields`, `notes`, `title`</td></tr> <tr><td>Person</td><td>`custom_fields`, `email`, `name`, `notes`, `phone`</td></tr> <tr><td>Organization</td><td>`address`, `custom_fields`, `name`, `notes`</td></tr> <tr><td>Product</td><td>`code`, `custom_fields`, `name`</td></tr> <tr><td>Lead</td><td>`custom_fields`, `notes`, `email`, `organization_name`, `person_name`, `phone`, `title`</td></tr> <tr><td>File</td><td>`name`</td></tr> <tr><td>Mail attachment</td><td>`name`</td></tr> <tr><td>Project</td><td> `custom_fields`, `notes`, `title`, `description` </td></tr> </table> <br> Only the following custom field types are searchable: `address`, `varchar`, `text`, `varchar_auto`, `double`, `monetary` and `phone`. Read more about searching by custom fields <a href=\"https://support.pipedrive.com/en/article/search-finding-what-you-need#searching-by-custom-fields\" target=\"_blank\" rel=\"noopener noreferrer\">here</a>.<br/> When searching for leads, the email, organization_name, person_name, and phone fields will return results only for leads not linked to contacts. For searching leads by person or organization values, please use `search_for_related_items`.
$search_for_related_items = True; // bool | When enabled, the response will include up to 100 newest related leads and 100 newest related deals for each found person and organization and up to 100 newest related persons for each found organization
$exact_match = True; // bool | When enabled, only full exact matches against the given term are returned. It is <b>not</b> case sensitive.
$include_fields = 'include_fields_example'; // string | A comma-separated string array. Supports including optional fields in the results which are not provided by default.
$limit = 100; // int | For pagination, the limit of entries to be returned. If not provided, 100 items will be returned. Please note that a maximum value of 500 is allowed.
$cursor = 'cursor_example'; // string | For pagination, the marker (an opaque string value) representing the first item on the next page

try {
    $result = $apiInstance->searchItem($term, $item_types, $fields, $search_for_related_items, $exact_match, $include_fields, $limit, $cursor);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling BetaApi->searchItem: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **term** | **string**| The search term to look for. Minimum 2 characters (or 1 if using &#x60;exact_match&#x60;). Please note that the search term has to be URL encoded. |
 **item_types** | **string**| A comma-separated string array. The type of items to perform the search from. Defaults to all. | [optional]
 **fields** | **string**| A comma-separated string array. The fields to perform the search from. Defaults to all. Relevant for each item type are:&lt;br&gt; &lt;table&gt; &lt;tr&gt;&lt;th&gt;&lt;b&gt;Item type&lt;/b&gt;&lt;/th&gt;&lt;th&gt;&lt;b&gt;Field&lt;/b&gt;&lt;/th&gt;&lt;/tr&gt; &lt;tr&gt;&lt;td&gt;Deal&lt;/td&gt;&lt;td&gt;&#x60;custom_fields&#x60;, &#x60;notes&#x60;, &#x60;title&#x60;&lt;/td&gt;&lt;/tr&gt; &lt;tr&gt;&lt;td&gt;Person&lt;/td&gt;&lt;td&gt;&#x60;custom_fields&#x60;, &#x60;email&#x60;, &#x60;name&#x60;, &#x60;notes&#x60;, &#x60;phone&#x60;&lt;/td&gt;&lt;/tr&gt; &lt;tr&gt;&lt;td&gt;Organization&lt;/td&gt;&lt;td&gt;&#x60;address&#x60;, &#x60;custom_fields&#x60;, &#x60;name&#x60;, &#x60;notes&#x60;&lt;/td&gt;&lt;/tr&gt; &lt;tr&gt;&lt;td&gt;Product&lt;/td&gt;&lt;td&gt;&#x60;code&#x60;, &#x60;custom_fields&#x60;, &#x60;name&#x60;&lt;/td&gt;&lt;/tr&gt; &lt;tr&gt;&lt;td&gt;Lead&lt;/td&gt;&lt;td&gt;&#x60;custom_fields&#x60;, &#x60;notes&#x60;, &#x60;email&#x60;, &#x60;organization_name&#x60;, &#x60;person_name&#x60;, &#x60;phone&#x60;, &#x60;title&#x60;&lt;/td&gt;&lt;/tr&gt; &lt;tr&gt;&lt;td&gt;File&lt;/td&gt;&lt;td&gt;&#x60;name&#x60;&lt;/td&gt;&lt;/tr&gt; &lt;tr&gt;&lt;td&gt;Mail attachment&lt;/td&gt;&lt;td&gt;&#x60;name&#x60;&lt;/td&gt;&lt;/tr&gt; &lt;tr&gt;&lt;td&gt;Project&lt;/td&gt;&lt;td&gt; &#x60;custom_fields&#x60;, &#x60;notes&#x60;, &#x60;title&#x60;, &#x60;description&#x60; &lt;/td&gt;&lt;/tr&gt; &lt;/table&gt; &lt;br&gt; Only the following custom field types are searchable: &#x60;address&#x60;, &#x60;varchar&#x60;, &#x60;text&#x60;, &#x60;varchar_auto&#x60;, &#x60;double&#x60;, &#x60;monetary&#x60; and &#x60;phone&#x60;. Read more about searching by custom fields &lt;a href&#x3D;\&quot;https://support.pipedrive.com/en/article/search-finding-what-you-need#searching-by-custom-fields\&quot; target&#x3D;\&quot;_blank\&quot; rel&#x3D;\&quot;noopener noreferrer\&quot;&gt;here&lt;/a&gt;.&lt;br/&gt; When searching for leads, the email, organization_name, person_name, and phone fields will return results only for leads not linked to contacts. For searching leads by person or organization values, please use &#x60;search_for_related_items&#x60;. | [optional]
 **search_for_related_items** | **bool**| When enabled, the response will include up to 100 newest related leads and 100 newest related deals for each found person and organization and up to 100 newest related persons for each found organization | [optional]
 **exact_match** | **bool**| When enabled, only full exact matches against the given term are returned. It is &lt;b&gt;not&lt;/b&gt; case sensitive. | [optional]
 **include_fields** | **string**| A comma-separated string array. Supports including optional fields in the results which are not provided by default. | [optional]
 **limit** | **int**| For pagination, the limit of entries to be returned. If not provided, 100 items will be returned. Please note that a maximum value of 500 is allowed. | [optional]
 **cursor** | **string**| For pagination, the marker (an opaque string value) representing the first item on the next page | [optional]

### Return type

[**\Pipedrive\versions\v2\Model\ItemSearchResponse**](../Model/ItemSearchResponse.md)

### Authorization

[api_key](../README.md#api_key), [oauth2](../README.md#oauth2)

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json`

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints)
[[Back to Model list]](../README.md#documentation-for-models)
[[Back to README]](../README.md)

## `searchItemByField()`

```php
searchItemByField($term, $entity_type, $field, $match, $limit, $cursor): \Pipedrive\versions\v2\Model\ItemSearchFieldResponse
```

Perform a search using a specific field from an item type

Performs a search from the values of a specific field. Results can either be the distinct values of the field (useful for searching autocomplete field values), or the IDs of actual items (deals, leads, persons, organizations or products).

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');


// Configure API key authorization: api_key
$config = (new Pipedrive\versions\v2\Configuration())->setApiKey('api_token', 'YOUR_API_KEY');
// Uncomment below to setup prefix (e.g. Bearer) for API key, if needed
// $config = (new Pipedrive\versions\v2\Configuration())->setApiKeyPrefix('api_token', 'Bearer');

// Configure OAuth2 access token for authorization: oauth2
$config = (new Pipedrive\versions\v2\Configuration())->setAccessToken('YOUR_ACCESS_TOKEN');


$apiInstance = new Pipedrive\versions\v2\Api\BetaApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$term = 'term_example'; // string | The search term to look for. Minimum 2 characters (or 1 if `match` is `exact`). Please note that the search term has to be URL encoded.
$entity_type = 'entity_type_example'; // string | The type of the field to perform the search from
$field = 'field_example'; // string | The key of the field to search from. The field key can be obtained by fetching the list of the fields using any of the fields' API GET methods (dealFields, personFields, etc.). Only the following custom field types are searchable: `address`, `varchar`, `text`, `varchar_auto`, `double`, `monetary` and `phone`. Read more about searching by custom fields <a href=\"https://support.pipedrive.com/en/article/search-finding-what-you-need#searching-by-custom-fields\" target=\"_blank\" rel=\"noopener noreferrer\">here</a>.
$match = 'exact'; // string | The type of match used against the term. The search <b>is</b> case sensitive.<br/><br/> E.g. in case of searching for a value `monkey`, <ul> <li>with `exact` match, you will only find it if term is `monkey`</li> <li>with `beginning` match, you will only find it if the term matches the beginning or the whole string, e.g. `monk` and `monkey`</li> <li>with `middle` match, you will find the it if the term matches any substring of the value, e.g. `onk` and `ke`</li> </ul>.
$limit = 100; // int | For pagination, the limit of entries to be returned. If not provided, 100 items will be returned. Please note that a maximum value of 500 is allowed.
$cursor = 'cursor_example'; // string | For pagination, the marker (an opaque string value) representing the first item on the next page

try {
    $result = $apiInstance->searchItemByField($term, $entity_type, $field, $match, $limit, $cursor);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling BetaApi->searchItemByField: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **term** | **string**| The search term to look for. Minimum 2 characters (or 1 if &#x60;match&#x60; is &#x60;exact&#x60;). Please note that the search term has to be URL encoded. |
 **entity_type** | **string**| The type of the field to perform the search from |
 **field** | **string**| The key of the field to search from. The field key can be obtained by fetching the list of the fields using any of the fields&#39; API GET methods (dealFields, personFields, etc.). Only the following custom field types are searchable: &#x60;address&#x60;, &#x60;varchar&#x60;, &#x60;text&#x60;, &#x60;varchar_auto&#x60;, &#x60;double&#x60;, &#x60;monetary&#x60; and &#x60;phone&#x60;. Read more about searching by custom fields &lt;a href&#x3D;\&quot;https://support.pipedrive.com/en/article/search-finding-what-you-need#searching-by-custom-fields\&quot; target&#x3D;\&quot;_blank\&quot; rel&#x3D;\&quot;noopener noreferrer\&quot;&gt;here&lt;/a&gt;. |
 **match** | **string**| The type of match used against the term. The search &lt;b&gt;is&lt;/b&gt; case sensitive.&lt;br/&gt;&lt;br/&gt; E.g. in case of searching for a value &#x60;monkey&#x60;, &lt;ul&gt; &lt;li&gt;with &#x60;exact&#x60; match, you will only find it if term is &#x60;monkey&#x60;&lt;/li&gt; &lt;li&gt;with &#x60;beginning&#x60; match, you will only find it if the term matches the beginning or the whole string, e.g. &#x60;monk&#x60; and &#x60;monkey&#x60;&lt;/li&gt; &lt;li&gt;with &#x60;middle&#x60; match, you will find the it if the term matches any substring of the value, e.g. &#x60;onk&#x60; and &#x60;ke&#x60;&lt;/li&gt; &lt;/ul&gt;. | [optional] [default to &#39;exact&#39;]
 **limit** | **int**| For pagination, the limit of entries to be returned. If not provided, 100 items will be returned. Please note that a maximum value of 500 is allowed. | [optional]
 **cursor** | **string**| For pagination, the marker (an opaque string value) representing the first item on the next page | [optional]

### Return type

[**\Pipedrive\versions\v2\Model\ItemSearchFieldResponse**](../Model/ItemSearchFieldResponse.md)

### Authorization

[api_key](../README.md#api_key), [oauth2](../README.md#oauth2)

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json`

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints)
[[Back to Model list]](../README.md#documentation-for-models)
[[Back to README]](../README.md)

## `searchLeads()`

```php
searchLeads($term, $fields, $exact_match, $person_id, $organization_id, $include_fields, $limit, $cursor): \Pipedrive\versions\v2\Model\LeadSearchResponse
```

Search leads

Searches all leads by title, notes and/or custom fields. This endpoint is a wrapper of <a href=\"https://developers.pipedrive.com/docs/api/v1/ItemSearch#searchItem\">/v1/itemSearch</a> with a narrower OAuth scope. Found leads can be filtered by the person ID and the organization ID.

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');


// Configure API key authorization: api_key
$config = (new Pipedrive\versions\v2\Configuration())->setApiKey('api_token', 'YOUR_API_KEY');
// Uncomment below to setup prefix (e.g. Bearer) for API key, if needed
// $config = (new Pipedrive\versions\v2\Configuration())->setApiKeyPrefix('api_token', 'Bearer');

// Configure OAuth2 access token for authorization: oauth2
$config = (new Pipedrive\versions\v2\Configuration())->setAccessToken('YOUR_ACCESS_TOKEN');


$apiInstance = new Pipedrive\versions\v2\Api\BetaApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$term = 'term_example'; // string | The search term to look for. Minimum 2 characters (or 1 if using `exact_match`). Please note that the search term has to be URL encoded.
$fields = 'fields_example'; // string | A comma-separated string array. The fields to perform the search from. Defaults to all of them.
$exact_match = True; // bool | When enabled, only full exact matches against the given term are returned. It is <b>not</b> case sensitive.
$person_id = 56; // int | Will filter leads by the provided person ID. The upper limit of found leads associated with the person is 2000.
$organization_id = 56; // int | Will filter leads by the provided organization ID. The upper limit of found leads associated with the organization is 2000.
$include_fields = 'include_fields_example'; // string | Supports including optional fields in the results which are not provided by default
$limit = 100; // int | For pagination, the limit of entries to be returned. If not provided, 100 items will be returned. Please note that a maximum value of 500 is allowed.
$cursor = 'cursor_example'; // string | For pagination, the marker (an opaque string value) representing the first item on the next page

try {
    $result = $apiInstance->searchLeads($term, $fields, $exact_match, $person_id, $organization_id, $include_fields, $limit, $cursor);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling BetaApi->searchLeads: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **term** | **string**| The search term to look for. Minimum 2 characters (or 1 if using &#x60;exact_match&#x60;). Please note that the search term has to be URL encoded. |
 **fields** | **string**| A comma-separated string array. The fields to perform the search from. Defaults to all of them. | [optional]
 **exact_match** | **bool**| When enabled, only full exact matches against the given term are returned. It is &lt;b&gt;not&lt;/b&gt; case sensitive. | [optional]
 **person_id** | **int**| Will filter leads by the provided person ID. The upper limit of found leads associated with the person is 2000. | [optional]
 **organization_id** | **int**| Will filter leads by the provided organization ID. The upper limit of found leads associated with the organization is 2000. | [optional]
 **include_fields** | **string**| Supports including optional fields in the results which are not provided by default | [optional]
 **limit** | **int**| For pagination, the limit of entries to be returned. If not provided, 100 items will be returned. Please note that a maximum value of 500 is allowed. | [optional]
 **cursor** | **string**| For pagination, the marker (an opaque string value) representing the first item on the next page | [optional]

### Return type

[**\Pipedrive\versions\v2\Model\LeadSearchResponse**](../Model/LeadSearchResponse.md)

### Authorization

[api_key](../README.md#api_key), [oauth2](../README.md#oauth2)

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json`

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints)
[[Back to Model list]](../README.md#documentation-for-models)
[[Back to README]](../README.md)

## `searchOrganization()`

```php
searchOrganization($term, $fields, $exact_match, $limit, $cursor): \Pipedrive\versions\v2\Model\GetOrganizationSearchResponse
```

Search organizations

Searches all organizations by name, address, notes and/or custom fields. This endpoint is a wrapper of <a href=\"https://developers.pipedrive.com/docs/api/v1/ItemSearch#searchItem\">/v1/itemSearch</a> with a narrower OAuth scope.

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');


// Configure API key authorization: api_key
$config = (new Pipedrive\versions\v2\Configuration())->setApiKey('api_token', 'YOUR_API_KEY');
// Uncomment below to setup prefix (e.g. Bearer) for API key, if needed
// $config = (new Pipedrive\versions\v2\Configuration())->setApiKeyPrefix('api_token', 'Bearer');

// Configure OAuth2 access token for authorization: oauth2
$config = (new Pipedrive\versions\v2\Configuration())->setAccessToken('YOUR_ACCESS_TOKEN');


$apiInstance = new Pipedrive\versions\v2\Api\BetaApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$term = 'term_example'; // string | The search term to look for. Minimum 2 characters (or 1 if using `exact_match`). Please note that the search term has to be URL encoded.
$fields = 'fields_example'; // string | A comma-separated string array. The fields to perform the search from. Defaults to all of them. Only the following custom field types are searchable: `address`, `varchar`, `text`, `varchar_auto`, `double`, `monetary` and `phone`. Read more about searching by custom fields <a href=\"https://support.pipedrive.com/en/article/search-finding-what-you-need#searching-by-custom-fields\" target=\"_blank\" rel=\"noopener noreferrer\">here</a>.
$exact_match = True; // bool | When enabled, only full exact matches against the given term are returned. It is <b>not</b> case sensitive.
$limit = 100; // int | For pagination, the limit of entries to be returned. If not provided, 100 items will be returned. Please note that a maximum value of 500 is allowed.
$cursor = 'cursor_example'; // string | For pagination, the marker (an opaque string value) representing the first item on the next page

try {
    $result = $apiInstance->searchOrganization($term, $fields, $exact_match, $limit, $cursor);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling BetaApi->searchOrganization: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **term** | **string**| The search term to look for. Minimum 2 characters (or 1 if using &#x60;exact_match&#x60;). Please note that the search term has to be URL encoded. |
 **fields** | **string**| A comma-separated string array. The fields to perform the search from. Defaults to all of them. Only the following custom field types are searchable: &#x60;address&#x60;, &#x60;varchar&#x60;, &#x60;text&#x60;, &#x60;varchar_auto&#x60;, &#x60;double&#x60;, &#x60;monetary&#x60; and &#x60;phone&#x60;. Read more about searching by custom fields &lt;a href&#x3D;\&quot;https://support.pipedrive.com/en/article/search-finding-what-you-need#searching-by-custom-fields\&quot; target&#x3D;\&quot;_blank\&quot; rel&#x3D;\&quot;noopener noreferrer\&quot;&gt;here&lt;/a&gt;. | [optional]
 **exact_match** | **bool**| When enabled, only full exact matches against the given term are returned. It is &lt;b&gt;not&lt;/b&gt; case sensitive. | [optional]
 **limit** | **int**| For pagination, the limit of entries to be returned. If not provided, 100 items will be returned. Please note that a maximum value of 500 is allowed. | [optional]
 **cursor** | **string**| For pagination, the marker (an opaque string value) representing the first item on the next page | [optional]

### Return type

[**\Pipedrive\versions\v2\Model\GetOrganizationSearchResponse**](../Model/GetOrganizationSearchResponse.md)

### Authorization

[api_key](../README.md#api_key), [oauth2](../README.md#oauth2)

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json`

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints)
[[Back to Model list]](../README.md#documentation-for-models)
[[Back to README]](../README.md)

## `searchPersons()`

```php
searchPersons($term, $fields, $exact_match, $organization_id, $include_fields, $limit, $cursor): \Pipedrive\versions\v2\Model\PersonSearchResponse
```

Search persons

Searches all persons by name, email, phone, notes and/or custom fields. This endpoint is a wrapper of <a href=\"https://developers.pipedrive.com/docs/api/v1/ItemSearch#searchItem\">/v1/itemSearch</a> with a narrower OAuth scope. Found persons can be filtered by organization ID.

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');


// Configure API key authorization: api_key
$config = (new Pipedrive\versions\v2\Configuration())->setApiKey('api_token', 'YOUR_API_KEY');
// Uncomment below to setup prefix (e.g. Bearer) for API key, if needed
// $config = (new Pipedrive\versions\v2\Configuration())->setApiKeyPrefix('api_token', 'Bearer');

// Configure OAuth2 access token for authorization: oauth2
$config = (new Pipedrive\versions\v2\Configuration())->setAccessToken('YOUR_ACCESS_TOKEN');


$apiInstance = new Pipedrive\versions\v2\Api\BetaApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$term = 'term_example'; // string | The search term to look for. Minimum 2 characters (or 1 if using `exact_match`). Please note that the search term has to be URL encoded.
$fields = 'fields_example'; // string | A comma-separated string array. The fields to perform the search from. Defaults to all of them. Only the following custom field types are searchable: `address`, `varchar`, `text`, `varchar_auto`, `double`, `monetary` and `phone`. Read more about searching by custom fields <a href=\"https://support.pipedrive.com/en/article/search-finding-what-you-need#searching-by-custom-fields\" target=\"_blank\" rel=\"noopener noreferrer\">here</a>.
$exact_match = True; // bool | When enabled, only full exact matches against the given term are returned. It is <b>not</b> case sensitive.
$organization_id = 56; // int | Will filter persons by the provided organization ID. The upper limit of found persons associated with the organization is 2000.
$include_fields = 'include_fields_example'; // string | Supports including optional fields in the results which are not provided by default
$limit = 100; // int | For pagination, the limit of entries to be returned. If not provided, 100 items will be returned. Please note that a maximum value of 500 is allowed.
$cursor = 'cursor_example'; // string | For pagination, the marker (an opaque string value) representing the first item on the next page

try {
    $result = $apiInstance->searchPersons($term, $fields, $exact_match, $organization_id, $include_fields, $limit, $cursor);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling BetaApi->searchPersons: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **term** | **string**| The search term to look for. Minimum 2 characters (or 1 if using &#x60;exact_match&#x60;). Please note that the search term has to be URL encoded. |
 **fields** | **string**| A comma-separated string array. The fields to perform the search from. Defaults to all of them. Only the following custom field types are searchable: &#x60;address&#x60;, &#x60;varchar&#x60;, &#x60;text&#x60;, &#x60;varchar_auto&#x60;, &#x60;double&#x60;, &#x60;monetary&#x60; and &#x60;phone&#x60;. Read more about searching by custom fields &lt;a href&#x3D;\&quot;https://support.pipedrive.com/en/article/search-finding-what-you-need#searching-by-custom-fields\&quot; target&#x3D;\&quot;_blank\&quot; rel&#x3D;\&quot;noopener noreferrer\&quot;&gt;here&lt;/a&gt;. | [optional]
 **exact_match** | **bool**| When enabled, only full exact matches against the given term are returned. It is &lt;b&gt;not&lt;/b&gt; case sensitive. | [optional]
 **organization_id** | **int**| Will filter persons by the provided organization ID. The upper limit of found persons associated with the organization is 2000. | [optional]
 **include_fields** | **string**| Supports including optional fields in the results which are not provided by default | [optional]
 **limit** | **int**| For pagination, the limit of entries to be returned. If not provided, 100 items will be returned. Please note that a maximum value of 500 is allowed. | [optional]
 **cursor** | **string**| For pagination, the marker (an opaque string value) representing the first item on the next page | [optional]

### Return type

[**\Pipedrive\versions\v2\Model\PersonSearchResponse**](../Model/PersonSearchResponse.md)

### Authorization

[api_key](../README.md#api_key), [oauth2](../README.md#oauth2)

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json`

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints)
[[Back to Model list]](../README.md#documentation-for-models)
[[Back to README]](../README.md)

## `updateActivity()`

```php
updateActivity($id, $activity_request_body): \Pipedrive\versions\v2\Model\PostPatchGetActivity
```

Update an activity

Updates the properties of an activity.

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');


// Configure API key authorization: api_key
$config = (new Pipedrive\versions\v2\Configuration())->setApiKey('api_token', 'YOUR_API_KEY');
// Uncomment below to setup prefix (e.g. Bearer) for API key, if needed
// $config = (new Pipedrive\versions\v2\Configuration())->setApiKeyPrefix('api_token', 'Bearer');

// Configure OAuth2 access token for authorization: oauth2
$config = (new Pipedrive\versions\v2\Configuration())->setAccessToken('YOUR_ACCESS_TOKEN');


$apiInstance = new Pipedrive\versions\v2\Api\BetaApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$id = 56; // int | The ID of the activity
$activity_request_body = new \Pipedrive\versions\v2\Model\ActivityRequestBody(); // \Pipedrive\versions\v2\Model\ActivityRequestBody

try {
    $result = $apiInstance->updateActivity($id, $activity_request_body);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling BetaApi->updateActivity: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **id** | **int**| The ID of the activity |
 **activity_request_body** | [**\Pipedrive\versions\v2\Model\ActivityRequestBody**](../Model/ActivityRequestBody.md)|  | [optional]

### Return type

[**\Pipedrive\versions\v2\Model\PostPatchGetActivity**](../Model/PostPatchGetActivity.md)

### Authorization

[api_key](../README.md#api_key), [oauth2](../README.md#oauth2)

### HTTP request headers

- **Content-Type**: `application/json`
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
$config = (new Pipedrive\versions\v2\Configuration())->setApiKey('api_token', 'YOUR_API_KEY');
// Uncomment below to setup prefix (e.g. Bearer) for API key, if needed
// $config = (new Pipedrive\versions\v2\Configuration())->setApiKeyPrefix('api_token', 'Bearer');

// Configure OAuth2 access token for authorization: oauth2
$config = (new Pipedrive\versions\v2\Configuration())->setAccessToken('YOUR_ACCESS_TOKEN');


$apiInstance = new Pipedrive\versions\v2\Api\BetaApi(
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
    echo 'Exception when calling BetaApi->updateAdditionalDiscount: ', $e->getMessage(), PHP_EOL;
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
$config = (new Pipedrive\versions\v2\Configuration())->setApiKey('api_token', 'YOUR_API_KEY');
// Uncomment below to setup prefix (e.g. Bearer) for API key, if needed
// $config = (new Pipedrive\versions\v2\Configuration())->setApiKeyPrefix('api_token', 'Bearer');

// Configure OAuth2 access token for authorization: oauth2
$config = (new Pipedrive\versions\v2\Configuration())->setAccessToken('YOUR_ACCESS_TOKEN');


$apiInstance = new Pipedrive\versions\v2\Api\BetaApi(
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
    echo 'Exception when calling BetaApi->updateDeal: ', $e->getMessage(), PHP_EOL;
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
$config = (new Pipedrive\versions\v2\Configuration())->setApiKey('api_token', 'YOUR_API_KEY');
// Uncomment below to setup prefix (e.g. Bearer) for API key, if needed
// $config = (new Pipedrive\versions\v2\Configuration())->setApiKeyPrefix('api_token', 'Bearer');

// Configure OAuth2 access token for authorization: oauth2
$config = (new Pipedrive\versions\v2\Configuration())->setAccessToken('YOUR_ACCESS_TOKEN');


$apiInstance = new Pipedrive\versions\v2\Api\BetaApi(
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
    echo 'Exception when calling BetaApi->updateDealProduct: ', $e->getMessage(), PHP_EOL;
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
$config = (new Pipedrive\versions\v2\Configuration())->setApiKey('api_token', 'YOUR_API_KEY');
// Uncomment below to setup prefix (e.g. Bearer) for API key, if needed
// $config = (new Pipedrive\versions\v2\Configuration())->setApiKeyPrefix('api_token', 'Bearer');

// Configure OAuth2 access token for authorization: oauth2
$config = (new Pipedrive\versions\v2\Configuration())->setAccessToken('YOUR_ACCESS_TOKEN');


$apiInstance = new Pipedrive\versions\v2\Api\BetaApi(
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
    echo 'Exception when calling BetaApi->updateInstallment: ', $e->getMessage(), PHP_EOL;
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

## `updateOrganization()`

```php
updateOrganization($id, $organization_request_body): \Pipedrive\versions\v2\Model\PostPatchGetOrganization
```

Update a organization

Updates the properties of a organization.

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');


// Configure API key authorization: api_key
$config = (new Pipedrive\versions\v2\Configuration())->setApiKey('api_token', 'YOUR_API_KEY');
// Uncomment below to setup prefix (e.g. Bearer) for API key, if needed
// $config = (new Pipedrive\versions\v2\Configuration())->setApiKeyPrefix('api_token', 'Bearer');

// Configure OAuth2 access token for authorization: oauth2
$config = (new Pipedrive\versions\v2\Configuration())->setAccessToken('YOUR_ACCESS_TOKEN');


$apiInstance = new Pipedrive\versions\v2\Api\BetaApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$id = 56; // int | The ID of the organization
$organization_request_body = new \Pipedrive\versions\v2\Model\OrganizationRequestBody(); // \Pipedrive\versions\v2\Model\OrganizationRequestBody

try {
    $result = $apiInstance->updateOrganization($id, $organization_request_body);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling BetaApi->updateOrganization: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **id** | **int**| The ID of the organization |
 **organization_request_body** | [**\Pipedrive\versions\v2\Model\OrganizationRequestBody**](../Model/OrganizationRequestBody.md)|  | [optional]

### Return type

[**\Pipedrive\versions\v2\Model\PostPatchGetOrganization**](../Model/PostPatchGetOrganization.md)

### Authorization

[api_key](../README.md#api_key), [oauth2](../README.md#oauth2)

### HTTP request headers

- **Content-Type**: `application/json`
- **Accept**: `application/json`

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints)
[[Back to Model list]](../README.md#documentation-for-models)
[[Back to README]](../README.md)

## `updatePerson()`

```php
updatePerson($id, $person_request_body): \Pipedrive\versions\v2\Model\PostPatchGetPerson
```

Update a person

Updates the properties of a person.

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');


// Configure API key authorization: api_key
$config = (new Pipedrive\versions\v2\Configuration())->setApiKey('api_token', 'YOUR_API_KEY');
// Uncomment below to setup prefix (e.g. Bearer) for API key, if needed
// $config = (new Pipedrive\versions\v2\Configuration())->setApiKeyPrefix('api_token', 'Bearer');

// Configure OAuth2 access token for authorization: oauth2
$config = (new Pipedrive\versions\v2\Configuration())->setAccessToken('YOUR_ACCESS_TOKEN');


$apiInstance = new Pipedrive\versions\v2\Api\BetaApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$id = 56; // int | The ID of the person
$person_request_body = new \Pipedrive\versions\v2\Model\PersonRequestBody(); // \Pipedrive\versions\v2\Model\PersonRequestBody

try {
    $result = $apiInstance->updatePerson($id, $person_request_body);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling BetaApi->updatePerson: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **id** | **int**| The ID of the person |
 **person_request_body** | [**\Pipedrive\versions\v2\Model\PersonRequestBody**](../Model/PersonRequestBody.md)|  | [optional]

### Return type

[**\Pipedrive\versions\v2\Model\PostPatchGetPerson**](../Model/PostPatchGetPerson.md)

### Authorization

[api_key](../README.md#api_key), [oauth2](../README.md#oauth2)

### HTTP request headers

- **Content-Type**: `application/json`
- **Accept**: `application/json`

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints)
[[Back to Model list]](../README.md#documentation-for-models)
[[Back to README]](../README.md)
