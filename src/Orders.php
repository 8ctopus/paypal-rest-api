<?php

/**
 * https://developer.paypal.com/docs/api/orders/v2/
 */

declare(strict_types=1);

namespace Oct8pus\PayPal;

use Oct8pus\PayPal\OAuth\OAuth;
use Oct8pus\PayPal\Orders\Intent;

class Orders extends RestBase
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
     * Get order
     *
     * @param string $id
     *
     * @return array
     */
    public function get(string $id) : array
    {
        $url = "/v2/checkout/orders/{$id}";

        $response = $this->sendRequest('GET', $url, [], null, 200);

        return json_decode($response, true);
    }

    /**
     * Create
     *
     * @param Intent  $intent
     * @param string  $currency
     * @param float   $amount
     * @param ?string $returnUrl
     * @param ?string $cancelUrl
     *
     * @return array<mixed>
     *
     * @note in all cases, you will need to redirect the user to the rel approve url
     */
    public function create(Intent $intent, string $currency, float $amount, ?string $returnUrl = null, ?string $cancelUrl = null) : array
    {
        $url = '/v2/checkout/orders';

        $order = [
            'intent' => $intent->value,
            'purchase_units' => [
                [
                    'amount' => [
                        'value' => $amount,
                        'currency_code' => $currency,
                    ],
                ],
            ],
        ];

        if (isset($returnUrl)) {
            $order['payment_source'] = [
                'paypal' => [
                    'experience_context' => [
                        'return_url' => $returnUrl,
                        'cancel_url' => $cancelUrl,
                    ],
                ],
            ];
        }

        $response = $this->sendJsonRequest('POST', $url, [], $order, [200, 201]);

        return json_decode($response, true);
    }

    /**
     * Capture payment for existing order
     *
     * @param string $id order id
     *
     * @return array
     */
    public function capture(string $id) : array
    {
        $url = "/v2/checkout/orders/{$id}/capture";

        $response = $this->sendRequest('POST', $url, [], null, 201);

        return json_decode($response, true);
    }

    /**
     * Authorize payment for existing order
     *
     * @param string $id order id
     *
     * @return array
     */
    public function authorize(string $id) : array
    {
        $url = "/v2/checkout/orders/{$id}/authorize";

        $response = $this->sendRequest('POST', $url, [], null, 201);

        return json_decode($response, true);
    }

    /**
     * Add tracking
     *
     * @param string $id
     * @param string $carrier
     * @param string $trackingNumber
     * @param string $captureId
     * @param bool   $notifyPayer
     *
     * @return array
     */
    public function track(string $id, string $carrier, string $trackingNumber, string $captureId, bool $notifyPayer) : array
    {
        $url = "/v2/checkout/orders/{$id}/track";

        $order = [
            'tracking_number' => $trackingNumber,
            'carrier' => $carrier,
            // carrier_name_other
            'notify_payer' => $notifyPayer ? 'true' : 'false',
            'capture_id' => $captureId,
            // items
        ];

        $response = $this->sendJsonRequest('POST', $url, [], $order, 201);

        return json_decode($response, true);
    }
}
