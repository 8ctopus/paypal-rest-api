<?php

/**
 * @reference https://developer.paypal.com/docs/api/subscriptions/v1/#subscriptions_get
 */

declare(strict_types=1);

namespace Oct8pus\PayPal;

use Oct8pus\PayPal\Client;
use Oct8pus\PayPal\OAuth;

class Subscription extends Client
{
    private OAuth $auth;

    /**
     * Constructor
     *
     * @param OAuth $auth - OAuth 2.0 token
     */
    public function __construct(OAuth $auth)
    {
        parent::__construct(true);

        $this->auth = $auth;
    }

    /**
     * Get info
     *
     * @param string $id
     *
     * @return array
     */
    public function get(string $id) : array
    {
        $url = "/v1/billing/subscriptions/{$id}";

        $options = [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => [
                'Authorization: Bearer ' . $this->auth->get(),
                'Content-Type: application/json',
            ],
        ];

        $json = $this->curl($url, $options, 200);

        return json_decode($json, true);
    }

    /**
     * Cancel
     *
     * @param string $id
     *
     * @return self
     */
    public function cancel(string $id) : self
    {
        $url = "/v1/billing/subscriptions/{$id}/cancel";

        $options = [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => [
                'Authorization: Bearer ' . $this->auth->get(),
                'Content-Type: application/json',
            ],
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => '',
        ];

        $this->curl($url, $options, 204);

        return $this;
    }
}
