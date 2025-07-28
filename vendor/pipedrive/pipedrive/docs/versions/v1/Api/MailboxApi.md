# Pipedrive\versions\v1\MailboxApi

All URIs are relative to https://api.pipedrive.com/v1.

Method | HTTP request | Description
------------- | ------------- | -------------
[**deleteMailThread()**](MailboxApi.md#deleteMailThread) | **DELETE** /mailbox/mailThreads/{id} | Delete mail thread
[**getMailMessage()**](MailboxApi.md#getMailMessage) | **GET** /mailbox/mailMessages/{id} | Get one mail message
[**getMailThread()**](MailboxApi.md#getMailThread) | **GET** /mailbox/mailThreads/{id} | Get one mail thread
[**getMailThreadMessages()**](MailboxApi.md#getMailThreadMessages) | **GET** /mailbox/mailThreads/{id}/mailMessages | Get all mail messages of mail thread
[**getMailThreads()**](MailboxApi.md#getMailThreads) | **GET** /mailbox/mailThreads | Get mail threads
[**updateMailThreadDetails()**](MailboxApi.md#updateMailThreadDetails) | **PUT** /mailbox/mailThreads/{id} | Update mail thread details


## `deleteMailThread()`

```php
deleteMailThread($id): \Pipedrive\versions\v1\Model\MailThreadDelete
```

Delete mail thread

Marks a mail thread as deleted.

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


$apiInstance = new Pipedrive\versions\v1\Api\MailboxApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$id = 56; // int | The ID of the mail thread

try {
    $result = $apiInstance->deleteMailThread($id);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling MailboxApi->deleteMailThread: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **id** | **int**| The ID of the mail thread |

### Return type

[**\Pipedrive\versions\v1\Model\MailThreadDelete**](../Model/MailThreadDelete.md)

### Authorization

[api_key](../README.md#api_key), [oauth2](../README.md#oauth2)

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json`

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints)
[[Back to Model list]](../README.md#documentation-for-models)
[[Back to README]](../README.md)

## `getMailMessage()`

```php
getMailMessage($id, $include_body): \Pipedrive\versions\v1\Model\MailMessage
```

Get one mail message

Returns data about a specific mail message.

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


$apiInstance = new Pipedrive\versions\v1\Api\MailboxApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$id = 56; // int | The ID of the mail message to fetch
$include_body = new \Pipedrive\versions\v1\Model\\Pipedrive\versions\v1\Model\NumberBooleanDefault0(); // \Pipedrive\versions\v1\Model\NumberBooleanDefault0 | Whether to include the full message body or not. `0` = Don't include, `1` = Include.

try {
    $result = $apiInstance->getMailMessage($id, $include_body);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling MailboxApi->getMailMessage: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **id** | **int**| The ID of the mail message to fetch |
 **include_body** | [**\Pipedrive\versions\v1\Model\NumberBooleanDefault0**](../Model/.md)| Whether to include the full message body or not. &#x60;0&#x60; &#x3D; Don&#39;t include, &#x60;1&#x60; &#x3D; Include. | [optional]

### Return type

[**\Pipedrive\versions\v1\Model\MailMessage**](../Model/MailMessage.md)

### Authorization

[api_key](../README.md#api_key), [oauth2](../README.md#oauth2)

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json`

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints)
[[Back to Model list]](../README.md#documentation-for-models)
[[Back to README]](../README.md)

## `getMailThread()`

```php
getMailThread($id): \Pipedrive\versions\v1\Model\MailThreadOne
```

Get one mail thread

Returns a specific mail thread.

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


$apiInstance = new Pipedrive\versions\v1\Api\MailboxApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$id = 56; // int | The ID of the mail thread

try {
    $result = $apiInstance->getMailThread($id);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling MailboxApi->getMailThread: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **id** | **int**| The ID of the mail thread |

### Return type

[**\Pipedrive\versions\v1\Model\MailThreadOne**](../Model/MailThreadOne.md)

### Authorization

[api_key](../README.md#api_key), [oauth2](../README.md#oauth2)

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json`

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints)
[[Back to Model list]](../README.md#documentation-for-models)
[[Back to README]](../README.md)

## `getMailThreadMessages()`

```php
getMailThreadMessages($id): \Pipedrive\versions\v1\Model\MailThreadMessages
```

Get all mail messages of mail thread

Returns all the mail messages inside a specified mail thread.

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


$apiInstance = new Pipedrive\versions\v1\Api\MailboxApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$id = 56; // int | The ID of the mail thread

try {
    $result = $apiInstance->getMailThreadMessages($id);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling MailboxApi->getMailThreadMessages: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **id** | **int**| The ID of the mail thread |

### Return type

[**\Pipedrive\versions\v1\Model\MailThreadMessages**](../Model/MailThreadMessages.md)

### Authorization

[api_key](../README.md#api_key), [oauth2](../README.md#oauth2)

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json`

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints)
[[Back to Model list]](../README.md#documentation-for-models)
[[Back to README]](../README.md)

## `getMailThreads()`

```php
getMailThreads($folder, $start, $limit): \Pipedrive\versions\v1\Model\MailThread
```

Get mail threads

Returns mail threads in a specified folder ordered by the most recent message within.

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


$apiInstance = new Pipedrive\versions\v1\Api\MailboxApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$folder = 'inbox'; // string | The type of folder to fetch
$start = 0; // int | Pagination start
$limit = 56; // int | Items shown per page

try {
    $result = $apiInstance->getMailThreads($folder, $start, $limit);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling MailboxApi->getMailThreads: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **folder** | **string**| The type of folder to fetch | [default to &#39;inbox&#39;]
 **start** | **int**| Pagination start | [optional] [default to 0]
 **limit** | **int**| Items shown per page | [optional]

### Return type

[**\Pipedrive\versions\v1\Model\MailThread**](../Model/MailThread.md)

### Authorization

[api_key](../README.md#api_key), [oauth2](../README.md#oauth2)

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json`

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints)
[[Back to Model list]](../README.md#documentation-for-models)
[[Back to README]](../README.md)

## `updateMailThreadDetails()`

```php
updateMailThreadDetails($id, $deal_id, $lead_id, $shared_flag, $read_flag, $archived_flag): \Pipedrive\versions\v1\Model\MailThreadPut
```

Update mail thread details

Updates the properties of a mail thread.

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


$apiInstance = new Pipedrive\versions\v1\Api\MailboxApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$id = 56; // int | The ID of the mail thread
$deal_id = 56; // int | The ID of the deal this thread is associated with
$lead_id = 'lead_id_example'; // string | The ID of the lead this thread is associated with
$shared_flag = new \Pipedrive\versions\v1\Model\NumberBoolean(); // \Pipedrive\versions\v1\Model\NumberBoolean | Whether this thread is shared with other users in your company
$read_flag = new \Pipedrive\versions\v1\Model\NumberBoolean(); // \Pipedrive\versions\v1\Model\NumberBoolean | Whether this thread is read or unread
$archived_flag = new \Pipedrive\versions\v1\Model\NumberBoolean(); // \Pipedrive\versions\v1\Model\NumberBoolean | Whether this thread is archived or not. You can only archive threads that belong to Inbox folder. Archived threads will disappear from Inbox.

try {
    $result = $apiInstance->updateMailThreadDetails($id, $deal_id, $lead_id, $shared_flag, $read_flag, $archived_flag);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling MailboxApi->updateMailThreadDetails: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **id** | **int**| The ID of the mail thread |
 **deal_id** | **int**| The ID of the deal this thread is associated with | [optional]
 **lead_id** | **string**| The ID of the lead this thread is associated with | [optional]
 **shared_flag** | [**NumberBoolean**](../Model/NumberBoolean.md)| Whether this thread is shared with other users in your company | [optional]
 **read_flag** | [**NumberBoolean**](../Model/NumberBoolean.md)| Whether this thread is read or unread | [optional]
 **archived_flag** | [**NumberBoolean**](../Model/NumberBoolean.md)| Whether this thread is archived or not. You can only archive threads that belong to Inbox folder. Archived threads will disappear from Inbox. | [optional]

### Return type

[**\Pipedrive\versions\v1\Model\MailThreadPut**](../Model/MailThreadPut.md)

### Authorization

[api_key](../README.md#api_key), [oauth2](../README.md#oauth2)

### HTTP request headers

- **Content-Type**: `application/x-www-form-urlencoded`
- **Accept**: `application/json`

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints)
[[Back to Model list]](../README.md#documentation-for-models)
[[Back to README]](../README.md)
