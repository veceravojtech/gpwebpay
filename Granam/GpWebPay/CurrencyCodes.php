<?php
namespace Granam\GpWebPay;

use Granam\Strict\Object\StrictObject;

class CurrencyCodes extends StrictObject implements Codes
{
    const EUR = 978;
    const CZK = 203;

    /**
     * @return array|int[]
     */
    public static function getCurrencyCodes()
    {
        return [
            'EUR' => self::EUR,
            'CZK' => self::CZK,
        ];
    }

    /**
     * @param int $code
     * @return bool
     */
    public static function isCurrencyCode(int $code)
    {
        return in_array($code, self::getCurrencyCodes(), true);
    }
}