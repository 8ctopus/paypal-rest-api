<?php

declare(strict_types=1);

use Clue\Commander\Router;
use Nimbly\Capsule\Factory\RequestFactory;
use Nimbly\Capsule\Factory\StreamFactory;
use Nimbly\Shuttle\Shuttle;
use Noodlehaus\Config;
use NunoMaduro\Collision\Provider;
use Oct8pus\PayPal\Hooks;
use Oct8pus\PayPal\HttpHandler;
use Oct8pus\PayPal\OAuthCache;
use Oct8pus\PayPal\Orders;
use Oct8pus\PayPal\Orders\Intent;
use Oct8pus\PayPal\Payments;
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
use Oct8pus\PayPal\Products;
use Oct8pus\PayPal\Status;
use Oct8pus\PayPal\Subscriptions;

$vendor = __DIR__ . '/vendor/autoload.php';

if (!file_exists($vendor)) {
    echo <<<'TXT'
    Please run composer install

    TXT;
    return;
}

require_once $vendor;

(new Provider())
    ->register();

$file = __DIR__ . '/.env.php';

if (!file_exists($file)) {
    echo <<<'TXT'
    Please create env.php based on env.php.example

    TXT;
    return;
}

date_default_timezone_set('UTC');

$config = Config::load($file);

$sandbox = $config->get('paypal.rest.sandbox');

$handler = new HttpHandler(
    // PSR-18 http client
    new Shuttle(),
    // PSR-17 request factory
    new RequestFactory(),
    // PSR-7 stream
    new StreamFactory()
);

$auth = new OAuthCache($sandbox, $handler, $config->get('paypal.rest.id'), $config->get('paypal.rest.secret'), __DIR__ . '/.cache.json');

$router = new Router();

$router->add('hooks list', static function () use ($sandbox, $handler, $auth) : void {
    $webhooks = new Hooks($sandbox, $handler, $auth);
    $hooks = $webhooks->list();

    foreach ($hooks as $hook) {
        echo "{$hook['id']} - {$hook['url']}\n";
    }
});

$router->add('hooks get <id>', static function (array $args) use ($sandbox, $handler, $auth) : void {
    $webhooks = new Hooks($sandbox, $handler, $auth);
    dump($webhooks->get($args['id']));
});

$router->add('hooks create <url>', static function (array $args) use ($sandbox, $handler, $auth) : void {
    $webhooks = new Hooks($sandbox, $handler, $auth);
    $webhooks->create($args['url'], [
        // a payment on a subscription was made
        'PAYMENT.SALE.COMPLETED',
        // a payment on a subscription was refunded
        'PAYMENT.SALE.REFUNDED',
        // a payment on a subscription was reversed
        'PAYMENT.SALE.REVERSED',

        // user starts subscription process - it's not completed yet!
        'BILLING.SUBSCRIPTION.CREATED',
        // either user just subscribed to a plan - no payment yet or subscription resumed
        'BILLING.SUBSCRIPTION.ACTIVATED',
        // subscription expired
        'BILLING.SUBSCRIPTION.EXPIRED',
        // user subscription was canceled (from PayPal admin, REST api or from user side inside account)
        'BILLING.SUBSCRIPTION.CANCELLED',
        // subscription paused
        'BILLING.SUBSCRIPTION.SUSPENDED',
        // payment failed on subscription
        'BILLING.SUBSCRIPTION.PAYMENT.FAILED',
        // subscription is updated - how to do that? (like suspended, change of state? no)
        'BILLING.SUBSCRIPTION.UPDATED',

        //'PAYMENT.AUTHORIZATION.CREATED',
        //'PAYMENT.AUTHORIZATION.VOIDED',
        //'PAYMENT.CAPTURE.COMPLETED',

        // customer dispute created
        'CUSTOMER.DISPUTE.CREATED',

        // customer dispute updated
        'CUSTOMER.DISPUTE.UPDATED',

        // customer dispute resolved
        'CUSTOMER.DISPUTE.RESOLVED',
    ]);
});

$router->add('hooks delete <id>', static function (array $args) use ($sandbox, $handler, $auth) : void {
    $webhooks = new Hooks($sandbox, $handler, $auth);
    $webhooks->delete($args['id']);
});

