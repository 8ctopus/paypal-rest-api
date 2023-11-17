<?php

/**
 * https://developer.paypal.com/api/rest/
 */

declare(strict_types=1);

namespace Oct8pus\PayPal;

abstract class RestBase
{
    protected readonly string $baseUri;
    protected readonly RequestHandler $handler;

    public function __construct(bool $sandbox, RequestHandler $handler)
    {
        $this->baseUri = $sandbox ? 'https://api-m.sandbox.paypal.com' : 'https://api-m.paypal.com';
        $this->handler = $handler;
    }

    protected function request(string $method, string $uri, array $headers, ?string $body, int $expectedStatus) : string
    {
        return $this->handler->request($method, $this->baseUri . $uri, $headers, $body, $expectedStatus);
    }
}
