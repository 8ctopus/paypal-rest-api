<?php

declare(strict_types=1);

namespace Tests;

use Nimbly\Capsule\Factory\RequestFactory;
use Nimbly\Capsule\Factory\StreamFactory;
use Nimbly\Capsule\Response;
use Nimbly\Shuttle\Shuttle;
use Oct8pus\PayPal\OAuth;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 *
 * @covers \Oct8pus\PayPal\OAuth
 * @covers \Oct8pus\PayPal\RestBase
 */
final class OAuthTest extends TestCase
{
    private static HttpHandlerMock $handler;

    public static function setUpBeforeClass() : void
    {
        self::$handler = new HttpHandlerMock(new Shuttle(), new RequestFactory(), new StreamFactory());
    }

    public function testToken() : void
    {
        self::$handler->setResponse(new Response(200, <<<JSON
        {"scope":"https://uri.paypal.com/services/checkout/one-click-with-merchant-issued-token https://uri.paypal.com/services/invoicing https://uri.paypal.com/services/vault/payment-tokens/read https://uri.paypal.com/services/disputes/read-buyer https://uri.paypal.com/services/payments/realtimepayment https://uri.paypal.com/services/disputes/update-seller https://uri.paypal.com/services/payments/payment/authcapture openid https://uri.paypal.com/services/disputes/read-seller Braintree:Vault https://uri.paypal.com/services/payments/refund https://api.paypal.com/v1/vault/credit-card https://uri.paypal.com/services/billing-agreements https://api.paypal.com/v1/payments/.* https://uri.paypal.com/payments/payouts https://uri.paypal.com/services/vault/payment-tokens/readwrite https://api.paypal.com/v1/vault/credit-card/.* https://uri.paypal.com/services/shipping/trackers/readwrite https://uri.paypal.com/services/subscriptions https://uri.paypal.com/services/applications/webhooks","access_token":"A21AAKhpstvuJ7TbYe3kZrQDlO-nmW_Dxby3W06pGsWNRGH447IxnAbWXTD_01X1PT_ISaoY-Fq7LI-VWDgxAUqPDYONyZ9fA","token_type":"Bearer","app_id":"APP-80W284485P519543T","expires_in":32252,"nonce":"2023-11-20T06:08:31ZmZDn3q-SvDfxSa-7gMDOrS1u_XjViX213s3yoHTojAk"}
        JSON));

        $token = (new OAuth(self::$handler, 'testId', 'testSecret'))
            ->token();

        self::assertSame('A21AAKhpstvuJ7TbYe3kZrQDlO-nmW_Dxby3W06pGsWNRGH447IxnAbWXTD_01X1PT_ISaoY-Fq7LI-VWDgxAUqPDYONyZ9fA', $token);

        $expected = <<<TEXT
        https://api-m.sandbox.paypal.com/v1/oauth2/token
        Host: api-m.sandbox.paypal.com
        Authorization: Basic dGVzdElkOnRlc3RTZWNyZXQ=
        Content-Type: application/x-www-form-urlencoded
        Accept: application/json

        TEXT;

        self::assertSame($expected, self::$handler->dumpRequest());
    }
}
