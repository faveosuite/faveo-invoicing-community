<p align="center"><a href="https://postmarkapp.com" target="_blank"><img src="https://postmarkapp.com/images/logo.svg" alt="Postmark" width="240" height="52"></a>

# Laravel Postmark

[![Latest Version on Packagist][ico-version]][link-packagist]
[![Software License][ico-license]](LICENSE.md)
[![Tests][ico-tests]][link-tests]
[![StyleCI][ico-style-ci]][link-style-ci]
[![Total Downloads][ico-downloads]][link-downloads]

> [Postmark](https://postmarkapp.com) is the easiest and most reliable way to be sure your important transactional emails get to your customer's inbox.

## Upgrading

Please see [UPGRADE](UPGRADE.md) for details.

## Installation

You can install the package via composer:

``` bash
$ composer require coconutcraig/laravel-postmark
```

The package will automatically register itself.

## Usage

Update your `.env` file by adding your server key and set your mail driver to `postmark`.

```php
MAIL_MAILER=postmark
POSTMARK_TOKEN=YOUR-SERVER-KEY-HERE
```

That's it! The mail system continues to work the exact same way as before and you can switch out Postmark for any of the pre-packaged Laravel mail drivers (smtp, mailgun, log, etc...).

> Remember, when using Postmark the sending address used in your emails must be a [valid Sender Signature](http://support.postmarkapp.com/category/45-category) that you have already configured.

## Postmark Templates

### Notification

Postmark offers a fantastic templating service for you to utilize instead of maintaining your templates within your Laravel application. If you would like to take advantage of that, this package offers an extension on the base `MailMessage` provided out of the box with Laravel. Within a Laravel notification, you can do the following to start taking advantage of Postmark templates.

```php
use CraigPaul\Mail\TemplatedMailMessage;

public function toMail($notifiable)
{
    return (new TemplatedMailMessage)
        ->identifier(8675309)
        ->include([
            'name' => 'Customer Name',
            'action_url' => 'https://example.com/login',
        ]);
}
```

### Mailable

It is also possible to use templated notifications via an extension on the base `Mailable` provided out of the box with Laravel.

```php
use CraigPaul\Mail\TemplatedMailable;
use Illuminate\Support\Facades\Mail;

$mailable = (new TemplatedMailable())
    ->identifier(8675309)
    ->include([
        'name' => 'Customer Name',
        'action_url' => 'https://example.com/login',
    ]);

Mail::to('mail@example.com')->send($mailable);
```

> You may also utilize an alias instead of the template identifier by using the `->alias()` method in both cases.

## Postmark Tags

If you rely on categorizing your outgoing emails using Tags in Postmark, you can simply add a header within your Mailable class's build method.

```php
use Symfony\Component\Mailer\Header\TagHeader;
use Symfony\Component\Mime\Email;

public function build()
{
    $this->withSymfonyMessage(function (Email $message) {
        $message->getHeaders()->add(new TagHeader('value'))
    });
}
```

## Postmark Metadata

Similar to tags, you can also include [metadata](https://postmarkapp.com/support/article/1125-custom-metadata-faq) by adding a header.

```php
use Symfony\Component\Mailer\Header\MetadataHeader;
use Symfony\Component\Mime\Email;

public function build()
{
    $this->withSymfonyMessage(function (Email $message) {
        $message->getHeaders()->add(new MetadataHeader('field', 'value'));
        $message->getHeaders()->add(new MetadataHeader('another-field', 'another value'));
    });
}
```

In this case, the following object will be sent to Postmark as metadata.

```
{
    "field": "value",
    "another-field", "another value"
}
```

## Postmark Servers

Out of the box, we determine the Postmark server you send to using a configuration variable set within the environment you have deployed to. This works for most use cases, but if you have the need or desire to determine the Postmark server at runtime, you can supply a header during the sending process.

```php
use CraigPaul\Mail\PostmarkServerTokenHeader;
use Symfony\Component\Mime\Email;

public function build()
{
    $this->withSymfonyMessage(function (Email $message) {
        $message->getHeaders()->add(new PostmarkServerTokenHeader('POSTMARK_TOKEN'))
    });
}
```

## Change log

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Testing

``` bash
$ composer test
```

## Contributing

Please see [CONTRIBUTING](.github/CONTRIBUTING.md) and [CONDUCT](.github/CODE_OF_CONDUCT.md) for details.

## Security Vulnerabilities

Please review our [security policy](.github/SECURITY.md) on how to report security vulnerabilities.

## Credits

- [Craig Paul][link-author-paul]
- [Mark van den Broek][link-author-mark]
- [All Contributors][link-contributors]

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

[ico-version]: https://img.shields.io/packagist/v/coconutcraig/laravel-postmark.svg?style=flat-square
[ico-license]: https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square
[ico-tests]: https://img.shields.io/github/workflow/status/craigpaul/laravel-postmark/tests/main?label=tests&style=flat-square
[ico-style-ci]: https://styleci.io/repos/80351847/shield?branch=main
[ico-downloads]: https://img.shields.io/packagist/dt/coconutcraig/laravel-postmark.svg?style=flat-square

[link-packagist]: https://packagist.org/packages/coconutcraig/laravel-postmark
[link-tests]: https://github.com/craigpaul/laravel-postmark/actions?query=workflow%3Atests
[link-style-ci]: https://styleci.io/repos/80351847
[link-downloads]: https://packagist.org/packages/coconutcraig/laravel-postmark
[link-author-paul]: https://github.com/craigpaul
[link-author-mark]: https://github.com/mvdnbrk
[link-contributors]: ../../contributors
