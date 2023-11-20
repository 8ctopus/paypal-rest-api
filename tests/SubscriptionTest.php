<?php

declare(strict_types=1);

namespace Tests;

use Nimbly\Capsule\Factory\RequestFactory;
use Nimbly\Capsule\Factory\StreamFactory;
use Nimbly\Capsule\Response;
use Nimbly\Shuttle\Shuttle;
use Oct8pus\PayPal\PayPalException;
use Oct8pus\PayPal\Subscription;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 *
 * @covers \Oct8pus\PayPal\Subscription
 */
final class SubscriptionTest extends TestCase
{
    private static HttpHandlerMock $handler;
    private static OAuthMock $auth;
    private static Subscription $subscription;

    public static function setUpBeforeClass() : void
    {
        self::$handler = new HttpHandlerMock(new Shuttle(), new RequestFactory(), new StreamFactory());
        self::$auth = new OAuthMock(self::$handler, 'testId', 'testSecret');
        self::$subscription = new Subscription(self::$handler, self::$auth);
    }

    public function testConstructor() : void
    {
        self::assertInstanceOf(Subscription::class, new Subscription(self::$handler, self::$auth));
    }

    public function testGet() : void
    {
        self::$handler->setResponse(new Response(200, file_get_contents(__DIR__ . '/fixtures/SubscriptionDetails.json')));

        $id = 'I-BW452GLLEP1G';

        self::$subscription->get($id);

        $expected = <<<TEXT
        https://api-m.sandbox.paypal.com/v1/billing/subscriptions/{$id}
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

        self::$subscription->suspend($id);

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

        self::$subscription->activate($id);

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

        self::$subscription->cancel($id);

        $expected = <<<TEXT
        https://api-m.sandbox.paypal.com/v1/billing/subscriptions/{$id}/cancel
        Host: api-m.sandbox.paypal.com
        Authorization: Bearer test
        Content-Type: application/json

        TEXT;

        self::assertSame($expected, self::$handler->dumpRequest());
    }
}
