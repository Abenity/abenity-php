# PHP Client for Abenity API

A PHP library for using the Abenity API.

You can find the full API documentation in the [official documentation](https://abenity.com/developers/api/members).

## Requirements

This library works with PHP 5.6 and PHP 7 and requires the [OpenSSL module](https://www.php.net/manual/en/book.openssl.php)

## Installation

### With Composer

[`abenity/abenity-php`](http://packagist.org/packages/abenity/abenity-php) package is available on [Packagist](http://packagist.org).

Include it in your `composer.json` as follows:

1. Add abenity/abenity-php as a dependency in composer.json.

    ```
    composer require abenity/abenity-php:^2
    ```

3. Now `Abenity` will be autoloaded into your project.

    ```
    require 'vendor/autoload.php';

    $abenity = new Abenity\ApiClient('my_api_key','my_api_username','my_api_password');
    ```

### Manually

1. Download the latest release.

2. Extract into a folder in your project root named "abenity-php".

3. Include `Abenity` in your project like this:

    ```
    require 'abenity-php/lib/Abenity.php';

    $abenity = new Abenity\ApiClient('my_api_key','my_api_username','my_api_password');
    ```

## Usage

### See examples folder

## Contributing

Run the tests from the project root with [PHPUnit](http://phpunit.de) like this:

```
[abenity-php]# phpunit tests
```
