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
use Oct8pus\PayPal\Plans;
use Oct8pus\PayPal\Products;
use Oct8pus\PayPal\Subscription;

require_once __DIR__ . '/vendor/autoload.php';

(new Provider())->register();

$file = __DIR__ . '/.env.php';

if (!file_exists($file)) {
    echo <<<'TXT'
    Please create env.php based on env.php.example

    TXT;
}

$config = Config::load($file);

$handler = new HttpHandler(
    // PSR-18 http client
    new Shuttle(),
    // PSR-17 request factory
    new RequestFactory(),
    // PSR-7 stream
    new StreamFactory()
);

$cacheFile = __DIR__ . '/.cache.json';
$sandbox = $config->get('paypal.rest.sandbox');

$auth = new OAuthCache($sandbox, $handler, $config->get('paypal.rest.id'), $config->get('paypal.rest.secret'), $cacheFile);

$router = new Router();

$router->add('hooks list', static function () use ($sandbox, $handler, $auth) : void {
    $webhooks = new Hooks($sandbox, $handler, $auth);
    $hooks = $webhooks->list();

    foreach ($hooks as $hook) {
        echo "{$hook['id']} - {$hook['url']}\n";
    }
});

$router->add('hooks add <url>', static function (array $args) use ($sandbox, $handler, $auth) : void {
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
        // user just subscribed to a plan - no payment yet
        // subscription resumed
        'BILLING.SUBSCRIPTION.ACTIVATED',
        // subscription is updated - how to do that? (like suspended, change of state? no)
        'BILLING.SUBSCRIPTION.UPDATED',
        // subscription expired
        'BILLING.SUBSCRIPTION.EXPIRED',
        // user subscription was canceled (from PayPal admin, REST api or from user side inside account)
        'BILLING.SUBSCRIPTION.CANCELLED',
        // subscription paused
        'BILLING.SUBSCRIPTION.SUSPENDED',
        // payment failed on subscription
        'BILLING.SUBSCRIPTION.PAYMENT.FAILED',

        //'PAYMENT.AUTHORIZATION.CREATED',
        //'PAYMENT.AUTHORIZATION.VOIDED',
        //'PAYMENT.CAPTURE.COMPLETED',
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

$router->add('subscription get <id>', static function (array $args) use ($sandbox, $handler, $auth) : void {
    $subscription = new Subscription($sandbox, $handler, $auth);
    dump($subscription->get($args['id']));
});

$router->add('subscription cancel <id>', static function (array $args) use ($sandbox, $handler, $auth) : void {
    $subscription = new Subscription($sandbox, $handler, $auth);
    dump($subscription->cancel($args['id']));
});

$router->add('subscription suspend <id>', static function (array $args) use ($sandbox, $handler, $auth) : void {
    $subscription = new Subscription($sandbox, $handler, $auth);
    dump($subscription->suspend($args['id']));
});

$router->add('subscription activate <id>', static function (array $args) use ($sandbox, $handler, $auth) : void {
    $subscription = new Subscription($sandbox, $handler, $auth);
    dump($subscription->activate($args['id']));
});

$router->add('plans list', static function () use ($sandbox, $handler, $auth) : void {
    $plans = new Plans($sandbox, $handler, $auth);
    dump($plans->list());
});

$router->add('plans get <id>', static function (array $args) use ($sandbox, $handler, $auth) : void {
    $plans = new Plans($sandbox, $handler, $auth);
    dump($plans->get($args['id']));
});

$router->add('plans activate <id>', static function (array $args) use ($sandbox, $handler, $auth) : void {
    $plans = new Plans($sandbox, $handler, $auth);
    dump($plans->activate($args['id']));
});

$router->add('plans deactivate <id>', static function (array $args) use ($sandbox, $handler, $auth) : void {
    $plans = new Plans($sandbox, $handler, $auth);
    dump($plans->deactivate($args['id']));
});

$router->add('products list', static function () use ($sandbox, $handler, $auth) : void {
    $products = new Products($sandbox, $handler, $auth);
    dump($products->list());
});

$router->add('products get <id>', static function (array $args) use ($sandbox, $handler, $auth) : void {
    $products = new Products($sandbox, $handler, $auth);
    dump($products->get($args['id']));
});

$router->add('products create <id> <name> <description> <type> <category> <home_url> <image_url>', static function (array $args) use ($sandbox, $handler, $auth) : void {
    $products = new Products($sandbox, $handler, $auth);
    dump($products->create([
        'name' => $args['name'],
        'description' => $args['description'],
        'type' => $args['type'], // Physical Goods, Digital Goods, Service
        'category' => $args['category'], // Software
        'home_url' => $args['home_url'],
        'image_url' => $args['image_url'],
    ]));
});

$router->add('products update <id> <operation> <path> <value>', static function (array $args) use ($sandbox, $handler, $auth) : void {
    $products = new Products($sandbox, $handler, $auth);
    $products->update($args['id'], $args['operation'], $args['path'], $args['value']);
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
