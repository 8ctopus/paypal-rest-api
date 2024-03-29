<?php

declare(strict_types=1);

namespace Oct8pus\PayPal\Plans;

enum TenureType : string
{
    case Regular = 'REGULAR';
    case Trial = 'TRIAL';
}
