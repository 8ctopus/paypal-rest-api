<?php

/**
 * @reference https://developer.paypal.com/docs/api/subscriptions/v1/#subscriptions_get
 */

declare(strict_types=1);

namespace Oct8pus\PayPal;

use DateTime;
use DateTimeInterface;
use Oct8pus\PayPal\OAuth\OAuth;

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
     * @param string $billingAgreement
     *
     * @return array<mixed>
     */
    public function get(string $billingAgreement) : array
    {
        $url = "/v1/billing/subscriptions/{$billingAgreement}";

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
     * @param string $billingAgreement
     * @param string $currency
     * @param float  $amount
     * @param string $note
     *
     * @return self
     */
    public function capture(string $billingAgreement, string $currency, float $amount, string $note) : self
    {
        $url = "/v1/billing/subscriptions/{$billingAgreement}/capture";

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
     * @param string $billingAgreement
     * @param string $reason
     *
     * @return self
     */
    public function cancel(string $billingAgreement, string $reason) : self
    {
        $url = "/v1/billing/subscriptions/{$billingAgreement}/cancel";

        $body = [
            'reason' => $reason,
        ];

        $body = json_encode($body, JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR);

        $this->sendRequest('POST', $url, [], $body, 204);

        return $this;
    }

    /**
     * Suspend
     *
     * @param string $billingAgreement
     *
     * @return self
     */
    public function suspend(string $billingAgreement) : self
    {
        $url = "/v1/billing/subscriptions/{$billingAgreement}/suspend";

        $this->sendRequest('POST', $url, [], null, 204);

        return $this;
    }

    /**
     * Activate
     *
     * @param string $billingAgreement
     *
     * @return self
     */
    public function activate(string $billingAgreement) : self
    {
        $url = "/v1/billing/subscriptions/{$billingAgreement}/activate";

        $this->sendRequest('POST', $url, [], null, 204);

        return $this;
    }

    /**
     * List transactions
     *
     * @param string    $billingAgreement
     * @param ?DateTime $start
     * @param ?DateTime $end
     *
     * @return ?array<mixed>
     */
    public function listTransactions(string $billingAgreement, ?DateTime $start = null, ?DateTime $end = null) : ?array
    {
        $url = "/v1/billing/subscriptions/{$billingAgreement}/transactions";

        if (!$end) {
            $end = new DateTime('now');
        }

        if (!$start) {
            $start = new DateTime('2020-01-01');
        }

        $params = [
            // both RFC3339 and RFC3339 extended work
            'start_time' => $start->format(DateTimeInterface::RFC3339),
            'end_time' => $end->format(DateTimeInterface::RFC3339),
        ];

        $url .= '?' . http_build_query($params);

        $response = $this->sendRequest('GET', $url, [], null, 200);
        $response = json_decode($response, true);

        return $response['transactions'] ?? null;
    }
}
