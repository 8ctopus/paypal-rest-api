<?php

declare(strict_types=1);

namespace Oct8pus\PayPal;

use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
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
     * @param StreamInterface         $streamFactory
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
     * @param string  $method
     * @param string  $uri
     * @param array<string>   $headers
     * @param ?string $body
     * @param int     $expectedStatus
     *
     * @return string
     *
     * @throws PayPalException
     */
    public function request(string $method, string $uri, array $headers, ?string $body, int $expectedStatus) : string
    {
        $request = $this->requestFactory->createRequest($method, $uri);

        foreach ($headers as $name => $value) {
            $request = $request->withHeader($name, $value);
        }

        if ($body !== null) {
            $stream = $this->streamFactory->createStream($body);
            $request = $request->withBody($stream);
        }

        $response = $this->client->sendRequest($request);

        $status = $response->getStatusCode();

        if ($status !== $expectedStatus) {
            throw new PayPalException("status {$status}, expected {$expectedStatus} - " . (string) $response->getBody());
        }

        return (string) $response->getBody();
    }
}
