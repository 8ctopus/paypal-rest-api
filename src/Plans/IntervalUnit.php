<?php

declare(strict_types=1);

namespace Oct8pus\PayPal\Plans;

enum IntervalUnit : string
{
    case Week = 'WEEK';
    case Month = 'MONTH';
    case Year = 'YEAR';
}
