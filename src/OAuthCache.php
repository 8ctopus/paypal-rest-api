<?php

/**
 * https://developer.paypal.com/api/rest/
 */

declare(strict_types=1);

namespace Oct8pus\PayPal;

class OAuthCache extends OAuth
{
    private readonly string $file;

    /**
     * Constructor
     *
     * @param HttpHandler $handler
     * @param string         $clientId
     * @param string         $clientSecret
     * @param string         $cacheFile
     */
    public function __construct(HttpHandler $handler, string $clientId, string $clientSecret, string $cacheFile)
    {
        parent::__construct($handler, $clientId, $clientSecret);

        $this->file = $cacheFile;

        $this->load();
    }

    public function __destruct()
    {
        $this->save();
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
