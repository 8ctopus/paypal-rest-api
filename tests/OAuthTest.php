<?php

declare(strict_types=1);

namespace Tests;

use Nimbly\Capsule\Factory\RequestFactory;
use Nimbly\Capsule\Factory\StreamFactory;
use Nimbly\Capsule\Response;
use Nimbly\Shuttle\Shuttle;
use Oct8pus\PayPal\OAuth;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 *
 * @covers \Oct8pus\PayPal\OAuth
 * @covers \Oct8pus\PayPal\RestBase
 */
final class OAuthTest extends TestCase
{
    private static HttpHandlerMock $handler;

    public static function setUpBeforeClass() : void
    {
        self::$handler = new HttpHandlerMock(new Shuttle(), new RequestFactory(), new StreamFactory());
    }

    public function testToken() : void
    {
        self::$handler->setResponse(new Response(200, file_get_contents(__DIR__ . '/fixtures/OAuth.json')));

        $token = (new OAuth(true, self::$handler, 'testId', 'testSecret'))
            ->token();

        self::assertSame('A21AAKhpstvuJ7TbYe3kZrQDlO-nmW_Dxby3W06pGsWNRGH447IxnAbWXTD_01X1PT_ISaoY-Fq7LI-VWDgxAUqPDYONyZ9fA', $token);

        $expected = <<<'TEXT'
        https://api-m.sandbox.paypal.com/v1/oauth2/token
        Host: api-m.sandbox.paypal.com
        Authorization: Basic dGVzdElkOnRlc3RTZWNyZXQ=
        Content-Type: application/x-www-form-urlencoded
        Accept: application/json
        grant_type=client_credentials
        TEXT;

        self::assertSame($expected, self::$handler->dumpRequest());
    }
}
