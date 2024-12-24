<?php

declare(strict_types=1);

namespace Tests;

use Nimbly\Capsule\Factory\RequestFactory;
use Nimbly\Capsule\Factory\StreamFactory;
use Nimbly\Capsule\Response;
use Nimbly\Shuttle\Shuttle;
use Oct8pus\PayPal\Plans;
use Oct8pus\PayPal\Plans\BillingCycle;
use Oct8pus\PayPal\Plans\BillingCycles;
use Oct8pus\PayPal\Plans\Frequency;
use Oct8pus\PayPal\Plans\IntervalUnit;
use Oct8pus\PayPal\Plans\Operation;
use Oct8pus\PayPal\Plans\PaymentPreferences;
use Oct8pus\PayPal\Plans\PricingScheme;
use Oct8pus\PayPal\Plans\SetupFeeFailure;
use Oct8pus\PayPal\Plans\Taxes;
use Oct8pus\PayPal\Plans\TenureType;
use Oct8pus\PayPal\RestBase;
use Oct8pus\PayPal\Status;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(Plans::class)]
#[CoversClass(BillingCycle::class)]
#[CoversClass(BillingCycles::class)]
#[CoversClass(Frequency::class)]
#[CoversClass(IntervalUnit::class)]
#[CoversClass(Operation::class)]
#[CoversClass(PaymentPreferences::class)]
#[CoversClass(PricingScheme::class)]
#[CoversClass(SetupFeeFailure::class)]
#[CoversClass(Taxes::class)]
#[CoversClass(TenureType::class)]
#[CoversClass(RestBase::class)]
final class PlansTest extends TestCase
{
    private static HttpHandlerMock $handler;
    private static OAuthMock $auth;
    private static Plans $plans;

    public static function setUpBeforeClass() : void
    {
        self::$handler = new HttpHandlerMock(new Shuttle(), new RequestFactory(), new StreamFactory());
        self::$auth = new OAuthMock(self::$handler, 'testId', 'testSecret');
        self::$plans = new Plans(true, self::$handler, self::$auth);
    }

    public function testConstructor() : void
    {
        self::assertInstanceOf(Plans::class, new Plans(true, self::$handler, self::$auth));
    }

    public function testList() : void
    {
        self::$handler->setResponse(new Response(200, file_get_contents(__DIR__ . '/fixtures/PlansList.json')));

        self::$plans->list();

        $expected = <<<'TEXT'
        https://api-m.sandbox.paypal.com/v1/billing/plans
        Host: api-m.sandbox.paypal.com
        Authorization: Bearer test
        Content-Type: application/json

        TEXT;

        self::assertSame($expected, self::$handler->dumpRequest());
    }

    public function testGet() : void
    {
        self::$handler->setResponse(new Response(200, file_get_contents(__DIR__ . '/fixtures/PlanDetails.json')));

        $id = 'P-5ML4271244454362WXNWU5NQ';

        self::$plans->get($id);

        $expected = <<<TEXT
        https://api-m.sandbox.paypal.com/v1/billing/plans/{$id}
        Host: api-m.sandbox.paypal.com
        Authorization: Bearer test
        Content-Type: application/json

        TEXT;

        self::assertSame($expected, self::$handler->dumpRequest());
    }

    public function testActivate() : void
    {
        self::$handler->setResponse(new Response(204, ''));

        $id = 'P-5ML4271244454362WXNWU5NQ';

        self::$plans->activate($id);

        $expected = <<<TEXT
        https://api-m.sandbox.paypal.com/v1/billing/plans/{$id}/activate
        Host: api-m.sandbox.paypal.com
        Authorization: Bearer test
        Content-Type: application/json

        TEXT;

        self::assertSame($expected, self::$handler->dumpRequest());
    }

