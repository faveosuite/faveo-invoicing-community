# Pipedrive\versions\v1\StagesApi

All URIs are relative to https://api.pipedrive.com/v1.

Method | HTTP request | Description
------------- | ------------- | -------------
[**getStageDeals()**](StagesApi.md#getStageDeals) | **GET** /stages/{id}/deals | Get deals in a stage


## `getStageDeals()`

```php
getStageDeals($id, $filter_id, $user_id, $everyone, $start, $limit): \Pipedrive\versions\v1\Model\GetStageDeals
```

Get deals in a stage

Lists deals in a specific stage. If no parameters are provided open deals owned by the authorized user will be returned. <br>This endpoint has been deprecated. Please use <a href=\"https://developers.pipedrive.com/docs/api/v1/Deals#getDeals\" target=\"_blank\" rel=\"noopener noreferrer\">GET /api/v2/deals?stage_id={id}</a> instead.

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


$apiInstance = new Pipedrive\versions\v1\Api\StagesApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$id = 56; // int | The ID of the stage
$filter_id = 56; // int | If supplied, only deals matching the given filter will be returned
$user_id = 56; // int | If supplied, `filter_id` will not be considered and only deals owned by the given user will be returned. If omitted, deals owned by the authorized user will be returned.
$everyone = new \Pipedrive\versions\v1\Model\\Pipedrive\versions\v1\Model\NumberBoolean(); // \Pipedrive\versions\v1\Model\NumberBoolean | If supplied, `filter_id` and `user_id` will not be considered – instead, deals owned by everyone will be returned
$start = 0; // int | Pagination start
$limit = 56; // int | Items shown per page

try {
    $result = $apiInstance->getStageDeals($id, $filter_id, $user_id, $everyone, $start, $limit);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling StagesApi->getStageDeals: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **id** | **int**| The ID of the stage |
 **filter_id** | **int**| If supplied, only deals matching the given filter will be returned | [optional]
 **user_id** | **int**| If supplied, &#x60;filter_id&#x60; will not be considered and only deals owned by the given user will be returned. If omitted, deals owned by the authorized user will be returned. | [optional]
 **everyone** | [**\Pipedrive\versions\v1\Model\NumberBoolean**](../Model/.md)| If supplied, &#x60;filter_id&#x60; and &#x60;user_id&#x60; will not be considered – instead, deals owned by everyone will be returned | [optional]
 **start** | **int**| Pagination start | [optional] [default to 0]
 **limit** | **int**| Items shown per page | [optional]

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
