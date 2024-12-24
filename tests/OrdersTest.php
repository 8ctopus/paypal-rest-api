<?php

declare(strict_types=1);

namespace Tests;

use Nimbly\Capsule\Factory\RequestFactory;
use Nimbly\Capsule\Factory\StreamFactory;
use Nimbly\Capsule\Response;
use Nimbly\Shuttle\Shuttle;
use Oct8pus\PayPal\Orders;
use Oct8pus\PayPal\Orders\Intent;
use Oct8pus\PayPal\RestBase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(Orders::class)]
#[CoversClass(Intent::class)]
#[CoversClass(RestBase::class)]
final class OrdersTest extends TestCase
{
    private static HttpHandlerMock $handler;
    private static OAuthMock $auth;
    private static Orders $orders;

    public static function setUpBeforeClass() : void
    {
        self::$handler = new HttpHandlerMock(new Shuttle(), new RequestFactory(), new StreamFactory());
        self::$auth = new OAuthMock(self::$handler, 'testId', 'testSecret');
        self::$orders = new Orders(true, self::$handler, self::$auth);
    }

    public function testConstructor() : void
    {
        self::assertInstanceOf(Orders::class, new Orders(true, self::$handler, self::$auth));
    }

    public function testCreateCapture() : void
    {
        self::$handler->setResponse(new Response(201, file_get_contents(__DIR__ . '/fixtures/OrderCreateCapture.json')));

        self::$orders->create(Intent::Capture, 'USD', 10, 'http://localhost/success/', 'http://localhost/cancel/');

        $expected = <<<'TEXT'
        https://api-m.sandbox.paypal.com/v2/checkout/orders
        Host: api-m.sandbox.paypal.com
        Authorization: Bearer test
        Content-Type: application/json
        {
            "intent": "CAPTURE",
            "purchase_units": [
                {
                    "amount": {
                        "value": 10,
                        "currency_code": "USD"
                    }
                }
            ],
            "payment_source": {
                "paypal": {
                    "experience_context": {
                        "return_url": "http:\/\/localhost\/success\/",
                        "cancel_url": "http:\/\/localhost\/cancel\/"
                    }
                }
            }
        }
        TEXT;

        self::assertSame($expected, self::$handler->dumpRequest());
    }

    public function testCaptureSuccess() : void
    {
        self::$handler->setResponse(new Response(201, file_get_contents(__DIR__ . '/fixtures/OrderCapture.json')));

        $id = '30L74699WY872124E';

        self::$orders->capture($id);

        $expected = <<<TEXT
        https://api-m.sandbox.paypal.com/v2/checkout/orders/{$id}/capture
        Host: api-m.sandbox.paypal.com
        Authorization: Bearer test
        Content-Type: application/json

        TEXT;

        self::assertSame($expected, self::$handler->dumpRequest());
    }

    public function testGet() : void
    {
        self::$handler->setResponse(new Response(200, file_get_contents(__DIR__ . '/fixtures/OrderGet.json')));

        $id = '30L74699WY872124E';

        self::$orders->get($id);

        $expected = <<<TEXT
        https://api-m.sandbox.paypal.com/v2/checkout/orders/{$id}
        Host: api-m.sandbox.paypal.com
        Authorization: Bearer test
        Content-Type: application/json

        TEXT;

        self::assertSame($expected, self::$handler->dumpRequest());
    }

    public function testCreateAuthorize() : void
    {
        self::$handler->setResponse(new Response(201, file_get_contents(__DIR__ . '/fixtures/OrderCreateAuthorize.json')));

        self::$orders->create(Intent::fromLowerCase('authorize'), 'USD', 10);

        $expected = <<<'TEXT'
        https://api-m.sandbox.paypal.com/v2/checkout/orders
        Host: api-m.sandbox.paypal.com
        Authorization: Bearer test
        Content-Type: application/json
        {
            "intent": "AUTHORIZE",
            "purchase_units": [
                {
                    "amount": {
                        "value": 10,
                        "currency_code": "USD"
                    }
                }
            ]
        }
        TEXT;

        self::assertSame($expected, self::$handler->dumpRequest());
    }

    public function testAuthorize() : void
    {
        self::$handler->setResponse(new Response(201, file_get_contents(__DIR__ . '/fixtures/OrderAuthorize.json')));

        $id = '8AB97868H7440304S';

        self::$orders->authorize($id);

        $expected = <<<TEXT
        https://api-m.sandbox.paypal.com/v2/checkout/orders/{$id}/authorize
        Host: api-m.sandbox.paypal.com
        Authorization: Bearer test
        Content-Type: application/json

        TEXT;

        self::assertSame($expected, self::$handler->dumpRequest());
    }

    public function testTrack() : void
    {
        self::$handler->setResponse(new Response(201, file_get_contents(__DIR__ . '/fixtures/OrderTrack.json')));

        $id = '8AB97868H7440304S';

        self::$orders->track($id, 'DHL', '1234', '22Y6807431701101J', false);

        $expected = <<<TEXT
        https://api-m.sandbox.paypal.com/v2/checkout/orders/{$id}/track
        Host: api-m.sandbox.paypal.com
        Authorization: Bearer test
        Content-Type: application/json
        {
            "tracking_number": "1234",
            "carrier": "DHL",
            "notify_payer": "false",
            "capture_id": "22Y6807431701101J"
        }
        TEXT;

        self::assertSame($expected, self::$handler->dumpRequest());
    }
}
