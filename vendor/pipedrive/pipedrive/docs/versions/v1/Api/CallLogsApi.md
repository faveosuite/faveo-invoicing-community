# Pipedrive\versions\v1\CallLogsApi

All URIs are relative to https://api.pipedrive.com/v1.

Method | HTTP request | Description
------------- | ------------- | -------------
[**addCallLog()**](CallLogsApi.md#addCallLog) | **POST** /callLogs | Add a call log
[**addCallLogAudioFile()**](CallLogsApi.md#addCallLogAudioFile) | **POST** /callLogs/{id}/recordings | Attach an audio file to the call log
[**deleteCallLog()**](CallLogsApi.md#deleteCallLog) | **DELETE** /callLogs/{id} | Delete a call log
[**getCallLog()**](CallLogsApi.md#getCallLog) | **GET** /callLogs/{id} | Get details of a call log
[**getUserCallLogs()**](CallLogsApi.md#getUserCallLogs) | **GET** /callLogs | Get all call logs assigned to a particular user


## `addCallLog()`

```php
addCallLog($call_log_object): \Pipedrive\versions\v1\Model\CallLogResponse200
```

Add a call log

Adds a new call log.

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


$apiInstance = new Pipedrive\versions\v1\Api\CallLogsApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$call_log_object = new \Pipedrive\versions\v1\Model\CallLogObject(); // \Pipedrive\versions\v1\Model\CallLogObject

try {
    $result = $apiInstance->addCallLog($call_log_object);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling CallLogsApi->addCallLog: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **call_log_object** | [**\Pipedrive\versions\v1\Model\CallLogObject**](../Model/CallLogObject.md)|  | [optional]

### Return type

[**\Pipedrive\versions\v1\Model\CallLogResponse200**](../Model/CallLogResponse200.md)

### Authorization

[api_key](../README.md#api_key), [oauth2](../README.md#oauth2)

### HTTP request headers

- **Content-Type**: `application/json`
- **Accept**: `application/json`

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints)
[[Back to Model list]](../README.md#documentation-for-models)
[[Back to README]](../README.md)

## `addCallLogAudioFile()`

```php
addCallLogAudioFile($id, $file): \Pipedrive\versions\v1\Model\BaseResponse
```

Attach an audio file to the call log

Adds an audio recording to the call log. That audio can be played by those who have access to the call log object.

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


$apiInstance = new Pipedrive\versions\v1\Api\CallLogsApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$id = 3cde3b05035cae14dcfc172bd8000d08; // string | The ID received when you create the call log
$file = "/path/to/file.txt"; // \SplFileObject | Audio file supported by the HTML5 specification

try {
    $result = $apiInstance->addCallLogAudioFile($id, $file);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling CallLogsApi->addCallLogAudioFile: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **id** | **string**| The ID received when you create the call log |
 **file** | **\SplFileObject****\SplFileObject**| Audio file supported by the HTML5 specification |

### Return type

[**\Pipedrive\versions\v1\Model\BaseResponse**](../Model/BaseResponse.md)

### Authorization

[api_key](../README.md#api_key), [oauth2](../README.md#oauth2)

### HTTP request headers

- **Content-Type**: `multipart/form-data`
- **Accept**: `application/json`

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints)
[[Back to Model list]](../README.md#documentation-for-models)
[[Back to README]](../README.md)

## `deleteCallLog()`

```php
deleteCallLog($id): \Pipedrive\versions\v1\Model\BaseResponse
```

Delete a call log

Deletes a call log. If there is an audio recording attached to it, it will also be deleted. The related activity will not be removed by this request. If you want to remove the related activities, please use the endpoint which is specific for activities.

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


$apiInstance = new Pipedrive\versions\v1\Api\CallLogsApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$id = 3cde3b05035cae14dcfc172bd8000d08; // string | The ID received when you create the call log

try {
    $result = $apiInstance->deleteCallLog($id);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling CallLogsApi->deleteCallLog: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **id** | **string**| The ID received when you create the call log |

### Return type

[**\Pipedrive\versions\v1\Model\BaseResponse**](../Model/BaseResponse.md)

### Authorization

[api_key](../README.md#api_key), [oauth2](../README.md#oauth2)

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json`

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints)
[[Back to Model list]](../README.md#documentation-for-models)
[[Back to README]](../README.md)

## `getCallLog()`

```php
getCallLog($id): \Pipedrive\versions\v1\Model\CallLogResponse200
```

Get details of a call log

Returns details of a specific call log.

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


$apiInstance = new Pipedrive\versions\v1\Api\CallLogsApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$id = 3cde3b05035cae14dcfc172bd8000d08; // string | The ID received when you create the call log

try {
    $result = $apiInstance->getCallLog($id);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling CallLogsApi->getCallLog: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **id** | **string**| The ID received when you create the call log |

### Return type

[**\Pipedrive\versions\v1\Model\CallLogResponse200**](../Model/CallLogResponse200.md)

### Authorization

[api_key](../README.md#api_key), [oauth2](../README.md#oauth2)

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json`

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints)
[[Back to Model list]](../README.md#documentation-for-models)
[[Back to README]](../README.md)

## `getUserCallLogs()`

```php
getUserCallLogs($start, $limit): \Pipedrive\versions\v1\Model\CallLogsResponse
```

Get all call logs assigned to a particular user

Returns all call logs assigned to a particular user.

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


$apiInstance = new Pipedrive\versions\v1\Api\CallLogsApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$start = 0; // int | Pagination start
$limit = 56; // int | For pagination, the limit of entries to be returned. The upper limit is 50.

try {
    $result = $apiInstance->getUserCallLogs($start, $limit);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling CallLogsApi->getUserCallLogs: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **start** | **int**| Pagination start | [optional] [default to 0]
 **limit** | **int**| For pagination, the limit of entries to be returned. The upper limit is 50. | [optional]

### Return type

[**\Pipedrive\versions\v1\Model\CallLogsResponse**](../Model/CallLogsResponse.md)

### Authorization

[api_key](../README.md#api_key), [oauth2](../README.md#oauth2)

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json`

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints)
[[Back to Model list]](../README.md#documentation-for-models)
[[Back to README]](../README.md)
