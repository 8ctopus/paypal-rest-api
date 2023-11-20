# PayPal REST api

[![packagist](http://poser.pugx.org/8ctopus/paypal-rest-api/v)](https://packagist.org/packages/8ctopus/paypal-rest-api)
[![downloads](http://poser.pugx.org/8ctopus/paypal-rest-api/downloads)](https://packagist.org/packages/8ctopus/paypal-rest-api)
[![min php version](http://poser.pugx.org/8ctopus/paypal-rest-api/require/php)](https://packagist.org/packages/8ctopus/paypal-rest-api)
[![license](http://poser.pugx.org/8ctopus/paypal-rest-api/license)](https://packagist.org/packages/8ctopus/paypal-rest-api)
[![tests](https://github.com/8ctopus/paypal-rest-api/actions/workflows/tests.yml/badge.svg)](https://github.com/8ctopus/paypal-rest-api/actions/workflows/tests.yml)
![code coverage badge](https://raw.githubusercontent.com/8ctopus/paypal-rest-api/image-data/coverage.svg)
![lines of code](https://raw.githubusercontent.com/8ctopus/paypal-rest-api/image-data/lines.svg)

php implementation of PayPal's REST api using PSR-7, PSR-17 and PSR-18.

It is a work in progress and contributions are welcome. For now, it covers subscriptions (Products and Plans) and web hooks.

## install

    composer require 8ctopus/paypal-rest-api

## demo

Also run the `demo.php` to see what is possible.

```php
use HttpSoft\Message\RequestFactory;
use HttpSoft\Message\StreamFactory;
use Nimbly\Shuttle\Shuttle;
use Oct8pus\PayPal\Hooks;
use Oct8pus\PayPal\OAuth;
use Oct8pus\PayPal\HttpHandler;

require_once __DIR__ . '/vendor/autoload.php';

$sandbox = true;

$handler = new HttpHandler(
    // PSR-18 http client
    new Shuttle(),
    // PSR-17 request factory
    new RequestFactory(),
    // PSR-7 stream factory
    new StreamFactory()
);

// get oauth token
$auth = new OAuth($sandbox, $handler, 'rest.id', 'rest.pass');

// list webhooks
$webhooks = new Hooks($sandbox, $handler, $auth);
var_dump($webhooks->list());
```

## run tests

    composer test

# references

- PayPal REST api official documentation: https://developer.paypal.com/api/rest/
- PayPal REST archived SDK https://github.com/paypal/PayPal-PHP-SDK/
