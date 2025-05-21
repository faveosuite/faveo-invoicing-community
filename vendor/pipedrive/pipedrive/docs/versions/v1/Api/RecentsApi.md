# Pipedrive\versions\v1\RecentsApi

All URIs are relative to https://api.pipedrive.com/v1.

Method | HTTP request | Description
------------- | ------------- | -------------
[**getRecents()**](RecentsApi.md#getRecents) | **GET** /recents | Get recents


## `getRecents()`

```php
getRecents($since_timestamp, $items, $start, $limit): \Pipedrive\versions\v1\Model\GetRecents
```

Get recents

Returns data about all recent changes occurred after the given timestamp.

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


$apiInstance = new Pipedrive\versions\v1\Api\RecentsApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$since_timestamp = 'since_timestamp_example'; // string | The timestamp in UTC. Format: YYYY-MM-DD HH:MM:SS.
$items = 'items_example'; // string | Multiple selection of item types to include in the query (optional)
$start = 0; // int | Pagination start
$limit = 56; // int | Items shown per page

try {
    $result = $apiInstance->getRecents($since_timestamp, $items, $start, $limit);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling RecentsApi->getRecents: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **since_timestamp** | **string**| The timestamp in UTC. Format: YYYY-MM-DD HH:MM:SS. |
 **items** | **string**| Multiple selection of item types to include in the query (optional) | [optional]
 **start** | **int**| Pagination start | [optional] [default to 0]
 **limit** | **int**| Items shown per page | [optional]

### Return type

[**\Pipedrive\versions\v1\Model\GetRecents**](../Model/GetRecents.md)

### Authorization

[api_key](../README.md#api_key), [oauth2](../README.md#oauth2)

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json`

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints)
[[Back to Model list]](../README.md#documentation-for-models)
[[Back to README]](../README.md)
