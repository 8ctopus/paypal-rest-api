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
use Oct8pus\PayPal\Plans\PaymentPreferences;
use Oct8pus\PayPal\Plans\PricingScheme;
use Oct8pus\PayPal\Plans\SetupFeeFailure;
use Oct8pus\PayPal\Plans\Taxes;
use Oct8pus\PayPal\Plans\TenureType;
use Oct8pus\PayPal\Status;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 *
 * @covers \Oct8pus\PayPal\Plans
 * @covers \Oct8pus\PayPal\Plans\BillingCycle
 * @covers \Oct8pus\PayPal\Plans\BillingCycles
 * @covers \Oct8pus\PayPal\Plans\Frequency
 * @covers \Oct8pus\PayPal\Plans\IntervalUnit
 * @covers \Oct8pus\PayPal\Plans\PaymentPreferences
 * @covers \Oct8pus\PayPal\Plans\PricingScheme
 * @covers \Oct8pus\PayPal\Plans\SetupFeeFailure
 * @covers \Oct8pus\PayPal\Plans\Taxes
 * @covers \Oct8pus\PayPal\Plans\TenureType
 * @covers \Oct8pus\PayPal\RestBase
 */
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
            ->add(new BillingCycle(TenureType::Trial, new Frequency(IntervalUnit::Month, 1), 2, new PricingScheme(3, 'USD')))
            ->add(new BillingCycle(TenureType::Trial, new Frequency(IntervalUnit::Month, 1), 3, new PricingScheme(6, 'USD')))
            ->add(new BillingCycle(TenureType::Regular, new Frequency(IntervalUnit::Month, 1), 12, new PricingScheme(10, 'USD')));

        $paymentPreferences = new PaymentPreferences(true, 10, SetupFeeFailure::Continue, 3);
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

        TEXT;

        self::assertSame($expected, self::$handler->dumpRequest());
    }

    public function testUpdate() : void
    {
        self::$handler->setResponse(new Response(204));

        self::$plans->update('PROD-XXCD1234QWER65782', 'replace', 'description', 'test');

        $expected = <<<'TEXT'
        https://api-m.sandbox.paypal.com/v1/billing/plans/PROD-XXCD1234QWER65782
        Host: api-m.sandbox.paypal.com
        Authorization: Bearer test
        Content-Type: application/json

        TEXT;

        self::assertSame($expected, self::$handler->dumpRequest());
    }
}
