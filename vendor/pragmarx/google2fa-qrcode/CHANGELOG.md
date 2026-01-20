## Change Log

## [3.0.0] - 2020-08-15
### Changed
- Allow users to set the image backend

## [2.1.1] - 2020-08-15
### Changed
- Don't add the SVG header if we have it already

## [2.1.0] - 2020-10-17
### Removed
- Support for Bacon QRCode 1.x

## [2.0.0] - 2020-10-16
### Changed
- Add support for SVG QRCodes
- Add support for chillerlan/php-qrcode package
- No need to install the Imagick extension
- Allow users to define their on QRCode service renderer
- Breaking change: beginning on version 2.0 the rendering service is optional, so you have to manually install one of those packages in order to generate QRCodes: [BaconQrCode](https://github.com/Bacon/BaconQrCode): renders PNG by default, but requires the Imagick PHP extension. [chillerlan/php-qrcode](https://github.com/chillerlan/php-qrcode): renders SVG by default and don't require the Imagick PHP extension.
- Add PHP 8.0 compatibility

## [1.0.2] - 2018-10-10
### Changed
- Dropped support for PHP 5.4 & PHP 5.5
- Test QRCode by decoding it

## [1.0.1] - 2018-10-10
### Added
- Add support for more image renderer back ends

## [1.0.0] - 2018-10-06
### Added
- Package created
