<?php

declare(strict_types=1);

namespace Tests;

use Nimbly\Capsule\Factory\RequestFactory;
use Nimbly\Capsule\Factory\StreamFactory;
use Nimbly\Capsule\Response;
use Nimbly\Shuttle\Shuttle;
use Oct8pus\PayPal\PayPalException;
use Oct8pus\PayPal\Plans;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 *
 * @covers \Oct8pus\PayPal\Plans
 * @covers \Oct8pus\PayPal\RestBase
 */
final class PlansTest extends TestCase
{
    private static HttpHandlerMock $handler;
    private static OAuthMock $auth;
    private static Plans $plans;

    public static function setUpBeforeClass() : void
    {
        self::$handler = new HttpHandlerMock(new Shuttle(), new RequestFactory(), new StreamFactory());
        self::$auth = new OAuthMock(self::$handler, 'testId', 'testSecret');
        self::$plans = new Plans(true, self::$handler, self::$auth);
    }

    public function testConstructor() : void
    {
        self::assertInstanceOf(Plans::class, new Plans(true, self::$handler, self::$auth));
    }

    public function testList() : void
    {
        self::$handler->setResponse(new Response(200, file_get_contents(__DIR__ . '/fixtures/PlansList.json')));

        self::$plans->list();

        $expected = <<<'TEXT'
        https://api-m.sandbox.paypal.com/v1/billing/plans
        Host: api-m.sandbox.paypal.com
        Authorization: Bearer test
        Content-Type: application/json

        TEXT;

        self::assertSame($expected, self::$handler->dumpRequest());
    }

    public function testGet() : void
    {
        self::$handler->setResponse(new Response(200, file_get_contents(__DIR__ . '/fixtures/PlanDetails.json')));

        $id = 'P-5ML4271244454362WXNWU5NQ';

        self::$plans->get($id);

        $expected = <<<TEXT
        https://api-m.sandbox.paypal.com/v1/billing/plans/{$id}
        Host: api-m.sandbox.paypal.com
        Authorization: Bearer test
        Content-Type: application/json

        TEXT;

        self::assertSame($expected, self::$handler->dumpRequest());
    }

    public function testActivate() : void
    {
        self::$handler->setResponse(new Response(204, ''));

        $id = 'P-5ML4271244454362WXNWU5NQ';

        self::$plans->activate($id);

        $expected = <<<TEXT
        https://api-m.sandbox.paypal.com/v1/billing/plans/{$id}/activate
        Host: api-m.sandbox.paypal.com
        Authorization: Bearer test
        Content-Type: application/json

        TEXT;

        self::assertSame($expected, self::$handler->dumpRequest());
    }

    public function testDeactivate() : void
    {
        self::$handler->setResponse(new Response(204, ''));

        $id = 'P-5ML4271244454362WXNWU5NQ';

        self::$plans->deactivate($id);

        $expected = <<<TEXT
        https://api-m.sandbox.paypal.com/v1/billing/plans/{$id}/deactivate
        Host: api-m.sandbox.paypal.com
        Authorization: Bearer test
        Content-Type: application/json

        TEXT;

        self::assertSame($expected, self::$handler->dumpRequest());
    }

    public function testCreate() : void
    {
        self::$handler->setResponse(new Response(200, file_get_contents(__DIR__ . '/fixtures/PlanAdd.json')));

        self::expectException(PayPalException::class);
        self::expectExceptionMessage('not implemented');

        $plan = [
            'product_id' => '',
            'name' => '',
            'description' => '',
            'status' => '',
            'billing_cycles' => '',
            'payment_preferences' => '',
            'taxes' => '',
        ];

        self::$plans->create($plan);
    }
}
