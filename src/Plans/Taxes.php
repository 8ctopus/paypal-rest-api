<?php

declare(strict_types=1);

namespace Oct8pus\PayPal\Plans;

use stdClass;

class Taxes
{
    private readonly float $percentage;
    private readonly bool $inclusive;

    public function __construct(float $percentage, bool $inclusive)
    {
        $this->percentage = $percentage;
        $this->inclusive = $inclusive;
    }

    public function object() : stdClass
    {
        $object = new stdClass();

        $object->taxes = [
            'percentage' => round($this->percentage * 100, 0),
            'inclusive' => $this->inclusive,
        ];

        return $object;
    }
}
