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
    private ?OAuth $auth;

    public function __construct(bool $sandbox, RequestHandler $handler, ?OAuth $auth)
    {
        $this->baseUri = $sandbox ? 'https://api-m.sandbox.paypal.com' : 'https://api-m.paypal.com';
        $this->handler = $handler;
        $this->auth = $auth;
    }

    protected function request(string $method, string $uri, array $headers, ?string $body, int $expectedStatus) : string
    {
        return $this->handler->request($method, $this->baseUri . $uri, array_merge($this->headers(), $headers), $body, $expectedStatus);
    }

    protected function headers() : array
    {
        return [
            'Authorization' => 'Bearer ' . $this->auth->token(),
            'Content-Type' => 'application/json',
        ];
    }
}
