# Laravel DataTables Fractal Plugin

[![Laravel 11.x](https://img.shields.io/badge/Laravel-11.x-orange.svg)](http://laravel.com)
[![Latest Stable Version](https://img.shields.io/packagist/v/yajra/laravel-datatables-fractal.svg)](https://packagist.org/packages/yajra/laravel-datatables-fractal)
[![Build Status](https://travis-ci.org/yajra/laravel-datatables-fractal.svg?branch=master)](https://travis-ci.org/yajra/laravel-datatables-fractal)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/yajra/laravel-datatables-fractal/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/yajra/laravel-datatables-fractal/?branch=master)
[![Total Downloads](https://img.shields.io/packagist/dt/yajra/laravel-datatables-fractal.svg)](https://packagist.org/packages/yajra/laravel-datatables-fractal)
[![License](https://img.shields.io/github/license/mashape/apistatus.svg)](https://packagist.org/packages/yajra/laravel-datatables-fractal)

This package is a plugin of [Laravel DataTables](https://github.com/yajra/laravel-datatables) for transforming server-side response using [Fractal](https://github.com/thephpleague/fractal).

## Requirements

- [PHP >= 8.2](http://php.net/)
- [Laravel 11.x](https://github.com/laravel/framework)
- [Laravel DataTables](https://github.com/yajra/laravel-datatables)

## Documentations

- [Laravel DataTables Fractal Documentation](https://yajrabox.com/docs/laravel-datatables/master/response-fractal)

## Laravel Version Compatibility

| Laravel       | Package |
|:--------------|:--------|
| 8.x and below | 1.x     |
| 9.x           | 9.x     |
| 10.x          | 10.x    |
| 11.x          | 11.x    |

## Quick Installation

`composer require yajra/laravel-datatables-fractal:^11.0`

### Register Service Provider (Optional on Laravel 5.5+)

`Yajra\DataTables\FractalServiceProvider::class`

### Configuration and Assets (Optional)

`$ php artisan vendor:publish --tag=datatables-fractal --force`

And that's it! Start building out some awesome DataTables!

## Contributing

Please see [CONTRIBUTING](https://github.com/yajra/laravel-datatables-fractal/blob/master/.github/CONTRIBUTING.md) for details.

## Security

If you discover any security related issues, please email [aqangeles@gmail.com](mailto:aqangeles@gmail.com) instead of using the issue tracker.

## Credits

- [Arjay Angeles](https://github.com/yajra)
- [All Contributors](https://github.com/yajra/laravel-datatables-fractal/graphs/contributors)

## License

The MIT License (MIT). Please see [License File](https://github.com/yajra/laravel-datatables-fractal/blob/master/LICENSE.md) for more information.
