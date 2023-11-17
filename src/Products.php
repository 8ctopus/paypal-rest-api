<?php

/**
 * https://developer.paypal.com/docs/api/catalog-products/v1/#products_create
 */

declare(strict_types=1);

namespace Oct8pus\PayPal;

class Products extends RestBase
{
    private OAuth $auth;

    /**
     * Constructor
     *
     * @param RequestHandler $handler
     * @param OAuth $auth
     */
    public function __construct(RequestHandler $handler, OAuth $auth)
    {
        parent::__construct(true, $handler);

        $this->auth = $auth;
    }

    /**
     * List products
     *
     * @return array
     */
    public function list() : array
    {
        $url = '/v1/catalogs/products';

        $headers = [
            'Authorization' => 'Bearer ' . $this->auth->token(),
            'Content-Type' => 'application/json',
        ];

        $json = $this->request('GET', $url, $headers, null, 200);

        return json_decode($json, true)['products'];
    }

    /**
     * Get product
     *
     * @param string $id
     *
     * @return array
     */
    public function get(string $id) : array
    {
        $url = "/v1/catalogs/products/{$id}";

        $headers = [
            'Authorization' => 'Bearer ' . $this->auth->token(),
            'Content-Type' => 'application/json',
        ];

        $json = $this->request('GET', $url, $headers, null, 200);

        return json_decode($json, true);
    }

    /**
     * Add product
     *
     * @param array $product
     *
     * @return self
     *
     * @throws PayPalException
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

        $headers = [
            'Authorization' => 'Bearer ' . $this->auth->token(),
            'Content-Type' => 'application/json',
        ];

        $body = json_encode($product, JSON_PRETTY_PRINT);

        $this->request('POST', $url, $headers, $body, 201);

        return $this;
    }
}
