<?php

/**
 * https://developer.paypal.com/docs/api/orders/v2/
 */

declare(strict_types=1);

namespace Oct8pus\PayPal;

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
     * @param  string $id
     *
     * @return array
     */
    public function get(string $id) : array
    {
        $url = "/v2/checkout/orders/{$id}";

        $json = $this->sendRequest('GET', $url, [], null, 200);

        return json_decode($json, true);
    }

    /**
     * Create
     *
     * @param Intent $intent
     * @param string $currency
     * @param float  $amount
     *
     * @return array<mixed>
     *
     * @note in all cases, you will need to redirect the user to the rel approve url
     */
    public function create(Intent $intent, string $currency, float $amount) : array
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
            /*
            'payment_source' => [
                'paypal' => [
                    'experience_context' => [
                        "return_url" => "https://example.com/returnUrl",
                    ],
                ],
            ],
            */
        ];

        $body = json_encode($order, JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR);

        $json = $this->sendRequest('POST', $url, [], $body, [200, 201]);

        return json_decode($json, true);
    }

    /**
     * Capture payment for existing order
     *
     * @param  string $id order id
     *
     * @return array
     */
    public function capture(string $id) : array
    {
        $url = "/v2/checkout/orders/{$id}/capture";

        $json = $this->sendRequest('POST', $url, [], null, 201);

        return json_decode($json, true);
    }

    /**
     * Authorize payment for existing order
     *
     * @param  string $id order id
     *
     * @return array
     */
    public function authorize(string $id) : array
    {
        $url = "/v2/checkout/orders/{$id}/authorize";

        $json = $this->sendRequest('POST', $url, [], null, 201);

        return json_decode($json, true);
    }

    /**
     * Add tracking
     *
     * @param  string $id
     * @param  string $carrier
     * @param  string $trackingNumber
     * @param  string $captureId
     * @param  bool   $notifyPayer
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

        $body = json_encode($order, JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR);

        $json = $this->sendRequest('POST', $url, [], $body, 201);

        return json_decode($json, true);
    }
}
