<?php

/**
 * @reference https://developer.paypal.com/docs/api/webhooks/v1/
 */

declare(strict_types=1);

namespace Oct8pus\PayPal;

use JsonException;

class Hooks extends RestBase
{
    /**
     * Constructor
     *
     * @param bool        $sandbox
     * @param HttpHandler $handler
     * @param OAuth       $auth
     */
    public function __construct(bool $sandbox, HttpHandler $handler, OAuth $auth)
    {
        parent::__construct($sandbox, $handler, $auth);
    }

    /**
     * List hooks
     *
     * @return array<mixed>
     */
    public function list() : array
    {
        $url = '/v1/notifications/webhooks';

        $response = $this->sendRequest('GET', $url, [], null, 200);

        return json_decode($response, true)['webhooks'];
    }

    /**
     * Get hook details
     *
     * @param string $id
     *
     * @return array
     */
    public function get(string $id) : array
    {
        $url = "/v1/notifications/webhooks/{$id}";

        $response = $this->sendRequest('GET', $url, [], null, 200);

        return json_decode($response, true);
    }

    /**
     * Create hook
     *
     * @param string        $url
     * @param array<string> $eventTypes
     *
     * @return string id
     *
     * @throws JsonException|PayPalException
     */
    public function create(string $url, array $eventTypes) : string
    {
        $data = [
            'url' => $url,
            'event_types' => [],
        ];

        foreach ($eventTypes as $type) {
            $data['event_types'][] = ['name' => $type];
        }

        $url = '/v1/notifications/webhooks';

        $response = $this->sendJsonRequest('POST', $url, [], $data, 201);

        $decoded = json_decode($response, true, 512, JSON_THROW_ON_ERROR);

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

        $this->sendRequest('DELETE', $url, [], null, 204);

        return $this;
    }

    /**
     * Simulate hook
     *
     * @param string $webhookId
     * @param string $eventType
     *
     * @return array<mixed>
     *
     * @throws JsonException
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

        $json = [
            'webhook_id' => $webhookId,
            'event_type' => $eventType,
            'resource_version' => $version,
        ];

        $response = $this->sendJsonRequest('POST', $url, [], $json, 202);

        return json_decode($response, true);
    }
}
