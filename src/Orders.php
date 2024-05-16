<?php

/**
 * https://developer.paypal.com/docs/api/catalog-products/v1/#products_create
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
     * Create
     *
     * @param Intent $intent
     * @param string $currency
     * @param float  $amount
     *
     * @return array<mixed>
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
        ];

        $body = json_encode($order, JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR);

        $json = $this->sendRequest('POST', $url, [], $body, 201);

        return json_decode($json, true);
    }

    public function get(string $id) : array
    {
        $url = "/v2/checkout/orders/{$id}";

        $json = $this->sendRequest('GET', $url, [], null, 200);

        return json_decode($json, true);
    }
}
