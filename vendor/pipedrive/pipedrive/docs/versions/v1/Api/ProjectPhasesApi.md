# Pipedrive\versions\v1\ProjectPhasesApi

All URIs are relative to https://api.pipedrive.com/v1.

Method | HTTP request | Description
------------- | ------------- | -------------
[**getProjectsPhase()**](ProjectPhasesApi.md#getProjectsPhase) | **GET** /projects/phases/{id} | Get details of a phase
[**getProjectsPhases()**](ProjectPhasesApi.md#getProjectsPhases) | **GET** /projects/phases | Get project phases


## `getProjectsPhase()`

```php
getProjectsPhase($id): \Pipedrive\versions\v1\Model\GetProjectPhaseResponse
```

Get details of a phase

Returns the details of a specific project phase.

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


$apiInstance = new Pipedrive\versions\v1\Api\ProjectPhasesApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$id = 56; // int | The ID of the project phase

try {
    $result = $apiInstance->getProjectsPhase($id);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling ProjectPhasesApi->getProjectsPhase: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **id** | **int**| The ID of the project phase |

### Return type

[**\Pipedrive\versions\v1\Model\GetProjectPhaseResponse**](../Model/GetProjectPhaseResponse.md)

### Authorization

[api_key](../README.md#api_key), [oauth2](../README.md#oauth2)

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json`

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints)
[[Back to Model list]](../README.md#documentation-for-models)
[[Back to README]](../README.md)

## `getProjectsPhases()`

```php
getProjectsPhases($board_id): \Pipedrive\versions\v1\Model\GetProjectPhasesResponse
```

Get project phases

Returns all active project phases under a specific board.

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


$apiInstance = new Pipedrive\versions\v1\Api\ProjectPhasesApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$board_id = 1; // int | ID of the board for which phases are requested

try {
    $result = $apiInstance->getProjectsPhases($board_id);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling ProjectPhasesApi->getProjectsPhases: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **board_id** | **int**| ID of the board for which phases are requested |

### Return type

[**\Pipedrive\versions\v1\Model\GetProjectPhasesResponse**](../Model/GetProjectPhasesResponse.md)

### Authorization

[api_key](../README.md#api_key), [oauth2](../README.md#oauth2)

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json`

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints)
[[Back to Model list]](../README.md#documentation-for-models)
[[Back to README]](../README.md)
