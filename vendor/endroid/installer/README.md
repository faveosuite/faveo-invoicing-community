# Installer

*By [endroid](https://endroid.nl/)*

[![Latest Stable Version](http://img.shields.io/packagist/v/endroid/installer.svg)](https://packagist.org/packages/endroid/installer)
[![Build Status](https://github.com/endroid/installer/workflows/CI/badge.svg)](https://github.com/endroid/installer/actions)
[![Total Downloads](http://img.shields.io/packagist/dt/endroid/installer.svg)](https://packagist.org/packages/endroid/installer)
[![Monthly Downloads](http://img.shields.io/packagist/dm/endroid/installer.svg)](https://packagist.org/packages/endroid/installer)
[![License](http://img.shields.io/packagist/l/endroid/installer.svg)](https://packagist.org/packages/endroid/installer)

Composer plugin for installing configuration files. The installer automatically
detects the project type in which your library is installed and installs the
corresponding configuration files from your package.

Read the [blog](https://medium.com/@endroid/auto-package-configuration-for-symfony-e14780e29d81)
for more information on why I created this plugin.

## Installation

``` bash
composer config allow-plugins.endroid/installer true
composer require endroid/installer
```

## Usage

Add the configuration files you want to be copied upon installation and update
of the package to the .install directory in the root of your package. The files
will be copied to the corresponding directories in the project.

```
.install
    symfony
        config
            packages
                package_name.yaml
            routes
                package_name.yaml
```

Please note that the installer will only copy files that are not yet present in
the project to make sure user made changes will not be overwritten. If you want
the latest default configuration just remove the files locally before update.

## Disabling auto installation for a package

Generally you want the files to be installed automatically but if you
experience issues with the installer or just don't want some package to be
auto installed you can specify this via your composer.json.

```
"extra": {
    "endroid": {
        "installer": {
            "enabled": false,
            "exclude": [
                "endroid/asset",
                "endroid/embed"
            ]
        }
    }
}
```

## Versioning

Version numbers follow the MAJOR.MINOR.PATCH scheme. Backwards compatible
changes will be kept to a minimum but be aware that these can occur. Lock
your dependencies for production and test your code when upgrading.

## License

This bundle is under the MIT license. For the full copyright and license
information please view the LICENSE file that was distributed with this source code.
