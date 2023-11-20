<?php

/**
 * @reference https://developer.paypal.com/docs/api/subscriptions/v1/#plans_list
 */

declare(strict_types=1);

namespace Oct8pus\PayPal;

class Plans extends RestBase
{
    /**
     * Constructor
     *
     * @param HttpHandler $handler
     * @param OAuth       $auth
     */
    public function __construct(HttpHandler $handler, OAuth $auth)
    {
        parent::__construct(true, $handler, $auth);
    }

    /**
     * List plans
     *
     * @return array<mixed>
     *
     * @throws PayPalException
     */
    public function list() : array
    {
        $url = '/v1/billing/plans';

        $json = $this->sendRequest('GET', $url, [], null, 200);

        return json_decode($json, true)['plans'];
    }

    /**
     * Get plan
     *
     * @param string $id
     *
     * @return array<mixed>
     *
     * @throws PayPalException
     */
    public function get(string $id) : array
    {
        $url = "/v1/billing/plans/{$id}";

        $json = $this->sendRequest('GET', $url, [], null, 200);

        return json_decode($json, true);
    }

    /**
     * Add plan
     *
     * @param array<string> $plan
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

        $this->sendRequest('POST', $url, [], null, 204);

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

        $this->sendRequest('POST', $url, [], null, 204);

        return $this;
    }
}
