# Pipedrive\versions\v2\DealInstallmentsApi

All URIs are relative to https://api.pipedrive.com/api/v2.

Method | HTTP request | Description
------------- | ------------- | -------------
[**deleteInstallment()**](DealInstallmentsApi.md#deleteInstallment) | **DELETE** /deals/{id}/installments/{installment_id} | Delete an installment from a deal
[**getInstallments()**](DealInstallmentsApi.md#getInstallments) | **GET** /deals/installments | List installments added to a list of deals
[**postInstallment()**](DealInstallmentsApi.md#postInstallment) | **POST** /deals/{id}/installments | Add an installment to a deal
[**updateInstallment()**](DealInstallmentsApi.md#updateInstallment) | **PATCH** /deals/{id}/installments/{installment_id} | Update an installment added to a deal


## `deleteInstallment()`

```php
deleteInstallment($id, $installment_id): \Pipedrive\versions\v2\Model\DeleteInstallmentResponse
```

Delete an installment from a deal

Removes an installment from a deal.  Only available in Growth and above plans.

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


$apiInstance = new Pipedrive\versions\v2\Api\DealInstallmentsApi(
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
    echo 'Exception when calling DealInstallmentsApi->deleteInstallment: ', $e->getMessage(), PHP_EOL;
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

## `getInstallments()`

```php
getInstallments($deal_ids, $cursor, $limit, $sort_by, $sort_direction): \Pipedrive\versions\v2\Model\InstallmentsResponse
```

List installments added to a list of deals

Lists installments attached to a list of deals.  Only available in Growth and above plans.

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


$apiInstance = new Pipedrive\versions\v2\Api\DealInstallmentsApi(
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
    echo 'Exception when calling DealInstallmentsApi->getInstallments: ', $e->getMessage(), PHP_EOL;
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

## `postInstallment()`

```php
postInstallment($id, $body): \Pipedrive\versions\v2\Model\AddInstallmentResponse
```

Add an installment to a deal

Adds an installment to a deal.  An installment can only be added if the deal includes at least one one-time product.  If the deal contains at least one recurring product, adding installments is not allowed.  Only available in Growth and above plans.

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


$apiInstance = new Pipedrive\versions\v2\Api\DealInstallmentsApi(
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
    echo 'Exception when calling DealInstallmentsApi->postInstallment: ', $e->getMessage(), PHP_EOL;
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

## `updateInstallment()`

```php
updateInstallment($id, $installment_id, $body): \Pipedrive\versions\v2\Model\UpdateInstallmentResponse
```

Update an installment added to a deal

Edits an installment added to a deal.  Only available in Growth and above plans.

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


$apiInstance = new Pipedrive\versions\v2\Api\DealInstallmentsApi(
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
    echo 'Exception when calling DealInstallmentsApi->updateInstallment: ', $e->getMessage(), PHP_EOL;
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
