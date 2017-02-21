<?php
namespace Granam\Tests\GpWebPay\Codes;

use Granam\GpWebPay\Codes\ResponseDigestKeys;
use Granam\GpWebPay\Codes\ResponsePayloadKeys;
use Granam\Tests\Tools\TestWithMockery;

class ResponsePayloadKeysTest extends TestWithMockery
{

    /**
     * @test
     */
    public function I_can_get_both_non_digest_and_digest_keys_for_response()
    {
        $expectedConstants = ['DIGEST', 'DIGEST1'];
        foreach ($expectedConstants as $expectedConstant) {
            self::assertTrue(defined(ResponsePayloadKeys::class . '::' . $expectedConstant));
            self::assertSame($expectedConstant, constant(ResponsePayloadKeys::class . '::' . $expectedConstant));
        }
        self::assertSame(
            ResponsePayloadKeys::getResponsePayloadKeys(),
            array_merge(ResponseDigestKeys::getResponseDigestKeys(), $expectedConstants)
        );
    }
}