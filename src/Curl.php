<?php

/**
 * https://developer.paypal.com/api/rest/
 */

declare(strict_types=1);

namespace Oct8pus\PayPal;

abstract class Curl
{
    protected readonly string $baseUri;

    /**
     * Constructor
     *
     * @param bool $sandbox
     */
    public function __construct(bool $sandbox)
    {
        $this->baseUri = $sandbox ? 'https://api-m.sandbox.paypal.com' : 'https://api-m.paypal.com';
    }

    /**
     * Curl
     *
     * @param string $url
     * @param array  $options
     * @param int    $expectedStatus
     *
     * @return string
     *
     * @throws PayPalException
     */
    public function curl(string $url, array $options, int $expectedStatus) : string
    {
        $curl = curl_init();

        if ($curl === false) {
            throw new PayPalException('curl init');
        }

        $options[CURLOPT_URL] = "{$this->baseUri}{$url}";

        curl_setopt_array($curl, $options);

        $response = curl_exec($curl);

        $status = curl_getinfo($curl, CURLINFO_RESPONSE_CODE);

        curl_close($curl);

        if ($response === false || $status !== $expectedStatus) {
            throw new PayPalException("curl - {$status}");
        }

        return $response;
    }
}