$router->add('hooks clear', static function () use ($sandbox, $handler, $auth) : void {
    $webhooks = new Hooks($sandbox, $handler, $auth);
    $hooks = $webhooks->list();

    foreach ($hooks as $hook) {
        $webhooks->delete($hook['id']);
    }
});

$router->add('hooks simulate <id> <event>', static function (array $args) use ($sandbox, $handler, $auth) : void {
    $webhooks = new Hooks($sandbox, $handler, $auth);
    dump($webhooks->simulate($args['id'], $args['event']));
});

$router->add('hooks list events <event-type> <search> <max-events>', static function (array $args) use ($sandbox, $handler, $auth) : void {
    $webhooks = new Hooks($sandbox, $handler, $auth);

    $eventType = $args['event-type'] !== 'null' ? $args['event-type'] : null;
    $search = $args['search'] !== 'null' ? $args['search'] : null;

    $end = new DateTime('now');
    $start = clone $end;
    $start = $start->sub(new DateInterval('P30D'));

    dump($webhooks->listEvents($eventType, $search, $start, $end, (int) $args['max-events']));
});

$router->add('subscriptions get <billing-agreement>', static function (array $args) use ($sandbox, $handler, $auth) : void {
    $subscriptions = new Subscriptions($sandbox, $handler, $auth);
    dump($subscriptions->get($args['billing-agreement']));
});

$router->add('subscriptions list transactions <billing-agreement>', static function (array $args) use ($sandbox, $handler, $auth) : void {
    $subscriptions = new Subscriptions($sandbox, $handler, $auth);
    dump($subscriptions->listTransactions($args['billing-agreement']));
});

$router->add('subscriptions create <plan-id> <success-url> <cancel-url>', static function (array $args) use ($sandbox, $handler, $auth) : void {
    $subscriptions = new Subscriptions($sandbox, $handler, $auth);

    $response = $subscriptions->create($args['plan-id'], $args['success-url'], $args['cancel-url']);

    foreach ($response['links'] as $link) {
        if ($link['rel'] === 'approve') {
            echo "redirect user to {$link['href']} to approve the subscription\n\n";
            break;
        }
    }

    dump($response);
});

$router->add('subscriptions capture <billing-agreement> <currency> <amount> <note>', static function (array $args) use ($sandbox, $handler, $auth) : void {
    $subscriptions = new Subscriptions($sandbox, $handler, $auth);
    dump($subscriptions->capture($args['billing-agreement'], $args['currency'], $args['amount'], $args['note']));
});

$router->add('subscriptions activate <billing-agreement>', static function (array $args) use ($sandbox, $handler, $auth) : void {
    $subscriptions = new Subscriptions($sandbox, $handler, $auth);
    dump($subscriptions->activate($args['billing-agreement']));
});

$router->add('subscriptions suspend <billing-agreement>', static function (array $args) use ($sandbox, $handler, $auth) : void {
    $subscriptions = new Subscriptions($sandbox, $handler, $auth);
    dump($subscriptions->suspend($args['billing-agreement']));
});

$router->add('subscriptions cancel <billing-agreement>', static function (array $args) use ($sandbox, $handler, $auth) : void {
    $subscriptions = new Subscriptions($sandbox, $handler, $auth);
    dump($subscriptions->cancel($args['billing-agreement']));
});

$router->add('plans list', static function () use ($sandbox, $handler, $auth) : void {
    $plans = new Plans($sandbox, $handler, $auth);
    dump($plans->list());
});

$router->add('plans get <plan-id>', static function (array $args) use ($sandbox, $handler, $auth) : void {
    $plans = new Plans($sandbox, $handler, $auth);
    dump($plans->get($args['plan-id']));
});

$router->add('plans activate <plan-id>', static function (array $args) use ($sandbox, $handler, $auth) : void {
    $plans = new Plans($sandbox, $handler, $auth);
    dump($plans->activate($args['plan-id']));
});

$router->add('plans deactivate <plan-id>', static function (array $args) use ($sandbox, $handler, $auth) : void {
    $plans = new Plans($sandbox, $handler, $auth);
    dump($plans->deactivate($args['plan-id']));
});

