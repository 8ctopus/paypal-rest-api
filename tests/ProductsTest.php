<?php

declare(strict_types=1);

namespace Tests;

use Nimbly\Capsule\Factory\RequestFactory;
use Nimbly\Capsule\Factory\StreamFactory;
use Nimbly\Capsule\Response;
use Nimbly\Shuttle\Shuttle;
use Oct8pus\PayPal\Products;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 *
 * @covers \Oct8pus\PayPal\Products
 * @covers \Oct8pus\PayPal\RestBase
 */
final class ProductsTest extends TestCase
{
    private static HttpHandlerMock $handler;
    private static OAuthMock $auth;
    private static Products $products;

    public static function setUpBeforeClass() : void
    {
        self::$handler = new HttpHandlerMock(new Shuttle(), new RequestFactory(), new StreamFactory());
        self::$auth = new OAuthMock(self::$handler, 'testId', 'testSecret');
        self::$products = new Products(true, self::$handler, self::$auth);
    }

    public function testConstructor() : void
    {
        self::assertInstanceOf(Products::class, new Products(true, self::$handler, self::$auth));
    }

    public function testList() : void
    {
        self::$handler->setResponse(new Response(200, file_get_contents(__DIR__ . '/fixtures/ProductsList.json')));

        self::$products->list();

        $expected = <<<'TEXT'
        https://api-m.sandbox.paypal.com/v1/catalogs/products
        Host: api-m.sandbox.paypal.com
        Authorization: Bearer test
        Content-Type: application/json

        TEXT;

        self::assertSame($expected, self::$handler->dumpRequest());
    }

    public function testGet() : void
    {
        self::$handler->setResponse(new Response(200, file_get_contents(__DIR__ . '/fixtures/ProductDetails.json')));

        $id = 'P-5ML4271244454362WXNWU5NQ';

        self::$products->get($id);

        $expected = <<<TEXT
        https://api-m.sandbox.paypal.com/v1/catalogs/products/{$id}
        Host: api-m.sandbox.paypal.com
        Authorization: Bearer test
        Content-Type: application/json

        TEXT;

        self::assertSame($expected, self::$handler->dumpRequest());
    }

    public function testCreate() : void
    {
        self::$handler->setResponse(new Response(201, file_get_contents(__DIR__ . '/fixtures/ProductAdd.json')));

        $product = [
            'name' => 'Video Streaming Service',
            'type' => 'SERVICE',
            'description' => 'Video streaming service',
            'category' => 'SOFTWARE',
            'image_url' => 'https://example.com/streaming.jpg',
            'home_url' => 'https://example.com/home',
        ];

        self::$products->create($product);

        $expected = <<<'TEXT'
        https://api-m.sandbox.paypal.com/v1/catalogs/products
        Host: api-m.sandbox.paypal.com
        Authorization: Bearer test
        Content-Type: application/json

        TEXT;

        self::assertSame($expected, self::$handler->dumpRequest());
    }
}
