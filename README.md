# PayPal REST API

[![packagist](https://poser.pugx.org/8ctopus/paypal-rest-api/v)](https://packagist.org/packages/8ctopus/paypal-rest-api)
[![downloads](https://poser.pugx.org/8ctopus/paypal-rest-api/downloads)](https://packagist.org/packages/8ctopus/paypal-rest-api)
[![min php version](https://poser.pugx.org/8ctopus/paypal-rest-api/require/php)](https://packagist.org/packages/8ctopus/paypal-rest-api)
[![license](https://poser.pugx.org/8ctopus/paypal-rest-api/license)](https://packagist.org/packages/8ctopus/paypal-rest-api)
[![tests](https://github.com/8ctopus/paypal-rest-api/actions/workflows/tests.yml/badge.svg)](https://github.com/8ctopus/paypal-rest-api/actions/workflows/tests.yml)
![code coverage badge](https://raw.githubusercontent.com/8ctopus/paypal-rest-api/image-data/coverage.svg)
![lines of code](https://raw.githubusercontent.com/8ctopus/paypal-rest-api/image-data/lines.svg)

A php implementation of the PayPal REST API using `PSR-7`, `PSR-17` and `PSR-18`.

The package is a work in progress and contributions are welcome. For now, it covers `Orders` (one-time payments), subscriptions (`Products`, `Plans` and `Subscriptions`), and `web hooks` (receive notifications from PayPal when certain events occur). That's all that's needed to create a store, be it one-time payment or subscription based.

## install package

    composer require 8ctopus/paypal-rest-api

## before you get started

Copy `.env.example` to `.env` and fill in your PayPal REST API credentials. If you don't have credentials yet, follow the guide:

    https://developer.paypal.com/api/rest/

## demo

Here's a code sample that shows how to make a one-time payment. To see all possibilites run `demo.php`. There is also a demo store using this package, check out [PayPal sandbox](https://github.com/8ctopus/paypal-sandbox).

```php
use HttpSoft\Message\RequestFactory;
use HttpSoft\Message\StreamFactory;
use Nimbly\Shuttle\Shuttle;
use Oct8pus\PayPal\Orders;
use Oct8pus\PayPal\Orders\Intent;
use Oct8pus\PayPal\OAuth;
use Oct8pus\PayPal\HttpHandler;

require_once __DIR__ . '/vendor/autoload.php';

$handler = new HttpHandler(
    // PSR-18 http client
    new Shuttle(),
    // PSR-17 request factory
    new RequestFactory(),
    // PSR-7 stream factory
    new StreamFactory()
);

$sandbox = true;

// get authorization token
$auth = new OAuth($sandbox, $handler, 'rest.id', 'rest.pass');

$orders = new Orders($sandbox, $handler, $auth);

// create order
$response = $orders->create(Intent::Capture, 'USD', 10.0);

// you must redirect the user to approve the payment before you can capture
$redirectUrl = "https://www.sandbox.paypal.com/checkoutnow?token={$response['id']}";

...

// once the user has approved the payment, capture it
$response = $orders->capture($args['id']);

if ($response['status'] === 'COMPLETED') {
    echo 'payment processed!';
}
```

## run tests

    composer test

# references

- PayPal REST API official documentation: https://developer.paypal.com/api/rest/
- PayPal REST archived php SDK https://github.com/paypal/PayPal-PHP-SDK/
