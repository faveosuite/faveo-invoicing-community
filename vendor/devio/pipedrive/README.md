Complete Pipedrive API client for PHP
=============================

[![Latest Stable Version](https://poser.pugx.org/devio/pipedrive/v/stable)](https://packagist.org/packages/devio/pipedrive) [![Build Status](https://travis-ci.org/IsraelOrtuno/pipedrive.svg)](https://travis-ci.org/IsraelOrtuno/pipedrive) [![Total Downloads](https://poser.pugx.org/devio/pipedrive/downloads)](https://packagist.org/packages/devio/pipedrive) 

## Contribute by referral code / link

This won't take much time. You could use my referral code or link to get up to 45 days completely free of charge. Just sign up using this link or add the code to the billing section:

[pdp-devio](https://www.pipedrive.com/register?promocode=pdp-devio)

## Consider donating

Do you like this package? Did you find it useful? Donate and support its development.

[![Donate](https://img.shields.io/badge/Donate-PayPal-green.svg)](https://www.paypal.me/IsraelOrtuno)

---

This package provides a complete **framework agnostic** Pipedrive CRM API client library for PHP. It includes all the resources listed on Pipedrive's documentation.

**IMPORTANT:** If you are using Laravel >= 5.8 make sure your version is > 2.1.0.

Feel free to drop me a message at [israel@devio.es](mailto:israel@devio.es) or tweet me at [@IsraelOrtuno](https://twitter.com/IsraelOrtuno).

## Quick start using API token (read below for OAuth)
```php
$token = 'xxxxxxxxxxxxxxxxxxxxxxxxxxx';
$pipedrive = new Pipedrive($token);

// Easily access a Pipedrive resource and its values
$organization = $pipedrive->organizations->find(1);
var_dump($organization->getData());

// Also simple to update any Pipedrive resource value
$organization = $pipedrive->organizations->update(1, ['name' => 'Big Code']);
var_dump($organization->getData());

// Keep reading this documentation to find out more.
```

For a deeper knowledge of how to use this package, follow this index:

- [Installation](#installation)
- [Usage](#usage)
    - [Create the Pipedrive instance](#create-the-pipedrive-instance)
        - [With API token](#using-api-token)
        - [With OAuth](#using-oauth)
    - [Resolve a Pipedrive API Resource](#resolve-a-pipedrive-api-resource)
- [Performing a resource call](#performing-a-resource-call)
    - [Available methods](#available-methods)
    - [Performing the Request](#performing-the-request)
    - [Handling the response](#handling-the-response)
    - [Response methods](#response-methods)
- [Available resources](#available-resources)
    - [The File Resource](#the-file-resource)
- [Configure and use in Laravel](#configure-and-use-in-laravel)
    - [Service Provider and Facade](#service-provider-and-facade)
    - [The service configuration](#the-service-configuration)
    - [Using it](#using-it)
- [Contribute](#contribute)

# Important

Versions 1.x do not include OAuth support, update to version 2.x to use this feature.

# Installation

You can install the package via `composer require` command:

```shell
composer require devio/pipedrive
```

Or simply add it to your composer.json dependences and run `composer update`:

```json
"require": {
    "devio/pipedrive": "^2.0"
}
```

# Usage

## Create the Pipedrive instance

`Devio\Pipedrive\Pipedrive` class acts as Manager and will be responsible of resolving the different API resources available.
Pipedrive supports two different authentication methods: via **API token** (for manual integrations) and with **OAuth** (for public and private apps).
You can read more about it on the official documentation, here: https://pipedrive.readme.io/docs/core-api-concepts-authentication 

### Using API token

```php
$token = 'PipedriveTokenHere';
$pipedrive = new Pipedrive($token);
```

> NOTE: Consider storing this object into a global variable.

### Using OAuth

To understand how the OAuth flow works, please read the documentation first.<br>You can find it here: https://pipedrive.readme.io/docs/marketplace-oauth-authorization

You will first need to create an app and retrieve client_id and client_secret.
Please, read the official documentation to learn how to do that.<br>You can find all you need here: https://pipedrive.readme.io/docs/marketplace-creating-a-proper-app

Once you have your client_id, client_secret, and redirect_url, you can instantiate the class like this:
```php
$pipedrive = Pipedrive::OAuth([
    'clientId' => '<your-client-id>',
    'clientSecret' => '<your-client-secret>',
    'redirectUrl' => '<your-redirect-url>',
    'storage' => new PipedriveTokenIO() // This is your implementation of the PipedriveTokenStorage interface (example below)
]);
```
**The class will automatically handle the redirect to the authentication server and refresh token requests.**

The only thing you need to provide is your own implementation of the PipedriveTokenStorage interface.

The purpose of this class is to read and write a `PipedriveToken` object, containing `access_token`, `refresh_token`, and `expiresAt`, giving you the ability to handle this information as you prefer (for example storing these properties in your preferred way).

Here's an example of how it can be implemented:
```php
class PipedriveTokenIO implements \Devio\Pipedrive\PipedriveTokenStorage
{
    public function setToken(\Devio\Pipedrive\PipedriveToken $token) {
        $_SESSION['token'] = serialize($token); // or encrypt and store in the db, or anything else...
    }

    public function getToken() { // Returns a PipedriveToken instance 
        return isset($_SESSION['token']) ? unserialize($_SESSION['token']) : null;
    }
}
```
In this simple example, the PipedriveToken is simply stored and retrieved from the session. Which means that once the session expires, the user will be redirected to the authentication page.

You might want to store this object inside the database. Storing the whole object serialized could be the fastest way to do that, but you can also retrieve access token, refresh token, and the expiration time individually using the methods `getAccessToken`, `getRefreshToken`, and `expiresAt`. Like this:

```php
public function setToken(\Devio\Pipedrive\PipedriveToken $token) {
    $token->getAccessToken(); // save it individually
    $token->getRefreshToken(); // save it individually
    $token->expiresAt(); // save it individually
}
```
Similarly, the object can be instantiated like so:
```php
$token = new \Devio\Pipedrive\PipedriveToken([
    'accessToken' => 'xxxxx', // read it individually from the db
    'refreshToken' => 'xxxxx', // read it individually from the db
    'expiresAt' => 'xxxxx', // read it individually from the db
]);
```
#### Handling the callback
In the callback (the url you specified as `redirectUrl`), you should call the `authorize` method on the `$pipedrive` object, like so:
```php
if(!empty($_GET['code'])) {
    $pipedrive->authorize($_GET['code']);
}
```
This will exchange the authorization code for the first access token, and store it using the `setToken` method you provided.
## Resolve a Pipedrive API Resource

Once we have our Pipedrive instance, we are able to resolve any Pipedrive API Resource in many ways.

First you could do it calling the `make()` method:

```php
// Organizations
$organizations = $pipedrive->make('organizations');
// Persons
$persons = $pipedrive->make('persons');
// ...
````

It also intercepts the magic method `__get` so we could do:

```php
// Deals
$deals = $pipedrive->deals;
// Activities
$activities = $pipedrive->activities;
// ...
```

And just in case you prefer `__call`, you can use it, too:

```php
// EmailMessages
$emailMessages = $pipedrive->emailMessages();
// GlobalMessages
$globalMessages = $pipedrive->globalMessages();
// ...
```

They are 3 different ways of doing the same thing, pick the one you like the most. It will automatically set the **studly case** version of the asked resource, so it will work with `emailMessages`, `EmailMessages`, `email_messages`...

> IMPORTANT: Navigate to the `src/Resources` directory to find out all the resources available.


## Performing a resource call

### Available methods

All resources have various methods for performing the different API requests. Please, navigate to the resource class you would like to work with to find out all the methods available. Every method is documented and can also be found at [Pipedrive API Docs page](https://developers.pipedrive.com/v1).

Every resource extends from `Devio\Pipedrive\Resources\Basics\Resource` where the most common methods are defined. Some of them are disabled for the resources that do not include them. Do not forget to check out the Traits included and some resources use, they define some other common calls to avoid code duplication.

### Performing the Request

After resolved the resource we want to use, we are able to perform an API request. At this point, we only have to execute the endpoint we would like to access:

```php
$organizations = $pipedrive->organizations->all();
//
$pipedrive->persons->update(1, ['name' => 'Israel Ortuno']);
```

Any of these methods will perform a synchronous request to the Pipedrive API.

### Handling the response

Every Pipedrive API endpoint gives a response and this response is converted to a `Devio\Pipedrive\Http\Response` object to handle it:

```php
$response = $pipedrive->organizations->all();

$organizations = $response->getData();
```

#### Response methods

The `Response` class has many methods available for accessing the response data:

##### isSuccess()

Check if the server responded the request was successful.

##### getContent()

Will provide the raw response provided by the Pipedrive API. Useful if you need specific control.

##### getData()

Get the response main data object which will include the information about the endpoint we are calling.

##### getAdditionalData()

Some responses include an additional data object with some extra information. Fetch this object with this method.

##### getStatusCode()

Get the response status code.

##### getHeaders()

Get the response headers.

## Available resources

Every Resource logic is located at the `src/Resources` directory. However we'll mention every included resource here:

| Resource                  | Methods implemented       | Notes         |
|:--------------------------|:--------------------------|:--------------|
| Activities                | :white_check_mark: 6/6    | |
| ActivityFields            | :white_check_mark: 1/1    | |
| ActivityTypes             | :white_check_mark: 5/5    | |
| Currencies                | :white_check_mark: 1/1    | |
| DealFields                | :white_check_mark: 25/25  | |
| Deals                     | :white_check_mark: 6/6    | |
| EmailMessages             | :white_check_mark: 4/4    | |
| EmailThreads              | :white_check_mark: 6/6    | |
| Files                     | :white_check_mark: 8/8    | |
| Filters                   | :white_check_mark: 6/6    | |
| GlobalMessages            | :white_check_mark: 2/2    | |
| Goals                     | :warning: 5/6             | Missing goal results method |
| Notes                     | :white_check_mark: 5/5    | |
| NoteFields                | :white_check_mark: 1/1    | |
| OrganizationFields        | :white_check_mark: 6/6    | |
| OrganizationRelationships | :white_check_mark: 5/5    | |
| Organizations             | :white_check_mark: 18/18  | |
| PermissionsSets           | :white_check_mark: 6/6    | |
| PersonFields              | :white_check_mark: 18/20  | |
| Persons                   | :warning: 18/20           | Missing `add` and `delete` pictures as getting required fields error. |
| Pipelines                 | :warning: 6/8             | Missing deals conversion rates and deals movements |
| ProductFields             | :white_check_mark: 6/6    | |
| Products                  | :white_check_mark: 9/9    | |
| PushNotifications         | :white_check_mark: 4/4    | |
| Recents                   | :white_check_mark: 1/1    | |
| Roles                     | :warning: 0/11            | Getting unathorized access |
| SearchResults             | :white_check_mark: 2/2    | |
| Stages                    | :white_check_mark: 7/7    | |
| UserConnections           | :white_check_mark: 1/1    | |
| Users                     | :warning: 13/20           | Getting unathorized access when playing with roles and permissions |
| UserSettings              | :white_check_mark: 1/1    | |

:white_check_mark: Completed / :warning: Pipedrive API errors

### The File Resource

The File resource is the only one that works a little bit different than others. While other resources may be intuitively used as most of them just require a plain array of tada, the `File` resource requires an `\SplFileInfo` instance to make it work:

```php
$file = new \SplFileInfo('document.pdf');

$pipedrive->files->add([
    'file'   => $file,
    'person_id' => 1,
    // 'deal_id' => 1
]);
```

Actually, it is pretty simple. Just pass a `\SplFileInfo` instance to the `file` key of the options array and specify at least one of the elements it goes related to (deal, person, ...).

# Configure and use in Laravel

If you are using Laravel, you could make use of the `PipedriveServiceProvider` and `PipedriveFacade` which will make the using of this package much more comfortable:

## Service Provider and Facade

Include the `PipedriveServiceProvider` to the providers array in `config/app.php` and register the Laravel Facade.

```php
'providers' => [
  ...
  Devio\Pipedrive\PipedriveServiceProvider::class,
  ...
],
'alias' => [
    ...
    'Pipedrive' => Devio\Pipedrive\PipedriveFacade::class,
    ...
]
```

## The service configuration

Laravel includes a configuration file for storing external services information at `config/services.php`. We have to set up our Pipedrive token at that file like this:

```php
'pipedrive' => [
    'token' => 'the pipedrive token'
]
```

Of course, as many other config parameters, you could store the token at your `.env` file or environment variable and fetch it using `dotenv`:

```php
'pipedrive' => [
    'token' => env('PIPEDRIVE_TOKEN')
]
```

## Using it

You could use it using the Laravel facade `PipedriveFacade` that we have previously loaded:

```php 
$organizations = Pipedrive::organizations()->all();
//
Pipedrive::persons()->add(['name' => 'John Doe']);
```

Also, resolve it out of the service container:

```php
$pipedrive = app()->make('pipedrive');
````

Or even inject it wherever you may need using the `Devio\Pipedrive\Pipedrive` signature.

# Contribute

Feel free to contribute via PR.
