<?php

/**
 * @reference https://developer.paypal.com/docs/api/webhooks/v1/
 */

declare(strict_types=1);

namespace Oct8pus\PayPal;

use Oct8pus\PayPal\OAuth;
use Oct8pus\PayPal\RequestHandler;
use Oct8pus\PayPal\RestBase;

class Hooks extends RestBase
{
    private OAuth $auth;

    /**
     * Constructor
     *
     * @param RequestHandler $handler
     * @param OAuth $auth - OAuth 2.0 token
     */
    public function __construct(RequestHandler $handler, OAuth $auth)
    {
        parent::__construct(true, $handler);

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

        $url = '/v1/notifications/webhooks';

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

        $body = json_encode([
            'webhook_id' => $webhookId,
            'event_type' => $eventType,
            'resource_version' => $version,
        ], JSON_PRETTY_PRINT);

        $json = $this->request('POST', $url, $headers, $body, 202);

        return json_decode($json, true);
    }
}
