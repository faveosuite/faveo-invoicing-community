# Pipedrive\versions\v1\OrganizationRelationshipsApi

All URIs are relative to https://api.pipedrive.com/v1.

Method | HTTP request | Description
------------- | ------------- | -------------
[**addOrganizationRelationship()**](OrganizationRelationshipsApi.md#addOrganizationRelationship) | **POST** /organizationRelationships | Create an organization relationship
[**deleteOrganizationRelationship()**](OrganizationRelationshipsApi.md#deleteOrganizationRelationship) | **DELETE** /organizationRelationships/{id} | Delete an organization relationship
[**getOrganizationRelationship()**](OrganizationRelationshipsApi.md#getOrganizationRelationship) | **GET** /organizationRelationships/{id} | Get one organization relationship
[**getOrganizationRelationships()**](OrganizationRelationshipsApi.md#getOrganizationRelationships) | **GET** /organizationRelationships | Get all relationships for organization
[**updateOrganizationRelationship()**](OrganizationRelationshipsApi.md#updateOrganizationRelationship) | **PUT** /organizationRelationships/{id} | Update an organization relationship


## `addOrganizationRelationship()`

```php
addOrganizationRelationship($add_organization_relationship_request): \Pipedrive\versions\v1\Model\OrganizationRelationshipPostResponse
```

Create an organization relationship

Creates and returns an organization relationship.

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


$apiInstance = new Pipedrive\versions\v1\Api\OrganizationRelationshipsApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$add_organization_relationship_request = new \Pipedrive\versions\v1\Model\AddOrganizationRelationshipRequest(); // \Pipedrive\versions\v1\Model\AddOrganizationRelationshipRequest

try {
    $result = $apiInstance->addOrganizationRelationship($add_organization_relationship_request);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling OrganizationRelationshipsApi->addOrganizationRelationship: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **add_organization_relationship_request** | [**\Pipedrive\versions\v1\Model\AddOrganizationRelationshipRequest**](../Model/AddOrganizationRelationshipRequest.md)|  | [optional]

### Return type

[**\Pipedrive\versions\v1\Model\OrganizationRelationshipPostResponse**](../Model/OrganizationRelationshipPostResponse.md)

### Authorization

[api_key](../README.md#api_key), [oauth2](../README.md#oauth2)

### HTTP request headers

- **Content-Type**: `application/json`
- **Accept**: `application/json`

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints)
[[Back to Model list]](../README.md#documentation-for-models)
[[Back to README]](../README.md)

## `deleteOrganizationRelationship()`

```php
deleteOrganizationRelationship($id): \Pipedrive\versions\v1\Model\OrganizationRelationshipDeleteResponse
```

Delete an organization relationship

Deletes an organization relationship and returns the deleted ID.

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


$apiInstance = new Pipedrive\versions\v1\Api\OrganizationRelationshipsApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$id = 56; // int | The ID of the organization relationship

try {
    $result = $apiInstance->deleteOrganizationRelationship($id);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling OrganizationRelationshipsApi->deleteOrganizationRelationship: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **id** | **int**| The ID of the organization relationship |

### Return type

[**\Pipedrive\versions\v1\Model\OrganizationRelationshipDeleteResponse**](../Model/OrganizationRelationshipDeleteResponse.md)

### Authorization

[api_key](../README.md#api_key), [oauth2](../README.md#oauth2)

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json`

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints)
[[Back to Model list]](../README.md#documentation-for-models)
[[Back to README]](../README.md)

## `getOrganizationRelationship()`

```php
getOrganizationRelationship($id, $org_id): \Pipedrive\versions\v1\Model\OrganizationRelationshipGetResponse
```

Get one organization relationship

Finds and returns an organization relationship from its ID.

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


$apiInstance = new Pipedrive\versions\v1\Api\OrganizationRelationshipsApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$id = 56; // int | The ID of the organization relationship
$org_id = 56; // int | The ID of the base organization for the returned calculated values

try {
    $result = $apiInstance->getOrganizationRelationship($id, $org_id);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling OrganizationRelationshipsApi->getOrganizationRelationship: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **id** | **int**| The ID of the organization relationship |
 **org_id** | **int**| The ID of the base organization for the returned calculated values | [optional]

### Return type

[**\Pipedrive\versions\v1\Model\OrganizationRelationshipGetResponse**](../Model/OrganizationRelationshipGetResponse.md)

### Authorization

[api_key](../README.md#api_key), [oauth2](../README.md#oauth2)

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json`

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints)
[[Back to Model list]](../README.md#documentation-for-models)
[[Back to README]](../README.md)

## `getOrganizationRelationships()`

```php
getOrganizationRelationships($org_id): \Pipedrive\versions\v1\Model\AllOrganizationRelationshipsGetResponse
```

Get all relationships for organization

Gets all of the relationships for a supplied organization ID.

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


$apiInstance = new Pipedrive\versions\v1\Api\OrganizationRelationshipsApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$org_id = 56; // int | The ID of the organization to get relationships for

try {
    $result = $apiInstance->getOrganizationRelationships($org_id);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling OrganizationRelationshipsApi->getOrganizationRelationships: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **org_id** | **int**| The ID of the organization to get relationships for |

### Return type

[**\Pipedrive\versions\v1\Model\AllOrganizationRelationshipsGetResponse**](../Model/AllOrganizationRelationshipsGetResponse.md)

### Authorization

[api_key](../README.md#api_key), [oauth2](../README.md#oauth2)

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json`

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints)
[[Back to Model list]](../README.md#documentation-for-models)
[[Back to README]](../README.md)

## `updateOrganizationRelationship()`

```php
updateOrganizationRelationship($id, $organization_relationship): \Pipedrive\versions\v1\Model\OrganizationRelationshipUpdateResponse
```

Update an organization relationship

Updates and returns an organization relationship.

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


$apiInstance = new Pipedrive\versions\v1\Api\OrganizationRelationshipsApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$id = 56; // int | The ID of the organization relationship
$organization_relationship = new \Pipedrive\versions\v1\Model\OrganizationRelationship(); // \Pipedrive\versions\v1\Model\OrganizationRelationship

try {
    $result = $apiInstance->updateOrganizationRelationship($id, $organization_relationship);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling OrganizationRelationshipsApi->updateOrganizationRelationship: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **id** | **int**| The ID of the organization relationship |
 **organization_relationship** | [**\Pipedrive\versions\v1\Model\OrganizationRelationship**](../Model/OrganizationRelationship.md)|  | [optional]

### Return type

[**\Pipedrive\versions\v1\Model\OrganizationRelationshipUpdateResponse**](../Model/OrganizationRelationshipUpdateResponse.md)

### Authorization

[api_key](../README.md#api_key), [oauth2](../README.md#oauth2)

### HTTP request headers

- **Content-Type**: `application/json`
- **Accept**: `application/json`

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints)
[[Back to Model list]](../README.md#documentation-for-models)
[[Back to README]](../README.md)
