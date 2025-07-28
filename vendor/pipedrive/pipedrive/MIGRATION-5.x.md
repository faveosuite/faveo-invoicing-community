# Migration Guide from 4.x to 5.x

## Configuration

### API Token

Configure authorization in case of `api_token` usage.

<table>
<tr>
<td> <b>4.x</b> </td> <td> <b>5.x</b> </td>
</tr>
<tr>
<td>No configuration needed, <code>api_token</code> should have been set when the <code>Client</code> instance was created.</td>
<td>

Default configuration should be updated by setting the api key with the `api_token` prefix.

```php
use Pipedrive\Configuration;

$config = new Configuration();
$config->setApiKey('api_token', 'YOUR_API_TOKEN');
```
</td>
</tr>
</table>

### OAuth

Configure authorization in case of OAuth 2.0 usage.

<table>
<tr>
<td> <b>4.x</b> </td> <td> <b>5.x</b> </td>
</tr>
<tr>
<td>
OAuth 2.0 configuration properties should have been set <br> when <code>Client</code> instance was created.

Stored user tokens have to be set by re-assigning `$token` object with custom values:

```php

use Pipedrive\Configuration;

Configuration::$token = (object) [
    'accessToken' => 'USER_ACCESS_TOKEN',
    'refreshToken' => 'USER_REFRESH_TOKEN',
    'expiry' => 'USER_ACCESS_TOKEN_EXPIRATION_MS',
    'tokenType' => 'Bearer',
];
```

`$oAuthTokenUpdateCallback` has to be defined to handle callback update tokens in storage.

```php
Configuration::$oAuthTokenUpdateCallback = function($token) {
    // use session or some other way to persist the $token
};
```
</td>
<td>
Default configuration should be updated with OAuth 2.0 properties.

```php
use Pipedrive\Configuration;

$config = new Configuration();
$config->setClientId('YOUR_APP_CLIENT_ID');
$config->setClientSecret('YOUR_APP_CLIENT_SECRET');
$config->setOauthRedirectUri('YOUR_APP_REDIRECT_URI');
$config->setOAuthTokenUpdateCallback(function () {
    // use session or some other way to persist the $token
});
```

Stored user tokens have to be set with `updateOAuthRelatedFields` method of `Configuration` instance:
```php
$config->updateOAuthRelatedFields([
    'expires_in' => 'USER_ACCESS_TOKEN_EXPIRATION_MS',
    'access_token' => 'USER_ACCESS_TOKEN',
    'refresh_token' => 'USER_REFRESH_TOKEN',
    'api_domain' => 'USER_API_DOMAIN',
]);
```
</td>
</tr>
</table>
<br>

## Authentication

<table>
<tr>
    <td> <b>4.x</b> </td> <td> <b>5.x</b> </td>
</tr>
<tr>
<td>
    Create an instance of <code>Client</code> and navigate the user to authorization URL:

```php
use Pipedrive\Client;

$client = new Client(/* ... */);

$client->auth()->buildAuthorizationUrl());
```

Handle the incoming request, exchange `code` query param and store `$token`.

```php
use Pipedrive\Client;

$code = /* get query param value */;

$client = new Client(/* ... */);

$token = $client->auth()->authorize($code);
```
</td>
<td>
Update config with your app properties.

```php
use Pipedrive\Configuration;

$config = new Configuration();
/* ... */

$config->getAuthorizationPageUrl();
```

Handle incoming request, exchange `code` query param and store `$token`.

```php
use Pipedrive\Configuration;

$config = new Configuration();
/* ... */

$config->authorize($code);
```

This will automatically set token properties (using `updateOAuthRelatedFields`) and call `setOAuthTokenUpdateCallback` callback.
</td>
</tr>
</table>
<br>

## API requests

Here are some examples based on the **deal** entity.

### List deals

<table>
<tr>
    <td> <b>4.x</b> </td> <td> <b>5.x</b> </td>
</tr>
<tr>
<td>

```php
use Pipedrive\Client;

/* ...configuration needed here... */

$client = new Client(/* ... */);
$client->getDeals()->getAllDeals([
    'limit' => 10,
    'status' => 'open',
]);
```
</td>
<td>

```php
use Pipedrive\Client;

$config = new Configuration();

/* ...configuration needed here... */

$dealsApiInstance = new DealsApi(null, $config);
$response = $dealsApiInstance->getDeals(
    null,
    null,
    null,
    'open',
    0,
    10,
);
```
</td>
</tr>
</table>

### Add new deal

<table>
<tr>
    <td> <b>4.x</b> </td> <td> <b>5.x</b> </td>
</tr>
<tr>
<td>

```php
use Pipedrive\Client;

/* ...configuration needed here... */

$client = new Client(/* ... */);
$response = $client->getDeals()->addADeal([
    'title' => 'DEAL_TITLE'
]);
```
</td>
<td>

```php
use Pipedrive\Client;
use Pipedrive\Model\NewDeal;

$config = new Configuration();

/* ...configuration needed here... */

$dealsApiInstance = new DealsApi(null, $config);
$response = $dealsApiInstance->addDeal(new NewDeal([
    'title' => 'DEAL_TITLE',
]));
```
</td>
</tr>
</table>

### Update deal

<table>
<tr>
    <td> <b>4.x</b> </td> <td> <b>5.x</b> </td>
</tr>
<tr>
<td>

```php
use Pipedrive\Client;

/* ...configuration needed here... */

$client = new Client(/* ... */);
$response = $client->getDeals()->updateADeal([
    'id' => 'DEAL_ID',
    'title' => 'DEAL_TITLE_UPDATED'
]);
```
</td>
<td>

```php
use Pipedrive\Client;
use Pipedrive\Model\NewDeal;

$config = new Configuration();

/* ...configuration needed here... */

$dealId = /* DEAL_ID */;
$dealsApiInstance = new DealsApi(null, $config);
$response = $dealsApiInstance->updateDeal($dealId, new UpdateDealRequest([
    'title' => 'DEAL_TITLE_UPDATED',
]));
```
</td>
</tr>
</table>

### Delete deal

<table>
<tr>
    <td> <b>4.x</b> </td> <td> <b>5.x</b> </td>
</tr>
<tr>
<td>

```php
use Pipedrive\Client;

/* ...configuration needed here... */

$client = new Client(/* ... */);
$response = $client->getDeals()->deleteADeal('DEAL_ID');
```
</td>
<td>

```php
use Pipedrive\Client;

$config = new Configuration();

/* ...configuration needed here... */

$dealId = /* DEAL_ID */;
$dealsApiInstance = new DealsApi(null, $config);
$response = $dealsApiInstance->deleteDeal($dealId);
```
</td>
</tr>
</table>
