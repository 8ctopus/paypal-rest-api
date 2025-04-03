<?php

declare(strict_types=1);

namespace Tests;

use Nimbly\Capsule\Factory\RequestFactory;
use Nimbly\Capsule\Factory\StreamFactory;
use Nimbly\Capsule\Response;
use Nimbly\Shuttle\Shuttle;
use Oct8pus\PayPal\Hooks;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Tests\OAuth\OAuthMock;

/**
 * @internal
 */
#[CoversClass(Hooks::class)]
final class HooksTest extends TestCase
{
    private static HttpHandlerMock $handler;
    private static OAuthMock $auth;
    private static Hooks $hooks;

    public static function setUpBeforeClass() : void
    {
        self::$handler = new HttpHandlerMock(new Shuttle(), new RequestFactory(), new StreamFactory());
        self::$auth = new OAuthMock(self::$handler, 'testId', 'testSecret');
        self::$hooks = new Hooks(true, self::$handler, self::$auth);
    }

    public function testConstructor() : void
    {
        self::assertInstanceOf(Hooks::class, new Hooks(true, self::$handler, self::$auth));
    }

    public function testList() : void
    {
        self::$handler->setResponse(new Response(200, file_get_contents(__DIR__ . '/fixtures/HooksList.json')));

        self::$hooks->list();

        $expected = <<<'TEXT'
        https://api-m.sandbox.paypal.com/v1/notifications/webhooks
        Host: api-m.sandbox.paypal.com
        Authorization: Bearer test
        Content-Type: application/json

        TEXT;

        self::assertSame($expected, self::$handler->dumpRequest());
    }

    public function testShow() : void
    {
        self::$handler->setResponse(new Response(200, file_get_contents(__DIR__ . '/fixtures/HookShow.json')));

        $id = '0EH40505U7160970P';

        self::$hooks->get($id);

        $expected = <<<TEXT
        https://api-m.sandbox.paypal.com/v1/notifications/webhooks/{$id}
        Host: api-m.sandbox.paypal.com
        Authorization: Bearer test
        Content-Type: application/json

        TEXT;

        self::assertSame($expected, self::$handler->dumpRequest());
    }

    public function testCreate() : void
    {
        self::$handler->setResponse(new Response(201, file_get_contents(__DIR__ . '/fixtures/HookCreate.json')));

        $url = 'https://example.com/example_webhook';

        $types = [
            'PAYMENT.AUTHORIZATION.CREATED',
            'PAYMENT.AUTHORIZATION.VOIDED',
        ];

        self::$hooks->create($url, $types);

        $expected = <<<'TEXT'
        https://api-m.sandbox.paypal.com/v1/notifications/webhooks
        Host: api-m.sandbox.paypal.com
        Authorization: Bearer test
        Content-Type: application/json
        {
            "url": "https:\/\/example.com\/example_webhook",
            "event_types": [
                {
                    "name": "PAYMENT.AUTHORIZATION.CREATED"
                },
                {
                    "name": "PAYMENT.AUTHORIZATION.VOIDED"
                }
            ]
        }
        TEXT;

        self::assertSame($expected, self::$handler->dumpRequest());
    }

    public function testDelete() : void
    {
        self::$handler->setResponse(new Response(204, ''));

        $id = '5GP028458E2496506';

        self::$hooks->delete($id);

        $expected = <<<TEXT
        https://api-m.sandbox.paypal.com/v1/notifications/webhooks/{$id}
        Host: api-m.sandbox.paypal.com
        Authorization: Bearer test
        Content-Type: application/json

        TEXT;

        self::assertSame($expected, self::$handler->dumpRequest());
    }

    public function testSimulate() : void
    {
        self::$handler->setResponse(new Response(202, file_get_contents(__DIR__ . '/fixtures/HookSimulate.json')));

        $id = '8PT597110X687430LKGECATA';

        self::$hooks->simulate($id, 'PAYMENT.AUTHORIZATION.CREATED');

        $expected = <<<'TEXT'
        https://api-m.sandbox.paypal.com/v1/notifications/simulate-event
        Host: api-m.sandbox.paypal.com
        Authorization: Bearer test
        Content-Type: application/json
        {
            "webhook_id": "8PT597110X687430LKGECATA",
            "event_type": "PAYMENT.AUTHORIZATION.CREATED",
            "resource_version": "1.0"
        }
        TEXT;

        self::assertSame($expected, self::$handler->dumpRequest());
    }

    public function testListEvents() : void
    {
        self::$handler->setResponse(new Response(200, file_get_contents(__DIR__ . '/fixtures/HookListEvents.json')));

        self::$hooks->listEvents('PAYMENT.SALE.COMPLETED', null, null, null, 10);

        $expected = <<<'TEXT'
        https://api-m.sandbox.paypal.com/v1/notifications/webhooks-events?page_size=10&event_type=PAYMENT.SALE.COMPLETED
        Host: api-m.sandbox.paypal.com
        Authorization: Bearer test
        Content-Type: application/json

        TEXT;

        self::assertSame($expected, self::$handler->dumpRequest());
    }
}
