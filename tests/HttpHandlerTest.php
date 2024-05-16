<?php

declare(strict_types=1);

namespace Tests;

use Nimbly\Capsule\Factory\RequestFactory;
use Nimbly\Capsule\Factory\StreamFactory;
use Nimbly\Capsule\Response;
use Nimbly\Shuttle\Shuttle;
use Oct8pus\PayPal\HttpHandler;
use Oct8pus\PayPal\PayPalException;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 *
 * @covers \Oct8pus\PayPal\HttpHandler
 */
final class HttpHandlerTest extends TestCase
{
    public function testCreateRequest() : void
    {
        $handler = new HttpHandler(new Shuttle(), new RequestFactory(), new StreamFactory());

        $headers = [
            'Authorization' => 'Basic ' . base64_encode('test:test'),
            'Content-Type' => 'application/x-www-form-urlencoded',
            'Accept' => 'application/json',
        ];

        $request = $handler->createRequest('GET', 'http://localhost/', $headers, 'test');

        $dump = (string) $request->getUri() . "\n";

        foreach ($request->getHeaders() as $name => $values) {
            $dump .= $name . ': ' . implode(', ', $values) . "\n";
        }

        $expected = <<<'TEXT'
        http://localhost/
        Host: localhost
        Authorization: Basic dGVzdDp0ZXN0
        Content-Type: application/x-www-form-urlencoded
        Accept: application/json

        TEXT;

        self::assertSame($expected, $dump);
    }

    public function testProcessResponseOK() : void
    {
        $handler = new HttpHandler(new Shuttle(), new RequestFactory(), new StreamFactory());

        $response = new Response(200, 'test');

        $result = $handler->processResponse($response, 200);

        self::assertSame('test', $result);
    }

    public function testProcessResponseFail() : void
    {
        $handler = new HttpHandler(new Shuttle(), new RequestFactory(), new StreamFactory());

        self::expectException(PayPalException::class);
        self::expectExceptionMessage('status 200 - expected [201] - test');

        $response = new Response(200, 'test');
        $handler->processResponse($response, 201);
    }
}
