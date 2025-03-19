# Pipedrive\versions\v1\LegacyTeamsApi

All URIs are relative to https://api.pipedrive.com/v1.

Method | HTTP request | Description
------------- | ------------- | -------------
[**addTeam()**](LegacyTeamsApi.md#addTeam) | **POST** /legacyTeams | Add a new team
[**addTeamUser()**](LegacyTeamsApi.md#addTeamUser) | **POST** /legacyTeams/{id}/users | Add users to a team
[**deleteTeamUser()**](LegacyTeamsApi.md#deleteTeamUser) | **DELETE** /legacyTeams/{id}/users | Delete users from a team
[**getTeam()**](LegacyTeamsApi.md#getTeam) | **GET** /legacyTeams/{id} | Get a single team
[**getTeamUsers()**](LegacyTeamsApi.md#getTeamUsers) | **GET** /legacyTeams/{id}/users | Get all users in a team
[**getTeams()**](LegacyTeamsApi.md#getTeams) | **GET** /legacyTeams | Get all teams
[**getUserTeams()**](LegacyTeamsApi.md#getUserTeams) | **GET** /legacyTeams/user/{id} | Get all teams of a user
[**updateTeam()**](LegacyTeamsApi.md#updateTeam) | **PUT** /legacyTeams/{id} | Update a team


## `addTeam()`

```php
addTeam($create_team): \Pipedrive\versions\v1\Model\Team
```

Add a new team

Adds a new team to the company and returns the created object.

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


$apiInstance = new Pipedrive\versions\v1\Api\LegacyTeamsApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$create_team = new \Pipedrive\versions\v1\Model\CreateTeam(); // \Pipedrive\versions\v1\Model\CreateTeam

try {
    $result = $apiInstance->addTeam($create_team);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling LegacyTeamsApi->addTeam: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **create_team** | [**\Pipedrive\versions\v1\Model\CreateTeam**](../Model/CreateTeam.md)|  | [optional]

### Return type

[**\Pipedrive\versions\v1\Model\Team**](../Model/Team.md)

### Authorization

[api_key](../README.md#api_key), [oauth2](../README.md#oauth2)

### HTTP request headers

- **Content-Type**: `application/json`
- **Accept**: `application/json`

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints)
[[Back to Model list]](../README.md#documentation-for-models)
[[Back to README]](../README.md)

## `addTeamUser()`

```php
addTeamUser($id, $add_team_user_request): \Pipedrive\versions\v1\Model\UserIDs
```

Add users to a team

Adds users to an existing team.

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


$apiInstance = new Pipedrive\versions\v1\Api\LegacyTeamsApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$id = 56; // int | The ID of the team
$add_team_user_request = new \Pipedrive\versions\v1\Model\AddTeamUserRequest(); // \Pipedrive\versions\v1\Model\AddTeamUserRequest

try {
    $result = $apiInstance->addTeamUser($id, $add_team_user_request);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling LegacyTeamsApi->addTeamUser: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **id** | **int**| The ID of the team |
 **add_team_user_request** | [**\Pipedrive\versions\v1\Model\AddTeamUserRequest**](../Model/AddTeamUserRequest.md)|  | [optional]

### Return type

[**\Pipedrive\versions\v1\Model\UserIDs**](../Model/UserIDs.md)

### Authorization

[api_key](../README.md#api_key), [oauth2](../README.md#oauth2)

### HTTP request headers

- **Content-Type**: `application/json`
- **Accept**: `application/json`

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints)
[[Back to Model list]](../README.md#documentation-for-models)
[[Back to README]](../README.md)

## `deleteTeamUser()`

```php
deleteTeamUser($id, $delete_team_user_request): \Pipedrive\versions\v1\Model\UserIDs
```

Delete users from a team

Deletes users from an existing team.

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


$apiInstance = new Pipedrive\versions\v1\Api\LegacyTeamsApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$id = 56; // int | The ID of the team
$delete_team_user_request = new \Pipedrive\versions\v1\Model\DeleteTeamUserRequest(); // \Pipedrive\versions\v1\Model\DeleteTeamUserRequest

try {
    $result = $apiInstance->deleteTeamUser($id, $delete_team_user_request);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling LegacyTeamsApi->deleteTeamUser: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **id** | **int**| The ID of the team |
 **delete_team_user_request** | [**\Pipedrive\versions\v1\Model\DeleteTeamUserRequest**](../Model/DeleteTeamUserRequest.md)|  | [optional]

### Return type

[**\Pipedrive\versions\v1\Model\UserIDs**](../Model/UserIDs.md)

### Authorization

[api_key](../README.md#api_key), [oauth2](../README.md#oauth2)

### HTTP request headers

- **Content-Type**: `application/json`
- **Accept**: `application/json`

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints)
[[Back to Model list]](../README.md#documentation-for-models)
[[Back to README]](../README.md)

## `getTeam()`

```php
getTeam($id, $skip_users): \Pipedrive\versions\v1\Model\Team
```

Get a single team

Returns data about a specific team.

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


$apiInstance = new Pipedrive\versions\v1\Api\LegacyTeamsApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$id = 56; // int | The ID of the team
$skip_users = new \Pipedrive\versions\v1\Model\\Pipedrive\versions\v1\Model\NumberBooleanDefault0(); // \Pipedrive\versions\v1\Model\NumberBooleanDefault0 | When enabled, the teams will not include IDs of member users

try {
    $result = $apiInstance->getTeam($id, $skip_users);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling LegacyTeamsApi->getTeam: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **id** | **int**| The ID of the team |
 **skip_users** | [**\Pipedrive\versions\v1\Model\NumberBooleanDefault0**](../Model/.md)| When enabled, the teams will not include IDs of member users | [optional]

### Return type

[**\Pipedrive\versions\v1\Model\Team**](../Model/Team.md)

### Authorization

[api_key](../README.md#api_key), [oauth2](../README.md#oauth2)

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json`

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints)
[[Back to Model list]](../README.md#documentation-for-models)
[[Back to README]](../README.md)

## `getTeamUsers()`

```php
getTeamUsers($id): \Pipedrive\versions\v1\Model\UserIDs
```

Get all users in a team

Returns a list of all user IDs within a team.

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


$apiInstance = new Pipedrive\versions\v1\Api\LegacyTeamsApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$id = 56; // int | The ID of the team

try {
    $result = $apiInstance->getTeamUsers($id);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling LegacyTeamsApi->getTeamUsers: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **id** | **int**| The ID of the team |

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

## `getTeams()`

```php
getTeams($order_by, $skip_users): \Pipedrive\versions\v1\Model\Teams
```

Get all teams

Returns data about teams within the company.

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


$apiInstance = new Pipedrive\versions\v1\Api\LegacyTeamsApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$order_by = 'id'; // string | The field name to sort returned teams by
$skip_users = new \Pipedrive\versions\v1\Model\\Pipedrive\versions\v1\Model\NumberBooleanDefault0(); // \Pipedrive\versions\v1\Model\NumberBooleanDefault0 | When enabled, the teams will not include IDs of member users

try {
    $result = $apiInstance->getTeams($order_by, $skip_users);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling LegacyTeamsApi->getTeams: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **order_by** | **string**| The field name to sort returned teams by | [optional] [default to &#39;id&#39;]
 **skip_users** | [**\Pipedrive\versions\v1\Model\NumberBooleanDefault0**](../Model/.md)| When enabled, the teams will not include IDs of member users | [optional]

### Return type

[**\Pipedrive\versions\v1\Model\Teams**](../Model/Teams.md)

### Authorization

[api_key](../README.md#api_key), [oauth2](../README.md#oauth2)

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json`

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints)
[[Back to Model list]](../README.md#documentation-for-models)
[[Back to README]](../README.md)

## `getUserTeams()`

```php
getUserTeams($id, $order_by, $skip_users): \Pipedrive\versions\v1\Model\Teams
```

Get all teams of a user

Returns data about all teams which have the specified user as a member.

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


$apiInstance = new Pipedrive\versions\v1\Api\LegacyTeamsApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$id = 56; // int | The ID of the user
$order_by = 'id'; // string | The field name to sort returned teams by
$skip_users = new \Pipedrive\versions\v1\Model\\Pipedrive\versions\v1\Model\NumberBooleanDefault0(); // \Pipedrive\versions\v1\Model\NumberBooleanDefault0 | When enabled, the teams will not include IDs of member users

try {
    $result = $apiInstance->getUserTeams($id, $order_by, $skip_users);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling LegacyTeamsApi->getUserTeams: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **id** | **int**| The ID of the user |
 **order_by** | **string**| The field name to sort returned teams by | [optional] [default to &#39;id&#39;]
 **skip_users** | [**\Pipedrive\versions\v1\Model\NumberBooleanDefault0**](../Model/.md)| When enabled, the teams will not include IDs of member users | [optional]

### Return type

[**\Pipedrive\versions\v1\Model\Teams**](../Model/Teams.md)

### Authorization

[api_key](../README.md#api_key), [oauth2](../README.md#oauth2)

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json`

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints)
[[Back to Model list]](../README.md#documentation-for-models)
[[Back to README]](../README.md)

## `updateTeam()`

```php
updateTeam($id, $update_team): \Pipedrive\versions\v1\Model\Team
```

Update a team

Updates an existing team and returns the updated object.

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


$apiInstance = new Pipedrive\versions\v1\Api\LegacyTeamsApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$id = 56; // int | The ID of the team
$update_team = new \Pipedrive\versions\v1\Model\UpdateTeam(); // \Pipedrive\versions\v1\Model\UpdateTeam

try {
    $result = $apiInstance->updateTeam($id, $update_team);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling LegacyTeamsApi->updateTeam: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **id** | **int**| The ID of the team |
 **update_team** | [**\Pipedrive\versions\v1\Model\UpdateTeam**](../Model/UpdateTeam.md)|  | [optional]

### Return type

[**\Pipedrive\versions\v1\Model\Team**](../Model/Team.md)

### Authorization

[api_key](../README.md#api_key), [oauth2](../README.md#oauth2)

### HTTP request headers

- **Content-Type**: `application/json`
- **Accept**: `application/json`

[[Back to top]](#) [[Back to API list]](../README.md#documentation-for-api-endpoints)
[[Back to Model list]](../README.md#documentation-for-models)
[[Back to README]](../README.md)
