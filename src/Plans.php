<?php

/**
 * @reference https://developer.paypal.com/docs/api/subscriptions/v1/#plans_list
 */

declare(strict_types=1);

namespace Oct8pus\PayPal;

use Oct8pus\PayPal\Client;
use Oct8pus\PayPal\OAuth;

class Plans extends Client
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
     * List plans
     *
     * @return array
     *
     * @throws PayPalException
     */
    public function list() : array
    {
        $url = '/v1/billing/plans';

        $options = [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => [
                'Authorization: Bearer ' . $this->auth->token(),
                'Content-Type: application/json',
            ],
        ];

        $json = $this->curl($url, $options, 200);

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

        $options = [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => [
                'Authorization: Bearer ' . $this->auth->token(),
                'Content-Type: application/json',
            ],
        ];

        $json = $this->curl($url, $options, 200);

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

        $options = [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => [
                'Authorization: Bearer ' . $this->auth->token(),
                'Content-Type: application/json',
            ],
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => '',
        ];

        $this->curl($url, $options, 204);

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

        $options = [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => [
                'Authorization: Bearer ' . $this->auth->token(),
                'Content-Type: application/json',
            ],
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => '',
        ];

        $this->curl($url, $options, 204);

        return $this;
    }
}
