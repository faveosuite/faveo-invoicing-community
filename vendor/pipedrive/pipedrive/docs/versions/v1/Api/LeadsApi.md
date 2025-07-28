# Pipedrive\versions\v1\LeadsApi

All URIs are relative to https://api.pipedrive.com/v1.

Method | HTTP request | Description
------------- | ------------- | -------------
[**addLead()**](LeadsApi.md#addLead) | **POST** /leads | Add a lead
[**deleteLead()**](LeadsApi.md#deleteLead) | **DELETE** /leads/{id} | Delete a lead
[**getArchivedLeads()**](LeadsApi.md#getArchivedLeads) | **GET** /leads/archived | Get all archived leads
[**getLead()**](LeadsApi.md#getLead) | **GET** /leads/{id} | Get one lead
[**getLeadUsers()**](LeadsApi.md#getLeadUsers) | **GET** /leads/{id}/permittedUsers | List permitted users
[**getLeads()**](LeadsApi.md#getLeads) | **GET** /leads | Get all leads
[**searchLeads()**](LeadsApi.md#searchLeads) | **GET** /leads/search | Search leads
[**updateLead()**](LeadsApi.md#updateLead) | **PATCH** /leads/{id} | Update a lead


## `addLead()`

```php
addLead($add_lead_request): \Pipedrive\versions\v1\Model\GetLeadResponse
```

Add a lead

Creates a lead. A lead always has to be linked to a person or an organization or both. All leads created through the Pipedrive API will have a lead source and origin set to `API`. Here's the tutorial for <a href=\"https://pipedrive.readme.io/docs/adding-a-lead\" target=\"_blank\" rel=\"noopener noreferrer\">adding a lead</a>. If a lead contains custom fields, the fields' values will be included in the response in the same format as with the `Deals` endpoints. If a custom field's value hasn't been set for the lead, it won't appear in the response. Please note that leads do not have a separate set of custom fields, instead they inherit the custom fields' structure from deals. See an example given in the <a href=\"https://pipedrive.readme.io/docs/updating-custom-field-value\" target=\"_blank\" rel=\"noopener noreferrer\">updating custom fields' values tutorial</a>.

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


$apiInstance = new Pipedrive\versions\v1\Api\LeadsApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$add_lead_request = new \Pipedrive\versions\v1\Model\AddLeadRequest(); // \Pipedrive\versions\v1\Model\AddLeadRequest

try {
    $result = $apiInstance->addLead($add_lead_request);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling LeadsApi->addLead: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **add_lead_request** | [**\Pipedrive\versions\v1\Model\AddLeadRequest**](../Model/AddLeadRequest.md)|  | [optional]

### Return type

[**\Pipedrive\versions\v1\Model\GetLeadResponse**](../Model/GetLeadResponse.md)

### Authorization

[api_key](../README.md#api_key), [oauth2](../README.md#oauth2)

### HTTP request headers

- **Content-Type**: `application/json`
- **Accept**: `application/json`

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints)
[[Back to Model list]](../README.md#documentation-for-models)
[[Back to README]](../README.md)

## `deleteLead()`

```php
deleteLead($id): \Pipedrive\versions\v1\Model\GetLeadIdResponse
```

Delete a lead

Deletes a specific lead.

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


$apiInstance = new Pipedrive\versions\v1\Api\LeadsApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$id = 'id_example'; // string | The ID of the lead

try {
    $result = $apiInstance->deleteLead($id);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling LeadsApi->deleteLead: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **id** | **string**| The ID of the lead |

### Return type

[**\Pipedrive\versions\v1\Model\GetLeadIdResponse**](../Model/GetLeadIdResponse.md)

### Authorization

[api_key](../README.md#api_key), [oauth2](../README.md#oauth2)

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json`

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints)
[[Back to Model list]](../README.md#documentation-for-models)
[[Back to README]](../README.md)

## `getArchivedLeads()`

```php
getArchivedLeads($limit, $start, $owner_id, $person_id, $organization_id, $filter_id, $sort): \Pipedrive\versions\v1\Model\GetLeadsResponse
```

Get all archived leads

Returns multiple archived leads. Leads are sorted by the time they were created, from oldest to newest. Pagination can be controlled using `limit` and `start` query parameters. If a lead contains custom fields, the fields' values will be included in the response in the same format as with the `Deals` endpoints. If a custom field's value hasn't been set for the lead, it won't appear in the response. Please note that leads do not have a separate set of custom fields, instead they inherit the custom fields' structure from deals.

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


$apiInstance = new Pipedrive\versions\v1\Api\LeadsApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$limit = 100; // int | For pagination, the limit of entries to be returned. If not provided, 100 items will be returned.
$start = 0; // int | For pagination, the position that represents the first result for the page
$owner_id = 1; // int | If supplied, only leads matching the given user will be returned. However, `filter_id` takes precedence over `owner_id` when supplied.
$person_id = 1; // int | If supplied, only leads matching the given person will be returned. However, `filter_id` takes precedence over `person_id` when supplied.
$organization_id = 1; // int | If supplied, only leads matching the given organization will be returned. However, `filter_id` takes precedence over `organization_id` when supplied.
$filter_id = 1; // int | The ID of the filter to use
$sort = 'sort_example'; // string | The field names and sorting mode separated by a comma (`field_name_1 ASC`, `field_name_2 DESC`). Only first-level field keys are supported (no nested keys).

try {
    $result = $apiInstance->getArchivedLeads($limit, $start, $owner_id, $person_id, $organization_id, $filter_id, $sort);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling LeadsApi->getArchivedLeads: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **limit** | **int**| For pagination, the limit of entries to be returned. If not provided, 100 items will be returned. | [optional]
 **start** | **int**| For pagination, the position that represents the first result for the page | [optional]
 **owner_id** | **int**| If supplied, only leads matching the given user will be returned. However, &#x60;filter_id&#x60; takes precedence over &#x60;owner_id&#x60; when supplied. | [optional]
 **person_id** | **int**| If supplied, only leads matching the given person will be returned. However, &#x60;filter_id&#x60; takes precedence over &#x60;person_id&#x60; when supplied. | [optional]
 **organization_id** | **int**| If supplied, only leads matching the given organization will be returned. However, &#x60;filter_id&#x60; takes precedence over &#x60;organization_id&#x60; when supplied. | [optional]
 **filter_id** | **int**| The ID of the filter to use | [optional]
 **sort** | **string**| The field names and sorting mode separated by a comma (&#x60;field_name_1 ASC&#x60;, &#x60;field_name_2 DESC&#x60;). Only first-level field keys are supported (no nested keys). | [optional]

### Return type

[**\Pipedrive\versions\v1\Model\GetLeadsResponse**](../Model/GetLeadsResponse.md)

### Authorization

[api_key](../README.md#api_key), [oauth2](../README.md#oauth2)

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json`

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints)
[[Back to Model list]](../README.md#documentation-for-models)
[[Back to README]](../README.md)

## `getLead()`

```php
getLead($id): \Pipedrive\versions\v1\Model\GetLeadResponse
```

Get one lead

Returns details of a specific lead. If a lead contains custom fields, the fields' values will be included in the response in the same format as with the `Deals` endpoints. If a custom field's value hasn't been set for the lead, it won't appear in the response. Please note that leads do not have a separate set of custom fields, instead they inherit the custom fields’ structure from deals.

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


$apiInstance = new Pipedrive\versions\v1\Api\LeadsApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$id = 'id_example'; // string | The ID of the lead

try {
    $result = $apiInstance->getLead($id);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling LeadsApi->getLead: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **id** | **string**| The ID of the lead |

### Return type

[**\Pipedrive\versions\v1\Model\GetLeadResponse**](../Model/GetLeadResponse.md)

### Authorization

[api_key](../README.md#api_key), [oauth2](../README.md#oauth2)

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json`

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints)
[[Back to Model list]](../README.md#documentation-for-models)
[[Back to README]](../README.md)

## `getLeadUsers()`

```php
getLeadUsers($id): \Pipedrive\versions\v1\Model\UserIDs
```

List permitted users

Lists the users permitted to access a lead.

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


$apiInstance = new Pipedrive\versions\v1\Api\LeadsApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$id = 'id_example'; // string | The ID of the lead

try {
    $result = $apiInstance->getLeadUsers($id);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling LeadsApi->getLeadUsers: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **id** | **string**| The ID of the lead |

### Return type

[**\Pipedrive\versions\v1\Model\UserIDs**](../Model/UserIDs.md)

### Authorization

[api_key](../README.md#api_key), [oauth2](../README.md#oauth2)

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json`

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints)
[[Back to Model list]](../README.md#documentation-for-models)
[[Back to README]](../README.md)

## `getLeads()`

```php
getLeads($limit, $start, $owner_id, $person_id, $organization_id, $filter_id, $sort): \Pipedrive\versions\v1\Model\GetLeadsResponse
```

Get all leads

Returns multiple not archived leads. Leads are sorted by the time they were created, from oldest to newest. Pagination can be controlled using `limit` and `start` query parameters. If a lead contains custom fields, the fields' values will be included in the response in the same format as with the `Deals` endpoints. If a custom field's value hasn't been set for the lead, it won't appear in the response. Please note that leads do not have a separate set of custom fields, instead they inherit the custom fields' structure from deals.

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


$apiInstance = new Pipedrive\versions\v1\Api\LeadsApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$limit = 100; // int | For pagination, the limit of entries to be returned. If not provided, 100 items will be returned.
$start = 0; // int | For pagination, the position that represents the first result for the page
$owner_id = 1; // int | If supplied, only leads matching the given user will be returned. However, `filter_id` takes precedence over `owner_id` when supplied.
$person_id = 1; // int | If supplied, only leads matching the given person will be returned. However, `filter_id` takes precedence over `person_id` when supplied.
$organization_id = 1; // int | If supplied, only leads matching the given organization will be returned. However, `filter_id` takes precedence over `organization_id` when supplied.
$filter_id = 1; // int | The ID of the filter to use
$sort = 'sort_example'; // string | The field names and sorting mode separated by a comma (`field_name_1 ASC`, `field_name_2 DESC`). Only first-level field keys are supported (no nested keys).

try {
    $result = $apiInstance->getLeads($limit, $start, $owner_id, $person_id, $organization_id, $filter_id, $sort);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling LeadsApi->getLeads: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **limit** | **int**| For pagination, the limit of entries to be returned. If not provided, 100 items will be returned. | [optional]
 **start** | **int**| For pagination, the position that represents the first result for the page | [optional]
 **owner_id** | **int**| If supplied, only leads matching the given user will be returned. However, &#x60;filter_id&#x60; takes precedence over &#x60;owner_id&#x60; when supplied. | [optional]
 **person_id** | **int**| If supplied, only leads matching the given person will be returned. However, &#x60;filter_id&#x60; takes precedence over &#x60;person_id&#x60; when supplied. | [optional]
 **organization_id** | **int**| If supplied, only leads matching the given organization will be returned. However, &#x60;filter_id&#x60; takes precedence over &#x60;organization_id&#x60; when supplied. | [optional]
 **filter_id** | **int**| The ID of the filter to use | [optional]
 **sort** | **string**| The field names and sorting mode separated by a comma (&#x60;field_name_1 ASC&#x60;, &#x60;field_name_2 DESC&#x60;). Only first-level field keys are supported (no nested keys). | [optional]

### Return type

[**\Pipedrive\versions\v1\Model\GetLeadsResponse**](../Model/GetLeadsResponse.md)

### Authorization

[api_key](../README.md#api_key), [oauth2](../README.md#oauth2)

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json`

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints)
[[Back to Model list]](../README.md#documentation-for-models)
[[Back to README]](../README.md)

## `searchLeads()`

```php
searchLeads($term, $fields, $exact_match, $person_id, $organization_id, $include_fields, $start, $limit): \Pipedrive\versions\v1\Model\LeadSearchResponse
```

Search leads

Searches all leads by title, notes and/or custom fields. This endpoint is a wrapper of <a href=\"https://developers.pipedrive.com/docs/api/v1/ItemSearch#searchItem\">/v1/itemSearch</a> with a narrower OAuth scope. Found leads can be filtered by the person ID and the organization ID.

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


$apiInstance = new Pipedrive\versions\v1\Api\LeadsApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$term = 'term_example'; // string | The search term to look for. Minimum 2 characters (or 1 if using `exact_match`). Please note that the search term has to be URL encoded.
$fields = 'fields_example'; // string | A comma-separated string array. The fields to perform the search from. Defaults to all of them.
$exact_match = True; // bool | When enabled, only full exact matches against the given term are returned. It is <b>not</b> case sensitive.
$person_id = 56; // int | Will filter leads by the provided person ID. The upper limit of found leads associated with the person is 2000.
$organization_id = 56; // int | Will filter leads by the provided organization ID. The upper limit of found leads associated with the organization is 2000.
$include_fields = 'include_fields_example'; // string | Supports including optional fields in the results which are not provided by default
$start = 0; // int | Pagination start. Note that the pagination is based on main results and does not include related items when using `search_for_related_items` parameter.
$limit = 56; // int | Items shown per page

try {
    $result = $apiInstance->searchLeads($term, $fields, $exact_match, $person_id, $organization_id, $include_fields, $start, $limit);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling LeadsApi->searchLeads: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **term** | **string**| The search term to look for. Minimum 2 characters (or 1 if using &#x60;exact_match&#x60;). Please note that the search term has to be URL encoded. |
 **fields** | **string**| A comma-separated string array. The fields to perform the search from. Defaults to all of them. | [optional]
 **exact_match** | **bool**| When enabled, only full exact matches against the given term are returned. It is &lt;b&gt;not&lt;/b&gt; case sensitive. | [optional]
 **person_id** | **int**| Will filter leads by the provided person ID. The upper limit of found leads associated with the person is 2000. | [optional]
 **organization_id** | **int**| Will filter leads by the provided organization ID. The upper limit of found leads associated with the organization is 2000. | [optional]
 **include_fields** | **string**| Supports including optional fields in the results which are not provided by default | [optional]
 **start** | **int**| Pagination start. Note that the pagination is based on main results and does not include related items when using &#x60;search_for_related_items&#x60; parameter. | [optional] [default to 0]
 **limit** | **int**| Items shown per page | [optional]

### Return type

[**\Pipedrive\versions\v1\Model\LeadSearchResponse**](../Model/LeadSearchResponse.md)

### Authorization

[api_key](../README.md#api_key), [oauth2](../README.md#oauth2)

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json`

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints)
[[Back to Model list]](../README.md#documentation-for-models)
[[Back to README]](../README.md)

## `updateLead()`

```php
updateLead($id, $update_lead_request): \Pipedrive\versions\v1\Model\GetLeadResponse
```

Update a lead

Updates one or more properties of a lead. Only properties included in the request will be updated. Send `null` to unset a property (applicable for example for `value`, `person_id` or `organization_id`). If a lead contains custom fields, the fields' values will be included in the response in the same format as with the `Deals` endpoints. If a custom field's value hasn't been set for the lead, it won't appear in the response. Please note that leads do not have a separate set of custom fields, instead they inherit the custom fields’ structure from deals. See an example given in the <a href=\"https://pipedrive.readme.io/docs/updating-custom-field-value\" target=\"_blank\" rel=\"noopener noreferrer\">updating custom fields’ values tutorial</a>.

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


$apiInstance = new Pipedrive\versions\v1\Api\LeadsApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$id = 'id_example'; // string | The ID of the lead
$update_lead_request = new \Pipedrive\versions\v1\Model\UpdateLeadRequest(); // \Pipedrive\versions\v1\Model\UpdateLeadRequest

try {
    $result = $apiInstance->updateLead($id, $update_lead_request);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling LeadsApi->updateLead: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **id** | **string**| The ID of the lead |
 **update_lead_request** | [**\Pipedrive\versions\v1\Model\UpdateLeadRequest**](../Model/UpdateLeadRequest.md)|  | [optional]

### Return type

[**\Pipedrive\versions\v1\Model\GetLeadResponse**](../Model/GetLeadResponse.md)

### Authorization

[api_key](../README.md#api_key), [oauth2](../README.md#oauth2)

### HTTP request headers

- **Content-Type**: `application/json`
- **Accept**: `application/json`

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints)
[[Back to Model list]](../README.md#documentation-for-models)
[[Back to README]](../README.md)
