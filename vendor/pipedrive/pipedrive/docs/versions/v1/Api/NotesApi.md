# Pipedrive\versions\v1\NotesApi

All URIs are relative to https://api.pipedrive.com/v1.

Method | HTTP request | Description
------------- | ------------- | -------------
[**addNote()**](NotesApi.md#addNote) | **POST** /notes | Add a note
[**addNoteComment()**](NotesApi.md#addNoteComment) | **POST** /notes/{id}/comments | Add a comment to a note
[**deleteComment()**](NotesApi.md#deleteComment) | **DELETE** /notes/{id}/comments/{commentId} | Delete a comment related to a note
[**deleteNote()**](NotesApi.md#deleteNote) | **DELETE** /notes/{id} | Delete a note
[**getComment()**](NotesApi.md#getComment) | **GET** /notes/{id}/comments/{commentId} | Get one comment
[**getNote()**](NotesApi.md#getNote) | **GET** /notes/{id} | Get one note
[**getNoteComments()**](NotesApi.md#getNoteComments) | **GET** /notes/{id}/comments | Get all comments for a note
[**getNotes()**](NotesApi.md#getNotes) | **GET** /notes | Get all notes
[**updateCommentForNote()**](NotesApi.md#updateCommentForNote) | **PUT** /notes/{id}/comments/{commentId} | Update a comment related to a note
[**updateNote()**](NotesApi.md#updateNote) | **PUT** /notes/{id} | Update a note


## `addNote()`

```php
addNote($add_note_request): \Pipedrive\versions\v1\Model\PostNote
```

Add a note

Adds a new note.

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


$apiInstance = new Pipedrive\versions\v1\Api\NotesApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$add_note_request = new \Pipedrive\versions\v1\Model\AddNoteRequest(); // \Pipedrive\versions\v1\Model\AddNoteRequest

try {
    $result = $apiInstance->addNote($add_note_request);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling NotesApi->addNote: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **add_note_request** | [**\Pipedrive\versions\v1\Model\AddNoteRequest**](../Model/AddNoteRequest.md)|  | [optional]

### Return type

[**\Pipedrive\versions\v1\Model\PostNote**](../Model/PostNote.md)

### Authorization

[api_key](../README.md#api_key), [oauth2](../README.md#oauth2)

### HTTP request headers

- **Content-Type**: `application/json`
- **Accept**: `application/json`

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints)
[[Back to Model list]](../README.md#documentation-for-models)
[[Back to README]](../README.md)

## `addNoteComment()`

```php
addNoteComment($id, $comment_post_put_object): \Pipedrive\versions\v1\Model\PostComment
```

Add a comment to a note

Adds a new comment to a note.

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


$apiInstance = new Pipedrive\versions\v1\Api\NotesApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$id = 56; // int | The ID of the note
$comment_post_put_object = new \Pipedrive\versions\v1\Model\CommentPostPutObject(); // \Pipedrive\versions\v1\Model\CommentPostPutObject

try {
    $result = $apiInstance->addNoteComment($id, $comment_post_put_object);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling NotesApi->addNoteComment: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **id** | **int**| The ID of the note |
 **comment_post_put_object** | [**\Pipedrive\versions\v1\Model\CommentPostPutObject**](../Model/CommentPostPutObject.md)|  | [optional]

### Return type

[**\Pipedrive\versions\v1\Model\PostComment**](../Model/PostComment.md)

### Authorization

[api_key](../README.md#api_key), [oauth2](../README.md#oauth2)

### HTTP request headers

- **Content-Type**: `application/json`
- **Accept**: `application/json`

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints)
[[Back to Model list]](../README.md#documentation-for-models)
[[Back to README]](../README.md)

## `deleteComment()`

```php
deleteComment($id, $comment_id): \Pipedrive\versions\v1\Model\DeleteComment
```

Delete a comment related to a note

Deletes a comment.

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


$apiInstance = new Pipedrive\versions\v1\Api\NotesApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$id = 56; // int | The ID of the note
$comment_id = 'comment_id_example'; // string | The ID of the comment

try {
    $result = $apiInstance->deleteComment($id, $comment_id);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling NotesApi->deleteComment: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **id** | **int**| The ID of the note |
 **comment_id** | **string**| The ID of the comment |

### Return type

[**\Pipedrive\versions\v1\Model\DeleteComment**](../Model/DeleteComment.md)

### Authorization

[api_key](../README.md#api_key), [oauth2](../README.md#oauth2)

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json`

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints)
[[Back to Model list]](../README.md#documentation-for-models)
[[Back to README]](../README.md)

## `deleteNote()`

```php
deleteNote($id): \Pipedrive\versions\v1\Model\DeleteNote
```

Delete a note

Deletes a specific note.

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


$apiInstance = new Pipedrive\versions\v1\Api\NotesApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$id = 56; // int | The ID of the note

try {
    $result = $apiInstance->deleteNote($id);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling NotesApi->deleteNote: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **id** | **int**| The ID of the note |

### Return type

[**\Pipedrive\versions\v1\Model\DeleteNote**](../Model/DeleteNote.md)

### Authorization

[api_key](../README.md#api_key), [oauth2](../README.md#oauth2)

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json`

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints)
[[Back to Model list]](../README.md#documentation-for-models)
[[Back to README]](../README.md)

## `getComment()`

```php
getComment($id, $comment_id): \Pipedrive\versions\v1\Model\PostComment
```

Get one comment

Returns the details of a comment.

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


$apiInstance = new Pipedrive\versions\v1\Api\NotesApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$id = 56; // int | The ID of the note
$comment_id = 'comment_id_example'; // string | The ID of the comment

try {
    $result = $apiInstance->getComment($id, $comment_id);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling NotesApi->getComment: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **id** | **int**| The ID of the note |
 **comment_id** | **string**| The ID of the comment |

### Return type

[**\Pipedrive\versions\v1\Model\PostComment**](../Model/PostComment.md)

### Authorization

[api_key](../README.md#api_key), [oauth2](../README.md#oauth2)

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json`

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints)
[[Back to Model list]](../README.md#documentation-for-models)
[[Back to README]](../README.md)

## `getNote()`

```php
getNote($id): \Pipedrive\versions\v1\Model\PostNote
```

Get one note

Returns details about a specific note.

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


$apiInstance = new Pipedrive\versions\v1\Api\NotesApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$id = 56; // int | The ID of the note

try {
    $result = $apiInstance->getNote($id);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling NotesApi->getNote: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **id** | **int**| The ID of the note |

### Return type

[**\Pipedrive\versions\v1\Model\PostNote**](../Model/PostNote.md)

### Authorization

[api_key](../README.md#api_key), [oauth2](../README.md#oauth2)

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json`

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints)
[[Back to Model list]](../README.md#documentation-for-models)
[[Back to README]](../README.md)

## `getNoteComments()`

```php
getNoteComments($id, $start, $limit): \Pipedrive\versions\v1\Model\GetComments
```

Get all comments for a note

Returns all comments associated with a note.

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


$apiInstance = new Pipedrive\versions\v1\Api\NotesApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$id = 56; // int | The ID of the note
$start = 0; // int | Pagination start
$limit = 56; // int | Items shown per page

try {
    $result = $apiInstance->getNoteComments($id, $start, $limit);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling NotesApi->getNoteComments: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **id** | **int**| The ID of the note |
 **start** | **int**| Pagination start | [optional] [default to 0]
 **limit** | **int**| Items shown per page | [optional]

### Return type

[**\Pipedrive\versions\v1\Model\GetComments**](../Model/GetComments.md)

### Authorization

[api_key](../README.md#api_key), [oauth2](../README.md#oauth2)

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json`

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints)
[[Back to Model list]](../README.md#documentation-for-models)
[[Back to README]](../README.md)

## `getNotes()`

```php
getNotes($user_id, $lead_id, $deal_id, $person_id, $org_id, $project_id, $start, $limit, $sort, $start_date, $end_date, $pinned_to_lead_flag, $pinned_to_deal_flag, $pinned_to_organization_flag, $pinned_to_person_flag, $pinned_to_project_flag): \Pipedrive\versions\v1\Model\GetNotes
```

Get all notes

Returns all notes.

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


$apiInstance = new Pipedrive\versions\v1\Api\NotesApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$user_id = 56; // int | The ID of the user whose notes to fetch. If omitted, notes by all users will be returned.
$lead_id = 'lead_id_example'; // string | The ID of the lead which notes to fetch. If omitted, notes about all leads will be returned.
$deal_id = 56; // int | The ID of the deal which notes to fetch. If omitted, notes about all deals will be returned.
$person_id = 56; // int | The ID of the person whose notes to fetch. If omitted, notes about all persons will be returned.
$org_id = 56; // int | The ID of the organization which notes to fetch. If omitted, notes about all organizations will be returned.
$project_id = 56; // int | The ID of the project which notes to fetch. If omitted, notes about all projects will be returned.
$start = 0; // int | Pagination start
$limit = 56; // int | Items shown per page
$sort = 'sort_example'; // string | The field names and sorting mode separated by a comma (`field_name_1 ASC`, `field_name_2 DESC`). Only first-level field keys are supported (no nested keys). Supported fields: `id`, `user_id`, `deal_id`, `person_id`, `org_id`, `content`, `add_time`, `update_time`.
$start_date = new \DateTime("2013-10-20T19:20:30+01:00"); // \DateTime | The date in format of YYYY-MM-DD from which notes to fetch
$end_date = new \DateTime("2013-10-20T19:20:30+01:00"); // \DateTime | The date in format of YYYY-MM-DD until which notes to fetch to
$pinned_to_lead_flag = new \Pipedrive\versions\v1\Model\\Pipedrive\versions\v1\Model\NumberBoolean(); // \Pipedrive\versions\v1\Model\NumberBoolean | If set, the results are filtered by note to lead pinning state
$pinned_to_deal_flag = new \Pipedrive\versions\v1\Model\\Pipedrive\versions\v1\Model\NumberBoolean(); // \Pipedrive\versions\v1\Model\NumberBoolean | If set, the results are filtered by note to deal pinning state
$pinned_to_organization_flag = new \Pipedrive\versions\v1\Model\\Pipedrive\versions\v1\Model\NumberBoolean(); // \Pipedrive\versions\v1\Model\NumberBoolean | If set, the results are filtered by note to organization pinning state
$pinned_to_person_flag = new \Pipedrive\versions\v1\Model\\Pipedrive\versions\v1\Model\NumberBoolean(); // \Pipedrive\versions\v1\Model\NumberBoolean | If set, the results are filtered by note to person pinning state
$pinned_to_project_flag = new \Pipedrive\versions\v1\Model\\Pipedrive\versions\v1\Model\NumberBoolean(); // \Pipedrive\versions\v1\Model\NumberBoolean | If set, the results are filtered by note to project pinning state

try {
    $result = $apiInstance->getNotes($user_id, $lead_id, $deal_id, $person_id, $org_id, $project_id, $start, $limit, $sort, $start_date, $end_date, $pinned_to_lead_flag, $pinned_to_deal_flag, $pinned_to_organization_flag, $pinned_to_person_flag, $pinned_to_project_flag);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling NotesApi->getNotes: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **user_id** | **int**| The ID of the user whose notes to fetch. If omitted, notes by all users will be returned. | [optional]
 **lead_id** | **string**| The ID of the lead which notes to fetch. If omitted, notes about all leads will be returned. | [optional]
 **deal_id** | **int**| The ID of the deal which notes to fetch. If omitted, notes about all deals will be returned. | [optional]
 **person_id** | **int**| The ID of the person whose notes to fetch. If omitted, notes about all persons will be returned. | [optional]
 **org_id** | **int**| The ID of the organization which notes to fetch. If omitted, notes about all organizations will be returned. | [optional]
 **project_id** | **int**| The ID of the project which notes to fetch. If omitted, notes about all projects will be returned. | [optional]
 **start** | **int**| Pagination start | [optional] [default to 0]
 **limit** | **int**| Items shown per page | [optional]
 **sort** | **string**| The field names and sorting mode separated by a comma (&#x60;field_name_1 ASC&#x60;, &#x60;field_name_2 DESC&#x60;). Only first-level field keys are supported (no nested keys). Supported fields: &#x60;id&#x60;, &#x60;user_id&#x60;, &#x60;deal_id&#x60;, &#x60;person_id&#x60;, &#x60;org_id&#x60;, &#x60;content&#x60;, &#x60;add_time&#x60;, &#x60;update_time&#x60;. | [optional]
 **start_date** | **\DateTime**| The date in format of YYYY-MM-DD from which notes to fetch | [optional]
 **end_date** | **\DateTime**| The date in format of YYYY-MM-DD until which notes to fetch to | [optional]
 **pinned_to_lead_flag** | [**\Pipedrive\versions\v1\Model\NumberBoolean**](../Model/.md)| If set, the results are filtered by note to lead pinning state | [optional]
 **pinned_to_deal_flag** | [**\Pipedrive\versions\v1\Model\NumberBoolean**](../Model/.md)| If set, the results are filtered by note to deal pinning state | [optional]
 **pinned_to_organization_flag** | [**\Pipedrive\versions\v1\Model\NumberBoolean**](../Model/.md)| If set, the results are filtered by note to organization pinning state | [optional]
 **pinned_to_person_flag** | [**\Pipedrive\versions\v1\Model\NumberBoolean**](../Model/.md)| If set, the results are filtered by note to person pinning state | [optional]
 **pinned_to_project_flag** | [**\Pipedrive\versions\v1\Model\NumberBoolean**](../Model/.md)| If set, the results are filtered by note to project pinning state | [optional]

### Return type

[**\Pipedrive\versions\v1\Model\GetNotes**](../Model/GetNotes.md)

### Authorization

[api_key](../README.md#api_key), [oauth2](../README.md#oauth2)

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json`

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints)
[[Back to Model list]](../README.md#documentation-for-models)
[[Back to README]](../README.md)

## `updateCommentForNote()`

```php
updateCommentForNote($id, $comment_id, $comment_post_put_object): \Pipedrive\versions\v1\Model\PostComment
```

Update a comment related to a note

Updates a comment related to a note.

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


$apiInstance = new Pipedrive\versions\v1\Api\NotesApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$id = 56; // int | The ID of the note
$comment_id = 'comment_id_example'; // string | The ID of the comment
$comment_post_put_object = new \Pipedrive\versions\v1\Model\CommentPostPutObject(); // \Pipedrive\versions\v1\Model\CommentPostPutObject

try {
    $result = $apiInstance->updateCommentForNote($id, $comment_id, $comment_post_put_object);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling NotesApi->updateCommentForNote: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **id** | **int**| The ID of the note |
 **comment_id** | **string**| The ID of the comment |
 **comment_post_put_object** | [**\Pipedrive\versions\v1\Model\CommentPostPutObject**](../Model/CommentPostPutObject.md)|  | [optional]

### Return type

[**\Pipedrive\versions\v1\Model\PostComment**](../Model/PostComment.md)

### Authorization

[api_key](../README.md#api_key), [oauth2](../README.md#oauth2)

### HTTP request headers

- **Content-Type**: `application/json`
- **Accept**: `application/json`

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints)
[[Back to Model list]](../README.md#documentation-for-models)
[[Back to README]](../README.md)

## `updateNote()`

```php
updateNote($id, $note): \Pipedrive\versions\v1\Model\PostNote
```

Update a note

Updates a note.

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


$apiInstance = new Pipedrive\versions\v1\Api\NotesApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$id = 56; // int | The ID of the note
$note = new \Pipedrive\versions\v1\Model\Note(); // \Pipedrive\versions\v1\Model\Note

try {
    $result = $apiInstance->updateNote($id, $note);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling NotesApi->updateNote: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **id** | **int**| The ID of the note |
 **note** | [**\Pipedrive\versions\v1\Model\Note**](../Model/Note.md)|  | [optional]

### Return type

[**\Pipedrive\versions\v1\Model\PostNote**](../Model/PostNote.md)

### Authorization

[api_key](../README.md#api_key), [oauth2](../README.md#oauth2)

### HTTP request headers

- **Content-Type**: `application/json`
- **Accept**: `application/json`

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints)
[[Back to Model list]](../README.md#documentation-for-models)
[[Back to README]](../README.md)
