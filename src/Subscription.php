<?php

/**
 * @reference https://developer.paypal.com/docs/api/subscriptions/v1/#subscriptions_get
 */

declare(strict_types=1);

namespace Oct8pus\PayPal;

class Subscription extends RestBase
{
    /**
     * Constructor
     *
     * @param RequestHandler $handler
     * @param OAuth          $auth
     */
    public function __construct(RequestHandler $handler, OAuth $auth)
    {
        parent::__construct(true, $handler, $auth);
    }

    /**
     * Get info
     *
     * @param string $id
     *
     * @return array<mixed>
     */
    public function get(string $id) : array
    {
        $url = "/v1/billing/subscriptions/{$id}";

        $json = $this->request('GET', $url, [], null, 200);

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

        $this->request('POST', $url, [], null, 204);

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

        $this->request('POST', $url, [], null, 204);

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

        $this->request('POST', $url, [], null, 204);

        return $this;
    }
}
