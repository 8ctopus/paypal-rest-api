<?php

declare(strict_types=1);

namespace Tests\OAuth;

use Oct8pus\PayPal\HttpHandler;
use Oct8pus\PayPal\OAuth\OAuth;

class OAuthMock extends OAuth
{
    public function __construct(HttpHandler $handler, string $clientId, string $clientSecret)
    {
        $this->token = 'test';
        $this->expires = time() + 3600;

        parent::__construct(true, $handler, $clientId, $clientSecret);
    }
}
