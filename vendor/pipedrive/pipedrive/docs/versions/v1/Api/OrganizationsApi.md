# Pipedrive\versions\v1\OrganizationsApi

All URIs are relative to https://api.pipedrive.com/v1.

Method | HTTP request | Description
------------- | ------------- | -------------
[**addOrganization()**](OrganizationsApi.md#addOrganization) | **POST** /organizations | Add an organization
[**addOrganizationFollower()**](OrganizationsApi.md#addOrganizationFollower) | **POST** /organizations/{id}/followers | Add a follower to an organization
[**deleteOrganization()**](OrganizationsApi.md#deleteOrganization) | **DELETE** /organizations/{id} | Delete an organization
[**deleteOrganizationFollower()**](OrganizationsApi.md#deleteOrganizationFollower) | **DELETE** /organizations/{id}/followers/{follower_id} | Delete a follower from an organization
[**deleteOrganizations()**](OrganizationsApi.md#deleteOrganizations) | **DELETE** /organizations | Delete multiple organizations in bulk
[**getOrganization()**](OrganizationsApi.md#getOrganization) | **GET** /organizations/{id} | Get details of an organization
[**getOrganizationActivities()**](OrganizationsApi.md#getOrganizationActivities) | **GET** /organizations/{id}/activities | List activities associated with an organization
[**getOrganizationChangelog()**](OrganizationsApi.md#getOrganizationChangelog) | **GET** /organizations/{id}/changelog | List updates about organization field values
[**getOrganizationDeals()**](OrganizationsApi.md#getOrganizationDeals) | **GET** /organizations/{id}/deals | List deals associated with an organization
[**getOrganizationFiles()**](OrganizationsApi.md#getOrganizationFiles) | **GET** /organizations/{id}/files | List files attached to an organization
[**getOrganizationFollowers()**](OrganizationsApi.md#getOrganizationFollowers) | **GET** /organizations/{id}/followers | List followers of an organization
[**getOrganizationMailMessages()**](OrganizationsApi.md#getOrganizationMailMessages) | **GET** /organizations/{id}/mailMessages | List mail messages associated with an organization
[**getOrganizationPersons()**](OrganizationsApi.md#getOrganizationPersons) | **GET** /organizations/{id}/persons | List persons of an organization
[**getOrganizationUpdates()**](OrganizationsApi.md#getOrganizationUpdates) | **GET** /organizations/{id}/flow | List updates about an organization
[**getOrganizationUsers()**](OrganizationsApi.md#getOrganizationUsers) | **GET** /organizations/{id}/permittedUsers | List permitted users
[**getOrganizations()**](OrganizationsApi.md#getOrganizations) | **GET** /organizations | Get all organizations
[**getOrganizationsCollection()**](OrganizationsApi.md#getOrganizationsCollection) | **GET** /organizations/collection | Get all organizations (BETA)
[**mergeOrganizations()**](OrganizationsApi.md#mergeOrganizations) | **PUT** /organizations/{id}/merge | Merge two organizations
[**searchOrganization()**](OrganizationsApi.md#searchOrganization) | **GET** /organizations/search | Search organizations
[**updateOrganization()**](OrganizationsApi.md#updateOrganization) | **PUT** /organizations/{id} | Update an organization


## `addOrganization()`

```php
addOrganization($new_organization): \Pipedrive\versions\v1\Model\OrganizationPostResponse
```

Add an organization

Adds a new organization. Note that you can supply additional custom fields along with the request that are not described here. These custom fields are different for each Pipedrive account and can be recognized by long hashes as keys. To determine which custom fields exists, fetch the organizationFields and look for `key` values. For more information, see the tutorial for <a href=\"https://pipedrive.readme.io/docs/adding-an-organization\" target=\"_blank\" rel=\"noopener noreferrer\">adding an organization</a>.

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');


// Configure API key authorization: api_key
$config = (new Pipedrive\versions\v1\Configuration())->setApiKey('api_token', 'YOUR_API_KEY');
// Uncomment below to setup prefix (e.g. Bearer) for API key, if needed
// $config = (new Pipedrive\versions\v1\Configuration())->setApiKeyPrefix('api_token', 'Bearer');

// Configure OAuth2 access token for authorization: oauth2
$config = (new Pipedrive\versions\v1\Configuration())->setAccessToken('YOUR_ACCESS_TOKEN');


$apiInstance = new Pipedrive\versions\v1\Api\OrganizationsApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$new_organization = new \Pipedrive\versions\v1\Model\NewOrganization(); // \Pipedrive\versions\v1\Model\NewOrganization

try {
    $result = $apiInstance->addOrganization($new_organization);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling OrganizationsApi->addOrganization: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **new_organization** | [**\Pipedrive\versions\v1\Model\NewOrganization**](../Model/NewOrganization.md)|  | [optional]

### Return type

[**\Pipedrive\versions\v1\Model\OrganizationPostResponse**](../Model/OrganizationPostResponse.md)

### Authorization

[api_key](../README.md#api_key), [oauth2](../README.md#oauth2)

### HTTP request headers

- **Content-Type**: `application/json`
- **Accept**: `application/json`

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints)
[[Back to Model list]](../README.md#documentation-for-models)
[[Back to README]](../README.md)

## `addOrganizationFollower()`

```php
addOrganizationFollower($id, $add_organization_follower_request): \Pipedrive\versions\v1\Model\OrganizationFollowerPostResponse
```

Add a follower to an organization

Adds a follower to an organization.

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');


// Configure API key authorization: api_key
$config = (new Pipedrive\versions\v1\Configuration())->setApiKey('api_token', 'YOUR_API_KEY');
// Uncomment below to setup prefix (e.g. Bearer) for API key, if needed
// $config = (new Pipedrive\versions\v1\Configuration())->setApiKeyPrefix('api_token', 'Bearer');

// Configure OAuth2 access token for authorization: oauth2
$config = (new Pipedrive\versions\v1\Configuration())->setAccessToken('YOUR_ACCESS_TOKEN');


$apiInstance = new Pipedrive\versions\v1\Api\OrganizationsApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$id = 56; // int | The ID of the organization
$add_organization_follower_request = new \Pipedrive\versions\v1\Model\AddOrganizationFollowerRequest(); // \Pipedrive\versions\v1\Model\AddOrganizationFollowerRequest

try {
    $result = $apiInstance->addOrganizationFollower($id, $add_organization_follower_request);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling OrganizationsApi->addOrganizationFollower: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **id** | **int**| The ID of the organization |
 **add_organization_follower_request** | [**\Pipedrive\versions\v1\Model\AddOrganizationFollowerRequest**](../Model/AddOrganizationFollowerRequest.md)|  | [optional]

### Return type

[**\Pipedrive\versions\v1\Model\OrganizationFollowerPostResponse**](../Model/OrganizationFollowerPostResponse.md)

### Authorization

[api_key](../README.md#api_key), [oauth2](../README.md#oauth2)

### HTTP request headers

- **Content-Type**: `application/json`
- **Accept**: `application/json`

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints)
[[Back to Model list]](../README.md#documentation-for-models)
[[Back to README]](../README.md)

## `deleteOrganization()`

```php
deleteOrganization($id): \Pipedrive\versions\v1\Model\OrganizationDeleteResponse
```

Delete an organization

Marks an organization as deleted. After 30 days, the organization will be permanently deleted.

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');


// Configure API key authorization: api_key
$config = (new Pipedrive\versions\v1\Configuration())->setApiKey('api_token', 'YOUR_API_KEY');
// Uncomment below to setup prefix (e.g. Bearer) for API key, if needed
// $config = (new Pipedrive\versions\v1\Configuration())->setApiKeyPrefix('api_token', 'Bearer');

// Configure OAuth2 access token for authorization: oauth2
$config = (new Pipedrive\versions\v1\Configuration())->setAccessToken('YOUR_ACCESS_TOKEN');


$apiInstance = new Pipedrive\versions\v1\Api\OrganizationsApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$id = 56; // int | The ID of the organization

try {
    $result = $apiInstance->deleteOrganization($id);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling OrganizationsApi->deleteOrganization: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **id** | **int**| The ID of the organization |

### Return type

[**\Pipedrive\versions\v1\Model\OrganizationDeleteResponse**](../Model/OrganizationDeleteResponse.md)

### Authorization

[api_key](../README.md#api_key), [oauth2](../README.md#oauth2)

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json`

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints)
[[Back to Model list]](../README.md#documentation-for-models)
[[Back to README]](../README.md)

## `deleteOrganizationFollower()`

```php
deleteOrganizationFollower($id, $follower_id): \Pipedrive\versions\v1\Model\OrganizationFollowerDeleteResponse
```

Delete a follower from an organization

Deletes a follower from an organization. You can retrieve the `follower_id` from the <a href=\"https://developers.pipedrive.com/docs/api/v1/Organizations#getOrganizationFollowers\">List followers of an organization</a> endpoint.

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');


// Configure API key authorization: api_key
$config = (new Pipedrive\versions\v1\Configuration())->setApiKey('api_token', 'YOUR_API_KEY');
// Uncomment below to setup prefix (e.g. Bearer) for API key, if needed
// $config = (new Pipedrive\versions\v1\Configuration())->setApiKeyPrefix('api_token', 'Bearer');

// Configure OAuth2 access token for authorization: oauth2
$config = (new Pipedrive\versions\v1\Configuration())->setAccessToken('YOUR_ACCESS_TOKEN');


$apiInstance = new Pipedrive\versions\v1\Api\OrganizationsApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$id = 56; // int | The ID of the organization
$follower_id = 56; // int | The ID of the follower

try {
    $result = $apiInstance->deleteOrganizationFollower($id, $follower_id);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling OrganizationsApi->deleteOrganizationFollower: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **id** | **int**| The ID of the organization |
 **follower_id** | **int**| The ID of the follower |

### Return type

[**\Pipedrive\versions\v1\Model\OrganizationFollowerDeleteResponse**](../Model/OrganizationFollowerDeleteResponse.md)

### Authorization

[api_key](../README.md#api_key), [oauth2](../README.md#oauth2)

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json`

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints)
[[Back to Model list]](../README.md#documentation-for-models)
[[Back to README]](../README.md)

## `deleteOrganizations()`

```php
deleteOrganizations($ids): \Pipedrive\versions\v1\Model\OrganizationsDeleteResponse
```

Delete multiple organizations in bulk

Marks multiple organizations as deleted. After 30 days, the organizations will be permanently deleted.

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');


// Configure API key authorization: api_key
$config = (new Pipedrive\versions\v1\Configuration())->setApiKey('api_token', 'YOUR_API_KEY');
// Uncomment below to setup prefix (e.g. Bearer) for API key, if needed
// $config = (new Pipedrive\versions\v1\Configuration())->setApiKeyPrefix('api_token', 'Bearer');

// Configure OAuth2 access token for authorization: oauth2
$config = (new Pipedrive\versions\v1\Configuration())->setAccessToken('YOUR_ACCESS_TOKEN');


$apiInstance = new Pipedrive\versions\v1\Api\OrganizationsApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$ids = 'ids_example'; // string | The comma-separated IDs that will be deleted

try {
    $result = $apiInstance->deleteOrganizations($ids);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling OrganizationsApi->deleteOrganizations: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **ids** | **string**| The comma-separated IDs that will be deleted |

### Return type

[**\Pipedrive\versions\v1\Model\OrganizationsDeleteResponse**](../Model/OrganizationsDeleteResponse.md)

### Authorization

[api_key](../README.md#api_key), [oauth2](../README.md#oauth2)

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json`

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints)
[[Back to Model list]](../README.md#documentation-for-models)
[[Back to README]](../README.md)

## `getOrganization()`

```php
getOrganization($id): \Pipedrive\versions\v1\Model\OrganizationDetailsGetResponse
```

Get details of an organization

Returns the details of an organization. Note that this also returns some additional fields which are not present when asking for all organizations. Also note that custom fields appear as long hashes in the resulting data. These hashes can be mapped against the `key` value of organizationFields.

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');


// Configure API key authorization: api_key
$config = (new Pipedrive\versions\v1\Configuration())->setApiKey('api_token', 'YOUR_API_KEY');
// Uncomment below to setup prefix (e.g. Bearer) for API key, if needed
// $config = (new Pipedrive\versions\v1\Configuration())->setApiKeyPrefix('api_token', 'Bearer');

// Configure OAuth2 access token for authorization: oauth2
$config = (new Pipedrive\versions\v1\Configuration())->setAccessToken('YOUR_ACCESS_TOKEN');


$apiInstance = new Pipedrive\versions\v1\Api\OrganizationsApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$id = 56; // int | The ID of the organization

try {
    $result = $apiInstance->getOrganization($id);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling OrganizationsApi->getOrganization: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **id** | **int**| The ID of the organization |

### Return type

[**\Pipedrive\versions\v1\Model\OrganizationDetailsGetResponse**](../Model/OrganizationDetailsGetResponse.md)

### Authorization

[api_key](../README.md#api_key), [oauth2](../README.md#oauth2)

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json`

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints)
[[Back to Model list]](../README.md#documentation-for-models)
[[Back to README]](../README.md)

## `getOrganizationActivities()`

```php
getOrganizationActivities($id, $start, $limit, $done, $exclude): \Pipedrive\versions\v1\Model\ListActivitiesResponse
```

List activities associated with an organization

Lists activities associated with an organization.

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');


// Configure API key authorization: api_key
$config = (new Pipedrive\versions\v1\Configuration())->setApiKey('api_token', 'YOUR_API_KEY');
// Uncomment below to setup prefix (e.g. Bearer) for API key, if needed
// $config = (new Pipedrive\versions\v1\Configuration())->setApiKeyPrefix('api_token', 'Bearer');

// Configure OAuth2 access token for authorization: oauth2
$config = (new Pipedrive\versions\v1\Configuration())->setAccessToken('YOUR_ACCESS_TOKEN');


$apiInstance = new Pipedrive\versions\v1\Api\OrganizationsApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$id = 56; // int | The ID of the organization
$start = 0; // int | Pagination start
$limit = 56; // int | Items shown per page
$done = new \Pipedrive\versions\v1\Model\\Pipedrive\versions\v1\Model\NumberBoolean(); // \Pipedrive\versions\v1\Model\NumberBoolean | Whether the activity is done or not. 0 = Not done, 1 = Done. If omitted returns both Done and Not done activities.
$exclude = 'exclude_example'; // string | A comma-separated string of activity IDs to exclude from result

try {
    $result = $apiInstance->getOrganizationActivities($id, $start, $limit, $done, $exclude);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling OrganizationsApi->getOrganizationActivities: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **id** | **int**| The ID of the organization |
 **start** | **int**| Pagination start | [optional] [default to 0]
 **limit** | **int**| Items shown per page | [optional]
 **done** | [**\Pipedrive\versions\v1\Model\NumberBoolean**](../Model/.md)| Whether the activity is done or not. 0 &#x3D; Not done, 1 &#x3D; Done. If omitted returns both Done and Not done activities. | [optional]
 **exclude** | **string**| A comma-separated string of activity IDs to exclude from result | [optional]

### Return type

[**\Pipedrive\versions\v1\Model\ListActivitiesResponse**](../Model/ListActivitiesResponse.md)

### Authorization

[api_key](../README.md#api_key), [oauth2](../README.md#oauth2)

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json`

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints)
[[Back to Model list]](../README.md#documentation-for-models)
[[Back to README]](../README.md)

## `getOrganizationChangelog()`

```php
getOrganizationChangelog($id, $cursor, $limit): \Pipedrive\versions\v1\Model\ChangelogResponse
```

List updates about organization field values

Lists updates about field values of an organization.

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');


// Configure API key authorization: api_key
$config = (new Pipedrive\versions\v1\Configuration())->setApiKey('api_token', 'YOUR_API_KEY');
// Uncomment below to setup prefix (e.g. Bearer) for API key, if needed
// $config = (new Pipedrive\versions\v1\Configuration())->setApiKeyPrefix('api_token', 'Bearer');

// Configure OAuth2 access token for authorization: oauth2
$config = (new Pipedrive\versions\v1\Configuration())->setAccessToken('YOUR_ACCESS_TOKEN');


$apiInstance = new Pipedrive\versions\v1\Api\OrganizationsApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$id = 56; // int | The ID of the organization
$cursor = 'cursor_example'; // string | For pagination, the marker (an opaque string value) representing the first item on the next page
$limit = 56; // int | Items shown per page

try {
    $result = $apiInstance->getOrganizationChangelog($id, $cursor, $limit);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling OrganizationsApi->getOrganizationChangelog: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **id** | **int**| The ID of the organization |
 **cursor** | **string**| For pagination, the marker (an opaque string value) representing the first item on the next page | [optional]
 **limit** | **int**| Items shown per page | [optional]

### Return type

[**\Pipedrive\versions\v1\Model\ChangelogResponse**](../Model/ChangelogResponse.md)

### Authorization

[api_key](../README.md#api_key), [oauth2](../README.md#oauth2)

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json`

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints)
[[Back to Model list]](../README.md#documentation-for-models)
[[Back to README]](../README.md)

## `getOrganizationDeals()`

```php
getOrganizationDeals($id, $start, $limit, $status, $sort, $only_primary_association): \Pipedrive\versions\v1\Model\ListDealsResponse
```

List deals associated with an organization

Lists deals associated with an organization.

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');


// Configure API key authorization: api_key
$config = (new Pipedrive\versions\v1\Configuration())->setApiKey('api_token', 'YOUR_API_KEY');
// Uncomment below to setup prefix (e.g. Bearer) for API key, if needed
// $config = (new Pipedrive\versions\v1\Configuration())->setApiKeyPrefix('api_token', 'Bearer');

// Configure OAuth2 access token for authorization: oauth2
$config = (new Pipedrive\versions\v1\Configuration())->setAccessToken('YOUR_ACCESS_TOKEN');


$apiInstance = new Pipedrive\versions\v1\Api\OrganizationsApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$id = 56; // int | The ID of the organization
$start = 0; // int | Pagination start
$limit = 56; // int | Items shown per page
$status = 'all_not_deleted'; // string | Only fetch deals with a specific status. If omitted, all not deleted deals are returned. If set to deleted, deals that have been deleted up to 30 days ago will be included.
$sort = 'sort_example'; // string | The field names and sorting mode separated by a comma (`field_name_1 ASC`, `field_name_2 DESC`). Only first-level field keys are supported (no nested keys).
$only_primary_association = new \Pipedrive\versions\v1\Model\\Pipedrive\versions\v1\Model\NumberBoolean(); // \Pipedrive\versions\v1\Model\NumberBoolean | If set, only deals that are directly associated to the organization are fetched. If not set (default), all deals are fetched that are either directly or indirectly related to the organization. Indirect relations include relations through custom, organization-type fields and through persons of the given organization.

try {
    $result = $apiInstance->getOrganizationDeals($id, $start, $limit, $status, $sort, $only_primary_association);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling OrganizationsApi->getOrganizationDeals: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **id** | **int**| The ID of the organization |
 **start** | **int**| Pagination start | [optional] [default to 0]
 **limit** | **int**| Items shown per page | [optional]
 **status** | **string**| Only fetch deals with a specific status. If omitted, all not deleted deals are returned. If set to deleted, deals that have been deleted up to 30 days ago will be included. | [optional] [default to &#39;all_not_deleted&#39;]
 **sort** | **string**| The field names and sorting mode separated by a comma (&#x60;field_name_1 ASC&#x60;, &#x60;field_name_2 DESC&#x60;). Only first-level field keys are supported (no nested keys). | [optional]
 **only_primary_association** | [**\Pipedrive\versions\v1\Model\NumberBoolean**](../Model/.md)| If set, only deals that are directly associated to the organization are fetched. If not set (default), all deals are fetched that are either directly or indirectly related to the organization. Indirect relations include relations through custom, organization-type fields and through persons of the given organization. | [optional]

### Return type

[**\Pipedrive\versions\v1\Model\ListDealsResponse**](../Model/ListDealsResponse.md)

### Authorization

[api_key](../README.md#api_key), [oauth2](../README.md#oauth2)

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json`

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints)
[[Back to Model list]](../README.md#documentation-for-models)
[[Back to README]](../README.md)

## `getOrganizationFiles()`

```php
getOrganizationFiles($id, $start, $limit, $sort): \Pipedrive\versions\v1\Model\ListFilesResponse
```

List files attached to an organization

Lists files associated with an organization.

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');


// Configure API key authorization: api_key
$config = (new Pipedrive\versions\v1\Configuration())->setApiKey('api_token', 'YOUR_API_KEY');
// Uncomment below to setup prefix (e.g. Bearer) for API key, if needed
// $config = (new Pipedrive\versions\v1\Configuration())->setApiKeyPrefix('api_token', 'Bearer');

// Configure OAuth2 access token for authorization: oauth2
$config = (new Pipedrive\versions\v1\Configuration())->setAccessToken('YOUR_ACCESS_TOKEN');


$apiInstance = new Pipedrive\versions\v1\Api\OrganizationsApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$id = 56; // int | The ID of the organization
$start = 0; // int | Pagination start
$limit = 56; // int | Items shown per page. Please note that a maximum value of 100 is allowed.
$sort = 'sort_example'; // string | Supported fields: `id`, `update_time`

try {
    $result = $apiInstance->getOrganizationFiles($id, $start, $limit, $sort);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling OrganizationsApi->getOrganizationFiles: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **id** | **int**| The ID of the organization |
 **start** | **int**| Pagination start | [optional] [default to 0]
 **limit** | **int**| Items shown per page. Please note that a maximum value of 100 is allowed. | [optional]
 **sort** | **string**| Supported fields: &#x60;id&#x60;, &#x60;update_time&#x60; | [optional]

### Return type

[**\Pipedrive\versions\v1\Model\ListFilesResponse**](../Model/ListFilesResponse.md)

### Authorization

[api_key](../README.md#api_key), [oauth2](../README.md#oauth2)

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json`

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints)
[[Back to Model list]](../README.md#documentation-for-models)
[[Back to README]](../README.md)

## `getOrganizationFollowers()`

```php
getOrganizationFollowers($id): \Pipedrive\versions\v1\Model\OrganizationFollowersListResponse
```

List followers of an organization

Lists the followers of an organization.

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');


// Configure API key authorization: api_key
$config = (new Pipedrive\versions\v1\Configuration())->setApiKey('api_token', 'YOUR_API_KEY');
// Uncomment below to setup prefix (e.g. Bearer) for API key, if needed
// $config = (new Pipedrive\versions\v1\Configuration())->setApiKeyPrefix('api_token', 'Bearer');

// Configure OAuth2 access token for authorization: oauth2
$config = (new Pipedrive\versions\v1\Configuration())->setAccessToken('YOUR_ACCESS_TOKEN');


$apiInstance = new Pipedrive\versions\v1\Api\OrganizationsApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$id = 56; // int | The ID of the organization

try {
    $result = $apiInstance->getOrganizationFollowers($id);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling OrganizationsApi->getOrganizationFollowers: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **id** | **int**| The ID of the organization |

### Return type

[**\Pipedrive\versions\v1\Model\OrganizationFollowersListResponse**](../Model/OrganizationFollowersListResponse.md)

### Authorization

[api_key](../README.md#api_key), [oauth2](../README.md#oauth2)

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json`

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints)
[[Back to Model list]](../README.md#documentation-for-models)
[[Back to README]](../README.md)

## `getOrganizationMailMessages()`

```php
getOrganizationMailMessages($id, $start, $limit): \Pipedrive\versions\v1\Model\ListMailMessagesResponse
```

List mail messages associated with an organization

Lists mail messages associated with an organization.

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');


// Configure API key authorization: api_key
$config = (new Pipedrive\versions\v1\Configuration())->setApiKey('api_token', 'YOUR_API_KEY');
// Uncomment below to setup prefix (e.g. Bearer) for API key, if needed
// $config = (new Pipedrive\versions\v1\Configuration())->setApiKeyPrefix('api_token', 'Bearer');

// Configure OAuth2 access token for authorization: oauth2
$config = (new Pipedrive\versions\v1\Configuration())->setAccessToken('YOUR_ACCESS_TOKEN');


$apiInstance = new Pipedrive\versions\v1\Api\OrganizationsApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$id = 56; // int | The ID of the organization
$start = 0; // int | Pagination start
$limit = 56; // int | Items shown per page

try {
    $result = $apiInstance->getOrganizationMailMessages($id, $start, $limit);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling OrganizationsApi->getOrganizationMailMessages: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **id** | **int**| The ID of the organization |
 **start** | **int**| Pagination start | [optional] [default to 0]
 **limit** | **int**| Items shown per page | [optional]

### Return type

[**\Pipedrive\versions\v1\Model\ListMailMessagesResponse**](../Model/ListMailMessagesResponse.md)

### Authorization

[api_key](../README.md#api_key), [oauth2](../README.md#oauth2)

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json`

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints)
[[Back to Model list]](../README.md#documentation-for-models)
[[Back to README]](../README.md)

## `getOrganizationPersons()`

```php
getOrganizationPersons($id, $start, $limit): \Pipedrive\versions\v1\Model\ListPersonsResponse
```

List persons of an organization

Lists persons associated with an organization.<br>If a company uses the [Campaigns product](https://pipedrive.readme.io/docs/campaigns-in-pipedrive-api), then this endpoint will also return the `data.marketing_status` field.

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');


// Configure API key authorization: api_key
$config = (new Pipedrive\versions\v1\Configuration())->setApiKey('api_token', 'YOUR_API_KEY');
// Uncomment below to setup prefix (e.g. Bearer) for API key, if needed
// $config = (new Pipedrive\versions\v1\Configuration())->setApiKeyPrefix('api_token', 'Bearer');

// Configure OAuth2 access token for authorization: oauth2
$config = (new Pipedrive\versions\v1\Configuration())->setAccessToken('YOUR_ACCESS_TOKEN');


$apiInstance = new Pipedrive\versions\v1\Api\OrganizationsApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$id = 56; // int | The ID of the organization
$start = 0; // int | Pagination start
$limit = 56; // int | Items shown per page

try {
    $result = $apiInstance->getOrganizationPersons($id, $start, $limit);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling OrganizationsApi->getOrganizationPersons: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **id** | **int**| The ID of the organization |
 **start** | **int**| Pagination start | [optional] [default to 0]
 **limit** | **int**| Items shown per page | [optional]

### Return type

[**\Pipedrive\versions\v1\Model\ListPersonsResponse**](../Model/ListPersonsResponse.md)

### Authorization

[api_key](../README.md#api_key), [oauth2](../README.md#oauth2)

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json`

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints)
[[Back to Model list]](../README.md#documentation-for-models)
[[Back to README]](../README.md)

## `getOrganizationUpdates()`

```php
getOrganizationUpdates($id, $start, $limit, $all_changes, $items): \Pipedrive\versions\v1\Model\OrganizationFlowResponse
```

List updates about an organization

Lists updates about an organization.

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');


// Configure API key authorization: api_key
$config = (new Pipedrive\versions\v1\Configuration())->setApiKey('api_token', 'YOUR_API_KEY');
// Uncomment below to setup prefix (e.g. Bearer) for API key, if needed
// $config = (new Pipedrive\versions\v1\Configuration())->setApiKeyPrefix('api_token', 'Bearer');

// Configure OAuth2 access token for authorization: oauth2
$config = (new Pipedrive\versions\v1\Configuration())->setAccessToken('YOUR_ACCESS_TOKEN');


$apiInstance = new Pipedrive\versions\v1\Api\OrganizationsApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$id = 56; // int | The ID of the organization
$start = 0; // int | Pagination start
$limit = 56; // int | Items shown per page
$all_changes = 'all_changes_example'; // string | Whether to show custom field updates or not. 1 = Include custom field changes. If omitted, returns changes without custom field updates.
$items = 'items_example'; // string | A comma-separated string for filtering out item specific updates. (Possible values - activity, plannedActivity, note, file, change, deal, follower, participant, mailMessage, mailMessageWithAttachment, invoice, activityFile, document).

try {
    $result = $apiInstance->getOrganizationUpdates($id, $start, $limit, $all_changes, $items);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling OrganizationsApi->getOrganizationUpdates: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **id** | **int**| The ID of the organization |
 **start** | **int**| Pagination start | [optional] [default to 0]
 **limit** | **int**| Items shown per page | [optional]
 **all_changes** | **string**| Whether to show custom field updates or not. 1 &#x3D; Include custom field changes. If omitted, returns changes without custom field updates. | [optional]
 **items** | **string**| A comma-separated string for filtering out item specific updates. (Possible values - activity, plannedActivity, note, file, change, deal, follower, participant, mailMessage, mailMessageWithAttachment, invoice, activityFile, document). | [optional]

### Return type

[**\Pipedrive\versions\v1\Model\OrganizationFlowResponse**](../Model/OrganizationFlowResponse.md)

### Authorization

[api_key](../README.md#api_key), [oauth2](../README.md#oauth2)

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json`

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints)
[[Back to Model list]](../README.md#documentation-for-models)
[[Back to README]](../README.md)

## `getOrganizationUsers()`

```php
getOrganizationUsers($id): \Pipedrive\versions\v1\Model\ListPermittedUsersResponse1
```

List permitted users

List users permitted to access an organization.

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');


// Configure API key authorization: api_key
$config = (new Pipedrive\versions\v1\Configuration())->setApiKey('api_token', 'YOUR_API_KEY');
// Uncomment below to setup prefix (e.g. Bearer) for API key, if needed
// $config = (new Pipedrive\versions\v1\Configuration())->setApiKeyPrefix('api_token', 'Bearer');

// Configure OAuth2 access token for authorization: oauth2
$config = (new Pipedrive\versions\v1\Configuration())->setAccessToken('YOUR_ACCESS_TOKEN');


$apiInstance = new Pipedrive\versions\v1\Api\OrganizationsApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$id = 56; // int | The ID of the organization

try {
    $result = $apiInstance->getOrganizationUsers($id);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling OrganizationsApi->getOrganizationUsers: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **id** | **int**| The ID of the organization |

### Return type

[**\Pipedrive\versions\v1\Model\ListPermittedUsersResponse1**](../Model/ListPermittedUsersResponse1.md)

### Authorization

[api_key](../README.md#api_key), [oauth2](../README.md#oauth2)

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json`

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints)
[[Back to Model list]](../README.md#documentation-for-models)
[[Back to README]](../README.md)

## `getOrganizations()`

```php
getOrganizations($user_id, $filter_id, $first_char, $start, $limit, $sort): \Pipedrive\versions\v1\Model\AllOrganizationsGetResponse
```

Get all organizations

Returns all organizations.

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');


// Configure API key authorization: api_key
$config = (new Pipedrive\versions\v1\Configuration())->setApiKey('api_token', 'YOUR_API_KEY');
// Uncomment below to setup prefix (e.g. Bearer) for API key, if needed
// $config = (new Pipedrive\versions\v1\Configuration())->setApiKeyPrefix('api_token', 'Bearer');

// Configure OAuth2 access token for authorization: oauth2
$config = (new Pipedrive\versions\v1\Configuration())->setAccessToken('YOUR_ACCESS_TOKEN');


$apiInstance = new Pipedrive\versions\v1\Api\OrganizationsApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$user_id = 56; // int | If supplied, only organizations owned by the given user will be returned. However, `filter_id` takes precedence over `user_id` when both are supplied.
$filter_id = 56; // int | The ID of the filter to use
$first_char = 'first_char_example'; // string | If supplied, only organizations whose name starts with the specified letter will be returned (case-insensitive)
$start = 0; // int | Pagination start
$limit = 56; // int | Items shown per page
$sort = 'sort_example'; // string | The field names and sorting mode separated by a comma (`field_name_1 ASC`, `field_name_2 DESC`). Only first-level field keys are supported (no nested keys).

try {
    $result = $apiInstance->getOrganizations($user_id, $filter_id, $first_char, $start, $limit, $sort);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling OrganizationsApi->getOrganizations: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **user_id** | **int**| If supplied, only organizations owned by the given user will be returned. However, &#x60;filter_id&#x60; takes precedence over &#x60;user_id&#x60; when both are supplied. | [optional]
 **filter_id** | **int**| The ID of the filter to use | [optional]
 **first_char** | **string**| If supplied, only organizations whose name starts with the specified letter will be returned (case-insensitive) | [optional]
 **start** | **int**| Pagination start | [optional] [default to 0]
 **limit** | **int**| Items shown per page | [optional]
 **sort** | **string**| The field names and sorting mode separated by a comma (&#x60;field_name_1 ASC&#x60;, &#x60;field_name_2 DESC&#x60;). Only first-level field keys are supported (no nested keys). | [optional]

### Return type

[**\Pipedrive\versions\v1\Model\AllOrganizationsGetResponse**](../Model/AllOrganizationsGetResponse.md)

### Authorization

[api_key](../README.md#api_key), [oauth2](../README.md#oauth2)

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json`

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints)
[[Back to Model list]](../README.md#documentation-for-models)
[[Back to README]](../README.md)

## `getOrganizationsCollection()`

```php
getOrganizationsCollection($cursor, $limit, $since, $until, $owner_id, $first_char): \Pipedrive\versions\v1\Model\InlineResponse200
```

Get all organizations (BETA)

Returns all organizations. This is a cursor-paginated endpoint that is currently in BETA. For more information, please refer to our documentation on <a href=\"https://pipedrive.readme.io/docs/core-api-concepts-pagination\" target=\"_blank\" rel=\"noopener noreferrer\">pagination</a>. Please note that only global admins (those with global permissions) can access these endpoints. Users with regular permissions will receive a 403 response. Read more about global permissions <a href=\"https://support.pipedrive.com/en/article/global-user-management\" target=\"_blank\" rel=\"noopener noreferrer\">here</a>.

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');


// Configure API key authorization: api_key
$config = (new Pipedrive\versions\v1\Configuration())->setApiKey('api_token', 'YOUR_API_KEY');
// Uncomment below to setup prefix (e.g. Bearer) for API key, if needed
// $config = (new Pipedrive\versions\v1\Configuration())->setApiKeyPrefix('api_token', 'Bearer');

// Configure OAuth2 access token for authorization: oauth2
$config = (new Pipedrive\versions\v1\Configuration())->setAccessToken('YOUR_ACCESS_TOKEN');


$apiInstance = new Pipedrive\versions\v1\Api\OrganizationsApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$cursor = 'cursor_example'; // string | For pagination, the marker (an opaque string value) representing the first item on the next page
$limit = 100; // int | For pagination, the limit of entries to be returned. If not provided, 100 items will be returned. Please note that a maximum value of 500 is allowed.
$since = 'since_example'; // string | The time boundary that points to the start of the range of data. Datetime in ISO 8601 format. E.g. 2022-11-01 08:55:59. Operates on the `update_time` field.
$until = 'until_example'; // string | The time boundary that points to the end of the range of data. Datetime in ISO 8601 format. E.g. 2022-11-01 08:55:59. Operates on the `update_time` field.
$owner_id = 56; // int | If supplied, only organizations owned by the given user will be returned
$first_char = 'first_char_example'; // string | If supplied, only organizations whose name starts with the specified letter will be returned (case-insensitive)

try {
    $result = $apiInstance->getOrganizationsCollection($cursor, $limit, $since, $until, $owner_id, $first_char);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling OrganizationsApi->getOrganizationsCollection: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **cursor** | **string**| For pagination, the marker (an opaque string value) representing the first item on the next page | [optional]
 **limit** | **int**| For pagination, the limit of entries to be returned. If not provided, 100 items will be returned. Please note that a maximum value of 500 is allowed. | [optional]
 **since** | **string**| The time boundary that points to the start of the range of data. Datetime in ISO 8601 format. E.g. 2022-11-01 08:55:59. Operates on the &#x60;update_time&#x60; field. | [optional]
 **until** | **string**| The time boundary that points to the end of the range of data. Datetime in ISO 8601 format. E.g. 2022-11-01 08:55:59. Operates on the &#x60;update_time&#x60; field. | [optional]
 **owner_id** | **int**| If supplied, only organizations owned by the given user will be returned | [optional]
 **first_char** | **string**| If supplied, only organizations whose name starts with the specified letter will be returned (case-insensitive) | [optional]

### Return type

[**\Pipedrive\versions\v1\Model\InlineResponse200**](../Model/InlineResponse200.md)

### Authorization

[api_key](../README.md#api_key), [oauth2](../README.md#oauth2)

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json`

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints)
[[Back to Model list]](../README.md#documentation-for-models)
[[Back to README]](../README.md)

## `mergeOrganizations()`

```php
mergeOrganizations($id, $merge_organizations_request): \Pipedrive\versions\v1\Model\OrganizationsMergeResponse
```

Merge two organizations

Merges an organization with another organization. For more information, see the tutorial for <a href=\"https://pipedrive.readme.io/docs/merging-two-organizations\" target=\"_blank\" rel=\"noopener noreferrer\">merging two organizations</a>.

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');


// Configure API key authorization: api_key
$config = (new Pipedrive\versions\v1\Configuration())->setApiKey('api_token', 'YOUR_API_KEY');
// Uncomment below to setup prefix (e.g. Bearer) for API key, if needed
// $config = (new Pipedrive\versions\v1\Configuration())->setApiKeyPrefix('api_token', 'Bearer');

// Configure OAuth2 access token for authorization: oauth2
$config = (new Pipedrive\versions\v1\Configuration())->setAccessToken('YOUR_ACCESS_TOKEN');


$apiInstance = new Pipedrive\versions\v1\Api\OrganizationsApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$id = 56; // int | The ID of the organization
$merge_organizations_request = new \Pipedrive\versions\v1\Model\MergeOrganizationsRequest(); // \Pipedrive\versions\v1\Model\MergeOrganizationsRequest

try {
    $result = $apiInstance->mergeOrganizations($id, $merge_organizations_request);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling OrganizationsApi->mergeOrganizations: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **id** | **int**| The ID of the organization |
 **merge_organizations_request** | [**\Pipedrive\versions\v1\Model\MergeOrganizationsRequest**](../Model/MergeOrganizationsRequest.md)|  | [optional]

### Return type

[**\Pipedrive\versions\v1\Model\OrganizationsMergeResponse**](../Model/OrganizationsMergeResponse.md)

### Authorization

[api_key](../README.md#api_key), [oauth2](../README.md#oauth2)

### HTTP request headers

- **Content-Type**: `application/json`
- **Accept**: `application/json`

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints)
[[Back to Model list]](../README.md#documentation-for-models)
[[Back to README]](../README.md)

## `searchOrganization()`

```php
searchOrganization($term, $fields, $exact_match, $start, $limit): \Pipedrive\versions\v1\Model\OrganizationSearchResponse
```

Search organizations

Searches all organizations by name, address, notes and/or custom fields. This endpoint is a wrapper of <a href=\"https://developers.pipedrive.com/docs/api/v1/ItemSearch#searchItem\">/v1/itemSearch</a> with a narrower OAuth scope.

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');


// Configure API key authorization: api_key
$config = (new Pipedrive\versions\v1\Configuration())->setApiKey('api_token', 'YOUR_API_KEY');
// Uncomment below to setup prefix (e.g. Bearer) for API key, if needed
// $config = (new Pipedrive\versions\v1\Configuration())->setApiKeyPrefix('api_token', 'Bearer');

// Configure OAuth2 access token for authorization: oauth2
$config = (new Pipedrive\versions\v1\Configuration())->setAccessToken('YOUR_ACCESS_TOKEN');


$apiInstance = new Pipedrive\versions\v1\Api\OrganizationsApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$term = 'term_example'; // string | The search term to look for. Minimum 2 characters (or 1 if using `exact_match`). Please note that the search term has to be URL encoded.
$fields = 'fields_example'; // string | A comma-separated string array. The fields to perform the search from. Defaults to all of them. Only the following custom field types are searchable: `address`, `varchar`, `text`, `varchar_auto`, `double`, `monetary` and `phone`. Read more about searching by custom fields <a href=\"https://support.pipedrive.com/en/article/search-finding-what-you-need#searching-by-custom-fields\" target=\"_blank\" rel=\"noopener noreferrer\">here</a>.
$exact_match = True; // bool | When enabled, only full exact matches against the given term are returned. It is <b>not</b> case sensitive.
$start = 0; // int | Pagination start. Note that the pagination is based on main results and does not include related items when using `search_for_related_items` parameter.
$limit = 56; // int | Items shown per page

try {
    $result = $apiInstance->searchOrganization($term, $fields, $exact_match, $start, $limit);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling OrganizationsApi->searchOrganization: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **term** | **string**| The search term to look for. Minimum 2 characters (or 1 if using &#x60;exact_match&#x60;). Please note that the search term has to be URL encoded. |
 **fields** | **string**| A comma-separated string array. The fields to perform the search from. Defaults to all of them. Only the following custom field types are searchable: &#x60;address&#x60;, &#x60;varchar&#x60;, &#x60;text&#x60;, &#x60;varchar_auto&#x60;, &#x60;double&#x60;, &#x60;monetary&#x60; and &#x60;phone&#x60;. Read more about searching by custom fields &lt;a href&#x3D;\&quot;https://support.pipedrive.com/en/article/search-finding-what-you-need#searching-by-custom-fields\&quot; target&#x3D;\&quot;_blank\&quot; rel&#x3D;\&quot;noopener noreferrer\&quot;&gt;here&lt;/a&gt;. | [optional]
 **exact_match** | **bool**| When enabled, only full exact matches against the given term are returned. It is &lt;b&gt;not&lt;/b&gt; case sensitive. | [optional]
 **start** | **int**| Pagination start. Note that the pagination is based on main results and does not include related items when using &#x60;search_for_related_items&#x60; parameter. | [optional] [default to 0]
 **limit** | **int**| Items shown per page | [optional]

### Return type

[**\Pipedrive\versions\v1\Model\OrganizationSearchResponse**](../Model/OrganizationSearchResponse.md)

### Authorization

[api_key](../README.md#api_key), [oauth2](../README.md#oauth2)

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json`

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints)
[[Back to Model list]](../README.md#documentation-for-models)
[[Back to README]](../README.md)

## `updateOrganization()`

```php
updateOrganization($id, $update_organization): \Pipedrive\versions\v1\Model\OrganizationUpdateResponse
```

Update an organization

Updates the properties of an organization.

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');


// Configure API key authorization: api_key
$config = (new Pipedrive\versions\v1\Configuration())->setApiKey('api_token', 'YOUR_API_KEY');
// Uncomment below to setup prefix (e.g. Bearer) for API key, if needed
// $config = (new Pipedrive\versions\v1\Configuration())->setApiKeyPrefix('api_token', 'Bearer');

// Configure OAuth2 access token for authorization: oauth2
$config = (new Pipedrive\versions\v1\Configuration())->setAccessToken('YOUR_ACCESS_TOKEN');


$apiInstance = new Pipedrive\versions\v1\Api\OrganizationsApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$id = 56; // int | The ID of the organization
$update_organization = new \Pipedrive\versions\v1\Model\UpdateOrganization(); // \Pipedrive\versions\v1\Model\UpdateOrganization

try {
    $result = $apiInstance->updateOrganization($id, $update_organization);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling OrganizationsApi->updateOrganization: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **id** | **int**| The ID of the organization |
 **update_organization** | [**\Pipedrive\versions\v1\Model\UpdateOrganization**](../Model/UpdateOrganization.md)|  | [optional]

### Return type

[**\Pipedrive\versions\v1\Model\OrganizationUpdateResponse**](../Model/OrganizationUpdateResponse.md)

### Authorization

[api_key](../README.md#api_key), [oauth2](../README.md#oauth2)

### HTTP request headers

- **Content-Type**: `application/json`
- **Accept**: `application/json`

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints)
[[Back to Model list]](../README.md#documentation-for-models)
[[Back to README]](../README.md)