// php demo.php plans create PROD-XXCD1234QWER65782 "Video Streaming Service Plan" "Video Streaming Service basic plan" active
$router->add('plans create <product-id> <name> <description> <status>', static function (array $args) use ($sandbox, $handler, $auth) : void {
    $plans = new Plans($sandbox, $handler, $auth);

    /*
    $billingCycles = (new BillingCycles())
        ->add(new BillingCycle(TenureType::Trial, new Frequency(IntervalUnit::Month, 1), 2, new PricingScheme(3.0, 'USD')))
        ->add(new BillingCycle(TenureType::Trial, new Frequency(IntervalUnit::Month, 1), 3, new PricingScheme(6.0, 'USD')))
        ->add(new BillingCycle(TenureType::Regular, new Frequency(IntervalUnit::Month, 1), 12, new PricingScheme(10.0, 'USD')));

    $paymentPreferences = new PaymentPreferences(true, 'USD', 10.0, SetupFeeFailure::Continue, 3);
    $taxes = new Taxes(0.10, false);
    */

    /*
    // monthly $4.99
    $billingCycles = (new BillingCycles())
        ->add(new BillingCycle(TenureType::Regular, new Frequency(IntervalUnit::Month, 1), 0, new PricingScheme(4.99, 'USD')));

    $paymentPreferences = new PaymentPreferences(true, 'USD', 0.0, SetupFeeFailure::Continue, 1);
    $taxes = new Taxes(0.0, false);
    */

    // yearly $12
    $billingCycles = (new BillingCycles())
        ->add(new BillingCycle(TenureType::Regular, new Frequency(IntervalUnit::Year, 1), 0, new PricingScheme(12.0, 'USD')));

    $paymentPreferences = new PaymentPreferences(true, 'USD', 0.0, SetupFeeFailure::Continue, 1);
    $taxes = new Taxes(0.0, false);

    /*
    // first year $24 then from year 2 $1 yearly
    $billingCycles = (new BillingCycles())
        ->add(new BillingCycle(TenureType::Trial, new Frequency(IntervalUnit::Year, 1), 1, new PricingScheme(24.0, 'USD')))
        ->add(new BillingCycle(TenureType::Regular, new Frequency(IntervalUnit::Year, 1), 0, new PricingScheme(1.0, 'USD')));

    $paymentPreferences = new PaymentPreferences(true, 'USD', 0.0, SetupFeeFailure::Continue, 1);
    $taxes = new Taxes(0.0, false);
    */

    dump($plans->create(
        $args['product-id'],
        $args['name'],
        $args['description'],
        Status::fromLowerCase($args['status']),
        $billingCycles,
        $paymentPreferences,
        $taxes
    ));
});

$router->add('plans update <plan-id> <operation> <attribute> <value>', static function (array $args) use ($sandbox, $handler, $auth) : void {
    $plans = new Plans($sandbox, $handler, $auth);
    dump($plans->update($args['plan-id'], Operation::from($args['operation']), $args['attribute'], $args['value']));
});

$router->add('plans update pricing <plan-id>', static function (array $args) use ($sandbox, $handler, $auth) : void {
    $plans = new Plans($sandbox, $handler, $auth);

    $billingCycles = (new BillingCycles())
        ->add(new BillingCycle(TenureType::Regular, new Frequency(IntervalUnit::Month, 1), 0, new PricingScheme(4.99, 'USD')));

    throw new Exception('not working');

    dump($plans->updatePricing($args['plan-id'], $billingCycles));
});

$router->add('products list', static function () use ($sandbox, $handler, $auth) : void {
    $products = new Products($sandbox, $handler, $auth);
    dump($products->list());
});

$router->add('products get <product-id>', static function (array $args) use ($sandbox, $handler, $auth) : void {
    $products = new Products($sandbox, $handler, $auth);
    dump($products->get($args['product-id']));
});

$router->add('products create <product-id> <name> <description> <type> <category> <home-url> <image-url>', static function (array $args) use ($sandbox, $handler, $auth) : void {
    $products = new Products($sandbox, $handler, $auth);
    dump($products->create([
        'name' => $args['name'],
        'description' => $args['description'],
        'type' => $args['type'], // Physical Goods, Digital Goods, Service
        'category' => $args['category'], // Software
        'home_url' => $args['home-url'],
        'image_url' => $args['image-url'],
    ]));
});

