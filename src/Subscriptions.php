<?php

/**
 * @reference https://developer.paypal.com/docs/api/subscriptions/v1/#subscriptions_get
 */

declare(strict_types=1);

namespace Oct8pus\PayPal;

use DateInterval;
use DateTime;
use DateTimeInterface;

class Subscriptions extends RestBase
{
    /**
     * Constructor
     *
     * @param bool        $sandbox
     * @param HttpHandler $handler
     * @param OAuth       $auth
     */
    public function __construct(bool $sandbox, HttpHandler $handler, OAuth $auth)
    {
        parent::__construct($sandbox, $handler, $auth);
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

        $response = $this->sendRequest('GET', $url, [], null, 200);

        return json_decode($response, true);
    }

    /**
     * Create subscription
     *
     * @param string $planId
     * @param string $successUrl
     * @param string $cancelUrl
     *
     * @return array
     *
     * @note You must redirect the customer to PayPal to approve the subscription
     */
    public function create(string $planId, string $successUrl, string $cancelUrl) : array
    {
        $url = '/v1/billing/subscriptions';

        $subscription = [
            'plan_id' => $planId,
            //'quantity' => 1,
            'application_context' => [
                //'brand_name' => 'walmart',
                'locale' => 'en-US',
                /*
                'shipping_preference' => 'SET_PROVIDED_ADDRESS',
                'user_action' => 'SUBSCRIBE_NOW',
                'payment_method' => [
                    'payer_selected' => 'PAYPAL',
                    'payee_preferred' => 'IMMEDIATE_PAYMENT_REQUIRED',
                ],
                */
                'return_url' => $successUrl,
                'cancel_url' => $cancelUrl,
            ],
        ];

        $response = $this->sendJsonRequest('POST', $url, [], $subscription, 201);

        return json_decode($response, true);
    }

    /**
     * Capture payment
     *
     * @param  string $id
     * @param  string $currency
     * @param  float  $amount
     * @param  string $note
     *
     * @return self
     */
    public function capture(string $id, string $currency, float $amount, string $note) : self
    {
        $url = "/v1/billing/subscriptions/{$id}/capture";

        $capture = [
            'capture_type' => 'OUTSTANDING_BALANCE',
            'amount' => [
                'currency_code' => $currency,
                'value' => $amount,
            ],
            'note' => $note,
        ];

        $this->sendJsonRequest('POST', $url, [], $capture, 202);

        return $this;
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

        $this->sendRequest('POST', $url, [], null, 204);

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

        $this->sendRequest('POST', $url, [], null, 204);

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

        $this->sendRequest('POST', $url, [], null, 204);

        return $this;
    }

    /**
     * List transactions
     *
     * @param string $id
     * @param ?DateTime $start
     * @param ?DateTime $end
     *
     * @return array<mixed>
     */
    public function listTransactions(string $id, ?DateTime $start = null, ?DateTime $end = null) : array
    {
        $url = "/v1/billing/subscriptions/{$id}/transactions";

        if (!$end) {
            $end = new DateTime('now');
        }

        if (!$start) {
            $start = new DateTime('2020-01-01');
        }

        $params = [
            'start_time' => $start->format(DateTimeInterface::RFC3339),
            'end_time' => $end->format(DateTimeInterface::RFC3339),
        ];

        $url .= '?' . http_build_query($params);

        $response = $this->sendRequest('GET', $url, [], null, 200);

        return json_decode($response, true);
    }
}
