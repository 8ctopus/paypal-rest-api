<?php

/**
 * https://developer.paypal.com/api/rest/
 */

declare(strict_types=1);

namespace Oct8pus\PayPal;

use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\StreamInterface;

abstract class Client
{
    protected readonly string $baseUri;
    protected readonly ClientInterface $client;
    protected readonly RequestFactoryInterface $requestFactory;
    protected readonly StreamInterface $stream;

    /**
     * Constructor
     *
     * @param bool                    $sandbox
     * @param ClientInterface         $client
     * @param RequestFactoryInterface $requestFactory
     * @param StreamInterface         $streamInterface
     */
    public function __construct(bool $sandbox, ClientInterface $client, RequestFactoryInterface $requestFactory, StreamInterface $stream)
    {
        $this->baseUri = $sandbox ? 'https://api-m.sandbox.paypal.com' : 'https://api-m.paypal.com';
        $this->client = $client;
        $this->requestFactory = $requestFactory;
        $this->stream = $stream;
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
        $request = $this->requestFactory->createRequest($method, "{$this->baseUri}{$url}");

        foreach ($headers as $name => $value) {
            $request = $request->withHeader($name, $value);
        }

        if ($body !== null) {
            $this->stream->write($body);
            $this->stream->rewind();

            $request = $request->withBody($this->stream);
        }

        $response = $this->client->sendRequest($request);

        $status = $response->getStatusCode();

        if ($status !== $expectedStatus) {
            throw new PayPalException("status {$status}, expected {$expectedStatus} - " . (string) $response->getBody());
        }

        return (string) $response->getBody();
    }
}
