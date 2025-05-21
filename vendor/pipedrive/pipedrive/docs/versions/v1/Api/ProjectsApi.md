# Pipedrive\versions\v1\ProjectsApi

All URIs are relative to https://api.pipedrive.com/v1.

Method | HTTP request | Description
------------- | ------------- | -------------
[**addProject()**](ProjectsApi.md#addProject) | **POST** /projects | Add a project
[**archiveProject()**](ProjectsApi.md#archiveProject) | **POST** /projects/{id}/archive | Archive a project
[**deleteProject()**](ProjectsApi.md#deleteProject) | **DELETE** /projects/{id} | Delete a project
[**getProject()**](ProjectsApi.md#getProject) | **GET** /projects/{id} | Get details of a project
[**getProjectActivities()**](ProjectsApi.md#getProjectActivities) | **GET** /projects/{id}/activities | Returns project activities
[**getProjectGroups()**](ProjectsApi.md#getProjectGroups) | **GET** /projects/{id}/groups | Returns project groups
[**getProjectPlan()**](ProjectsApi.md#getProjectPlan) | **GET** /projects/{id}/plan | Returns project plan
[**getProjectTasks()**](ProjectsApi.md#getProjectTasks) | **GET** /projects/{id}/tasks | Returns project tasks
[**getProjects()**](ProjectsApi.md#getProjects) | **GET** /projects | Get all projects
[**getProjectsBoards()**](ProjectsApi.md#getProjectsBoards) | **GET** /projects/boards | Get all project boards
[**getProjectsPhases()**](ProjectsApi.md#getProjectsPhases) | **GET** /projects/phases | Get project phases
[**putProjectPlanActivity()**](ProjectsApi.md#putProjectPlanActivity) | **PUT** /projects/{id}/plan/activities/{activityId} | Update activity in project plan
[**putProjectPlanTask()**](ProjectsApi.md#putProjectPlanTask) | **PUT** /projects/{id}/plan/tasks/{taskId} | Update task in project plan
[**updateProject()**](ProjectsApi.md#updateProject) | **PUT** /projects/{id} | Update a project


## `addProject()`

```php
addProject($project_post_object): \Pipedrive\versions\v1\Model\AddProjectResponse
```

Add a project

Adds a new project. Note that you can supply additional custom fields along with the request that are not described here. These custom fields are different for each Pipedrive account and can be recognized by long hashes as keys.

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


$apiInstance = new Pipedrive\versions\v1\Api\ProjectsApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$project_post_object = new \Pipedrive\versions\v1\Model\ProjectPostObject(); // \Pipedrive\versions\v1\Model\ProjectPostObject

try {
    $result = $apiInstance->addProject($project_post_object);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling ProjectsApi->addProject: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **project_post_object** | [**\Pipedrive\versions\v1\Model\ProjectPostObject**](../Model/ProjectPostObject.md)|  | [optional]

### Return type

[**\Pipedrive\versions\v1\Model\AddProjectResponse**](../Model/AddProjectResponse.md)

### Authorization

[api_key](../README.md#api_key), [oauth2](../README.md#oauth2)

### HTTP request headers

- **Content-Type**: `application/json`
- **Accept**: `application/json`

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints)
[[Back to Model list]](../README.md#documentation-for-models)
[[Back to README]](../README.md)

## `archiveProject()`

```php
archiveProject($id): \Pipedrive\versions\v1\Model\UpdateProjectResponse
```

Archive a project

Archives a project.

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


$apiInstance = new Pipedrive\versions\v1\Api\ProjectsApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$id = 56; // int | The ID of the project

try {
    $result = $apiInstance->archiveProject($id);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling ProjectsApi->archiveProject: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **id** | **int**| The ID of the project |

### Return type

[**\Pipedrive\versions\v1\Model\UpdateProjectResponse**](../Model/UpdateProjectResponse.md)

### Authorization

[api_key](../README.md#api_key), [oauth2](../README.md#oauth2)

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json`

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints)
[[Back to Model list]](../README.md#documentation-for-models)
[[Back to README]](../README.md)

## `deleteProject()`

```php
deleteProject($id): \Pipedrive\versions\v1\Model\DeleteProjectResponse
```

Delete a project

Marks a project as deleted.

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


$apiInstance = new Pipedrive\versions\v1\Api\ProjectsApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$id = 56; // int | The ID of the project

try {
    $result = $apiInstance->deleteProject($id);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling ProjectsApi->deleteProject: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **id** | **int**| The ID of the project |

### Return type

[**\Pipedrive\versions\v1\Model\DeleteProjectResponse**](../Model/DeleteProjectResponse.md)

### Authorization

[api_key](../README.md#api_key), [oauth2](../README.md#oauth2)

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json`

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints)
[[Back to Model list]](../README.md#documentation-for-models)
[[Back to README]](../README.md)

## `getProject()`

```php
getProject($id): \Pipedrive\versions\v1\Model\GetProjectResponse
```

Get details of a project

Returns the details of a specific project. Also note that custom fields appear as long hashes in the resulting data. These hashes can be mapped against the `key` value of project fields.

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


$apiInstance = new Pipedrive\versions\v1\Api\ProjectsApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$id = 56; // int | The ID of the project

try {
    $result = $apiInstance->getProject($id);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling ProjectsApi->getProject: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **id** | **int**| The ID of the project |

### Return type

[**\Pipedrive\versions\v1\Model\GetProjectResponse**](../Model/GetProjectResponse.md)

### Authorization

[api_key](../README.md#api_key), [oauth2](../README.md#oauth2)

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json`

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints)
[[Back to Model list]](../README.md#documentation-for-models)
[[Back to README]](../README.md)

## `getProjectActivities()`

```php
getProjectActivities($id): \Pipedrive\versions\v1\Model\GetActivitiesCollectionResponse
```

Returns project activities

Returns activities linked to a specific project.

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


$apiInstance = new Pipedrive\versions\v1\Api\ProjectsApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$id = 56; // int | The ID of the project

try {
    $result = $apiInstance->getProjectActivities($id);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling ProjectsApi->getProjectActivities: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **id** | **int**| The ID of the project |

### Return type

[**\Pipedrive\versions\v1\Model\GetActivitiesCollectionResponse**](../Model/GetActivitiesCollectionResponse.md)

### Authorization

[api_key](../README.md#api_key), [oauth2](../README.md#oauth2)

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json`

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints)
[[Back to Model list]](../README.md#documentation-for-models)
[[Back to README]](../README.md)

## `getProjectGroups()`

```php
getProjectGroups($id): \Pipedrive\versions\v1\Model\GetProjectGroupsResponse
```

Returns project groups

Returns all active groups under a specific project.

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


$apiInstance = new Pipedrive\versions\v1\Api\ProjectsApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$id = 56; // int | The ID of the project

try {
    $result = $apiInstance->getProjectGroups($id);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling ProjectsApi->getProjectGroups: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **id** | **int**| The ID of the project |

### Return type

[**\Pipedrive\versions\v1\Model\GetProjectGroupsResponse**](../Model/GetProjectGroupsResponse.md)

### Authorization

[api_key](../README.md#api_key), [oauth2](../README.md#oauth2)

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json`

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints)
[[Back to Model list]](../README.md#documentation-for-models)
[[Back to README]](../README.md)

## `getProjectPlan()`

```php
getProjectPlan($id): \Pipedrive\versions\v1\Model\GetProjectPlanResponse
```

Returns project plan

Returns information about items in a project plan. Items consists of tasks and activities and are linked to specific project phase and group.

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


$apiInstance = new Pipedrive\versions\v1\Api\ProjectsApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$id = 56; // int | The ID of the project

try {
    $result = $apiInstance->getProjectPlan($id);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling ProjectsApi->getProjectPlan: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **id** | **int**| The ID of the project |

### Return type

[**\Pipedrive\versions\v1\Model\GetProjectPlanResponse**](../Model/GetProjectPlanResponse.md)

### Authorization

[api_key](../README.md#api_key), [oauth2](../README.md#oauth2)

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json`

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints)
[[Back to Model list]](../README.md#documentation-for-models)
[[Back to README]](../README.md)

## `getProjectTasks()`

```php
getProjectTasks($id): \Pipedrive\versions\v1\Model\GetTasksResponse
```

Returns project tasks

Returns tasks linked to a specific project.

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


$apiInstance = new Pipedrive\versions\v1\Api\ProjectsApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$id = 56; // int | The ID of the project

try {
    $result = $apiInstance->getProjectTasks($id);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling ProjectsApi->getProjectTasks: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **id** | **int**| The ID of the project |

### Return type

[**\Pipedrive\versions\v1\Model\GetTasksResponse**](../Model/GetTasksResponse.md)

### Authorization

[api_key](../README.md#api_key), [oauth2](../README.md#oauth2)

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json`

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints)
[[Back to Model list]](../README.md#documentation-for-models)
[[Back to README]](../README.md)

## `getProjects()`

```php
getProjects($cursor, $limit, $filter_id, $status, $phase_id, $include_archived): \Pipedrive\versions\v1\Model\GetProjectsResponse
```

Get all projects

Returns all projects. This is a cursor-paginated endpoint. For more information, please refer to our documentation on <a href=\"https://pipedrive.readme.io/docs/core-api-concepts-pagination\" target=\"_blank\" rel=\"noopener noreferrer\">pagination</a>.

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


$apiInstance = new Pipedrive\versions\v1\Api\ProjectsApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$cursor = 'cursor_example'; // string | For pagination, the marker (an opaque string value) representing the first item on the next page
$limit = 100; // int | For pagination, the limit of entries to be returned. If not provided, 100 items will be returned.
$filter_id = 56; // int | The ID of the filter to use
$status = open,completed; // string | If supplied, includes only projects with the specified statuses. Possible values are `open`, `completed`, `canceled` and `deleted`. By default `deleted` projects are not returned.
$phase_id = 56; // int | If supplied, only projects in specified phase are returned
$include_archived = True; // bool | If supplied with `true` then archived projects are also included in the response. By default only not archived projects are returned.

try {
    $result = $apiInstance->getProjects($cursor, $limit, $filter_id, $status, $phase_id, $include_archived);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling ProjectsApi->getProjects: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **cursor** | **string**| For pagination, the marker (an opaque string value) representing the first item on the next page | [optional]
 **limit** | **int**| For pagination, the limit of entries to be returned. If not provided, 100 items will be returned. | [optional]
 **filter_id** | **int**| The ID of the filter to use | [optional]
 **status** | **string**| If supplied, includes only projects with the specified statuses. Possible values are &#x60;open&#x60;, &#x60;completed&#x60;, &#x60;canceled&#x60; and &#x60;deleted&#x60;. By default &#x60;deleted&#x60; projects are not returned. | [optional]
 **phase_id** | **int**| If supplied, only projects in specified phase are returned | [optional]
 **include_archived** | **bool**| If supplied with &#x60;true&#x60; then archived projects are also included in the response. By default only not archived projects are returned. | [optional]

### Return type

[**\Pipedrive\versions\v1\Model\GetProjectsResponse**](../Model/GetProjectsResponse.md)

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
getProjectsBoards(): \Pipedrive\versions\v1\Model\GetProjectBoardsResponse
```

Get all project boards

Returns all projects boards that are not deleted.

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


$apiInstance = new Pipedrive\versions\v1\Api\ProjectsApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);

try {
    $result = $apiInstance->getProjectsBoards();
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling ProjectsApi->getProjectsBoards: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

This endpoint does not need any parameter.

### Return type

[**\Pipedrive\versions\v1\Model\GetProjectBoardsResponse**](../Model/GetProjectBoardsResponse.md)

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


$apiInstance = new Pipedrive\versions\v1\Api\ProjectsApi(
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
    echo 'Exception when calling ProjectsApi->getProjectsPhases: ', $e->getMessage(), PHP_EOL;
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

## `putProjectPlanActivity()`

```php
putProjectPlanActivity($id, $activity_id, $project_put_plan_item_body_object): \Pipedrive\versions\v1\Model\UpdateActivityPlanItemResponse
```

Update activity in project plan

Updates an activity phase or group in a project.

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


$apiInstance = new Pipedrive\versions\v1\Api\ProjectsApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$id = 56; // int | The ID of the project
$activity_id = 56; // int | The ID of the activity
$project_put_plan_item_body_object = new \Pipedrive\versions\v1\Model\ProjectPutPlanItemBodyObject(); // \Pipedrive\versions\v1\Model\ProjectPutPlanItemBodyObject

try {
    $result = $apiInstance->putProjectPlanActivity($id, $activity_id, $project_put_plan_item_body_object);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling ProjectsApi->putProjectPlanActivity: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **id** | **int**| The ID of the project |
 **activity_id** | **int**| The ID of the activity |
 **project_put_plan_item_body_object** | [**\Pipedrive\versions\v1\Model\ProjectPutPlanItemBodyObject**](../Model/ProjectPutPlanItemBodyObject.md)|  | [optional]

### Return type

[**\Pipedrive\versions\v1\Model\UpdateActivityPlanItemResponse**](../Model/UpdateActivityPlanItemResponse.md)

### Authorization

[api_key](../README.md#api_key), [oauth2](../README.md#oauth2)

### HTTP request headers

- **Content-Type**: `application/json`
- **Accept**: `application/json`

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints)
[[Back to Model list]](../README.md#documentation-for-models)
[[Back to README]](../README.md)

## `putProjectPlanTask()`

```php
putProjectPlanTask($id, $task_id, $project_put_plan_item_body_object): \Pipedrive\versions\v1\Model\UpdateTaskPlanItemResponse
```

Update task in project plan

Updates a task phase or group in a project.

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


$apiInstance = new Pipedrive\versions\v1\Api\ProjectsApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$id = 56; // int | The ID of the project
$task_id = 56; // int | The ID of the task
$project_put_plan_item_body_object = new \Pipedrive\versions\v1\Model\ProjectPutPlanItemBodyObject(); // \Pipedrive\versions\v1\Model\ProjectPutPlanItemBodyObject

try {
    $result = $apiInstance->putProjectPlanTask($id, $task_id, $project_put_plan_item_body_object);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling ProjectsApi->putProjectPlanTask: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **id** | **int**| The ID of the project |
 **task_id** | **int**| The ID of the task |
 **project_put_plan_item_body_object** | [**\Pipedrive\versions\v1\Model\ProjectPutPlanItemBodyObject**](../Model/ProjectPutPlanItemBodyObject.md)|  | [optional]

### Return type

[**\Pipedrive\versions\v1\Model\UpdateTaskPlanItemResponse**](../Model/UpdateTaskPlanItemResponse.md)

### Authorization

[api_key](../README.md#api_key), [oauth2](../README.md#oauth2)

### HTTP request headers

- **Content-Type**: `application/json`
- **Accept**: `application/json`

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints)
[[Back to Model list]](../README.md#documentation-for-models)
[[Back to README]](../README.md)

## `updateProject()`

```php
updateProject($id, $project_put_object): \Pipedrive\versions\v1\Model\UpdateProjectResponse
```

Update a project

Updates a project.

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


$apiInstance = new Pipedrive\versions\v1\Api\ProjectsApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$id = 56; // int | The ID of the project
$project_put_object = new \Pipedrive\versions\v1\Model\ProjectPutObject(); // \Pipedrive\versions\v1\Model\ProjectPutObject

try {
    $result = $apiInstance->updateProject($id, $project_put_object);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling ProjectsApi->updateProject: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **id** | **int**| The ID of the project |
 **project_put_object** | [**\Pipedrive\versions\v1\Model\ProjectPutObject**](../Model/ProjectPutObject.md)|  | [optional]

### Return type

[**\Pipedrive\versions\v1\Model\UpdateProjectResponse**](../Model/UpdateProjectResponse.md)

### Authorization

[api_key](../README.md#api_key), [oauth2](../README.md#oauth2)

### HTTP request headers

- **Content-Type**: `application/json`
- **Accept**: `application/json`

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints)
[[Back to Model list]](../README.md#documentation-for-models)
[[Back to README]](../README.md)
