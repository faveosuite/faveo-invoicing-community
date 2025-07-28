# Laravel SparkPost Driver

[![Latest Version on Packagist](https://img.shields.io/packagist/v/vemcogroup/laravel-sparkpost-driver.svg?style=flat-square)](https://packagist.org/packages/vemcogroup/laravel-sparkpost-driver)
[![Total Downloads](https://img.shields.io/packagist/dt/vemcogroup/laravel-sparkpost-driver.svg?style=flat-square)](https://packagist.org/packages/vemcogroup/laravel-sparkpost-driver)

## Description

This package allows you to still use SparkPost as MailDriver in Laravel.

This package is inspired by: https://github.com/clarification/sparkpost-laravel-driver and updated with driver from Laravel 5.8.x

## Version

Find the correct version to use in the table below:

| Laravel version | Version |
|:---------------:|:-------:|
|       6.x       |   2.x   |
|       7.x       |   3.x   |
|       8.x       |   4.x   |
|    9.x, 10.x    |   5.x   |

## Installation

You can install the package via composer:

```bash
composer require vemcogroup/laravel-sparkpost-driver
```

If you're running an older version of Laravel, make sure you include the version number in your install. For example, for Laravel 8.x:

```bash
composer require vemcogroup/laravel-sparkpost-driver:4.x
```

The package will automatically register its service provider.

## Usage

You will need to configure your Laravel installation before you can use Sparkpost.

**1. Update config/services.php**

You will need to add Sparkpost service to your `config/services.php`:

```php
'sparkpost' => [
    'secret' => env('SPARKPOST_SECRET')
],
```

You can configure additional options there, too:

**Sparkpost API options**

You can define specific [SparkPost options]
(https://developers.sparkpost.com/api/transmissions/#header-request-body) like `open_tracking`, `click_tracking`, `transactional`

**EU GDPR compliance**

You are able to use the EU endpoint for Europe GDPR compliance by setting the `endpoint` option or the default will be used.

SparkPost (default): `https://api.sparkpost.com/api/v1`
SparkPost EU: `https://api.eu.sparkpost.com/api/v1`

**Guzzle options**

You are able to specify [Guzzle options](http://docs.guzzlephp.org/en/stable/request-options.html) in the SparkPost config section `guzzle`.

Just include the additional configuration in your `config/services.php`.

```php
'sparkpost' => [
    'secret' => env('SPARKPOST_SECRET'),

    // optional guzzle specific configuration
    'guzzle' => [
        'verify' => true,
        'decode_content' => true,
        ...
    ],
    'options' => [
        // configure endpoint, if not default
        'endpoint' => env('SPARKPOST_ENDPOINT'),

        // optional Sparkpost API options go here
        'return_path' => 'mail@bounces.domain.com',
        'options' => [
            'open_tracking' => false,
            'click_tracking' => false,
            'transactional' => true,
        ],
    ],
],
```

**2. Set API Key**

You will also need to add the SparkPost API Key to your environment (`.env`) file:

```php
SPARKPOST_SECRET=__Your_key_here__
```

**3. Set Mail Driver**

You need to set your mail driver to SparkPost.

You can do this by setting the environment variable `MAIL_MAILER` in your `.env` file

```php
MAIL_MAILER=sparkpost
```

Or, alternatively by changing the driver in `config/mail.php`:

```php
'driver' => env('MAIL_MAILER', 'sparkpost'),
```

> Note: If you are still using Laravel 5, `MAIL_MAILER` will be referenced as `MAIL_DRIVER`.

**4. Update config/mail.php**

Finally, you will also need to add the `sparkpost` driver to the `config/mail.php` mailer section.

```php
'mailers' => [
    ...
    'sparkpost' => [
        'transport' => 'sparkpost'
    ],
    ...
],
```

> Note: Laravel 5 already includes this configuration, so you don't need to do it if you're using Laravel 5

## Helper functions

### Delete supressions
```php
sparkpost_delete_supression('test@example.com');
```

### Validate single email address
```php
sparkpost_check_email('test@example.com');
```

## Mail Subaccounts

To send an email using a [SparkPost mail subaccount](https://support.sparkpost.com/docs/user-guide/subaccounts), add the desired subaccount id to the message header before sending:
```php
$subaccount_id = 1234;
$this->withSymfonyMessage(function ($message) use ($subaccount_id) { // 'this' is a mailable
    $headers = $message->getHeaders();
    $headers->addTextHeader('subaccount_id', $subaccount_id);
});
``` 
