<?php

/**
 * @reference https://developer.paypal.com/docs/api/subscriptions/v1/#subscriptions_get
 */

declare(strict_types=1);

namespace Oct8pus\PayPal;

use Oct8pus\PayPal\OAuth;
use Oct8pus\PayPal\RequestHandler;
use Oct8pus\PayPal\RestBase;

class Subscription extends RestBase
{
    private OAuth $auth;

    /**
     * Constructor
     *
     * @param RequestHandler $handler
     * @param OAuth $auth - OAuth 2.0 token
     */
    public function __construct(RequestHandler $handler, OAuth $auth)
    {
        parent::__construct(true, $handler);

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

        $headers = [
            'Authorization' => 'Bearer ' . $this->auth->token(),
            'Content-Type' => 'application/json',
        ];

        $json = $this->request('GET', $url, $headers, null, 200);

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

        $headers = [
            'Authorization' => 'Bearer ' . $this->auth->token(),
            'Content-Type' => 'application/json',
        ];

        $this->request('POST', $url, $headers, null, 204);

        return $this;
    }

    /**
     * Suspend
     *
     * @param string $id
     *
     * @return self
     */
    public function suspend(string $id) : self
    {
        $url = "/v1/billing/subscriptions/{$id}/suspend";

        $headers = [
            'Authorization' => 'Bearer ' . $this->auth->token(),
            'Content-Type' => 'application/json',
        ];

        $this->request('POST', $url, $headers, null, 204);

        return $this;
    }

    /**
     * Activate
     *
     * @param string $id
     *
     * @return self
     */
    public function activate(string $id) : self
    {
        $url = "/v1/billing/subscriptions/{$id}/activate";

        $headers = [
            'Authorization' => 'Bearer ' . $this->auth->token(),
            'Content-Type' => 'application/json',
        ];

        $this->request('POST', $url, $headers, null, 204);

        return $this;
    }
}
