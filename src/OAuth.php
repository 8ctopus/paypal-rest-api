<?php

/**
 * https://developer.paypal.com/api/rest/
 */

declare(strict_types=1);

namespace Oct8pus\PayPal;

use DateTime;
use DateTimeInterface;

class OAuth extends RestBase
{
    protected string $token;
    protected int $expires;
    protected readonly string $clientId;
    private readonly string $clientSecret;

    /**
     * Constructor
     *
     * @param bool        $sandbox
     * @param HttpHandler $handler
     * @param string      $clientId
     * @param string      $clientSecret
     */
    public function __construct(bool $sandbox, HttpHandler $handler, string $clientId, string $clientSecret)
    {
        parent::__construct($sandbox, $handler, null);

        $this->clientId = $clientId;
        $this->clientSecret = $clientSecret;
    }

    /**
     * Get token
     *
     * @return string
     *
     * @throws PayPalException
     */
    public function token() : string
    {
        if (isset($this->token)) {
            if (time() < $this->expires) {
                return $this->token;
            }
        }

        $url = '/v1/oauth2/token';

        $body = http_build_query([
            'grant_type' => 'client_credentials',
        ]);

        $json = $this->sendRequest('POST', $url, [], $body, 200);

        $decoded = json_decode($json, true);

        if (!array_key_exists('access_token', $decoded)) {
            throw new PayPalException('access token missing');
        }

        $this->token = $decoded['access_token'];

        $nonce = $decoded['nonce'];
        $nonce = substr($nonce, 0, strpos($nonce, 'Z') + 1);
        $nonce = DateTime::createFromFormat(DateTimeInterface::RFC3339, $nonce);

        if ($nonce === false) {
            throw new PayPalException("invalid date - {$nonce}");
        }

        $this->expires = $nonce->getTimestamp() + $decoded['expires_in'];

        $this->save();

        return $this->token;
    }

    protected function save() : void
    {
        // so that inherited classes can save token
    }

    protected function headers() : array
    {
        return [
            'Authorization' => 'Basic ' . base64_encode($this->clientId . ':' . $this->clientSecret),
            'Content-Type' => 'application/x-www-form-urlencoded',
            'Accept' => 'application/json',
        ];
    }
}
