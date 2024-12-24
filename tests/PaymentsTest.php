<?php

declare(strict_types=1);

namespace Tests;

use Nimbly\Capsule\Factory\RequestFactory;
use Nimbly\Capsule\Factory\StreamFactory;
use Nimbly\Capsule\Response;
use Nimbly\Shuttle\Shuttle;
use Oct8pus\PayPal\Payments;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(Payments::class)]
final class PaymentsTest extends TestCase
{
    private static HttpHandlerMock $handler;
    private static OAuthMock $auth;
    private static Payments $payments;

    public static function setUpBeforeClass() : void
    {
        self::$handler = new HttpHandlerMock(new Shuttle(), new RequestFactory(), new StreamFactory());
        self::$auth = new OAuthMock(self::$handler, 'testId', 'testSecret');
        self::$payments = new Payments(true, self::$handler, self::$auth);
    }

    public function testConstructor() : void
    {
        self::assertInstanceOf(Payments::class, new Payments(true, self::$handler, self::$auth));
    }

    /*
    // FIX ME - add sample json
    public function testGetAuthorized() : void
    {
        self::$handler->setResponse(new Response(200, file_get_contents(__DIR__ . '/fixtures/PaymentGetAuthorized.json')));

        $id = '30L74699WY872124E';

        self::$payments->getAuthorized($id);

        $expected = <<<TEXT
        https://api-m.sandbox.paypal.com/v2/payments/authorizations/{$id}
        Host: api-m.sandbox.paypal.com
        Authorization: Bearer test
        Content-Type: application/json

        TEXT;

        self::assertSame($expected, self::$handler->dumpRequest());
    }
    */

    public function testGetCaptured() : void
    {
        self::$handler->setResponse(new Response(200, file_get_contents(__DIR__ . '/fixtures/PaymentGetCaptured.json')));

        $id = '8FJ94262P5616910T';

        self::$payments->getCaptured($id);

        $expected = <<<TEXT
        https://api-m.sandbox.paypal.com/v2/payments/captures/{$id}
        Host: api-m.sandbox.paypal.com
        Authorization: Bearer test
        Content-Type: application/json

        TEXT;

        self::assertSame($expected, self::$handler->dumpRequest());
    }

    public function testGetRefunded() : void
    {
        self::$handler->setResponse(new Response(200, file_get_contents(__DIR__ . '/fixtures/PaymentGetRefunded.json')));

        $id = '1HG7183619108511N';

        self::$payments->getRefunded($id);

        $expected = <<<TEXT
        https://api-m.sandbox.paypal.com/v2/payments/refunds/{$id}
        Host: api-m.sandbox.paypal.com
        Authorization: Bearer test
        Content-Type: application/json

        TEXT;

        self::assertSame($expected, self::$handler->dumpRequest());
    }
}
