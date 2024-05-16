<?php

/**
 * @reference https://developer.paypal.com/docs/api/subscriptions/v1/#plans_list
 */

declare(strict_types=1);

namespace Oct8pus\PayPal;

use Oct8pus\PayPal\Plans\BillingCycles;
use Oct8pus\PayPal\Plans\Operation;
use Oct8pus\PayPal\Plans\PaymentPreferences;
use Oct8pus\PayPal\Plans\Taxes;
use stdClass;

enum Status : string
{
    case Created = 'CREATED';
    case Active = 'ACTIVE';
    case Inactive = 'INACTIVE';

    public static function fromLowerCase(string $value) : self
    {
        return self::from(strtoupper($value));
    }
}

class Plans extends RestBase
{
    /**
     * Constructor
     *
     * @param HttpHandler $handler
     * @param OAuth       $auth
     */
    public function __construct(bool $sandbox, HttpHandler $handler, OAuth $auth)
    {
        parent::__construct($sandbox, $handler, $auth);
    }

    /**
     * List plans
     *
     * @return array<mixed>
     *
     * @throws PayPalException
     */
    public function list() : array
    {
        $url = '/v1/billing/plans';

        $json = $this->sendRequest('GET', $url, [], null, 200);

        return json_decode($json, true)['plans'];
    }

    /**
     * Get plan
     *
     * @param string $id
     *
     * @return array<mixed>
     *
     * @throws PayPalException
     */
    public function get(string $id) : array
    {
        $url = "/v1/billing/plans/{$id}";

        $json = $this->sendRequest('GET', $url, [], null, 200);

        return json_decode($json, true);
    }

    /**
     * Create plan
     *
     * @param string             $productId
     * @param string             $name
     * @param string             $description
     * @param Status             $status
     * @param BillingCycles      $cycles
     * @param PaymentPreferences $payment
     * @param Taxes              $taxes
     *
     * @return array
     */
    public function create(string $productId, string $name, string $description, Status $status, BillingCycles $cycles, PaymentPreferences $payment, Taxes $taxes) : array
    {
        $url = '/v1/billing/plans';

        $object1 = new stdClass();
        $object1->product_id = $productId;
        $object1->name = $name;

        $object2 = new stdClass();
        $object2->description = $description;
        $object2->status = $status->value;

        $object = (object) array_merge((array) $object1, $cycles->object(), (array) $payment->object(), (array) $object2, (array) $taxes->object());

        $body = json_encode($object, JSON_PRETTY_PRINT);

        $json = $this->sendRequest('POST', $url, [], $body, 201);

        return json_decode($json, true, 512, JSON_THROW_ON_ERROR);
    }

    /**
     * Activate plan
     *
     * @param string $id
     *
     * @return self
     *
     * @throws PayPalException
     */
    public function activate(string $id) : self
    {
        $url = "/v1/billing/plans/{$id}/activate";

        $this->sendRequest('POST', $url, [], null, 204);

        return $this;
    }

    /**
     * Deactivate plan
     *
     * @param string $id
     *
     * @return self
     *
     * @throws PayPalException
     */
    public function deactivate(string $id) : self
    {
        $url = "/v1/billing/plans/{$id}/deactivate";

        $this->sendRequest('POST', $url, [], null, 204);

        return $this;
    }

    /**
     * Update plan
     *
     * @param string $id
     * @param Operation $operation
     * @param string $attribute
     * @param string|int|bool $value
     *
     * @return self
     *
     * @throws PayPalException
     */
    public function update(string $id, Operation $operation, string $attribute, string|int|bool $value) : self
    {
        $url = "/v1/billing/plans/{$id}";

        switch ($attribute) {
            case 'name':
            case 'description':
                $path = "/{$attribute}";
                break;

            default:
                throw new PayPalException("invalid attribute - {$attribute}");
        }

        $update = [
            [
                'op' => $operation,
                'path' => $path,
                'value' => $value,
            ],
        ];

        $body = json_encode($update, JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR);

        $this->sendRequest('PATCH', $url, [], $body, 204);

        return $this;
    }

    /**
     * Update pricing
     *
     * @param  string        $id
     * @param  BillingCycles $cycles
     *
     * @return self
     */
    public function updatePricing(string $id, BillingCycles $cycles) : self
    {
        $url = "/v1/billing/plans/{$id}/update-pricing-schemes";

        $body = json_encode($cycles->object(), JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR);

        $body = str_replace(['billing_cycles', 'sequence'], ['pricing_schemes', 'billing_cycle_sequence'], $body);

        $this->sendRequest('POST', $url, [], $body, 204);

        return $this;
    }
}
