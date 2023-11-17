<?php

/**
 * https://developer.paypal.com/docs/api/catalog-products/v1/#products_create
 */

declare(strict_types=1);

namespace Oct8pus\PayPal;

use Oct8pus\PayPal\Client;
use Oct8pus\PayPal\OAuth;
use HttpSoft\Message\RequestFactory;
use HttpSoft\Message\Stream;
use Nimbly\Shuttle\Shuttle;

class Products extends Client
{
    private OAuth $auth;

    /**
     * Constructor
     *
     * @param OAuth $auth - OAuth 2.0 token
     */
    public function __construct(OAuth $auth)
    {
        $shuttle = new Shuttle();
        $factory = new RequestFactory();
        $stream = new Stream();

        parent::__construct(true, $shuttle, $factory, $stream);

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
