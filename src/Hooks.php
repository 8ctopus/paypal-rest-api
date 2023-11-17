<?php

/**
 * @reference https://developer.paypal.com/docs/api/webhooks/v1/
 */

declare(strict_types=1);

namespace Oct8pus\PayPal;

use Oct8pus\PayPal\OAuth;
use Oct8pus\PayPal\Client;
use HttpSoft\Message\RequestFactory;
use Nimbly\Shuttle\Shuttle;

class Hooks extends Client
{
    private OAuth $auth;

    /**
     * Constructor
     *
     * @param OAuth $auth
     */
    public function __construct(OAuth $auth)
    {
        $shuttle = new Shuttle();
        $factory = new RequestFactory();

        parent::__construct(true, $shuttle, $factory);

        $this->auth = $auth;
    }

    /**
     * List hooks
     *
     * @return array
     */
    public function list() : array
    {
        $url = '/v1/notifications/webhooks';

        $headers = [
            'Authorization' => 'Bearer ' . $this->auth->token(),
            'Content-Type' => 'application/json',
        ];

        $json = $this->request('GET', $url, $headers, null, 200);

        return json_decode($json, true)['webhooks'];
    }

    /**
     * Add hook
     *
     * @param string $url
     * @param array  $eventTypes
     *
     * @return string id
     *
     * @throws PayPalException
     */
    public function add(string $url, array $eventTypes) : string
    {
        $url = '/v1/notifications/webhooks';

        $headers = [
            'Authorization' => 'Bearer ' . $this->auth->token(),
            'Content-Type' => 'application/json',
        ];

        $data = [
            'url' => $url,
            'event_types' => [],
        ];

        foreach ($eventTypes as $type) {
            $data['event_types'][] = ['name' => $type];
        }

        $body = json_encode($data, JSON_PRETTY_PRINT);

        $json = $this->request('POST', $url, $headers, $body, 201);

        $decoded = json_decode($json, true, 512, JSON_THROW_ON_ERROR);

        if (array_key_exists('id', $decoded)) {
            return $decoded['id'];
        }

        throw new PayPalException('hook id missing');
    }

    /**
     * Delete hook
     *
     * @param string $id
     *
     * @return self
     */
    public function delete(string $id) : self
    {
        $url = "/v1/notifications/webhooks/{$id}";

        $headers = [
            'Authorization' => 'Bearer ' . $this->auth->token(),
            'Content-Type' => 'application/json',
        ];

        $this->request('DELETE', $url, $headers, null, 204);

        return $this;
    }

    /**
     * Simulate hook
     *
     * @param string $webhookId
     * @param string $eventType
     *
     * @return array
     */
    public function simulate(string $webhookId, string $eventType) : array
    {
        $url = '/v1/notifications/simulate-event';

        switch ($eventType) {
            case 'BILLING.SUBSCRIPTION.EXPIRED':
            case 'BILLING.SUBSCRIPTION.UPDATED':
                $version = '2.0';
                break;

            default:
                $version = '1.0';
                break;
        }

        $headers = [
            'Authorization' => 'Bearer ' . $this->auth->token(),
            'Content-Type' => 'application/json',
        ];

        $data = json_encode([
            'webhook_id' => $webhookId,
            'event_type' => $eventType,
            'resource_version' => $version,
        ], JSON_PRETTY_PRINT);

        $json = $this->request('POST', $url, $headers, $data, 202);

        return json_decode($json, true);
    }
}
