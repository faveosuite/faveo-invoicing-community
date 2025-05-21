# Pipedrive\versions\v1\ChannelsApi

All URIs are relative to https://api.pipedrive.com/v1.

Method | HTTP request | Description
------------- | ------------- | -------------
[**addChannel()**](ChannelsApi.md#addChannel) | **POST** /channels | Add a channel
[**deleteChannel()**](ChannelsApi.md#deleteChannel) | **DELETE** /channels/{id} | Delete a channel
[**deleteConversation()**](ChannelsApi.md#deleteConversation) | **DELETE** /channels/{channel-id}/conversations/{conversation-id} | Delete a conversation
[**receiveMessage()**](ChannelsApi.md#receiveMessage) | **POST** /channels/messages/receive | Receives an incoming message


## `addChannel()`

```php
addChannel($channel_object): \Pipedrive\versions\v1\Model\ChannelObjectResponse
```

Add a channel

Adds a new messaging channel, only admins are able to register new channels. It will use the getConversations endpoint to fetch conversations, participants and messages afterward. To use the endpoint, you need to have **Messengers integration** OAuth scope enabled and the Messaging manifest ready for the [Messaging app extension](https://pipedrive.readme.io/docs/messaging-app-extension).

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


$apiInstance = new Pipedrive\versions\v1\Api\ChannelsApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$channel_object = new \Pipedrive\versions\v1\Model\ChannelObject(); // \Pipedrive\versions\v1\Model\ChannelObject

try {
    $result = $apiInstance->addChannel($channel_object);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling ChannelsApi->addChannel: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **channel_object** | [**\Pipedrive\versions\v1\Model\ChannelObject**](../Model/ChannelObject.md)|  | [optional]

### Return type

[**\Pipedrive\versions\v1\Model\ChannelObjectResponse**](../Model/ChannelObjectResponse.md)

### Authorization

[api_key](../README.md#api_key), [oauth2](../README.md#oauth2)

### HTTP request headers

- **Content-Type**: `application/json`
- **Accept**: `application/json`

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints)
[[Back to Model list]](../README.md#documentation-for-models)
[[Back to README]](../README.md)

## `deleteChannel()`

```php
deleteChannel($id): \Pipedrive\versions\v1\Model\DeleteChannelSuccess
```

Delete a channel

Deletes an existing messenger’s channel and all related entities (conversations and messages). To use the endpoint, you need to have **Messengers integration** OAuth scope enabled and the Messaging manifest ready for the [Messaging app extension](https://pipedrive.readme.io/docs/messaging-app-extension).

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


$apiInstance = new Pipedrive\versions\v1\Api\ChannelsApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$id = 'id_example'; // string | The ID of the channel provided by the integration

try {
    $result = $apiInstance->deleteChannel($id);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling ChannelsApi->deleteChannel: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **id** | **string**| The ID of the channel provided by the integration |

### Return type

[**\Pipedrive\versions\v1\Model\DeleteChannelSuccess**](../Model/DeleteChannelSuccess.md)

### Authorization

[api_key](../README.md#api_key), [oauth2](../README.md#oauth2)

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json`

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints)
[[Back to Model list]](../README.md#documentation-for-models)
[[Back to README]](../README.md)

## `deleteConversation()`

```php
deleteConversation($channel_id, $conversation_id): \Pipedrive\versions\v1\Model\DeleteConversationSuccess
```

Delete a conversation

Deletes an existing conversation. To use the endpoint, you need to have **Messengers integration** OAuth scope enabled and the Messaging manifest ready for the [Messaging app extension](https://pipedrive.readme.io/docs/messaging-app-extension).

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


$apiInstance = new Pipedrive\versions\v1\Api\ChannelsApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$channel_id = 'channel_id_example'; // string | The ID of the channel provided by the integration
$conversation_id = 'conversation_id_example'; // string | The ID of the conversation provided by the integration

try {
    $result = $apiInstance->deleteConversation($channel_id, $conversation_id);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling ChannelsApi->deleteConversation: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **channel_id** | **string**| The ID of the channel provided by the integration |
 **conversation_id** | **string**| The ID of the conversation provided by the integration |

### Return type

[**\Pipedrive\versions\v1\Model\DeleteConversationSuccess**](../Model/DeleteConversationSuccess.md)

### Authorization

[api_key](../README.md#api_key), [oauth2](../README.md#oauth2)

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json`

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints)
[[Back to Model list]](../README.md#documentation-for-models)
[[Back to README]](../README.md)

## `receiveMessage()`

```php
receiveMessage($message_object): \Pipedrive\versions\v1\Model\GetReceiveMessageSuccessResponse
```

Receives an incoming message

Adds a message to a conversation. To use the endpoint, you need to have **Messengers integration** OAuth scope enabled and the Messaging manifest ready for the [Messaging app extension](https://pipedrive.readme.io/docs/messaging-app-extension).

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


$apiInstance = new Pipedrive\versions\v1\Api\ChannelsApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$message_object = new \Pipedrive\versions\v1\Model\MessageObject(); // \Pipedrive\versions\v1\Model\MessageObject

try {
    $result = $apiInstance->receiveMessage($message_object);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling ChannelsApi->receiveMessage: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **message_object** | [**\Pipedrive\versions\v1\Model\MessageObject**](../Model/MessageObject.md)|  | [optional]

### Return type

[**\Pipedrive\versions\v1\Model\GetReceiveMessageSuccessResponse**](../Model/GetReceiveMessageSuccessResponse.md)

### Authorization

[api_key](../README.md#api_key), [oauth2](../README.md#oauth2)

### HTTP request headers

- **Content-Type**: `application/json`
- **Accept**: `application/json`

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints)
[[Back to Model list]](../README.md#documentation-for-models)
[[Back to README]](../README.md)
