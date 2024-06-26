<?php

declare(strict_types=1);

namespace Oct8pus\PayPal\Plans;

use stdClass;

class Frequency
{
    private readonly IntervalUnit $interval;
    private readonly int $count;

    /**
     * Constructor
     *
     * @param IntervalUnit $interval
     * @param int          $count
     */
    public function __construct(IntervalUnit $interval, int $count)
    {
        $this->interval = $interval;
        $this->count = $count;
    }

    public function object() : stdClass
    {
        $object = new stdClass();

        $object->frequency = [
            'interval_unit' => $this->interval->value,
            'interval_count' => $this->count,
        ];

        return $object;
    }
}
