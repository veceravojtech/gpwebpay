<?php
namespace Granam\Tests\GpWebPay;

use Granam\GpWebPay\CurrencyCodes;
use PHPUnit\Framework\TestCase;

class CurrencyCodesTest extends TestCase
{
    /**
     * @test
     */
    public function I_can_get_all_currency_codes_indexed_by_their_names()
    {
        $reflectionClass = new \ReflectionClass(CurrencyCodes::class);
        $constantValues = $reflectionClass->getConstants();
        ksort($constantValues);
        $currencyCodes = CurrencyCodes::getCurrencyCodes();
        ksort($currencyCodes);
        self::assertSame($constantValues, $currencyCodes);
    }

    /**
     * @test
     * @param int $code
     * @param bool $expectedResult
     * @dataProvider provideNumberAndIfIsCurrencyCode
     */
    public function I_can_ask_if_a_number_is_currency_code(int $code, bool $expectedResult)
    {
        self::assertSame($expectedResult, CurrencyCodes::isCurrencyCode($code));
    }

    public function provideNumberAndIfIsCurrencyCode()
    {
        $values = [];
        foreach (CurrencyCodes::getCurrencyCodes() as $currencyCode) {
            $values[] = [$currencyCode, true];
        }
        $values[] = [0, false];

        return $values;
    }
}