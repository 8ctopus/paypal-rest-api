<?php

/**
 * @reference https://developer.paypal.com/docs/api/subscriptions/v1/#plans_list
 */

declare(strict_types=1);

namespace Oct8pus\PayPal;

use Oct8pus\PayPal\RestBase;
use Oct8pus\PayPal\OAuth;

class Plans extends RestBase
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
     * List plans
     *
     * @return array
     *
     * @throws PayPalException
     */
    public function list() : array
    {
        $url = '/v1/billing/plans';

        $headers = [
            'Authorization' => 'Bearer ' . $this->auth->token(),
            'Content-Type' => 'application/json',
        ];

        $json = $this->request('GET', $url, $headers, null, 200);

        return json_decode($json, true)['plans'];
    }

    /**
     * Get plan
     *
     * @param string $id
     *
     * @return array
     *
     * @throws PayPalException
     */
    public function get(string $id) : array
    {
        $url = "/v1/billing/plans/{$id}";

        $headers = [
            'Authorization' => 'Bearer ' . $this->auth->token(),
            'Content-Type' => 'application/json',
        ];

        $json = $this->request('GET', $url, $headers, null, 200);

        return json_decode($json, true);
    }

    /**
     * Add plan
     *
     * @param array $plan
     *
     * @return self
     *
     * @throws PayPalException
     */
    public function add(array $plan) : self
    {
        $keys = [
            'product_id',
            'name',
            'description',
            'status',
            'billing_cycles',
            'payment_preferences',
            'taxes',
        ];

        foreach ($keys as $key) {
            if (!array_key_exists($key, $plan)) {
                throw new PayPalException("missing key {$key}");
            }
        }

        throw new PayPalException('not implemented');
    }

    /**
     * Activate plan
     *
     * @param string $id
     *
     * @return self
     *
     * @throws PayPalException
     */
    public function activate(string $id) : self
    {
        $url = "/v1/billing/plans/{$id}/activate";

        $headers = [
            'Authorization' => 'Bearer ' . $this->auth->token(),
            'Content-Type' => 'application/json',
        ];

        $this->request('POST', $url, $headers, null, 204);

        return $this;
    }

    /**
     * Deactivate plan
     *
     * @param string $id
     *
     * @return self
     *
     * @throws PayPalException
     */
    public function deactivate(string $id) : self
    {
        $url = "/v1/billing/plans/{$id}/deactivate";

        $headers = [
            'Authorization' => 'Bearer ' . $this->auth->token(),
            'Content-Type' => 'application/json',
        ];

        $this->request('POST', $url, $headers, null, 204);

        return $this;
    }
}
