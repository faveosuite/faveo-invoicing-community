# Pipedrive\versions\v2\ActivitiesApi

All URIs are relative to https://api.pipedrive.com/api/v2.

Method | HTTP request | Description
------------- | ------------- | -------------
[**addActivity()**](ActivitiesApi.md#addActivity) | **POST** /activities | Add a new activity
[**deleteActivity()**](ActivitiesApi.md#deleteActivity) | **DELETE** /activities/{id} | Delete an activity
[**getActivities()**](ActivitiesApi.md#getActivities) | **GET** /activities | Get all activities
[**getActivity()**](ActivitiesApi.md#getActivity) | **GET** /activities/{id} | Get details of an activity
[**updateActivity()**](ActivitiesApi.md#updateActivity) | **PATCH** /activities/{id} | Update an activity


## `addActivity()`

```php
addActivity($activity_request_body): \Pipedrive\versions\v2\Model\PostPatchGetActivity
```

Add a new activity

Adds a new activity.

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


$apiInstance = new Pipedrive\versions\v2\Api\ActivitiesApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$activity_request_body = new \Pipedrive\versions\v2\Model\ActivityRequestBody(); // \Pipedrive\versions\v2\Model\ActivityRequestBody

try {
    $result = $apiInstance->addActivity($activity_request_body);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling ActivitiesApi->addActivity: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **activity_request_body** | [**\Pipedrive\versions\v2\Model\ActivityRequestBody**](../Model/ActivityRequestBody.md)|  | [optional]

### Return type

[**\Pipedrive\versions\v2\Model\PostPatchGetActivity**](../Model/PostPatchGetActivity.md)

### Authorization

[api_key](../README.md#api_key), [oauth2](../README.md#oauth2)

### HTTP request headers

- **Content-Type**: `application/json`
- **Accept**: `application/json`

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints)
[[Back to Model list]](../README.md#documentation-for-models)
[[Back to README]](../README.md)

## `deleteActivity()`

```php
deleteActivity($id): \Pipedrive\versions\v2\Model\DeleteActivityResponse
```

Delete an activity

Marks an activity as deleted. After 30 days, the activity will be permanently deleted.

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


$apiInstance = new Pipedrive\versions\v2\Api\ActivitiesApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$id = 56; // int | The ID of the activity

try {
    $result = $apiInstance->deleteActivity($id);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling ActivitiesApi->deleteActivity: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **id** | **int**| The ID of the activity |

### Return type

[**\Pipedrive\versions\v2\Model\DeleteActivityResponse**](../Model/DeleteActivityResponse.md)

### Authorization

[api_key](../README.md#api_key), [oauth2](../README.md#oauth2)

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json`

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints)
[[Back to Model list]](../README.md#documentation-for-models)
[[Back to README]](../README.md)

## `getActivities()`

```php
getActivities($filter_id, $ids, $owner_id, $deal_id, $lead_id, $person_id, $org_id, $done, $updated_since, $updated_until, $sort_by, $sort_direction, $include_fields, $limit, $cursor): \Pipedrive\versions\v2\Model\GetActivities
```

Get all activities

Returns data about all activities.

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


$apiInstance = new Pipedrive\versions\v2\Api\ActivitiesApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$filter_id = 56; // int | If supplied, only activities matching the specified filter are returned
$ids = 'ids_example'; // string | Optional comma separated string array of up to 100 entity ids to fetch. If filter_id is provided, this is ignored. If any of the requested entities do not exist or are not visible, they are not included in the response.
$owner_id = 56; // int | If supplied, only activities owned by the specified user are returned. If filter_id is provided, this is ignored.
$deal_id = 56; // int | If supplied, only activities linked to the specified deal are returned. If filter_id is provided, this is ignored.
$lead_id = 'lead_id_example'; // string | If supplied, only activities linked to the specified lead are returned. If filter_id is provided, this is ignored.
$person_id = 56; // int | If supplied, only activities whose primary participant is the given person are returned. If filter_id is provided, this is ignored.
$org_id = 56; // int | If supplied, only activities linked to the specified organization are returned. If filter_id is provided, this is ignored.
$done = True; // bool | If supplied, only activities with specified 'done' flag value are returned
$updated_since = 'updated_since_example'; // string | If set, only activities with an `update_time` later than or equal to this time are returned. In RFC3339 format, e.g. 2025-01-01T10:20:00Z.
$updated_until = 'updated_until_example'; // string | If set, only activities with an `update_time` earlier than this time are returned. In RFC3339 format, e.g. 2025-01-01T10:20:00Z.
$sort_by = 'id'; // string | The field to sort by. Supported fields: `id`, `update_time`, `add_time`, `due_date`.
$sort_direction = 'asc'; // string | The sorting direction. Supported values: `asc`, `desc`.
$include_fields = 'include_fields_example'; // string | Optional comma separated string array of additional fields to include
$limit = 100; // int | For pagination, the limit of entries to be returned. If not provided, 100 items will be returned. Please note that a maximum value of 500 is allowed.
$cursor = 'cursor_example'; // string | For pagination, the marker (an opaque string value) representing the first item on the next page

try {
    $result = $apiInstance->getActivities($filter_id, $ids, $owner_id, $deal_id, $lead_id, $person_id, $org_id, $done, $updated_since, $updated_until, $sort_by, $sort_direction, $include_fields, $limit, $cursor);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling ActivitiesApi->getActivities: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **filter_id** | **int**| If supplied, only activities matching the specified filter are returned | [optional]
 **ids** | **string**| Optional comma separated string array of up to 100 entity ids to fetch. If filter_id is provided, this is ignored. If any of the requested entities do not exist or are not visible, they are not included in the response. | [optional]
 **owner_id** | **int**| If supplied, only activities owned by the specified user are returned. If filter_id is provided, this is ignored. | [optional]
 **deal_id** | **int**| If supplied, only activities linked to the specified deal are returned. If filter_id is provided, this is ignored. | [optional]
 **lead_id** | **string**| If supplied, only activities linked to the specified lead are returned. If filter_id is provided, this is ignored. | [optional]
 **person_id** | **int**| If supplied, only activities whose primary participant is the given person are returned. If filter_id is provided, this is ignored. | [optional]
 **org_id** | **int**| If supplied, only activities linked to the specified organization are returned. If filter_id is provided, this is ignored. | [optional]
 **done** | **bool**| If supplied, only activities with specified &#39;done&#39; flag value are returned | [optional]
 **updated_since** | **string**| If set, only activities with an &#x60;update_time&#x60; later than or equal to this time are returned. In RFC3339 format, e.g. 2025-01-01T10:20:00Z. | [optional]
 **updated_until** | **string**| If set, only activities with an &#x60;update_time&#x60; earlier than this time are returned. In RFC3339 format, e.g. 2025-01-01T10:20:00Z. | [optional]
 **sort_by** | **string**| The field to sort by. Supported fields: &#x60;id&#x60;, &#x60;update_time&#x60;, &#x60;add_time&#x60;, &#x60;due_date&#x60;. | [optional] [default to &#39;id&#39;]
 **sort_direction** | **string**| The sorting direction. Supported values: &#x60;asc&#x60;, &#x60;desc&#x60;. | [optional] [default to &#39;asc&#39;]
 **include_fields** | **string**| Optional comma separated string array of additional fields to include | [optional]
 **limit** | **int**| For pagination, the limit of entries to be returned. If not provided, 100 items will be returned. Please note that a maximum value of 500 is allowed. | [optional]
 **cursor** | **string**| For pagination, the marker (an opaque string value) representing the first item on the next page | [optional]

### Return type

[**\Pipedrive\versions\v2\Model\GetActivities**](../Model/GetActivities.md)

### Authorization

[api_key](../README.md#api_key), [oauth2](../README.md#oauth2)

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json`

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints)
[[Back to Model list]](../README.md#documentation-for-models)
[[Back to README]](../README.md)

## `getActivity()`

```php
getActivity($id, $include_fields): \Pipedrive\versions\v2\Model\PostPatchGetActivity
```

Get details of an activity

Returns the details of a specific activity.

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


$apiInstance = new Pipedrive\versions\v2\Api\ActivitiesApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$id = 56; // int | The ID of the activity
$include_fields = 'include_fields_example'; // string | Optional comma separated string array of additional fields to include

try {
    $result = $apiInstance->getActivity($id, $include_fields);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling ActivitiesApi->getActivity: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **id** | **int**| The ID of the activity |
 **include_fields** | **string**| Optional comma separated string array of additional fields to include | [optional]

### Return type

[**\Pipedrive\versions\v2\Model\PostPatchGetActivity**](../Model/PostPatchGetActivity.md)

### Authorization

[api_key](../README.md#api_key), [oauth2](../README.md#oauth2)

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json`

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints)
[[Back to Model list]](../README.md#documentation-for-models)
[[Back to README]](../README.md)

## `updateActivity()`

```php
updateActivity($id, $activity_request_body): \Pipedrive\versions\v2\Model\PostPatchGetActivity
```

Update an activity

Updates the properties of an activity.

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


$apiInstance = new Pipedrive\versions\v2\Api\ActivitiesApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$id = 56; // int | The ID of the activity
$activity_request_body = new \Pipedrive\versions\v2\Model\ActivityRequestBody(); // \Pipedrive\versions\v2\Model\ActivityRequestBody

try {
    $result = $apiInstance->updateActivity($id, $activity_request_body);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling ActivitiesApi->updateActivity: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **id** | **int**| The ID of the activity |
 **activity_request_body** | [**\Pipedrive\versions\v2\Model\ActivityRequestBody**](../Model/ActivityRequestBody.md)|  | [optional]

### Return type

[**\Pipedrive\versions\v2\Model\PostPatchGetActivity**](../Model/PostPatchGetActivity.md)

### Authorization

[api_key](../README.md#api_key), [oauth2](../README.md#oauth2)

### HTTP request headers

- **Content-Type**: `application/json`
- **Accept**: `application/json`

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints)
[[Back to Model list]](../README.md#documentation-for-models)
[[Back to README]](../README.md)
