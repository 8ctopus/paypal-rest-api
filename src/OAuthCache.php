<?php

declare(strict_types=1);

namespace Oct8pus\PayPal;

class OAuthCache extends OAuth
{
    private readonly string $file;

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

        if (file_exists($this->file)) {
            $this->load($this->file);
        }
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

    /**
     * Load token
     *
     * @param string $file
     *
     * @return void
     */
    private function load(string $file) : void
    {
        $json = file_get_contents($file);

        if ($json === false) {
            return;
        }

        $decoded = json_decode($json, true);

        if (!is_array($decoded)) {
            return;
        }

        if ($this->clientId !== ($decoded['clientId'] ?? '')) {
            return;
        }

        $this->token = $decoded['token'];
        $this->expires = (int) $decoded['expires'];
    }
}
