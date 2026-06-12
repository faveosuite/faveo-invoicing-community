# Pipedrive\versions\v2\ProjectBoardsApi

All URIs are relative to https://api.pipedrive.com/api/v2.

Method | HTTP request | Description
------------- | ------------- | -------------
[**addProjectBoard()**](ProjectBoardsApi.md#addProjectBoard) | **POST** /boards | Add a project board
[**deleteProjectBoard()**](ProjectBoardsApi.md#deleteProjectBoard) | **DELETE** /boards/{id} | Delete a project board
[**getProjectsBoard()**](ProjectBoardsApi.md#getProjectsBoard) | **GET** /boards/{id} | Get details of a project board
[**getProjectsBoards()**](ProjectBoardsApi.md#getProjectsBoards) | **GET** /boards | Get all project boards
[**updateProjectBoard()**](ProjectBoardsApi.md#updateProjectBoard) | **PATCH** /boards/{id} | Update a project board


## `addProjectBoard()`

```php
addProjectBoard($board_request_body): \Pipedrive\versions\v2\Model\PostPatchGetBoard
```

Add a project board

Adds a new project board.

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


$apiInstance = new Pipedrive\versions\v2\Api\ProjectBoardsApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$board_request_body = new \Pipedrive\versions\v2\Model\BoardRequestBody(); // \Pipedrive\versions\v2\Model\BoardRequestBody

try {
    $result = $apiInstance->addProjectBoard($board_request_body);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling ProjectBoardsApi->addProjectBoard: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **board_request_body** | [**\Pipedrive\versions\v2\Model\BoardRequestBody**](../Model/BoardRequestBody.md)|  |

### Return type

[**\Pipedrive\versions\v2\Model\PostPatchGetBoard**](../Model/PostPatchGetBoard.md)

### Authorization

[api_key](../README.md#api_key), [oauth2](../README.md#oauth2)

### HTTP request headers

- **Content-Type**: `application/json`
- **Accept**: `application/json`

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints)
[[Back to Model list]](../README.md#documentation-for-models)
[[Back to README]](../README.md)

## `deleteProjectBoard()`

```php
deleteProjectBoard($id): \Pipedrive\versions\v2\Model\DeleteProjectBoardResponse
```

Delete a project board

Marks a project board as deleted.

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


$apiInstance = new Pipedrive\versions\v2\Api\ProjectBoardsApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$id = 56; // int | The ID of the project board

try {
    $result = $apiInstance->deleteProjectBoard($id);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling ProjectBoardsApi->deleteProjectBoard: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **id** | **int**| The ID of the project board |

### Return type

[**\Pipedrive\versions\v2\Model\DeleteProjectBoardResponse**](../Model/DeleteProjectBoardResponse.md)

### Authorization

[api_key](../README.md#api_key), [oauth2](../README.md#oauth2)

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json`

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints)
[[Back to Model list]](../README.md#documentation-for-models)
[[Back to README]](../README.md)

## `getProjectsBoard()`

```php
getProjectsBoard($id): \Pipedrive\versions\v2\Model\PostPatchGetBoard
```

Get details of a project board

Returns the details of a specific project board.

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


$apiInstance = new Pipedrive\versions\v2\Api\ProjectBoardsApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$id = 56; // int | The ID of the project board

try {
    $result = $apiInstance->getProjectsBoard($id);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling ProjectBoardsApi->getProjectsBoard: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **id** | **int**| The ID of the project board |

### Return type

[**\Pipedrive\versions\v2\Model\PostPatchGetBoard**](../Model/PostPatchGetBoard.md)

### Authorization

[api_key](../README.md#api_key), [oauth2](../README.md#oauth2)

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json`

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints)
[[Back to Model list]](../README.md#documentation-for-models)
[[Back to README]](../README.md)

## `getProjectsBoards()`

```php
getProjectsBoards(): \Pipedrive\versions\v2\Model\GetBoards
```

Get all project boards

Returns all active project boards.

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


$apiInstance = new Pipedrive\versions\v2\Api\ProjectBoardsApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);

try {
    $result = $apiInstance->getProjectsBoards();
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling ProjectBoardsApi->getProjectsBoards: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

This endpoint does not need any parameter.

### Return type

[**\Pipedrive\versions\v2\Model\GetBoards**](../Model/GetBoards.md)

### Authorization

[api_key](../README.md#api_key), [oauth2](../README.md#oauth2)

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json`

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints)
[[Back to Model list]](../README.md#documentation-for-models)
[[Back to README]](../README.md)

## `updateProjectBoard()`

```php
updateProjectBoard($id, $board_request_body): \Pipedrive\versions\v2\Model\PostPatchGetBoard
```

Update a project board

Updates the properties of a project board.

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


$apiInstance = new Pipedrive\versions\v2\Api\ProjectBoardsApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$id = 56; // int | The ID of the project board
$board_request_body = new \Pipedrive\versions\v2\Model\BoardRequestBody(); // \Pipedrive\versions\v2\Model\BoardRequestBody

try {
    $result = $apiInstance->updateProjectBoard($id, $board_request_body);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling ProjectBoardsApi->updateProjectBoard: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **id** | **int**| The ID of the project board |
 **board_request_body** | [**\Pipedrive\versions\v2\Model\BoardRequestBody**](../Model/BoardRequestBody.md)|  | [optional]

### Return type

[**\Pipedrive\versions\v2\Model\PostPatchGetBoard**](../Model/PostPatchGetBoard.md)

### Authorization

[api_key](../README.md#api_key), [oauth2](../README.md#oauth2)

### HTTP request headers

- **Content-Type**: `application/json`
- **Accept**: `application/json`

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints)
[[Back to Model list]](../README.md#documentation-for-models)
[[Back to README]](../README.md)
