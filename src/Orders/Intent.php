<?php

declare(strict_types=1);

namespace Oct8pus\PayPal\Orders;

enum Intent : string
{
    case Capture = 'CAPTURE';
    case Authorize = 'AUTHORIZE';

    public static function fromLowerCase(string $value) : self
    {
        return self::from(strtoupper($value));
    }
}