    public function testDeactivate() : void
    {
        self::$handler->setResponse(new Response(204, ''));

        $id = 'P-5ML4271244454362WXNWU5NQ';

        self::$plans->deactivate($id);

        $expected = <<<TEXT
        https://api-m.sandbox.paypal.com/v1/billing/plans/{$id}/deactivate
        Host: api-m.sandbox.paypal.com
        Authorization: Bearer test
        Content-Type: application/json

        TEXT;

        self::assertSame($expected, self::$handler->dumpRequest());
    }

    public function testCreate() : void
    {
        self::$handler->setResponse(new Response(201, file_get_contents(__DIR__ . '/fixtures/PlanCreate.json')));

        $billingCycles = new BillingCycles();
        $billingCycles
            ->add(new BillingCycle(TenureType::Trial, new Frequency(IntervalUnit::Month, 1), 2, new PricingScheme(3.0, 'USD')))
            ->add(new BillingCycle(TenureType::Trial, new Frequency(IntervalUnit::Month, 1), 3, new PricingScheme(6.0, 'USD')))
            ->add(new BillingCycle(TenureType::Regular, new Frequency(IntervalUnit::Month, 1), 12, new PricingScheme(10.0, 'USD')));

        $paymentPreferences = new PaymentPreferences(true, 'USD', 10.0, SetupFeeFailure::Continue, 3);
        $taxes = new Taxes(0.10, false);

        self::$plans->create(
            '',
            '',
            '',
            Status::Active,
            $billingCycles,
            $paymentPreferences,
            $taxes,
        );

        $expected = <<<'TEXT'
        https://api-m.sandbox.paypal.com/v1/billing/plans
        Host: api-m.sandbox.paypal.com
        Authorization: Bearer test
        Content-Type: application/json
        {
            "product_id": "",
            "name": "",
            "billing_cycles": [
                {
                    "frequency": {
                        "interval_unit": "MONTH",
                        "interval_count": 1
                    },
                    "tenure_type": "TRIAL",
                    "sequence": 1,
                    "total_cycles": 2,
                    "pricing_scheme": {
                        "fixed_price": {
                            "value": 3,
                            "currency_code": "USD"
                        }
                    }
                },
                {
                    "frequency": {
                        "interval_unit": "MONTH",
                        "interval_count": 1
                    },
                    "tenure_type": "TRIAL",
                    "sequence": 2,
                    "total_cycles": 3,
                    "pricing_scheme": {
                        "fixed_price": {
                            "value": 6,
                            "currency_code": "USD"
                        }
                    }
                },
                {
                    "frequency": {
                        "interval_unit": "MONTH",
                        "interval_count": 1
                    },
                    "tenure_type": "REGULAR",
                    "sequence": 3,
                    "total_cycles": 12,
                    "pricing_scheme": {
                        "fixed_price": {
                            "value": 10,
                            "currency_code": "USD"
                        }
                    }
                }
            ],
            "payment_preferences": {
                "auto_bill_outstanding": true,
                "setup_fee": {
                    "value": 10,
                    "currency_code": "USD"
                },
                "setup_fee_failure_action": "CONTINUE",
                "payment_failure_threshold": 3
            },
            "description": "",
            "status": "ACTIVE",
            "taxes": {
                "percentage": 10,
                "inclusive": false
            }
        }
        TEXT;

        self::assertSame($expected, self::$handler->dumpRequest());
    }

    public function testUpdate() : void
    {
        self::$handler->setResponse(new Response(204));

        self::$plans->update('PROD-XXCD1234QWER65782', Operation::from('replace'), 'description', 'test');

        $expected = <<<'TEXT'
        https://api-m.sandbox.paypal.com/v1/billing/plans/PROD-XXCD1234QWER65782
        Host: api-m.sandbox.paypal.com
        Authorization: Bearer test
        Content-Type: application/json
        [
            {
                "op": "replace",
                "path": "\/description",
                "value": "test"
            }
        ]
        TEXT;

        self::assertSame($expected, self::$handler->dumpRequest());
    }
}
