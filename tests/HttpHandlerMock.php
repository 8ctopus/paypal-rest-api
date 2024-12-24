<?php

declare(strict_types=1);

namespace Tests;

use Oct8pus\PayPal\HttpHandler;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamFactoryInterface;

class HttpHandlerMock extends HttpHandler
{
    private RequestInterface $request;
    private ResponseInterface $response;

    public function __construct(ClientInterface $client, RequestFactoryInterface $requestFactory, StreamFactoryInterface $streamFactory)
    {
        parent::__construct($client, $requestFactory, $streamFactory);
    }

    public function sendRequest(RequestInterface $request) : ResponseInterface
    {
        $this->request = $request;

        return $this->response;
    }

    public function setResponse(ResponseInterface $response) : self
    {
        $this->response = $response;
        return $this;
    }

    public function dumpRequest() : string
    {
        $dump = (string) $this->request->getUri() . "\n";

        foreach ($this->request->getHeaders() as $name => $values) {
            $dump .= $name . ': ' . implode(', ', $values) . "\n";
        }

        $dump .= $this->request->getBody();

        return $dump;
    }
}
