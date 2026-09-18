# Pipedrive\versions\v2\BetaApi

All URIs are relative to https://api.pipedrive.com/api/v2.

Method | HTTP request | Description
------------- | ------------- | -------------
[**addProjectBoard()**](BetaApi.md#addProjectBoard) | **POST** /boards | Add a project board
[**addProjectField()**](BetaApi.md#addProjectField) | **POST** /projectFields | Create one project field
[**addProjectFieldOptions()**](BetaApi.md#addProjectFieldOptions) | **POST** /projectFields/{field_code}/options | Add project field options in bulk
[**addProjectPhase()**](BetaApi.md#addProjectPhase) | **POST** /phases | Add a project phase
[**addTask()**](BetaApi.md#addTask) | **POST** /tasks | Add a task
[**deleteProjectBoard()**](BetaApi.md#deleteProjectBoard) | **DELETE** /boards/{id} | Delete a project board
[**deleteProjectField()**](BetaApi.md#deleteProjectField) | **DELETE** /projectFields/{field_code} | Delete one project field
[**deleteProjectFieldOptions()**](BetaApi.md#deleteProjectFieldOptions) | **DELETE** /projectFields/{field_code}/options | Delete project field options in bulk
[**deleteProjectPhase()**](BetaApi.md#deleteProjectPhase) | **DELETE** /phases/{id} | Delete a project phase
[**deleteTask()**](BetaApi.md#deleteTask) | **DELETE** /tasks/{id} | Delete a task
[**getProjectField()**](BetaApi.md#getProjectField) | **GET** /projectFields/{field_code} | Get one project field
[**getProjectFields()**](BetaApi.md#getProjectFields) | **GET** /projectFields | Get all project fields
[**getProjectsBoard()**](BetaApi.md#getProjectsBoard) | **GET** /boards/{id} | Get details of a project board
[**getProjectsBoards()**](BetaApi.md#getProjectsBoards) | **GET** /boards | Get all project boards
[**getProjectsPhase()**](BetaApi.md#getProjectsPhase) | **GET** /phases/{id} | Get details of a project phase
[**getProjectsPhases()**](BetaApi.md#getProjectsPhases) | **GET** /phases | Get project phases
[**getTask()**](BetaApi.md#getTask) | **GET** /tasks/{id} | Get details of a task
[**getTasks()**](BetaApi.md#getTasks) | **GET** /tasks | Get all tasks
[**searchProjects()**](BetaApi.md#searchProjects) | **GET** /projects/search | Search projects
[**updateProjectBoard()**](BetaApi.md#updateProjectBoard) | **PATCH** /boards/{id} | Update a project board
[**updateProjectField()**](BetaApi.md#updateProjectField) | **PATCH** /projectFields/{field_code} | Update one project field
[**updateProjectFieldOptions()**](BetaApi.md#updateProjectFieldOptions) | **PATCH** /projectFields/{field_code}/options | Update project field options in bulk
[**updateProjectPhase()**](BetaApi.md#updateProjectPhase) | **PATCH** /phases/{id} | Update a project phase
[**updateTask()**](BetaApi.md#updateTask) | **PATCH** /tasks/{id} | Update a task


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


$apiInstance = new Pipedrive\versions\v2\Api\BetaApi(
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
    echo 'Exception when calling BetaApi->addProjectBoard: ', $e->getMessage(), PHP_EOL;
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

## `addProjectField()`

```php
addProjectField($create_project_field_request): \Pipedrive\versions\v2\Model\CreateProjectField
```

Create one project field

Creates a new project custom field.

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


$apiInstance = new Pipedrive\versions\v2\Api\BetaApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$create_project_field_request = new \Pipedrive\versions\v2\Model\CreateProjectFieldRequest(); // \Pipedrive\versions\v2\Model\CreateProjectFieldRequest

try {
    $result = $apiInstance->addProjectField($create_project_field_request);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling BetaApi->addProjectField: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **create_project_field_request** | [**\Pipedrive\versions\v2\Model\CreateProjectFieldRequest**](../Model/CreateProjectFieldRequest.md)|  |

### Return type

[**\Pipedrive\versions\v2\Model\CreateProjectField**](../Model/CreateProjectField.md)

### Authorization

[api_key](../README.md#api_key), [oauth2](../README.md#oauth2)

### HTTP request headers

- **Content-Type**: `application/json`
- **Accept**: `application/json`

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints)
[[Back to Model list]](../README.md#documentation-for-models)
[[Back to README]](../README.md)

## `addProjectFieldOptions()`

```php
addProjectFieldOptions($field_code, $request_body): \Pipedrive\versions\v2\Model\InlineResponse200
```

Add project field options in bulk

Adds new options to a project custom field that supports options (enum or set field types). This operation is atomic - all options are added or none are added. Returns only the newly added options.

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


$apiInstance = new Pipedrive\versions\v2\Api\BetaApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$field_code = 'field_code_example'; // string | The unique code identifying the field
$request_body = array(new \stdClass); // object[]

try {
    $result = $apiInstance->addProjectFieldOptions($field_code, $request_body);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling BetaApi->addProjectFieldOptions: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **field_code** | **string**| The unique code identifying the field |
 **request_body** | [**object[]**](../Model/object.md)|  |

### Return type

[**\Pipedrive\versions\v2\Model\InlineResponse200**](../Model/InlineResponse200.md)

### Authorization

[api_key](../README.md#api_key), [oauth2](../README.md#oauth2)

### HTTP request headers

- **Content-Type**: `application/json`
- **Accept**: `application/json`

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints)
[[Back to Model list]](../README.md#documentation-for-models)
[[Back to README]](../README.md)

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


$apiInstance = new Pipedrive\versions\v2\Api\BetaApi(
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
    echo 'Exception when calling BetaApi->addProjectPhase: ', $e->getMessage(), PHP_EOL;
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


$apiInstance = new Pipedrive\versions\v2\Api\BetaApi(
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
    echo 'Exception when calling BetaApi->addTask: ', $e->getMessage(), PHP_EOL;
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


$apiInstance = new Pipedrive\versions\v2\Api\BetaApi(
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
    echo 'Exception when calling BetaApi->deleteProjectBoard: ', $e->getMessage(), PHP_EOL;
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

## `deleteProjectField()`

```php
deleteProjectField($field_code): \Pipedrive\versions\v2\Model\DeleteProjectField
```

Delete one project field

Marks a custom field as deleted.

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


$apiInstance = new Pipedrive\versions\v2\Api\BetaApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$field_code = 'field_code_example'; // string | The unique code identifying the field

try {
    $result = $apiInstance->deleteProjectField($field_code);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling BetaApi->deleteProjectField: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **field_code** | **string**| The unique code identifying the field |

### Return type

[**\Pipedrive\versions\v2\Model\DeleteProjectField**](../Model/DeleteProjectField.md)

### Authorization

[api_key](../README.md#api_key), [oauth2](../README.md#oauth2)

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json`

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints)
[[Back to Model list]](../README.md#documentation-for-models)
[[Back to README]](../README.md)

## `deleteProjectFieldOptions()`

```php
deleteProjectFieldOptions($field_code, $request_body): \Pipedrive\versions\v2\Model\InlineResponse200
```

Delete project field options in bulk

Removes existing options from a project custom field. This operation is atomic and fails if any of the specified option IDs do not exist. Returns only the deleted options.

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


$apiInstance = new Pipedrive\versions\v2\Api\BetaApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$field_code = 'field_code_example'; // string | The unique code identifying the field
$request_body = array(new \stdClass); // object[]

try {
    $result = $apiInstance->deleteProjectFieldOptions($field_code, $request_body);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling BetaApi->deleteProjectFieldOptions: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **field_code** | **string**| The unique code identifying the field |
 **request_body** | [**object[]**](../Model/object.md)|  |

### Return type

[**\Pipedrive\versions\v2\Model\InlineResponse200**](../Model/InlineResponse200.md)

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


$apiInstance = new Pipedrive\versions\v2\Api\BetaApi(
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
    echo 'Exception when calling BetaApi->deleteProjectPhase: ', $e->getMessage(), PHP_EOL;
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


$apiInstance = new Pipedrive\versions\v2\Api\BetaApi(
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
    echo 'Exception when calling BetaApi->deleteTask: ', $e->getMessage(), PHP_EOL;
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

## `getProjectField()`

```php
getProjectField($field_code): \Pipedrive\versions\v2\Model\GetProjectField
```

Get one project field

Returns metadata about a specific project field.

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


$apiInstance = new Pipedrive\versions\v2\Api\BetaApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$field_code = 'field_code_example'; // string | The unique code identifying the field

try {
    $result = $apiInstance->getProjectField($field_code);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling BetaApi->getProjectField: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **field_code** | **string**| The unique code identifying the field |

### Return type

[**\Pipedrive\versions\v2\Model\GetProjectField**](../Model/GetProjectField.md)

### Authorization

[api_key](../README.md#api_key), [oauth2](../README.md#oauth2)

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json`

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints)
[[Back to Model list]](../README.md#documentation-for-models)
[[Back to README]](../README.md)

## `getProjectFields()`

```php
getProjectFields($limit, $cursor): \Pipedrive\versions\v2\Model\GetProjectFields
```

Get all project fields

Returns metadata about all project fields in the company.

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


$apiInstance = new Pipedrive\versions\v2\Api\BetaApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$limit = 100; // int | For pagination, the limit of entries to be returned. If not provided, 100 items will be returned. Please note that a maximum value of 500 is allowed.
$cursor = 'cursor_example'; // string | For pagination, the marker (an opaque string value) representing the first item on the next page

try {
    $result = $apiInstance->getProjectFields($limit, $cursor);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling BetaApi->getProjectFields: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **limit** | **int**| For pagination, the limit of entries to be returned. If not provided, 100 items will be returned. Please note that a maximum value of 500 is allowed. | [optional]
 **cursor** | **string**| For pagination, the marker (an opaque string value) representing the first item on the next page | [optional]

### Return type

[**\Pipedrive\versions\v2\Model\GetProjectFields**](../Model/GetProjectFields.md)

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


$apiInstance = new Pipedrive\versions\v2\Api\BetaApi(
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
    echo 'Exception when calling BetaApi->getProjectsBoard: ', $e->getMessage(), PHP_EOL;
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


$apiInstance = new Pipedrive\versions\v2\Api\BetaApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);

try {
    $result = $apiInstance->getProjectsBoards();
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling BetaApi->getProjectsBoards: ', $e->getMessage(), PHP_EOL;
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


$apiInstance = new Pipedrive\versions\v2\Api\BetaApi(
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
    echo 'Exception when calling BetaApi->getProjectsPhase: ', $e->getMessage(), PHP_EOL;
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


$apiInstance = new Pipedrive\versions\v2\Api\BetaApi(
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
    echo 'Exception when calling BetaApi->getProjectsPhases: ', $e->getMessage(), PHP_EOL;
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


$apiInstance = new Pipedrive\versions\v2\Api\BetaApi(
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
    echo 'Exception when calling BetaApi->getTask: ', $e->getMessage(), PHP_EOL;
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


$apiInstance = new Pipedrive\versions\v2\Api\BetaApi(
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
    echo 'Exception when calling BetaApi->getTasks: ', $e->getMessage(), PHP_EOL;
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

## `searchProjects()`

```php
searchProjects($term, $fields, $exact_match, $person_id, $organization_id, $limit, $cursor): \Pipedrive\versions\v2\Model\ProjectSearchResponse
```

Search projects

Searches all projects by title, description, notes and/or custom fields. This endpoint is a wrapper of <a href=\"https://developers.pipedrive.com/docs/api/v1/ItemSearch#searchItem\">/v1/itemSearch</a> with a narrower OAuth scope. Found projects can be filtered by person ID or organization ID.

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


$apiInstance = new Pipedrive\versions\v2\Api\BetaApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$term = 'term_example'; // string | The search term to look for. Minimum 2 characters (or 1 if using `exact_match`). Please note that the search term has to be URL encoded.
$fields = 'fields_example'; // string | A comma-separated string array. The fields to perform the search from. Defaults to all of them. Only the following custom field types are searchable: `address`, `varchar`, `text`, `varchar_auto`, `double`, `monetary` and `phone`. Read more about searching by custom fields <a href=\"https://support.pipedrive.com/en/article/search-finding-what-you-need#searching-by-custom-fields\" target=\"_blank\" rel=\"noopener noreferrer\">here</a>.
$exact_match = True; // bool | When enabled, only full exact matches against the given term are returned. It is <b>not</b> case sensitive.
$person_id = 56; // int | Will filter projects by the provided person ID
$organization_id = 56; // int | Will filter projects by the provided organization ID
$limit = 100; // int | For pagination, the limit of entries to be returned. If not provided, 100 items will be returned. Please note that a maximum value of 500 is allowed.
$cursor = 'cursor_example'; // string | For pagination, the marker (an opaque string value) representing the first item on the next page

try {
    $result = $apiInstance->searchProjects($term, $fields, $exact_match, $person_id, $organization_id, $limit, $cursor);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling BetaApi->searchProjects: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **term** | **string**| The search term to look for. Minimum 2 characters (or 1 if using &#x60;exact_match&#x60;). Please note that the search term has to be URL encoded. |
 **fields** | **string**| A comma-separated string array. The fields to perform the search from. Defaults to all of them. Only the following custom field types are searchable: &#x60;address&#x60;, &#x60;varchar&#x60;, &#x60;text&#x60;, &#x60;varchar_auto&#x60;, &#x60;double&#x60;, &#x60;monetary&#x60; and &#x60;phone&#x60;. Read more about searching by custom fields &lt;a href&#x3D;\&quot;https://support.pipedrive.com/en/article/search-finding-what-you-need#searching-by-custom-fields\&quot; target&#x3D;\&quot;_blank\&quot; rel&#x3D;\&quot;noopener noreferrer\&quot;&gt;here&lt;/a&gt;. | [optional]
 **exact_match** | **bool**| When enabled, only full exact matches against the given term are returned. It is &lt;b&gt;not&lt;/b&gt; case sensitive. | [optional]
 **person_id** | **int**| Will filter projects by the provided person ID | [optional]
 **organization_id** | **int**| Will filter projects by the provided organization ID | [optional]
 **limit** | **int**| For pagination, the limit of entries to be returned. If not provided, 100 items will be returned. Please note that a maximum value of 500 is allowed. | [optional]
 **cursor** | **string**| For pagination, the marker (an opaque string value) representing the first item on the next page | [optional]

### Return type

[**\Pipedrive\versions\v2\Model\ProjectSearchResponse**](../Model/ProjectSearchResponse.md)

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


$apiInstance = new Pipedrive\versions\v2\Api\BetaApi(
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
    echo 'Exception when calling BetaApi->updateProjectBoard: ', $e->getMessage(), PHP_EOL;
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

## `updateProjectField()`

```php
updateProjectField($field_code, $update_project_field_request): \Pipedrive\versions\v2\Model\GetProjectField
```

Update one project field

Updates a project custom field. The field_code and field_type cannot be changed. At least one field must be provided in the request body.

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


$apiInstance = new Pipedrive\versions\v2\Api\BetaApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$field_code = 'field_code_example'; // string | The unique code identifying the field
$update_project_field_request = new \Pipedrive\versions\v2\Model\UpdateProjectFieldRequest(); // \Pipedrive\versions\v2\Model\UpdateProjectFieldRequest

try {
    $result = $apiInstance->updateProjectField($field_code, $update_project_field_request);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling BetaApi->updateProjectField: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **field_code** | **string**| The unique code identifying the field |
 **update_project_field_request** | [**\Pipedrive\versions\v2\Model\UpdateProjectFieldRequest**](../Model/UpdateProjectFieldRequest.md)|  |

### Return type

[**\Pipedrive\versions\v2\Model\GetProjectField**](../Model/GetProjectField.md)

### Authorization

[api_key](../README.md#api_key), [oauth2](../README.md#oauth2)

### HTTP request headers

- **Content-Type**: `application/json`
- **Accept**: `application/json`

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints)
[[Back to Model list]](../README.md#documentation-for-models)
[[Back to README]](../README.md)

## `updateProjectFieldOptions()`

```php
updateProjectFieldOptions($field_code, $request_body): \Pipedrive\versions\v2\Model\InlineResponse200
```

Update project field options in bulk

Updates existing options for a project custom field. This operation is atomic and fails if any of the specified option IDs do not exist. Returns only the updated options.

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


$apiInstance = new Pipedrive\versions\v2\Api\BetaApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$field_code = 'field_code_example'; // string | The unique code identifying the field
$request_body = array(new \stdClass); // object[]

try {
    $result = $apiInstance->updateProjectFieldOptions($field_code, $request_body);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling BetaApi->updateProjectFieldOptions: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **field_code** | **string**| The unique code identifying the field |
 **request_body** | [**object[]**](../Model/object.md)|  |

### Return type

[**\Pipedrive\versions\v2\Model\InlineResponse200**](../Model/InlineResponse200.md)

### Authorization

[api_key](../README.md#api_key), [oauth2](../README.md#oauth2)

### HTTP request headers

- **Content-Type**: `application/json`
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


$apiInstance = new Pipedrive\versions\v2\Api\BetaApi(
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
    echo 'Exception when calling BetaApi->updateProjectPhase: ', $e->getMessage(), PHP_EOL;
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


$apiInstance = new Pipedrive\versions\v2\Api\BetaApi(
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
    echo 'Exception when calling BetaApi->updateTask: ', $e->getMessage(), PHP_EOL;
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
