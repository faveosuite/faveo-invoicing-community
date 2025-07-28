# Pipedrive\versions\v1\FilesApi

All URIs are relative to https://api.pipedrive.com/v1.

Method | HTTP request | Description
------------- | ------------- | -------------
[**addFile()**](FilesApi.md#addFile) | **POST** /files | Add file
[**addFileAndLinkIt()**](FilesApi.md#addFileAndLinkIt) | **POST** /files/remote | Create a remote file and link it to an item
[**deleteFile()**](FilesApi.md#deleteFile) | **DELETE** /files/{id} | Delete a file
[**downloadFile()**](FilesApi.md#downloadFile) | **GET** /files/{id}/download | Download one file
[**getFile()**](FilesApi.md#getFile) | **GET** /files/{id} | Get one file
[**getFiles()**](FilesApi.md#getFiles) | **GET** /files | Get all files
[**linkFileToItem()**](FilesApi.md#linkFileToItem) | **POST** /files/remoteLink | Link a remote file to an item
[**updateFile()**](FilesApi.md#updateFile) | **PUT** /files/{id} | Update file details


## `addFile()`

```php
addFile($file, $deal_id, $person_id, $org_id, $product_id, $activity_id, $lead_id): \Pipedrive\versions\v1\Model\AddFile
```

Add file

Lets you upload a file and associate it with a deal, person, organization, activity, product or lead. For more information, see the tutorial for <a href=\"https://pipedrive.readme.io/docs/adding-a-file\" target=\"_blank\" rel=\"noopener noreferrer\">adding a file</a>.

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


$apiInstance = new Pipedrive\versions\v1\Api\FilesApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$file = "/path/to/file.txt"; // \SplFileObject | A single file, supplied in the multipart/form-data encoding and contained within the given boundaries
$deal_id = 56; // int | The ID of the deal to associate file(s) with
$person_id = 56; // int | The ID of the person to associate file(s) with
$org_id = 56; // int | The ID of the organization to associate file(s) with
$product_id = 56; // int | The ID of the product to associate file(s) with
$activity_id = 56; // int | The ID of the activity to associate file(s) with
$lead_id = 'lead_id_example'; // string | The ID of the lead to associate file(s) with

try {
    $result = $apiInstance->addFile($file, $deal_id, $person_id, $org_id, $product_id, $activity_id, $lead_id);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling FilesApi->addFile: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **file** | **\SplFileObject****\SplFileObject**| A single file, supplied in the multipart/form-data encoding and contained within the given boundaries |
 **deal_id** | **int**| The ID of the deal to associate file(s) with | [optional]
 **person_id** | **int**| The ID of the person to associate file(s) with | [optional]
 **org_id** | **int**| The ID of the organization to associate file(s) with | [optional]
 **product_id** | **int**| The ID of the product to associate file(s) with | [optional]
 **activity_id** | **int**| The ID of the activity to associate file(s) with | [optional]
 **lead_id** | **string**| The ID of the lead to associate file(s) with | [optional]

### Return type

[**\Pipedrive\versions\v1\Model\AddFile**](../Model/AddFile.md)

### Authorization

[api_key](../README.md#api_key), [oauth2](../README.md#oauth2)

### HTTP request headers

- **Content-Type**: `multipart/form-data`
- **Accept**: `application/json`

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints)
[[Back to Model list]](../README.md#documentation-for-models)
[[Back to README]](../README.md)

## `addFileAndLinkIt()`

```php
addFileAndLinkIt($file_type, $title, $item_type, $item_id, $remote_location): \Pipedrive\versions\v1\Model\CreateRemoteFileAndLinkItToItem
```

Create a remote file and link it to an item

Creates a new empty file in the remote location (`googledrive`) that will be linked to the item you supply. For more information, see the tutorial for <a href=\"https://pipedrive.readme.io/docs/adding-a-remote-file\" target=\"_blank\" rel=\"noopener noreferrer\">adding a remote file</a>.

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


$apiInstance = new Pipedrive\versions\v1\Api\FilesApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$file_type = 'file_type_example'; // string | The file type
$title = 'title_example'; // string | The title of the file
$item_type = 'item_type_example'; // string | The item type
$item_id = 56; // int | The ID of the item to associate the file with
$remote_location = 'remote_location_example'; // string | The location type to send the file to. Only `googledrive` is supported at the moment.

try {
    $result = $apiInstance->addFileAndLinkIt($file_type, $title, $item_type, $item_id, $remote_location);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling FilesApi->addFileAndLinkIt: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **file_type** | **string**| The file type |
 **title** | **string**| The title of the file |
 **item_type** | **string**| The item type |
 **item_id** | **int**| The ID of the item to associate the file with |
 **remote_location** | **string**| The location type to send the file to. Only &#x60;googledrive&#x60; is supported at the moment. |

### Return type

[**\Pipedrive\versions\v1\Model\CreateRemoteFileAndLinkItToItem**](../Model/CreateRemoteFileAndLinkItToItem.md)

### Authorization

[api_key](../README.md#api_key), [oauth2](../README.md#oauth2)

### HTTP request headers

- **Content-Type**: `application/x-www-form-urlencoded`
- **Accept**: `application/json`

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints)
[[Back to Model list]](../README.md#documentation-for-models)
[[Back to README]](../README.md)

## `deleteFile()`

```php
deleteFile($id): \Pipedrive\versions\v1\Model\DeleteFile
```

Delete a file

Marks a file as deleted. After 30 days, the file will be permanently deleted.

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


$apiInstance = new Pipedrive\versions\v1\Api\FilesApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$id = 56; // int | The ID of the file

try {
    $result = $apiInstance->deleteFile($id);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling FilesApi->deleteFile: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **id** | **int**| The ID of the file |

### Return type

[**\Pipedrive\versions\v1\Model\DeleteFile**](../Model/DeleteFile.md)

### Authorization

[api_key](../README.md#api_key), [oauth2](../README.md#oauth2)

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json`

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints)
[[Back to Model list]](../README.md#documentation-for-models)
[[Back to README]](../README.md)

## `downloadFile()`

```php
downloadFile($id): string
```

Download one file

Initializes a file download.

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


$apiInstance = new Pipedrive\versions\v1\Api\FilesApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$id = 56; // int | The ID of the file

try {
    $result = $apiInstance->downloadFile($id);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling FilesApi->downloadFile: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **id** | **int**| The ID of the file |

### Return type

**string**

### Authorization

[api_key](../README.md#api_key), [oauth2](../README.md#oauth2)

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/octet-stream`

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints)
[[Back to Model list]](../README.md#documentation-for-models)
[[Back to README]](../README.md)

## `getFile()`

```php
getFile($id): \Pipedrive\versions\v1\Model\GetOneFile
```

Get one file

Returns data about a specific file.

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


$apiInstance = new Pipedrive\versions\v1\Api\FilesApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$id = 56; // int | The ID of the file

try {
    $result = $apiInstance->getFile($id);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling FilesApi->getFile: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **id** | **int**| The ID of the file |

### Return type

[**\Pipedrive\versions\v1\Model\GetOneFile**](../Model/GetOneFile.md)

### Authorization

[api_key](../README.md#api_key), [oauth2](../README.md#oauth2)

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json`

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints)
[[Back to Model list]](../README.md#documentation-for-models)
[[Back to README]](../README.md)

## `getFiles()`

```php
getFiles($start, $limit, $sort): \Pipedrive\versions\v1\Model\GetAllFiles
```

Get all files

Returns data about all files.

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


$apiInstance = new Pipedrive\versions\v1\Api\FilesApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$start = 0; // int | Pagination start
$limit = 56; // int | Items shown per page. Please note that a maximum value of 100 is allowed.
$sort = 'sort_example'; // string | Supported fields: `id`, `update_time`

try {
    $result = $apiInstance->getFiles($start, $limit, $sort);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling FilesApi->getFiles: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **start** | **int**| Pagination start | [optional] [default to 0]
 **limit** | **int**| Items shown per page. Please note that a maximum value of 100 is allowed. | [optional]
 **sort** | **string**| Supported fields: &#x60;id&#x60;, &#x60;update_time&#x60; | [optional]

### Return type

[**\Pipedrive\versions\v1\Model\GetAllFiles**](../Model/GetAllFiles.md)

### Authorization

[api_key](../README.md#api_key), [oauth2](../README.md#oauth2)

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json`

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints)
[[Back to Model list]](../README.md#documentation-for-models)
[[Back to README]](../README.md)

## `linkFileToItem()`

```php
linkFileToItem($item_type, $item_id, $remote_id, $remote_location): \Pipedrive\versions\v1\Model\LinkRemoteFileToItem
```

Link a remote file to an item

Links an existing remote file (`googledrive`) to the item you supply. For more information, see the tutorial for <a href=\"https://pipedrive.readme.io/docs/adding-a-remote-file\" target=\"_blank\" rel=\"noopener noreferrer\">adding a remote file</a>.

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


$apiInstance = new Pipedrive\versions\v1\Api\FilesApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$item_type = 'item_type_example'; // string | The item type
$item_id = 56; // int | The ID of the item to associate the file with
$remote_id = 'remote_id_example'; // string | The remote item ID
$remote_location = 'remote_location_example'; // string | The location type to send the file to. Only `googledrive` is supported at the moment.

try {
    $result = $apiInstance->linkFileToItem($item_type, $item_id, $remote_id, $remote_location);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling FilesApi->linkFileToItem: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **item_type** | **string**| The item type |
 **item_id** | **int**| The ID of the item to associate the file with |
 **remote_id** | **string**| The remote item ID |
 **remote_location** | **string**| The location type to send the file to. Only &#x60;googledrive&#x60; is supported at the moment. |

### Return type

[**\Pipedrive\versions\v1\Model\LinkRemoteFileToItem**](../Model/LinkRemoteFileToItem.md)

### Authorization

[api_key](../README.md#api_key), [oauth2](../README.md#oauth2)

### HTTP request headers

- **Content-Type**: `application/x-www-form-urlencoded`
- **Accept**: `application/json`

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints)
[[Back to Model list]](../README.md#documentation-for-models)
[[Back to README]](../README.md)

## `updateFile()`

```php
updateFile($id, $name, $description): \Pipedrive\versions\v1\Model\UpdateFile
```

Update file details

Updates the properties of a file.

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


$apiInstance = new Pipedrive\versions\v1\Api\FilesApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$id = 56; // int | The ID of the file
$name = 'name_example'; // string | The visible name of the file
$description = 'description_example'; // string | The description of the file

try {
    $result = $apiInstance->updateFile($id, $name, $description);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling FilesApi->updateFile: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **id** | **int**| The ID of the file |
 **name** | **string**| The visible name of the file | [optional]
 **description** | **string**| The description of the file | [optional]

### Return type

[**\Pipedrive\versions\v1\Model\UpdateFile**](../Model/UpdateFile.md)

### Authorization

[api_key](../README.md#api_key), [oauth2](../README.md#oauth2)

### HTTP request headers

- **Content-Type**: `application/x-www-form-urlencoded`
- **Accept**: `application/json`

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints)
[[Back to Model list]](../README.md#documentation-for-models)
[[Back to README]](../README.md)
