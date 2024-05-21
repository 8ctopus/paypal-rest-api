<?php

declare(strict_types=1);

namespace Oct8pus\PayPal\Plans;

use stdClass;

class Taxes
{
    private readonly float $percentage;
    private readonly bool $taxIncluded;

    /**
     * Constructor
     *
     * @param float $percentage  - tax percentage on billing amount
     * @param bool  $taxIncluded - whether the tax is already included in the billing amount
     */
    public function __construct(float $percentage, bool $taxIncluded)
    {
        $this->percentage = $percentage;
        $this->taxIncluded = $taxIncluded;
    }

    public function object() : stdClass
    {
        $object = new stdClass();

        $object->taxes = [
            'percentage' => round($this->percentage * 100, 0),
            'inclusive' => $this->taxIncluded,
        ];

        return $object;
    }
}
