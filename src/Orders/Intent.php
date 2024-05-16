<?php

/**
 * @see https://dev.to/faxriddinmaxmadiyorov/paypal-rest-api-3pi6
 */

declare(strict_types=1);

namespace Oct8pus\PayPal\Orders;

enum Intent : string
{
    // PayPal takes the amount from the customer's account completing the transaction
    case Capture = 'CAPTURE';

    // When you set the intent to AUTHORIZE, PayPal authorizes the payment amount but doesn't take the funds immediately. Instead, it places a hold on the funds, reserving them for capture at a later time.
    case Authorize = 'AUTHORIZE';

    public static function fromLowerCase(string $value) : self
    {
        return self::from(strtoupper($value));
    }
}
