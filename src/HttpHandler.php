<?php

declare(strict_types=1);

namespace Oct8pus\PayPal;

use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamFactoryInterface;

class HttpHandler
{
    protected readonly ClientInterface $client;
    protected readonly RequestFactoryInterface $requestFactory;
    protected readonly StreamFactoryInterface $streamFactory;

    /**
     * Constructor
     *
     * @param ClientInterface         $client
     * @param RequestFactoryInterface $requestFactory
     * @param StreamFactoryInterface  $streamFactory
     */
    public function __construct(ClientInterface $client, RequestFactoryInterface $requestFactory, StreamFactoryInterface $streamFactory)
    {
        $this->client = $client;
        $this->requestFactory = $requestFactory;
        $this->streamFactory = $streamFactory;
    }

    /**
     * Send request
     *
     * @param string        $method
     * @param string        $uri
     * @param array<string> $headers
     * @param ?string       $body
     * @param int           $expectedStatus
     *
     * @return RequestInterface
     */
    public function createRequest(string $method, string $uri, array $headers, ?string $body) : RequestInterface
    {
        $request = $this->requestFactory->createRequest($method, $uri);

        foreach ($headers as $name => $value) {
            $request = $request->withHeader($name, $value);
        }

        if ($body !== null) {
            $stream = $this->streamFactory->createStream($body);
            $request = $request->withBody($stream);
        }

        return $request;
    }

    public function sendRequest(RequestInterface $request) : ResponseInterface
    {
        // @codeCoverageIgnoreStart
        // testing the http client is out of scope
        return $this->client->sendRequest($request);
        // @codeCoverageIgnoreEnd
    }

    public function processResponse(ResponseInterface $response, int|array $expectedStatus) : string
    {
        if (is_int($expectedStatus)) {
            $expectedStatus = [$expectedStatus];
        }

        $status = $response->getStatusCode();

        if (!in_array($status, $expectedStatus, true)) {
            $expected = implode(', ', $expectedStatus);
            throw new PayPalException("status {$status} - expected [{$expected}] - " . (string) $response->getBody());
        }

        return (string) $response->getBody();
    }
}
