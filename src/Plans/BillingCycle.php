<?php

declare(strict_types=1);

namespace Oct8pus\PayPal\Plans;

use stdClass;

enum TenureType : string
{
    case Regular = 'REGULAR';
    case Trial = 'TRIAL';
}

class BillingCycle
{
    /*
    {
        "sequence": 1,
        "frequency": {
            "interval_unit": "MONTH",
            "interval_count": 1
        },
        "tenure_type": "TRIAL",
        "total_cycles": 2,
        "pricing_scheme": {
            "fixed_price": {
                "value": "3",
                "currency_code": "USD"
            }
        }
    },
    */

    private readonly TenureType $tenureType;
    private readonly Frequency $frequency;
    private readonly int $totalCycles;
    private readonly PricingScheme $pricingScheme;

    public function __construct(TenureType $tenureType, Frequency $frequency, int $totalCycles, PricingScheme $pricingScheme)
    {
        $this->tenureType = $tenureType;
        $this->frequency = $frequency;
        $this->totalCycles = $totalCycles;
        $this->pricingScheme = $pricingScheme;
    }

    public function object(int $sequence) : stdClass
    {
        $object = $this->frequency->object();

        $object->tenure_type = $this->tenureType->value;
        $object->sequence = $sequence;
        $object->total_cycles = $this->totalCycles;

        $object = (object) array_merge((array) $object, (array) $this->pricingScheme->object());

        return $object;
    }
}