$router->add('products update <product-id> <operation> <path> <value>', static function (array $args) use ($sandbox, $handler, $auth) : void {
    $products = new Products($sandbox, $handler, $auth);
    $products->update($args['product-id'], $args['operation'], $args['path'], $args['value']);
});

$router->add('orders create <intent> <amount> <currency>', static function (array $args) use ($sandbox, $handler, $auth) : void {
    $orders = new Orders($sandbox, $handler, $auth);
    $response = $orders->create(Intent::fromLowerCase($args['intent']), $args['currency'], (float) $args['amount']);

    echo "go to https://www.sandbox.paypal.com/checkoutnow?token={$response['id']} to approve the payment and finally capture the payment\n\n";

    dump($response);
});

$router->add('orders get <id>', static function (array $args) use ($sandbox, $handler, $auth) : void {
    $orders = new Orders($sandbox, $handler, $auth);
    dump($orders->get($args['id']));
});

$router->add('orders authorize <id>', static function (array $args) use ($sandbox, $handler, $auth) : void {
    $orders = new Orders($sandbox, $handler, $auth);
    dump($orders->authorize($args['id']));
});

$router->add('orders capture <id>', static function (array $args) use ($sandbox, $handler, $auth) : void {
    $orders = new Orders($sandbox, $handler, $auth);
    dump($orders->capture($args['id']));
});

$router->add('orders track <id> <carrier> <tracking-number> <capture-id>', static function (array $args) use ($sandbox, $handler, $auth) : void {
    $orders = new Orders($sandbox, $handler, $auth);
    dump($orders->track($args['id'], $args['carrier'], $args['tracking-number'], $args['capture-id'], false));
});

$router->add('payments get authorized <id>', static function (array $args) use ($sandbox, $handler, $auth) : void {
    $payments = new Payments($sandbox, $handler, $auth);
    dump($payments->getAuthorized($args['id']));
});

$router->add('payments get captured <id>', static function (array $args) use ($sandbox, $handler, $auth) : void {
    $payments = new Payments($sandbox, $handler, $auth);
    dump($payments->getCaptured($args['id']));
});

$router->add('payments get refunded <id>', static function (array $args) use ($sandbox, $handler, $auth) : void {
    $payments = new Payments($sandbox, $handler, $auth);
    dump($payments->getRefunded($args['id']));
});

$router->add('auth token', static function () use ($auth) : void {
    dump($auth->token());
});

$router->add('custom simulate <url> <file>', static function (array $args) : void {
    simulate($args['url'], $args['file']);
});

$router->add('[--help | -h]', static function () use ($router) : void {
    echo 'Usage:' . PHP_EOL;
    foreach ($router->getRoutes() as $route) {
        echo '  ' . $route . PHP_EOL;
    }
});

echo $sandbox ? "SANDBOX\n" : "PRODUCTION\n";

$router->execArgv();

/**
 * Dump variable
 *
 * @param mixed $variable
 *
 * @return void
 */
function dump(mixed $variable) : void
{
    echo json_encode($variable, JSON_PRETTY_PRINT) . PHP_EOL;
}

/**
 * Simulate custom event
 *
 * @param string $webhookUrl
 * @param string $file
 *
 * @return void
 */
function simulate(string $webhookUrl, string $file) : void
{
    $event = file_get_contents($file);

    if ($event === false) {
        throw new Exception('file not found');
    }

    $options = [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => [
            'Content-Type: application/json',
        ],
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => $event,
    ];

    $curl = curl_init();

    if ($curl === false) {
        throw new Exception('curl init');
    }

    $options[CURLOPT_URL] = $webhookUrl;

    curl_setopt_array($curl, $options);

    $response = curl_exec($curl);

    $status = curl_getinfo($curl, CURLINFO_RESPONSE_CODE);

    curl_close($curl);

    if ($response === false || $status !== 200) {
        throw new Exception("curl - {$status}");
    }
}
