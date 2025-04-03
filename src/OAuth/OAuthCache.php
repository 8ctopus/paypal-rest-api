<?php

declare(strict_types=1);

namespace Oct8pus\PayPal;

/**
 * Store the authentication token for future use in a file
 */
class OAuthCache extends OAuth
{
    protected string $file;

    /**
     * Constructor
     *
     * @param bool        $sandbox
     * @param HttpHandler $handler
     * @param string      $clientId
     * @param string      $clientSecret
     * @param string      $cacheFile
     */
    public function __construct(bool $sandbox, HttpHandler $handler, string $clientId, string $clientSecret, string $cacheFile)
    {
        parent::__construct($sandbox, $handler, $clientId, $clientSecret);

        $this->file = $cacheFile;
    }

    public function token() : string
    {
        if (!isset($this->token)) {
            $this->load($this->file);
        }

        return parent::token();
    }

    /**
     * Load token
     *
     * @param string $file
     *
     * @return bool
     */
    protected function load(string $file) : bool
    {
        if (!file_exists($file)) {
            return false;
        }

        $json = file_get_contents($file);

        if ($json === false) {
            return false;
        }

        $decoded = json_decode($json, true);

        if (!is_array($decoded)) {
            return false;
        }

        if ($this->clientId !== ($decoded['clientId'] ?? null)) {
            return false;
        }

        $this->token = $decoded['token'];
        $this->expires = (int) $decoded['expires'];

        return true;
    }

    /**
     * Save token
     *
     * @return void
     */
    protected function save() : void
    {
        $json = [
            'clientId' => $this->clientId,
            'token' => $this->token,
            'expires' => $this->expires,
        ];

        file_put_contents($this->file, json_encode($json, JSON_PRETTY_PRINT));
    }
}
