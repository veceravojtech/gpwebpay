<?php
namespace Granam\Tests\GpWebPay\Exceptions;

use Granam\GpWebPay\Exceptions\GpWebPayErrorByCustomerResponse;
use Granam\Tests\Tools\TestWithMockery;

class GpWebPayErrorByCustomerResponseTest extends TestWithMockery
{
    /**
     * @test
     * @expectedException \Granam\GpWebPay\Exceptions\GpWebPayErrorByCustomerResponse
     * @expectedExceptionMessageRegExp ~foo.+17\(6\)~
     */
    public function I_can_use_it_as_exception()
    {
        throw new GpWebPayErrorByCustomerResponse(17, 6, 'foo');
    }

    /**
     * @test
     */
    public function I_am_warned_when_creating_this_with_non_customer_fail_codes()
    {
        $previousErrorReporting = error_reporting(-1 ^ E_USER_WARNING);
        error_clear_last();
        new GpWebPayErrorByCustomerResponse(1, 2, 'foo');
        $lastError = error_get_last();
        error_clear_last();
        error_reporting($previousErrorReporting);
        self::assertNotEmpty($lastError);
        self::assertSame(E_USER_WARNING, $lastError['type']);
        self::assertRegExp('~PR code 1.+SR code 2~', $lastError['message']);
    }
}