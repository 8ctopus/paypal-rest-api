<?php

/**
 * @reference https://developer.paypal.com/docs/api/webhooks/v1/
 */

declare(strict_types=1);

namespace Oct8pus\PayPal;

use Oct8pus\PayPal\OAuth;

class Hooks extends Curl
{
    private OAuth $auth;

    /**
     * Constructor
     *
     * @param OAuth $auth
     */
    public function __construct(OAuth $auth)
    {
        parent::__construct(true);

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

        $options = [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => [
                'Authorization: Bearer ' . $this->auth->get(),
                'Content-Type: application/json',
            ],
        ];

        $json = $this->curl($url, $options, 200);

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
        $data = [
            'url' => $url,
            'event_types' => [],
        ];

        foreach ($eventTypes as $type) {
            $data['event_types'][] = ['name' => $type];
        }

        $data = json_encode($data, JSON_PRETTY_PRINT);

        $url = '/v1/notifications/webhooks';

        $options = [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => [
                'Authorization: Bearer ' . $this->auth->get(),
                'Content-Type: application/json',
            ],
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $data,
        ];

        $json = $this->curl($url, $options, 201);

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

        $options = [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => [
                'Authorization: Bearer ' . $this->auth->get(),
                'Content-Type: application/json',
            ],
            CURLOPT_CUSTOMREQUEST => 'DELETE',
        ];

        $this->curl($url, $options, 204);

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

        $data = [
            'webhook_id' => $webhookId,
            'event_type' => $eventType,
            'resource_version' => $version,
        ];

        $options = [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => [
                'Authorization: Bearer ' . $this->auth->get(),
                'Content-Type: application/json',
            ],
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($data, JSON_PRETTY_PRINT),
        ];

        $json = $this->curl($url, $options, 202);

        return json_decode($json, true);
    }
}
