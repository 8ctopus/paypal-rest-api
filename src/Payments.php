<?php

/**
 * https://developer.paypal.com/docs/api/payments/v2/
 */

declare(strict_types=1);

namespace Oct8pus\PayPal;

class Payments extends RestBase
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
     * Get info for authorized payment
     *
     * @param string $authorizationId
     *
     * @return array
     */
    public function getAuthorized(string $authorizationId) : array
    {
        $url = "/v2/payments/authorizations/{$authorizationId}";

        $response = $this->sendRequest('GET', $url, [], null, 200);

        return json_decode($response, true);
    }

    /**
     * Get info for captured payment
     *
     * @param string $captureId
     *
     * @return array
     */
    public function getCaptured(string $captureId) : array
    {
        $url = "/v2/payments/captures/{$captureId}";

        $response = $this->sendRequest('GET', $url, [], null, 200);

        return json_decode($response, true);
    }

    /**
     * Get info for refunded payment
     *
     * @param string $refundId
     *
     * @return array
     */
    public function getRefunded(string $refundId) : array
    {
        $url = "/v2/payments/refunds/{$refundId}";

        $response = $this->sendRequest('GET', $url, [], null, 200);

        return json_decode($response, true);
    }

    /**
     * Capture authorized payment
     *
     * @param string $id authorization id
     *
     * @return array
     */
    public function capture(string $id) : array
    {
        $url = "/v2/payments/authorizations/{$id}/capture";

        $response = $this->sendRequest('POST', $url, [], null, 201);

        return json_decode($response, true);
    }

    /**
     * Refund captured payment
     *
     * @param string $captureId
     *
     * @return array
     */
    public function refund(string $captureId) : array
    {
        $url = "/v2/payments/captures/{$captureId}/refund";

        $response = $this->sendRequest('POST', $url, [], null, 201);

        return json_decode($response, true);
    }

    /**
     * Void authorized payment
     *
     * @param string $authorizationId
     *
     * @return array
     */
    public function void(string $authorizationId) : array
    {
        $url = "/v2/payments/authorizations/{$authorizationId}/void";

        $response = $this->sendRequest('POST', $url, [], null, 201);

        return json_decode($response, true);
    }
}
