# Pipedrive\versions\v1\GoalsApi

All URIs are relative to https://api.pipedrive.com/v1.

Method | HTTP request | Description
------------- | ------------- | -------------
[**addGoal()**](GoalsApi.md#addGoal) | **POST** /goals | Add a new goal
[**deleteGoal()**](GoalsApi.md#deleteGoal) | **DELETE** /goals/{id} | Delete existing goal
[**getGoalResult()**](GoalsApi.md#getGoalResult) | **GET** /goals/{id}/results | Get result of a goal
[**getGoals()**](GoalsApi.md#getGoals) | **GET** /goals/find | Find goals
[**updateGoal()**](GoalsApi.md#updateGoal) | **PUT** /goals/{id} | Update existing goal


## `addGoal()`

```php
addGoal($new_goal): \Pipedrive\versions\v1\Model\UpsertGoalResponse
```

Add a new goal

Adds a new goal. Along with adding a new goal, a report is created to track the progress of your goal.

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


$apiInstance = new Pipedrive\versions\v1\Api\GoalsApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$new_goal = new \Pipedrive\versions\v1\Model\NewGoal(); // \Pipedrive\versions\v1\Model\NewGoal

try {
    $result = $apiInstance->addGoal($new_goal);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling GoalsApi->addGoal: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **new_goal** | [**\Pipedrive\versions\v1\Model\NewGoal**](../Model/NewGoal.md)|  | [optional]

### Return type

[**\Pipedrive\versions\v1\Model\UpsertGoalResponse**](../Model/UpsertGoalResponse.md)

### Authorization

[api_key](../README.md#api_key), [oauth2](../README.md#oauth2)

### HTTP request headers

- **Content-Type**: `application/json`
- **Accept**: `application/json`

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints)
[[Back to Model list]](../README.md#documentation-for-models)
[[Back to README]](../README.md)

## `deleteGoal()`

```php
deleteGoal($id): \Pipedrive\versions\v1\Model\DeleteGoalResponse
```

Delete existing goal

Marks a goal as deleted.

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


$apiInstance = new Pipedrive\versions\v1\Api\GoalsApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$id = 'id_example'; // string | The ID of the goal

try {
    $result = $apiInstance->deleteGoal($id);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling GoalsApi->deleteGoal: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **id** | **string**| The ID of the goal |

### Return type

[**\Pipedrive\versions\v1\Model\DeleteGoalResponse**](../Model/DeleteGoalResponse.md)

### Authorization

[api_key](../README.md#api_key), [oauth2](../README.md#oauth2)

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json`

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints)
[[Back to Model list]](../README.md#documentation-for-models)
[[Back to README]](../README.md)

## `getGoalResult()`

```php
getGoalResult($id, $period_start, $period_end): \Pipedrive\versions\v1\Model\GetGoalResultResponse
```

Get result of a goal

Gets the progress of a goal for the specified period.

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


$apiInstance = new Pipedrive\versions\v1\Api\GoalsApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$id = 'id_example'; // string | The ID of the goal that the results are looked for
$period_start = new \DateTime("2013-10-20T19:20:30+01:00"); // \DateTime | The start date of the period for which to find the goal's progress. Format: YYYY-MM-DD. This date must be the same or after the goal duration start date.
$period_end = new \DateTime("2013-10-20T19:20:30+01:00"); // \DateTime | The end date of the period for which to find the goal's progress. Format: YYYY-MM-DD. This date must be the same or before the goal duration end date.

try {
    $result = $apiInstance->getGoalResult($id, $period_start, $period_end);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling GoalsApi->getGoalResult: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **id** | **string**| The ID of the goal that the results are looked for |
 **period_start** | **\DateTime**| The start date of the period for which to find the goal&#39;s progress. Format: YYYY-MM-DD. This date must be the same or after the goal duration start date. |
 **period_end** | **\DateTime**| The end date of the period for which to find the goal&#39;s progress. Format: YYYY-MM-DD. This date must be the same or before the goal duration end date. |

### Return type

[**\Pipedrive\versions\v1\Model\GetGoalResultResponse**](../Model/GetGoalResultResponse.md)

### Authorization

[api_key](../README.md#api_key), [oauth2](../README.md#oauth2)

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json`

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints)
[[Back to Model list]](../README.md#documentation-for-models)
[[Back to README]](../README.md)

## `getGoals()`

```php
getGoals($type_name, $title, $is_active, $assignee_id, $assignee_type, $expected_outcome_target, $expected_outcome_tracking_metric, $expected_outcome_currency_id, $type_params_pipeline_id, $type_params_stage_id, $type_params_activity_type_id, $period_start, $period_end): \Pipedrive\versions\v1\Model\GetGoalsResponse
```

Find goals

Returns data about goals based on criteria. For searching, append `{searchField}={searchValue}` to the URL, where `searchField` can be any one of the lowest-level fields in dot-notation (e.g. `type.params.pipeline_id`; `title`). `searchValue` should be the value you are looking for on that field. Additionally, `is_active=<true|false>` can be provided to search for only active/inactive goals. When providing `period.start`, `period.end` must also be provided and vice versa.

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


$apiInstance = new Pipedrive\versions\v1\Api\GoalsApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$type_name = 'type_name_example'; // string | The type of the goal. If provided, everyone's goals will be returned.
$title = 'title_example'; // string | The title of the goal
$is_active = true; // bool | Whether the goal is active or not
$assignee_id = 56; // int | The ID of the user who's goal to fetch. When omitted, only your goals will be returned.
$assignee_type = 'assignee_type_example'; // string | The type of the goal's assignee. If provided, everyone's goals will be returned.
$expected_outcome_target = 3.4; // float | The numeric value of the outcome. If provided, everyone's goals will be returned.
$expected_outcome_tracking_metric = 'expected_outcome_tracking_metric_example'; // string | The tracking metric of the expected outcome of the goal. If provided, everyone's goals will be returned.
$expected_outcome_currency_id = 56; // int | The numeric ID of the goal's currency. Only applicable to goals with `expected_outcome.tracking_metric` with value `sum`. If provided, everyone's goals will be returned.
$type_params_pipeline_id = array(56); // int[] | An array of pipeline IDs or `null` for all pipelines. If provided, everyone's goals will be returned.
$type_params_stage_id = 56; // int | The ID of the stage. Applicable to only `deals_progressed` type of goals. If provided, everyone's goals will be returned.
$type_params_activity_type_id = array(56); // int[] | An array of IDs or `null` for all activity types. Only applicable for `activities_completed` and/or `activities_added` types of goals. If provided, everyone's goals will be returned.
$period_start = new \DateTime("2013-10-20T19:20:30+01:00"); // \DateTime | The start date of the period for which to find goals. Date in format of YYYY-MM-DD. When `period.start` is provided, `period.end` must be provided too.
$period_end = new \DateTime("2013-10-20T19:20:30+01:00"); // \DateTime | The end date of the period for which to find goals. Date in format of YYYY-MM-DD.

try {
    $result = $apiInstance->getGoals($type_name, $title, $is_active, $assignee_id, $assignee_type, $expected_outcome_target, $expected_outcome_tracking_metric, $expected_outcome_currency_id, $type_params_pipeline_id, $type_params_stage_id, $type_params_activity_type_id, $period_start, $period_end);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling GoalsApi->getGoals: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **type_name** | **string**| The type of the goal. If provided, everyone&#39;s goals will be returned. | [optional]
 **title** | **string**| The title of the goal | [optional]
 **is_active** | **bool**| Whether the goal is active or not | [optional] [default to true]
 **assignee_id** | **int**| The ID of the user who&#39;s goal to fetch. When omitted, only your goals will be returned. | [optional]
 **assignee_type** | **string**| The type of the goal&#39;s assignee. If provided, everyone&#39;s goals will be returned. | [optional]
 **expected_outcome_target** | **float**| The numeric value of the outcome. If provided, everyone&#39;s goals will be returned. | [optional]
 **expected_outcome_tracking_metric** | **string**| The tracking metric of the expected outcome of the goal. If provided, everyone&#39;s goals will be returned. | [optional]
 **expected_outcome_currency_id** | **int**| The numeric ID of the goal&#39;s currency. Only applicable to goals with &#x60;expected_outcome.tracking_metric&#x60; with value &#x60;sum&#x60;. If provided, everyone&#39;s goals will be returned. | [optional]
 **type_params_pipeline_id** | [**int[]**](../Model/int.md)| An array of pipeline IDs or &#x60;null&#x60; for all pipelines. If provided, everyone&#39;s goals will be returned. | [optional]
 **type_params_stage_id** | **int**| The ID of the stage. Applicable to only &#x60;deals_progressed&#x60; type of goals. If provided, everyone&#39;s goals will be returned. | [optional]
 **type_params_activity_type_id** | [**int[]**](../Model/int.md)| An array of IDs or &#x60;null&#x60; for all activity types. Only applicable for &#x60;activities_completed&#x60; and/or &#x60;activities_added&#x60; types of goals. If provided, everyone&#39;s goals will be returned. | [optional]
 **period_start** | **\DateTime**| The start date of the period for which to find goals. Date in format of YYYY-MM-DD. When &#x60;period.start&#x60; is provided, &#x60;period.end&#x60; must be provided too. | [optional]
 **period_end** | **\DateTime**| The end date of the period for which to find goals. Date in format of YYYY-MM-DD. | [optional]

### Return type

[**\Pipedrive\versions\v1\Model\GetGoalsResponse**](../Model/GetGoalsResponse.md)

### Authorization

[api_key](../README.md#api_key), [oauth2](../README.md#oauth2)

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json`

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints)
[[Back to Model list]](../README.md#documentation-for-models)
[[Back to README]](../README.md)

## `updateGoal()`

```php
updateGoal($id, $basic_goal): \Pipedrive\versions\v1\Model\UpsertGoalResponse
```

Update existing goal

Updates an existing goal.

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


$apiInstance = new Pipedrive\versions\v1\Api\GoalsApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$id = 'id_example'; // string | The ID of the goal
$basic_goal = new \Pipedrive\versions\v1\Model\BasicGoal(); // \Pipedrive\versions\v1\Model\BasicGoal

try {
    $result = $apiInstance->updateGoal($id, $basic_goal);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling GoalsApi->updateGoal: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **id** | **string**| The ID of the goal |
 **basic_goal** | [**\Pipedrive\versions\v1\Model\BasicGoal**](../Model/BasicGoal.md)|  | [optional]

### Return type

[**\Pipedrive\versions\v1\Model\UpsertGoalResponse**](../Model/UpsertGoalResponse.md)

### Authorization

[api_key](../README.md#api_key), [oauth2](../README.md#oauth2)

### HTTP request headers

- **Content-Type**: `application/json`
- **Accept**: `application/json`

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints)
[[Back to Model list]](../README.md#documentation-for-models)
[[Back to README]](../README.md)
