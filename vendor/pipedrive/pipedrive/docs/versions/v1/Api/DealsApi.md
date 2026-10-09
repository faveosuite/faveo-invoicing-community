# Pipedrive\versions\v1\DealsApi

All URIs are relative to https://api.pipedrive.com/v1.

Method | HTTP request | Description
------------- | ------------- | -------------
[**addDealFollower()**](DealsApi.md#addDealFollower) | **POST** /deals/{id}/followers | Add a follower to a deal
[**addDealParticipant()**](DealsApi.md#addDealParticipant) | **POST** /deals/{id}/participants | Add a participant to a deal
[**deleteDealFollower()**](DealsApi.md#deleteDealFollower) | **DELETE** /deals/{id}/followers/{follower_id} | Delete a follower from a deal
[**deleteDealParticipant()**](DealsApi.md#deleteDealParticipant) | **DELETE** /deals/{id}/participants/{deal_participant_id} | Delete a participant from a deal
[**duplicateDeal()**](DealsApi.md#duplicateDeal) | **POST** /deals/{id}/duplicate | Duplicate deal
[**getArchivedDeals()**](DealsApi.md#getArchivedDeals) | **GET** /deals/archived | Get all archived deals
[**getArchivedDealsSummary()**](DealsApi.md#getArchivedDealsSummary) | **GET** /deals/summary/archived | Get archived deals summary
[**getArchivedDealsTimeline()**](DealsApi.md#getArchivedDealsTimeline) | **GET** /deals/timeline/archived | Get archived deals timeline
[**getDealChangelog()**](DealsApi.md#getDealChangelog) | **GET** /deals/{id}/changelog | List updates about deal field values
[**getDealFiles()**](DealsApi.md#getDealFiles) | **GET** /deals/{id}/files | List files attached to a deal
[**getDealFollowers()**](DealsApi.md#getDealFollowers) | **GET** /deals/{id}/followers | List followers of a deal
[**getDealMailMessages()**](DealsApi.md#getDealMailMessages) | **GET** /deals/{id}/mailMessages | List mail messages associated with a deal
[**getDealParticipants()**](DealsApi.md#getDealParticipants) | **GET** /deals/{id}/participants | List participants of a deal
[**getDealParticipantsChangelog()**](DealsApi.md#getDealParticipantsChangelog) | **GET** /deals/{id}/participantsChangelog | List updates about participants of a deal
[**getDealUpdates()**](DealsApi.md#getDealUpdates) | **GET** /deals/{id}/flow | List updates about a deal
[**getDealUsers()**](DealsApi.md#getDealUsers) | **GET** /deals/{id}/permittedUsers | List permitted users
[**getDealsSummary()**](DealsApi.md#getDealsSummary) | **GET** /deals/summary | Get deals summary
[**getDealsTimeline()**](DealsApi.md#getDealsTimeline) | **GET** /deals/timeline | Get deals timeline
[**mergeDeals()**](DealsApi.md#mergeDeals) | **PUT** /deals/{id}/merge | Merge two deals


## `addDealFollower()`

```php
addDealFollower($id, $add_deal_follower_request): \Pipedrive\versions\v1\Model\AddedDealFollower
```

Add a follower to a deal

Adds a follower to a deal.

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


$apiInstance = new Pipedrive\versions\v1\Api\DealsApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$id = 56; // int | The ID of the deal
$add_deal_follower_request = new \Pipedrive\versions\v1\Model\AddDealFollowerRequest(); // \Pipedrive\versions\v1\Model\AddDealFollowerRequest

try {
    $result = $apiInstance->addDealFollower($id, $add_deal_follower_request);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling DealsApi->addDealFollower: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **id** | **int**| The ID of the deal |
 **add_deal_follower_request** | [**\Pipedrive\versions\v1\Model\AddDealFollowerRequest**](../Model/AddDealFollowerRequest.md)|  | [optional]

### Return type

[**\Pipedrive\versions\v1\Model\AddedDealFollower**](../Model/AddedDealFollower.md)

### Authorization

[api_key](../README.md#api_key), [oauth2](../README.md#oauth2)

### HTTP request headers

- **Content-Type**: `application/json`
- **Accept**: `application/json`

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints)
[[Back to Model list]](../README.md#documentation-for-models)
[[Back to README]](../README.md)

## `addDealParticipant()`

```php
addDealParticipant($id, $add_deal_participant_request): \Pipedrive\versions\v1\Model\PostDealParticipants
```

Add a participant to a deal

Adds a participant to a deal.

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


$apiInstance = new Pipedrive\versions\v1\Api\DealsApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$id = 56; // int | The ID of the deal
$add_deal_participant_request = new \Pipedrive\versions\v1\Model\AddDealParticipantRequest(); // \Pipedrive\versions\v1\Model\AddDealParticipantRequest

try {
    $result = $apiInstance->addDealParticipant($id, $add_deal_participant_request);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling DealsApi->addDealParticipant: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **id** | **int**| The ID of the deal |
 **add_deal_participant_request** | [**\Pipedrive\versions\v1\Model\AddDealParticipantRequest**](../Model/AddDealParticipantRequest.md)|  | [optional]

### Return type

[**\Pipedrive\versions\v1\Model\PostDealParticipants**](../Model/PostDealParticipants.md)

### Authorization

[api_key](../README.md#api_key), [oauth2](../README.md#oauth2)

### HTTP request headers

- **Content-Type**: `application/json`
- **Accept**: `application/json`

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints)
[[Back to Model list]](../README.md#documentation-for-models)
[[Back to README]](../README.md)

## `deleteDealFollower()`

```php
deleteDealFollower($id, $follower_id): \Pipedrive\versions\v1\Model\DeleteDealFollower
```

Delete a follower from a deal

Deletes a follower from a deal.

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


$apiInstance = new Pipedrive\versions\v1\Api\DealsApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$id = 56; // int | The ID of the deal
$follower_id = 56; // int | The ID of the relationship between the follower and the deal

try {
    $result = $apiInstance->deleteDealFollower($id, $follower_id);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling DealsApi->deleteDealFollower: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **id** | **int**| The ID of the deal |
 **follower_id** | **int**| The ID of the relationship between the follower and the deal |

### Return type

[**\Pipedrive\versions\v1\Model\DeleteDealFollower**](../Model/DeleteDealFollower.md)

### Authorization

[api_key](../README.md#api_key), [oauth2](../README.md#oauth2)

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json`

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints)
[[Back to Model list]](../README.md#documentation-for-models)
[[Back to README]](../README.md)

## `deleteDealParticipant()`

```php
deleteDealParticipant($id, $deal_participant_id): \Pipedrive\versions\v1\Model\DeleteDealParticipant
```

Delete a participant from a deal

Deletes a participant from a deal.

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


$apiInstance = new Pipedrive\versions\v1\Api\DealsApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$id = 56; // int | The ID of the deal
$deal_participant_id = 56; // int | The ID of the participant of the deal

try {
    $result = $apiInstance->deleteDealParticipant($id, $deal_participant_id);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling DealsApi->deleteDealParticipant: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **id** | **int**| The ID of the deal |
 **deal_participant_id** | **int**| The ID of the participant of the deal |

### Return type

[**\Pipedrive\versions\v1\Model\DeleteDealParticipant**](../Model/DeleteDealParticipant.md)

### Authorization

[api_key](../README.md#api_key), [oauth2](../README.md#oauth2)

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json`

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints)
[[Back to Model list]](../README.md#documentation-for-models)
[[Back to README]](../README.md)

## `duplicateDeal()`

```php
duplicateDeal($id): \Pipedrive\versions\v1\Model\GetDuplicatedDeal
```

Duplicate deal

Duplicates a deal.

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


$apiInstance = new Pipedrive\versions\v1\Api\DealsApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$id = 56; // int | The ID of the deal

try {
    $result = $apiInstance->duplicateDeal($id);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling DealsApi->duplicateDeal: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **id** | **int**| The ID of the deal |

### Return type

[**\Pipedrive\versions\v1\Model\GetDuplicatedDeal**](../Model/GetDuplicatedDeal.md)

### Authorization

[api_key](../README.md#api_key), [oauth2](../README.md#oauth2)

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json`

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints)
[[Back to Model list]](../README.md#documentation-for-models)
[[Back to README]](../README.md)

## `getArchivedDeals()`

```php
getArchivedDeals($user_id, $filter_id, $person_id, $org_id, $product_id, $pipeline_id, $stage_id, $status, $start, $limit, $sort, $owned_by_you): \Pipedrive\versions\v1\Model\GetDeals
```

Get all archived deals

Returns all archived deals.

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


$apiInstance = new Pipedrive\versions\v1\Api\DealsApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$user_id = 56; // int | If supplied, only deals matching the given user will be returned. However, `filter_id` and `owned_by_you` takes precedence over `user_id` when supplied.
$filter_id = 56; // int | The ID of the filter to use
$person_id = 56; // int | If supplied, only deals linked to the specified person are returned. If filter_id is provided, this is ignored.
$org_id = 56; // int | If supplied, only deals linked to the specified organization are returned. If filter_id is provided, this is ignored.
$product_id = 56; // int | If supplied, only deals linked to the specified product are returned. If filter_id is provided, this is ignored.
$pipeline_id = 56; // int | If supplied, only deals in the specified pipeline are returned. If filter_id is provided, this is ignored.
$stage_id = 56; // int | If supplied, only deals in the specified stage are returned. If filter_id is provided, this is ignored.
$status = 'all_not_deleted'; // string | Only fetch deals with a specific status. If omitted, all not deleted deals are returned. If set to deleted, deals that have been deleted up to 30 days ago will be included.
$start = 0; // int | Pagination start
$limit = 56; // int | Items shown per page
$sort = 'sort_example'; // string | The field names and sorting mode separated by a comma (`field_name_1 ASC`, `field_name_2 DESC`). Only first-level field keys are supported (no nested keys).
$owned_by_you = new \Pipedrive\versions\v1\Model\\Pipedrive\versions\v1\Model\NumberBoolean(); // \Pipedrive\versions\v1\Model\NumberBoolean | When supplied, only deals owned by you are returned. However, `filter_id` takes precedence over `owned_by_you` when both are supplied.

try {
    $result = $apiInstance->getArchivedDeals($user_id, $filter_id, $person_id, $org_id, $product_id, $pipeline_id, $stage_id, $status, $start, $limit, $sort, $owned_by_you);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling DealsApi->getArchivedDeals: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **user_id** | **int**| If supplied, only deals matching the given user will be returned. However, &#x60;filter_id&#x60; and &#x60;owned_by_you&#x60; takes precedence over &#x60;user_id&#x60; when supplied. | [optional]
 **filter_id** | **int**| The ID of the filter to use | [optional]
 **person_id** | **int**| If supplied, only deals linked to the specified person are returned. If filter_id is provided, this is ignored. | [optional]
 **org_id** | **int**| If supplied, only deals linked to the specified organization are returned. If filter_id is provided, this is ignored. | [optional]
 **product_id** | **int**| If supplied, only deals linked to the specified product are returned. If filter_id is provided, this is ignored. | [optional]
 **pipeline_id** | **int**| If supplied, only deals in the specified pipeline are returned. If filter_id is provided, this is ignored. | [optional]
 **stage_id** | **int**| If supplied, only deals in the specified stage are returned. If filter_id is provided, this is ignored. | [optional]
 **status** | **string**| Only fetch deals with a specific status. If omitted, all not deleted deals are returned. If set to deleted, deals that have been deleted up to 30 days ago will be included. | [optional] [default to &#39;all_not_deleted&#39;]
 **start** | **int**| Pagination start | [optional] [default to 0]
 **limit** | **int**| Items shown per page | [optional]
 **sort** | **string**| The field names and sorting mode separated by a comma (&#x60;field_name_1 ASC&#x60;, &#x60;field_name_2 DESC&#x60;). Only first-level field keys are supported (no nested keys). | [optional]
 **owned_by_you** | [**\Pipedrive\versions\v1\Model\NumberBoolean**](../Model/.md)| When supplied, only deals owned by you are returned. However, &#x60;filter_id&#x60; takes precedence over &#x60;owned_by_you&#x60; when both are supplied. | [optional]

### Return type

[**\Pipedrive\versions\v1\Model\GetDeals**](../Model/GetDeals.md)

### Authorization

[api_key](../README.md#api_key), [oauth2](../README.md#oauth2)

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json`

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints)
[[Back to Model list]](../README.md#documentation-for-models)
[[Back to README]](../README.md)

## `getArchivedDealsSummary()`

```php
getArchivedDealsSummary($status, $filter_id, $user_id, $pipeline_id, $stage_id): \Pipedrive\versions\v1\Model\GetDealsSummary
```

Get archived deals summary

Returns a summary of all archived deals.

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


$apiInstance = new Pipedrive\versions\v1\Api\DealsApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$status = 'status_example'; // string | Only fetch deals with a specific status. open = Open, won = Won, lost = Lost.
$filter_id = 56; // int | <code>user_id</code> will not be considered. Only deals matching the given filter will be returned.
$user_id = 56; // int | Only deals matching the given user will be returned. `user_id` will not be considered if you use `filter_id`.
$pipeline_id = 56; // int | Only deals within the given pipeline will be returned
$stage_id = 56; // int | Only deals within the given stage will be returned

try {
    $result = $apiInstance->getArchivedDealsSummary($status, $filter_id, $user_id, $pipeline_id, $stage_id);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling DealsApi->getArchivedDealsSummary: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **status** | **string**| Only fetch deals with a specific status. open &#x3D; Open, won &#x3D; Won, lost &#x3D; Lost. | [optional]
 **filter_id** | **int**| &lt;code&gt;user_id&lt;/code&gt; will not be considered. Only deals matching the given filter will be returned. | [optional]
 **user_id** | **int**| Only deals matching the given user will be returned. &#x60;user_id&#x60; will not be considered if you use &#x60;filter_id&#x60;. | [optional]
 **pipeline_id** | **int**| Only deals within the given pipeline will be returned | [optional]
 **stage_id** | **int**| Only deals within the given stage will be returned | [optional]

### Return type

[**\Pipedrive\versions\v1\Model\GetDealsSummary**](../Model/GetDealsSummary.md)

### Authorization

[api_key](../README.md#api_key), [oauth2](../README.md#oauth2)

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json`

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints)
[[Back to Model list]](../README.md#documentation-for-models)
[[Back to README]](../README.md)

## `getArchivedDealsTimeline()`

```php
getArchivedDealsTimeline($start_date, $interval, $amount, $field_key, $user_id, $pipeline_id, $filter_id, $exclude_deals, $totals_convert_currency): \Pipedrive\versions\v1\Model\GetDealsTimeline
```

Get archived deals timeline

Returns archived open and won deals, grouped by a defined interval of time set in a date-type dealField (`field_key`) — e.g. when month is the chosen interval, and 3 months are asked starting from January 1st, 2012, deals are returned grouped into 3 groups — January, February and March — based on the value of the given `field_key`.

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


$apiInstance = new Pipedrive\versions\v1\Api\DealsApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$start_date = new \DateTime("2013-10-20T19:20:30+01:00"); // \DateTime | The date when the first interval starts. Format: YYYY-MM-DD.
$interval = 'interval_example'; // string | The type of the interval<table><tr><th>Value</th><th>Description</th></tr><tr><td>`day`</td><td>Day</td></tr><tr><td>`week`</td><td>A full week (7 days) starting from `start_date`</td></tr><tr><td>`month`</td><td>A full month (depending on the number of days in given month) starting from `start_date`</td></tr><tr><td>`quarter`</td><td>A full quarter (3 months) starting from `start_date`</td></tr></table>
$amount = 56; // int | The number of given intervals, starting from `start_date`, to fetch. E.g. 3 (months).
$field_key = 'field_key_example'; // string | The date field key which deals will be retrieved from
$user_id = 56; // int | If supplied, only deals matching the given user will be returned
$pipeline_id = 56; // int | If supplied, only deals matching the given pipeline will be returned
$filter_id = 56; // int | If supplied, only deals matching the given filter will be returned
$exclude_deals = new \Pipedrive\versions\v1\Model\\Pipedrive\versions\v1\Model\NumberBoolean(); // \Pipedrive\versions\v1\Model\NumberBoolean | Whether to exclude deals list (1) or not (0). Note that when deals are excluded, the timeline summary (counts and values) is still returned.
$totals_convert_currency = 'totals_convert_currency_example'; // string | The 3-letter currency code of any of the supported currencies. When supplied, `totals_converted` is returned per each interval which contains the currency-converted total amounts in the given currency. You may also set this parameter to `default_currency` in which case the user's default currency is used.

try {
    $result = $apiInstance->getArchivedDealsTimeline($start_date, $interval, $amount, $field_key, $user_id, $pipeline_id, $filter_id, $exclude_deals, $totals_convert_currency);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling DealsApi->getArchivedDealsTimeline: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **start_date** | **\DateTime**| The date when the first interval starts. Format: YYYY-MM-DD. |
 **interval** | **string**| The type of the interval&lt;table&gt;&lt;tr&gt;&lt;th&gt;Value&lt;/th&gt;&lt;th&gt;Description&lt;/th&gt;&lt;/tr&gt;&lt;tr&gt;&lt;td&gt;&#x60;day&#x60;&lt;/td&gt;&lt;td&gt;Day&lt;/td&gt;&lt;/tr&gt;&lt;tr&gt;&lt;td&gt;&#x60;week&#x60;&lt;/td&gt;&lt;td&gt;A full week (7 days) starting from &#x60;start_date&#x60;&lt;/td&gt;&lt;/tr&gt;&lt;tr&gt;&lt;td&gt;&#x60;month&#x60;&lt;/td&gt;&lt;td&gt;A full month (depending on the number of days in given month) starting from &#x60;start_date&#x60;&lt;/td&gt;&lt;/tr&gt;&lt;tr&gt;&lt;td&gt;&#x60;quarter&#x60;&lt;/td&gt;&lt;td&gt;A full quarter (3 months) starting from &#x60;start_date&#x60;&lt;/td&gt;&lt;/tr&gt;&lt;/table&gt; |
 **amount** | **int**| The number of given intervals, starting from &#x60;start_date&#x60;, to fetch. E.g. 3 (months). |
 **field_key** | **string**| The date field key which deals will be retrieved from |
 **user_id** | **int**| If supplied, only deals matching the given user will be returned | [optional]
 **pipeline_id** | **int**| If supplied, only deals matching the given pipeline will be returned | [optional]
 **filter_id** | **int**| If supplied, only deals matching the given filter will be returned | [optional]
 **exclude_deals** | [**\Pipedrive\versions\v1\Model\NumberBoolean**](../Model/.md)| Whether to exclude deals list (1) or not (0). Note that when deals are excluded, the timeline summary (counts and values) is still returned. | [optional]
 **totals_convert_currency** | **string**| The 3-letter currency code of any of the supported currencies. When supplied, &#x60;totals_converted&#x60; is returned per each interval which contains the currency-converted total amounts in the given currency. You may also set this parameter to &#x60;default_currency&#x60; in which case the user&#39;s default currency is used. | [optional]

### Return type

[**\Pipedrive\versions\v1\Model\GetDealsTimeline**](../Model/GetDealsTimeline.md)

### Authorization

[api_key](../README.md#api_key), [oauth2](../README.md#oauth2)

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json`

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints)
[[Back to Model list]](../README.md#documentation-for-models)
[[Back to README]](../README.md)

## `getDealChangelog()`

```php
getDealChangelog($id, $cursor, $limit): \Pipedrive\versions\v1\Model\ChangelogResponse
```

List updates about deal field values

Lists updates about field values of a deal.

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


$apiInstance = new Pipedrive\versions\v1\Api\DealsApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$id = 56; // int | The ID of the deal
$cursor = 'cursor_example'; // string | For pagination, the marker (an opaque string value) representing the first item on the next page
$limit = 56; // int | Items shown per page

try {
    $result = $apiInstance->getDealChangelog($id, $cursor, $limit);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling DealsApi->getDealChangelog: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **id** | **int**| The ID of the deal |
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

## `getDealFiles()`

```php
getDealFiles($id, $start, $limit, $sort): \Pipedrive\versions\v1\Model\ListFilesResponse
```

List files attached to a deal

Lists files associated with a deal.

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


$apiInstance = new Pipedrive\versions\v1\Api\DealsApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$id = 56; // int | The ID of the deal
$start = 0; // int | Pagination start
$limit = 56; // int | Items shown per page. Please note that a maximum value of 100 is allowed.
$sort = 'sort_example'; // string | Supported fields: `id`, `update_time`

try {
    $result = $apiInstance->getDealFiles($id, $start, $limit, $sort);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling DealsApi->getDealFiles: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **id** | **int**| The ID of the deal |
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

## `getDealFollowers()`

```php
getDealFollowers($id): \Pipedrive\versions\v1\Model\ListFollowersResponse
```

List followers of a deal

Lists the followers of a deal.

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


$apiInstance = new Pipedrive\versions\v1\Api\DealsApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$id = 56; // int | The ID of the deal

try {
    $result = $apiInstance->getDealFollowers($id);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling DealsApi->getDealFollowers: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **id** | **int**| The ID of the deal |

### Return type

[**\Pipedrive\versions\v1\Model\ListFollowersResponse**](../Model/ListFollowersResponse.md)

### Authorization

[api_key](../README.md#api_key), [oauth2](../README.md#oauth2)

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json`

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints)
[[Back to Model list]](../README.md#documentation-for-models)
[[Back to README]](../README.md)

## `getDealMailMessages()`

```php
getDealMailMessages($id, $start, $limit): \Pipedrive\versions\v1\Model\ListMailMessagesResponse
```

List mail messages associated with a deal

Lists mail messages associated with a deal.

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


$apiInstance = new Pipedrive\versions\v1\Api\DealsApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$id = 56; // int | The ID of the deal
$start = 0; // int | Pagination start
$limit = 56; // int | Items shown per page

try {
    $result = $apiInstance->getDealMailMessages($id, $start, $limit);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling DealsApi->getDealMailMessages: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **id** | **int**| The ID of the deal |
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

## `getDealParticipants()`

```php
getDealParticipants($id, $start, $limit): \Pipedrive\versions\v1\Model\DealParticipants
```

List participants of a deal

Lists the participants associated with a deal.<br>If a company uses the [Campaigns product](https://pipedrive.readme.io/docs/campaigns-in-pipedrive-api), then this endpoint will also return the `data.marketing_status` field.

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


$apiInstance = new Pipedrive\versions\v1\Api\DealsApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$id = 56; // int | The ID of the deal
$start = 0; // int | Pagination start
$limit = 56; // int | Items shown per page

try {
    $result = $apiInstance->getDealParticipants($id, $start, $limit);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling DealsApi->getDealParticipants: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **id** | **int**| The ID of the deal |
 **start** | **int**| Pagination start | [optional] [default to 0]
 **limit** | **int**| Items shown per page | [optional]

### Return type

[**\Pipedrive\versions\v1\Model\DealParticipants**](../Model/DealParticipants.md)

### Authorization

[api_key](../README.md#api_key), [oauth2](../README.md#oauth2)

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json`

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints)
[[Back to Model list]](../README.md#documentation-for-models)
[[Back to README]](../README.md)

## `getDealParticipantsChangelog()`

```php
getDealParticipantsChangelog($id, $limit, $cursor): \Pipedrive\versions\v1\Model\ParticipantsChangelog
```

List updates about participants of a deal

List updates about participants of a deal. This is a cursor-paginated endpoint. For more information, please refer to our documentation on <a href=\"https://pipedrive.readme.io/docs/core-api-concepts-pagination\" target=\"_blank\" rel=\"noopener noreferrer\">pagination</a>.

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


$apiInstance = new Pipedrive\versions\v1\Api\DealsApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$id = 56; // int | The ID of the deal
$limit = 56; // int | Items shown per page
$cursor = 'cursor_example'; // string | For pagination, the marker (an opaque string value) representing the first item on the next page

try {
    $result = $apiInstance->getDealParticipantsChangelog($id, $limit, $cursor);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling DealsApi->getDealParticipantsChangelog: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **id** | **int**| The ID of the deal |
 **limit** | **int**| Items shown per page | [optional]
 **cursor** | **string**| For pagination, the marker (an opaque string value) representing the first item on the next page | [optional]

### Return type

[**\Pipedrive\versions\v1\Model\ParticipantsChangelog**](../Model/ParticipantsChangelog.md)

### Authorization

[api_key](../README.md#api_key), [oauth2](../README.md#oauth2)

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json`

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints)
[[Back to Model list]](../README.md#documentation-for-models)
[[Back to README]](../README.md)

## `getDealUpdates()`

```php
getDealUpdates($id, $start, $limit, $all_changes, $items): \Pipedrive\versions\v1\Model\DealFlowResponse
```

List updates about a deal

Lists updates about a deal.

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


$apiInstance = new Pipedrive\versions\v1\Api\DealsApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$id = 56; // int | The ID of the deal
$start = 0; // int | Pagination start
$limit = 56; // int | Items shown per page
$all_changes = 'all_changes_example'; // string | Whether to show custom field updates or not. 1 = Include custom field changes. If omitted returns changes without custom field updates.
$items = 'items_example'; // string | A comma-separated string for filtering out item specific updates. (Possible values - call, activity, plannedActivity, change, note, deal, file, dealChange, personChange, organizationChange, follower, dealFollower, personFollower, organizationFollower, participant, comment, mailMessage, mailMessageWithAttachment, invoice, document, marketing_campaign_stat, marketing_status_change).

try {
    $result = $apiInstance->getDealUpdates($id, $start, $limit, $all_changes, $items);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling DealsApi->getDealUpdates: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **id** | **int**| The ID of the deal |
 **start** | **int**| Pagination start | [optional] [default to 0]
 **limit** | **int**| Items shown per page | [optional]
 **all_changes** | **string**| Whether to show custom field updates or not. 1 &#x3D; Include custom field changes. If omitted returns changes without custom field updates. | [optional]
 **items** | **string**| A comma-separated string for filtering out item specific updates. (Possible values - call, activity, plannedActivity, change, note, deal, file, dealChange, personChange, organizationChange, follower, dealFollower, personFollower, organizationFollower, participant, comment, mailMessage, mailMessageWithAttachment, invoice, document, marketing_campaign_stat, marketing_status_change). | [optional]

### Return type

[**\Pipedrive\versions\v1\Model\DealFlowResponse**](../Model/DealFlowResponse.md)

### Authorization

[api_key](../README.md#api_key), [oauth2](../README.md#oauth2)

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json`

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints)
[[Back to Model list]](../README.md#documentation-for-models)
[[Back to README]](../README.md)

## `getDealUsers()`

```php
getDealUsers($id): \Pipedrive\versions\v1\Model\ListPermittedUsersResponse
```

List permitted users

Lists the users permitted to access a deal.

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


$apiInstance = new Pipedrive\versions\v1\Api\DealsApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$id = 56; // int | The ID of the deal

try {
    $result = $apiInstance->getDealUsers($id);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling DealsApi->getDealUsers: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **id** | **int**| The ID of the deal |

### Return type

[**\Pipedrive\versions\v1\Model\ListPermittedUsersResponse**](../Model/ListPermittedUsersResponse.md)

### Authorization

[api_key](../README.md#api_key), [oauth2](../README.md#oauth2)

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json`

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints)
[[Back to Model list]](../README.md#documentation-for-models)
[[Back to README]](../README.md)

## `getDealsSummary()`

```php
getDealsSummary($status, $filter_id, $user_id, $pipeline_id, $stage_id): \Pipedrive\versions\v1\Model\GetDealsSummary
```

Get deals summary

Returns a summary of all not archived deals.

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


$apiInstance = new Pipedrive\versions\v1\Api\DealsApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$status = 'status_example'; // string | Only fetch deals with a specific status. open = Open, won = Won, lost = Lost.
$filter_id = 56; // int | <code>user_id</code> will not be considered. Only deals matching the given filter will be returned.
$user_id = 56; // int | Only deals matching the given user will be returned. `user_id` will not be considered if you use `filter_id`.
$pipeline_id = 56; // int | Only deals within the given pipeline will be returned
$stage_id = 56; // int | Only deals within the given stage will be returned

try {
    $result = $apiInstance->getDealsSummary($status, $filter_id, $user_id, $pipeline_id, $stage_id);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling DealsApi->getDealsSummary: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **status** | **string**| Only fetch deals with a specific status. open &#x3D; Open, won &#x3D; Won, lost &#x3D; Lost. | [optional]
 **filter_id** | **int**| &lt;code&gt;user_id&lt;/code&gt; will not be considered. Only deals matching the given filter will be returned. | [optional]
 **user_id** | **int**| Only deals matching the given user will be returned. &#x60;user_id&#x60; will not be considered if you use &#x60;filter_id&#x60;. | [optional]
 **pipeline_id** | **int**| Only deals within the given pipeline will be returned | [optional]
 **stage_id** | **int**| Only deals within the given stage will be returned | [optional]

### Return type

[**\Pipedrive\versions\v1\Model\GetDealsSummary**](../Model/GetDealsSummary.md)

### Authorization

[api_key](../README.md#api_key), [oauth2](../README.md#oauth2)

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json`

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints)
[[Back to Model list]](../README.md#documentation-for-models)
[[Back to README]](../README.md)

## `getDealsTimeline()`

```php
getDealsTimeline($start_date, $interval, $amount, $field_key, $user_id, $pipeline_id, $filter_id, $exclude_deals, $totals_convert_currency): \Pipedrive\versions\v1\Model\GetDealsTimeline
```

Get deals timeline

Returns not archived open and won deals, grouped by a defined interval of time set in a date-type dealField (`field_key`) — e.g. when month is the chosen interval, and 3 months are asked starting from January 1st, 2012, deals are returned grouped into 3 groups — January, February and March — based on the value of the given `field_key`.

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


$apiInstance = new Pipedrive\versions\v1\Api\DealsApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$start_date = new \DateTime("2013-10-20T19:20:30+01:00"); // \DateTime | The date when the first interval starts. Format: YYYY-MM-DD.
$interval = 'interval_example'; // string | The type of the interval<table><tr><th>Value</th><th>Description</th></tr><tr><td>`day`</td><td>Day</td></tr><tr><td>`week`</td><td>A full week (7 days) starting from `start_date`</td></tr><tr><td>`month`</td><td>A full month (depending on the number of days in given month) starting from `start_date`</td></tr><tr><td>`quarter`</td><td>A full quarter (3 months) starting from `start_date`</td></tr></table>
$amount = 56; // int | The number of given intervals, starting from `start_date`, to fetch. E.g. 3 (months).
$field_key = 'field_key_example'; // string | The date field key which deals will be retrieved from
$user_id = 56; // int | If supplied, only deals matching the given user will be returned
$pipeline_id = 56; // int | If supplied, only deals matching the given pipeline will be returned
$filter_id = 56; // int | If supplied, only deals matching the given filter will be returned
$exclude_deals = new \Pipedrive\versions\v1\Model\\Pipedrive\versions\v1\Model\NumberBoolean(); // \Pipedrive\versions\v1\Model\NumberBoolean | Whether to exclude deals list (1) or not (0). Note that when deals are excluded, the timeline summary (counts and values) is still returned.
$totals_convert_currency = 'totals_convert_currency_example'; // string | The 3-letter currency code of any of the supported currencies. When supplied, `totals_converted` is returned per each interval which contains the currency-converted total amounts in the given currency. You may also set this parameter to `default_currency` in which case the user's default currency is used.

try {
    $result = $apiInstance->getDealsTimeline($start_date, $interval, $amount, $field_key, $user_id, $pipeline_id, $filter_id, $exclude_deals, $totals_convert_currency);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling DealsApi->getDealsTimeline: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **start_date** | **\DateTime**| The date when the first interval starts. Format: YYYY-MM-DD. |
 **interval** | **string**| The type of the interval&lt;table&gt;&lt;tr&gt;&lt;th&gt;Value&lt;/th&gt;&lt;th&gt;Description&lt;/th&gt;&lt;/tr&gt;&lt;tr&gt;&lt;td&gt;&#x60;day&#x60;&lt;/td&gt;&lt;td&gt;Day&lt;/td&gt;&lt;/tr&gt;&lt;tr&gt;&lt;td&gt;&#x60;week&#x60;&lt;/td&gt;&lt;td&gt;A full week (7 days) starting from &#x60;start_date&#x60;&lt;/td&gt;&lt;/tr&gt;&lt;tr&gt;&lt;td&gt;&#x60;month&#x60;&lt;/td&gt;&lt;td&gt;A full month (depending on the number of days in given month) starting from &#x60;start_date&#x60;&lt;/td&gt;&lt;/tr&gt;&lt;tr&gt;&lt;td&gt;&#x60;quarter&#x60;&lt;/td&gt;&lt;td&gt;A full quarter (3 months) starting from &#x60;start_date&#x60;&lt;/td&gt;&lt;/tr&gt;&lt;/table&gt; |
 **amount** | **int**| The number of given intervals, starting from &#x60;start_date&#x60;, to fetch. E.g. 3 (months). |
 **field_key** | **string**| The date field key which deals will be retrieved from |
 **user_id** | **int**| If supplied, only deals matching the given user will be returned | [optional]
 **pipeline_id** | **int**| If supplied, only deals matching the given pipeline will be returned | [optional]
 **filter_id** | **int**| If supplied, only deals matching the given filter will be returned | [optional]
 **exclude_deals** | [**\Pipedrive\versions\v1\Model\NumberBoolean**](../Model/.md)| Whether to exclude deals list (1) or not (0). Note that when deals are excluded, the timeline summary (counts and values) is still returned. | [optional]
 **totals_convert_currency** | **string**| The 3-letter currency code of any of the supported currencies. When supplied, &#x60;totals_converted&#x60; is returned per each interval which contains the currency-converted total amounts in the given currency. You may also set this parameter to &#x60;default_currency&#x60; in which case the user&#39;s default currency is used. | [optional]

### Return type

[**\Pipedrive\versions\v1\Model\GetDealsTimeline**](../Model/GetDealsTimeline.md)

### Authorization

[api_key](../README.md#api_key), [oauth2](../README.md#oauth2)

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json`

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints)
[[Back to Model list]](../README.md#documentation-for-models)
[[Back to README]](../README.md)

## `mergeDeals()`

```php
mergeDeals($id, $merge_deals_request): \Pipedrive\versions\v1\Model\GetMergedDeal
```

Merge two deals

Merges a deal with another deal. For more information, see the tutorial for <a href=\"https://pipedrive.readme.io/docs/merging-two-deals\" target=\"_blank\" rel=\"noopener noreferrer\">merging two deals</a>.

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


$apiInstance = new Pipedrive\versions\v1\Api\DealsApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$id = 56; // int | The ID of the deal
$merge_deals_request = new \Pipedrive\versions\v1\Model\MergeDealsRequest(); // \Pipedrive\versions\v1\Model\MergeDealsRequest

try {
    $result = $apiInstance->mergeDeals($id, $merge_deals_request);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling DealsApi->mergeDeals: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **id** | **int**| The ID of the deal |
 **merge_deals_request** | [**\Pipedrive\versions\v1\Model\MergeDealsRequest**](../Model/MergeDealsRequest.md)|  | [optional]

### Return type

[**\Pipedrive\versions\v1\Model\GetMergedDeal**](../Model/GetMergedDeal.md)

### Authorization

[api_key](../README.md#api_key), [oauth2](../README.md#oauth2)

### HTTP request headers

- **Content-Type**: `application/json`
- **Accept**: `application/json`

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints)
[[Back to Model list]](../README.md#documentation-for-models)
[[Back to README]](../README.md)
