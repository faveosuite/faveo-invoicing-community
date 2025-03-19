# Pipedrive\versions\v2\PersonsApi

All URIs are relative to https://api.pipedrive.com/api/v2.

Method | HTTP request | Description
------------- | ------------- | -------------
[**addPerson()**](PersonsApi.md#addPerson) | **POST** /persons | Add a new person
[**deletePerson()**](PersonsApi.md#deletePerson) | **DELETE** /persons/{id} | Delete a person
[**getPerson()**](PersonsApi.md#getPerson) | **GET** /persons/{id} | Get details of a person
[**getPersons()**](PersonsApi.md#getPersons) | **GET** /persons | Get all persons
[**searchPersons()**](PersonsApi.md#searchPersons) | **GET** /persons/search | Search persons
[**updatePerson()**](PersonsApi.md#updatePerson) | **PATCH** /persons/{id} | Update a person


## `addPerson()`

```php
addPerson($person_request_body): \Pipedrive\versions\v2\Model\PostPatchGetPerson
```

Add a new person

Adds a new person.

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


$apiInstance = new Pipedrive\versions\v2\Api\PersonsApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$person_request_body = new \Pipedrive\versions\v2\Model\PersonRequestBody(); // \Pipedrive\versions\v2\Model\PersonRequestBody

try {
    $result = $apiInstance->addPerson($person_request_body);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling PersonsApi->addPerson: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **person_request_body** | [**\Pipedrive\versions\v2\Model\PersonRequestBody**](../Model/PersonRequestBody.md)|  | [optional]

### Return type

[**\Pipedrive\versions\v2\Model\PostPatchGetPerson**](../Model/PostPatchGetPerson.md)

### Authorization

[api_key](../README.md#api_key), [oauth2](../README.md#oauth2)

### HTTP request headers

- **Content-Type**: `application/json`
- **Accept**: `application/json`

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints)
[[Back to Model list]](../README.md#documentation-for-models)
[[Back to README]](../README.md)

## `deletePerson()`

```php
deletePerson($id): \Pipedrive\versions\v2\Model\DeletePersonResponse
```

Delete a person

Marks a person as deleted. After 30 days, the person will be permanently deleted.

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


$apiInstance = new Pipedrive\versions\v2\Api\PersonsApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$id = 56; // int | The ID of the person

try {
    $result = $apiInstance->deletePerson($id);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling PersonsApi->deletePerson: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **id** | **int**| The ID of the person |

### Return type

[**\Pipedrive\versions\v2\Model\DeletePersonResponse**](../Model/DeletePersonResponse.md)

### Authorization

[api_key](../README.md#api_key), [oauth2](../README.md#oauth2)

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json`

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints)
[[Back to Model list]](../README.md#documentation-for-models)
[[Back to README]](../README.md)

## `getPerson()`

```php
getPerson($id, $include_fields, $custom_fields): \Pipedrive\versions\v2\Model\PostPatchGetPerson
```

Get details of a person

Returns the details of a specific person. Fields `ims`, `postal_address`, `notes`, `birthday`, and `job_title` are only included if contact sync is enabled for the company.

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


$apiInstance = new Pipedrive\versions\v2\Api\PersonsApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$id = 56; // int | The ID of the person
$include_fields = 'include_fields_example'; // string | Optional comma separated string array of additional fields to include. `marketing_status` and `doi_status` can only be included if the company has marketing app enabled.
$custom_fields = 'custom_fields_example'; // string | Optional comma separated string array of custom fields keys to include. If you are only interested in a particular set of custom fields, please use this parameter for faster results and smaller response.<br/>A maximum of 15 keys is allowed.

try {
    $result = $apiInstance->getPerson($id, $include_fields, $custom_fields);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling PersonsApi->getPerson: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **id** | **int**| The ID of the person |
 **include_fields** | **string**| Optional comma separated string array of additional fields to include. &#x60;marketing_status&#x60; and &#x60;doi_status&#x60; can only be included if the company has marketing app enabled. | [optional]
 **custom_fields** | **string**| Optional comma separated string array of custom fields keys to include. If you are only interested in a particular set of custom fields, please use this parameter for faster results and smaller response.&lt;br/&gt;A maximum of 15 keys is allowed. | [optional]

### Return type

[**\Pipedrive\versions\v2\Model\PostPatchGetPerson**](../Model/PostPatchGetPerson.md)

### Authorization

[api_key](../README.md#api_key), [oauth2](../README.md#oauth2)

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json`

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints)
[[Back to Model list]](../README.md#documentation-for-models)
[[Back to README]](../README.md)

## `getPersons()`

```php
getPersons($filter_id, $ids, $owner_id, $org_id, $updated_since, $updated_until, $sort_by, $sort_direction, $include_fields, $custom_fields, $limit, $cursor): \Pipedrive\versions\v2\Model\GetPersons
```

Get all persons

Returns data about all persons. Fields `ims`, `postal_address`, `notes`, `birthday`, and `job_title` are only included if contact sync is enabled for the company.

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


$apiInstance = new Pipedrive\versions\v2\Api\PersonsApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$filter_id = 56; // int | If supplied, only persons matching the specified filter are returned
$ids = 'ids_example'; // string | Optional comma separated string array of up to 100 entity ids to fetch. If filter_id is provided, this is ignored. If any of the requested entities do not exist or are not visible, they are not included in the response.
$owner_id = 56; // int | If supplied, only persons owned by the specified user are returned. If filter_id is provided, this is ignored.
$org_id = 56; // int | If supplied, only persons linked to the specified organization are returned. If filter_id is provided, this is ignored.
$updated_since = 'updated_since_example'; // string | If set, only persons with an `update_time` later than or equal to this time are returned. In RFC3339 format, e.g. 2025-01-01T10:20:00Z.
$updated_until = 'updated_until_example'; // string | If set, only persons with an `update_time` earlier than this time are returned. In RFC3339 format, e.g. 2025-01-01T10:20:00Z.
$sort_by = 'id'; // string | The field to sort by. Supported fields: `id`, `update_time`, `add_time`.
$sort_direction = 'asc'; // string | The sorting direction. Supported values: `asc`, `desc`.
$include_fields = 'include_fields_example'; // string | Optional comma separated string array of additional fields to include. `marketing_status` and `doi_status` can only be included if the company has marketing app enabled.
$custom_fields = 'custom_fields_example'; // string | Optional comma separated string array of custom fields keys to include. If you are only interested in a particular set of custom fields, please use this parameter for faster results and smaller response.<br/>A maximum of 15 keys is allowed.
$limit = 100; // int | For pagination, the limit of entries to be returned. If not provided, 100 items will be returned. Please note that a maximum value of 500 is allowed.
$cursor = 'cursor_example'; // string | For pagination, the marker (an opaque string value) representing the first item on the next page

try {
    $result = $apiInstance->getPersons($filter_id, $ids, $owner_id, $org_id, $updated_since, $updated_until, $sort_by, $sort_direction, $include_fields, $custom_fields, $limit, $cursor);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling PersonsApi->getPersons: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **filter_id** | **int**| If supplied, only persons matching the specified filter are returned | [optional]
 **ids** | **string**| Optional comma separated string array of up to 100 entity ids to fetch. If filter_id is provided, this is ignored. If any of the requested entities do not exist or are not visible, they are not included in the response. | [optional]
 **owner_id** | **int**| If supplied, only persons owned by the specified user are returned. If filter_id is provided, this is ignored. | [optional]
 **org_id** | **int**| If supplied, only persons linked to the specified organization are returned. If filter_id is provided, this is ignored. | [optional]
 **updated_since** | **string**| If set, only persons with an &#x60;update_time&#x60; later than or equal to this time are returned. In RFC3339 format, e.g. 2025-01-01T10:20:00Z. | [optional]
 **updated_until** | **string**| If set, only persons with an &#x60;update_time&#x60; earlier than this time are returned. In RFC3339 format, e.g. 2025-01-01T10:20:00Z. | [optional]
 **sort_by** | **string**| The field to sort by. Supported fields: &#x60;id&#x60;, &#x60;update_time&#x60;, &#x60;add_time&#x60;. | [optional] [default to &#39;id&#39;]
 **sort_direction** | **string**| The sorting direction. Supported values: &#x60;asc&#x60;, &#x60;desc&#x60;. | [optional] [default to &#39;asc&#39;]
 **include_fields** | **string**| Optional comma separated string array of additional fields to include. &#x60;marketing_status&#x60; and &#x60;doi_status&#x60; can only be included if the company has marketing app enabled. | [optional]
 **custom_fields** | **string**| Optional comma separated string array of custom fields keys to include. If you are only interested in a particular set of custom fields, please use this parameter for faster results and smaller response.&lt;br/&gt;A maximum of 15 keys is allowed. | [optional]
 **limit** | **int**| For pagination, the limit of entries to be returned. If not provided, 100 items will be returned. Please note that a maximum value of 500 is allowed. | [optional]
 **cursor** | **string**| For pagination, the marker (an opaque string value) representing the first item on the next page | [optional]

### Return type

[**\Pipedrive\versions\v2\Model\GetPersons**](../Model/GetPersons.md)

### Authorization

[api_key](../README.md#api_key), [oauth2](../README.md#oauth2)

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json`

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints)
[[Back to Model list]](../README.md#documentation-for-models)
[[Back to README]](../README.md)

## `searchPersons()`

```php
searchPersons($term, $fields, $exact_match, $organization_id, $include_fields, $limit, $cursor): \Pipedrive\versions\v2\Model\PersonSearchResponse
```

Search persons

Searches all persons by name, email, phone, notes and/or custom fields. This endpoint is a wrapper of <a href=\"https://developers.pipedrive.com/docs/api/v1/ItemSearch#searchItem\">/v1/itemSearch</a> with a narrower OAuth scope. Found persons can be filtered by organization ID.

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


$apiInstance = new Pipedrive\versions\v2\Api\PersonsApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$term = 'term_example'; // string | The search term to look for. Minimum 2 characters (or 1 if using `exact_match`). Please note that the search term has to be URL encoded.
$fields = 'fields_example'; // string | A comma-separated string array. The fields to perform the search from. Defaults to all of them. Only the following custom field types are searchable: `address`, `varchar`, `text`, `varchar_auto`, `double`, `monetary` and `phone`. Read more about searching by custom fields <a href=\"https://support.pipedrive.com/en/article/search-finding-what-you-need#searching-by-custom-fields\" target=\"_blank\" rel=\"noopener noreferrer\">here</a>.
$exact_match = True; // bool | When enabled, only full exact matches against the given term are returned. It is <b>not</b> case sensitive.
$organization_id = 56; // int | Will filter persons by the provided organization ID. The upper limit of found persons associated with the organization is 2000.
$include_fields = 'include_fields_example'; // string | Supports including optional fields in the results which are not provided by default
$limit = 100; // int | For pagination, the limit of entries to be returned. If not provided, 100 items will be returned. Please note that a maximum value of 500 is allowed.
$cursor = 'cursor_example'; // string | For pagination, the marker (an opaque string value) representing the first item on the next page

try {
    $result = $apiInstance->searchPersons($term, $fields, $exact_match, $organization_id, $include_fields, $limit, $cursor);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling PersonsApi->searchPersons: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **term** | **string**| The search term to look for. Minimum 2 characters (or 1 if using &#x60;exact_match&#x60;). Please note that the search term has to be URL encoded. |
 **fields** | **string**| A comma-separated string array. The fields to perform the search from. Defaults to all of them. Only the following custom field types are searchable: &#x60;address&#x60;, &#x60;varchar&#x60;, &#x60;text&#x60;, &#x60;varchar_auto&#x60;, &#x60;double&#x60;, &#x60;monetary&#x60; and &#x60;phone&#x60;. Read more about searching by custom fields &lt;a href&#x3D;\&quot;https://support.pipedrive.com/en/article/search-finding-what-you-need#searching-by-custom-fields\&quot; target&#x3D;\&quot;_blank\&quot; rel&#x3D;\&quot;noopener noreferrer\&quot;&gt;here&lt;/a&gt;. | [optional]
 **exact_match** | **bool**| When enabled, only full exact matches against the given term are returned. It is &lt;b&gt;not&lt;/b&gt; case sensitive. | [optional]
 **organization_id** | **int**| Will filter persons by the provided organization ID. The upper limit of found persons associated with the organization is 2000. | [optional]
 **include_fields** | **string**| Supports including optional fields in the results which are not provided by default | [optional]
 **limit** | **int**| For pagination, the limit of entries to be returned. If not provided, 100 items will be returned. Please note that a maximum value of 500 is allowed. | [optional]
 **cursor** | **string**| For pagination, the marker (an opaque string value) representing the first item on the next page | [optional]

### Return type

[**\Pipedrive\versions\v2\Model\PersonSearchResponse**](../Model/PersonSearchResponse.md)

### Authorization

[api_key](../README.md#api_key), [oauth2](../README.md#oauth2)

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json`

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints)
[[Back to Model list]](../README.md#documentation-for-models)
[[Back to README]](../README.md)

## `updatePerson()`

```php
updatePerson($id, $person_request_body): \Pipedrive\versions\v2\Model\PostPatchGetPerson
```

Update a person

Updates the properties of a person.

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


$apiInstance = new Pipedrive\versions\v2\Api\PersonsApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$id = 56; // int | The ID of the person
$person_request_body = new \Pipedrive\versions\v2\Model\PersonRequestBody(); // \Pipedrive\versions\v2\Model\PersonRequestBody

try {
    $result = $apiInstance->updatePerson($id, $person_request_body);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling PersonsApi->updatePerson: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **id** | **int**| The ID of the person |
 **person_request_body** | [**\Pipedrive\versions\v2\Model\PersonRequestBody**](../Model/PersonRequestBody.md)|  | [optional]

### Return type

[**\Pipedrive\versions\v2\Model\PostPatchGetPerson**](../Model/PostPatchGetPerson.md)

### Authorization

[api_key](../README.md#api_key), [oauth2](../README.md#oauth2)

### HTTP request headers

- **Content-Type**: `application/json`
- **Accept**: `application/json`

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints)
[[Back to Model list]](../README.md#documentation-for-models)
[[Back to README]](../README.md)
