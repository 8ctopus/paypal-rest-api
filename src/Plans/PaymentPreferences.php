<?php

declare(strict_types=1);

namespace Oct8pus\PayPal\Plans;

use stdClass;

class PaymentPreferences
{
    private readonly bool $autoBillOutstanding;
    private readonly string $currency;
    private readonly int $setupFee;
    private readonly SetupFeeFailure $setupFeeFailure;
    private readonly int $paymentFailureThreshold;

    /**
     * Constructor
     *
     * @param bool            $autoBillOutstanding
     * @param string          $currency
     * @param int             $setupFee
     * @param SetupFeeFailure $setupFeeFailure
     * @param int             $paymentFailureThreshold
     */
    public function __construct(bool $autoBillOutstanding, string $currency, int $setupFee, SetupFeeFailure $setupFeeFailure, int $paymentFailureThreshold)
    {
        $this->autoBillOutstanding = $autoBillOutstanding;
        $this->currency = $currency;
        $this->setupFee = $setupFee;
        $this->setupFeeFailure = $setupFeeFailure;
        $this->paymentFailureThreshold = $paymentFailureThreshold;
    }

    public function object() : stdClass
    {
        $object = new stdClass();

        $object->payment_preferences = [
            'auto_bill_outstanding' => $this->autoBillOutstanding,
            'setup_fee' => [
                'value' => $this->setupFee,
                'currency_code' => $this->currency,
            ],
            'setup_fee_failure_action' => $this->setupFeeFailure->value,
            'payment_failure_threshold' => $this->paymentFailureThreshold,
        ];

        return $object;
    }
}
