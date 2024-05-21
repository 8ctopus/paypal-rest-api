<?php

declare(strict_types=1);

namespace Oct8pus\PayPal\Plans;

use stdClass;

class BillingCycle
{
    private readonly TenureType $tenureType;
    private readonly Frequency $frequency;
    private readonly int $totalCycles;
    private readonly PricingScheme $pricingScheme;

    /**
     * Constructor
     *
     * @param TenureType    $tenureType
     * @param Frequency     $frequency
     * @param int           $totalCycles   - set to zero for perpetual
     * @param PricingScheme $pricingScheme
     */
    public function __construct(TenureType $tenureType, Frequency $frequency, int $totalCycles, PricingScheme $pricingScheme)
    {
        $this->tenureType = $tenureType;
        $this->frequency = $frequency;
        $this->totalCycles = $totalCycles;
        $this->pricingScheme = $pricingScheme;
    }

    /**
     * Get object
     *
     * @param int $sequence
     *
     * @return stdClass
     */
    public function object(int $sequence) : stdClass
    {
        $object = $this->frequency->object();

        $object->tenure_type = $this->tenureType->value;
        $object->sequence = $sequence;
        $object->total_cycles = $this->totalCycles;

        return (object) array_merge((array) $object, (array) $this->pricingScheme->object());
    }
}
