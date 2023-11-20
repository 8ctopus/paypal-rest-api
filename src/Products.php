<?php

/**
 * https://developer.paypal.com/docs/api/catalog-products/v1/#products_create
 */

declare(strict_types=1);

namespace Oct8pus\PayPal;

use JsonException;

class Products extends RestBase
{
    /**
     * Constructor
     *
     * @param HttpHandler $handler
     * @param OAuth       $auth
     */
    public function __construct(HttpHandler $handler, OAuth $auth)
    {
        parent::__construct(true, $handler, $auth);
    }

    /**
     * List products
     *
     * @return array<mixed>
     */
    public function list() : array
    {
        $url = '/v1/catalogs/products';

        $json = $this->request('GET', $url, [], null, 200);

        return json_decode($json, true)['products'];
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

        $json = $this->request('GET', $url, [], null, 200);

        return json_decode($json, true);
    }

    /**
     * Add product
     *
     * @param array<string> $product
     *
     * @return self
     *
     * @throws JsonException|PayPalException
     */
    public function add(array $product) : self
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
                throw new PayPalException("missing key {$key}");
            }
        }

        $url = '/v1/catalogs/products';

        $body = json_encode($product, JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR);

        $this->request('POST', $url, [], $body, 201);

        return $this;
    }
}
