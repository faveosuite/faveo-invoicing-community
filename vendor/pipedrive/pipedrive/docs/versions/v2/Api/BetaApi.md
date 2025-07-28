# Pipedrive\versions\v2\BetaApi

All URIs are relative to https://api.pipedrive.com/api/v2.

Method | HTTP request | Description
------------- | ------------- | -------------
[**convertDealToLead()**](BetaApi.md#convertDealToLead) | **POST** /deals/{id}/convert/lead | Convert a deal to a lead (BETA)
[**convertLeadToDeal()**](BetaApi.md#convertLeadToDeal) | **POST** /leads/{id}/convert/deal | Convert a lead to a deal (BETA)
[**deleteInstallment()**](BetaApi.md#deleteInstallment) | **DELETE** /deals/{id}/installments/{installment_id} | Delete an installment from a deal
[**getDealConversionStatus()**](BetaApi.md#getDealConversionStatus) | **GET** /deals/{id}/convert/status/{conversion_id} | Get Deal conversion status (BETA)
[**getInstallments()**](BetaApi.md#getInstallments) | **GET** /deals/installments | List installments added to a list of deals
[**getLeadConversionStatus()**](BetaApi.md#getLeadConversionStatus) | **GET** /leads/{id}/convert/status/{conversion_id} | Get Lead conversion status (BETA)
[**postInstallment()**](BetaApi.md#postInstallment) | **POST** /deals/{id}/installments | Add an installment to a deal
[**updateInstallment()**](BetaApi.md#updateInstallment) | **PATCH** /deals/{id}/installments/{installment_id} | Update an installment added to a deal


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


$apiInstance = new Pipedrive\versions\v2\Api\BetaApi(
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
    echo 'Exception when calling BetaApi->convertDealToLead: ', $e->getMessage(), PHP_EOL;
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

## `convertLeadToDeal()`

```php
convertLeadToDeal($id, $inline_object): \Pipedrive\versions\v2\Model\AddConvertLeadToDealResponse
```

Convert a lead to a deal (BETA)

Initiates a conversion of a lead to a deal. The return value is an ID of a job that was assigned to perform the conversion. Related entities (notes, files, emails, activities, ...) are transferred during the process to the target entity. If the conversion is successful, the lead is marked as deleted. To retrieve the created entity ID and the result of the conversion, call the <a href=\"https://developers.pipedrive.com/docs/api/v1/Leads#getLeadConversionStatus\">/api/v2/leads/{lead_id}/convert/status/{conversion_id}</a> endpoint.

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


$apiInstance = new Pipedrive\versions\v2\Api\BetaApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$id = 'id_example'; // string | The ID of the lead to convert
$inline_object = new \Pipedrive\versions\v2\Model\InlineObject(); // \Pipedrive\versions\v2\Model\InlineObject

try {
    $result = $apiInstance->convertLeadToDeal($id, $inline_object);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling BetaApi->convertLeadToDeal: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **id** | **string**| The ID of the lead to convert |
 **inline_object** | [**\Pipedrive\versions\v2\Model\InlineObject**](../Model/InlineObject.md)|  | [optional]

### Return type

[**\Pipedrive\versions\v2\Model\AddConvertLeadToDealResponse**](../Model/AddConvertLeadToDealResponse.md)

### Authorization

[api_key](../README.md#api_key), [oauth2](../README.md#oauth2)

### HTTP request headers

- **Content-Type**: `application/json`
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


$apiInstance = new Pipedrive\versions\v2\Api\BetaApi(
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
    echo 'Exception when calling BetaApi->getDealConversionStatus: ', $e->getMessage(), PHP_EOL;
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

## `getLeadConversionStatus()`

```php
getLeadConversionStatus($id, $conversion_id)
```

Get Lead conversion status (BETA)

Returns data about the conversion. Status is always present and its value (not_started, running, completed, failed, rejected) represents the current state of the conversion. Deal ID is only present if the conversion was successfully finished. This data is only temporary and removed after a few days.

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


$apiInstance = new Pipedrive\versions\v2\Api\BetaApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$id = 'id_example'; // string | The ID of a lead
$conversion_id = 'conversion_id_example'; // string | The ID of the conversion

try {
    $apiInstance->getLeadConversionStatus($id, $conversion_id);
} catch (Exception $e) {
    echo 'Exception when calling BetaApi->getLeadConversionStatus: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **id** | **string**| The ID of a lead |
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
