# Laravel DataTables Complete Package

[![Join the chat at https://gitter.im/yajra/laravel-datatables](https://badges.gitter.im/yajra/laravel-datatables.svg)](https://gitter.im/yajra/laravel-datatables?utm_source=badge&utm_medium=badge&utm_campaign=pr-badge&utm_content=badge)
[![Donate](https://img.shields.io/badge/donate-paypal-blue.svg)](https://www.paypal.me/yajra)
[![Donate](https://img.shields.io/badge/donate-patreon-blue.svg)](https://www.patreon.com/bePatron?u=4521203)

[![Laravel 11](https://img.shields.io/badge/Laravel-11-orange.svg)](http://laravel.com)
[![Latest Stable Version](https://img.shields.io/packagist/v/yajra/laravel-datatables-oracle.svg)](https://packagist.org/packages/yajra/laravel-datatables-oracle)
[![Continuous Integration](https://github.com/yajra/laravel-datatables/actions/workflows/continuous-integration.yml/badge.svg)](https://github.com/yajra/laravel-datatables/actions/workflows/continuous-integration.yml)
[![Static Analysis](https://github.com/yajra/laravel-datatables/actions/workflows/static-analysis.yml/badge.svg)](https://github.com/yajra/laravel-datatables/actions/workflows/static-analysis.yml)

[![Total Downloads](https://poser.pugx.org/yajra/laravel-datatables-oracle/downloads.png)](https://packagist.org/packages/yajra/laravel-datatables-oracle)
[![License](https://img.shields.io/github/license/mashape/apistatus.svg)](https://packagist.org/packages/yajra/laravel-datatables-oracle)

This package is a complete installer of [Laravel DataTables](https://github.com/yajra/laravel-datatables) core & plugins.

## Requirements

- [PHP >= 8.2](http://php.net/)
- [Laravel 11.x](https://github.com/laravel/framework)
- [jQuery DataTables v1.10.x](http://datatables.net/)
- [jQuery DataTables Buttons Extension](https://datatables.net/reference/button/)

## Documentations

- [Laravel DataTables Documentation](http://yajrabox.com/docs/laravel-datatables)

## Laravel Version Compatibility

| Laravel       | Package |
|:--------------|:--------|
| 8.x and below | 1.x     |
| 9.x           | 9.x     |
| 10.x          | 10.x    |
| 11.x          | 11.x    |

## Installation

`composer require yajra/laravel-datatables:^11`

#### Service Providers

Update `config/app.php` and register the following providers.

> This step is optional if you are using Laravel 5.5.

```php
Yajra\DataTables\DataTablesServiceProvider::class,
Yajra\DataTables\ButtonsServiceProvider::class,
Yajra\DataTables\FractalServiceProvider::class
```

#### Configuration and Assets (Optional)

`$ php artisan vendor:publish`

And that's it! Start building out some awesome DataTables!

## Contributing

Please see [CONTRIBUTING](https://github.com/yajra/laravel-datatables/blob/master/.github/CONTRIBUTING.md) for details.

## Security

If you discover any security related issues, please email [aqangeles@gmail.com](mailto:aqangeles@gmail.com) instead of using the issue tracker.

## Credits

- [Arjay Angeles](https://github.com/yajra)
- [All Contributors](https://github.com/yajra/laravel-datatables/graphs/contributors)

## License

The MIT License (MIT). Please see [License File](https://github.com/yajra/laravel-datatables/blob/master/LICENSE.md) for more information.
