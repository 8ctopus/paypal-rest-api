<?php

declare(strict_types=1);

namespace Tests;

use Nimbly\Capsule\Factory\RequestFactory;
use Nimbly\Capsule\Factory\StreamFactory;
use Nimbly\Capsule\Response;
use Nimbly\Shuttle\Shuttle;
use Oct8pus\PayPal\Subscriptions;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 *
 * @covers \Oct8pus\PayPal\RestBase
 * @covers \Oct8pus\PayPal\Subscriptions
 */
final class SubscriptionsTest extends TestCase
{
    private static HttpHandlerMock $handler;
    private static OAuthMock $auth;
    private static Subscriptions $subscriptions;

    public static function setUpBeforeClass() : void
    {
        self::$handler = new HttpHandlerMock(new Shuttle(), new RequestFactory(), new StreamFactory());
        self::$auth = new OAuthMock(self::$handler, 'testId', 'testSecret');
        self::$subscriptions = new Subscriptions(true, self::$handler, self::$auth);
    }

    public function testConstructor() : void
    {
        self::assertInstanceOf(Subscriptions::class, new Subscriptions(true, self::$handler, self::$auth));
    }

    public function testGet() : void
    {
        self::$handler->setResponse(new Response(200, file_get_contents(__DIR__ . '/fixtures/SubscriptionDetails.json')));

        $id = 'I-BW452GLLEP1G';

        self::$subscriptions->get($id);

        $expected = <<<TEXT
        https://api-m.sandbox.paypal.com/v1/billing/subscriptions/{$id}
        Host: api-m.sandbox.paypal.com
        Authorization: Bearer test
        Content-Type: application/json

        TEXT;

        self::assertSame($expected, self::$handler->dumpRequest());
    }

    public function testCreate() : void
    {
        self::$handler->setResponse(new Response(201, file_get_contents(__DIR__ . '/fixtures/SubscriptionCreate.json')));

        $id = 'P-5ML4271244454362WXNWU5NQ';

        self::$subscriptions->create($id, 'http://localhost/success/', 'http://localhost/cancel/');

        $expected = <<<TEXT
        https://api-m.sandbox.paypal.com/v1/billing/subscriptions
        Host: api-m.sandbox.paypal.com
        Authorization: Bearer test
        Content-Type: application/json

        TEXT;

        self::assertSame($expected, self::$handler->dumpRequest());
    }

    public function testCapture() : void
    {
        self::$handler->setResponse(new Response(202, ''));

        $id = 'I-MT4EHFSKC1U4';

        self::$subscriptions->capture($id, 'USD', 1, 'plan payment');

        $expected = <<<TEXT
        https://api-m.sandbox.paypal.com/v1/billing/subscriptions/{$id}/capture
        Host: api-m.sandbox.paypal.com
        Authorization: Bearer test
        Content-Type: application/json

        TEXT;

        self::assertSame($expected, self::$handler->dumpRequest());
    }

    public function testSuspend() : void
    {
        self::$handler->setResponse(new Response(204, ''));

        $id = 'I-BW452GLLEP1G';

        self::$subscriptions->suspend($id);

        $expected = <<<TEXT
        https://api-m.sandbox.paypal.com/v1/billing/subscriptions/{$id}/suspend
        Host: api-m.sandbox.paypal.com
        Authorization: Bearer test
        Content-Type: application/json

        TEXT;

        self::assertSame($expected, self::$handler->dumpRequest());
    }

    public function testActivate() : void
    {
        self::$handler->setResponse(new Response(204, ''));

        $id = 'I-BW452GLLEP1G';

        self::$subscriptions->activate($id);

        $expected = <<<TEXT
        https://api-m.sandbox.paypal.com/v1/billing/subscriptions/{$id}/activate
        Host: api-m.sandbox.paypal.com
        Authorization: Bearer test
        Content-Type: application/json

        TEXT;

        self::assertSame($expected, self::$handler->dumpRequest());
    }

    public function testCancel() : void
    {
        self::$handler->setResponse(new Response(204, ''));

        $id = 'I-BW452GLLEP1G';

        self::$subscriptions->cancel($id);

        $expected = <<<TEXT
        https://api-m.sandbox.paypal.com/v1/billing/subscriptions/{$id}/cancel
        Host: api-m.sandbox.paypal.com
        Authorization: Bearer test
        Content-Type: application/json

        TEXT;

        self::assertSame($expected, self::$handler->dumpRequest());
    }
}
