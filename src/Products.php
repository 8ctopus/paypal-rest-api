<?php

/**
 * https://developer.paypal.com/docs/api/catalog-products/v1/#products_create
 */

declare(strict_types=1);

namespace Oct8pus\PayPal;

use JsonException;
use Oct8pus\PayPal\OAuth\OAuth;

/* FIX ME
enum Operation : string
{
    case Add = 'add';
    case Replace = 'replace';
    case Remove = 'remove';
}

enum Path : string
{
    case Description = '/description';
    case Category = '/category';
    case HomeUrl = '/home_url';
    case ImageUrl = '/image_url';
}
*/

class Products extends RestBase
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
     * List products
     *
     * @return array<mixed>
     */
    public function list() : array
    {
        $url = '/v1/catalogs/products';

        $response = $this->sendRequest('GET', $url, [], null, 200);

        return json_decode($response, true)['products'];
    }

    /**
     * Get product
     *
     * @param string $id
     *
     * @return array<mixed>
     */
    public function get(string $id) : array
    {
        $url = "/v1/catalogs/products/{$id}";

        $response = $this->sendRequest('GET', $url, [], null, 200);

        return json_decode($response, true);
    }

    /**
     * Create product
     *
     * @param array<string> $product
     *
     * @return self
     *
     * @throws JsonException|PayPalException
     */
    public function create(array $product) : self
    {
        $keys = [
            'name',
            'description',
            'type', // Physical Goods, Digital Goods, Service
            'category', // Software
            'home_url',
            'image_url',
        ];

        foreach ($keys as $key) {
            if (!array_key_exists($key, $product)) {
                throw new PayPalException("missing key - {$key}");
            }
        }

        $url = '/v1/catalogs/products';

        $this->sendJsonRequest('POST', $url, [], $product, 201);

        return $this;
    }

    /**
     * Update product
     *
     * @param string $id
     * @param string $operation
     * @param string $path
     * @param string $value
     *
     * @return self
     */
    public function update(string $id, string $operation, string $path, string $value) : self
    {
        $update = [
            [
                'op' => $operation,
                'path' => "/{$path}",
                'value' => $value,
            ],
        ];

        $url = "/v1/catalogs/products/{$id}";

        $this->sendJsonRequest('PATCH', $url, [], $update, 204);

        return $this;
    }
}
