<?php

/**
 * https://developer.paypal.com/api/rest/
 */

declare(strict_types=1);

namespace Oct8pus\PayPal;

use Oct8pus\PayPal\Client;
use DateTime;
use DateTimeInterface;
use HttpSoft\Message\RequestFactory;
use Nimbly\Shuttle\Shuttle;

class OAuth extends Client
{
    private readonly string $clientId;
    private readonly string $clientSecret;

    private string $token;
    private int $expires;

    private readonly string $file;

    /**
     * Constructor
     *
     * @param string $clientId
     * @param string $clientSecret
     */
    public function __construct(string $clientId, string $clientSecret)
    {
        $shuttle = new Shuttle();
        $factory = new RequestFactory();

        parent::__construct(true, $shuttle, $factory);

        $this->clientId = $clientId;
        $this->clientSecret = $clientSecret;

        $this->file = sys_get_temp_dir() . '/paypal-oauth.json';

        $this->load();
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

        $headers = [
            'Authorization' => 'Basic ' . base64_encode($this->clientId . ':' . $this->clientSecret),
            'Content-Type: application/x-www-form-urlencoded',
            'Accept: application/json',
        ];

        $body = http_build_query(['grant_type' => 'client_credentials']);

        $json = $this->request('POST', $url, $headers, $body, 200);

        $decoded = json_decode($json, true);

        if (!array_key_exists('access_token', $decoded)) {
            throw new PayPalException('access token missing');
        }

        $this->token = $decoded['access_token'];

        $nonce = $decoded['nonce'];
        $nonce = substr($nonce, 0, strpos($nonce, 'Z') + 1);

        $this->expires = DateTime::createFromFormat(DateTimeInterface::RFC3339, $nonce)->getTimestamp() + $decoded['expires_in'];

        $this->save();

        return $this->token;
    }

    /**
     * Load token from file
     *
     * @return void
     */
    private function load() : void
    {
        if (!file_exists($this->file)) {
            return;
        }

        $json = file_get_contents($this->file);

        if ($json === false) {
            return;
        }

        $decoded = json_decode($json, true);

        if ($decoded === false) {
            return;
        }

        $this->token = $decoded['token'];
        $this->expires = (int) $decoded['expires'];
    }

    /**
     * Save token to file
     *
     * @return void
     */
    private function save() : void
    {
        $json = [
            'token' => $this->token,
            'expires' => $this->expires,
        ];

        file_put_contents($this->file, json_encode($json, JSON_PRETTY_PRINT));
    }
}
