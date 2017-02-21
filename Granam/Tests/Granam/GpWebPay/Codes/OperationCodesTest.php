<?php
namespace Granam\Tests\GpWebPay\Codes;

use Granam\GpWebPay\Codes\OperationCodes;
use Granam\Tests\Tools\TestWithMockery;

class OperationCodesTest extends TestWithMockery
{
    /**
     * @test
     */
    public function I_can_use_create_order_operation_code_as_constant()
    {
        self::assertTrue(defined(OperationCodes::class . '::CREATE_ORDER'));
        self::assertSame('CREATE_ORDER', OperationCodes::CREATE_ORDER);
    }
}