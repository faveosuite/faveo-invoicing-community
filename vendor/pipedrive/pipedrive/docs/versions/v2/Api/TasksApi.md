# Pipedrive\versions\v2\TasksApi

All URIs are relative to https://api.pipedrive.com/api/v2.

Method | HTTP request | Description
------------- | ------------- | -------------
[**addTask()**](TasksApi.md#addTask) | **POST** /tasks | Add a task
[**deleteTask()**](TasksApi.md#deleteTask) | **DELETE** /tasks/{id} | Delete a task
[**getTask()**](TasksApi.md#getTask) | **GET** /tasks/{id} | Get details of a task
[**getTasks()**](TasksApi.md#getTasks) | **GET** /tasks | Get all tasks
[**updateTask()**](TasksApi.md#updateTask) | **PATCH** /tasks/{id} | Update a task


## `addTask()`

```php
addTask($task_post_request): \Pipedrive\versions\v2\Model\AddTaskResponse
```

Add a task

Adds a new task.

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


$apiInstance = new Pipedrive\versions\v2\Api\TasksApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$task_post_request = new \Pipedrive\versions\v2\Model\TaskPostRequest(); // \Pipedrive\versions\v2\Model\TaskPostRequest

try {
    $result = $apiInstance->addTask($task_post_request);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling TasksApi->addTask: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **task_post_request** | [**\Pipedrive\versions\v2\Model\TaskPostRequest**](../Model/TaskPostRequest.md)|  |

### Return type

[**\Pipedrive\versions\v2\Model\AddTaskResponse**](../Model/AddTaskResponse.md)

### Authorization

[api_key](../README.md#api_key), [oauth2](../README.md#oauth2)

### HTTP request headers

- **Content-Type**: `application/json`
- **Accept**: `application/json`

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints)
[[Back to Model list]](../README.md#documentation-for-models)
[[Back to README]](../README.md)

## `deleteTask()`

```php
deleteTask($id): \Pipedrive\versions\v2\Model\DeleteTaskResponse
```

Delete a task

Marks a task as deleted. If the task has subtasks, those will also be deleted.

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


$apiInstance = new Pipedrive\versions\v2\Api\TasksApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$id = 56; // int | The ID of the task

try {
    $result = $apiInstance->deleteTask($id);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling TasksApi->deleteTask: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **id** | **int**| The ID of the task |

### Return type

[**\Pipedrive\versions\v2\Model\DeleteTaskResponse**](../Model/DeleteTaskResponse.md)

### Authorization

[api_key](../README.md#api_key), [oauth2](../README.md#oauth2)

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json`

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints)
[[Back to Model list]](../README.md#documentation-for-models)
[[Back to README]](../README.md)

## `getTask()`

```php
getTask($id): \Pipedrive\versions\v2\Model\GetTaskResponse
```

Get details of a task

Returns the details of a specific task.

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


$apiInstance = new Pipedrive\versions\v2\Api\TasksApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$id = 56; // int | The ID of the task

try {
    $result = $apiInstance->getTask($id);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling TasksApi->getTask: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **id** | **int**| The ID of the task |

### Return type

[**\Pipedrive\versions\v2\Model\GetTaskResponse**](../Model/GetTaskResponse.md)

### Authorization

[api_key](../README.md#api_key), [oauth2](../README.md#oauth2)

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json`

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints)
[[Back to Model list]](../README.md#documentation-for-models)
[[Back to README]](../README.md)

## `getTasks()`

```php
getTasks($cursor, $limit, $is_done, $is_milestone, $assignee_id, $project_id, $parent_task_id): \Pipedrive\versions\v2\Model\GetTasksResponse
```

Get all tasks

Returns all tasks.

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


$apiInstance = new Pipedrive\versions\v2\Api\TasksApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$cursor = 'cursor_example'; // string | For pagination, the marker (an opaque string value) representing the first item on the next page
$limit = 100; // int | For pagination, the limit of entries to be returned. If not provided, 100 items will be returned. Please note that a maximum value of 500 is allowed.
$is_done = True; // bool | Whether the task is done or not. If omitted, both done and not done tasks are returned.
$is_milestone = True; // bool | Whether the task is a milestone or not. If omitted, both milestone and non-milestone tasks are returned.
$assignee_id = 56; // int | If supplied, only tasks assigned to this user are returned
$project_id = 56; // int | If supplied, only tasks belonging to this project are returned
$parent_task_id = 'parent_task_id_example'; // string | If `null` is supplied, only root-level tasks (without a parent) are returned. If an integer is supplied, only subtasks of that specific task are returned. By default all tasks are returned.

try {
    $result = $apiInstance->getTasks($cursor, $limit, $is_done, $is_milestone, $assignee_id, $project_id, $parent_task_id);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling TasksApi->getTasks: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **cursor** | **string**| For pagination, the marker (an opaque string value) representing the first item on the next page | [optional]
 **limit** | **int**| For pagination, the limit of entries to be returned. If not provided, 100 items will be returned. Please note that a maximum value of 500 is allowed. | [optional]
 **is_done** | **bool**| Whether the task is done or not. If omitted, both done and not done tasks are returned. | [optional]
 **is_milestone** | **bool**| Whether the task is a milestone or not. If omitted, both milestone and non-milestone tasks are returned. | [optional]
 **assignee_id** | **int**| If supplied, only tasks assigned to this user are returned | [optional]
 **project_id** | **int**| If supplied, only tasks belonging to this project are returned | [optional]
 **parent_task_id** | **string**| If &#x60;null&#x60; is supplied, only root-level tasks (without a parent) are returned. If an integer is supplied, only subtasks of that specific task are returned. By default all tasks are returned. | [optional]

### Return type

[**\Pipedrive\versions\v2\Model\GetTasksResponse**](../Model/GetTasksResponse.md)

### Authorization

[api_key](../README.md#api_key), [oauth2](../README.md#oauth2)

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json`

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints)
[[Back to Model list]](../README.md#documentation-for-models)
[[Back to README]](../README.md)

## `updateTask()`

```php
updateTask($id, $task_patch_request): \Pipedrive\versions\v2\Model\UpdateTaskResponse
```

Update a task

Updates a task.

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


$apiInstance = new Pipedrive\versions\v2\Api\TasksApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$id = 56; // int | The ID of the task
$task_patch_request = new \Pipedrive\versions\v2\Model\TaskPatchRequest(); // \Pipedrive\versions\v2\Model\TaskPatchRequest

try {
    $result = $apiInstance->updateTask($id, $task_patch_request);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling TasksApi->updateTask: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **id** | **int**| The ID of the task |
 **task_patch_request** | [**\Pipedrive\versions\v2\Model\TaskPatchRequest**](../Model/TaskPatchRequest.md)|  | [optional]

### Return type

[**\Pipedrive\versions\v2\Model\UpdateTaskResponse**](../Model/UpdateTaskResponse.md)

### Authorization

[api_key](../README.md#api_key), [oauth2](../README.md#oauth2)

### HTTP request headers

- **Content-Type**: `application/json`
- **Accept**: `application/json`

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints)
[[Back to Model list]](../README.md#documentation-for-models)
[[Back to README]](../README.md)
