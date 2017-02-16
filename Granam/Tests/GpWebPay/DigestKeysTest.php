<?php
namespace Granam\Tests\GpWebPay;

use Granam\GpWebPay\RequestDigestKeys;
use PHPUnit\Framework\TestCase;

class DigestKeysTest extends TestCase
{
    /**
     * @test
     */
    public function I_can_get_list_of_all_digest_keys()
    {
        $reflectionClass = new \ReflectionClass(RequestDigestKeys::class);
        $constantValues = array_values($reflectionClass->getConstants());
        sort($constantValues);
        $digestKeys = RequestDigestKeys::getDigestKeys();
        sort($digestKeys);
        self::assertSame($constantValues, $digestKeys);
    }
}