<?php

declare(strict_types=1);

namespace Tests\OAuth;

use Nimbly\Capsule\Factory\RequestFactory;
use Nimbly\Capsule\Factory\StreamFactory;
use Nimbly\Capsule\Response;
use Nimbly\Shuttle\Shuttle;
use Oct8pus\PayPal\OAuth\OAuthCache;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Tests\HttpHandlerMock;

/**
 * @internal
 */
#[CoversClass(OAuthCache::class)]
final class OAuthCacheTest extends TestCase
{
    private static HttpHandlerMock $handler;
    private static string $cacheFile;

    public static function setUpBeforeClass() : void
    {
        self::$handler = new HttpHandlerMock(new Shuttle(), new RequestFactory(), new StreamFactory());
        self::$cacheFile = tempnam(sys_get_temp_dir(), 'php');
        unlink(self::$cacheFile);
    }

    public function testTokenNoCache() : void
    {
        self::$handler->setResponse(new Response(200, file_get_contents(__DIR__ . '/../fixtures/OAuth.json')));

        $token = (new OAuthCache(true, self::$handler, 'testId', 'testSecret', self::$cacheFile))
            ->token();

        self::assertSame('A21AAKhpstvuJ7TbYe3kZrQDlO-nmW_Dxby3W06pGsWNRGH447IxnAbWXTD_01X1PT_ISaoY-Fq7LI-VWDgxAUqPDYONyZ9fA', $token);
    }

    public function testTokenCache() : void
    {
        self::$handler->setResponse(new Response(200, file_get_contents(__DIR__ . '/../fixtures/OAuth.json')));

        $token = (new OAuthCache(true, self::$handler, 'testId', 'testSecret2', self::$cacheFile))
            ->token();

        self::assertSame('A21AAKhpstvuJ7TbYe3kZrQDlO-nmW_Dxby3W06pGsWNRGH447IxnAbWXTD_01X1PT_ISaoY-Fq7LI-VWDgxAUqPDYONyZ9fA', $token);
    }
}
