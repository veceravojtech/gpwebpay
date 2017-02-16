<?php
namespace Granam\Tests\GpWebPay;

use Granam\GpWebPay\DigestKeys;
use PHPUnit\Framework\TestCase;

class DigestKeysTest extends TestCase
{
    /**
     * @test
     */
    public function I_can_get_list_of_all_digest_keys()
    {
        $reflectionClass = new \ReflectionClass(DigestKeys::class);
        $constantValues = array_values($reflectionClass->getConstants());
        sort($constantValues);
        $digestKeys = DigestKeys::getDigestKeys();
        sort($digestKeys);
        self::assertSame($constantValues, $digestKeys);
    }
}