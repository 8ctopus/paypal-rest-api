<?php

declare(strict_types=1);

namespace Oct8pus\PayPal\OAuth;

use Oct8pus\PayPal\HttpHandler;
use Oct8pus\PayPal\PayPalException;

/**
 * Store the authentication token for future use in a file in encrypted form
 */
class OAuthSecureCache extends OAuthCache
{
    private readonly string $encryptionKey;

    /**
     * Constructor
     *
     * @param bool        $sandbox
     * @param HttpHandler $handler
     * @param string      $clientId
     * @param string      $clientSecret
     * @param string      $cacheFile
     * @param string      $encryptionKey
     */
    public function __construct(
        bool $sandbox,
        HttpHandler $handler,
        string $clientId,
        string $clientSecret,
        string $cacheFile,
        string $encryptionKey
    ) {
        $this->encryptionKey = $encryptionKey;

        parent::__construct($sandbox, $handler, $clientId, $clientSecret, $cacheFile);
    }

    /**
     * Load and decrypt token
     *
     * @param string $file
     *
     * @return bool
     */
    protected function load(string $file) : bool
    {
        if (!parent::load($file)) {
            return false;
        }

        $encrypted = base64_decode($this->token, true);

        if ($encrypted === false) {
            return false;
        }

        $iv = substr($encrypted, 0, 16);
        $ciphertext = substr($encrypted, 16);

        $decrypted = openssl_decrypt($ciphertext, 'AES-256-CBC', $this->encryptionKey, OPENSSL_RAW_DATA, $iv);

        if ($decrypted === false) {
            return false;
        }

        $this->token = $decrypted;
        return true;
    }

    /**
     * Save token
     *
     * @return void
     */
    protected function save() : void
    {
        $iv = random_bytes(16);

        $encrypted = openssl_encrypt($this->token, 'AES-256-CBC', $this->encryptionKey, OPENSSL_RAW_DATA, $iv);

        if ($encrypted === false) {
            throw new PayPalException('encrypt token');
        }

        $json = [
            'clientId' => $this->clientId,
            'token' => base64_encode($iv . $encrypted),
            'expires' => $this->expires,
        ];

        file_put_contents($this->file, json_encode($json, JSON_PRETTY_PRINT));
    }
}
