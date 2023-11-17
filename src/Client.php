<?php

/**
 * https://developer.paypal.com/api/rest/
 */

declare(strict_types=1);

namespace Oct8pus\PayPal;

use HttpSoft\Message\Stream;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;

abstract class Client
{
    protected readonly string $baseUri;
    protected readonly ClientInterface $clientInterface;
    protected readonly RequestFactoryInterface $requestFactoryInterface;

    /**
     * Constructor
     *
     * @param bool $sandbox
     */
    public function __construct(bool $sandbox, ClientInterface $clientInterface, RequestFactoryInterface $requestFactoryInterface)
    {
        $this->baseUri = $sandbox ? 'https://api-m.sandbox.paypal.com' : 'https://api-m.paypal.com';
        $this->clientInterface = $clientInterface;
        $this->requestFactoryInterface = $requestFactoryInterface;
    }

    /**
     * Send request
     *
     * @param  string  $method
     * @param  string  $url
     * @param  array   $headers
     * @param  ?string $body
     * @param  int     $expectedStatus
     *
     * @return string
     *
     * @throws PayPalException
     */
    public function request(string $method, string $url, array $headers, ?string $body, int $expectedStatus) : string
    {
        $request = $this->requestFactoryInterface->createRequest($method, "{$this->baseUri}{$url}");

        foreach ($headers as $name => $value) {
            $request = $request->withHeader($name, $value);
        }

        if ($body !== null) {
            $stream = new Stream();
            $stream->write($body);

            $request = $request->withBody($stream);
        }

        $response = $this->clientInterface->sendRequest($request);

        $status = $response->getStatusCode();

        if ($status !== $expectedStatus) {
            throw new PayPalException("status {$status}, expected {$expectedStatus} - " . (string) $response->getBody());
        }

        return (string) $response->getBody();
    }
}
