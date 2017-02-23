<?php
namespace Granam\Tests\GpWebPay\Codes;

use Alcohol\ISO4217;
use Granam\GpWebPay\Codes\CurrencyCodes;

class CurrencyCodesTest extends CodesTest
{
    /**
     * @test
     */
    public function I_can_ask_if_a_number_is_currency_code()
    {
        $currencyCodes = new CurrencyCodes(new ISO4217());
        self::assertTrue($currencyCodes->isCurrencyNumericCode(978 /* EUR */));
        self::assertTrue($currencyCodes->isCurrencyNumericCode(203 /* CZK */));
        self::assertTrue($currencyCodes->isCurrencyNumericCode(52 /* BBD */));
        self::assertFalse($currencyCodes->isCurrencyNumericCode(0));
    }

    /**
     * @test
     */
    public function I_can_get_currency_precision()
    {
        $currencyCodes = new CurrencyCodes(new ISO4217());
        self::assertSame(2, $currencyCodes->getCurrencyPrecision(978 /* EUR */));
        self::assertSame(3, $currencyCodes->getCurrencyPrecision(48 /* BHD */));
    }

    /**
     * @test
     * @expectedException \Granam\GpWebPay\Exceptions\UnknownCurrency
     * @expectedExceptionMessageRegExp ~0~
     */
    public function I_can_not_get_precision_for_unknown_currency()
    {
        (new CurrencyCodes(new ISO4217()))->getCurrencyPrecision(0);
    }
}