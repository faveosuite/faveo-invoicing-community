# Pipedrive\versions\v2\PipelinesApi

All URIs are relative to https://api.pipedrive.com/api/v2.

Method | HTTP request | Description
------------- | ------------- | -------------
[**addPipeline()**](PipelinesApi.md#addPipeline) | **POST** /pipelines | Add a new pipeline
[**deletePipeline()**](PipelinesApi.md#deletePipeline) | **DELETE** /pipelines/{id} | Delete a pipeline
[**getPipeline()**](PipelinesApi.md#getPipeline) | **GET** /pipelines/{id} | Get one pipeline
[**getPipelines()**](PipelinesApi.md#getPipelines) | **GET** /pipelines | Get all pipelines
[**updatePipeline()**](PipelinesApi.md#updatePipeline) | **PATCH** /pipelines/{id} | Update a pipeline


## `addPipeline()`

```php
addPipeline($pipeline_request_body): \Pipedrive\versions\v2\Model\PostPatchGetPipeline
```

Add a new pipeline

Adds a new pipeline.

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


$apiInstance = new Pipedrive\versions\v2\Api\PipelinesApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$pipeline_request_body = new \Pipedrive\versions\v2\Model\PipelineRequestBody(); // \Pipedrive\versions\v2\Model\PipelineRequestBody

try {
    $result = $apiInstance->addPipeline($pipeline_request_body);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling PipelinesApi->addPipeline: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **pipeline_request_body** | [**\Pipedrive\versions\v2\Model\PipelineRequestBody**](../Model/PipelineRequestBody.md)|  | [optional]

### Return type

[**\Pipedrive\versions\v2\Model\PostPatchGetPipeline**](../Model/PostPatchGetPipeline.md)

### Authorization

[api_key](../README.md#api_key), [oauth2](../README.md#oauth2)

### HTTP request headers

- **Content-Type**: `application/json`
- **Accept**: `application/json`

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints)
[[Back to Model list]](../README.md#documentation-for-models)
[[Back to README]](../README.md)

## `deletePipeline()`

```php
deletePipeline($id): \Pipedrive\versions\v2\Model\DeletePipelineResponse
```

Delete a pipeline

Marks a pipeline as deleted.

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


$apiInstance = new Pipedrive\versions\v2\Api\PipelinesApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$id = 56; // int | The ID of the pipeline

try {
    $result = $apiInstance->deletePipeline($id);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling PipelinesApi->deletePipeline: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **id** | **int**| The ID of the pipeline |

### Return type

[**\Pipedrive\versions\v2\Model\DeletePipelineResponse**](../Model/DeletePipelineResponse.md)

### Authorization

[api_key](../README.md#api_key), [oauth2](../README.md#oauth2)

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json`

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints)
[[Back to Model list]](../README.md#documentation-for-models)
[[Back to README]](../README.md)

## `getPipeline()`

```php
getPipeline($id): \Pipedrive\versions\v2\Model\PostPatchGetPipeline
```

Get one pipeline

Returns data about a specific pipeline.

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


$apiInstance = new Pipedrive\versions\v2\Api\PipelinesApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$id = 56; // int | The ID of the pipeline

try {
    $result = $apiInstance->getPipeline($id);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling PipelinesApi->getPipeline: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **id** | **int**| The ID of the pipeline |

### Return type

[**\Pipedrive\versions\v2\Model\PostPatchGetPipeline**](../Model/PostPatchGetPipeline.md)

### Authorization

[api_key](../README.md#api_key), [oauth2](../README.md#oauth2)

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json`

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints)
[[Back to Model list]](../README.md#documentation-for-models)
[[Back to README]](../README.md)

## `getPipelines()`

```php
getPipelines($sort_by, $sort_direction, $limit, $cursor): \Pipedrive\versions\v2\Model\GetPipelines
```

Get all pipelines

Returns data about all pipelines.

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


$apiInstance = new Pipedrive\versions\v2\Api\PipelinesApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$sort_by = 'id'; // string | The field to sort by. Supported fields: `id`, `update_time`, `add_time`.
$sort_direction = 'asc'; // string | The sorting direction. Supported values: `asc`, `desc`.
$limit = 100; // int | For pagination, the limit of entries to be returned. If not provided, 100 items will be returned. Please note that a maximum value of 500 is allowed.
$cursor = 'cursor_example'; // string | For pagination, the marker (an opaque string value) representing the first item on the next page

try {
    $result = $apiInstance->getPipelines($sort_by, $sort_direction, $limit, $cursor);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling PipelinesApi->getPipelines: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **sort_by** | **string**| The field to sort by. Supported fields: &#x60;id&#x60;, &#x60;update_time&#x60;, &#x60;add_time&#x60;. | [optional] [default to &#39;id&#39;]
 **sort_direction** | **string**| The sorting direction. Supported values: &#x60;asc&#x60;, &#x60;desc&#x60;. | [optional] [default to &#39;asc&#39;]
 **limit** | **int**| For pagination, the limit of entries to be returned. If not provided, 100 items will be returned. Please note that a maximum value of 500 is allowed. | [optional]
 **cursor** | **string**| For pagination, the marker (an opaque string value) representing the first item on the next page | [optional]

### Return type

[**\Pipedrive\versions\v2\Model\GetPipelines**](../Model/GetPipelines.md)

### Authorization

[api_key](../README.md#api_key), [oauth2](../README.md#oauth2)

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json`

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints)
[[Back to Model list]](../README.md#documentation-for-models)
[[Back to README]](../README.md)

## `updatePipeline()`

```php
updatePipeline($id, $pipeline_request_body): \Pipedrive\versions\v2\Model\PostPatchGetPipeline
```

Update a pipeline

Updates the properties of a pipeline.

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


$apiInstance = new Pipedrive\versions\v2\Api\PipelinesApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$id = 56; // int | The ID of the pipeline
$pipeline_request_body = new \Pipedrive\versions\v2\Model\PipelineRequestBody(); // \Pipedrive\versions\v2\Model\PipelineRequestBody

try {
    $result = $apiInstance->updatePipeline($id, $pipeline_request_body);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling PipelinesApi->updatePipeline: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **id** | **int**| The ID of the pipeline |
 **pipeline_request_body** | [**\Pipedrive\versions\v2\Model\PipelineRequestBody**](../Model/PipelineRequestBody.md)|  | [optional]

### Return type

[**\Pipedrive\versions\v2\Model\PostPatchGetPipeline**](../Model/PostPatchGetPipeline.md)

### Authorization

[api_key](../README.md#api_key), [oauth2](../README.md#oauth2)

### HTTP request headers

- **Content-Type**: `application/json`
- **Accept**: `application/json`

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints)
[[Back to Model list]](../README.md#documentation-for-models)
[[Back to README]](../README.md)
