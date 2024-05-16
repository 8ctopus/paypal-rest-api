<?php

declare(strict_types=1);

namespace Oct8pus\PayPal\Plans;

enum Operation : string
{
    case Add = 'add';
    case Month = 'replace';
}
