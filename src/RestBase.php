<?php

/**
 * https://developer.paypal.com/api/rest/
 */

declare(strict_types=1);

namespace Oct8pus\PayPal;

abstract class RestBase
{
    protected readonly string $baseUri;
    protected readonly HttpHandler $handler;
    private ?OAuth $auth;

    /**
     * Constructor
     *
     * @param bool        $sandbox - use PayPal sandbox/production
     * @param HttpHandler $handler
     * @param OAuth       $auth
     */
    public function __construct(bool $sandbox, HttpHandler $handler, ?OAuth $auth)
    {
        $this->baseUri = $sandbox ? 'https://api-m.sandbox.paypal.com' : 'https://api-m.paypal.com';
        $this->handler = $handler;
        $this->auth = $auth;
    }

    /**
     * Send request
     *
     * @param string        $method
     * @param string        $uri
     * @param array<string> $headers
     * @param ?string       $body
     * @param array|int     $expectedStatus
     *
     * @return string
     *
     * @throws PayPalException
     */
    protected function sendRequest(string $method, string $uri, array $headers, ?string $body, array|int $expectedStatus) : string
    {
        $request = $this->handler->createRequest($method, $this->baseUri . $uri, array_merge($this->headers(), $headers), $body);

        $response = $this->handler->sendRequest($request);

        return $this->handler->processResponse($response, $expectedStatus);
    }

    /**
     * Get headers
     *
     * @return array<string, string>
     */
    protected function headers() : array
    {
        return [
            'Authorization' => 'Bearer ' . $this->auth?->token(),
            'Content-Type' => 'application/json',
        ];
    }
}
