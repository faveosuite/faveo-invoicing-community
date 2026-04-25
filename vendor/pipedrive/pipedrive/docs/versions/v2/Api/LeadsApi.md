# Pipedrive\versions\v2\LeadsApi

All URIs are relative to https://api.pipedrive.com/api/v2.

Method | HTTP request | Description
------------- | ------------- | -------------
[**convertLeadToDeal()**](LeadsApi.md#convertLeadToDeal) | **POST** /leads/{id}/convert/deal | Convert a lead to a deal
[**getLeadConversionStatus()**](LeadsApi.md#getLeadConversionStatus) | **GET** /leads/{id}/convert/status/{conversion_id} | Get Lead conversion status
[**searchLeads()**](LeadsApi.md#searchLeads) | **GET** /leads/search | Search leads


## `convertLeadToDeal()`

```php
convertLeadToDeal($id, $inline_object): \Pipedrive\versions\v2\Model\AddConvertLeadToDealResponse
```

Convert a lead to a deal

Initiates a conversion of a lead to a deal. The return value is an ID of a job that was assigned to perform the conversion. Related entities (notes, files, emails, activities, ...) are transferred during the process to the target entity. If the conversion is successful, the lead is marked as deleted. To retrieve the created entity ID and the result of the conversion, call the <a href=\"https://developers.pipedrive.com/docs/api/v1/Leads#getLeadConversionStatus\">/api/v2/leads/{lead_id}/convert/status/{conversion_id}</a> endpoint.

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


$apiInstance = new Pipedrive\versions\v2\Api\LeadsApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$id = 'id_example'; // string | The ID of the lead to convert
$inline_object = new \Pipedrive\versions\v2\Model\InlineObject(); // \Pipedrive\versions\v2\Model\InlineObject

try {
    $result = $apiInstance->convertLeadToDeal($id, $inline_object);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling LeadsApi->convertLeadToDeal: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **id** | **string**| The ID of the lead to convert |
 **inline_object** | [**\Pipedrive\versions\v2\Model\InlineObject**](../Model/InlineObject.md)|  | [optional]

### Return type

[**\Pipedrive\versions\v2\Model\AddConvertLeadToDealResponse**](../Model/AddConvertLeadToDealResponse.md)

### Authorization

[api_key](../README.md#api_key), [oauth2](../README.md#oauth2)

### HTTP request headers

- **Content-Type**: `application/json`
- **Accept**: `application/json`

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints)
[[Back to Model list]](../README.md#documentation-for-models)
[[Back to README]](../README.md)

## `getLeadConversionStatus()`

```php
getLeadConversionStatus($id, $conversion_id)
```

Get Lead conversion status

Returns data about the conversion. Status is always present and its value (not_started, running, completed, failed, rejected) represents the current state of the conversion. Deal ID is only present if the conversion was successfully finished. This data is only temporary and removed after a few days.

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


$apiInstance = new Pipedrive\versions\v2\Api\LeadsApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$id = 'id_example'; // string | The ID of a lead
$conversion_id = 'conversion_id_example'; // string | The ID of the conversion

try {
    $apiInstance->getLeadConversionStatus($id, $conversion_id);
} catch (Exception $e) {
    echo 'Exception when calling LeadsApi->getLeadConversionStatus: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **id** | **string**| The ID of a lead |
 **conversion_id** | **string**| The ID of the conversion |

### Return type

void (empty response body)

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
searchLeads($term, $fields, $exact_match, $person_id, $organization_id, $include_fields, $limit, $cursor): \Pipedrive\versions\v2\Model\LeadSearchResponse
```

Search leads

Searches all leads by title, notes and/or custom fields. This endpoint is a wrapper of <a href=\"https://developers.pipedrive.com/docs/api/v1/ItemSearch#searchItem\">/v1/itemSearch</a> with a narrower OAuth scope. Found leads can be filtered by the person ID and the organization ID.

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


$apiInstance = new Pipedrive\versions\v2\Api\LeadsApi(
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
$limit = 100; // int | For pagination, the limit of entries to be returned. If not provided, 100 items will be returned. Please note that a maximum value of 500 is allowed.
$cursor = 'cursor_example'; // string | For pagination, the marker (an opaque string value) representing the first item on the next page

try {
    $result = $apiInstance->searchLeads($term, $fields, $exact_match, $person_id, $organization_id, $include_fields, $limit, $cursor);
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
 **limit** | **int**| For pagination, the limit of entries to be returned. If not provided, 100 items will be returned. Please note that a maximum value of 500 is allowed. | [optional]
 **cursor** | **string**| For pagination, the marker (an opaque string value) representing the first item on the next page | [optional]

### Return type

[**\Pipedrive\versions\v2\Model\LeadSearchResponse**](../Model/LeadSearchResponse.md)

### Authorization

[api_key](../README.md#api_key), [oauth2](../README.md#oauth2)

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json`

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints)
[[Back to Model list]](../README.md#documentation-for-models)
[[Back to README]](../README.md)
