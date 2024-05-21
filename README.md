# PayPal REST api

[![packagist](http://poser.pugx.org/8ctopus/paypal-rest-api/v)](https://packagist.org/packages/8ctopus/paypal-rest-api)
[![downloads](http://poser.pugx.org/8ctopus/paypal-rest-api/downloads)](https://packagist.org/packages/8ctopus/paypal-rest-api)
[![min php version](http://poser.pugx.org/8ctopus/paypal-rest-api/require/php)](https://packagist.org/packages/8ctopus/paypal-rest-api)
[![license](http://poser.pugx.org/8ctopus/paypal-rest-api/license)](https://packagist.org/packages/8ctopus/paypal-rest-api)
[![tests](https://github.com/8ctopus/paypal-rest-api/actions/workflows/tests.yml/badge.svg)](https://github.com/8ctopus/paypal-rest-api/actions/workflows/tests.yml)
![code coverage badge](https://raw.githubusercontent.com/8ctopus/paypal-rest-api/image-data/coverage.svg)
![lines of code](https://raw.githubusercontent.com/8ctopus/paypal-rest-api/image-data/lines.svg)

A php implementation of PayPal REST api using `PSR-7`, `PSR-17` and `PSR-18`.

The package is a work in progress and contributions are welcome. For now, it covers subscriptions (`Products`, `Plans` and `Subscriptions`), `Orders` and `web hooks`. That's basically what's needed to create a store be it one-time payment or subscription based.

## install

    composer require 8ctopus/paypal-rest-api

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

- PayPal REST api official documentation: https://developer.paypal.com/api/rest/
- PayPal REST archived php SDK https://github.com/paypal/PayPal-PHP-SDK/
