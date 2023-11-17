<?php

declare(strict_types=1);

use Oct8pus\PayPal\Hooks;
use Oct8pus\PayPal\OAuth;
use Oct8pus\PayPal\Plans;
use Oct8pus\PayPal\Products;
use Oct8pus\PayPal\Subscription;
use Noodlehaus\Config;

require_once __DIR__ . '/vendor/autoload.php';

$args = $argv;

// remove file name
array_shift($args);

if (count($args) < 2) {
    throw new Exception('missing group or command');
}

$config = Config::load(__DIR__ . '/.env.php');

$auth = new OAuth($config->get('paypal.rest.id'), $config->get('paypal.rest.secret'));

$group = array_shift($args);
$command = array_shift($args);

switch ($group) {
    case 'hooks':
        switch ($command) {
            case 'list':
                $webhooks = new Hooks($auth);
                $hooks = $webhooks->list();

                foreach ($hooks as $hook) {
                    echo "{$hook['id']} - {$hook['url']}\n";
                }

                break;

            case 'add':
                if (count($args) < 1) {
                    throw new Exception('missing hook url');
                }

                $webhooks = new Hooks($auth);
                $webhooks->add($args[0] . '/paypal/hook/', [
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

                break;

            case 'delete':
                if (count($args) < 1) {
                    throw new Exception('missing hook id');
                }

                $webhooks = new Hooks($auth);
                $webhooks->delete($args[0]);

                break;

            case 'clear':
                $webhooks = new Hooks($auth);
                $hooks = $webhooks->list();

                foreach ($hooks as $hook) {
                    $webhooks->delete($hook['id']);
                }

                break;

            case 'simulate':
                if (count($args) < 2) {
                    throw new Exception('missing webhook id or event type');
                }

                $webhooks = new Hooks($auth);
                dump($webhooks->simulate($args[0], $args[1]));

                break;

            default:
                throw new Exception("unknown command - {$command}");
        }

        break;

    case 'subscriptions':
        switch ($command) {
            case 'get':
                if (count($args) < 1) {
                    throw new Exception('missing subscription');
                }

                $subscription = new Subscription($auth);
                dump($subscription->get($args[0]));
                break;

            case 'cancel':
                if (count($args) < 1) {
                    throw new Exception('missing subscription');
                }

                $subscription = new Subscription($auth);
                $subscription->cancel($args[0]);
                break;

            case 'suspend':
                if (count($args) < 1) {
                    throw new Exception('missing subscription');
                }

                $subscription = new Subscription($auth);
                $subscription->suspend($args[0]);
                break;

            case 'activate':
                if (count($args) < 1) {
                    throw new Exception('missing subscription');
                }

                $subscription = new Subscription($auth);
                $subscription->activate($args[0]);
                break;
        }

        break;

    case 'plans':
        switch ($command) {
            case 'list':
                $plans = new Plans($auth);

                dump($plans->list());
                break;

            case 'get':
                if (count($args) < 1) {
                    throw new Exception('missing plan');
                }

                $plans = new Plans($auth);
                dump($plans->get($args[0]));
                break;

            case 'activate':
                if (count($args) < 1) {
                    throw new Exception('missing plan');
                }

                $plans = new Plans($auth);
                dump($plans->activate($args[0]));
                break;

            case 'deactivate':
                if (count($args) < 1) {
                    throw new Exception('missing plan');
                }

                $plans = new Plans($auth);
                dump($plans->deactivate($args[0]));
                break;

            default:
                throw new Exception("unknown command - {$command}");
        }

        break;

    case 'products':
        switch ($command) {
            case 'list':
                $products = new Products($auth);

                dump($products->list());
                break;

            case 'get':
                if (count($args) < 1) {
                    throw new Exception('missing product');
                }

                $products = new Products($auth);
                dump($products->get($args[0]));
                break;

            case 'add':
                $products = new Products($auth);
                dump($products->add([
                    'name' => 'CopyTrans Studio',
                    'description' => 'CopyTrans Studio',
                    'type' => 'Service', // Physical Goods, Digital Goods, Service
                    'category' => 'Software',
                    'home_url' => 'https://copytrans.studio/',
                    'image_url' => 'https://copytrans.studio/app/themes/studio/assets/images/logo.svg',
                ]));
                break;

            default:
                throw new Exception("unknown command - {$command}");
        }

        break;

    case 'auth':
        switch ($command) {
            case 'token':
                dump($auth->token());
                break;

            default:
                throw new Exception("unknown command - {$command}");
        }

        break;

    case 'custom':
        switch ($command) {
            case 'simulate':
                if (count($args) < 2) {
                    throw new Exception('missing webhook url or file');
                }

                simulate($args[0], $args[1]);
                break;

            default:
                throw new Exception("unknown command - {$command}");
        }

        break;

    default:
        throw new Exception("unknown group - {$group}");
}

function dump($var) : void
{
    echo json_encode($var, JSON_PRETTY_PRINT) . PHP_EOL;
}

/**
 * Simulate custom event
 *
 * @param  string $webhookUrl
 * @param  string $file
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
