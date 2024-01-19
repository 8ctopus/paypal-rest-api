<?php

declare(strict_types=1);

namespace Oct8pus\PayPal\Plans;

use stdClass;

class PricingScheme
{
    private readonly float $value;
    private readonly string $currencyCode;

    /**
     * Constructor
     *
     * @param float  $value
     * @param string $currencyCode
     */
    public function __construct(float $value, string $currencyCode)
    {
        $this->value = $value;
        $this->currencyCode = $currencyCode;
    }

    public function object() : stdClass
    {
        /*
        "pricing_scheme": {
            "fixed_price": {
                "value": "3",
                "currency_code": "USD"
            }
        }
        */

        $object = new stdClass();

        $object->pricing_scheme = [
            'fixed_price' => [
                'value' => $this->value,
                'currency_code' => $this->currencyCode,
            ],
        ];

        return $object;
    }
}
