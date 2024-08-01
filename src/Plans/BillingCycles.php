<?php

declare(strict_types=1);

namespace Oct8pus\PayPal\Plans;

class BillingCycles
{
    private array $billingCycles;

    public function __construct()
    {
        $this->billingCycles = [];
    }

    /**
     * Add cycle
     *
     * @param BillingCycle $billingCycle
     *
     * @return self
     *
     * @note A plan can have at most two trial cycles and only one regular cycle
     */
    public function add(BillingCycle $billingCycle) : self
    {
        $this->billingCycles[] = $billingCycle;
        return $this;
    }

    public function object() : array
    {
        $output = [];

        foreach ($this->billingCycles as $sequence => $cycle) {
            $output[] = $cycle->object($sequence + 1);
        }

        return [
            'billing_cycles' => $output,
        ];
    }
}
