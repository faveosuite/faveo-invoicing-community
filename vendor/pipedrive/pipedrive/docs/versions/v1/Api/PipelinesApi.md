# Pipedrive\versions\v1\PipelinesApi

All URIs are relative to https://api.pipedrive.com/v1.

Method | HTTP request | Description
------------- | ------------- | -------------
[**getPipelineConversionStatistics()**](PipelinesApi.md#getPipelineConversionStatistics) | **GET** /pipelines/{id}/conversion_statistics | Get deals conversion rates in pipeline
[**getPipelineDeals()**](PipelinesApi.md#getPipelineDeals) | **GET** /pipelines/{id}/deals | Get deals in a pipeline
[**getPipelineMovementStatistics()**](PipelinesApi.md#getPipelineMovementStatistics) | **GET** /pipelines/{id}/movement_statistics | Get deals movements in pipeline


## `getPipelineConversionStatistics()`

```php
getPipelineConversionStatistics($id, $start_date, $end_date, $user_id): \Pipedrive\versions\v1\Model\GetDealsConversionRatesInPipeline
```

Get deals conversion rates in pipeline

Returns all stage-to-stage conversion and pipeline-to-close rates for the given time period.

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


$apiInstance = new Pipedrive\versions\v1\Api\PipelinesApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$id = 56; // int | The ID of the pipeline
$start_date = new \DateTime("2013-10-20T19:20:30+01:00"); // \DateTime | The start of the period. Date in format of YYYY-MM-DD.
$end_date = new \DateTime("2013-10-20T19:20:30+01:00"); // \DateTime | The end of the period. Date in format of YYYY-MM-DD.
$user_id = 56; // int | The ID of the user who's pipeline metrics statistics to fetch. If omitted, the authorized user will be used.

try {
    $result = $apiInstance->getPipelineConversionStatistics($id, $start_date, $end_date, $user_id);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling PipelinesApi->getPipelineConversionStatistics: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **id** | **int**| The ID of the pipeline |
 **start_date** | **\DateTime**| The start of the period. Date in format of YYYY-MM-DD. |
 **end_date** | **\DateTime**| The end of the period. Date in format of YYYY-MM-DD. |
 **user_id** | **int**| The ID of the user who&#39;s pipeline metrics statistics to fetch. If omitted, the authorized user will be used. | [optional]

### Return type

[**\Pipedrive\versions\v1\Model\GetDealsConversionRatesInPipeline**](../Model/GetDealsConversionRatesInPipeline.md)

### Authorization

[api_key](../README.md#api_key), [oauth2](../README.md#oauth2)

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json`

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints)
[[Back to Model list]](../README.md#documentation-for-models)
[[Back to README]](../README.md)

## `getPipelineDeals()`

```php
getPipelineDeals($id, $filter_id, $user_id, $everyone, $stage_id, $start, $limit, $get_summary, $totals_convert_currency): \Pipedrive\versions\v1\Model\GetStageDeals
```

Get deals in a pipeline

Lists deals in a specific pipeline across all its stages. If no parameters are provided open deals owned by the authorized user will be returned. <br>This endpoint has been deprecated. Please use <a href=\"https://developers.pipedrive.com/docs/api/v1/Deals#getDeals\" target=\"_blank\" rel=\"noopener noreferrer\">GET /api/v2/deals?pipeline_id={id}</a> instead.

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


$apiInstance = new Pipedrive\versions\v1\Api\PipelinesApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$id = 56; // int | The ID of the pipeline
$filter_id = 56; // int | If supplied, only deals matching the given filter will be returned
$user_id = 56; // int | If supplied, `filter_id` will not be considered and only deals owned by the given user will be returned. If omitted, deals owned by the authorized user will be returned.
$everyone = new \Pipedrive\versions\v1\Model\\Pipedrive\versions\v1\Model\NumberBoolean(); // \Pipedrive\versions\v1\Model\NumberBoolean | If supplied, `filter_id` and `user_id` will not be considered – instead, deals owned by everyone will be returned
$stage_id = 56; // int | If supplied, only deals within the given stage will be returned
$start = 0; // int | Pagination start
$limit = 56; // int | Items shown per page
$get_summary = new \Pipedrive\versions\v1\Model\\Pipedrive\versions\v1\Model\NumberBoolean(); // \Pipedrive\versions\v1\Model\NumberBoolean | Whether to include a summary of the pipeline in the `additional_data` or not
$totals_convert_currency = 'totals_convert_currency_example'; // string | The 3-letter currency code of any of the supported currencies. When supplied, `per_stages_converted` is returned inside `deals_summary` inside `additional_data` which contains the currency-converted total amounts in the given currency per each stage. You may also set this parameter to `default_currency` in which case users default currency is used. Only works when `get_summary` parameter flag is enabled.

try {
    $result = $apiInstance->getPipelineDeals($id, $filter_id, $user_id, $everyone, $stage_id, $start, $limit, $get_summary, $totals_convert_currency);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling PipelinesApi->getPipelineDeals: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **id** | **int**| The ID of the pipeline |
 **filter_id** | **int**| If supplied, only deals matching the given filter will be returned | [optional]
 **user_id** | **int**| If supplied, &#x60;filter_id&#x60; will not be considered and only deals owned by the given user will be returned. If omitted, deals owned by the authorized user will be returned. | [optional]
 **everyone** | [**\Pipedrive\versions\v1\Model\NumberBoolean**](../Model/.md)| If supplied, &#x60;filter_id&#x60; and &#x60;user_id&#x60; will not be considered – instead, deals owned by everyone will be returned | [optional]
 **stage_id** | **int**| If supplied, only deals within the given stage will be returned | [optional]
 **start** | **int**| Pagination start | [optional] [default to 0]
 **limit** | **int**| Items shown per page | [optional]
 **get_summary** | [**\Pipedrive\versions\v1\Model\NumberBoolean**](../Model/.md)| Whether to include a summary of the pipeline in the &#x60;additional_data&#x60; or not | [optional]
 **totals_convert_currency** | **string**| The 3-letter currency code of any of the supported currencies. When supplied, &#x60;per_stages_converted&#x60; is returned inside &#x60;deals_summary&#x60; inside &#x60;additional_data&#x60; which contains the currency-converted total amounts in the given currency per each stage. You may also set this parameter to &#x60;default_currency&#x60; in which case users default currency is used. Only works when &#x60;get_summary&#x60; parameter flag is enabled. | [optional]

### Return type

[**\Pipedrive\versions\v1\Model\GetStageDeals**](../Model/GetStageDeals.md)

### Authorization

[api_key](../README.md#api_key), [oauth2](../README.md#oauth2)

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json`

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints)
[[Back to Model list]](../README.md#documentation-for-models)
[[Back to README]](../README.md)

## `getPipelineMovementStatistics()`

```php
getPipelineMovementStatistics($id, $start_date, $end_date, $user_id): \Pipedrive\versions\v1\Model\GetDealsMovementsInPipeline
```

Get deals movements in pipeline

Returns statistics for deals movements for the given time period.

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


$apiInstance = new Pipedrive\versions\v1\Api\PipelinesApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$id = 56; // int | The ID of the pipeline
$start_date = new \DateTime("2013-10-20T19:20:30+01:00"); // \DateTime | The start of the period. Date in format of YYYY-MM-DD.
$end_date = new \DateTime("2013-10-20T19:20:30+01:00"); // \DateTime | The end of the period. Date in format of YYYY-MM-DD.
$user_id = 56; // int | The ID of the user who's pipeline statistics to fetch. If omitted, the authorized user will be used.

try {
    $result = $apiInstance->getPipelineMovementStatistics($id, $start_date, $end_date, $user_id);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling PipelinesApi->getPipelineMovementStatistics: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **id** | **int**| The ID of the pipeline |
 **start_date** | **\DateTime**| The start of the period. Date in format of YYYY-MM-DD. |
 **end_date** | **\DateTime**| The end of the period. Date in format of YYYY-MM-DD. |
 **user_id** | **int**| The ID of the user who&#39;s pipeline statistics to fetch. If omitted, the authorized user will be used. | [optional]

### Return type

[**\Pipedrive\versions\v1\Model\GetDealsMovementsInPipeline**](../Model/GetDealsMovementsInPipeline.md)

### Authorization

[api_key](../README.md#api_key), [oauth2](../README.md#oauth2)

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json`

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints)
[[Back to Model list]](../README.md#documentation-for-models)
[[Back to README]](../README.md)
