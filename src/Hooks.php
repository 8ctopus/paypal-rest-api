<?php

/**
 * @reference https://developer.paypal.com/docs/api/webhooks/v1/
 *
 * Successful receipt requires your listener endpoint to respond with an HTTP 200 or other 2xx status code.
 * Any non-2xx status code will cause PayPal to reattempt delivery up to 25 times over the course of 3 days
 * or until it receives a 2xx success code. If unsuccessful after 3 days, delivery will be marked as Failed
 * (but can be resent again manually from the Webhook Events dashboard).
 */

declare(strict_types=1);

namespace Oct8pus\PayPal;

use DateTime;
use DateTimeInterface;
use JsonException;
use Oct8pus\PayPal\OAuth\OAuth;

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

    /**
     * List events
     *
     * @param ?string   $eventType
     * @param ?string   $search
     * @param ?DateTime $start
     * @param ?DateTime $end
     * @param int       $maxEvents
     *
     * @return array
     *
     * @note it does not list the event of a hook but the events of all hooks
     * @note start date can be maximum 30 days ago
     */
    public function listEvents(?string $eventType, ?string $search, ?DateTime $start, ?DateTime $end, int $maxEvents = 10) : array
    {
        $url = '/v1/notifications/webhooks-events';

        $params = [
            'page_size' => $maxEvents,
        ];

        if ($eventType) {
            $params['event_type'] = $eventType;
        }

        // supported: event id, transaction id, but maybe other fields are supported
        // not supported: billing agreement, email
        if ($search) {
            $params['transaction_id'] = $search;
        }

        if ($start) {
            $params['start_time'] = $start->format(DateTimeInterface::RFC3339);
        }

        if ($end) {
            $params['end_time'] = $end->format(DateTimeInterface::RFC3339);
        }

        $url .= '?' . http_build_query($params);

        $response = $this->sendRequest('GET', $url, [], null, 200);
        $response = json_decode($response, true);

        return $response['events'];
    }

    /**
     * List available hook events
     *
     * @return array
     */
    public function listEventTypes() : array
    {
        $url = '/v1/notifications/webhooks-event-types';

        $response = $this->sendRequest('GET', $url, [], null, 200);
        $response = json_decode($response, true);

        return $response['event_types'];
    }
}
