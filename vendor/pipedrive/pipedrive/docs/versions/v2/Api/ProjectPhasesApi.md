# Pipedrive\versions\v2\ProjectPhasesApi

All URIs are relative to https://api.pipedrive.com/api/v2.

Method | HTTP request | Description
------------- | ------------- | -------------
[**addProjectPhase()**](ProjectPhasesApi.md#addProjectPhase) | **POST** /phases | Add a project phase
[**deleteProjectPhase()**](ProjectPhasesApi.md#deleteProjectPhase) | **DELETE** /phases/{id} | Delete a project phase
[**getProjectsPhase()**](ProjectPhasesApi.md#getProjectsPhase) | **GET** /phases/{id} | Get details of a project phase
[**getProjectsPhases()**](ProjectPhasesApi.md#getProjectsPhases) | **GET** /phases | Get project phases
[**updateProjectPhase()**](ProjectPhasesApi.md#updateProjectPhase) | **PATCH** /phases/{id} | Update a project phase


## `addProjectPhase()`

```php
addProjectPhase($phase_request_body): \Pipedrive\versions\v2\Model\PostPatchGetPhase
```

Add a project phase

Adds a new project phase to a board.

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


$apiInstance = new Pipedrive\versions\v2\Api\ProjectPhasesApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$phase_request_body = new \Pipedrive\versions\v2\Model\PhaseRequestBody(); // \Pipedrive\versions\v2\Model\PhaseRequestBody

try {
    $result = $apiInstance->addProjectPhase($phase_request_body);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling ProjectPhasesApi->addProjectPhase: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **phase_request_body** | [**\Pipedrive\versions\v2\Model\PhaseRequestBody**](../Model/PhaseRequestBody.md)|  |

### Return type

[**\Pipedrive\versions\v2\Model\PostPatchGetPhase**](../Model/PostPatchGetPhase.md)

### Authorization

[api_key](../README.md#api_key), [oauth2](../README.md#oauth2)

### HTTP request headers

- **Content-Type**: `application/json`
- **Accept**: `application/json`

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints)
[[Back to Model list]](../README.md#documentation-for-models)
[[Back to README]](../README.md)

## `deleteProjectPhase()`

```php
deleteProjectPhase($id): \Pipedrive\versions\v2\Model\DeleteProjectPhaseResponse
```

Delete a project phase

Marks a project phase as deleted.

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


$apiInstance = new Pipedrive\versions\v2\Api\ProjectPhasesApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$id = 56; // int | The ID of the project phase

try {
    $result = $apiInstance->deleteProjectPhase($id);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling ProjectPhasesApi->deleteProjectPhase: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **id** | **int**| The ID of the project phase |

### Return type

[**\Pipedrive\versions\v2\Model\DeleteProjectPhaseResponse**](../Model/DeleteProjectPhaseResponse.md)

### Authorization

[api_key](../README.md#api_key), [oauth2](../README.md#oauth2)

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json`

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints)
[[Back to Model list]](../README.md#documentation-for-models)
[[Back to README]](../README.md)

## `getProjectsPhase()`

```php
getProjectsPhase($id): \Pipedrive\versions\v2\Model\PostPatchGetPhase
```

Get details of a project phase

Returns the details of a specific project phase.

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


$apiInstance = new Pipedrive\versions\v2\Api\ProjectPhasesApi(
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

[**\Pipedrive\versions\v2\Model\PostPatchGetPhase**](../Model/PostPatchGetPhase.md)

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
getProjectsPhases($board_id): \Pipedrive\versions\v2\Model\GetPhases
```

Get project phases

Returns all active project phases under a specific board.

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


$apiInstance = new Pipedrive\versions\v2\Api\ProjectPhasesApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$board_id = 1; // int | The ID of the board for which phases are requested

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
 **board_id** | **int**| The ID of the board for which phases are requested |

### Return type

[**\Pipedrive\versions\v2\Model\GetPhases**](../Model/GetPhases.md)

### Authorization

[api_key](../README.md#api_key), [oauth2](../README.md#oauth2)

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json`

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints)
[[Back to Model list]](../README.md#documentation-for-models)
[[Back to README]](../README.md)

## `updateProjectPhase()`

```php
updateProjectPhase($id, $phase_request_body): \Pipedrive\versions\v2\Model\PostPatchGetPhase
```

Update a project phase

Updates the properties of a project phase.

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


$apiInstance = new Pipedrive\versions\v2\Api\ProjectPhasesApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$id = 56; // int | The ID of the project phase
$phase_request_body = new \Pipedrive\versions\v2\Model\PhaseRequestBody(); // \Pipedrive\versions\v2\Model\PhaseRequestBody

try {
    $result = $apiInstance->updateProjectPhase($id, $phase_request_body);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling ProjectPhasesApi->updateProjectPhase: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **id** | **int**| The ID of the project phase |
 **phase_request_body** | [**\Pipedrive\versions\v2\Model\PhaseRequestBody**](../Model/PhaseRequestBody.md)|  | [optional]

### Return type

[**\Pipedrive\versions\v2\Model\PostPatchGetPhase**](../Model/PostPatchGetPhase.md)

### Authorization

[api_key](../README.md#api_key), [oauth2](../README.md#oauth2)

### HTTP request headers

- **Content-Type**: `application/json`
- **Accept**: `application/json`

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints)
[[Back to Model list]](../README.md#documentation-for-models)
[[Back to README]](../README.md)
